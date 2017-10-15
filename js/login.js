function login(){
	var theEmail = document.getElementById('name').value
	var thePassword = document.getElementById("password").value;
	
	//ajax request to login to the website
	$.ajax({
    type: "POST",
    url: "login.php",
	data: {username : theEmail, password: thePassword},
    dataType: "text",
    success: function (data) 
	{
		if (data=="success") 
		{
            window.location='distribution.php';
        }	
        else
		{
        	document.getElementById('errors').innerHTML = data;
       		console.log(data);
        }
    }
});
}
