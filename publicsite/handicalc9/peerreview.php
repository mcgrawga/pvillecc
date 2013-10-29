<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
    if ($_GET["uid"])
	{
        $uid .= $_GET['uid']; 
        GetOfficialHandicapIndexForGolfer($HandicapIndex, $HandicapIndexLabel, $uid);
        if ($HandicapIndex < 0)
        {
            $HandicapIndex *= -1;
            $HandicapIndex = "+ " . $HandicapIndex;
        }
        
        GetTrendHandicapIndexForGolfer($TrendHandicapIndex, $TrendHandicapIndexLabel, $uid);
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
        
    	printf("<h3>%s's USGA Handicap Index:  <span class=\"RequiredFieldIndicator\">%s %s</span></h3>",GetUserNameFromID($uid),$HandicapIndex,$HandicapIndexLabel);
        printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><span class=\"NineHoleScoreFont\">Handicap Last Revised On:&nbsp;&nbsp;</span></td><td><span class=\"NineHoleScoreFont\">%s</span></td></tr>", $LastRevisionDate);
        printf("<tr><td><span class=\"NineHoleScoreFont\">Next revision date:  </span></td><td><span class=\"NineHoleScoreFont\">%s</span></td></tr>", $NextRevisionDate);
        printf("<tr><td><span class=\"NineHoleScoreFont\">Trend Handicap:  </span></td><td><span class=\"NineHoleScoreFont\">%s %s</span></td></tr></table><br>",$TrendHandicapIndex, $TrendHandicapIndexLabel);
        
        $HandicapArray = GetSixMostRecentHandicapIndexes($uid);
         printf("<table border=\"0\" cellspacing=\"0\"><tr><td align=\"left\"><h3>HANDICAP INDEX HISTORY</h3></td></tr><tr><td>");
            printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"sortable\" id=\"sortable_table\">");
            // Table Header
    		printf("<tr class=\"ScoreHistoryTDHeader\">
                    <td class=\"sorttable_nosort\">&nbsp</td>
                    <td><a href=\"javascript:void();\">Revision Date</a></td>
                    <td><a href=\"javascript:void();\">Handicap Index</a></td>
                    </tr>
                  ");
        
            $RowCount = 1;
            $keys = array_keys($HandicapArray);
    		foreach ($HandicapArray as $k => $v)
    		{
                $classname = ($RowCount % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
                $timestamp = strtotime($k);
                printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">%s</td><td>%s</td></tr>", date("M d, Y", $timestamp),$v);
                $RowCount++;
    		}
    		printf("</table>");
            printf("</td></tr></table><br>");

        
        
        	
        
    	$sql = "select os.type, st.id as scoreid, st.adjustedgrossscore,  st.officialscoreid, DATE_FORMAT(st.dateplayed, '%b %d, %Y') as dateplayed, Concat(ct.name, ' (', tt.name, ')') as coursename, os.total
                from score_tbl st left join tools_officialscore os on st.officialscoreid = os.id, tee_tbl tt, course_tbl ct
                where st.userid = $uid and st.teeid = tt.id and tt.courseid = ct.id
                order by st.dateplayed desc limit 20";
    	
    	//printf("%s", $sql);
    	$result = mysql_query($sql) or die("Could not get a list of scores: " . mysql_error());
    	
    	if ( mysql_num_rows($result) == 0 )
    		printf("<h3>SCORE HISTORY</h3>No scores to report.");
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
            $OfficialScoreIDArray = GetHandiIDs($uid);
            
            $RowCount = 1;
    		for ($i=0; $i < count($ScoreArray); $i++)
    		{
                $classname = ($RowCount % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
                extract($ScoreArray[$i]);
                $timestamp = strtotime($dateplayed);
                if (in_array($officialscoreid, $OfficialScoreIDArray))
                    printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">$dateplayed</td><td>$coursename</td><td><font color=\"red\">$total</font></td><td>%s</td></tr>", GetScoreType($type));
                else
                    printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">$dateplayed</td><td>$coursename</td><td>$total</td><td>%s</td></tr>", GetScoreType($type));
                $RowCount++;
    		}
    		printf("</table>");
            printf("</td></tr></table>");
    		

    		
    		printf("<br>A <font color=\"red\">red</font> score means that it has been used to calculate your USGA handicap index.  ");
    		printf("To learn about how a handicap is calculated go <a href=\"http://www.usga.org/handicap/index.html#\">here</a>.");
    	}
    }
    else if ($_GET["days"])
	{
        $days = $_GET['days']; 
        $days--;
        $sql = "select ut.fname, ut.lname, os.type, st.id as scoreid, st.adjustedgrossscore,  st.officialscoreid, DATE_FORMAT(st.dateplayed, '%b %d, %Y') as dateplayed, Concat(ct.name, ' (', tt.name, ')') as coursename, os.total
                from score_tbl st left join tools_officialscore os on st.officialscoreid = os.id, tee_tbl tt, course_tbl ct, user_tbl ut
                where st.userid = ut.id and st.teeid = tt.id and tt.courseid = ct.id and st.dateplayed > DATE_SUB(CURDATE(), INTERVAL $days DAY)
                order by st.dateplayed desc";
    	
    	//printf("%s", $sql);
    	$result = mysql_query($sql) or die("Could not get a list of scores: " . mysql_error());
    	
    	if ( mysql_num_rows($result) == 0 )
    		printf("<h3>SCORE HISTORY</h3>No scores to report.");
    	else
    	{
            // load up an array full of scores
            $ScoreArray = array();
            while ($row = mysql_fetch_array($result))
                $ScoreArray[] = $row;
             
        

            printf("<table border=\"0\" cellspacing=\"0\"><tr><td align=\"left\"><h3>SCORE HISTORY LAST $days DAYS</h3></td></tr><tr><td>");
            printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"sortable\" id=\"sortable_table\">");
            // Table Header
    		printf("<tr class=\"ScoreHistoryTDHeader\">
                    <td class=\"sorttable_nosort\">&nbsp</td>
                    <td><a href=\"javascript:void();\">Date</a></td>
                    <td><a href=\"javascript:void();\">Name</a></td>
                    <td><a href=\"javascript:void();\">Golf Course</a></td>
                    <td><a href=\"javascript:void();\">Score</a></td>
                    <td><a href=\"javascript:void();\">Type</a></td>
                    </tr>
                  ");
        
            // Get an array of Official Score IDs used in handicap calculation so we can identify which scores to turn red.
            //$OfficialScoreIDArray = GetHandiIDs();
            
            $RowCount = 1;
    		for ($i=0; $i < count($ScoreArray); $i++)
    		{
                $classname = ($RowCount % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
                extract($ScoreArray[$i]);
                $fname = substr(ucfirst($fname), 0, 1);
                $timestamp = strtotime($dateplayed);
                $dateplayed = date("m/d/y", $timestamp);
                printf("<tr class=\"$classname\"><td>$RowCount</td><td sorttable_customkey=\"$timestamp\">$dateplayed</td><td>$lname, $fname</td><td>$coursename</td><td>$total</td><td>%s</td></tr>", GetScoreType($type));
                $RowCount++;
    		}
    		printf("</table>");
            printf("</td></tr></table>");
    	}
    }
    else
    {
        print("<h3>Peer Review</h3>");
        print("<span class=\"PeerReviewText\">\"Peer review\" is the ability of golfers to gain an understanding of a player's potential ability and to form a reasonable basis for supporting or disputing a score that has been posted.  For a more detailed description review the definition at the <a href=\"http://www.usga.org/playing/handicaps/manual/sections/section_02.html#peerReview\">USGA Online Handicap Manual</a></span><br><br>");
        
        $sql = "select * from user_tbl where length(fname) > 0 and length(lname) > 0 order by lname asc";
    	//printf("%s", $sql);
    	$result = mysql_query($sql) or die("Could not get a list of golfers: " . mysql_error());
        
        ?>
        <div style="float: right; margin: 1px 25px 0px 0px;">
        <table border="0" cellspacing="0">
        <tr><td><h3>REVIEW ALL SCORES</h3></td></tr>
        <tr><td class="ScoreHistoryTDScores1"><a href="peerreview.php?days=1">Today</a></td></tr>
        <tr><td class="ScoreHistoryTDScores2"><a href="peerreview.php?days=8">Last 7 Days</a></td></tr>
        <tr><td class="ScoreHistoryTDScores1"><a href="peerreview.php?days=31">Last 30 Days</a></td></tr>
        <tr><td class="ScoreHistoryTDScores2"><a href="peerreview.php?days=61">Last 60 Days</a></td></tr>
        <tr><td class="ScoreHistoryTDScores1"><a href="peerreview.php?days=91">Last 90 Days</a></td></tr>
        <tr><td class="ScoreHistoryTDScores2"><a href="peerreview.php?days=181">Last 6 Months</a></td></tr>
        <tr><td class="ScoreHistoryTDScores1"><a href="peerreview.php?days=366">Last 12 Months</a></td></tr>
        </table>
        </div>
        <?
    	
    	if ( mysql_num_rows($result) == 0 )
    		printf("No golfers to review.");
    	else
    	{
            printf("<table border=\"0\" cellspacing=\"0\"><tr><td align=\"left\"><h3>REVIEW INDIVIDUAL SCORES</h3></td></tr><tr><td>");
            printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"sortable\" id=\"sortable_table\">");
            // Table Header
    		printf("<tr class=\"ScoreHistoryTDHeader\"><td><a href=\"javascript:void();\">Name</a></td><td></td></tr>");
            
            $RowCount = 1;
            while ($row = mysql_fetch_array($result))
            {
                $classname = ($RowCount % 2) ? 'ScoreHistoryTDScores2' : 'ScoreHistoryTDScores1';
                extract($row);
                printf("<tr class=\"$classname\"><td>$lname, $fname</td><td><a href=\"peerreview.php?uid=$id\">view</td></a></td></td></tr>");
                $RowCount++;
            }
            printf("</table>");
            printf("</td></tr></table>");
        }
        
    }
	DisplayCommonFooter();
}


		DisplayPage();
/*

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
	}*/
?>