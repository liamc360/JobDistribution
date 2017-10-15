<?php
	include('functions/Authenticate.php');
?>

<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<title>Distribute tasks to remote machines</title>
	<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>-->
	<!--<link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">-->
	<!--<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>-->
	<!--<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>-->
	<!--<link rel="icon" href=""/>-->
	
	
	<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>

	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">


	<link rel="stylesheet" href="css/material.min.css"/>
	<link rel="stylesheet" href="css/mystyle.css"/> 
<!--	<link rel="stylesheet" href="css/combined.css"/>  -->
	
	<script src="js/jquery-3.1.1.min.js"></script>
	<script src="js/material.min.js"></script>
	<script src="js/pattern.js"></script>
	<script src="js/distribution.js"></script>
	<script src="js/notification.js"></script>
	
	
	<script src="js/material.min.js"></script>
	<!-- auto complete -->
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	
</head>
<body>

<div class="header-area">
	<?php include_once 'navigation.php'; ?>
</div>

<div class = "wrapper">

	
	<!---------------------- Modals ---------------------->
	<div id="problemsModal" class="modal">
	  <div class="modal-content">
		<a class="close">&times;</a>
			<p> To select problem files you must first select the directory containing the files by using the dropdown box.<br><span style="color: #4CAF50;padding-left: 10% " >Note: All folders will search every directory</span></p>
			<p> Next apply a pattern. Input the pattern into the field and click 'Apply' to retrieve all of the files<br><span style="color: #4CAF50;padding-left: 10% " >Example: <i>*.txt'</i> retrieves all .txt files and <i>*</i> retrieves all files</span></p>
			<p> When a pattern match is found, files will be listed in the "Files Excluded" list.<br>
				You can select files from the lists by clicking (or shift clicking to select multiple).<br><br>
				Use the four control buttons to move files between lists. The Files included list contains the files which will be included for processing.</p>
	  </div>
	</div>
	<div id="programsModal" class="modal">
	  <div class="modal-content">
		<a class="close">&times;</a>
			<p> This section allows you to specify programs, pre-processors and parameters to be used in the execution of the problem files. </p>
			<p> You must first select a program before selecting a pre-processor. </p>
			<p> Leave the parameters blank to use the default parameters. </p>
			<p> Multiple configurations can be added. For example with 10 problem files using 3 program/pre-processor configurations will mean that each problem file is executed 3 times. </p>
	  </div>
	</div>
	<div id="optionsModal" class="modal">
	  <div class="modal-content">
		<a class="close">&times;</a>
			<p> To start a task you must first specify the following: </p>
			<ol>
				<li> <b>Task Name:</b> The name of a task e.g. 3CNF2-KSP <span style="color: #F44336;" ></span></li>
				<li> <b>Memory Requirement:</b> The free memory required on the client until the problem file should be solved. <span style="color: #F44336;" >Maximum 8000</span></li>
				<li> <b>Load Requirement:</b> The maximum load (over the past minute) on the client before it should be allocated a job. <span style="color: #F44336;" >Minimum 1, Maximum 8. *Recommended 4.</span></li>
				<li> <b>Timeout:</b> The maximum time each problem file can be executed for before it is terminated. <span style="color: #F44336;" >Minimum 0, Maximum 72000</span></li>
			</ol>
			<br>
			<p> The start button will create a new task which will contain jobs for all of the problem files using each program/pre-processor combination.</p>
			<p> You can view the progress of the task you just created on the View Tasks page. </p>
	  </div>
	</div>
	
	<!---------------------- selecting problem files ---------------------->
	<div class="card-wide mdl-card mdl-shadow--2dp" id = "selectFilesCard">
		
		<!-- title -->
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary ">Select Problem Files</h2>
			<button id="problemsModalBtn" class="mdl-button mdl-js-button mdl-button--icon show-dialog"><i class="fa fa-info-circle" ></i>
		</div>
		
		<!-- content -->
		<div class="mdl-card__actions" id="selectFilesArea">
			
			<div>
				<!-- selecting directory -->
				<div class="patternDiv">
					<b>Directory</b>
					<select id="directorySelect">
						<option value="">ALL FOLDERS</option>
						<?php 
							include('functions/LoadDirectories.php');
						?>
					</select>
				</div>	

				
				<!-- applying pattern -->
				<div class="patternDiv">
				  <b>Pattern</b>
				  <input type="text" name="patternField" id = "patternField"> </input>
				</div>  
				<div class="applyPatternDiv">
					<br>
					<button id="applyPatternBtn" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="applyPattern()">
						<i class="fa fa-search "></i> 
						Apply	
					</button>
					<span id="noPatternFound" style="color: #F44336; font-size:18px">No matches found</span>
					<br>
				</div>
			</div>
			
			<!-- files area -->
			<div id="flexDiv">
				<!-- excluded files -->
				<div id="excludedListDiv" class="left">
					<b id="excludedLabel">Files Excluded (0)</b> 
					<select multiple size="0" class = "selectableList"  id="excludedFilesList"></select>
					<button class="mdl-button mdl-js-button mdl-button--accent" onclick="clearExcludedList()" id="clearBtn">
						Clear
					</button>
				</div>
				
				<!-- file action buttons -->
				<div id="moveFilesDiv">
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="moveRows(excludedFilesList,includedFilesList)">
						>
					</button>
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="moveAllRows(excludedFilesList,includedFilesList)">
						>>
					</button>
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="moveRows(includedFilesList,excludedFilesList)">
						<
					</button>
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="moveAllRows(includedFilesList,excludedFilesList)">
						<<
					</button>
				</div>
				
				<!-- included files -->
				<div id="includedListDiv" class="left">
					<b id="includedLabel">Files Included (0)</b> 
					<select multiple size="0" class = "selectableList" id="includedFilesList"></select>
					<button class="mdl-button mdl-js-button mdl-button--accent" onclick="clearIncludedList()" id="clearBtn">
						Clear
					</button>
				</div> 
			</div>
		</div>
		<div id = "fileWarning"></div>	
	</div>    

	
	
	<!---------------------- selecting programs/preprocessors/parameters ---------------------->
	<div class="card-wide mdl-card mdl-shadow--2dp" id = "programsCard">
		
		<!-- title -->
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Select Programs</h2>
			<button id="programsModalBtn" class="mdl-button mdl-js-button mdl-button--icon show-dialog"><i class="fa fa-info-circle" ></i>
		</div>
		
		<!-- content -->
		<div class="mdl-card__actions" id="programsCardArea">
			
			<!-- setting configuration -->
			<div id="programsTopDiv">
				<div class="left"><b>Program</b><select id="program-select"></select>	</div>
				<div class="left"><b>Pre-Processor</b><select id="preprocessor-select"></select>	</div>
				<div class="left"><b>Parameters</b><input  id="parametersInput" size="35" type="text" name="parametersInput"></div>
				
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="addConfiguration()">
					<i class="fa fa-plus "></i>
					Add
				</button>
				
				<span id="programsError" style="color: #F44336; font-size:18px">Configuration Exists</span>	
			</div>
		
			<!-- configuration table -->
			<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp" id="configurationTable">
				<thead>
					<tr>
						<th class="mdl-data-table__cell--non-numeric">Program</th>
						<th class="mdl-data-table__cell--non-numeric">Pre-Processor</th>
						<th class="mdl-data-table__cell--non-numeric">Parameters</th>
						<th>Remove</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<div id = "programWarning"></div>
	</div>   

	
	
	<!---------------------- selecting clients ---------------------->
	<div class="card-wide mdl-card mdl-shadow--2dp" id = "serversCard">
		
		<!-- title -->
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Select Clients</h2>
		</div>
		
		<!-- server radio buttons -->
		<div id="serversRadioButtonsDiv">
			<label class="mdl-radio mdl-js-radio" for="allServersOption">
				<input type="radio" id="allServersOption" name="serverOptions" class="mdl-radio__button">
				<span class="mdl-radio__label">All</span>
			</label>
			 <label class="mdl-radio mdl-js-radio" for="noServersOption">
				<input type="radio" id="noServersOption" name="serverOptions" class="mdl-radio__button" checked>
				<span class="mdl-radio__label">None</span>
			 </label>	 
		</div>
		
		<!-- content -->
		<div class="mdl-card__actions" id="serversCardArea"></div>
		<div id = "serverWarning"></div>
	</div>


	<!---------------------- selecting options/starting task ---------------------->
	<div class="card-wide mdl-card mdl-shadow--2dp" id = "optionsCard">
		<!-- title -->
		<div class="mdl-card__title ">
			<h2 class="mdl-card__title-text mdl-color-text--primary">Change Options & Start Task</h2>
			<button id="optionsModalBtn" class="mdl-button mdl-js-button mdl-button--icon show-dialog"><i class="fa fa-info-circle" ></i>
		</div>
		
		<!-- content -->
		<div class="mdl-card__supporting-text">
			<div class="mdl-grid">
			
				<!-- option fields -->
				<div class="mdl-cell mdl-cell--12-col">
					<b>Task Name (optional)</b><br>
					<input type="text" class = "settingsField" name="nameField" id = "nameField"> </input> 
					<br><br>
					<b>Memory Requirement (Mb)</b><br>
					<input type="text" class = "settingsField" name="memoryField" id = "memoryField"> </input> <span id="memoryError" style="color: #F44336; font-size:18px">Invalid Memory</span>
					<br><br>
					<b>Maximum Load</b><br>
					<input type="text" class = "settingsField" name="loadField" id = "loadField"> </input> <span id="loadError" style="color: #F44336; font-size:18px">Invalid Load</span>
					<br><br>
					<b>Timeout (s)</b><br>
					<input type="text" class = "settingsField" name="timeoutField" id = "timeoutField"> </input> <span id="timeoutError" style="color: #F44336; font-size:18px">Invalid Timeout</span>
					<br><br>
				</div>

				<!-- start button and loading spinner -->
				<div class="mdl-cell mdl-cell--12-col" id="startTaskArea">
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="validateAndStart()" id="startTaskBtn">
						Start Task
					</button>
					<div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active" id="processingTask"></div>
				</div>
			</div>	
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