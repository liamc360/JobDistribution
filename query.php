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
	<script src="js/query.js"></script>
</head>



<body>
<div class="header-area">
	<?php include_once 'navigation.php'; ?>
</div>
    
<div class="wrapper">

	<!---------------------- query database ---------------------->
	<div class="card-wide mdl-card mdl-shadow--2dp" id = "programsCard">
			
		<!-- title -->
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Query Database</h2>
		</div>
		
		<!-- query actions -->
		<div class="mdl-card__actions">
			<div id = "queryInputs">
			
				<div id = "programSelect"class="left"><b>Program</b>
					<select id="program-select">
						<option value="none"></option>
					</select>
				</div>
				
				<div id = "preProcessorSelect"class="left"><b>Pre-Processor</b>
					<select id="preprocessor-select">
						<option value="none"></option>
					</select>
				</div>
				
				<div id = "clientSelect"class="left"><b>Client</b>
					<select id="client-select">
						<option value="none"></option>
					</select>
				</div>
				
				<div id = "statusSelect"class="left"><b>Status</b>
					<select id="status-select">
						<option value="none"></option>
						<option value="cancelled">cancelled</option>
						<option value="completed">completed</option>
						<option value="error">error</option>
						<option value="failed">failed</option>
						<option value="killed">killed</option>
						<option value="timeout">timeout</option>
						<option value="waiting">waiting</option>
						<option value="working">working</option>
					</select>
				</div>
				
				<div class="left"><b>Task ID</b>
					<input type="text" id = "taskIDInput"> </input>
				</div>
				
				<div class="left"><b>Problem File</b>
					<input type="text" id = "problemFileInput"> </input>
				</div>
				
				<br>
				<div class="left"> 
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" id="queryBtn" onclick="loadJobs()">
						Query
					</button>
				</div>
				<br>
				
				<span id="programsError" style="color: #F44336; font-size:18px">
					Invalid Task ID
				</span>
			</div>
			
		</div>
		
		<div class="mdl-card mdl-shadow--2dp" id="jobsTableArea">
			<table id="jobsTable" class=" table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th class="mdl-data-table__cell--non-numeric">Job ID</th>
						<th class="mdl-data-table__cell--non-numeric">Task ID</th>
						<th class="mdl-data-table__cell--non-numeric">Task Name</th>
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