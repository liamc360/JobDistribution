import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.text.DateFormat;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.util.Properties;
import java.io.InputStream;
import java.io.FileInputStream;

public class DBConnect{
	
	private static String DB_HOST;
	private static String DB_NAME;
	private static String DB_USERNAME;
	private static String DB_PASSWORD;
	
	private Connection con;
	private Statement st;
	private ResultSet rs;
	
	//connect to the database upon object creation
	public DBConnect()
	{
		getDBInfo();
		
		boolean connected = false;
		while(!connected)
		{
			try
			{
				Class.forName("com.mysql.jdbc.Driver");
				con = DriverManager.getConnection(
								 "jdbc:mysql://"+DB_HOST+"/"+DB_NAME+"?autoReconnect=true", DB_USERNAME, DB_PASSWORD);
						
				System.out.println("Connected to database!");
				connected = true;
			}
			catch(Exception ex) //error connecting. retry in 30 seconds
			{
				System.err.println("Could not connect to database!");
				try 
				{
					Thread.sleep(30000);
				} 
				catch (InterruptedException ie) 
				{
					ie.printStackTrace();
				}
			}
		}
	}
	
	public void checkConnection()
	{
		boolean connected = false;
		while(!connected)
		{
			try
			{
				//check connection is still valid with 5 second timeout
				if(con.isValid(5))
				{
					connected = true;
				}
				else
				{
					con = DriverManager.getConnection(
							 "jdbc:mysql://"+DB_HOST+"/"+DB_NAME+"?autoReconnect=true", DB_USERNAME, DB_PASSWORD);
							
					System.out.println("Connection established!");
					connected = true;
				}
			}
			catch(Exception ex)
			{
				System.out.println("Error checking connection: "+ex);
				try 
				{
					Thread.sleep(10000);
				} 
				catch (InterruptedException ie) 
				{
					ie.printStackTrace();
				}
			}
		}
	}
	
	protected static void getDBInfo()
	{
		Properties prop = new Properties();
		InputStream input = null;

		try 
		{
			input = new FileInputStream("config.properties");

			// load a properties file
			prop.load(input);
	
			DB_HOST = prop.getProperty("host");
			DB_NAME = prop.getProperty("name");
			DB_USERNAME = prop.getProperty("username");
			DB_PASSWORD = prop.getProperty("pass");

		}
		catch (IOException ex) 
		{
			ex.printStackTrace();
		} 
		finally 
		{
			if (input != null) 
			{
				try
				{
					input.close();
				}
				catch (IOException e)
				{
					e.printStackTrace();
				}
			}
		}
	}
	
	private String removeLastChar(String s) {
	    if (s == null || s.length() == 0) {
	        return s;
	    }
	    return s.substring(0, s.length()-1);
	}
	
	private String getCurrentTimeStamp() 
	{
	    return new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SSS").format(new Date());
	}
	
	public Client[] getClients(List<Integer> clientIDs) throws Exception
	{
		
		List<Client> clients = new ArrayList<Client>();
		
		st = con.createStatement();
		
		String inString = "(";
		for (Integer s : clientIDs)
		{
			inString += s+",";
		}
		inString = removeLastChar(inString);
		inString += ")";
		
		String query = "select * from Clients WHERE client_id IN"+inString+" LIMIT "+clientIDs.size();
		
		System.out.println("query is "+query);

		rs = st.executeQuery(query);
		System.out.println("Records from DB");
		
		while(rs.next())
		{
			int id = rs.getInt("client_id");
			String ip = rs.getString("client_ip");
			int port = rs.getInt("client_port");
			System.out.println("ip = "+ip+"     "+"port= "+port);
			clients.add(new Client(id,ip,port));
		}
		
		Client[] clientArray = clients.toArray(new Client[clients.size()]);
		
		return clientArray;
	}
	
	public Client[] getClientInfo()
	{
		checkConnection();
		List<Client> clients = new ArrayList<Client>();
		
		try{
			st = con.createStatement();
			String query = "select * from Clients";
			rs = st.executeQuery(query);
			System.out.println("Records from DB");
			
			while(rs.next()){
				int id = rs.getInt("client_id");
				String ip = rs.getString("client_ip");
				int port = rs.getInt("client_port");
				System.out.println("ip = "+ip+"     "+"port= "+port);
				clients.add(new Client(id,ip,port));
			}
		}
		catch(Exception ex){
			System.out.println("Error: "+ex);
		}
		Client[] clientArray = clients.toArray(new Client[clients.size()]);
		
		return clientArray;
	}

	//method to get the remaining jobs for a task (waiting/working/error status jobs)
	public ArrayList<Job> getJobsForTask(int taskID) throws Exception{
		
		checkConnection();
		ArrayList<Job> jobs = new ArrayList<Job>();
		
		//create statement object
		st = con.createStatement();
		
		//query to retrieve job information for task
		String query = "SELECT j.job_id, j.input_file, prog.program_name, pre.pre_processor_name, j.job_parameters, post.post_processor_name, j.job_status"
				+ " FROM Jobs AS j"
				+ " INNER JOIN Programs AS prog ON j.program = prog.program_id"
				+ " INNER JOIN PreProcessors AS pre ON j.pre_processor = pre.pre_processor_id"
				+ " INNER JOIN PostProcessors AS post ON prog.post_processor = post.post_processor_id"
				+ " WHERE j.task_id = '"+taskID+"' AND j.job_status != 'completed' AND j.job_status != 'timeout' AND j.job_status != 'failed' AND j.job_status != 'killed'"
				+ " ORDER BY j.job_id";
		
		//execute query and store results in resultset object
		rs = st.executeQuery(query);
		
		//load result set fields
		while(rs.next())
		{
			int jid = rs.getInt(1);
			String input = rs.getString(2);
			String prog = rs.getString(3);
			String pre = rs.getString(4);
			String param = rs.getString(5);	
			String post = rs.getString(6);
			String status = rs.getString(7);
			
			//add new job to queue
			jobs.add(new Job(jid,input,prog,pre,param,post,status));
		}
		
		return jobs;
	}
	
	/*
		if(param.equals(""))
			{
				param = "none";
			}
	*/
	//System.out.println("loaded a job with id: "+jid);
	
	public TaskInfo getTaskInfo(int taskID) throws Exception
	{
		checkConnection();
		st = con.createStatement();
		String query = "select * from Tasks where task_id = '"+taskID+"' LIMIT 1";
		rs = st.executeQuery(query);
		System.out.println("Records from DB:");
			
		if(!rs.next())
		{
			System.err.println("no task found");
			throw new Exception();
		}
		else
		{
			int id = rs.getInt("task_id");
			double load = rs.getDouble("max_load");
			int memory = rs.getInt("max_memory");
			int timeout = rs.getInt("timeout");
			System.out.println("task_id = "+id+"     "+"max_load= "+load+"        max_memory= "+memory+"          +timeout= "+timeout);
			return new TaskInfo(id,load,memory,timeout);
		}
	}
	
	public void saveCompletedJob(int taskID, int jobID, String startTime, String finishTime, String status, String logName, int clientID)
	{
		checkConnection();
		try{
			st = con.createStatement();
			String updateJobQuery = "UPDATE Jobs SET start_time='"+startTime+"', end_time='"+finishTime+"', job_status='"+status+"', log_name='"+logName+"', client_id='"+clientID+"' WHERE job_id='"+jobID+"' LIMIT 1";
			String updateTaskQuery = "UPDATE Tasks SET jobs_completed=jobs_completed+1 WHERE task_id='"+taskID+"' LIMIT 1";
			st.executeUpdate(updateJobQuery);
			st.executeUpdate(updateTaskQuery);
			System.out.println("Updated completed job "+jobID);
		}
		catch(Exception ex){
			System.out.println("Error: "+ex);
		}
	}
	
	public void saveCompletedTask(int taskID, int code, String finishTime)
	{
		checkConnection();
		try{
			st = con.createStatement();
			String query = "UPDATE Tasks SET task_finished='"+code+"', finish_time='"+finishTime+"' WHERE task_id='"+taskID+"' LIMIT 1";
			int numUpdated = st.executeUpdate(query);
			System.out.println("Task "+taskID+" has completed");
		}
		catch(Exception ex){
			System.out.println("Error: "+ex);
		}
	}
	
	//method to update a jobs start time, status and client
	public void saveWorkingJob(int taskID, int jobID, String status, int clientID)
	{
		checkConnection();
		try
		{
			//create statement object
			st = con.createStatement();
			
			//query to update a single jobs start time, status and client
			String query = "UPDATE Jobs SET start_time='"+getCurrentTimeStamp()+"', job_status='"+status+"', client_id='"+clientID+"' WHERE job_id='"+jobID+"' LIMIT 1";
			
			//execute query and store results in resultset object
			st.executeUpdate(query);
			//System.out.println("Updated working job: "+jobID);
		}
		catch(Exception ex)
		{
			System.out.println("Error updating working job: "+ex);
		}
	}
	
	public void saveCancelledJobs(int taskID)
	{
		checkConnection();
		try{
			st = con.createStatement();
			String query = "UPDATE Jobs SET job_status='cancelled' WHERE (task_id='"+taskID+"') AND (job_status='waiting' OR job_status='working')";
			int numUpdated = st.executeUpdate(query);
			System.out.println("Updated cancelled jobs for task "+taskID);
		}
		catch(Exception ex){
			System.out.println("Error: "+ex);
		}
	}
	
	public void updateErrorJobs(int taskID)
	{
		checkConnection();
		try{
			st = con.createStatement();
			String query = "UPDATE Jobs SET job_status='error', client_id=null, end_time=null WHERE task_id='"+taskID+"' AND job_status='working'";
			int numUpdated = st.executeUpdate(query);
			System.err.println("Updated "+numUpdated+" rows with errors");
		}
		catch(Exception ex){
			System.out.println("Error: "+ex);
		}
	}
	
	public void disconnectFromDB(){
		try {
			con.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
}