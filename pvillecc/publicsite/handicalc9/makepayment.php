<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';



function DisplayPage()
{
	DisplayOnlyBanner();
	ConnectToDB();
	?>
	
	<table class="InstructionsTable">
	<td>
	
	<p>
	<?printf("The annual StatGolf fee is $%s.<br>  Paying now will extend your account through %s", getConfigValue("ANNUAL_FEE"), GetNextPaidThroughDate());?>
	</p>
	
	<br>
	<?include 'paymentbutton.php';?>
	
	<p>
	Our payment processor is Paypal but you do not have to have an account with them to make a payment.  On the payment page there is a link to use a credit card or bank account.
	</p>
	</td>
	</table>
	<?
	DisplayCommonFooter();
}

	if ($_POST['LoginUser'])		// Check to see if we should login user
	{
		if ( ValidateCredentials($_POST['UserName'], $_POST['Password']) )
			DisplayPage();
	}
	else if (isset($_SESSION['userid']))	// Already logged in?
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
