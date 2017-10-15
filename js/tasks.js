$(document).ready(function() {
	$.fn.dataTable.ext.errMode = 'none';
	setupModals();
    loadTasks();
});

//data for the tasks table
var loadedTasks;

//position of page scroll
var pageScrollPos=0;

function setupModals()
{
	//when help button clicked
	$(".show-dialog").click(function(){
		
		//display modal
		modal = document.getElementById('tasksModal');
		modal.style.display = "block"; 

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

//function to load the users tasks and setup the jquery datatable
function loadTasks()
{	
	//request task data from database
	$.ajax({
    type: "GET",
    url: "functions/LoadTasks.php",
	dataType: "json",
    success: function (data) {
		loadedTasks = data;
		var tempPercent = 0;
		var taskStatus = 0;
		
		//jquery datatable
		var table = $('#tasksTable').DataTable( {
			
			dom: 'Blfrtip',
			buttons: [
			
			],
			"iDisplayLength": 50, //set to 50 results per page by default
			"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], //results per page values
			"pagingType": "full_numbers",
			
			//setup scroll height remembering on table redraw
			"preDrawCallback": function (settings) {
				pageScrollPos = $('div.dataTables_scrollBody').scrollTop();
			},
			"drawCallback": function (settings) {
				$('div.dataTables_scrollBody').scrollTop(pageScrollPos);
			},
			
			//set loadedTasks as array for datatable
			"aaData": loadedTasks, 
				"aoColumns": 
			[
				//column row data
				{"mDataProp": "task_id"},
				{"mDataProp": "task_name"},
				{"mDataProp": "start_time"},
				{"mDataProp": "finish_time"},
				{"mDataProp": "max_load"},
				{"mDataProp": "max_memory"},
				{"mDataProp": "timeout"},
				{
					//calculate cell 
					mRender: function(data, type, row){
						
						//load status of task, to be used in next cell
						taskStatus = row.task_finished;
						
						//calculate the tasks completion percentage
						tempPercent = Math.floor((row.jobs_completed / row.total_jobs) * 100);	
						return tempPercent;
					},
					
					//display cell data
					"fnCreatedCell": function(nTd, sData, oData, iRow, iCol){
						
						if(tempPercent==100) //task full progress
						{
							$(nTd).html("<progress class='green' value='"+oData.jobs_completed+"' max='"+oData.total_jobs+"' ></progress> ");
						}
						else if(tempPercent<=30) //task low progress
						{
							$(nTd).html("<progress class='red' value='"+oData.jobs_completed+"' max='"+oData.total_jobs+"' ></progress> ");
						}
						else //task mid progress
						{
							$(nTd).html("<progress class='amber' value='"+oData.jobs_completed+"' max='"+oData.total_jobs+"' ></progress> ");
						}  
					}
				},
				{
					//cell for total jobs completed out of total jobs for the task
					"fnCreatedCell": function(nTd, sData, oData, iRow, iCol){
						
						
						if(taskStatus==2) //task is cancelled, display jobs completed in red
						{
						   $(nTd).html("<span style='color:#F44336'>"+oData.jobs_completed+"/"+oData.total_jobs+"</span>");
						}
						else //display jobs completed
						{
							$(nTd).html(oData.jobs_completed+"/"+oData.total_jobs);
						}
					  }
				},
				{ 
					//cell for jobs hyperlink
					"fnCreatedCell": function(nTd, sData, oData, iRow, iCol){
						   $(nTd).html("<a href='jobs.php?id="+oData.task_id+"'>View</a>");
					 }
				},
				{	 
					//cell for actions (cancel/delete)
					mRender: function (o) { 
						if(taskStatus==0) //show cancel button if task in progress
						{
							return '<a class="btn-cancel mdl-button mdl-button--icon"><i class="fa fa-times" style="color:red" ></i></a>'; 
						}
						else //show delete button
						{
							return '<a class="btn-delete mdl-button mdl-button--icon"><i class="fa fa-trash-o" ></i></a>';
						}
					}	
				}
			],
			scrollY:        '65vh',
			scrollX: true,
			scrollCollapse: true,
			paging: true,
		});
		
		
		//handle click on "Delete" button
		$('#tasksTable tbody').on('click', '.btn-delete', function (e) {	 
			
			//get selected row data
			var data = table.row( $(this).parents('tr') ).data();
			 
			//ask user to confirm delete
			var response = confirm("Are you sure you want to delete task "+data.task_id+" ?");
			if(response == true)
			{
				//delete task and the task directory
				deleteTask(data.task_id);
				
				//console.log("deleted task "+ data.task_id);
				
				//remove task from table and redraw
				table.row( $(this).parents('tr') ).remove();
				table.draw();
			}
		});
		
		//handle click on "Cancel" button
		$('#tasksTable tbody').on('click', '.btn-cancel', function (e) {	 
			
			//get selected row data
			var data = table.row( $(this).parents('tr') ).data();
			
			//ask user to confirm cancellation
			var response = confirm("Are you sure you want to cancel task "+data.task_id+" ?");
			if(response == true)
			{
				//console.log("cancelling task "+ data.task_id);
				
				//cancel task
				cancelTask(data.task_id);
			}
		});
	 
	 
	 
	    //custom filtering to filter table according to the radio buttons selected
		$.fn.dataTable.ext.search.push(
			function( settings, data, dataIndex ) {
				
				if($('#allTasksOption').is(':checked'))//display whole table
				{
					return true;
				}
				else if($('#completedTasksOption').is(':checked')) //display completed tasks only
				{
					var progress = parseFloat( data[7] ) || 0; //use data for the progress column
					
					if(progress==100)
					{
						return true;
					}
					return false;
				}
				else if($('#incompleteTasksOption').is(':checked')) //display incomplete jobs only
				{
					var progress = parseFloat( data[7] ) || 0; //use data for the progress column
					
					if(progress<100)
					{
						return true;
					}
					return false;
				}
			}
		);
 
		//listener to redraw table when a radio button option is selected
		$(document).ready(function() {
			var table = $('#tasksTable').DataTable();
			 
			// Event listener to the two range filtering inputs to redraw on input
			$('#allTasksOption, #completedTasksOption, #incompleteTasksOption').change( function() {
				table.draw();
			});
		});
		$('.dataTables_filter').addClass('auto-margin');
    }
});

}

//function to cancel a working task
function cancelTask(taskID)
{
	//request to cancel the task on the distribution server
	$.ajax({
		type: "POST",
		url: "functions/CancelTask.php",
		cache: false,
		data: {task_id : taskID},
		dataType: "text",
		success: function (data) {
			displayNotification(data);
		}
	});
}

//function to delete a task
function deleteTask(theID){
	
	//request to delete task in database
	$.ajax({
		type: "POST",
		url: "functions/DeleteTask.php",
		data: {taskid: theID},
		dataType: "text",
		success: function (data) {
			displayNotification(data);
		}
	});
}
