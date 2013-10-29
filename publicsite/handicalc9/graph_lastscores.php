<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include("phpgraphlib.php"); 
include 'functions.php';

	$graph=new PHPGraphLib(475,300);
	ConnectToDB();

	$NumScores = 12;  // DEFAULT TO 12
	if ($_GET[num])
		$NumScores = $_GET[num];
	
	$TeeID = "";  // OPTIONAL
	if ($_GET[teeid])
		$TeeID = $_GET[teeid];
		
	
	
	$graphData = array();
	$graphData = getLastScores($_SESSION['userid'], 12);
	//$graphData = getLastScores($_SESSION['userid'], $NumScores, $TeeID);
	$graph->addData($graphData);
		
	$maxRange = getMaxArrayValue($graphData);
	$minRange = getMinArrayValue($graphData);
	setRangeValues($minRange, $maxRange, .05);
	$graph->setRange($maxRange, $minRange);
	
	$graph->setupXAxis(25);
	$graph->setDataValues(true);
	//$graph->setTitle("Last 12 Rounds");
	$graph->setGradient("lime", "green");
	$graph->createGraph();

?>