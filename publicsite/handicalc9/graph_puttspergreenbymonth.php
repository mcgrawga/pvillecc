<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include("phpgraphlib.php"); 
include 'functions.php';

	$graph=new PHPGraphLib(475,300);
	ConnectToDB();

	
	$graphData = array();
	$graphData = getAvgPuttsPerGreenByMonth($_SESSION['userid']);
	$graph->addData($graphData);
		
	$maxRange = getMaxArrayValue($graphData);
	$minRange = getMinArrayValue($graphData);
	setRangeValues($minRange, $maxRange, .10);
	$graph->setRange($maxRange, $minRange);
	
	$graph->setLine(true);
	$graph->setBars(false);
	$graph->setGoalLine((array_sum($graphData)/count($graphData)));
	$graph->setGoalLineColor("red");
	$graph->setupXAxis(25);
	$graph->setDataValues(true);
	//$graph->setTitle("Average Putts Per Green (TTM)");
	$graph->setGradient("lime", "green");
	$graph->setLineColor("green");
	$graph->createGraph();

?>