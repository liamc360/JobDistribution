$( document ).ready(function() {
	checkClientStatus();
	setupModals();
});

/*function to make the information buttons display modals*/
function setupModals()
{
	//when help button clicked
	$("#manageServersModalBtn").click(function(){
		
		var modal = document.getElementById('manageServersModal');
		modal.style.display = "block"; //display modal

		console.log("a");
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
	$("#clientsModalBtn").click(function(){
		
		var modal = document.getElementById('clientsModal');
		modal.style.display = "block"; //display modal

		console.log("a");
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

/*function to start the distribution server on the webserver.
kills the old distribution server in the process*/
function startDistributionServerWeb()
{
	var response = confirm("Are you sure you want to start the distribution server?\nWarning: This will stop the previous server");
	if(response == true)
	{
		var startBtn = document.getElementById("startDistributionServerWeb");
		startBtn.disabled = true;
		$.ajax({
			type: "GET",
			url: "functions/KillDistributionServer.php",
			cache: false,
			dataType: "text",
			success: function (data) {
				checkServerStatus();
				$.ajax({
					type: "GET",
					url: "functions/StartDistributionServer.php",
					cache: false,
					timeout:3000,
					dataType: "text",
					error: function(){
						//check the server status to update status label
						checkServerStatus();
						displayNotification("distribution server started");
						startBtn.disabled = false;
					},
					success: function (data) {
						//check the server status to update status label
						checkServerStatus();
						displayNotification("distribution server started");
						startBtn.disabled = false;
					}
				});
			}
		});		
	}
}

/*function to start the distribution server with given IP/name.
kills the old distribution server in the process*/
function startDistributionServer()
{
	var theIP = document.getElementById("ipField").value;
	
	//check IP/machine name has been entered
	if(theIP=="")
	{
		alert("Please enter an IP address or machine name");
	}
	else
	{
		//confirm users choice
		var response = confirm("Are you sure you want to start the distribution server?\nWarning: This will stop the previous server");
		if(response == true)
		{
			var startBtn = document.getElementById("startDistributionServer");
			startBtn.disabled = true;
			//make ajax request to kill the (old) server
			$.ajax({
				type: "GET",
				url: "functions/KillDistributionServer.php",
				cache: false,
				dataType: "text",
				success: function (data) {
					
					//check the server status to update status label
					checkServerStatus();
					
					//make ajax request to start the (new) server
					$.ajax({
						type: "POST",
						url: "functions/StartServerAdvanced.php",
						data: {IP : theIP},
						timeout:3000,
						cache: false,
						dataType: "text",
						error: function(){
							//check the server status to update status label
							console.log("started distribution server");
							displayNotification("distribution server started");
							checkServerStatus();
							startBtn.disabled = false;
						},
						success: function (data) {
							//check the server status to update status label
							console.log("started distribution server");
							displayNotification("distribution server started");
							checkServerStatus();
							startBtn.disabled = false;
						}
					});	
				}
			});
		}
	}
}

/*function to check the clients availabiliy and loads
and populate the table*/
function checkClientStatus(){
	
	var clientsLoadingSpinner = document.getElementById("clientsLoadingSpinner");
	var refreshClientsBtn = document.getElementById("refreshClientsBtn");
	var table = document.getElementById("onlineClientlist");
	clientsLoadingSpinner.style.display='block'; //display loading spinner
	refreshClientsBtn.disabled=true;
	table.style.display='none'; //hide table
	$('#onlineClientlist tr').slice(1).remove(); //remove current table data
	
	$.ajax({
		url: "functions/CheckRunning.php",
		cache: false,
		dataType: "json",
		success: function (data) {
			
			/*loop through the client data returned from the json request
			and add it to the table*/
			for(var i = 0; i < data.length; i++) 
			{
				var rowCount = table.rows.length;
				var row = table.insertRow(rowCount);
				var cell0 = row.insertCell(0);
				var cell1  = row.insertCell(1);
				var cell2  = row.insertCell(2);
				var rowClass;
				var load;
				
				//check client status
				if(data[i].status=="online")
				{
					rowClass = 'success';
					load = data[i].client_load;
				}
				else
				{
					rowClass = 'failure';
					load = "DOWN";
				}
				var newText  = document.createTextNode(data[i].client_name);
				cell0.className = 'mdl-data-table__cell--non-numeric'+' '+rowClass;
				cell0.appendChild(newText);
				
				var newText  = document.createTextNode(data[i].client_ip);
				cell1.className = rowClass;
				cell1.appendChild(newText);
				
				var newText  = document.createTextNode(load);
				cell2.className = 'mdl-data-table__cell--non-numeric'+' '+rowClass;
				cell2.appendChild(newText);

			}
			//hide loading spinner and display table
			clientsLoadingSpinner.style.display='none';
			table.style.display='inline-block';
			refreshClientsBtn.disabled=false;
		}
	});
}

/*function to stop the distribution servers execution*/
function killDistributionServer()
{
	var response = confirm("Are you sure you want to kill the distribution server?");
	if(response == true)
	{
		var killBtn = document.getElementById("killDistributionServer");
		killBtn.disabled = true;
		$.ajax({
			type: "GET",
			url: "functions/KillDistributionServer.php",
			cache: false,
			dataType: "text",
			success: function (data) {
				//check the server status to update status label
				checkServerStatus();
				displayNotification("distribution server killed");
				killBtn.disabled = false;
			},
			error: function(){
				//check the server status to update status label
				checkServerStatus();
				displayNotification("distribution server killed");
				killBtn.disabled = false;
			}
		});
	}
}

/*function to start the client software on all clients*/
function startClients()
{
	var response = confirm("Are you sure you want to start all of the clients?");
	if(response == true)
	{
		$.ajax({
			type: "GET",
			url: "functions/StartClients.php",
			cache: false,
			dataType: "text",
			success: function (data) {
			},
			error: function(){
			}
		});
		displayNotification("requests to start clients sent");
	}
}
/*function to stop the client software from running on all clients*/
function killClients()
{
	var response = confirm("Are you sure you want to kill all of the clients?");
	if(response == true)
	{
		var killBtn = document.getElementById("killClients");
		killBtn.disabled = true;
		$.ajax({
			type: "GET",
			url: "functions/KillClients.php",
			cache: false,
			dataType: "text",
			success: function (data) {
				console.log("clients killed");
				displayNotification("clients killed");
				killBtn.disabled = false;
			},
			error: function(){
				console.log("clients killed");
				displayNotification("clients killed");
				killBtn.disabled = false;
			}
		});
	}
}







