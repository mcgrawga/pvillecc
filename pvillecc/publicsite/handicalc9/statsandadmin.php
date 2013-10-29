<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<h1>STATS AND ADMIN PAGE</H1>");
	if ( !inConfigName("ADMIN_USER", $_SESSION['email']) )
	{
		printf("You are not authorized to view this page");
		//printf("Admin_User:  %s", getConfigValue("ADMIN_USER"));
		//printf("Email:  %s", $_SESSION['email']);
		DisplayCommonFooter();
		return;
	}
	/*
	else if ($_POST['CreateGiftCertificates'])
	{
		$num = 	$_POST['NumGiftCertificates'];
		for ($i = 0; $i < $num; $i++)
		{
			$gc = InsertGiftCode();
			MarkGiftCodeAsPaid($gc);
			printf("Gift Code %s:  <a href=\"successfulpaymentgc.php?gc=%s&email=NO_GO\">%s</a><br>", $i+1, $gc, $gc);
		}
		DisplayCommonFooter();
	}
	*/
	else
	{
		//printf("Admin_User:  %s<br>", getConfigValue("ADMIN_USER"));
		//printf("Email:  %s<br>", $_SESSION['email']);
		?>
		<!-- 
		<form action="statsandadmin.php" method="POST">
		I want to create <input class="ScoreInputBox" type="text" MAXLENGTH="2" name="NumGiftCertificates"> gift certificates.  
		<input  type="submit" name="CreateGiftCertificates" value="Create Gift Certificates">
		</form>
		-->
		
		<?
		printf("Users:  %s<br>", getNumUsers());
		printf("New users yesterday:  %s<br>", getNumUsersYesterday());
		printf("New users in past 7 days:  %s<br>", getNumUsersSince(7));
		printf("New users in past 30 days:  %s<br><br>", getNumUsersSince(30));
		
		
		printf("Courses:  %s<br>", getNumCourses());
		printf("Courses entered yesterday:  %s<br>", getNumCoursesYesterday());
		printf("Courses entered in past 7 days:  %s<br>", getNumCoursesSince(7));
		printf("Courses entered in past 30 days:  %s<br><br>", getNumCoursesSince(30));
		
		
		printf("Scores:  %s<br>", getNumScores());
		printf("Scores entered yesterday:  %s<br>", getNumScoresYesterday());
		printf("Scores entered in past 7 days:  %s<br>", getNumScoresSince(7));
		printf("Scores entered in past 30 days:  %s<br><br>", getNumScoresSince(30));
		
		?>
		<a href="adminusers.php">Show me a user list.</a><br><br>
		<?
		
		$result = getUsersAndNumScores();
		if ($result)
		{
			printf("<table>");
			$rowCnt = 0;
			printf("<tr><td><b>Email</b></td><td><b>Num Scores</b></td><td><b>Most Recent Score</b></td><td><b>Account Created On</b></td></tr>");
			while ($row = mysql_fetch_array($result))
			{
				$classname = ($rowCnt % 2) ? 'CourseList2' : 'CourseList1';
				printf("<tr class=\"$classname\">");
				printf("<td>%s</td>", $row["email"]);
				printf("<td>%s</td>", $row["num_scores"]);
				printf("<td>%s</td>", $row["last_score"]);
				printf("<td>%s</td>", $row["acct_create_dt"]);
				printf("</tr>");
				$rowCnt++;
			}
			printf("</table>");
		}
		
		$result = getStatesAndNumCourses();
		if ($result)
		{
			printf("<br><br><table>");
			$rowCnt = 0;
			printf("<tr><td></td><td><b>State / Province</b></td><td><b>Courses</b></td></tr>");
			while ($row = mysql_fetch_array($result))
			{
				$classname = ($rowCnt % 2) ? 'CourseList2' : 'CourseList1';
				printf("<tr class=\"$classname\">");
                printf("<td>%s</td>", ($rowCnt + 1));
				printf("<td>%s</td>", $row["state"]);
				printf("<td>%s</td>", $row["num_courses"]);
				printf("</tr>");
				$rowCnt++;
			}
			printf("</table>");
		}
				
		DisplayCommonFooter();
		return;
	}
}




	if ($_POST['LoginUser'])		// Check to see if we should login user
	{
		if ( ValidateCredentials($_POST['Email'], $_POST['Password']) )
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














