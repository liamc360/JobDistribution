<?php
if (isset($_POST['username']))
{
	$error = "Username or Password is invalid";
	
	//check if username or password is empty
	if (empty($_POST['username']) || empty($_POST['password']))
	{
		echo $error;
	}
	else
	{
		include('dbinfo.php');
		
		$email=$_POST['username'];
		$password=$_POST['password'];
		
		//setup sql connection using data from dbinfo file
		$mysql_conn = mysqli_connect(HOST, USER, PASS, DB)
						  or die("Login error" . mysqli_error($mysql_conn));
						  
		//prepare statements to be run on database 	  
		$stmt = $mysql_conn->prepare('SELECT * FROM Users WHERE user_email = ? AND user_password = ? LIMIT 1'); 
		$stmt->bind_param('ss', $email,$password);
		$stmt->execute();
		$result = $stmt->get_result();
			  
		//check if login is correct and start user session  
		if(mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_assoc($result);
			
			session_start();
			$_SESSION['login_user']=$email;
			$_SESSION['userid'] =$row['user_id'];
			$_SESSION['loggedin'] = true;
			echo "success";
		}
		else 
		{
			$error = "Username or Password is invalid";
			echo $error;
		}	
		mysql_close($mysql_conn);
	}
}
?>