<?php
	$pattern = $_GET['pattern'];
	$directory = $_GET['directory'];
	$files = array();
	
	//no pattern input, return empty array
	if($pattern == ''){}
	else if($directory == 'ALL FOLDERS')
	{
		//iterate through all directories within the 'problems' folder
		$directory = 'problems';	
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,RecursiveDirectoryIterator::SKIP_DOTS));
		
		//loop through each file path and check if pattern is found
		foreach($objects as $name => $object)
		{
			if (fnmatch($pattern, $name))
			{
				$files[] = substr($name, 9); //remove /problems/ from file location string
			}
		}
	}
	else
	{
		//iterate through all directories within the selected directory folder
		chdir('problems');
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,RecursiveDirectoryIterator::SKIP_DOTS));
		
		//loop through each file path and check if pattern is found
		foreach($objects as $name => $object)
		{
			if (fnmatch($pattern, $name))
			{
				$files[] = $name;
			}
		}
	}
	
	//sort files
	sort($files, SORT_NATURAL | SORT_FLAG_CASE);	
	echo json_encode($files);
?>