<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

$_SESSION = array(); 
session_destroy(); 
if($_SESSION['userid'])
{ 
	DisplayCommonHeader();
    	printf("Your session is still active."); 
} 
else 
{ 
	DisplayGeneralPublicHeader();	
    	printf("You have been logged out of the Handicap Calculator.<br><br>"); 
        printf("Click <a href=\"../\">here</a> to go to your club website.<br>"); 
        printf("Click <a href=\"./\">here</a> to login to the calculator again.<br>");
} 
DisplayCommonFooter();
?>














