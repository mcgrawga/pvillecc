<?php 
	session_start(); 
	header("Cache-control: private"); // IE 6 Fix. 
	include("phpgraphlib.php"); 
	include 'functions.php';
	ConnectToDB();


	$graph=new PHPGraphLib(475,300);
	$graphData = array();
	$graphData = getScoreHistory($_SESSION['userid']);
	$graph->addData($graphData);
	
	$maxRange = getMaxArrayValue($graphData);
	$minRange = getMinArrayValue($graphData);
	setRangeValues($minRange, $maxRange, .05);
	$graph->setRange($maxRange, $minRange);
	
	
	$graph->setBars(false);
	$graph->setLine(true);
	$graph->setXValues(false);
	$graph->setupXAxis(25);
	//$graph->setTitle("Long Term Trend (Max 100 Scores)");
	$graph->setGradient("lime", "green");
	$graph->setLineColor("green");
	$graph->createGraph();
?>