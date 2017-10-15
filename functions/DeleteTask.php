<?php
include('../dbinfo.php');
session_start();
$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
						  or die("Cannot connect to DB" . mysqli_error($mysql_conn));

$taskid = mysqli_real_escape_string($mysql_conn, $_POST['taskid']);

//delete Task with given taskid
$query = mysqli_query($mysql_conn, "DELETE FROM Tasks WHERE task_id='".$taskid."'");


$userid = $_SESSION['userid'];

//get location of tasks folder
$dirname = "../logs/".$userid."/".$taskid;

//delete the log files within the tasks folder
$dir = array_map('unlink', glob("$dirname/*.*"));
closedir($dir);
//delete the tasks folder itself
rmdir($dirname);
	
echo "Deleted Task ".$taskid;
?>