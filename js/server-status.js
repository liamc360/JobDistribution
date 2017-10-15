$( document ).ready(function() {
	checkServerStatus();
});

function checkServerStatus()
{
	$.ajax({
		type: "GET",
		url: "functions/ConnectToServer.php",
		cache: false,
		dataType: "text",
		success: function (data) {
			console.log(data);
			if(data=="Server UP"){
				var serverStatus = document.getElementById("serverStatus");
				serverStatus.innerHTML = "Server UP";
				serverStatus.style='color:#4CAF50'
			}
			else{
				var serverStatus = document.getElementById("serverStatus");
				serverStatus.innerHTML = "Server DOWN";
				serverStatus.style='color:#F44336'
			}
		}
	});	
}