<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';



function DisplayPage()
{
    DisplayCommonHeader();
    if ($_POST["UpdateScore"])
    {
        extract($_POST);
        $uid = $_SESSION['userid'];
        $type = "I";
        if ($Tournament)
            $type .= "T";
        if ($Away)
            $type .= "A";
        if ($Penalty)
            $type .= "P";
        if (!$AGS)
        {
            print("You must enter an adjusted gross score");
        }
        else
        {
            $sql = "update score_tbl set userid = $uid, teeid = $Tees, dateplayed = '$TheYear-$TheMonth-$TheDay', adjustedgrossscore = $AGS, type = '$type' where id = $id";
            $theDate = "$TheYear-$TheMonth-$TheDay";
            if (strtotime($theDate) > strtotime("now"))
            {
                printf("You can not enter a score with a date in the future");
                DisplayCommonFooter();
                return;
            }
            
            $FirstDayOfSeason = GetFirstDayOfSeason($TheYear);
            $LastDayOfSeason = GetLastDayOfSeason($TheYear);
            //printf("The date:  %s<br>", strtotime($theDate));
            //printf("The first:  %s<br>", strtotime($FirstDayOfSeason));
            //printf("The last:  %s<br>", strtotime($LastDayOfSeason));
            if ((strtotime($theDate) < strtotime($FirstDayOfSeason)) || (strtotime($theDate) > strtotime($LastDayOfSeason)))
            {
                printf("The date on your score must fall during your club's active season.<br>");
                printf("First day of season:  %s<br>", date("M d, Y", strtotime($FirstDayOfSeason)));
                printf("Last day of season:  %s<br>", date("M d, Y", strtotime($LastDayOfSeason)));
                
                printf("<br>If this score is from a club in an area observing an active season you may post it for handicap purposes.  Do you want to post this score?");
               	printf("<br><br><form action=\"home.php\" method=\"POST\"");
            	printf("<input type=\"submit\" value=\"No\">");
            	printf("</form> ");
                printf("<form action=\"updatequickscore.php\" method=\"POST\"");
            	printf("<input type=\"submit\" name=\"ConfirmOutOfSeasonScore\" value=\"Post This Score\">");
                printf("<input type=\"hidden\" name=\"sql\" value=\"$sql\">");
                printf("<input type=\"hidden\" name=\"id\" value=\"$id\">");
            	printf("</form> ");
                DisplayCommonFooter();
                return;
            }
            mysql_query($sql) or die("Could not add new score: " . mysql_error());
            
            //  Now that we have updated the scoring record with AGS, we can record the official score
            UpdateOfficialScore($id);
            
            //  Update the Handicap
            UpdateHandicapIndex();

            ?>
    		<meta http-equiv="Refresh" content="0; URL=./home.php">
    		<?

        }
    }
    else if ($_POST["ConfirmOutOfSeasonScore"])
    {
        extract($_POST);
        $sql = preg_replace("/\\\/", "", $sql);
        mysql_query($sql) or die("Could not add new score: " . mysql_error());
        
        //  Now that we have updated the scoring record with AGS, we can record the official score
        UpdateOfficialScore($id);
        
        //  Update the Handicap
        UpdateHandicapIndex();

        ?>
        <meta http-equiv="Refresh" content="0; URL=./home.php">
        <?
        
    }
    else if ($_POST["DeleteScore"])
    {
        extract($_POST);
 
        // DELETE THE OFFICIAL SCORE
        $scoresql = $sql = "select officialscoreid from score_tbl st where  st.id = $id";
        $result = mysql_query($sql) or die("Could not get official score id: " . mysql_error());
         extract(mysql_fetch_array($result));
        mysql_query("delete from tools_officialscore where id = $officialscoreid") or die("Could not delete official score: " . mysql_error());
 
        // DELETE THE DETAILED SCORE
        mysql_query("delete from score_tbl where id = $id") or die("Could not delete score: " . mysql_error());
        ?>
        <meta http-equiv="Refresh" content="0; URL=./home.php">
        <?
        
    }
    else
    {
        extract($_GET);
        // GET SCORE INFO.
        $scoresql = $sql = "select ct.name as CourseName, ct.id as CourseID, tt.id as TeeID, st.dateplayed as DatePlayed, st.adjustedgrossscore as AGS, st.type as ScoreType from course_tbl ct, tee_tbl tt, score_tbl st where ct.id = tt.courseid and tt.id = st.teeid and st.id = $id";
        $result = mysql_query($sql) or die("Could not get selected score: " . mysql_error());
    	extract(mysql_fetch_array($result));
        

        // GET TEES TO POPULATE THE TEE DROP DOWN
        $sql = "select tt.id as teeid, tt.name as teename from tee_tbl tt where tt.courseid = $CourseID";
    	$result = mysql_query($sql) or die("Could not get tees: " . mysql_error());
    	
    	
    	printf("<form action=\"updatequickscore.php\" method=\"POST\" name=\"scorecard\">", $addquickscore.php);
    	printf("<table class=\"CourseTable\">");
    	printf("<tr><td><b>Course Name:</b> </td><td><input class=\"InputBoxWidth\" disabled type=\"text\" name=\"CourseName\" value=\"%s\"></td></tr>", $CourseName);
    	printf("<tr><td><b>Tees:</b> </td><td>");
    	
    	printf("<select class=\"InputBoxWidth\" name=\"Tees\">");
    		while ($row = mysql_fetch_array($result))
    		{
                extract($row);
                if ($teeid == $TeeID)
                    print("<option value=\"$teeid\" selected>$teename</option>");
                else
                    print("<option value=\"$teeid\">$teename</option>");
    		}
    	printf("</select>");
    	
    	printf("<tr><td><b>Date:</b>  </td><td>");
        $formattedDate = substr($DatePlayed, 5, 2);
		$formattedDate .= "/";
		$formattedDate .= substr($DatePlayed, 8, 2);
		$formattedDate .= "/";
		$formattedDate .= substr($DatePlayed, 0, 4);
		getDateControl(getdate(strtotime($formattedDate)));
        print("</td></tr>");
        print("<tr><td><b>Adjusted Gross Score:</b> </td><td><input class=\"InputBoxWidth\" type=\"text\" name=\"AGS\" value=\"$AGS\"></td></tr>");
        
        $checked = null;
        if (strpbrk($ScoreType, "A"))
            $checked = "checked";
        print("<tr><td><input type=\"checkbox\" name=\"Away\" $checked>Away Score</td></tr>");
        $checked = null;
        if (strpbrk($ScoreType, "T"))
            $checked = "checked";
    	print("<tr><td><input type=\"checkbox\" name=\"Tournament\" $checked>Tournament Score</td></tr>");
        if (strpbrk($ScoreType, "P"))
        {
            $checked = "checked";
            print("<tr><td><input type=\"checkbox\" $checked disabled>Penalty Score<input type=\"hidden\" name=\"Penalty\" value=\"1\"></td></tr>");
        }
        else
            print("<tr><td><input type=\"checkbox\" disabled>Penalty Score</td></tr>");
        print("<tr><td><input type=\"hidden\" name=\"id\" value=\"$id\"></td></tr>");
    	printf("<tr><td><input type=\"submit\" value=\"Update\" name=\"UpdateScore\"><input type=\"submit\" value=\"Delete\" name=\"DeleteScore\" onClick=\"javascript:return confirm('Are you sure you want to delete?')\"></td></tr>");
    	printf("</table>");	
    	
    	printf("</form>");
        print("Note: Only 18 hole rounds are allowed to be entered as \"quick scores\".");
    	?>
        <script language="JavaScript">
    	<!--
            document.scorecard.AGS.focus()
    	//-->
        </script>
        <?
    }
	DisplayCommonFooter();
}





	if ($_POST['LoginUser'])		// Check to see if we should login user
	{
		if ( ValidateCredentials($_POST['UserName'], $_POST['Password']) )
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
