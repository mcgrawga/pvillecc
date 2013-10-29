<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	?>
	
		
	
	<H3>Instructions</H3>
	<div id="paragraphtext">
	<p>
	To begin using the handicap calculator just enter a <a href="addcourse.php">golf course</a> using the scorecard from that course.  
	Once a course has been added you can begin <a href="addscore.php">entering scores</a> for rounds played at that course.  As you play more courses you will add them the same way. You can have multiple tees 
	per course so be sure to select the correct tee when entering a score.  
	</p>
	<br>
	<p>
	Once 5 scores have been entered, your handicap will be calculated and displayed on the home page.  The <font color="red">red</font> highlighted 
	entries indicate the scores that were used to calculate your handicap.  You can also
	<a href="coursestats.php">view your statistics</a> or manage <a href="accountadmin.php">your account</a>.
	</p>
	</div>

	<?
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
















