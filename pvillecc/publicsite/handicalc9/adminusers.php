<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<h1>ADMIN PAGE</H1>");
	printf("<h2>User List</H2>");
	if ( !inConfigName("ADMIN_USER", $_SESSION['email']) )
	{
		printf("You are not authorized to view this page");
		//printf("Admin_User:  %s", getConfigValue("ADMIN_USER"));
		//printf("Email:  %s", $_SESSION['email']);
		DisplayCommonFooter();
		return;
	}
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
	else
	{
		//printf("Admin_User:  %s<br>", getConfigValue("ADMIN_USER"));
		//printf("Email:  %s<br>", $_SESSION['email']);

		$sql = "select u.*, date_format(createdt,'%m/%d/%y') as createdate from user_tbl u order by createdt desc";
		$result = mysql_query($sql) or die("Could not select users: " . mysql_error());
		$NumUsers = mysql_num_rows($result);
		printf("<table>");
		$rowCnt = 0;
			printf("<tr><td>Num</td><td>Email</td><td>Created</td></tr>");
		while ($row = mysql_fetch_array($result))
		{
			$classname = ($rowCnt % 2) ? 'CourseList2' : 'CourseList1';
			printf("<form action=\"home.php\" method=\"POST\">");
			printf("<tr class=\"$classname\">");
			printf("<td>%s</td>", $NumUsers);
			printf("<td>%s</td>", $row["email"]);
			printf("<td>%s</td>", $row["createdate"]);
			printf("<input type=\"hidden\" name=\"Email\" value=\"%s\">", $row["email"]);
			printf("<input type=\"hidden\" name=\"Password\" value=\"%s\">", $row["password"]);
			printf("<td><input type=\"submit\" name=\"LoginUser\" value=\"Login\"></td>");
			printf("</tr>");
			printf("</form>");
			$rowCnt++;
			$NumUsers--;
		}
		printf("</table>");
		
		
		
		
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














