<?php
header('Content-Type: text/xml');
header("Cache-Control: no-cache, must-revalidate");
//A date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
include 'functions.php';
ConnectToDB();

//
// SELECT COURSES
//
//$sql = "select distinct(Upper(ct.name)) as name, Upper(ct.city) as city, ct.id as course_id from state_tbl st, course_tbl ct where (UPPER(st.abbr) = UPPER(ct.state) or UPPER(st.name) = UPPER(ct.state)) and st.id = ";
$sql = "select * from course_tbl ct where state = ";
$sql .= $_GET["id"] ;
$sql .= " group by ct.name order by ct.name asc;";
$result = mysql_query($sql);

//
// CONVERT TO XML AND PRINT
//
printf(ResultSetToXML($result));

?>
