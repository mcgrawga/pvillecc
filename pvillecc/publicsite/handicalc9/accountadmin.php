<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<br><br><h2>Account Information and Administration</h2><br>");
	if ( $_GET["ChangePassword"] )
	{
		?>
		<table>
		<tr>
		<form action="accountadmin.php" method="POST" name="passwordchangetable">
		<tr><td>Current Password: </td><td><input type="password" name="CurrentPassword"></td><tr> 
		<tr><td>New Password:  </td><td><input type="password" name="NewPassword"></td><tr> 
		<tr><td>Confirm New Password:  </td><td><input type="password" name="ConfirmNewPassword"></td><tr> 
		<tr><td><input type="submit" value="Change Password" name="DoChangePassword"></td><tr> 
		</form>
		</tr>
		</table>
		<script language="JavaScript">
			<!--
			document.passwordchangetable.CurrentPassword.focus()
			//-->
		</script>
		<?
	}
	else if ( $_POST["DoChangePassword"] )
	{
		$curPwd = $_POST["CurrentPassword"];
		$newPwd = $_POST["NewPassword"];
		$confirmPwd = $_POST["ConfirmNewPassword"];
		$userId = $_SESSION['userid'];
		
		// Verify password
		$sql = "select password from user_tbl where id = '$userId'";
		$result = mysql_query($sql) or die("Could not validate current password: " . mysql_error() );
		$row = mysql_fetch_array($result);
		$pwd = $row["password"];
		if ( $pwd != $curPwd )
		{
			printf("Current Password Invalid");
			return 1;
		}
		
		if ( $newPwd != $confirmPwd )
		{
			printf("Confirmation password does not match the new password.");
			return 1;
		}
		
		$sql = "update user_tbl set password = '$newPwd' where id = '$userId'";
		$result = mysql_query($sql) or die("Could not update account with new password: " . mysql_error());;
		printf("Password change successful.  <br>Please use the new password the next time you login.");
		
	}
	else
	{
		//printf("<div id=\"paragraphtext\">");
		printf("Your account was created on %s<br>", date("F j, Y", GetSignUpDate()));
		?>	<br>
			<a href="accountinfo.php">Update Account Info</a><br>
			<a href="accountadmin.php?ChangePassword=1">Change Password</a><br><?
		//printf("</div>");
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
