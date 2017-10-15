import java.io.*;
import java.util.*;
import java.net.*;
import java.util.concurrent.*;
import java.util.concurrent.atomic.AtomicInteger;
import java.util.Date;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.sql.Timestamp;

public class ServerSession implements Runnable
{
	private static final int MAX_RECONNECT_ATTEMPTS = 1000;
	private static final int RECONNECT_WAIT_MILLIS = 10000;
	
	private static final int MAX_TIMES_KILLED = 2;
	
	private BufferedReader serverIn;
	private PrintWriter serverOut;
	private Socket serverSocket;
	private boolean validConnection;
	
	private int workerNum = 0;
	private Load loadObject;
	private Task task;
	private Client client;
	
	
	
	
	public ServerSession(Load l, int wNum, Client c, Task t){
		loadObject = l;
		workerNum = wNum;
		client = c;
		task = t;
	}
	
	private void stopSession(){
		try {
			if(serverSocket!=null){
				System.out.println("Closing connection to client "+client.getClientID());
				
				serverOut.println("2");
				serverOut.flush();
				
				serverIn.close();
				serverOut.close();
				serverSocket.close();
			} 
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}	
	}
	
	//method to connect to the client PC
	private void connectToClient()
	{
		int reconnectAttempts = 0;
		validConnection = false;
		
		//loop to retry connection until limit is reached
		while(reconnectAttempts < MAX_RECONNECT_ATTEMPTS)
		{
			//check if jobs remain
			if(task.numJobsCompleted < task.totalJobs)
			{		
				try 
				{
					//open socket and input/output streams to client PC
					serverSocket = new Socket(client.getIP(), client.getPORT());
					serverIn = new BufferedReader(
											new InputStreamReader(serverSocket.getInputStream()));
					serverOut = new PrintWriter(
											new OutputStreamWriter(serverSocket.getOutputStream()));
					validConnection = true;
					break;
				} 
				catch (IOException e) //could not connect
				{
					reconnectAttempts++;
					System.err.println("Error: could not connect to client "+client.getIP()+" using port "+client.getPORT()+". Retrying in "+RECONNECT_WAIT_MILLIS+"ms");
					
					try 
					{
						Thread.sleep(RECONNECT_WAIT_MILLIS); //wait 10seconds
					} 
					catch (InterruptedException ie) 
					{
						ie.printStackTrace();
					}
				}
			} 
			else 
			{
				stopSession();
				break;
			}
		}
	}
	
	public void run() 
	{
		connectToClient();
		if(validConnection == false)
		{
			return; //kill thread
		}
		
		Job nextJob = null;
		int jobID;
		boolean jobCompleted;
		double clientLoad;
		int clientMemory;
		
		while(task.numJobsCompleted<task.totalJobs)
		{
			try
			{
				//set load and memory to impossible values to handle disconnect
				clientLoad = -100.0;
				clientMemory = -1;
				jobCompleted = false;
				
				//request memory+load
				serverOut.println("1");
				serverOut.flush();
				
				//request memory and load from client
				String fromClient = null;
				while((fromClient = serverIn.readLine()) != null){
					String[] memoryAndLoad = fromClient.split("@");
					clientLoad = Double.parseDouble(memoryAndLoad[0]);
					clientMemory = Integer.parseInt(memoryAndLoad[1]);
					break;
				}
				
				System.out.println("clientMemory "+clientMemory+" task memory "+task.taskMemory+"  client load "+clientLoad);
				
				//check memory and load requirements
				if(loadObject.checkRequirements(clientMemory, clientLoad, task.taskMemory, task.taskLoad, workerNum))
				{
					//get next job in queue
					nextJob = task.getNextJob();
					jobID = nextJob.getJobID();
					
					//form job string to be sent to client
					String jobString = "0@";
					jobString = jobString+task.userID+"@";
					jobString = jobString+task.taskID+"@";
					jobString = jobString+jobID+"@";
					jobString = jobString+nextJob.getInputFile()+"@";
					jobString = jobString+nextJob.getProgram()+"@";
					jobString = jobString+nextJob.getPreProcessor()+"@";
					jobString = jobString+nextJob.getParameters()+"@";
					jobString = jobString+nextJob.getPostProcessor()+"@";
					jobString = jobString+task.timeout;
					
					System.out.println("sent "+jobString+" to client "+client.getClientID());
					
					//update working job
					task.connect.saveWorkingJob(task.taskID, jobID, "working", client.getClientID());
					
					//get current time
					Timestamp timestamp = new Timestamp(System.currentTimeMillis());
					long jobSentTime = timestamp.getTime();
					
					//send job to client
					serverOut.println(jobString);
					serverOut.flush();
					
					//wait for client response
					fromClient = null;
					while((fromClient = serverIn.readLine()) != null)
					{
						//client finished working on job so update its midprogress jobs
						loadObject.midProgressJobs[workerNum] = 0;
						timestamp = new Timestamp(System.currentTimeMillis());
												
						//get current time
						long jobFinishedTime = timestamp.getTime();
					
						//add new start time and finisht ime to finishedJobs list
						loadObject.finishedJobs.add(new times(jobSentTime, jobFinishedTime));
						
						
						//load response from client into variables
						String[] jobResponse = fromClient.split("@");
						String jobStatus = jobResponse[0];
						String startTime = jobResponse[1];
						String finishTime = jobResponse[2];
						String logName = jobResponse[3];
						System.out.println(jobStatus+" "+startTime+" "+finishTime+" "+logName);
						
						if(jobStatus.equals("killed")) //job did not complete
						{
							nextJob.updateTimesKilled();
							if(nextJob.getTimesKilled()>MAX_TIMES_KILLED) //check if job killed too many times
							{
								//save job as killed
								System.out.println("job "+jobID+" was killed too many times");
								task.connect.saveCompletedJob(task.taskID, jobID, startTime, finishTime, jobStatus, logName, client.getClientID());
								task.numJobsCompleted++;
								jobCompleted = true;
							}
						} 
						else //job completed
						{	
							//save job as completed/timeout
							System.out.println("client "+client.getClientID()+" completed job "+jobID+" for task "+task.taskID+" with status: " +jobStatus);
							task.connect.saveCompletedJob(task.taskID, jobID, startTime, finishTime, jobStatus, logName, client.getClientID());
							task.numJobsCompleted++;
							jobCompleted = true;
						}
						break;
					}
					//check if job completed or client disconnected
					if(jobCompleted == false)
					{
						task.connect.saveWorkingJob(task.taskID, jobID, "error", client.getClientID());
						System.err.println("adding job back to queue");
						task.addJobBackToQueue(nextJob);
					}
				} 
				else
				{
					//check if client disconnected before it was sent a job
					if(clientLoad==-100.0)
					{
						System.err.println("client "+client.getClientID()+" has disconnected!");
						connectToClient();
					}
					else //client does not have enough memory/sufficient load. wait 10 seconds
					{
						System.out.println("client "+client.getClientID()+" does not have enough memory "+clientMemory+"/"+task.taskMemory+" or load "+clientLoad+"/"+task.taskLoad);
						Thread.sleep(10000);
					}
				}
			}
			catch(IndexOutOfBoundsException iob) //no jobs left in queue. enter wait loop
			{
				while(task.getQueueSize() == 0 && task.numJobsCompleted<task.totalJobs)
				{
					System.err.println("No jobs left but task incomplete. Waiting "+RECONNECT_WAIT_MILLIS+"ms");
					
					try 
					{
						Thread.sleep(10000);
					} 
					catch (InterruptedException e) 
					{
						e.printStackTrace();
					}
				}
			}
			catch(InterruptedException ie)
			{
				ie.printStackTrace();
			}
			catch(IOException e)
			{	
				task.addJobBackToQueue(nextJob);
				System.err.println("Could not reach client");
				e.printStackTrace();
				connectToClient();
			}
		}
		stopSession();
	}
}