//function to display notification bar at the bottom of the page
function displayNotification(toastMessage){
	'use strict';
	
	 var snackbarContainer = document.querySelector('#toastBar');
	 var data = {message: toastMessage};
	 snackbarContainer.MaterialSnackbar.showSnackbar(data);
}