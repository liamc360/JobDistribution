<?php
	//connect to DB
	include('../dbinfo.php');
	$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
							  or die("Error connecting to DB " . mysqli_error($mysql_conn));

	$tableName = mysqli_real_escape_string($mysql_conn, $_POST['tableName']);
	
	//select data from table
	$query = mysqli_query( $mysql_conn, "SELECT * FROM `".$tableName."`");

	$tableData = array();
	
	//populate tableData array with query result
	while(($row =  mysqli_fetch_assoc($query))) {
		$tableData[] = $row;
	}

	echo json_encode($tableData);
?>