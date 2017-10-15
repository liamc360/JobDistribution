import java.io.*;
import java.net.*;
import java.util.concurrent.*;


public class ClientStart {
	
	private static ExecutorService workers;
	private static ServerSocket socket;
	private static int PORT_NUM = 8080;
	
	public static void main(String[] args) {
		
		Socket incoming = null;
		
		try
		{
			socket = new ServerSocket(PORT_NUM);
			
			//setup dynamic thread pool for connections
			workers = Executors.newCachedThreadPool();
			System.out.println("Waiting for connections\n");
			
			//wait for connections from distribution server and create new client threads
			for (;;) 
			{			
				incoming = socket.accept();
				workers.execute(new ClientSession(incoming));
			}
	
		}
		catch (IOException ioe) 
		{
			System.err.println("socket already in use");
		}
		
	}
}