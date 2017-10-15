<?php
//get IP address of distribution server
$host = file_get_contents('../ip.txt', FILE_USE_INCLUDE_PATH);

//distribution server port and timeout in seconds
$port = "8191";
$timeout = 4; 

//create socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_nonblock($socket);

$time = time();
while (!@socket_connect($socket, $host, $port))
{
	$err = socket_last_error($socket);
	if ($err == 115 || $err == 114)
	{
		if ((time() - $time) >= $timeout)
		{
			socket_close($socket);
		}
		sleep(1);
		continue;
	}
  
	//cannot connect to distribution server
	echo "Server is down!";
	die();
}
?>