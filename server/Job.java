
public class Job {
	
	private int jobID;
	private String inputFile;
	private String program;
	private String preProcessor;
	private String parameters = "none";
	private String postProcessor;
	private String jobStatus = "waiting";
	private int timesKilled = 0;
	
	public Job(int jid, String file, String prog, String pre, String param, String post, String status)
	{
		jobID = jid;
		inputFile = file;
		program = prog;
		preProcessor = pre;
		parameters = param;
		postProcessor = post;
		jobStatus = status;		
	}
	
	protected int getJobID()
	{
		return jobID;
	}
	protected String getInputFile()
	{
		return inputFile;
	}
	protected String getProgram()
	{
		return program;
	}
	protected String getPreProcessor()
	{
		return preProcessor;
	}
	protected String getParameters()
	{
		return parameters;
	}
	protected String getPostProcessor()
	{
		return postProcessor;
	}
	protected String getStatus()
	{
		return jobStatus;
	}
	protected int getTimesKilled()
	{
		return timesKilled;
	}
	protected void updateTimesKilled()
	{
		timesKilled++;
	}
}
