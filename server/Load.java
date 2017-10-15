import java.text.SimpleDateFormat;
import java.util.*;
import java.sql.Timestamp;

public class Load {
	
	//holds the start and finish times of the completed jobs in the last 60 seconds
	protected volatile ArrayList<times> finishedJobs = new ArrayList<times>();
	
	//holds the start time of a job in progress by the current worker
	protected volatile long[] midProgressJobs;
	
	//number of threads for the client
	private int numWorkers = 0;

	public Load(int nW)
	{
		numWorkers = nW;
		midProgressJobs = new long[numWorkers];
	}
	
	//method to calculate the offset for a client PCs load
	private double calcLoad()
	{
		Timestamp timestamp = new Timestamp(System.currentTimeMillis());
		
		//get the current time in milliseconds
		long current =  timestamp.getTime();
		
		//total number of milliseconds working over past 60000 milliseconds
		long totalMilliseconds = 0;
		
		//the load offset
		double loadOffset = 0.0;
		
		//loop through this clients finished jobs
		for (Iterator<times> iterator = finishedJobs.iterator(); iterator.hasNext();) 
		{	
			//get the next finished job
			times finishedJob = iterator.next();
			
			//subtract 60000 milliseconds from the current time
			long timeCutoff = current - 60000;
			
			//check if the last job finished more than 60 seconds ago
			if(finishedJob.finish<=timeCutoff)
			{
				//remove job as it no longer has a load impact on the client
				iterator.remove();
			}
			else if(finishedJob.start<=timeCutoff) //check if job started more than 60000 milliseconds ago
			{
				//add the jobs working time over the past 60000 milliseconds
				totalMilliseconds = totalMilliseconds + (finishedJob.finish-timeCutoff);	
			}
			else //job started and finished within past 60000 milliseconds
			{
				//add jobs working time over the past 60000 milliseconds
				totalMilliseconds = totalMilliseconds + (finishedJob.finish-finishedJob.start);
			}
		}
		
		//get the the fraction of time working over the past 60000 milliseconds
		loadOffset = (double)totalMilliseconds/60000;
		loadOffset = loadOffset * -1.0;
		System.out.println("total load for past 60 seconds to subtract = "+loadOffset);
		
		
		
		//what the load is predicted to be from the current workers
		for(int i=0;i<numWorkers;i++)
		{
			//check if current worker is working
			if(midProgressJobs[i]!=0)
			{
				//get the current time in milliseconds
				timestamp = new Timestamp(System.currentTimeMillis());
				current =  timestamp.getTime();
				
				//get the time working on the current job
				long millisecondsDuringJob = current - midProgressJobs[i];
				
				//cap the time at 60000 milliseconds
				if(millisecondsDuringJob>60000)
				{
					millisecondsDuringJob = 60000;
				}
				
				//get the fraction of time working over the past 60000 milliseconds
				double load = (double)millisecondsDuringJob/60000;
				
				//subtract the load from 1 
				double predictedLoadIncrease = 1.0-load;
				System.out.println("predicted load increase = "+predictedLoadIncrease);
				loadOffset = loadOffset + predictedLoadIncrease;
			}
		}
		return loadOffset;
	}
	
	//method to check the memory and load requirements are met
	protected synchronized boolean checkRequirements(int clientMemory, double clientLoad, int maxMemory, double maxLoad, int workerNum)
	{
		if(clientMemory<maxMemory)
		{
			return false;
		}

		double newLoad = 0.0;
		midProgressJobs[workerNum] = 0;
		newLoad = clientLoad+calcLoad();
		System.out.println("NEW LOAD IS "+newLoad);
	
		if(newLoad<maxLoad)
		{
			Timestamp timestamp = new Timestamp(System.currentTimeMillis());
			long current =  timestamp.getTime();
			midProgressJobs[workerNum] = current;
			return true;
		}
		else
		{
			return false;
		}
	}
}

//class to hold a start and finish time
class times{
	
	protected long start = 0;
	protected long finish = 0;
	
	public times(long s, long f)
	{
		start = s;
		finish = f;
	}
}
	
	
	
	
	
	
	
	
	
	/*public Load()
	{

	}
	
	public void setStartTime()
	{
		Timestamp timestamp = new Timestamp(System.currentTimeMillis());
		startTime = timestamp.getTime();
		System.out.println("load start time = "+startTime);
	}
	public void setFinishTime()
	{
		Timestamp timestamp = new Timestamp(System.currentTimeMillis());
		finishTime = timestamp.getTime();
	}
	
	public double getLoadAverage()
	{
		if(startTime == 0)
		{
			return 0.0;
		}
		
		
		long secondsDuringJob = finishTime-startTime; //30
		if(secondsDuringJob>60000)
		{
			secondsDuringJob = 60000;
		}
		
		double load = (double)secondsDuringJob/60000;
		
		
		
		System.out.println("secondsDuringJob = "+secondsDuringJob);
		System.out.println("double load = "+load);
		
		Timestamp timestamp = new Timestamp(System.currentTimeMillis());
		long current = timestamp.getTime();
		long secondsSinceJob = current-finishTime; //10seconds ago
		System.out.println("secondsSinceJob = "+secondsSinceJob);
		
		double newLoad = (((double)secondsSinceJob-60000)/60000) * load;
		
		return newLoad;
	}*/

	
	
/*
import java.text.SimpleDateFormat;
import java.util.*;
import java.sql.Timestamp;

public class Load {
	
	public long startTime = 0;
	public long finishTime = 0;	
	public ArrayList<times> finishedJobs = new ArrayList<times>();
	public static int numWorking = 0;

	public static long[] midProgressJobs = new long[4];
	
	public double calcLoad()
	{
		Timestamp timestamp = new Timestamp(System.currentTimeMillis());
		long current =  timestamp.getTime();
		
		double tempLoad = 0.0;
		for (Iterator<times> iterator = finishedJobs.iterator(); iterator.hasNext();) 
		{	
			times x = iterator.next();
			
			if(current-x.finish>=60000) 
			{
				iterator.remove();
			}
			else
			{		
				long secondsDuringJob = x.finish-x.start;
				
				if(secondsDuringJob>60000)
				{
					secondsDuringJob = 60000;
				}
				double load = (double)secondsDuringJob/60000;
				
				long secondsSinceJob = current-x.finish; 
				//reverse
				double newLoad = ((60000-(double)secondsSinceJob)/60000) * load;
				tempLoad+=newLoad;
			}
		}
		
		for(int i=0;i<4;i++)
		{
			if(midProgressJobs[i]!=0)
			{
				timestamp = new Timestamp(System.currentTimeMillis());
				current =  timestamp.getTime();
				long secondsDuringJob = current - midProgressJobs[i];
				System.out.println("--------------------------------- current time = "+current/1000+" mid progress = "+midProgressJobs[i]/1000+" seconds during job = "+secondsDuringJob/1000);
				if(secondsDuringJob>60000)
				{
					secondsDuringJob = 60000;
				}
				double load = (double)secondsDuringJob/60000;
				double loadMinus = 1.0-load;
				tempLoad+=loadMinus;
			}
		}
		return tempLoad;
	}
}

class times{
	
	long start = 0;
	long finish = 0;
	
	public times(long s, long f)
	{
		start = s;
		finish = f;
	}
}*/