$(document).ready(function() {
  $.fn.dataTable.ext.errMode = 'none';
    loadJobs();
	setupModals();
	 
} );

//holds all of the jobs
var loadedJobs;

function setupModals()
{
	//when help button clicked
	$(".show-dialog").click(function(){
		
		modal = document.getElementById('jobsModal');
		modal.style.display = "block"; //display modal

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

//function to load the tasks jobs from the database and setup the datatable
function loadJobs()
{	
	//request jobs from database
	$.ajax({
		type: "GET",
		url: "functions/LoadJobs.php",
		data: {none: "none"},
		dataType: "json",
		success: function (data) {
			//console.log(data);
			//console.log(JSON.stringify(data));
			loadedJobs = data;
			var paging = true;
			/*if(data.length>1400){
				paging = true;
			}*/
			
			//jquery datatable
			var table = $('#jobsTable').DataTable( {
				dom: 'Blfrtip',
				buttons: [
					{
						extend: 'csvHtml5', //CSV button 
						title: 'Jobs export' //name of exported file
					},
				],
				"iDisplayLength": 50, //set to 50 results per page by default
				"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], //results per page values
				"pagingType": "full_numbers",
				"aaData": loadedJobs, // <-- your array of objects
					"aoColumns": 
				[
					//column row data
					{"mDataProp": "job_id"},
					{"mDataProp": "start_time"},
					{"mDataProp": "end_time"},
					{"mDataProp": "pre_processor_name"},
					{"mDataProp": "program_name"},
					{"mDataProp": "input_file"},
					{"mDataProp": "job_parameters"},
					{ 
						//cell for the job status
						"mDataProp": "job_status",
						 "fnCreatedCell": function(nTd, sData, oData, iRow, iCol){
								
							//assign colours to the status text
							if(oData.job_status=="waiting")
							{
								$(nTd).css('color', '#FFB300'); //amber
							}
							else if(oData.job_status=="completed")
							{
								$(nTd).css('color', '#4CAF50'); //green
							}
							else if(oData.job_status=="timeout")
							{
								$(nTd).css('color', '#9C27B0'); //purple
							}
							else if(oData.job_status=="error")
							{
								$(nTd).css('color', '#0277BD'); //blue
							}
							else if(oData.job_status=="working")
							{
							   $(nTd).css('color', '#FF5722'); //orange
							}
							else
							{
							   $(nTd).css('color', '#F44336'); //red
							} 
						}
					},
					{"mDataProp": "client_name"},
					{ 
						//set value of cell to log name
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
				paging:         paging,
			} );	
			$('.dataTables_filter').addClass('auto-margin');
			
			//$('.dataTables_lengthMenu').addClass('pull-left');
			
		}
	});
	
	
}

