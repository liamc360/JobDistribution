<head>
	<script src="js/server-status.js"></script>
</head>
<div id="navigationDiv">
	<div id="left3">
		<h3>Job Distribution Tool</h3>
	</div>
	<div class = "right">
		<ul class = "nav">
        	<li><a class="navButton" href="logout.php"> Logout</a></li>
			<li><a class="navButton" href="manage.php"> Management</a></li>
			<li><a class="navButton" href="query.php"> Query Database</a></li>
			<li><a class="navButton" href="tasks.php"> View Results</a></li>
			<li><a class="navButton" href="distribution.php"> Distribute Jobs</a></li>
			<li class="icon">
					<a href="javascript:void(0);" style="font-size:30px;">☰</a>
			</li>
		</ul>
					 
		<button id="navigationMenuBtn" class="mdl-button mdl-js-button" > ☰ </button>

		<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect"
			for="navigationMenuBtn">
		  <li class="mdl-menu__item"><a href="distribution.php" class="mdl-menu__item"> Distribute Jobs</a></li>
		  <li class="mdl-menu__item"><a href="tasks.php" class="mdl-menu__item">View Tasks</a></li>
		  <li class="mdl-menu__item"><a href="query.php" class="mdl-menu__item">Query Database</a></li>
		  <li class="mdl-menu__item"><a href="manage.php" class="mdl-menu__item">Manage Servers</a></li>
		  <li class="mdl-menu__item"><a href="logout.php" class="mdl-menu__item"><span style="color: #F44336" >Logout</span></a></li>
		</ul>
	</div>
	<span id="serverStatus"></span>
</div>