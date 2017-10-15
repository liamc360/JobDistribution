import java.util.*;
public class Task {
	
	//task info
	protected final int taskID;
	protected final double taskLoad;
	protected final int taskMemory;
	protected final int timeout;
	protected final int userID;
	protected final int totalJobs;
	protected final DBConnect connect;	
	protected volatile int numJobsCompleted = 0;
	
	//holds all of the jobs for this task
	private ArrayList<Job> jobQueue;
	
	public Task(int uid, int tid, double load, int memory, int time, ArrayList<Job> jobs, int numJobs, DBConnect con)
	{
		userID = uid;
		taskID = tid;
		taskLoad = load;
		taskMemory = memory;
		timeout = time;
		jobQueue = jobs;
		totalJobs = numJobs;
		connect = con;
	}
	
	//method to get the number of jobs in the job queue
	protected int getQueueSize()
	{
		return jobQueue.size();
	}
	
	//method to get the next job in the jobqueue and remove it
	protected synchronized Job getNextJob() throws IndexOutOfBoundsException
	{
		if(jobQueue.size() == 0)
		{
	         throw new IndexOutOfBoundsException("No jobs left");
		}
		else
		{
			Job nextJob = jobQueue.remove(0);
			return nextJob;
		}
	}
	
	//method to add a job back to the job queue
	protected synchronized void addJobBackToQueue(Job aJob)
	{
		jobQueue.add(aJob);
	}
}
