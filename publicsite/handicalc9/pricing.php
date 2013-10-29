<?
include 'functions.php';
ConnectToDB();
DisplayGeneralPublicHeader();
?>

<div id="paragraphtext">
<h1>Pricing</h1>
<?
	printf("The annual fee for StatGolf is $%s.  ", getConfigValue("ANNUAL_FEE"));
?>
If you would like a refund within 30 days of paying the annual fee just email us at <a href="mailto:customercare@statgolf.com">customercare@statgolf.com</a>.  
We will issue a refund no questions asked.  Go ahead and <A HREF="newuser.php">join now.</A>

</div>
<?
DisplayCommonFooter();
?>
