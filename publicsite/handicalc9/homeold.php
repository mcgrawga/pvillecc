<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
    $HandicapIndex = GetOfficialHandicapIndex();
    if ($HandicapIndex < 0)
    {
        $HandicapIndex *= -1;
        $HandicapIndex = "+ " . $HandicapIndex;
    }
	printf("<h3>%s's USGA Handicap Index:  <span class=\"RequiredFieldIndicator\">%s</span></h3>",$_SESSION['username'],$HandicapIndex);
    printf("<h3>Index (if calculated today):  <span class=\"RequiredFieldIndicator\">%s</span></h3>",GetCurrentHandicapIndex());
	
	$sql = "select scores.*, 
		courses.name as name, 
		courses.city as city, 
		courses.state as state,
		tees.name as teename 
		from course_tbl courses, score_tbl scores, tee_tbl tees where courses.userid = ";
	$sql .= $_SESSION['userid']; 
	$sql .= " and tees.courseid = courses.id and scores.teeid = tees.id";
	if ( $_POST['dofilter'] )
	{
		//
		// FROM DATE
		//
		$fromDate = sprintf("%u",$_POST["TheYearfrom"]); // YEAR
		if ($_POST["TheMonthfrom"] < 10)
			$fromDate .= sprintf("0%u",$_POST["TheMonthfrom"]); // MONTH
		else
			$fromDate .= sprintf("%u",$_POST["TheMonthfrom"]); // MONTH
		if ($_POST["TheDayfrom"] < 10)
			$fromDate .= sprintf("0%u",$_POST["TheDayfrom"]); // DAY
		else
			$fromDate .= sprintf("%u",$_POST["TheDayfrom"]); // DAY
	
		$sql .= " and scores.dateplayed >= ";
		$sql .= $fromDate;


		//
		// TO DATE
		//	
		$toDate = sprintf("%u",$_POST["TheYearto"]); // YEAR
		if ($_POST["TheMonthto"] < 10)
			$toDate .= sprintf("0%u",$_POST["TheMonthto"]); // MONTH
		else
			$toDate .= sprintf("%u",$_POST["TheMonthto"]); // MONTH
		if ($_POST["TheDayto"] < 10)
			$toDate .= sprintf("0%u",$_POST["TheDayto"]); // DAY
		else
			$toDate .= sprintf("%u",$_POST["TheDayto"]); // DAY

		$sql .= " and scores.dateplayed <= ";
		$sql .= $toDate;
		

		if ( $_POST['teeid'] != "Any" )
		{
			$sql .= " and scores.teeid = ";
			$sql .= $_POST['teeid'];
		}
		if ( $_POST['scores'] != "Any" )
		{
			$sql .= " and scores.score ";
			$sql .= $_POST['compOper'];
			$sql .= " ";
			$sql .= $_POST['scores'];
		}
		
		// save filtered sql to session
		$_SESSION['filtersql'] = $sql;
		
		// save filter fields to session
		$_SESSION['fromdt'] = $fromDate;
		$_SESSION['todt'] = $toDate;
		$_SESSION['teeid'] = $_POST['teeid'];
		$_SESSION['scores'] = $_POST['scores'];
		$_SESSION['compOper'] = $_POST['compOper'];
	}
	else if ( $_SESSION['filtersql'] && ($_GET["datesort"] || $_GET["coursesort"] || $_GET["scoresort"])  )
		$sql = $_SESSION['filtersql'];
	
	if ( $_GET["datesort"] == "asc")
		$sql .= " order by scores.dateplayed asc";
	else if ( $_GET["datesort"] == "desc")
		$sql .= " order by scores.dateplayed desc";
	else if ( $_GET["coursesort"] == "asc")
		$sql .= " order by name asc";
	else if ( $_GET["coursesort"] == "desc")
		$sql .= " order by name desc";
	else if ( $_GET["scoresort"] == "asc")
		$sql .= " order by score asc";
	else if ( $_GET["scoresort"] == "desc")
		$sql .= " order by score desc";
	else 
		$sql .= " order by scores.dateplayed desc";		// default
	
	//printf("%s", $sql);
	$result = mysql_query($sql) or die("Could not get a list of scores: " . mysql_error());
	
	if ( mysql_num_rows($result) == 0 )
		printf("No scores to report.  <br>Click <a href=\"addscore.php\">here</a> to enter scores.");
	else
	{
		printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">");
		printf("<tr><td class=\"ScoreHistoryTDHeader\">Count</td><td class=\"ScoreHistoryTDHeader\">");
		if ( $_GET["datesort"] == "asc")
			printf("<a href=\"home.php?datesort=desc\">Date</a></td>");
		else
			printf("<a href=\"home.php?datesort=asc\">Date</a></td>");
		
		if ( $_GET["coursesort"] == "asc")	
			printf("<td class=\"ScoreHistoryTDHeader\"><a href=\"home.php?coursesort=desc\">Golf Course</a></td>");
		else
			printf("<td class=\"ScoreHistoryTDHeader\"><a href=\"home.php?coursesort=asc\">Golf Course</a></td>");
			
		if ( $_GET["scoresort"] == "asc")	
			printf("<td class=\"ScoreHistoryTDHeader\">Front</td><td class=\"ScoreHistoryTDHeader\">Back</td><td class=\"ScoreHistoryTDHeader\"><a href=\"home.php?scoresort=desc\">Total</a></td></tr>");
		else
			printf("<td class=\"ScoreHistoryTDHeader\">Front</td><td class=\"ScoreHistoryTDHeader\">Back</td><td class=\"ScoreHistoryTDHeader\"><a href=\"home.php?scoresort=asc\">Total</a></td></tr>");
		$rowCnt = 0;
		
		$handiIDArray = array();
		$handiIDArray = getHandiIDArray();
		
		
		
		//printf("<BR>");
		
		/*
		for ( $i = 0; $i < count($handiIDArray); $i++ )
		{
			list($key, $val) = each($handiIDArray);
			printf("%s<br>", $val);
		}
		reset($handiIDArray);
		*/
		
		//print_r($handiIDArray);
		
		$numRounds = 1;
		while ($row = mysql_fetch_array($result))
		{
			$dbDate = $row["dateplayed"];
			$formattedDate = substr($dbDate, 5, 2);
			$formattedDate .= "/";
			$formattedDate .= substr($dbDate, 8, 2);
			$formattedDate .= "/";
			$formattedDate .= substr($dbDate, 0, 4);
			$formattedDate .= "  ";
			$front9 = calcFront($row);
			$back9 = calcBack($row);
			if ( $front9 == 0 )
				$front9 = "N/A";
			else if ( $back9 == 0 )
				$back9 = "N/A";
			$courseNm = $row["name"];
			$courseNm .= "  ";
			$classname = ($rowCnt % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
			
		
			if ( in_array($row["id"], $handiIDArray) )
				printf("<tr class=\"$classname\"><td>%s</td><td><A HREF=\"scoreadmin.php?scoreID=%s\">%s</a></td><td>%s(%s)</td><td>%s</td><td>%s</td><td><font color=\"red\">%s</font></td></tr>",$numRounds, $row["id"],$formattedDate,$courseNm, $row["teename"], $front9, $back9, $row["score"]);
			else
				printf("<tr class=\"$classname\"><td>%s</td><td><A HREF=\"scoreadmin.php?scoreID=%s\">%s</a></td><td>%s(%s)</td><td>%s</td><td>%s</td><td>%s</td></tr>",$numRounds, $row["id"],$formattedDate,$courseNm, $row["teename"], $front9, $back9, $row["score"]);
			$rowCnt++;
			$numRounds++;
		}
		printf("</table>");
		

		
		printf("<br>A <font color=\"red\">red</font> score means that it has been used to calculate your current handicap.<br>");
		printf("To learn about how a handicap is calculated go <a href=\"http://www.usga.org/handicap/index.html#\">here</a>.");
	}
	DisplayCommonFooter();
}




	if ($_POST['LoginUser'])		// Check to see if we should login user
	{
		if ( ValidateCredentials($_POST['Email'], $_POST['Password']) )
			DisplayPage();
	}
	else if (isset($_SESSION['userid']) && isset($_SESSION['paidup']))	// Already logged in and account current?
	{
		DisplayPage();
	}
	else		// Make them login
	{
		?>
		<meta http-equiv="Refresh" content="0; URL=./index.php">
		<?
	}
?>

















<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
    $HandicapIndex = GetOfficialHandicapIndex();
    if ($HandicapIndex < 0)
    {
        $HandicapIndex *= -1;
        $HandicapIndex = "+ " . $HandicapIndex;
    }
	printf("<h3>%s's USGA Handicap Index:  <span class=\"RequiredFieldIndicator\">%s</span></h3>",$_SESSION['username'],$HandicapIndex);
    printf("<h3>Index (if calculated today):  <span class=\"RequiredFieldIndicator\">%s</span></h3>",GetCurrentHandicapIndex());
	
    $uid .= $_SESSION['userid']; 
	$sql = "select os.type, st.officialscoreid, DATE_FORMAT(st.dateplayed, '%b %d %Y') as dateplayed, Concat(ct.name, ' (', tt.name, ')') as coursename, os.total
            from score_tbl st left join tools_officialscore os on st.officialscoreid = os.id, tee_tbl tt, course_tbl ct
            where st.userid = $uid and st.teeid = tt.id and tt.courseid = ct.id
            order by st.dateplayed desc";
	
	//printf("%s", $sql);
	$result = mysql_query($sql) or die("Could not get a list of scores: " . mysql_error());
	
	if ( mysql_num_rows($result) == 0 )
		printf("No scores to report.  <br>Click <a href=\"addscore.php\">here</a> to enter scores.");
	else
	{
        // load up an array full of scores
        $ScoreArray = array();
        while ($row = mysql_fetch_array($result))
            $ScoreArray[] = $row;
            
       printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">");
        // Table Header
		printf("<tr>
                <td class=\"ScoreHistoryTDHeader\">&nbsp</td>
                <td class=\"ScoreHistoryTDHeader\">Date</td>
                <td class=\"ScoreHistoryTDHeader\">Golf Course</td>
                <td class=\"ScoreHistoryTDHeader\">Score</td>
                </tr>
              ");
		printf("<a href=\"home.php?datesort=desc\">");
        
        
		for ($i=0; $i < count($ScoreArray); $i++)
		{
            $RowCount = $i+1;
            $classname = ($RowCount % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
            extract($ScoreArray[$i]);

                printf("<tr class=\"$classname\"><td>$RowCount</td><td>$dateplayed</td><td>$coursename</td><td>$total</td><td>$officialscoreid</td><td>$type</td></tr>");
		}
		printf("</table><br><br>");
        
 
        
        
        // Move combined 9 hole scores next to each other in the array
        for ($i=0; $i < count($ScoreArray); $i++)
		{
            extract($ScoreArray[$i]);
            if ($type == "CI") 
            {
                // loop down the array and find it's buddy then move it up
                for ($j=$i+1; $j < count($ScoreArray); $j++)
                {
                    if ($officialscoreid == $ScoreArray[$j]['officialscoreid'])
                    {
                        // first remove it
                        $removed = array_splice($ScoreArray, $j, 1);
                        
                        // then insert it back up with it's partner
                        array_splice($ScoreArray, $i+1, 0, $removed);
                    }
                }
            }
		}
        
        
        printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">");
        // Table Header
		printf("<tr>
                <td class=\"ScoreHistoryTDHeader\">&nbsp</td>
                <td class=\"ScoreHistoryTDHeader\">Date</td>
                <td class=\"ScoreHistoryTDHeader\">Golf Course</td>
                <td class=\"ScoreHistoryTDHeader\">Score</td>
                </tr>
              ");
		printf("<a href=\"home.php?datesort=desc\">");
        
        
		for ($i=0; $i < count($ScoreArray); $i++)
		{
            $RowCount = $i+1;
            $classname = ($RowCount % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
            extract($ScoreArray[$i]);
            if ($type == "CI" && ($i+1 != count($ScoreArray)) && ($ScoreArray[$i+1]['officialscoreid'] == $officialscoreid))
                printf("<tr class=\"$classname\"><td>$RowCount</td><td>$dateplayed</td><td>$coursename<br>%s</td><td>$total</td><td>$officialscoreid</td><td>$type</td></tr>", $ScoreArray[$i+1]['coursename']);
            else
                printf("<tr class=\"$classname\"><td>$RowCount</td><td>$dateplayed</td><td>$coursename</td><td>$total</td><td>$officialscoreid</td><td>$type</td></tr>");
		}
		printf("</table>");
		

		
		printf("<br>A <font color=\"red\">red</font> score means that it has been used to calculate your current handicap.<br>");
		printf("To learn about how a handicap is calculated go <a href=\"http://www.usga.org/handicap/index.html#\">here</a>.");
	}
	DisplayCommonFooter();
}




	if ($_POST['LoginUser'])		// Check to see if we should login user
	{
		if ( ValidateCredentials($_POST['Email'], $_POST['Password']) )
			DisplayPage();
	}
	else if (isset($_SESSION['userid']) && isset($_SESSION['paidup']))	// Already logged in and account current?
	{
		DisplayPage();
	}
	else		// Make them login
	{
		?>
		<meta http-equiv="Refresh" content="0; URL=./index.php">
		<?
	}
?>














