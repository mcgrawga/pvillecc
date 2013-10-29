<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include("phpgraphlib.php"); 
include 'functions.php';

	$graph=new PHPGraphLib(475,300);
	ConnectToDB();


	$graphData = array();
	$graphData = getLastTenScores($_SESSION['userid']);
	$graph->addData($graphData);
	//$graph->setGradient("red", "maroon");
	//$graph->setGradient("lime", "green");
	$graph->setGradient("lime", "green");
	//$graph->setGradient("aqua", "teal");
	//$graph->setGradient("aqua", "gray");
	$graph->createGraph();

?>