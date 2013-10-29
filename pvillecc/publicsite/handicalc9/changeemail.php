<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<br><br><h3>Change Email</h3>");
	if ( $_POST["ChangeEmail"] )
	{
		if ( IsEmailValid($_POST["email"]) )
		{
			updateEmail($_SESSION['userid'], $_POST["email"]);
			?>
			<meta http-equiv="Refresh" content="0; URL=./accountadmin.php">
			<?
		}
	}
	else
	{
	?>
		<table>
		<tr>
		<form action="changeemail.php" method="POST" name="passwordchangetable">
		<tr><td>Email: </td><td><input type="text" name="email"></td><tr> 
		<tr><td><input type="submit" value="Update Email" name="ChangeEmail"></td><tr> 
		</form>
		</tr>
		</table>
	<?
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
