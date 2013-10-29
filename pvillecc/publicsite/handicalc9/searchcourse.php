<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
    
  	if ( $_POST["AddCourseToProfile"])
	{
		$courses = $_POST['coursecbx'];
		$CourseCount = count($courses);
		$UID = $_SESSION['userid'];
		for($i = 0; $i < $CourseCount; $i++)
			CopyCourse($courses[$i], $UID);
		//printf("Copied %s courses.", $CourseCount);
		
		?>
		<meta http-equiv="Refresh" content="0; URL=./courseadmin.php">
		<?
		
	}
	else
	{
		$sql = "select * from state_tbl order by name asc;";
		$result = mysql_query($sql) or die("Could not get list of states course: " . mysql_error());
		$row = mysql_fetch_array($result);
		$statesArray = array();
		$statesArray = $row;
		
		print("<h3>Search for courses</h3><br>");
		printf("<select onchange=\"StateCourseSearch(this.value)\" class=\"InputBoxWidth\" name=\"State\">");
			print("<option selected value=\"0\">Choose One...</option>");
			while ($statesArray)
			{
				printf("<option value=\"%s\">%s</option>",$statesArray["id"],$statesArray["name"]);
				$statesArray = mysql_fetch_array($result);
			}
		printf("</select>");
		?><div id="search_results">
        </div><?
	}
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
