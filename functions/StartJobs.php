<?php
include('../dbinfo.php');
session_start();
$jobs = json_decode($_POST["jobs"]);
$program = $_POST["program"];
$preprocessor = $_POST["preprocessor"];
$parameters = $_POST["parameters"];


$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
						  or die("Cannot connect to DB" . mysqli_error($mysql_conn));

$task_id = $_SESSION['task_id'];
ignore_user_abort(TRUE);
set_time_limit(600);


for ($i = 0; $i < count($program); $i++) {
	foreach ($jobs as $key => $value) {
		$query = mysqli_query($mysql_conn, "INSERT INTO Jobs (task_id, program, input_file, job_parameters, pre_processor)
		VALUES('$task_id', '$program[$i]', '$value', '$parameters[$i]', '$preprocessor[$i]')");
	}
}
echo "completed";
mysqli_close($mysql_conn);
?>