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
	
	
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/dt-1.10.13/b-1.2.4/b-html5-1.2.4/r-2.1.1/datatables.min.css"/>
	<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/dt-1.10.13/b-1.2.4/b-html5-1.2.4/r-2.1.1/datatables.min.js"></script>
	<link rel="stylesheet" href="css/material.min.css"/>
	<link rel="stylesheet" href="css/mystyle.css"/>

	<script src="js/material.min.js"></script>
	<script src="js/jobs.js"></script>
	


	
</head>


<body>

<!-- get id of the task -->
<?php
	session_start();
	$_SESSION['task_id'] = $_GET['id'];
?>

<div class="header-area">
	<?php include_once 'navigation.php'; ?>
</div>
    
<div class="wrapper">

	<!---------------------- Modals ---------------------->
	<div id="jobsModal" class="modal">
		 <div class="modal-content">
			<a class="close">&times;</a>
				<p> The table will classify job Status using the following criteria: </p>
				<ol>
					<li> <span style="color: #4CAF50;" >completed:</span> The problem file was executed without problems</li>
					<li> <span style="color: #FFB300;" >waiting:</span> The problem file is waiting to be solved</li>
					<li> <span style="color: #9C27B0;" >timeout:</span> The program timed out</li>
					<li> <span style="color: #0277BD;" >error:</span> The client pc disconnected whilst solving the problem. This job will retry once the other jobs have finished</li>
					<li> <span style="color: #FF5722;" >working:</span> The job is currently being processed by a client</li>
					<li> <span style="color: #F44336;" >failed:</span> The job encountered an execution error. This could be due to incorrect parameters or missing files. Check the log for more information</li>
					<li> <span style="color: #F44336;" >killed:</span> The job process was killed multiple times</li>
					<li> <span style="color: #F44336;" >cancelled:</span> The job was cancelled by the user</li>		
				</ol>
				<p> When a problem file has stopped executing (due to completing or being halted) the finish time, client pc and log (if available) will be displayed in the table.</p>
				<p> You can <b>export</b> the results to csv by clicking the csv button.</p>
		</div>
	</div>

	<!---------------------- viewing jobs ---------------------->
	<div class="card-wide mdl-card mdl-shadow--2dp" id = "tableCard">
		
		<!-- title -->
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Jobs for Task <?php echo $_GET['id']; ?></h2>
			<button id="jobsModalBtn" class="mdl-button mdl-js-button mdl-button--icon show-dialog"><i class="fa fa-info-circle" ></i>
		</div>
		
		<!-- jobs table -->
		<div class="mdl-card mdl-shadow--2dp" id="jobsTableArea">
			<table id="jobsTable" class=" table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th class="mdl-data-table__cell--non-numeric">Job ID</th>
						<th class="mdl-data-table__cell--non-numeric">Start Time</th>
						<th class="mdl-data-table__cell--non-numeric">Finish Time</th>
						<th class="mdl-data-table__cell--non-numeric">Pre-Processor</th>
						<th class="mdl-data-table__cell--non-numeric">Program</th>
						<th class="mdl-data-table__cell--non-numeric">Input File</th>
						<th class="mdl-data-table__cell--non-numeric">Parameters</th>
						<th class="mdl-data-table__cell--non-numeric">Status</th>
						<th class="mdl-data-table__cell--non-numeric">Client PC</th>
						<th class="mdl-data-table__cell--non-numeric no-sort">Log</th>
					</tr>
				</thead>
				<tbody></tbody>		
			</table>
		</div>
	</div>
</div>
	
</body>
</html>