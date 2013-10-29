<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<br><br><h3>ScreenShots</h3>");
	
	//DisplayQuickStats($_SESSION['userid']);
	//printf("<img src=\"colortester.php\" />");
	printf("<table width=\"450\" border=\"0\">");
  	printf("<tr><td valign=\"top\"><b>Home Page</b></br>The Home Page is where all the scores are listed.</td><td><img src=\"img/homeSmall.png\" border=\"1\" /></td></tr>");
	printf("<tr><td valign=\"top\">Add Score</td><td><img src=\"img/addScoreSmall.png\" border=\"1\" /></td></tr>");
		printf("<tr><td valign=\"top\"><b>Add Course</b></br>The Home Page is where all the scores are listed.</td><td><img src=\"img/addCourseSmall.png\" border=\"1\" /></td></tr>");
	printf("</table>");

	DisplayCommonFooter();
}



	if ($_POST['LoginUser'])		// Check to see if we should login user
	{
		if ( ValidateCredentials($_POST['UserName'], $_POST['Password']) )
			DisplayPage();
	}
	else if (isset($_SESSION['userid']) && isset($_SESSION['paidup']))	// Already logged in and account current?
	{
		DisplayPage();
	}
	else		// Make them login
	{
		?>
		<meta http-equiv="Refresh" content="0; URL=./index.php">
		<?
	}
?>















<table width="200" border="1">
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
