<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{

	$TeeID = $_GET['teeid'];
	DisplayCommonHeader();
	printf("<h3>%s</h3>", getCourseAndTeeName($TeeID));
	
		if (count(getLastScores($_SESSION['userid'], 12, $TeeID)) < 5)
			printf("<br><br><h3>Recent Scores</h3><span class=\"NineHoleScoreFont\">Not enough data, must have 5 scores</span><br><br>");
		else
			printf("<br><br><h3>Recent Scores</h3><img src=\"graph_lastscores.php?teeid=$TeeID&num=12\" />");
			
		$NumScores = 12;
		$LastFewScores = array();
		$LastFewScores = getDatesAndIds($_SESSION['userid'], $NumScores, $TeeID);
		
		
		//
		//  FAIRWAYS HIT
		//
		//PrintArray($LastFewScores);
		$graphData = array();
		foreach( $LastFewScores as $key => $value)
		{
				$val = getPercentFairways($value);
				if ($val != "N/A")
					$graphData[$key] = $val;
		}
		//PrintArray($graphData);

    		if (count($graphData) < 5)
    			printf("<br><br><h3>%% Fairways Hit</h3><span class=\"NineHoleScoreFont\">Not enough data, must have recorded fairways hit 5 times</span><br><br>");
    		else
    			printf("<br><br><h3>%% Fairways Hit</h3><img src=\"graph_fairwayshit.php?teeid=$TeeID&num=12\" />");
        
			
			
		//
		//  GREENS IN REGULATION
		//
		//PrintArray($LastFewScores);
		$graphData = array();
		reset($LastFewScores);
		foreach( $LastFewScores as $key => $value)
		{
				$val = getPercentGIR($value);
				if ($val != "N/A")
					$graphData[$key] = $val;
		}
		//PrintArray($graphData);
    		if (count($graphData) < 5)
    			printf("<br><br><h3>%% Greens in Regulation</h3><span class=\"NineHoleScoreFont\">Not enough data, must have recorded greens in regulation 5 times</span><br><br>");
    		else
    			printf("<br><br><h3>%% Greens in Regulation</h3><img src=\"graph_gir.php?teeid=$TeeID&num=12\" />");
			
			
		//
		//  PUTTS PER GREEN
		//
		//PrintArray($LastFewScores);
		$graphData = array();
		reset($LastFewScores);
		foreach( $LastFewScores as $key => $value)
		{
				$val = getPuttsPerGreen($value);
				if ($val != "N/A")
					$graphData[$key] = $val;
		}
		//PrintArray($graphData);
    		if (count($graphData) < 5)
    			printf("<br><br><h3>Putts Per Green</h3><span class=\"NineHoleScoreFont\">Not enough data, must have recorded putts 5 times</span><br><br>");
    		else
    			printf("<br><br><h3>Putts Per Green</h3><img src=\"graph_puttspergreen.php?teeid=$TeeID&num=12\" />");
			
		//
		//  PENALTIES PER NINE PER ROUND
		//
		//PrintArray($LastFewScores);
		$graphData = array();
		reset($LastFewScores);
		foreach( $LastFewScores as $key => $value)
		{
				$val = getPenaltiesPerNineForRound($value);
				if ($val != "N/A")
					$graphData[$key] = $val;
		}
		//PrintArray($graphData);
    		if (count($graphData) < 5)
    			printf("<br><br><h3>Penalties Per Nine</h3><span class=\"NineHoleScoreFont\">Not enough data, must have recorded penalties 5 times</span><br><br>");
    		else
    			printf("<br><br><h3>Penalties Per Nine</h3><img src=\"graph_penaltiespernine.php?teeid=$TeeID&num=12\" />");
			
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















