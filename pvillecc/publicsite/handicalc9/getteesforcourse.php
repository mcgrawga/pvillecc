<?php
header('Content-Type: text/xml');
header("Cache-Control: no-cache, must-revalidate");
//A date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
include 'functions.php';
ConnectToDB();

//
// SELECT TEES
//
$sql = "select * from tee_tbl where courseid = ";
$sql .= $_GET["id"];
$result = mysql_query($sql);

//
// CONVERT TO XML AND PRINT
//
printf(ResultSetToXML($result));

?>
