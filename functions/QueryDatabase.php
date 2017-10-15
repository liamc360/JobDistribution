<?php
include('../dbinfo.php');
session_start();

$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
						  or die("Cannot connect to DB" . mysqli_error($mysql_conn));

$program = mysqli_real_escape_string($mysql_conn, $_GET['program']);
$preProcessor = mysqli_real_escape_string($mysql_conn, $_GET['preProcessor']);
$server = mysqli_real_escape_string($mysql_conn, $_GET['server']);
$jobStatus = mysqli_real_escape_string($mysql_conn, $_GET['jobStatus']);
$taskID = mysqli_real_escape_string($mysql_conn, $_GET['taskID']);
$problemFile = mysqli_real_escape_string($mysql_conn, $_GET['problemFile']);

$userid = $_SESSION['userid'];

$conditions = ""; //conditions within where clause
$andString = ""; //holds " AND " if one or more conditions set
$conditionsExist = " AND "; //used to combine two where conditions if needed

//check if conditions have been set and form condition string
if($program !== "none")
{
	$conditions = $conditions.$andString." j.program = '".$program."' ";
	$andString = " AND ";
}
if($preProcessor !== "none")
{
	$conditions = $conditions.$andString." j.pre_processor = '".$preProcessor."' ";
	$andString = " AND ";
}
if($server !== "none")
{
	$conditions = $conditions.$andString." j.client_id = '".$server."' ";
	$andString = " AND ";
}
if($jobStatus !== "none")
{
	$conditions = $conditions.$andString." j.job_status = '".$jobStatus."' ";
	$andString = " AND ";
}
if($taskID !== "")
{
	$conditions = $conditions.$andString." j.task_id = '".$taskID."' ";
	$andString = " AND ";
}
if($problemFile !== "")
{
	$conditions = $conditions.$andString." j.input_file like '%".$problemFile."%' ";
	$andString = " AND ";
}

//no conditions specified so " AND " is not required to connect the conditions and userid
if($conditions == '')
{
	$conditionsExist = '';
}


$query = mysqli_query($mysql_conn, "SELECT j.job_id, j.task_id, tasks.task_name, prog.program_name, j.input_file, j.start_time, j.end_time, j.job_parameters, j.job_status, c.client_name, j.log_name, pre.pre_processor_name 
							FROM Jobs AS j
							INNER JOIN Programs AS prog ON j.program = prog.program_id
							INNER JOIN PreProcessors AS pre ON j.pre_processor = pre.pre_processor_id
							INNER JOIN Tasks As tasks ON j.task_id = tasks.task_id
							LEFT JOIN Clients AS c ON j.client_id = c.client_id
							WHERE ".$conditions." ".$conditionsExist." tasks.user_id = ".$userid." LIMIT 10000");

//load query results into array
$jobs = array();
while(($row =  mysqli_fetch_assoc($query))) {
	$jobs[] = $row;
}
mysqli_close($mysql_conn);

echo json_encode($jobs);
?>