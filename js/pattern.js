$( document ).ready(function() {
	
	//when ENTER is pressed, apply pattern
	$("#patternField").keyup(function(event){
		if(event.keyCode == 13){
			$("#applyPatternBtn").click();
		}
	});	
});

var numFilesExcluded = 0;

/*function to get the input pattern and chosen directory, send it to the php script
and populate the included files list with files matching the pattern*/
function applyPattern(){
	
	//get data from html
	var thePattern = document.getElementById('patternField').value
	var excludedListDiv = document.getElementById("excludedFilesList");
	var directories = document.getElementById("directorySelect");
	var chosenDirectory = directories.options[directories.selectedIndex].text;
	
	//ajax request to submit pattern and directory to 'pattern.php'
	$.ajax({
		type: "GET",
		url: "pattern.php",
		data: {pattern : thePattern, directory: chosenDirectory},
		dataType: "json",
		success: function (data) {
			
			//check if script returned matches
			if(data.length >= 1)
			{
				/*clear excluded list and loop through the files,
				adding them to the excluded files list*/
				excludedListDiv.innerHTML = "";
				for (var i = 0; i < data.length; i++) 
				{
					var opt = document.createElement('option');
					opt.value = data[i];
					opt.innerHTML = data[i];
					excludedListDiv.appendChild(opt);
				}
				//update the number of excluded files and hide error
				updateExcludedLabel();
				document.getElementById("noPatternFound").style.display = 'none';
			}
			else
			{
				//display error
				document.getElementById("noPatternFound").style.display = 'inline';
			}
		}
	});
}

function clearExcludedList()
{
	var excludedListDiv = document.getElementById("excludedFilesList");
	excludedListDiv.innerHTML = "";
	updateExcludedLabel()
}
function clearIncludedList()
{
	var includedListDiv = document.getElementById("includedFilesList");
	includedListDiv.innerHTML = "";
	updateIncludedLabel();
}
function updateIncludedLabel()
{
	var includedListDiv = document.getElementById("includedFilesList");
	var includedLabel = document.getElementById("includedLabel");
	includedLabel.innerHTML = "<b>Files Included ("+includedListDiv.length+")</b>";
}
function updateExcludedLabel()
{
	var excludedListDiv = document.getElementById("excludedFilesList");
	var excludedLabel = document.getElementById("excludedLabel");
	excludedLabel.innerHTML = "<b>Files Excluded ("+excludedListDiv.length+")</b>";
}

/*function to move selected files from one option list to another,
prevents duplicates from being added*/
function moveRows(list1,list2)
{
	var SelID = '';
    var SelText = '';
	var j;
	var list2Length = list2.options.length;
	
	//loop through list1
	for (i = list1.options.length - 1; i>=0; i--)
    {
		//check if current option is selected
        if (list1.options[i].selected == true)
        {
			//loop through list 2
            for(j=0; j<list2Length; j++)
			{
				//file exists in list2
				if(list1.options[i].text == list2.options[j].text)
				{
					break;
				}
			}
			
			//check if whole of list2 search found 0 matches of file
			if(j==list2Length)
			{
				//add file from list1 to list2 and remove it from list1
				SelID=list1.options[i].value;
				SelText=list1.options[i].text;
				var newRow = new Option(SelText,SelID);
				list2.options[list2.length] = newRow;
				list1.options[i] = null;
			}
        }
    }
	//update total labels for each list
	updateIncludedLabel();
	updateExcludedLabel();	
}

function moveAllRows(list1,list2)
{
	var SelID='';
    var SelText='';
	var j;
	var list2Length = list2.options.length;
	
    for (i=list1.options.length - 1; i>=0; i--)
    {
		for(j=0;j<list2Length;j++)
		{
			if(list1.options[i].text == list2.options[j].text)
			{
				//console.log("error "+list1.options[i].text+" already exists");
				break;
			}
		}
		if(j==list2Length)
		{
			SelID=list1.options[i].value;
			SelText=list1.options[i].text;
			var newRow = new Option(SelText,SelID);
			list2.options[list2.length]=newRow;
			list1.options[i]=null;
		}
    }
	updateIncludedLabel();
	updateExcludedLabel();	
}
