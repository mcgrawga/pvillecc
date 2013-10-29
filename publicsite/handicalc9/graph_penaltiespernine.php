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
			$val = getPenaltiesPerNineForRound($value);
			if ($val != "N/A")
				$graphData[$key] = $val;
	}
	
	$graph->addData($graphData);
		
	$maxRange = getMaxArrayValue($graphData);
	//$minRange = getMinArrayValue($graphData);
    $minRange = 0;
	/*
	if ($maxRange == 0 || $minRange == 0)
	{
		$maxRange == 1; 
		$minRange == 0;
	}
	else
		setRangeValues($minRange, $maxRange, .05);
	*/    
	$graph->setRange($maxRange, $minRange);
	
	
	$graph->setupXAxis(25);
	$graph->setDataValues(true);
	$graph->setGradient("lime", "green");
	$graph->createGraph();

?>