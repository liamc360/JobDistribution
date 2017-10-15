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
	<script src="js/tasks.js"></script>
	<script src="js/notification.js"></script>
</head>


<body>


<div class="header-area">
	<?php include_once 'navigation.php'; ?>
</div>

<div class="wrapper">

	<!---------------------- Modals ---------------------->
	<div id="tasksModal" class="modal">
	  <div class="modal-content">
			<a class="close">&times;</a>
			<p> All of the tasks you have created will be displayed in the table. </p>
			<p> You can sort the table by clicking on one of the table headers. </p>
			<p> To view the jobs for the task, click on the <span style="color: #3F51B5;" >"View"</span> hyperlink for the task. </p>		
			<p> <span style="color: #0277BD;" >To cancel a tasks execution you can click on the <i class="fa fa-times" ></i> button.</span> </p>
			<p> <span style="color: #0277BD;" >To delete a task and all of its log files you can click on the <i class="fa fa-trash" ></i> button.</span> </p>
			<p> <span style="color: #F44336;" >Cancelled tasks will have the jobs displayed in red.</span> </p>
	  </div>
	</div>

	<!---------------------- viewing tasks ---------------------->
	<div class="card-wide mdl-card mdl-shadow--2dp" id = "tableCard">
		
		<!-- title -->
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Tasks</h2>
			<button id="tasksModalBtn" class="mdl-button mdl-js-button mdl-button--icon show-dialog"><i class="fa fa-info-circle" ></i>
		</div>
		
		<!-- options -->
		<div id="taskOptionsArea">
			<label class="mdl-radio mdl-js-radio" for="allTasksOption">
				<input type="radio" id="allTasksOption" name="taskFilters" class="mdl-radio__button" checked>
				<span class="mdl-radio__label">All Tasks</span>
			 </label>
			 <label class="mdl-radio mdl-js-radio" for="completedTasksOption">
				<input type="radio" id="completedTasksOption" name="taskFilters" class="mdl-radio__button" >
				<span class="mdl-radio__label">Completed Tasks</span>
			 </label>	 
			 <label class="mdl-radio mdl-js-radio" for="incompleteTasksOption">
				<input type="radio" id="incompleteTasksOption" name="taskFilters" class="mdl-radio__button" >
				<span class="mdl-radio__label">Incomplete Tasks</span>
			 </label>
		</div>
		
		<!-- tasks table -->
		<div class="mdl-card mdl-shadow--2dp" id = "tasksTableArea">
			<table id="tasksTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th class="mdl-data-table__cell--non-numeric">Task ID</th>
						<th class="mdl-data-table__cell--non-numeric">Task Name</th>
						<th class="mdl-data-table__cell--non-numeric">Start Time</th>
						<th class="mdl-data-table__cell--non-numeric">Finish Time</th>
						<th class="mdl-data-table__cell--non-numeric">Load</th>
						<th class="mdl-data-table__cell--non-numeric">Memory (Mb)</th>
						<th class="mdl-data-table__cell--non-numeric">Timeout (s)</th>
						<th class="mdl-data-table__cell--non-numeric">Progress</th>
						<th class="mdl-data-table__cell--non-numeric no-sort">Jobs</th>
						<th class="mdl-data-table__cell--non-numeric no-sort">View Jobs</th>
						<th class="mdl-data-table__cell--non-numeric no-sort">Actions</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- snackbar for notifications -->
<div id="toastBar" class="mdl-js-snackbar mdl-snackbar">
  <div class="mdl-snackbar__text"></div>
  <button class="mdl-snackbar__action" type="button"></button>
</div>

</body>
</html>