<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	if ($_POST['UpdateInfo'])
	{
		ConnectToDB();
		
		//
		//  FORM VALIDATION
		//
		if ( !IsRequiredFieldPresent("First Name", $_POST['fname']) )
		{
			DisplayCommonFooter();
			return;
		}
		if ( !IsRequiredFieldPresent("Last Name", $_POST['lname']) )
		{
			DisplayCommonFooter();
			return;
		}
		if ( !IsEmailValid($_POST['email']) )
		{
			DisplayCommonFooter();
			return;
		}
		/*
		if ( userPreExists($_POST['email']) )
		{
			printf("The email you have choosen is already in use, please choose another email.");
			DisplayCommonFooter();
			return;
		}
		*/
		$id = $_SESSION['userid'];
		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$email = $_POST['email'];
		$streetaddress = "null";
		if ($_POST['streetaddress'])
		{
			$streetaddress = "'";
			$streetaddress .= $_POST['streetaddress']; 
			$streetaddress .= "'";
		}
			
		$city = "null";
		if ($_POST['city'])
		{
			$city = "'";
			$city .= $_POST['city']; 
			$city .= "'";
		}
			
		$state = "null";
		if ($_POST['state'])
		{
			$state = "'";
			$state .= $_POST['state']; 
			$state .= "'";
		}
			
		$zip = "null";
		if ($_POST['zip'])
		{
			$zip = "'";
			$zip .= $_POST['zip']; 
			$zip .= "'";
		}
		
		$country = "null";
		if ($_POST['country'])
		{
			$country = "'";
			$country .= $_POST['country']; 
			$country .= "'";
		}

		
		$sql = "update user_tbl set fname = '$fname', lname = '$lname', email = '$email', street = $streetaddress, city = $city, state = $state, zip = $zip, country = $country where id = $id";
		//printf("%s", $sql);
		mysql_query($sql) or die("Could not update account: " . mysql_error());
		?>
			<meta http-equiv="Refresh" content="0; URL=./accountadmin.php">
		<?
	}
	else
	{
		$id = $_SESSION['userid'];
		$sql = "select * from user_tbl where id = $id";
		//echo $sql;
		$result = mysql_query($sql) or die("Could not get user info " . mysql_error());
		$courseid = mysql_insert_id();
		$row = mysql_fetch_array($result);
		$fname = $row['fname']; 
		$lname = $row['lname'];
		$email = $row['email'];
		$street = $row['street'];
		$city = $row['city'];
		$state = $row['state'];
		$zip = $row['zip'];
		$country = $row['country'];
	?>
		<H1><B>Update Account Information</B></H1>
		<form action="accountinfo.php" method="POST">
		<table>
			<tr><td>First Name:  </td><td><input value="<?echo $fname;?>" type="text" MAXLENGTH="45" name="fname"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
			<tr><td>Last Name:  </td><td><input value="<?echo $lname;?>" type="text" MAXLENGTH="45" name="lname"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
			<tr><td>Email:  </td><td><input value="<?echo $email;?>" type="text" name="email"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
			<tr><td>Street Address:  </td><td><input value="<?echo $street;?>" type="text" MAXLENGTH="45" name="streetaddress"></td></tr>
			<tr><td>City:  </td><td><input value="<?echo $city;?>" type="text" MAXLENGTH="45" name="city"></td></tr>
			<tr><td>State:  </td><td><input value="<?echo $state;?>" type="text" MAXLENGTH="45" name="state"></td></tr>
			<tr><td>Zip:  </td><td><input value="<?echo $zip;?>" type="text" MAXLENGTH="45" name="zip"></td></tr>
			<tr><td>Country:  </td><td><input value="<?echo $country;?>" type="text" MAXLENGTH="45" name="country"></td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td><input type="submit" name="UpdateInfo" value="Update My Info"></td></tr>
			<tr><td><span class="RequiredFieldIndicator">&nbsp*</span> indicates a required field.</td></tr>
		</table>
		</form>
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