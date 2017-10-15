import java.io.*;
import java.net.Socket;
import java.util.*;
import java.net.*;
import java.util.concurrent.*;
import java.util.concurrent.atomic.AtomicInteger;
import java.util.Date;
import java.text.DateFormat;
import java.text.SimpleDateFormat;



public class ClientSession implements Runnable
{
	private BufferedReader clientIn;
	private PrintWriter clientOut;
	private final Socket client;
	private Process p = null;
	private final static String CONF_PATH = "../bin/";
	private final static String PROGRAMS_PATH = "../bin/";
	private final static String LOG_PATH = "../logs/";
	private final static String PROBLEMS_PATH = "../problems/";
	
	private List<String> commands = new ArrayList<String>();
	
	private String[] serverMessage;
	private String operation;
	private String logName;
	private String status;
	private String fromServer = null;
	private String finishTime = "";
	private String startTime = "";
	
	private int userID;
	private int taskID;
	private int jobID;
	private String inputFile;
	private String program;
	private String preProcessor;
	private String parameters;
	private String postProcessor;
	private int timeout;
	private String logLocation;
	
	public ClientSession(final Socket s)
	{
		client = s;
	}
	
	//method to check the available RAM of the system in megabytes
	private static int checkMemory()
	{
		int memoryMB = 0;
		
		//command to get memory
		String[] cmd = { "bash","-c","free -m | awk '/Mem:/ { free=$4 } END { print free}'" };
		Process process;
		try 
		{
			//run the command
			process = Runtime.getRuntime().exec(cmd);
			
			//create buffered reader to read the output of the command
		    BufferedReader reader = new BufferedReader(new InputStreamReader(        
		        process.getInputStream()));                                          
			
			//read output of command
			String s;                                                                
		    while ((s = reader.readLine()) != null) 
		    {
		    	System.out.println("Free memory (MB) = " + s);    
		    	memoryMB = Integer.parseInt(s);
		    	break;
		    }
		}
		catch (IOException e) 
		{
			e.printStackTrace();
		} 
		return memoryMB;
	}
	
	//method to check the average load of the system over the past 60 seconds
	private static double checkLoad()
	{
		double load = 0.0;
		
		//command to get load
		String[] cmd = { "bash","-c","cat /proc/loadavg | awk '{ load=$1 } END { print load}'" };
		Process process;
		try 
		{
			//run the command
			process = Runtime.getRuntime().exec(cmd);
			                   
			//create buffered reader to read the output of the command
		    BufferedReader reader = new BufferedReader(new InputStreamReader(        
		        process.getInputStream()));                                          
		   
			//read output of command
			String s;                                                                
		    while ((s = reader.readLine()) != null) 
		    {
		    	System.out.println("Load for past 1 minute = " + s);     
		    	load = Double.parseDouble(s);
		    	break;
		    }
		}
		catch (IOException e) 
		{
			e.printStackTrace();
		} 
	
		return load;
	}
	
	private String getCurrentTimeStamp() {
	    return new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SSS").format(new Date());
	}
	
	private String getCompletionStatus(int exitStatus) {
		
		System.out.println("error no: "+exitStatus);
		
		if(exitStatus==0)
		{
			System.out.println("completed");
			return "completed";
		}
		else if(exitStatus==15 || exitStatus==9)
		{
			System.out.println("killed");
			return "killed";
		}
		else
		{
			System.out.println("failed");
			return "failed";
		}
	}
	
	//method to scan a file and check if it contains the string "satisfiable"
	private static String checkTimeout(String log, int exitStatus)
	{
		try
		{
			String content = new Scanner(new File(log)).useDelimiter("\\Z").next();
			
			if(content.toLowerCase().contains("satisfiable"))
			{
				return "completed";
			}
			else
			{
				return "timeout";
			}
		}
		catch (IOException ioe)
		{
			ioe.printStackTrace();
			return "completed";
		}		
	}
	
	//method to run the postprocessor
	private void runPreProcessor()
	{
		System.out.println("PreProcessing job "+jobID);
		
		//setup command to run preprocessor
		commands.clear();
		commands.add("/bin/bash");
		commands.add("-c");
		commands.add("ulimit -t "+timeout+"; "+PROGRAMS_PATH+preProcessor+" "+PROBLEMS_PATH+inputFile+" "+LOG_PATH+logLocation+".preprocessed");	
		String[] cmd = commands.toArray(new String[0]);
		
		try 
		{
			//execute preprocessor
			p = Runtime.getRuntime().exec(cmd);
			
			//wait for preprocessor to finish
			p.waitFor();
			int exitStatus = p.exitValue();
			
			//check exit status of preprocessor
			status = getCompletionStatus(exitStatus);
		}
		catch (Exception e) 
		{
			status = "failed";
			e.printStackTrace();
			
			//write error to log file
			File file = new File(LOG_PATH+logLocation+".log");
			try 
			{
				BufferedWriter out = new BufferedWriter(new FileWriter(LOG_PATH+logLocation+".log"));
				out.write("failed to run preprocessor: "+preProcessor);
				out.close();
			} 
			catch (IOException ioe) {}
		}
		p.destroy();
	}
	
	private void runProgram()
	{
		System.out.println("Proving job "+jobID);
		
		//add the config file path to parameters if a config file has been used
		if(!parameters.equals(""))
		{
			parameters = parameters.replace("-c ", "-c "+CONF_PATH);
		}
		
		commands.clear();
		commands.add("/bin/bash");
		commands.add("-c");
		
		//check if preprocessed file is required
		if(preProcessor.equals("none"))
		{
			//run problem file directly as it did not require preprocessing
			commands.add("ulimit -t "+timeout+"; "+PROGRAMS_PATH+program+" "+parameters+" "+PROBLEMS_PATH+inputFile+" &> "+LOG_PATH+logLocation+".txt");
		}
		else
		{
			//run pre-processed file
			commands.add("ulimit -t "+timeout+"; "+PROGRAMS_PATH+program+" "+parameters+" "+LOG_PATH+logLocation+".preprocessed &> "+LOG_PATH+logLocation+".txt");
		}
		String[] cmd = commands.toArray(new String[0]);		
		
		try 
		{		
			//get the current date/time
			Date tempStartTime = new Date();
			
			//execute program
			p = Runtime.getRuntime().exec(cmd);	
			
			//wait for program to finish
			p.waitFor();
			int exitStatus = p.exitValue();	
			
			//get current date/time
			Date tempFinishTime = new Date();
			
			//check to see if timeout was possible
			if((tempFinishTime.getTime()-tempStartTime.getTime())/1000 >= timeout)
			{
				status = checkTimeout(LOG_PATH+logLocation+".txt", exitStatus);
			}
			else
			{
				status = getCompletionStatus(exitStatus);	
			}
			
			//delete the preprocessed file
			File file = new File("../logs/"+logLocation+".preprocessed");
			file.delete();
		}
		catch (Exception e) 
		{
			status = "killed";
			e.printStackTrace();
		}
		p.destroy();
	}
	
	private void runPostProcessor()
	{
		System.out.println("Post-processing job "+jobID+" with Post-processor: "+postProcessor);
		commands.clear();
		commands.add("/bin/bash");
		commands.add("-c");
		commands.add("ulimit -t "+timeout+";cd "+PROGRAMS_PATH+"; "+postProcessor+" "+LOG_PATH+logLocation+".txt &> "+LOG_PATH+logLocation+".log");
		String[] cmd = commands.toArray(new String[0]);
		
		try 
		{
			p = Runtime.getRuntime().exec(cmd);
			p.waitFor();
			int exitStatus = p.exitValue();
			File file = new File(LOG_PATH+logLocation+".txt");
			file.delete();
		}
		catch (Exception e) 
		{
			e.printStackTrace();
		}
		p.destroy();
	}
	
	
	public void run() 
	{
		boolean finished = false;
		try 
		{
			//setup inputstream and outputstream
			clientIn = new BufferedReader(
								new InputStreamReader(client.getInputStream()));
			clientOut = new PrintWriter(
									new OutputStreamWriter(client.getOutputStream()));
			
			//wait for server message
			while((fromServer = clientIn.readLine()) != null)
			{
				System.out.println("from server is "+fromServer);
				serverMessage = fromServer.split("@");
				operation = serverMessage[0];
				
				//memory and load check requested
				if(operation.equals("1"))
				{
					double load = checkLoad();
					int memory = checkMemory();
					String loadAndMemory = load+"@"+memory;	
					clientOut.println(loadAndMemory);
					clientOut.flush();
				}
				
				//job requested
				else if(operation.equals("0"))
				{
					status = "completed";
					logName = "";			
					userID = Integer.parseInt(serverMessage[1]);
					taskID = Integer.parseInt(serverMessage[2]);
					jobID = Integer.parseInt(serverMessage[3]);
					inputFile = serverMessage[4];
					program = serverMessage[5];
					preProcessor = serverMessage[6];
					parameters = serverMessage[7];
					postProcessor = serverMessage[8];
					timeout = Integer.parseInt(serverMessage[9]);
					logLocation = userID+"/"+taskID+"/"+jobID;
					
					System.out.println("Recieved "+inputFile+" "+program+" "+preProcessor+" "+parameters+" "+postProcessor+" "+timeout);
					startTime = getCurrentTimeStamp();	
					
					//run preprocessor
					if(!preProcessor.equals("none"))
					{
						runPreProcessor();
					}
			
					//run program
					if(status.equals("completed"))
					{
						runProgram();
					}
					
					//run postprocessor
					if(!postProcessor.equals("none"))
					{
						runPostProcessor();
					}
					
					finishTime = getCurrentTimeStamp();
					
					//send results
					clientOut.println(status+"@"+startTime+"@"+finishTime+"@/"+logLocation);
					clientOut.flush();
				}
				
				//server finished task
				else if(operation.equals("2"))
				{
					try 
					{
						System.err.println("server closed connection");
						clientIn.close();
						clientOut.close();
						client.close();
					} 
					catch (IOException e) 
					{
						e.printStackTrace();
					}
				}
			}
		} 
		catch (IOException e) 
		{
			System.err.println("error: server closed connection");
			p.destroy();
		}
		
		System.out.println("FINISHED");
	}
}
