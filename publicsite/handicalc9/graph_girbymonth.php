<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include("phpgraphlib.php"); 
include 'functions.php';

	$graph=new PHPGraphLib(475,300);
	ConnectToDB();

	$graphData = array();
	$graphData = getPercentGreensInRegulationByMonth($_SESSION['userid']);
	$graph->addData($graphData);
		
	$maxRange = getMaxArrayValue($graphData);
	$minRange = getMinArrayValue($graphData);
	setRangeValues($minRange, $maxRange, .05);
	$graph->setRange($maxRange, $minRange);
	
	
	$graph->setupXAxis(25);
	$graph->setDataValues(true);
	//$graph->setTitle("% Greens In Regulation (TTM)");
	$graph->setGradient("lime", "green");
	$graph->createGraph();

?>