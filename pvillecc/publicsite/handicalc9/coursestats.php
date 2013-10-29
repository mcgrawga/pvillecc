<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	
	
	ConnectToDB();
	
	if ( $_GET['ShowCourseStats'] )
	{
		$TeeId = $_GET['teeid'];
		printf("<h3>%s</h3>", getCourseAndTeeName($_GET['teeid']));
		DisplayScorecard( "COURSE_STATS", $_GET['teeid'] );
	}
	else
	{
		printf("<h3>Statistics</h3>");
		$sql = "select concat(ct.name, '  (', tt.name, ')') as courseandtee, tt.id as teeid from course_tbl ct, tee_tbl tt where tt.courseid = ct.id and ct.userid = ";
		//$sql = "select distinct course.name name, course.id id from course_tbl course where course.userid = ";
		$sql .= $_SESSION['userid'];
		$sql .= " order by courseandtee";
		$result = mysql_query($sql) or die("Could not get a list of courses: " . mysql_error());
		if ( mysql_num_rows($result) == 0 )
			printf("You have not entered any courses yet.  <br>Click <a href=\"addcourse.php\">here</a> to enter a course.");
		$rowCnt = 0;
		printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">");
		while ($row = mysql_fetch_array($result))
		{
			$classname = ($rowCnt % 2) ? 'CourseList2' : 'CourseList1';
			printf("<tr class=\"$classname\">");
			printf("<td>%s</td>",$row["courseandtee"]);
			printf("<td><A HREF=\"trendingstats.php?teeid=%s&num12\">Trending</A></td>",$row["teeid"],$row["courseandtee"]);
			printf("<td><A HREF=\"coursestats.php?ShowCourseStats=1&teeid=%s\">Hole By Hole</A></td>",$row["teeid"],$row["courseandtee"]);
			printf("<tr>");
			$rowCnt++;
		}
		printf("</table>");
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















