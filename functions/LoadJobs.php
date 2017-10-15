<?php
include('../dbinfo.php');
session_start();

$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
						  or die("Cannot connect to DB" . mysqli_error($mysql_conn));

$id = $_SESSION['task_id'];
$temp_id = $_SESSION['userid'];

//check to see if the task was created by the current user
$check = mysqli_query($mysql_conn, "SELECT user_id FROM Tasks where task_id='$id' LIMIT 1");
$temp_row =  mysqli_fetch_assoc($check);
if($temp_row[user_id] == $temp_id){
	
	//get the job data
	$query = mysqli_query($mysql_conn, "SELECT j.job_id, prog.program_name, j.input_file, j.start_time, j.end_time, j.job_parameters, j.job_status, c.client_name, j.log_name, pre.pre_processor_name 
							FROM Jobs AS j
							INNER JOIN Programs AS prog ON j.program = prog.program_id
							INNER JOIN PreProcessors AS pre ON j.pre_processor = pre.pre_processor_id
							LEFT JOIN Clients AS c ON j.client_id = c.client_id
							WHERE task_id =  '$id'");

	//load query results into array
	$jobs = array();
	while(($row =  mysqli_fetch_assoc($query))) {
		$jobs[] = $row;
	}

	echo json_encode($jobs);
}
mysqli_close($mysql_conn);

?>