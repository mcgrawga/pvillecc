

	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="payments@statgolf.com">
	<input type="hidden" name="item_name" value="StatGolf Annual Fee">
	<?
			printf("<input type=\"hidden\" name=\"amount\" value=\"%s.00\">", getConfigValue("ANNUAL_FEE"));
			//<input type="hidden" name="amount" value="35.00">
	?>
	<input type="hidden" name="shipping" value="0.00">
	<input type="hidden" name="no_shipping" value="0">
		<?
			printf("<input type=\"hidden\" name=\"return\" value=\"http://www.statgolf.com/successfulpaymentgc.php?email=%s&gc=%s\">", $_POST['email'], $GiftCode);
			// <input type="hidden" name="return" value="http://www.statgolf.com/successfulpayment.php">
		?>
	<input type="hidden" name="cancel_return" value="http://www.statgolf.com/cancelpayment.php">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0.00">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="bn" value="PP-BuyNowBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynow_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	
