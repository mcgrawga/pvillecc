<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';



function DisplayPage()
{
    DisplayCommonHeader();
    printf("<h3 style=\"margin: 0px 0px 0px 0px;\">Enter Score</h1>");
	if ( $_POST["SubmitScore"])
	{
        $type = "I";
        if ($_POST["Tournament"])
            $type .= "T";
        if ($_POST["Away"])
            $type .= "A";
        if ($_POST["Penalty"])
            $type .= "P";
	
		//
		//  FORM VALIDATION
		//
		

        // holes 1 - 9 must be valid values
        for ($i = 1; $i < 10; $i++)
        {
            $postVarName = "par$i";
            $fieldName = "Hole $i score";
            if ( !IsScoreValueForHoleValid($fieldName, $_POST[$postVarName]) )
                return;
        }
        
        // if any putts were entered, make sure putts were entered for every hole
        if ( (	strlen($_POST["putt1"]) > 0 ||
            strlen($_POST["putt2"]) > 0 ||
            strlen($_POST["putt3"]) > 0 ||
            strlen($_POST["putt4"]) > 0 ||
            strlen($_POST["putt5"]) > 0 ||
            strlen($_POST["putt6"]) > 0 ||
            strlen($_POST["putt7"]) > 0 ||
            strlen($_POST["putt8"]) > 0 ||
            strlen($_POST["putt9"]) > 0)  && ( strlen($_POST["putt1"]) == 0 ||
                        strlen($_POST["putt2"]) == 0 ||
                        strlen($_POST["putt3"]) == 0 ||
                        strlen($_POST["putt4"]) == 0 ||
                        strlen($_POST["putt5"]) == 0 ||
                        strlen($_POST["putt6"]) == 0 ||
                        strlen($_POST["putt7"]) == 0 ||
                        strlen($_POST["putt8"]) == 0 ||
                        strlen($_POST["putt9"]) == 0))
                        
                        
        {
            printf("You must enter a putt total for every hole played or none at all.");
            return;
        }
        
        // putts 1 - 9 must be valid values
        for ($i = 1; $i < 10; $i++)
        {
            $postVarName = "par$i";
            $fieldName = "Hole $i putt value";
            if ( !IsPuttValueForHoleValid($fieldName, $_POST[$postVarName]) )
                return;
        }                             
		
		if ( !IsGIRValid("Num of Greens in Reg", $_POST["greensInReg"]) )
			return;
		if ( !IsFairwaysHitValid("Num of Fairways Hit", $_POST["fairwaysHit"]) )
			return;
		if ( !IsPenaltiesValid("Num of Penalties", $_POST["penalties"]) )
			return;  
		
		                                
                                                        
		$theDate = sprintf("%u",$_POST["TheYear"]); // YEAR
		if ($_POST["TheMonth"] < 10)
			$theDate .= sprintf("0%u",$_POST["TheMonth"]); // MONTH
		else
			$theDate .= sprintf("%u",$_POST["TheMonth"]); // MONTH
		if ($_POST["TheDay"] < 10)
			$theDate .= sprintf("0%u",$_POST["TheDay"]); // DAY
		else
			$theDate .= sprintf("%u",$_POST["TheDay"]); // DAY
		
			// ENTERING PUTTS IS OPTIONAL, SO WE MUST RECORD NULL IF NOT ENTERED.
			// OTHERWISE IT SHOWS UP AS A ZERO.
			$put1 = formatPuttScore( $_POST["putt1"] );
			$put2 = formatPuttScore( $_POST["putt2"] );
			$put3 = formatPuttScore( $_POST["putt3"] );
			$put4 = formatPuttScore( $_POST["putt4"] );
			$put5 = formatPuttScore( $_POST["putt5"] );
			$put6 = formatPuttScore( $_POST["putt6"] );
			$put7 = formatPuttScore( $_POST["putt7"] );
			$put8 = formatPuttScore( $_POST["putt8"] );
			$put9 = formatPuttScore( $_POST["putt9"] );
			$greens = formatPuttScore( $_POST["greensInReg"] );
			$fairways = formatPuttScore( $_POST["fairwaysHit"] );
			$penalties = formatPuttScore( $_POST["penalties"] );
			
			$tot = ($_POST["par1"] + $_POST["par2"] + $_POST["par3"] + $_POST["par4"] + $_POST["par5"] + $_POST["par6"] + $_POST["par7"] + $_POST["par8"] + $_POST["par9"]);
			$sql = "insert into score_tbl (type, userid, teeid, dateplayed, hole1, hole2, hole3, hole4, hole5, hole6, hole7, hole8, hole9, putt1, putt2, putt3, putt4, putt5, putt6, putt7, putt8, putt9, score, greens, fairways, penalties, comment) values ('";
            $sql .= $type;
			$sql .= "', ";
			$sql .= $_SESSION['userid'];
			$sql .= ", ";
			$sql .= $_POST["Tees"];
			$sql .= ", ";
			$sql .= $theDate;
			$sql .= ", '";
			$sql .= $_POST["par1"];
			$sql .= "', '";
			$sql .= $_POST["par2"];
			$sql .= "', '";
			$sql .= $_POST["par3"];
			$sql .= "', '";
			$sql .= $_POST["par4"];
			$sql .= "', '";
			$sql .= $_POST["par5"];
			$sql .= "', '";
			$sql .= $_POST["par6"];
			$sql .= "', '";
			$sql .= $_POST["par7"];
			$sql .= "', '";
			$sql .= $_POST["par8"];
			$sql .= "', '";
			$sql .= $_POST["par9"];
			$sql .= "', ";
			$sql .= $put1;
			$sql .= ", ";
			$sql .= $put2;
			$sql .= ", ";
			$sql .= $put3;
			$sql .= ", ";
			$sql .= $put4;
			$sql .= ", ";
			$sql .= $put5;
			$sql .= ", ";
			$sql .= $put6;
			$sql .= ", ";
			$sql .= $put7;
			$sql .= ", ";
			$sql .= $put8;
			$sql .= ", ";
			$sql .= $put9;
			$sql .= ", '";
			$sql .= $tot;
			$sql .= "', ";
			$sql .= $greens;
			$sql .= ", ";
			$sql .= $fairways;
			$sql .= ", ";
			$sql .= $penalties;
			$sql .= ", '";
			$sql .= $_POST["commentText"];
			//$sql .= preg_replace("/\'/", "''", $_POST["commentText"]);
			$sql .= "')";
		
		//printf("%s", $sql);
		//printf("%s<br><br>", $sql);
        
        if (strtotime($theDate) > strtotime("now"))
        {
            printf("You can not enter a score with a date in the future");
            DisplayCommonFooter();
            return;
        }
        
        $FirstDayOfSeason = GetFirstDayOfSeason($_POST["TheYear"]);
        $LastDayOfSeason = GetLastDayOfSeason($_POST["TheYear"]);
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
            printf("<form action=\"addscore.php\" method=\"POST\"");
        	printf("<input type=\"submit\" name=\"ConfirmOutOfSeasonScore\" value=\"Post This Score\">");
            printf("<input type=\"hidden\" name=\"SQL\" value=\"$sql\">");
        	printf("</form> ");
            DisplayCommonFooter();
            return;
        }
        
        mysql_query($sql) or die("Could not add new score: " . mysql_error());
        
        // We've inserted the actual score, now calculate the Adjusted Gross Score and update the scoring record with it.
        $ScoreID = mysql_insert_id();
        $AGS = GetAdjustedGrossScore($ScoreID);
        //printf("Adjusted Gross Score:  %s",$AGS);
        UpdateAdjustedGrossScore($ScoreID, $AGS);
        
        //  Now that we have updated the scoring record with AGS, we can record the official score
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
    else if ($_POST["ConfirmOutOfSeasonScore"])
	{
        $sql = preg_replace("/\\\/", "", $_POST["SQL"]);
        //print("<br>SQL:  $sql");
        mysql_query($sql) or die("Could not add new score: " . mysql_error());
        
        // We've inserted the actual score, now calculate the Adjusted Gross Score and update the scoring record with it.
        $ScoreID = mysql_insert_id();
        //print("<br>ScoreID:  $ScoreID");
        $AGS = GetAdjustedGrossScore($ScoreID);
        //printf("Adjusted Gross Score:  %s",$AGS);
        UpdateAdjustedGrossScore($ScoreID, $AGS);
        
        //  Now that we have updated the scoring record with AGS, we can record the official score
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
	else if ( $_GET["EnterScoreForCourse"])
	{	
        print("<br>");
		DisplayScorecard( "ENTER_ROUND", $_GET["CourseID"], $_SERVER['PHP_SELF'] );
	}
	else
	{
        $_SESSION['CallBackPage'] = $_SERVER['HTTP_REFERER'];  // We use this to redirect back to the admin console if necessary.
        $sql = "select name, id from course_tbl where userid = ";
		$sql .= $_SESSION['userid'];
		$result = mysql_query($sql) or die("Could not get a list of courses: " . mysql_error());
		if ( mysql_num_rows($result) == 0 )
			printf("You have not entered any courses yet.  <br>Click <a href=\"addcourse.php\">here</a> to enter a course or <a href=\"searchcourse.php\">here</a> to search for one.");
		else
		{
			$rowCnt = 0;
            printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">");
            printf("<tr><td align=\"right\"><span class=\"NineHoleScoreFont\"><a href=addcourse.php?frompage=%s>Add Course</a></span>", $_SERVER['PHP_SELF']);
            ?>&nbsp&nbsp&nbsp<span class="NineHoleScoreFont"><a href=searchcourse.php>Search For Course</a></span></td></tr><?
            print("<tr><td>");
            printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"sortable\" id=\"sortable_table\">");
            
            print("<tr class=\"ScoreHistoryTDHeader\"><td><a href=\"javascript:void();\">Course</a></td><td></td><td></td></tr>");
			while ($row = mysql_fetch_array($result))
			{
				$classname = ($rowCnt % 2) ? 'CourseList1' : 'CourseList2';
                $NumTees = getNumTeesForCourse($row["id"]);
                extract($row);
				printf("<tr class=\"$classname\">");
				if ($NumTees > 0)
                {
					print("<td>$name</td>");
                    printf("<td><A HREF=\"addscore.php?EnterScoreForCourse=1&CourseID=%s\">Detailed Score</A></td>",$row["id"],$row["name"]);
                    printf("<td><A HREF=\"addquickscore.php?CourseID=%s\">Quick Score</A></td>",$id);
                }    
				else
                {
					printf("<td><A HREF=\"addscore.php?EnterScoreForCourse=1&CourseID=%s\" >%s</A></td>",$row["id"],$row["name"]);
                    print("<td>$name</td>");
                    printf("<td><A HREF=\"addscore.php?EnterScoreForCourse=1&CourseID=%s\"  onclick=\"javascript:alert('There are no tees for this course.  Please create a set before entering a score.'); return false;\">Detailed Score</A></td>",$row["id"],$row["name"]);
                    printf("<td><A HREF=\"addquickscore.php?CourseID=%s\" onclick=\"javascript:alert('There are no tees for this course.  Please create a set before entering a score.'); return false;\">Quick Score</A></td>",$id);
                }    
				printf("</tr>");
				$rowCnt++;
			}
			printf("</table>");
            printf("</td></tr></table>");
		}
		
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
