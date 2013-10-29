<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';



function DisplayPage()
{
	DisplayCommonHeader();
	
	// ------------------------------------
	print '$_POST array';
	// ------------------------------------
	print "<BR/>";
	// ------------------------------------
	foreach ( $_POST as $key => $value ) {
	 print $key . " " . "=" . " " . $value;
	 print "<BR/>";
	}
	// ------------------------------------
	print "<BR/>";
	// ------------------------------------
	print "<BR/>";
	// ------------------------------------
	print '$_GET array';
	// ------------------------------------
	print "<BR/>";
	// ------------------------------------
	foreach ( $_GET as $key => $value ) {
	 print $key . " " . "=" . " " . $value;
	 print "<BR/>";
	}
	// ------------------------------------
	print "<BR/>";
	// ------------------------------------
	print "<BR/>";
	// ------------------------------------
	print '$_REQUEST array';
	// ------------------------------------
	print "<BR/>";
	// ------------------------------------
	foreach ( $_REQUEST as $key => $value ) {
	 print $key . " " . "=" . " " . $value;
	 print "<BR/>";
	}
	// ------------------------------------

	
	
	if ( !$_GET['uid'] ||  !$_GET['amt'] ||  !$_GET['effdt'])
		die("<br>There is a problem with how you are accessing the page.");
		
	
	
	if ( InsertPayment($_GET['uid'], $_GET['amt'], $_GET['effdt']) )
	{
		?>
			<table class="InstructionsTable">
			<td>
			<p>
			<br>Thank you for your payment. Your transaction has been completed, and a receipt for your purchase has been emailed to you.  
			We appreciate your business and hope you enjoy StatGolf.  If you have
			any questions or concerns please contact us through our feedback <a href="comments.php">form</a> or drop us a line at
			<a href="mailto:customercare@statgolf.com">customercare@statgolf.com</a>.  You may also log into 
			<a href="http://www.paypal.com">www.paypal.com</a> to view the details of this transaction.
			</p>
			</td>
			</table>
		<?
	}
	else
	{
		?>
			<table class="InstructionsTable">
			<td>
			<p>
			<br>There was a problem recording your payment at StatGolf.  One of our staff will grant you access
			within 24 hours.  We appreciate your business and hope you enjoy StatGolf.  If you have
			any questions or concerns please contact us through our feedback <a href="comments.php">form</a> or drop us a line at
			<a href="mailto:customercare@statgolf.com">customercare@statgolf.com</a>
			</p>
			</td>
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
