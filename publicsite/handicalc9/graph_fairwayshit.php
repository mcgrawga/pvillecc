<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include("phpgraphlib.php"); 
include 'functions.php';

	$graph=new PHPGraphLib(475,300);
	ConnectToDB();
	
	$NumScores = $_GET[num];
	$TeeID = $_GET[teeid];
	$CourseName = getCourseAndTeeName($TeeID);
	$LastFewScores = array();
	$LastFewScores = getDatesAndIds($_SESSION['userid'], $NumScores, $TeeID);
	$graphData = array();
	foreach( $LastFewScores as $key => $value)
	{
			$val = getPercentFairways($value);
			if ($val != "N/A")
				$graphData[$key] = $val;
	}

	$graph->addData($graphData);
	/*	
	$maxRange = getMaxArrayValue($graphData);
	$minRange = getMinArrayValue($graphData);
	setRangeValues($minRange, $maxRange, .05);
    */
    $maxRange = 100;
    $minRange = 0;
    
	$graph->setRange($maxRange, $minRange);
	
	
	$graph->setupXAxis(25);
	$graph->setDataValues(true);
	$graph->setGradient("lime", "green");
	$graph->createGraph();

?>