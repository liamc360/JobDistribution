<?php
include('../dbinfo.php');
session_start();

$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
						  or die("Cannot connect to DB" . mysqli_error($mysql_conn));

//select all of the tasks with the users id
$query = mysqli_query($mysql_conn, "SELECT task_id, task_name, start_time, finish_time, max_load, max_memory, timeout, task_finished, total_jobs, jobs_completed FROM Tasks WHERE user_id= '".$_SESSION['userid']."'");

//load the tasks into array
$tasks = array();
while(($row =  mysqli_fetch_assoc($query))) {
    $tasks[] = $row;
}

echo json_encode($tasks);


?>