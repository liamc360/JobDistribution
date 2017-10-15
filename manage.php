<?php
	include('functions/Authenticate.php');
?>

<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<title>Distribute tasks to remote machines</title>
	
	<script src="js/jquery-3.1.1.min.js"></script>
	
	<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css"/>
	<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/material.min.css"/>
	<link rel="stylesheet" href="css/mystyle.css"/>

	<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
	<link rel="stylesheet" href="css/material.min.css"/>
	<link rel="stylesheet" href="css/mystyle.css"/>
	
	
	<script src="js/material.min.js"></script>
	<script src="js/manage.js"></script>
	<script src="js/notification.js"></script>
</head>


<body>


<div class="header-area">
	<?php include_once 'navigation.php'; ?>
</div>

<div class="wrapper">


	<!---------------------- Modals ---------------------->
	<div id="manageServersModal" class="modal">
	  <div class="modal-content">
		<a class="close">&times;</a>
			 <b> Start Server with IP: </b> Starts the distribution software on the machine provided in the text field. <br><br>
			 <b> Start Server on webserver: </b> Starts the distribution software using the webserver. <br><br>
			 <b> Stop Server: </b> Stops the distribution software on the machine it is currently running on. <br><br>
			 <b> Start Clients: </b> Starts the client software on all of the clients. <br><br>
			 <b> Stop Clients: </b> Stops the client software running on all of the clients. 		
	  </div>
	</div>
	
	<div id="clientsModal" class="modal">
	  <div class="modal-content">
		<a class="close">&times;</a>
			<p><b>Load:</b> From left to right, these numbers show you the average load of the client over the last one minute, 
			the last five minutes, and the last fifteen minutes.</b><br>
			A completely idle client has an average load of 0.0.<br>
			For a client with 4 cores an average load of >4 would mean that the processor was overburdened.<br>
			One job/problem file should produce an average load of 1 during its execution.</p>	
			
			<p>Available/online clients will be displayed in <span style="color: #4CAF50;">green</span>.<br>
			Unavailable/offline clients will be displayed in <span style="color: #F44336;">red</span>.
			
	  </div>
	</div>



	<div class="card-wide mdl-card mdl-shadow--2dp">
		
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Manage Servers</h2>
			<button id="manageServersModalBtn" class="mdl-button mdl-js-button mdl-button--icon show-dialog"><i class="fa fa-info-circle" ></i>
		</div>
  
		<div id="manageCard">
			<div class="selectDiv">
				  <b>IP Address/Machine name</b>
				  <input type="text" name="ipField" id = "ipField"> </input>
			</div>
			<!--<div class="bottomButton">-->
			<br>
			<div id="manageButtonsArea" class="left">
			
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="startDistributionServer()" id="startDistributionServer">
					<i class="fa fa-play  "></i> 
					Start Server (With IP)
				</button>
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="startDistributionServerWeb()" id="startDistributionServerWeb">
					<i class="fa fa-play  "></i> 
					Start Server (On Webserver)
				</button>
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent" onclick="killDistributionServer()" id="killDistributionServer">
					<i class="fa fa-stop  "></i> 
					Stop Server
				</button>
			<br><br>
			<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="startClients()" id="startClients">
					<i class="fa fa-play  "></i> 
					Start Clients
			</button>
			<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent" onclick="killClients()" id="killClients">
				<i class="fa fa-stop  "></i> 
					Stop Clients
			</button>
			</div>
		</div>

  </div>
	<div class="card-wide mdl-card mdl-shadow--2dp" id="onlineClientsCard">
		
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Clients</h2>
			<button id="refreshClientsBtn" class="mdl-button mdl-js-button mdl-button--icon show-dialog" onclick="checkClientStatus()"><i class="fa fa-refresh" ></i>
			<button id="clientsModalBtn" class="mdl-button mdl-js-button mdl-button--icon show-clients-dialog"><i class="fa fa-info-circle" ></i>
		</div>
		<div id="clientsArea">
		<div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active" id="clientsLoadingSpinner"></div>
		
		<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp" id="onlineClientlist">
			<thead>
				<tr>
					<th class="mdl-data-table__cell--non-numeric largeTableFont">Client</th>
					<th class="mdl-data-table__cell--non-numeric largeTableFont">IP</th>
					<th class="mdl-data-table__cell--non-numeric largeTableFont">Load</th>
				</tr>
			<thead>
		</table>
		
		
		
		<!--<ul class="mdl-list success" id="onlineClientlist"></ul>
		<br>
		<ul class="mdl-list failure" id="offlineClientlist"></ul>-->
		
		</div>

	</div>
</div>

</div>

<div id="toastBar" class="mdl-js-snackbar mdl-snackbar">
  <div class="mdl-snackbar__text"></div>
  <button class="mdl-snackbar__action" type="button"></button>
</div>


</body>
</html>