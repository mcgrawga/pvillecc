<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<h3>Overall Statistics</h3><br>");
	DisplayQuickStats($_SESSION['userid']);
	
	
	
	
	//printf("<img src=\"colortester.php\" />");
	
		if (count(getLastScores($_SESSION['userid'], 12)) < 5)
			printf("<br><br><h3>Recent Scores</h3><span class=\"NineHoleScoreFont\">Not Enough Data, Must Have 5 Scores</span>");
		else
			printf("<br><br><h3>Recent Scores</h3><img src=\"graph_lastscores.php?num=12\" />");
			
		if (count(getScoreHistory($_SESSION['userid'])) < 15)
			printf("<br><br><h3>Long Term Trend</h3><span class=\"NineHoleScoreFont\">Not Enough Data, Must Have 15 Scores</span>");
		else
			printf("<br><br><h3>Long Term Trend</h3><img src=\"graph_careertrend.php\" />");
			
		if (count(getAvgScoreByMonth($_SESSION['userid'])) < 5)
			printf("<br><br><h3>Average Score By Month</h3><span class=\"NineHoleScoreFont\">Not Enough Data, Must Have 5 Months Of Scores</span>");
		else
			printf("<br><br><h3>Average Score By Month</h3><img src=\"graph_scorebymonth.php\" />");
			
        $p = getPercentFairwaysHitByMonth($_SESSION['userid']);
        if (array_sum($p) > 0)
        {
    		if (count($p) < 5)
    			printf("<br><br><h3>%% Fairways Hit By Month</h3><span class=\"NineHoleScoreFont\">Not Enough Data, Must Have 5 Months Of Scores</span>");
    		else
    			printf("<br><br><h3>%% Fairways Hit By Month</h3><img src=\"graph_fairwayshitbymonth.php\" />");
		}

        
		if (count(getAvgPuttsPerGreenByMonth($_SESSION['userid'])) < 5)
			printf("<br><br><h3>Putts Per Green By Month</h3><span class=\"NineHoleScoreFont\">Not Enough Data, Must Have 5 Months Of Scores</span>");
		else
			printf("<br><br><h3>Putts Per Green By Month</h3><img src=\"graph_puttspergreenbymonth.php\" />");
		
        $p = getPenaltiesPerNineHolesByMonth($_SESSION['userid']);
        if (array_sum($p) > 0)
        {
    		if (count($p) < 5)
    			printf("<br><br><h3>Penalties Per Round By Month</h3><span class=\"NineHoleScoreFont\">Not Enough Data, Must Have 5 Months Of Scores</span>");
    		else
    			printf("<br><br><h3>Penalties Per Round By Month</h3><img src=\"graph_penaltiesbymonth.php\"/>");
        }
        
        $a = getPercentGreensInRegulationByMonth($_SESSION['userid']);
        if (array_sum($a) > 0)
        {
    		if (count($a) < 5)
    			printf("<br><br><h3>%% Greens In Regulation By Month</h3><span class=\"NineHoleScoreFont\">Not Enough Data, Must Have 5 Months Of Scores</span>");
    		else
    			printf("<br><br><h3>%% Greens In Regulation By Month</h3><img src=\"graph_girbymonth.php\" />");
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















