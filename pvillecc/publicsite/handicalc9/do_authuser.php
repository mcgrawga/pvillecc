<?

if ((!$username) || (!$password)) {
	header("Location: http://localhost/index.html");
	exit;
} 


$db_name = "statgolf_teamdb";
$table_name = "people";

$connection = @mysql_connect("localhost", "statgolf_jon", "swan") 
	or die("Couldn't connect.");

mysql_select_db ("statgolf_teamdb"); 



$sql = "SELECT * FROM $table_name
	WHERE username = \"$username\" AND password = password(\"$password\")
	"; 

$result = mysql_query($sql) 
        or die ("Can't execute query."); 

$num = mysql_numrows($result); 

if ($num != 0) { 

	$msg = "<P>Congratulations, you're authorized!</p>";

} else { 

	header("Location: http://localhost/index.html");
	exit;
} 

?> 

<HTML>
<HEAD>
<TITLE>Secret Area</TITLE>
</HEAD>
<BODY>

<? echo "$msg"; ?>

</BODY>
</HTML>