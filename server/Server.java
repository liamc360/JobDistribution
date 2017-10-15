import java.util.*;
import java.io.*;
import java.net.*;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.attribute.FileAttribute;
import java.nio.file.attribute.PosixFilePermission;
import java.nio.file.attribute.PosixFilePermissions;
import java.util.concurrent.*;
import java.lang.management.ManagementFactory;
import javax.management.*;
import java.security.MessageDigest;
import java.text.SimpleDateFormat;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.regex.PatternSyntaxException;
import javax.xml.bind.DatatypeConverter;

public class Server implements Runnable {
	
	//socket and streams
	private Socket webConnection;
	private BufferedReader in;
	private OutputStream out;
	
	//states if a recovered task is used or one from the website
	private boolean validConnection = false;
	
	//holds information about the client machines selected for this task
	private Client[] usedClients;
	
	//task information
	protected Task task;
	
	//ID of the task to cancel
	private static int taskToCancel = -1;
	
	//instance of database
	protected DBConnect connect;
	
	//task to process from recovered file
	private String recoveryTask = "";
	
	//executorservice for ServerSession threads
	private ExecutorService sessions;
	
	private boolean shouldProgressCheck = false;
	
	//constructor for Server with connection from website
	public Server(final Socket s)
	{
		webConnection = s;
		validConnection = true;	
	}
	
	//constructor for Server with a task from recovery file
	public Server(String recoveredTask)
	{
		recoveryTask = recoveredTask;
	}
	
	//method to get the current datetime
	private String getCurrentTimeStamp() 
	{
	    return new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SSS").format(new Date());
	}
	
	//method to send a taskPieces to the webserver
	private void sendMessage(String msg)
	{
		try
		{
			byte[] byteMessage = buildFrame(msg);
			out.write(byteMessage);
			out.flush();
		}
		catch(Exception ex)
		{ 
			System.err.println("error sending result to webConnection");
		}
	}
	
	//method to setup ServerSession threads 
	private void createSessions()
	{
		//setup dynamic threadpool
		sessions = Executors.newCachedThreadPool();
		
		//round load up to nearest whole number
		int ceilLoad = (int)Math.ceil(task.taskLoad);
		
		//loop through each client selected
		for(int clientNum=0; clientNum<usedClients.length; clientNum++)
		{
			//create seperate Load object for each client
			Load tempClientLoad = new Load(ceilLoad);
			Client tempClient = usedClients[clientNum];
			
			//create ServerSession thread for each load
			for(int workerNum=0; workerNum<ceilLoad; workerNum++)
			{
				sessions.execute(new ServerSession(tempClientLoad, workerNum, tempClient, task));
			}
		}
	}
	
	private void createTask(String[] taskPieces)
	{
		//load task info
		int taskID = Integer.parseInt(taskPieces[1]);
		int userID = Integer.parseInt(taskPieces[2]);
		int numClients = Integer.parseInt(taskPieces[3]);
		
		//load each client id from taskPieces into a list
		List<Integer> clientIDs = new ArrayList<Integer>();
		for(int i=0;i<numClients;i++)
		{
			String clientID = taskPieces[4+i];
			clientIDs.add(Integer.parseInt(clientID));
		}
		
		try
		{
			//create new database instance
			DBConnect con = new DBConnect();
			
			//get task information
			TaskInfo tempTask = con.getTaskInfo(taskID);
			double taskLoad = tempTask.taskLoad;
			int taskMemory = tempTask.taskMemory;
			int taskTimeout = tempTask.timeout;
			
			//get job information
			ArrayList<Job> jobQueue = con.getJobsForTask(taskID);
			int totalJobs = jobQueue.size();
			
			//get client information
			usedClients = con.getClients(clientIDs);
			
			//update any 'working' jobs as the previous server ended before the task was finished
			if(validConnection != true)
			{
				con.updateErrorJobs(taskID);
			}
			else //add task to recovery file
			{
				MainServer.addTask(recoveryTask);
			}
			
			//create task
			task = new Task(userID, taskID, taskLoad, taskMemory, taskTimeout, jobQueue, totalJobs, con);
			
			//create sessions to work on the task
			createSessions();
			
			System.out.println("Task "+taskID+" successfully started execution!");
			System.out.println("Currently at 0 jobs completed out of "+totalJobs);
			
			//enter while loop to monitor this task upon exiting method
			shouldProgressCheck = true;
		}
		catch(Exception e)
		{
			System.err.println("error setting up task");
		}
	}
	
	public void run()
	{
		//holds the task parts (operation,userid,taskid,clients)
		String[] taskPieces;
		try
		{
			//webserver connection
			if(validConnection)
			{		
				//setup input and outputstrams
				in = new BufferedReader(new InputStreamReader(webConnection.getInputStream()));
				out = webConnection.getOutputStream();
				
				//read message from webserver
				recoveryTask = in.readLine();
				System.out.println("from webserver: "+recoveryTask);
			}
			
			//split task string into array
			taskPieces = recoveryTask.split("@");
		
			//get the requested operation
			String operation = taskPieces[0];
	
			if(operation.equals("0")) //task start
			{	
				createTask(taskPieces);
			}
			else if(operation.equals("1")) //task cancellation
			{
				//get the task to cancel
				taskToCancel = Integer.parseInt(taskPieces[1]);
				
				DBConnect tempCon = new DBConnect();
				tempCon.saveCompletedTask(taskToCancel, 2, getCurrentTimeStamp());
				tempCon.saveCancelledJobs(taskToCancel);
				tempCon.disconnectFromDB();
				
				
				System.out.println("user cancelled task "+taskToCancel);
			}
		}
		catch(Exception ex) //webserver closes connection
		{	
			System.err.println("Error parsing task string");
			ex.printStackTrace();
		}
		finally //close connection if needed
		{
			if(validConnection)
			{		
				closeConnection();
			}
		}
		
		//wait for further events
		while(shouldProgressCheck) 
		{
			//check if this task should be cancelled
			if(taskToCancel == task.taskID)
			{
				//cancel task in database
				task.connect.saveCompletedTask(taskToCancel, 2, getCurrentTimeStamp());
				task.connect.saveCancelledJobs(taskToCancel);
				task.connect.disconnectFromDB();
				
				//remove current task from list of recovered tasks
				MainServer.removeTask(recoveryTask);
				
				//complete the jobs to stop ServerSessions
				task.numJobsCompleted = task.totalJobs;
				shouldProgressCheck = false;
			}
			else if(task.numJobsCompleted>=task.totalJobs) //task completed
			{
				//save task in database
				task.connect.saveCompletedTask(task.taskID, 1, getCurrentTimeStamp());
				
				//remove task from list of recovered tasks
				MainServer.removeTask(recoveryTask);
				System.out.println("Task "+task.taskID+" has stopped executing");
				task.connect.disconnectFromDB();
				shouldProgressCheck = false;
			}
			else //sleep
			{
				try 
				{
					Thread.sleep(200);
				} 
				catch (InterruptedException e) 
				{
					e.printStackTrace();
				}
			}
		}
	}
	
	//method to close the webserver socket
	private void closeConnection()
	{
		System.out.println("Closing connections");
		try
		{
			webConnection.close();
		}
		catch(Exception e)
		{
			System.out.println("Error closing connection to webConnection");
		}
	}
	
	public static byte[] buildFrame(String taskPieces)
	{
		int length = taskPieces.length();
		int rawDataIndex = -1;
		if (length <= 125)
			rawDataIndex = 2;
		else if (length >= 126 && length <= 65535)
			rawDataIndex = 4;
		else
			rawDataIndex = 10;
		byte[] frame = new byte[length + rawDataIndex];
		frame[0] = (byte)129;
		if (rawDataIndex == 2)
			frame[1] = (byte)length;
		else if (rawDataIndex == 4)
		{
			frame[1] = (byte)126;
			frame[2] = (byte)(( length >> 8 ) & (byte)255);
			frame[3] = (byte)(( length      ) & (byte)255);
		}
		else
		{
			frame[1] = (byte)127;
			frame[2] = (byte)(( length >> 56 ) & (byte)255);
			frame[3] = (byte)(( length >> 48 ) & (byte)255);
			frame[4] = (byte)(( length >> 40 ) & (byte)255);
			frame[5] = (byte)(( length >> 32 ) & (byte)255);
			frame[6] = (byte)(( length >> 24 ) & (byte)255);
			frame[7] = (byte)(( length >> 16 ) & (byte)255);
			frame[8] = (byte)(( length >>  8 ) & (byte)255);
			frame[9] = (byte)(( length       ) & (byte)255);

		}
		for (int i = 0; i < length; i++)
			frame[rawDataIndex + i] = (byte)taskPieces.charAt(i);
		
		return frame;
	}
	
	
}
