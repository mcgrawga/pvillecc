<?php 
include 'functions.php';
ConnectToDB();
DisplayGeneralPublicHeader();
	
	if ( !$_GET['email'] || !$_GET['gc'])
	{
		print("<br>There is a problem with how you are accessing the page.");
		DisplayCommonFooter();
		return;
	}
		
	if ( $_GET['email'] == "NO_GO")
	{
		PrintGiftCertificate($_GET['gc']);
	}
	else if ( MarkGiftCodeAsPaid($_GET['gc']) )
	{
		PrintGiftCertificate($_GET['gc']);
		$to = $_GET['email'];
		$subject = "StatGolf Gift Certificate";
		$message = "This certificate is good for a 1 year membership to StatGolf, the premier golf handicap calculator and game analysis tool on the web.\r\n\r\n";
		$message .= "Use the following gift code when signing up or renewing an existing membership:  ";
		$message .= $_GET['gc'];
		$message .= "\r\n\r\nWe appreciate your business and hope you enjoy StatGolf.  If you have any questions or concerns please drop us a line at customercare@statgolf.com.";
		SendEmail($to, $subject, $message);
	}
	else
	{
		$to = $_GET['email'];
		$subject = "StatGolf Gift Certificate";
		$message = "This certificate is good for a 1 year membership to StatGolf, the premier golf handicap calculator and game analysis tool on the web.\r\n\r\n";
		$message .= "Use the following gift code when signing up or renewing an existing membership:  ";
		$message .= $_GET['gc'];
		$message .= "\r\n\r\nWe appreciate your business and hope you enjoy StatGolf.  If you have any questions or concerns please drop us a line at customercare@statgolf.com.";
		SendEmail($to, $subject, $message);
		?>
			We have sent you your gift certificate, but there was a problem recording your payment at StatGolf.  One of our staff will remedy the situation
			within 24 hours and contact you at <?printf("<b>%s</b>.  ", $_GET['email']);?>
			when complete.<br><br>  We appreciate your business and hope you enjoy StatGolf.  If you have
			any questions or concerns please contact us through our feedback <a href="comments.php">form</a> or drop us a line at
			<a href="mailto:customercare@statgolf.com">customercare@statgolf.com</a>
		<?
	}
	DisplayCommonFooter();


?>
