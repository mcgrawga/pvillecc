<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';



function DisplayPage()
{
    DisplayCommonHeader();
    printf("<h3 style=\"margin: 0px 0px 0px 0px;\">Enter Score</h1><br>");
    if ($_POST["SubmitScore"])
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
            $sql = "insert into score_tbl (userid, teeid, dateplayed, adjustedgrossscore, type) values ($uid, $Tees, '$TheYear-$TheMonth-$TheDay', $AGS, '$type')";
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
                printf("<form action=\"addquickscore.php\" method=\"POST\"");
            	printf("<input type=\"submit\" name=\"ConfirmOutOfSeasonScore\" value=\"Post This Score\">");
                printf("<input type=\"hidden\" name=\"sql\" value=\"$sql\">");
            	printf("</form> ");
                DisplayCommonFooter();
                return;
            }
            mysql_query($sql) or die("Could not add new score: " . mysql_error());
            
            //  Now that we have updated the scoring record with AGS, we can record the official score
            $ScoreID = mysql_insert_id();
            RecordOfficialScore($ScoreID);
            
            //  Update the Handicap
            UpdateHandicapIndex();

            extract($_SESSION);
            if (strstr($CallBackPage, "scores"))
            {
                $uid = $_SESSION['userid'];
                session_destroy(); 
                print("<meta http-equiv=\"Refresh\" content=\"0; URL=$CallBackPage#$uid\">");  // Redirect back to admin page if necessary
            }
            else
            {
                ?>
                <meta http-equiv="Refresh" content="0; URL=./home.php">
                <?
            }


        }
    }
    else if ($_POST["ConfirmOutOfSeasonScore"])
    {
        extract($_POST);
        $sql = preg_replace("/\\\/", "", $sql);
        mysql_query($sql) or die("Could not add new score: " . mysql_error());
        
        //  Now that we have updated the scoring record with AGS, we can record the official score
        $ScoreID = mysql_insert_id();
        RecordOfficialScore($ScoreID);
        
        //  Update the Handicap
        UpdateHandicapIndex();

        extract($_SESSION);
        if (strstr($CallBackPage, "scores"))
        {
            $uid = $_SESSION['userid'];
            session_destroy(); 
            print("<meta http-equiv=\"Refresh\" content=\"0; URL=$CallBackPage#$uid\">");  // Redirect back to admin page if necessary
        }
        else
        {
            ?>
            <meta http-equiv="Refresh" content="0; URL=./home.php">
            <?
        }
        
    }
    else
    {
        extract($_GET);
        $sql = "select ct.name as CourseName, tt.name as TeeName, tt.id as TeeID, tt.* from course_tbl ct, tee_tbl tt where tt.courseid = ct.id and ct.id = $CourseID";
    	//$sql = "select ct.name as CourseName, tt.name as TeeName, tt.id as TeeID, tt.* from course_tbl ct LEFT JOIN tee_tbl tt on ct.id = tt.courseid where ct.id =";
    	//$sql = "select * from course_tbl ct, tee_tbl tt where tt.courseid = ct.id and ct.id = ";
    	//$sql .= $courseID;
    	//printf("%s", $sql);
    	$result = mysql_query($sql) or die("Could not get selected course: " . mysql_error());
    	$row = mysql_fetch_array($result);
    	$teesArray = array();
    	$teesArray = $row;
    	
    	printf("<form action=\"addquickscore.php\" method=\"POST\" name=\"scorecard\">");
    	printf("<table class=\"CourseTable\">");
    	printf("<tr><td><b>Course Name:</b> </td><td><input class=\"InputBoxWidth\" disabled type=\"text\" name=\"CourseName\" value=\"%s\"></td></tr>", $row["CourseName"]);
    	printf("<tr><td><b>Tees:</b> </td><td>");
    	
    	printf("<select class=\"InputBoxWidth\" name=\"Tees\">");
    		while ($teesArray)
    		{
    			printf("<option value=\"%s\">%s</option>",$teesArray["TeeID"],$teesArray["TeeName"]);
    			$teesArray = mysql_fetch_array($result);
    		}
    	printf("</select>");
    	
    	
    	//printf("<tr><td><b>Slope Rating:</b>  </td><td><input class=\"InputBoxWidth\" disabled type=\"text\" name=\"SlopeRating\" value=\"%s\"></td></tr>", $row["slope"]);
    	//printf("<tr><td><b>Course Rating:</b>  </td><td><input class=\"InputBoxWidth\" disabled type=\"text\" name=\"CourseRating\" value=\"%s\"></td></tr> ", $row["rating"]);
    	//printf("<tr><td><b>City:</b>  </td><td><input class=\"InputBoxWidth\" disabled type=\"text\" name=\"City\" value=\"%s\"></td></tr> ", $row["city"]);
    	//printf("<tr><td><b>State/Province:</b>  </td><td><input class=\"InputBoxWidth\" disabled type=\"text\" name=\"State\" value=\"%s\"></td></tr>", $row["state"]);
    	printf("<tr><td><b>Date:</b>  </td><td>");
    	getDateControl();
        print("</td></tr>");
        print("<tr><td><b>Adjusted Gross Score:</b> </td><td><input class=\"InputBoxWidth\" type=\"text\" name=\"AGS\"></td></tr>");
        print("<tr><td><input type=\"checkbox\" name=\"Away\">Away Score</td></tr>");
    	print("<tr><td><input type=\"checkbox\" name=\"Tournament\">Tournament Score</td></tr>");
        extract($_SESSION);
        if (strstr($CallBackPage, "scores"))
            print("<tr><td><input type=\"checkbox\" name=\"Penalty\">Penalty Score</td></tr>");
        else
            print("<tr><td><input type=\"checkbox\" name=\"Penalty\" disabled>Penalty Score</td></tr>");
    	printf("<tr><td><input type=\"submit\" value=\"Save Score\" name=\"SubmitScore\"></td></tr>");
    	printf("</table>");	
    	
    	printf("</form>");
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
