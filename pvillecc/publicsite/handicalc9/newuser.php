<?
include 'functions.php';
DisplayGeneralPublicHeader();
if ($_POST['AddUser'])
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
	if ( userPreExists($_POST['email']) )
	{
		printf("The email you have choosen is already in use, please choose another email.");
		DisplayCommonFooter();
		return;
	}
	if ( !IsPasswordValid($_POST['password']) )
	{
		DisplayCommonFooter();
		return;
	}
	if ( $_POST['password'] == $_POST['username'] )
	{
		printf("Username and password must not be the same.");
		DisplayCommonFooter();
		return;
	}
	if ( !IsCPasswordValid($_POST['confirmpassword']) )
	{
		DisplayCommonFooter();
		return;
	}
	if ( $_POST['password'] != $_POST['confirmpassword'] )
	{
		printf("Passwords do not match.");
		DisplayCommonFooter();
		return;
	}
	if ( $_POST['giftcode'] )
	{
		// MAKE SURE THE GIFT CODE EXISTS.
		$sql = "select * from giftcode_tbl where code = '";
		$sql .= $_POST['giftcode'];
		$sql .= "'";
		$result = mysql_query($sql) or die("Could not get gift code: " . mysql_error());
		if ( mysql_num_rows($result) == 0 )
		{
			printf("Invalid gift code.");
			DisplayCommonFooter();
			return;
		}
		
		// MAKE SURE THE GIFT CODE HAS NOT ALREADY BEEN USED.
		$sql = "select * from giftcode_tbl where used = 0 and code = '";
		$sql .= $_POST['giftcode'];
		$sql .= "'";
		$result = mysql_query($sql) or die("Could not get gift code: " . mysql_error());
		if ( mysql_num_rows($result) == 0 )
		{
			printf("The gift code has already been used.");
			DisplayCommonFooter();
			return;
		}
	}

	
	
	printf("<H1><B>New User Signup</B></H1>");
	$sql = "insert into user_tbl (fname, lname, password, email, street, city, state, zip, country) values ('";
	$sql .= $_POST['fname']; 
	$sql .= "', '";
	$sql .= $_POST['lname']; 
	$sql .= "', '";
	$sql .= $_POST['password']; 
	$sql .= "', '";
	$sql .= $_POST['email']; 
	$sql .= "'";
	if ($_POST['streetaddress'])
	{
		$sql .= ", '";
		$sql .= $_POST['streetaddress']; 
		$sql .= "'";
	}
	else
		$sql .= ", null";
	
	if ($_POST['city'])
	{
		$sql .= ", '";
		$sql .= $_POST['city']; 
		$sql .= "'";
	}
	else
		$sql .= ", null";
		
	if ($_POST['state'])
	{
		$sql .= ", '";
		$sql .= $_POST['state']; 
		$sql .= "'";
	}
	else
		$sql .= ", null";
		
	if ($_POST['zip'])
	{
		$sql .= ", '";
		$sql .= $_POST['zip']; 
		$sql .= "'";
	}
	else
		$sql .= ", null";
		
	if ($_POST['country'])
	{
		$sql .= ", '";
		$sql .= $_POST['country']; 
		$sql .= "'";
	}
	else
		$sql .= ", null";
	
	$sql .= ")";
	//printf("%s", $sql);
	mysql_query($sql) or die("Could not create the new user account: " . mysql_error());
	if ($_POST['giftcode'])
	{
		// GET THE NEW USER'S USERID
		$sql = "select id from user_tbl where email = '";
		$sql .= $_POST['email'];
		$sql .= "'";
		$result = mysql_query($sql) or die("Could not get the user information: " . mysql_error());
		if ( mysql_num_rows($result) == 0 )
		{
			printf("%s does not match any email addresses in our system", $_POST['email']);
		}
		$row = mysql_fetch_array($result);
		$id = $row["id"];
		
				
		// MARK THE GIFT CODE AS USED
		$sql = "update giftcode_tbl set used = 1, user_id = $id, date_used = '";
		$sql .= date("Ymd", strtotime("today"));
		$sql .= "' where code = '";
		$sql .= $_POST['giftcode'];
		$sql .= "'";
		//printf("%s", $sql);
		mysql_query($sql) or die("Could not mark the gift code as used: " . mysql_error());
		
		// MARK THE USER AS PAID FOR ONE YEAR
		InsertPayment($id, 0, date("Ymd", strtotime("today")));
		mysql_query($sql) or die("Could not mark the user as paid: " . mysql_error());
		
		// PRINT THE CONFIRMATION
		printf("Your account was created successfully and credited for one year.<br>Click <a href=\"index.php\">here</a> to log in.");
	}
	else
	{
		printf("Account Creation Successful<br>Click <a href=\"index.php\">here</a> to log in.");
	}
	
}
else if ($_GET['SendPassword'])
{
	printf("<form action=\"newuser.php\" method=\"POST\">Please enter the email address you use to sign into StatGolf:<br><br>  <input type=\"text\" name=\"email\"><br><br><input type=\"submit\" name=\"SendPassword\" value=\"Send Me My Password\"></form>");
}
else if ($_POST['SendPassword'])
{
	$email = $_POST['email'];
	ConnectToDB();
	$sql = "select * from user_tbl where lcase(email) = lcase('$email')";
	$result = mysql_query($sql) or die("Could not get the user information: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
	{
		printf("%s does not match any email addresses in our system", $_POST['email']);
	}
	else
	{
		$row = mysql_fetch_array($result);
		$to = $row["email"];
		$subject = "StatGolf Account Info";
		$message = "Your password is:  ";
		$message .= $row["password"];
		SendEmail($to, $subject, $message);
		printf("Your password has been sent to %s.  Click <a href=\"index.php\">here</a> to log in.", $_POST['email']);
	}
}
else
{
	//printf("gift code:  %s", GenerateGiftCode());
?>
	<H1><B>New User Signup</B></H1>
	<form action="newuser.php" method="POST">
	<table>
		<tr><td>First Name:  </td><td><input type="text" MAXLENGTH="45" name="fname"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td>Last Name:  </td><td><input type="text" MAXLENGTH="45" name="lname"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td>Email:  </td><td><input type="text" name="email"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td>Password:  </td><td><input type="password" name="password"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td>Confirm Password:  </td><td><input type="password" name="confirmpassword"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td>Street Address:  </td><td><input type="text" MAXLENGTH="45" name="streetaddress"></td></tr>
		<tr><td>City:  </td><td><input type="text" MAXLENGTH="45" name="city"></td></tr>
		<tr><td>State:  </td><td><input type="text" MAXLENGTH="45" name="state"></td></tr>
		<tr><td>Zip:  </td><td><input type="text" MAXLENGTH="45" name="zip"></td></tr>
		<tr><td>Country:  </td><td><input type="text" MAXLENGTH="45" name="country"></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td><input type="submit" name="AddUser" value="Create My Account"></td></tr>
		<tr><td><span class="RequiredFieldIndicator">&nbsp*</span> indicates a required field.</td></tr>
	</table>
	</form>
<?
}
DisplayCommonFooter();
?>















