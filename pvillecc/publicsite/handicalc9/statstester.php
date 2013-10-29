<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<br><br><h3>Course Statistics.</h3>");
	
	
	ConnectToDB();
	
	printf("Max Score:  %s<br>", getMaxScore($_SESSION['userid']));
	printf("Avg Score:  %s<br>", getAvgScore($_SESSION['userid']));
	printf("Min Score:  %s<br>", getMinScore($_SESSION['userid']));
	printf("Avg Putts Per Green:  %s<br>", getAvgPuttsPerGreen($_SESSION['userid']));
	printf("Percent fairways hit:  %s<br>", getPercentFairwaysHit($_SESSION['userid']));
	printf("Percent greens in regulation:  %s<br>", getPercentGreensInRegulation($_SESSION['userid']));
	printf("Penalties per 9 holes:  %s<br>", getPenaltiesPerNineHoles($_SESSION['userid']));
	printf("Penalties per 9 holes:  %s<br>", getPenaltiesPerNineHoles($_SESSION['userid']));
	
	
	$min = getMinArrayValue(getLastTenScores($_SESSION['userid']));;
	$max = getMaxArrayValue(getLastTenScores($_SESSION['userid']));;
	
	//$min = getMinArrayValue(getScoreHistory($_SESSION['userid']));;
	//$max = getMaxArrayValue(getScoreHistory($_SESSION['userid']));;
	/*
	printf("10 smaller:  %s<br>", $min);
	printf("10 bigger:  %s<br>", $max);
	setRangeValues($min, $max, .1);
	printf("10 smaller:  %s<br>", $min);
	printf("10 bigger:  %s<br>", $max);
	*/
	
	
	//print_r(getAvgScoreByMonth($_SESSION['userid']));
	//print_r(getAvgPuttsPerGreenByMonth($_SESSION['userid']));
	//print_r(getPercentFairwaysHitByMonth($_SESSION['userid']));
	//print_r(getPenaltiesPerNineHolesByMonth($_SESSION['userid']));
	//print_r(getPercentGreensInRegulationByMonth($_SESSION['userid']));
	print_r(getLastTenScores($_SESSION['userid']));
	print_r(getScoreHistory($_SESSION['userid']));
	
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















