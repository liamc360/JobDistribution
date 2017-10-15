<?php
session_start();
if(!isset($_SESSION['userid'])){
	die("You must be logged in!");
}
include('../dbinfo.php');

//get data
$name = $_POST['name'];
$memory = $_POST['memory'];
$load = $_POST['load'];
$timeout = $_POST['timeout'];
$num_jobs = $_POST['num_jobs'];
$jobs = json_decode($_POST["jobs"]);
$program = $_POST["program"];
$preprocessor = $_POST["preprocessor"];
$parameters = $_POST["parameters"];
$servers = $_POST["selected_servers"];

//connect to database
$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
						  or die("Cannot connect to DB" . mysqli_error($mysql_conn));
$date = date('Y-m-d H:i:s');
$user_id = $_SESSION['userid'];

////////////////////////////// CREATE TASK //////////////////////////////
//add task to database and get the ID of the added task
$query = mysqli_query($mysql_conn, "INSERT INTO Tasks (user_id, task_name, start_time, max_load, max_memory, timeout, total_jobs)
VALUES('$user_id', '$name','$date','$load','$memory','$timeout','$num_jobs')");
$task_id = mysqli_insert_id($mysql_conn);


////////////////////////////// CREATE JOBS //////////////////////////////
ignore_user_abort(TRUE);
set_time_limit(600);

//loop number of program configurations
for ($i = 0; $i < count($program); $i++) {
	//loop problem files and insert job into Jobs table
	foreach ($jobs as $key => $value) {
		$query = mysqli_query($mysql_conn, "INSERT INTO Jobs (task_id, program, input_file, job_parameters, pre_processor)
		VALUES('$task_id', '$program[$i]', '$value', '$parameters[$i]', '$preprocessor[$i]')");
	}
}
//close database connection
mysqli_close($mysql_conn);


////////////////////////////// CREATE DIRECTORY //////////////////////////////
$path = '../logs/'.$user_id.'/'.$task_id;
mkdir($path, 0711, true);



////////////////////////////// CONNECT TO SERVER //////////////////////////////
//load ip address of server from file
$host = file_get_contents('../ip.txt', FILE_USE_INCLUDE_PATH);
$port = "8191";

//timeout in seconds
$timeout = 4; 

//message to send to server
$task_string = "0@".$task_id."@".$user_id."@".count($servers);

//concatenate each client to the string
for ($i = 0; $i < count($servers); $i++) {
	$task_string = $task_string."@".$servers[$i];
}

//setup socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_nonblock($socket);

//get current time
$time = time();

//attempt to connect to socket
while (!@socket_connect($socket, $host, $port))
{
	$err = socket_last_error($socket);
	if ($err == 115 || $err == 114)
	{
		//check if timeout is reached
		if ((time() - $time) >= $timeout)
		{
			socket_close($socket);
		}
		sleep(1);
		continue;
	}
	//could not connect, add task string to recovery file instead
	addToRecoveryFile($task_string);
}

//send task string to server and close socket
socket_write($socket, $task_string, strlen($task_string));
socket_close($socket);

echo "Started Task ".$task_id;

////////////////////////////// ADD TASK STRING TO RECOVERY FILE //////////////////////////////
//function to add task string to the end of recovery.txt
function addToRecoveryFile($task)
{
	$file = '../recovery.txt';
	$myfile = file_put_contents($file, $task ."\r\n" , FILE_APPEND | LOCK_EX);
	echo "Server Offline, Task will start when server is started!";
	die();
}

?>