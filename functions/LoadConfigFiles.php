<?php 
	$program=$_GET['program'];
	chdir('../bin');
	
	
	$files = glob("*".$program.".conf");
	echo json_encode($files);
?>