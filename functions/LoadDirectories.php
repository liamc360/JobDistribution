<?php 
	$dirPath = dir('problems');
	$directoryArray = array();
	
	//search problems directory for folders
	$results = scandir('problems');
	
	//loop through the problems folder and add folder names to array
	foreach ($results as $result)
	{
		//check a real folder exists
		if ($result === '.' or $result === '..'){}
		else
		{
			$directoryArray[ ] = basename($result);
		}
	}
	$dirPath->close();
	
	//sort folder names
	asort($directoryArray);
	
	//loop through the array creating options to be displayed on the HTML page
	$c = count($directoryArray);
	for($i=0; $i<$c; $i++)
	{
		echo "<option value=\"" . $directoryArray[$i] . "\">" . $directoryArray[$i] . "\n";
	}
?>