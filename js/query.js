$(document).ready(function() {
  $.fn.dataTable.ext.errMode = 'none';
    //loadJobs();
	loadClients();
	loadPreProcessors();
	loadPrograms();
	setupSelectionBoxes();
	setupTable();
} );

var loadedJobs;
var servers;
var preprocessors;
var programs;
var table;

//function to update the dropdown boxes with data loaded from the dtabase
function setupSelectionBoxes()
{
	var programSelect = document.getElementById("program-select");
	var preProcessorSelect = document.getElementById("preprocessor-select");
	
	//when a program is selected
	programSelect.addEventListener("change", function() {
		
		//empty the preprocessors and select first item
		$("#preprocessor-select").empty();
		$('#preprocessor-select').val(0);
		
		if(programSelect.options[programSelect.selectedIndex].value == "none")
		{
			var option = document.createElement("option");
			option.value = "none"
			preProcessorSelect.add(option);
			
			for(var i=0;i<preprocessors.length;i++)
			{
				var option = document.createElement("option");
				option.text = preprocessors[i].pre_processor_name;
				option.value = preprocessors[i].pre_processor_id;
				preProcessorSelect.add(option);
			}
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

//function to setup the results table
function setupTable()
{
	$('#jobsTable').dataTable().fnDestroy();
		table = $('#jobsTable').DataTable( {
			 
		dom: 'Blfrtip',
			buttons: [
				{
					extend: 'csvHtml5',
					title: 'Query export'
				},
			],
			"iDisplayLength": 50,
			"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
			"pagingType": "full_numbers",
		"aaData": loadedJobs, // <-- your array of objects
			"aoColumns": 
		[
			{"mDataProp": "job_id"}, // <-- which values to use inside object
			{"mDataProp": "task_id"},
			{"mDataProp": "task_name"},
			{"mDataProp": "start_time"},
			{"mDataProp": "end_time"}, // <-- which values to use inside object
			{"mDataProp": "pre_processor_name"},
			{"mDataProp": "program_name"},
			{"mDataProp": "input_file"},
			{"mDataProp": "job_parameters"},
			{ 
				"mDataProp": "job_status",
				"fnCreatedCell": function(nTd, sData, oData, iRow, iCol){
				
					if(oData.job_status=="waiting")
					{
						$(nTd).css('color', '#FFB300');
					}
					else if(oData.job_status=="completed")
					{
						$(nTd).css('color', '#4CAF50');
					}
					else if(oData.job_status=="timeout")
					{
						$(nTd).css('color', '#9C27B0');
					}
					else if(oData.job_status=="error")
					{
						$(nTd).css('color', '#0277BD'); 
					}
					else if(oData.job_status=="working")
					{
					   $(nTd).css('color', '#FF5722'); 
					}
					else
					{
						$(nTd).css('color', '#F44336');
					}
				}
			},
			{"mDataProp": "client_name"},
			{ 
					mRender: function(nTd, sData, oData, iRow, iCol){
						if(oData.log_name)
						{
							return "logs"+oData.log_name+".log";
						}
						
					},
					//cell for the log hyperlink
					"fnCreatedCell": function(nTd, sData, oData, iRow, iCol){
						if(oData.log_name)
						{
							$(nTd).html("<a href='logs"+oData.log_name+".log' target='_blank' >View</a>");
						}
					}
				}
		],
		scrollY:        '65vh',
		scrollX: true,
		scrollCollapse: true,
		paging:         true,
	} );
	$('.dataTables_filter').addClass('auto-margin');
}

//function to check if a value is an integer
function isInteger(x) {
    return x % 1 === 0;
}

//function to query the database and update the results table
function loadJobs()
{	
	var selectedProgram = document.getElementById("program-select").value;
	var selectedPreProcessor = document.getElementById("preprocessor-select").value;
	var selectedServer = document.getElementById("client-select").value;
	var selectedStatus = document.getElementById("status-select").value;
	var chosenTaskID = document.getElementById("taskIDInput").value;
	var chosenProblemFile = document.getElementById("problemFileInput").value;
	var queryBtn = document.getElementById("queryBtn");
	
	if(isInteger(chosenTaskID) || chosenTaskID==""){
		queryBtn.disabled = true;
		
		//send ajax request to query database
		$.ajax({
			type: "GET",
			url: "functions/QueryDatabase.php",
			data: {program : selectedProgram, preProcessor : selectedPreProcessor, server : selectedServer, jobStatus : selectedStatus, taskID : chosenTaskID, problemFile : chosenProblemFile},
			dataType: "json",
			success: function (data) {	
				console.log(data);
				console.log(JSON.stringify(data));
				queryBtn.disabled = false;
				table.clear().draw(); //remove old table data
				table.rows.add(data); //add new data
				table.columns.adjust().draw(); //redraw table
			}
		});
	}
}

//function to load all of the clients from the database
function loadClients(){
	console.log("attempting to load clients");
	$.ajax({
		type: "POST",
		url: "functions/LoadTables.php",
		data: {tableName : 'Clients'},
		dataType: "json",
		success: function (data) {
			console.log(JSON.stringify(data));
			servers=data;
				
			var x = document.getElementById("client-select");
			
			for(var i=0;i<servers.length;i++)
			{
				var option = document.createElement("option");
				option.text = servers[i].client_name;
				option.value = servers[i].client_id;
				x.add(option);
			}
		}
	});
}

//function to load all of the pre-processors from the database
function loadPreProcessors(){
	console.log("attempting to load preprocessors");
	$.ajax({
		type: "POST",
		url: "functions/LoadTables.php",
		data: {tableName : 'PreProcessors'},
		dataType: "json",
		success: function (data) {
			console.log(JSON.stringify(data));
			preprocessors=data;
			
			var x = document.getElementById("preprocessor-select");
			
			for(var i=0;i<preprocessors.length;i++)
			{
				var option = document.createElement("option");
				option.text = preprocessors[i].pre_processor_name;
				option.value = preprocessors[i].pre_processor_id;
				x.add(option);
			}
		}
	});
}

//function to load all of the programs from the database
function loadPrograms(){
	console.log("attempting to load programs");
	$.ajax({
		type: "POST",
		url: "functions/LoadTables.php",
		data: {tableName : 'Programs'},
		dataType: "json",
		success: function (data) {
			console.log(JSON.stringify(data));
			programs=data;
			
			var x = document.getElementById("program-select");
			
			for(var i=0;i<programs.length;i++)
			{
				var option = document.createElement("option");
				option.text = programs[i].program_name;
				option.value = programs[i].program_id;
				x.add(option);
			}
		}
	});
}
