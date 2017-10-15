<?php
	include('../dbinfo.php');
	$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
							  or die("Login error" . mysqli_error($mysql_conn));

	
	$query = mysqli_query( $mysql_conn, "SELECT * FROM Clients");

	//load all clients into array
	$clients = array();
	while(($row =  mysqli_fetch_assoc($query))) {
		$clients[] = $row;
	}

	$checkedclients = array();
	
	//loop through clients array
	foreach($clients as $item)
	{
		//get the IP address of client and run scripts to get the load on the pc
		$IP = $item['client_ip']; 
		exec('cd ../;./check-clients.sh '.$IP.'', $out, $status);
		
		//client is online
		if ($out != null) {	
			$checkedclients[] = array('status' => 'online', 'client_name' => $item['client_name'], 'client_ip' => $item['client_ip'], 'client_load' => $out);
		}
		
		//client is offline
		else {
			$checkedclients[] = array('status' => 'offline', 'client_name' => $item['client_name'], 'client_ip' => $item['client_ip'], 'client_load' => $out);
		}
		
		//clear out array
		unset($out);
	}
	echo json_encode($checkedclients);
?>
   