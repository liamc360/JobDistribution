<?php
session_start();
if(!isset($_SESSION['userid'])){
	die("You must be logged in!");
}

include 'OpenSocket.php';
$task_id = $_POST['task_id'];

/*send message with operation "1" (for task cancellations)
  followed by the task id to cancel*/
$message = "1@".$task_id;
socket_write($socket, $message, strlen($message));
socket_close($socket);

echo "Cancelled Task ".$task_id;


?>