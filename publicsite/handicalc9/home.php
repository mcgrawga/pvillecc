<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
    GetOfficialHandicapIndex($HandicapIndex, $HandicapIndexLabel);
    if ($HandicapIndex < 0)
    {
        $HandicapIndex *= -1;
        $HandicapIndex = "+ " . $HandicapIndex;
    }
    
    GetTrendHandicapIndex($TrendHandicapIndex, $TrendHandicapIndexLabel);
    if ($TrendHandicapIndex < 0)
    {
        $TrendHandicapIndex *= -1;
        $TrendHandicapIndex = "+ " . $TrendHandicapIndex;
    }
    
    $LastRevisionDate = GetLastHandicapRevisionDate();
    if ($LastRevisionDate == null)
        $LastRevisionDate = "N/A";
    else
        $LastRevisionDate = date("M d, Y", strtotime($LastRevisionDate));
        
    $NextRevisionDate = GetNextHandicapRevisionDate();
    if ($NextRevisionDate == null)
        $NextRevisionDate = "N/A";
    else
        $NextRevisionDate = date("M d, Y", strtotime($NextRevisionDate));
    
	printf("<h3>%s's USGA Handicap Index:  <span class=\"RequiredFieldIndicator\">%s %s</span></h3>",$_SESSION['username'],$HandicapIndex, $HandicapIndexLabel);
    ?><div style="float: right; margin: 0px 25px 0px 0px;"><?
    printf("<span class=\"NineHoleScoreFont\"><a href=\"peerreview.php?uid=%s\">Handicap Index History</a></span>", $_SESSION['userid']);
    ?></div><?
    printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><span class=\"NineHoleScoreFont\">Handicap Last Revised On:&nbsp;&nbsp;</span></td><td><span class=\"NineHoleScoreFont\">%s</span></td></tr>", $LastRevisionDate);
    printf("<tr><td><span class=\"NineHoleScoreFont\">Next revision date:  </span></td><td><span class=\"NineHoleScoreFont\">%s</span></td></tr>", $NextRevisionDate);
    printf("<tr><td><span class=\"NineHoleScoreFont\">Trend Handicap:  </span></td><td><span class=\"NineHoleScoreFont\">%s %s</span></td></tr></table><br>",$TrendHandicapIndex, $TrendHandicapIndexLabel);
    
    
    
    

    
    
    	
    $uid .= $_SESSION['userid']; 
	$sql = "select st.hole1, os.type, st.id as scoreid, st.adjustedgrossscore,  st.officialscoreid, DATE_FORMAT(st.dateplayed, '%m/%d/%Y') as dateplayed, Concat(ct.name, ' (', tt.name, ')') as coursename, os.total
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
            

        
 
        
        printf("<table border=\"0\" cellspacing=\"0\"><tr><td align=\"left\"><h3>SCORE HISTORY</h3></td></tr><tr><td>");
        printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"sortable\" id=\"sortable_table\">");
        // Table Header
		printf("<tr class=\"ScoreHistoryTDHeader\">
                <td class=\"sorttable_nosort\">&nbsp</td>
                <td><a href=\"javascript:void();\">Date</a></td>
                <td><a href=\"javascript:void();\">Golf Course</a></td>
                <td><a href=\"javascript:void();\">Score</a></td>
                <td><a href=\"javascript:void();\">Type</a></td>
                </tr>
              ");
    
        // Get an array of Official Score IDs used in handicap calculation so we can identify which scores to turn red.
        $OfficialScoreIDArray = GetHandiIDs();
        
        $RowCount = 1;
		for ($i=0; $i < count($ScoreArray); $i++)
		{
            $classname = ($RowCount % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
            extract($ScoreArray[$i]);
            $timestamp = strtotime($dateplayed);
            
            
            // Quick score, no individual hole values
            if ($hole1 == null)
            {
                if (in_array($officialscoreid, $OfficialScoreIDArray))
                    printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">$dateplayed</td><td><A HREF=\"updatequickscore.php?id=$scoreid\">$coursename</a></td><td><font color=\"red\">$total</font></td><td>%s</td></tr>", GetScoreType($type));
                else
                    printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">$dateplayed</td><td><A HREF=\"updatequickscore.php?id=$scoreid\">$coursename</a></td><td>$total</td><td>%s</td></tr>", GetScoreType($type));
            }
            else
            {
                if (in_array($officialscoreid, $OfficialScoreIDArray))
                    printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">$dateplayed</td><td><A HREF=\"scoreadmin.php?scoreID=$scoreid\">$coursename</a></td><td><font color=\"red\">$total</font></td><td>%s</td></tr>", GetScoreType($type));
                else
                    printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">$dateplayed</td><td><A HREF=\"scoreadmin.php?scoreID=$scoreid\">$coursename</a></td><td>$total</td><td>%s</td></tr>", GetScoreType($type));
            }
            $RowCount++;
		}
		printf("</table>");
        printf("</td></tr></table>");
		

		
		printf("<br>A <font color=\"red\">red</font> score means that it has been used to calculate your USGA handicap index.  ");
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