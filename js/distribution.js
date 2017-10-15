$( document ).ready(function() {

	//load necessary info from database
	loadClients();
	loadPreProcessors();
	loadPrograms();
	setupModals();

	//remove row from programs/configuration table when cross button is clicked
	$("#configurationTable").on('click', '.btn-delete', function () {
		$(this).closest('tr').remove();
	});

	//toggle all clients when all clients radio button is changed
	$('#allServersOption').change( function() {
		$(".server_group").prop("checked",true);
	} );
	//untoggle all clients when no clients radio button is changed
	$('#noServersOption').change( function() {
		$(".server_group").prop("checked",false);
	} );

	//hide warnings for each section
	$( "#fileWarning" ).fadeOut();
	$( "#serverWarning" ).fadeOut();
	$( "#programWarning" ).fadeOut();



	//when a key is pressed with the parameters field focussed
	$("#parametersInput").on("keydown", function(event) {
		//don't navigate away from the field on tab when selecting an item
		if (event.keyCode === $.ui.keyCode.TAB &&
			$(this).autocomplete( "instance" ).menu.active){
			event.preventDefault();
		}
	})
	.autocomplete({
		minLength: 0,
		source: function(request, response) {
		  //delegate back to autocomplete, but extract the last term
		  response($.ui.autocomplete.filter(
			configFiles, extractLast( request.term)));
		},
		focus: function() {
			//prevent value inserted on focus
			return false;
		},
		select: function( event, ui ) {
			var terms = split(this.value);
			//remove the current input
			terms.pop();
			//add the selected item
			terms.push( ui.item.value );
			//seperate terms with space
			this.value = terms.join(" ");
			return false;
		}
	});

});

var clients; //array of clients
var preprocessors; //array of preprocessors
var programs; //array of programs
var numLoadedTables = 0; //number of tables loaded from database
var jobsArray = [];
var configFiles = [];

//function to setup interactions for the help buttons (info buttons on each card)
function setupModals()
{
	//when help button clicked
	$(".show-dialog").click(function(){
		var dialogId = $(this).attr('id');
		var modal;
		if(dialogId=="problemsModalBtn"){
			modal = document.getElementById('problemsModal');
			modal.style.display = "block"; //display modal
		}
		else if(dialogId=="programsModalBtn"){
			modal = document.getElementById('programsModal');
			modal.style.display = "block"; //display modal
		}
		else if(dialogId=="optionsModalBtn"){
			modal = document.getElementById('optionsModal');
			modal.style.display = "block"; //display modal
		}
		//close modal when user clicks anywhere on the page
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
		//close modal when close button is clicked
		$(".close").click(function(){
			modal.style.display = "none";
		});
	});
}

//function to add clients to the clients area
function displayServers(){

	var div = document.getElementById('serversCardArea');

	for(var j=0;j<clients.length;j++)
	{
		//add new checkbox with value client_id and text client_name
		div.innerHTML = div.innerHTML + "<label><input type='checkbox' class='server_group' name='clients' value='"+clients[j].client_id+"'/><span>"+clients[j].client_name+"</span></label>";
	}
}



//function to load config files and handle the dropdown box for the parameters input field
function loadConfigFiles(selectedProgram){

	console.log("loading config files for "+selectedProgram);
	$.ajax({
	type: "GET",
	url: "functions/LoadConfigFiles.php",
	cache: false,
	data: {program : selectedProgram},
	dataType: "json",
	success: function (data) {
		console.log("Loaded config files "+data);
		configFiles = data;
	}
	});
}

function split(val) {
	return val.split( / \s*/ );
}
function extractLast(term) {
	return split(term).pop();
}

//function to populate the dropdown boxes boxes for the program and preprocessors and update
//the preprocessor based on the program selected
function updateProgramOptions(){

	//get the dropdown boxes
	var programSelect = document.getElementById("program-select");
	var preProcessorSelect = document.getElementById("preprocessor-select");
	var parametersInput = document.getElementById("parametersInput");

	//add default empty option
	var option = document.createElement("option");
	programSelect.add(option);

	//add all of the programs to the drop down
	for(var i=0;i<programs.length;i++)
	{
		var option = document.createElement("option");
		option.text = programs[i].program_name;
		option.value = programs[i].program_id;
		programSelect.add(option);
	}


	//when a program is selected
	programSelect.addEventListener("change", function() {

		loadConfigFiles(programSelect.options[programSelect.selectedIndex].text);

		//empty the preprocessors and select first item
		$("#preprocessor-select").empty();
		$('#preprocessor-select').val(0);

		//check if program is selected
		if(programSelect.options[programSelect.selectedIndex].value!='')
		{
			//set default parameters
			parametersInput.value=programs[programSelect.selectedIndex-1].program_parameters;
		}

		//loop through preprocessors
		for(var i=0;i<preprocessors.length;i++)
		{
			//add preprocessor option if it is designed for the current program
			if(preprocessors[i].program_id==programSelect.options[programSelect.selectedIndex].value)
			{
				var option = document.createElement("option");
				option.text = preprocessors[i].pre_processor_name;
				option.value = preprocessors[i].pre_processor_id;
				preProcessorSelect.add(option);
			}
		}
	});
}

//function to get the ID's of the selected server checkboxes and return as an array
function getSelectedClients()
{
    var selectedServers = [];

	//for each selected server_group checkbox
	$("input:checkbox[class='server_group']:checked").each(function(){
		selectedServers.push($(this).val());
	});
	return selectedServers;
}

function isDouble(num){
	var val = parseFloat(num);
	if(isNaN(val))
		return false;
	else
		return true;
}

//function to check if an input is an integer
function isInteger(x) {
    return x % 1 === 0;
}

//function to validate the option fields
function validateAndStart(){

	var valid = true;
	document.getElementById("startTaskBtn").disabled = true;

	//get values from option fields
	var theName = document.getElementById("nameField").value;
	var theMemory = document.getElementById("memoryField").value;
	var theLoad = document.getElementById("loadField").value;
	var theTimeout = document.getElementById("timeoutField").value;

	//disable start button and hide option errors
	//document.getElementById("startTaskBtn").disabled = true;
	document.getElementById("memoryError").style.display = 'none';
	document.getElementById("loadError").style.display = 'none';
	document.getElementById("timeoutError").style.display = 'none';

	//check fields are valid
	if(theMemory=="" || theMemory>8000 || theMemory<0 || !isInteger(theMemory))
	{
		document.getElementById("memoryError").style.display = 'inline-block';
		valid = false;
	}
	if(theLoad=="" || theLoad>8 || theLoad<1 || !isDouble(theLoad))
	{
		document.getElementById("loadError").style.display = 'inline-block';
		valid = false;
	}
	if(theTimeout=="" || theTimeout>72000 || theTimeout< 1|| !isInteger(theTimeout))
	{
		document.getElementById("timeoutError").style.display = 'inline-block';
		valid = false;
	}

	//check at least one server is selected
	if(getSelectedClients().length==0)
	{
		valid = false;
		var tempServers = $( "#serverWarning" ).get(0);
        tempServers.innerHTML = ' <div class="isa_warning"> <i class="fa fa-warning"></i>Must select at least one client</div>';
        $( "#serverWarning" ).fadeIn();
	}
	else
	{
		$( "#serverWarning" ).fadeOut();
	}

	//check at least 1 file has been included
	var includedListDiv = document.getElementById("includedFilesList");
	if(includedListDiv.options.length==0)
	{
		var tempJob = $( "#fileWarning" ).get(0);
        tempJob.innerHTML = ' <div class="isa_warning"> <i class="fa fa-warning"></i>Must select at least one problem file</div>';
        $( "#fileWarning" ).fadeIn();
		valid = false;
	}
	else
	{
		$( "#fileWarning" ).fadeOut();
	}

	//check at least 1 configuration has been added
	var numPrograms = document.getElementById("configurationTable").rows.length;
	if(numPrograms<2){
		valid = false;
		$( "#programWarning" ).fadeIn();
		var tempProgram = $( "#programWarning" ).get(0);
        tempProgram.innerHTML = ' <div class="isa_warning"> <i class="fa fa-warning"></i>Must select at least one program</div>';
	}
	else{
		$( "#programWarning" ).fadeOut();
	}

	//begin process of starting the task
	if(valid==true)
	{
		getProblemFiles();
	}
	else
	{
		document.getElementById("startTaskBtn").disabled = false;
	}
}

//function to create task (with webserver)
function createTask(numJobs)
{
	//display loading circle
	document.getElementById("processingTask").style.display = 'inline-block';

	//get option field data
	var theMemory = document.getElementById("memoryField").value;
	var theLoad = document.getElementById("loadField").value;
	var theTimeout = document.getElementById("timeoutField").value;
	var theName = document.getElementById("nameField").value;
	var tableRef = document.getElementById("configurationTable");

	var theProgram = [];
	var thePreprocessor = [];
	var theParameters = [];

	//load configuration table into arrays
	for(var i=1; i<tableRef.rows.length;i++){
		theProgram.push(tableRef.rows[i].cells[1].value);
		thePreprocessor.push(tableRef.rows[i].cells[0].value);
		theParameters.push(tableRef.rows[i].cells[2].value);
	}

	//load id's of selected clients
	var selectedClientsArray = getSelectedClients();

	//request to create task+jobs in database, create log directory and contact the distribution server to start the task
	$.ajax({
		type: "POST",
		url: "functions/StartTask.php",
		data: {name : theName, memory : theMemory, load : theLoad, timeout : theTimeout, num_jobs : numJobs,
				jobs : JSON.stringify(jobsArray), program : theProgram, preprocessor : thePreprocessor, parameters : theParameters,
				selected_servers : selectedClientsArray},
		cache: false,
		dataType: "text",
		success: function (data) {
			displayNotification(data);

			//hide loading bar and enable start button
			document.getElementById("processingTask").style.display = 'none';
			document.getElementById("startTaskBtn").disabled = false;
		}
	});
}

//function to load all of the included problems into an array
function getProblemFiles()
{
	//clear array
	jobsArray = [];

	var includedListDiv = document.getElementById("includedFilesList");
	var numPrograms = document.getElementById("configurationTable").rows.length;

	//add each included problem to array
	for(var i=0;i<includedListDiv.options.length;i++)
	{
		jobsArray.push(includedListDiv.options[i].value);
		//console.log(includedListDiv.options[i].value);
	}
	numPrograms = numPrograms-1;

	//call function to create task
	createTask(jobsArray.length*numPrograms);
}

//function to load all of the clients into an array
function loadClients(){
	console.log("attempting to load clients");

	//request to load clients table from database
	$.ajax({
		type: "POST",
		url: "functions/LoadTables.php",
		data: {tableName : 'Clients'},
		dataType: "json",
		success: function (data) {
			console.log(JSON.stringify(data));
			clients=data;
			numLoadedTables++;
			displayServers();
			checkTablesLoaded();
		}
	});
}

//function to load all of the preprocessors into an array
function loadPreProcessors(){
	console.log("attempting to load preprocessors");

	//request to load preprocessors table from database
	$.ajax({
		type: "POST",
		url: "functions/LoadTables.php",
		data: {tableName : 'PreProcessors'},
		dataType: "json",
		success: function (data) {
			console.log(JSON.stringify(data));
			preprocessors=data;
			numLoadedTables++;
			checkTablesLoaded();
		}
	});
}

//function to load all of the programs into an array
function loadPrograms(){
	console.log("attempting to load programs");

	//request to load programs table from database
	$.ajax({
		type: "POST",
		url: "functions/LoadTables.php",
		data: {tableName : 'Programs'},
		dataType: "json",
		success: function (data) {
			console.log(JSON.stringify(data));
			programs=data;
			numLoadedTables++;
			checkTablesLoaded();
		}
	});
}

//function to check if all tables from database have loaded
function checkTablesLoaded()
{
	if(numLoadedTables==3)
	{
		updateProgramOptions();
	}
}

/*function to add a program/pre-processor/parameters configuration to the table and check the configuration is
valid and does not already exist*/
function addConfiguration(){
	
	var valid = true;
	var selectedPreProcessor = document.getElementById("preprocessor-select");
	var selectedProgram = document.getElementById("program-select");
	var chosenParameters = document.getElementById("parametersInput").value;
	var errorText = document.getElementById("programsError");
	errorText.style.display = 'none';

	//check program/preprocessor has been selected
	try
	{
		var preText = selectedPreProcessor.options[selectedPreProcessor.selectedIndex].text;
		var proText = selectedProgram.options[selectedProgram.selectedIndex].text;
	}
	catch(err)
	{
		errorText.innerHTML = 'Invalid Configuration';
		errorText.style.display = 'inline-block';
		return;
	}
	if(chosenParameters.length>1024)
	{
		errorText.innerHTML = 'Invalid Parameters';
		errorText.style.display = 'inline-block';
		return;
	}

	//loop through the configuration table and check if the configuration already exists
	var tableRef = document.getElementById("configurationTable");
	var newID = proText+preText+chosenParameters;
	for(var i=1; i<tableRef.rows.length;i++)
	{
		var oldID = "";
		oldID = tableRef.rows[i].cells[0].innerHTML + tableRef.rows[i].cells[1].innerHTML + tableRef.rows[i].cells[2].innerHTML;
		if (newID == oldID)
		{
			valid = false;
		}
	}

	//configuration does not exist, add to table
	if(valid==true)
	{
		var newRow  = tableRef.insertRow(tableRef.rows.length);
		var cell0  =  newRow.insertCell(0);
		var cell1  = newRow.insertCell(1);
		var cell2  = newRow.insertCell(2);
		var cell3  = newRow.insertCell(3);

		var text = document.createTextNode(proText);
		cell0.className = 'mdl-data-table__cell--non-numeric';
		cell0.value = selectedPreProcessor.value;
		cell0.appendChild(text);

		var text = document.createTextNode(preText);
		cell1.className = 'mdl-data-table__cell--non-numeric';
		cell1.value = selectedProgram.value;
		cell1.appendChild(text);

		var text = document.createTextNode(chosenParameters);
		cell2.className = 'mdl-data-table__cell--non-numeric';
		cell2.value = chosenParameters;
		cell2.appendChild(text);

		cell3.innerHTML = '<a class="btn-delete mdl-button mdl-button--icon"><i class="fa fa-times" style="color:red" ></i></a>'
	}
	else //configuration exists, display error
	{
		console.log("error");
		document.getElementById("programsError").innerHTML = 'Configuration Exists';
		document.getElementById("programsError").style.display = 'inline-block';
	}
}
