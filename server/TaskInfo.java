class TaskInfo{
	
	protected int taskID;
	protected double taskLoad;
	protected int taskMemory;
	protected int timeout;
	
	public TaskInfo(int tid, double load, int memory, int time)
	{
		taskID = tid;
		taskLoad = load;
		taskMemory = memory;
		timeout = time;
	}
}