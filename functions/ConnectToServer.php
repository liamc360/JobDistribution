<?php

	$host = file_get_contents('../ip.txt', FILE_USE_INCLUDE_PATH);
    $port = "8191";
    $timeout = 4;  //timeout in seconds

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)
      or die("Unable to create socket\n");

    socket_set_nonblock($socket)
      or die("Unable to set nonblock on socket\n");

    $time = time();
    while (!@socket_connect($socket, $host, $port))
    {
      $err = socket_last_error($socket);
      if ($err == 115 || $err == 114)
      {
        if ((time() - $time) >= $timeout)
        {
          socket_close($socket);
          die("Connection timed out.\n");
        }
        sleep(1);
        continue;
      }
      die(socket_strerror($err) . "\n");
    }
	
	if ($socket)
	{
		$message = "2";
		socket_write($socket, $message, strlen($message));
		echo "Server UP";
		socket_close($socket);
	}
	else
	{
		echo "Server DOWN";
	}
	
	
?>