<?php

session_start();

if(isset($_SESSION['login_user'])){
	header("location: distribution.php");
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" href="css/w3.css"/>
	<link rel="stylesheet" href="css/loginstyle.css"/>
	<link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">
	<script src="js/jquery-3.1.1.min.js"></script>
	<script src="js/login.js"></script>
</head>
<body>
	<div class="w3-card-4" id="cardArea">
		<div class="w3-card-4 loginCard noShadows" id="loginArea">
			<div class="w3-container" >
			<h1 id = "title">Account Login</h1>
			  <p>
			  		<form action="" method="post">
						<label class="w3-text-grey">Email Address</label>
						<input class="w3-input w3-border w3-light-grey" name="username" type="text" id="name">
						<br>
						<label class="w3-text-grey">Password</label>
						<input class="w3-input w3-border w3-light-grey" id="password" name="password" type="password">
						<br>
						<button type="button" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" id = "loginBtn" value=" Login " onclick="login()">
							Login
						</button>
						<br><br><br>
						<span id="errors" style="color: #F44336; font-size:18px"></span>
						<br>
						<span id="noCookie" style="color: #F44336; font-size:18px"></span>
						<script>
							if(!navigator.cookieEnabled){
								document.getElementById("noCookie").innerHTML = "You must enable cookies to use this website";
							}
						
							$("#name").keyup(function(event){
								if(event.keyCode == 13){
									$("#loginBtn").click();
								}
							});
							$("#password").keyup(function(event){
								if(event.keyCode == 13){
									$("#loginBtn").click();
								}
							});
		
						</script>
					</form>
			  </p>
			</div>
		</div>
	</div>

</body>
</html>

</div>