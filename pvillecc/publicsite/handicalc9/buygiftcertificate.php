<?
include 'functions.php';
ConnectToDB();
DisplayGeneralPublicHeader();
?><h1>Purchase Gift Certificate</h1><?


if ($_POST['BuyGiftCertificate'])
{
	//
	//  FORM VALIDATION
	//
	if ( !IsEmailValid($_POST['email']) )
	{
		DisplayCommonFooter();
		return;
	}
	ConnectToDB();
	$GiftCode = 0;
	$GiftCode = InsertGiftCode($_POST['email']);
	if (!$GiftCode)
	{
		printf("Could not create the gift certificate gift code.");
		DisplayCommonFooter();
		return;
	}
	else
	{
		?>
		<div id="paragraphtext">
			Click the "Pay Now" button below to purchase a gift certificate for a one year membership to StatGolf.  
			<?printf("The price of the gift certificate is $%s.  Payment confirmation and a gift code will be sent to <b>%s</b>.  ", getConfigValue("ANNUAL_FEE"), $_POST['email']);?>
			<br><br>
			When payment is complete you will be returned to StatGolf and you may print the gift certificate.  Your
			email confirmation will also contain a link to the gift certificate so you can print it later.  
			To receive credit for the gift certificate, the gift code is entered when signing up for a new account or renewing an existing membership.<br><br>
			Our payment processor is Paypal but you do not have to have an account with them to make a payment.  On the payment page there is a link to use a credit card or bank account.<br><br>
		</div>
		<?


		include 'paymentbutton_gc.php';
	}
}
else
{
?>
	<div id="paragraphtext">
		<form action="buygiftcertificate.php" method="POST">
		<?printf("The price of the gift certificate is $%s.  The gift certificate is good for a one year membership to Statgolf.", getConfigValue("ANNUAL_FEE"));?>
		Please enter the email address where you would like to receive the gift certificate.<br>
		<br><input type="text" name="email"><span class="RequiredFieldIndicator">&nbsp*</span>
		<br><input type="submit" name="BuyGiftCertificate" value="Go To Next Step">
		<br><span class="RequiredFieldIndicator">&nbsp*</span> indicates a required field.
		</form>
	</div>
<?
}
DisplayCommonFooter();
?>
