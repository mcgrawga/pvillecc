<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include("phpgraphlib.php"); 
include 'functions.php';

	$graph=new PHPGraphLib(475,300);
	ConnectToDB();


	$graphData = array();
	//$graphData = getLastScores($_SESSION['userid'], 12);
	
	$TeeId = $_GET['TeeId'];
	$NumScores = $_GET['NumScores'];
	
	
	$graphData = getLastScores($_SESSION['userid'], $NumScores, $TeeId);
	$graph->addData($graphData);
		
	$maxRange = getMaxArrayValue($graphData);
	$minRange = getMinArrayValue($graphData);
	setRangeValues($minRange, $maxRange, .05);
	$graph->setRange($maxRange, $minRange);
	
	$graph->setupXAxis(25);
	$graph->setDataValues(true);
	$title = getCourseAndTeeName($TeeId);
	$graph->setTitle("Last $NumScores Rounds at $title");
	$graph->setGradient("lime", "green");
	$graph->createGraph();

?>