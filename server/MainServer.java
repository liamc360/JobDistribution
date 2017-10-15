import java.util.*;
import java.io.*;
import java.net.*;
import java.util.concurrent.*;
import java.lang.management.*;

/*
	MainServer class listens for connections from the webserver and forwards them to
	Server Threads. 
	Handles the recovery file for incomplete tasks, providing
	methods to read, save and delete tasks to/from the file. 
	Handles the IP Address of the machine and clients, providing methods to save
	them to files.
*/
public class MainServer {
	
	private static ExecutorService workers;
	private static ServerSocket server;
	private static int PORT_NUM = 8191;
	
	//locations for files
	private static String recoveryFileLocation = "../recovery.txt";
	private static String ipFileLocation = "../ip.txt";
	
	//holds the recovered tasks from recovery.txt
	private static ArrayList<String> workingTaskList = new ArrayList<String>();
	
	public static void main(String[] args)
    {
		//setup dynamic thread pool for connections
		workers = Executors.newCachedThreadPool();
		Socket incoming = null;
		
		//check if server is already running on this machine
		try
		{		
			server = new ServerSocket(PORT_NUM);
		}
		catch (Exception e) 
		{
			System.err.println("Socket already in use, shutting down");
			System.exit(0);
		}

		getIP(); //get ip address of machine and save it to file
		saveClients(); //save clients to hosts file
		loadRecoveryFile(); //load the recovery tasks from recoveryFileLocation

		try
		{
			//loop through the workingTaskList and create server threads with the task string
			for(int i=0;i<workingTaskList.size();i++)
			{
				String task = workingTaskList.get(i);
				workers.execute(new Server(task));
			}
			
			System.out.println("Waiting for connections\n\n");
			
			//wait for connections from the webserver and create new server threads
			for (;;)
			{
				incoming = server.accept();
				workers.execute(new Server(incoming));
			}
		} 
		catch (IOException ioe)
		{
			System.err.println(ioe.getMessage());
		} 
		finally
		{
			try {server.close();} catch (IOException e) {}
			System.exit(1);
		}
	}
	
	
	/*method to save the IP addresses of clients into a file called 'hosts'
	this will be used for scripts which start/shutdown the client software*/
	private static void saveClients()
	{
		//connect to database to retrieve clients as an array
		DBConnect connect = new DBConnect();
		Client[] usedClients = connect.getClientInfo();
		connect.disconnectFromDB();
		try
		{
			BufferedWriter outputWriter = null;
			outputWriter = new BufferedWriter(new FileWriter("../hosts"));
			
			//write each client IP address on a new line in the file
			for (int i = 0; i < usedClients.length; i++) 
			{
				outputWriter.write(usedClients[i].getIP()+"");
				outputWriter.newLine();
			}
			outputWriter.flush();  
			outputWriter.close();  
		}
		catch (IOException ioe)
		{
			System.err.println("Error saving clients");
			System.err.println(ioe.getMessage());
		}
	}
	
	
	//method to get the IPV4 address of the current machine 
	private static void getIP()
	{
		try
		{
			InetAddress IP = InetAddress.getLocalHost();
			System.out.println("IP of server is := "+IP.getHostAddress());
			System.out.println("using port "+PORT_NUM);
			saveIP(IP.getHostAddress());
		}
		catch (Exception se) 
		{
			saveIP("Cannot load IP of server");
			System.err.println("Error getting IP");
		}
	}
	
	//method to save the IP address into a .txt file
	private static void saveIP(String IP)
	{	
		BufferedWriter outputWriter = null;
		try
		{
			outputWriter = new BufferedWriter(new FileWriter(ipFileLocation));
			outputWriter.write(IP);
			outputWriter.flush();  
			outputWriter.close();
			System.out.println("Saved IP to file: "+ipFileLocation);		
			
		}
		catch (IOException e) 
		{
			System.err.println("Error writing to file: "+ipFileLocation);		
			e.printStackTrace();
		}
	}
	
	//method to save the workingTaskList to a text file at 'recoveryFileLocation'
	private static void saveRecoveryFile()
	{	
		try
		{
			BufferedWriter outputWriter = new BufferedWriter(new FileWriter(recoveryFileLocation));
			
			//write each task to a new line in the file
			for (int i = 0; i < workingTaskList.size(); i++)
			{
				outputWriter.write(workingTaskList.get(i));
				outputWriter.newLine();
			}
			
			outputWriter.flush();  
			outputWriter.close();
			System.out.println("Saved file: "+recoveryFileLocation);		
			
		}
		catch (IOException e) 
		{
			System.err.println("Error writing to file: "+recoveryFileLocation);		
			e.printStackTrace();
		}
	}
	
	//method to load the contents of recovered tasks file into arraylist 'workingTaskList'
	protected static void loadRecoveryFile()
	{
		try
		{
			Scanner s = new Scanner(new File(recoveryFileLocation));
			
			//loop until end of file
			while (s.hasNext())
			{
				workingTaskList.add(s.next());
			}
			s.close();
			System.out.println("loaded "+workingTaskList.size()+" recovery tasks");
		}
		catch (IOException ioe)
		{
			System.err.println("Error: cannot load "+recoveryFileLocation);
			ioe.printStackTrace();
		}		
	}
	
	//method to remove a task from arraylist 'workingTaskList'
	protected static synchronized void removeTask(String task)
	{
		//loop through arraylist
		for(int i=0;i<workingTaskList.size();i++)
		{
			String x = workingTaskList.get(i);
			
			//check if task matches provided task string and remove it from arraylist
			if(x.startsWith(task))
			{
				workingTaskList.remove(i);
				System.out.println("Removed task "+task);
				break;
			}
		}
		//write workingTaskList to recovery file
		saveRecoveryFile();
	}
	
	//method to add a new task string to the arraylist 'workingTaskList'
	protected static synchronized void addTask(String task)
	{
		System.out.println("Adding new task: "+task);
		workingTaskList.add(task);
		
		//write workingTaskList to recovery file
		saveRecoveryFile();
	}
	
}