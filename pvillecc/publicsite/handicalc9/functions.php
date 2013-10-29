<?	

function GetScoreType($type)
{
    if (strpbrk($type, "P"))
        return "P";
    if (strpbrk($type, "T") && strpbrk($type, "I"))
        return "TI";
    if (strpbrk($type, "A") && strpbrk($type, "I"))
        return "AI";
    if (strpbrk($type, "T"))
        return "T";
    if (strpbrk($type, "A"))
        return "A";
    if (strpbrk($type, "C"))
        return "C";
    return "I";
}

function GetGender()
{
    $uid = $_SESSION['userid'];
    $sql = "select gender from user_tbl where id = $uid";
    $result = mysql_query($sql) or die("Could not get gender: " . mysql_error());
    if (mysql_num_rows($result) == 0)
        die("Could not find user.");
    $row = mysql_fetch_array($result);        
    return $row['gender'];
}

function GetSixMostRecentHandicapIndexes($uid="")
{
    // Get the last 6 revision dates
    $sql = "select * from tools_handicaprevisiondate where dt <= curdate() order by dt desc limit 6";
    //$sql = "select * from tools_handicaprevisiondate where dt <= '2004-10-10' order by dt desc limit 6";
    $result = mysql_query($sql) or die("Could not get handicap revision dates: " . mysql_error());
    if (mysql_num_rows($result) == 0)
        return null;
    $RevisionDateArray = array();
    while ($row = mysql_fetch_array($result))    
    {
        extract($row);
        $RevisionDateArray[] = $dt;
    }
    
    // Get the handicap at each revision date and load into an array
    $HandicapIndexArray = array();
    for ($i = 0; $i < count($RevisionDateArray); $i++)
    {
        GetOfficialHandicapIndex($OfficialHandicapIndex, $Label, $RevisionDateArray[$i], $uid);
        $OfficialHandicapIndex .= $Label;
        $HandicapIndexArray[$RevisionDateArray[$i]] = $OfficialHandicapIndex;
    }
    //print_r($HandicapIndexArray);
    return $HandicapIndexArray;
}





// This function will return a course handicap for 18 hole, front 9 or back 9 
function GetCourseHandicap($TeeID, &$CourseHandicap)
{
    GetOfficialHandicapIndex($HandicapIndex);
    
    //  Get the slope(s)
    $sql = "select slope from tee_tbl where id = $TeeID";
    //printf("%s", $sql);
    $result = mysql_query($sql) or die("Could not get slope from tee table: " . mysql_error());
    if (mysql_num_rows($result) == 0)
        die("Could not find specific tee.");
    $row = mysql_fetch_array($result);        
       
    if ($row['slope'] == null)
        $CourseHandicap = "N/A";
    else
        $CourseHandicap = round((($HandicapIndex * $row['slope']) / 113));
        
    return;
       
}
// This function returns the handicap index as of the most recent revision date.  This is the official handicap index.
// If you pass the Revision date it gets the handicap index prior to it.
function GetOfficialHandicapIndex(&$OfficialHandicapIndex, &$Label="", $RevisionDate="", $uid="")
{
    //  Get the last revision date
    if (strlen($RevisionDate) > 0)
        $LastRevisionDate = $RevisionDate;
    else
        $LastRevisionDate = GetLastHandicapRevisionDate();
    if (strlen($uid)== 0)
        $uid = $_SESSION['userid'];

    if ($LastRevisionDate == null)
        return "N/A";
        
    //  Get the first handicap recorded prior to the last revision date.
    $sql = "select * from handicap_tbl where userid = $uid and dt <= '$LastRevisionDate' order by dt desc, id desc limit 1";
    //printf("%s", $sql);
    $result = mysql_query($sql) or die("Could not get first handicap prior to last revision date: " . mysql_error());
    if (mysql_num_rows($result) < 1)
    {
        $Label = null;
        $OfficialHandicapIndex = "N/A";
        return;
    }
    else
    {
        extract(mysql_fetch_array($result));        
        $OfficialHandicapIndex = $num;
        $Label = $label;
        if (strlen($OfficialHandicapIndex) == 0)
            $OfficialHandicapIndex = "N/A";
    }
}
function GetOfficialHandicapIndexForGolfer(&$OfficialHandicapIndex, &$Label="", $uid)
{
   $LastRevisionDate = GetLastHandicapRevisionDate();
    if ($LastRevisionDate == null)
        return "N/A";
        
    //  Get the first handicap recorded prior to the last revision date.
    $sql = "select * from handicap_tbl where userid = $uid and dt <= '$LastRevisionDate' order by dt desc, id desc limit 1";
    //printf("%s", $sql);
    $result = mysql_query($sql) or die("Could not get official handicap for golfer: " . mysql_error());
    if (mysql_num_rows($result) < 1)
    {
        $Label = null;
        $OfficialHandicapIndex = "N/A";
        return;
    }
    else
    {
        extract(mysql_fetch_array($result));        
        $OfficialHandicapIndex = $num;
        $Label = $label;
        if (strlen($OfficialHandicapIndex) == 0)
            $OfficialHandicapIndex = "N/A";
    }
}    

function GetLastHandicapRevisionDate()
{
    $sql = "select max(dt) as dt from tools_handicaprevisiondate where dt <= curdate()";
    $result = mysql_query($sql) or die("Could not get last revision date: " . mysql_error());
    if (mysql_num_rows($result) == 0)
        return null;
    extract(mysql_fetch_array($result));        
    return $dt;
}

function GetNextHandicapRevisionDate()
{
    $sql = "select min(dt) as dt from tools_handicaprevisiondate where dt > curdate()";
    $result = mysql_query($sql) or die("Could not get next revision date: " . mysql_error());
    if (mysql_num_rows($result) == 0)
        return null;
    extract(mysql_fetch_array($result));        
    return $dt;
}

function GetFirstDayOfSeason($year)
{
    $sql = "select start from tools_season where year = $year";
    $result = mysql_query($sql) or die("Could not get first day of season: " . mysql_error());
    if (mysql_num_rows($result) == 0)
        return null;
    extract(mysql_fetch_array($result));        
    return $start;
}

function GetLastDayOfSeason($year)
{
    $sql = "select end from tools_season where year = $year";
    $result = mysql_query($sql) or die("Could not get last day of season: " . mysql_error());
    if (mysql_num_rows($result) == 0)
        return null;
    extract(mysql_fetch_array($result));        
    return $end;
}


// This function returns the current handicap index.  It is calculated as if the revision date were today.
function GetTrendHandicapIndex(&$OfficialHandicapIndex, &$Label="")
{
    $uid = $_SESSION['userid'];
    $sql = "select * from handicap_tbl where userid = $uid order by dt desc, id desc limit 1";
	$result = mysql_query($sql) or die("Could not get most recent handicap: " . mysql_error());
    if (mysql_num_rows($result) < 1)
    {
        $OfficialHandicapIndex = "N/A";
        return;
    }
    else
    {
        extract(mysql_fetch_array($result));        
        $OfficialHandicapIndex = $num;
        $Label = $label;
        if (strlen($OfficialHandicapIndex) == 0)
            $OfficialHandicapIndex = "N/A";
    }
}

function GetTrendHandicapIndexForGolfer(&$OfficialHandicapIndex, &$Label="", $uid)
{
    $sql = "select * from handicap_tbl where userid = $uid order by dt desc, id desc limit 1";
	$result = mysql_query($sql) or die("Could not get trend handicap for golfer: " . mysql_error());
    if (mysql_num_rows($result) < 1)
    {
        $OfficialHandicapIndex = "N/A";
        return;
    }
    else
    {
        extract(mysql_fetch_array($result));        
        $OfficialHandicapIndex = $num;
        $Label = $label;
        if (strlen($OfficialHandicapIndex) == 0)
            $OfficialHandicapIndex = "N/A";
    }

}

function UpdateOfficialScore($ScoreID)
{

        //  Grab the foreign key to the hadicap_score_tbl
        $sql = "select * from score_tbl st, tee_tbl tt where st.id = $ScoreID and st.teeid = tt.id";
        //printf("%s<br><br>", $sql);
        $result = mysql_query($sql) or die("Could not get updated score: " . mysql_error());
        extract(mysql_fetch_array($result));
        PerformUpdateOfficialScore($officialscoreid, $adjustedgrossscore, $slope, $rating, $dateplayed, $type);
   
}

function DeleteOfficialScore($ScoreID)
{
        //  Grab the foreign key to the hadicap_score_tbl
        $sql = "select officialscoreid from score_tbl where id = $ScoreID";
        $result = mysql_query($sql) or die("Could not get officialscoreid: " . mysql_error());
        $row = mysql_fetch_array($result);
        $OfficialScoreID = $row['officialscoreid'];
        
        // Delete the score
        DeleteScore($ScoreID);
        
        // Delete the score from the tools_officialScore
        $sql = "delete from tools_officialscore where id = $OfficialScoreID";
        mysql_query($sql) or die("Could not delete official score: " . mysql_error());
}


function RecordOfficialScore($ScoreID)
{
    // Insert info into officialscore table, including score type code "I"
    $sql = "select s.type, s.adjustedgrossscore, t.slope, t.rating, date_format(s.dateplayed, '%Y-%m-%d') as dateplayed from score_tbl s, tee_tbl t where s.id = $ScoreID and s.teeid = t.id;";
    $result = mysql_query($sql) or die("Could not get score: " . mysql_error());
    extract(mysql_fetch_array($result));
    $OfficialScoreID = InsertOfficialScore($adjustedgrossscore, $slope, $rating, $dateplayed, $type);
    
    // Relate the official score to the score in the score table.
    $sql = "update score_tbl set officialscoreid = $OfficialScoreID where id = $ScoreID";
    $result = mysql_query($sql) or die("Could relate official score to score: " . mysql_error());

}
function InsertOfficialScore($total, $slope, $rating, $dateplayed, $type = "")
{
    $uid = $_SESSION['userid'];
    // INSERT HANDICAP SCORE
    $sql = "";
    if (strlen($type) > 0)
        $sql = "INSERT INTO tools_officialscore (total, slope, rating, type, dateplayed, userid) VALUES($total, $slope, $rating, '$type', '$dateplayed', '$uid')";
    else
        $sql = "INSERT INTO tools_officialscore (total, slope, rating, dateplayed, userid) VALUES($total, $slope, $rating, '$dateplayed', '$uid')";
	//printf("$sql");
    mysql_query($sql) or die("Could not insert official score: " . mysql_error());
    
    // GET ID AND RETURN IT
    return mysql_insert_id();
}

function PerformUpdateOfficialScore($ScoreID, $total, $slope, $rating, $dateplayed, $type = "")
{
    $uid = $_SESSION['userid'];
    // INSERT HANDICAP SCORE
    $sql = "";
    if (strlen($type) > 0)
        $sql = "UPDATE tools_officialscore set total = $total, slope = $slope, rating = $rating, dateplayed = '$dateplayed', type = '$type' where id = $ScoreID";
    else
        $sql = "UPDATE tools_officialscore set total = $total, slope = $slope, rating = $rating, dateplayed = '$dateplayed' where id = $ScoreID";
	//printf("$sql");
    mysql_query($sql) or die("Could not update official score: " . mysql_error());
}


function UserPaidUp()
{

	return 1;
/*
	$SignUpDate = GetSignUpDate();
	$Today = strtotime("today");
	$TrialPeriodEndDate = GetTrialPeriodEndDate();
	$PaidThroughDate = GetPaidThroughDate();
	
	// 1)  USER STILL IN TRIAL PERIOD
	// 2)  USER PAST TRIAL PERIOD AND HAS NOT PAID
	// 3)  USER HAS PAID PREVIOUSLY AND ACCOUNT EXPIRED
	// 4)  USER HAS PAID AND ACCOUNT IS NOT EXPIRED
	
	
	if ($Today <= $TrialPeriodEndDate)
	{
		return 1;
	}
	else if (($Today > $TrialPeriodEndDate) && ($PaidThroughDate == 'NEVER_PAID'))
	{
		DisplayOnlyBanner();
		printf("<div id=\"paragraphtext\">Thank you for signing up for StatGolf.  We are sure you will enjoy the site and that it will be a valuable tool to help improve your golf game.  Your trial period expired on %s.<br><br>Please pay the annual fee of $%s to activate your account through %s</div>", date("F j, Y", $TrialPeriodEndDate), getConfigValue("ANNUAL_FEE"), GetNextPaidThroughDate());
		include 'paymentbutton.php';
		DisplayCommonFooter();
		return 0;
	}
	else if ($Today > strtotime($PaidThroughDate))
	{
		DisplayOnlyBanner();
		printf("<div id=\"paragraphtext\">Your StatGolf account expired on %s.<br>Please pay now to keep all your scores and courses.<br><br>Paying now will extend your StatGolf account through %s</div>", $PaidThroughDate, GetNextPaidThroughDate());
		include 'paymentbutton.php';
		DisplayCommonFooter();
		return 0;
	}
	else
	{
		return 1;
	}
	
	
	/*
	// HAVE SIGNED UP, BUT NEVER PAID
	if ($PaidThroughDate == 'NEVER_PAID')
	{
		DisplayOnlyBanner();
		printf("<div id=\"paragraphtext\">Thank you for signing up for StatGolf.  We are sure you will enjoy the site and that it will be a valuable tool to help improve your golf game.<br><br>  Please pay the annual fee of $35 to activate your account through %s</div><br><br>", GetNextPaidThroughDate());
		include 'paymentbutton.php';
		DisplayCommonFooter();
		return 0;
	}
	
	printf("Today:  %s<br>", date("F j, Y", $Today));	
	printf("SignUpDate:  %s<br>", date("F j, Y", $SignUpDate));	
	printf("TrialPeriodEndDate:  %s<br>", date("F j, Y", $TrialPeriodEndDate));	
	printf("PaidThroughDate:  %s<br>", $PaidThroughDate);	
	
	
	*/
}


// THIS RETURNS THE DATE UNFORMATTED (UNIX TIMESTAMP)
function GetSignUpDate()
{

	$sql = "select createdt as signupdate from user_tbl where id = ";
	$sql .= $_SESSION['userid'];
	$result = mysql_query($sql) or die("Could not get sign up date: " . mysql_error());
	$row = mysql_fetch_array($result);

	return strtotime($row['signupdate']);
}

function GetClubName()
{

	$sql = "select name from tools_club";
	$result = mysql_query($sql) or die("Could not get club name: " . mysql_error());
	$row = mysql_fetch_array($result);

	return $row['name'];
}

// THIS RETURNS THE DATE UNFORMATTED (UNIX TIMESTAMP)
function GetTrialPeriodEndDate()
{

	$sql = "select ADDDATE(createdt, INTERVAL 30 DAY) as trialperiodenddate from user_tbl where id = ";
	$sql .= $_SESSION['userid'];
	$result = mysql_query($sql) or die("Could not get trial period end date: " . mysql_error());
	$row = mysql_fetch_array($result);

	return strtotime($row['trialperiodenddate']);
}
 

//
//  IF USER PAYS TODAY, THIS FUNCTION RETURNS THE DATE HE/SHE WOULD BE PAID THROUGH.
//
function GetNextPaidThroughDate()
{

	$sql = "select max(effectivedate), ADDDATE(max(effectivedate), INTERVAL 2 YEAR) as nextpaidthroughdate from payment_tbl where user_id = ";
	$sql .= $_SESSION['userid'];
	$result = mysql_query($sql) or die("Could not get paid through date: " . mysql_error());
	$row = mysql_fetch_array($result);
	
	// CREATE A DATE ONE YEAR FROM TODAY
	$oneYearFromToday = strtotime("+1 year");
	
	if ( !$row['nextpaidthroughdate'] )
	{
		//return $oneYearFromToday->format("F j, Y");
		return date("F j, Y", $oneYearFromToday);
	}
	else
	{
		$twoYearsFromLastPaidDate = strtotime($row['nextpaidthroughdate']);
		if ($twoYearsFromLastPaidDate > $oneYearFromToday)
			return date("F j, Y", $twoYearsFromLastPaidDate);
		else
			return date("F j, Y", $oneYearFromToday);
	}
}


function GetPaidThroughDate()
{

	$sql = "select max(effectivedate), ADDDATE(max(effectivedate), INTERVAL 1 YEAR) as paidthroughdate from payment_tbl where user_id = ";
	$sql .= $_SESSION['userid'];
	$result = mysql_query($sql) or die("Could not get paid through date: " . mysql_error());
	$row = mysql_fetch_array($result);
	
	// CREATE A DATE ONE YEAR FROM TODAY
	$oneYearFromToday = strtotime("+1 year");
	
	if ( !$row['paidthroughdate'] )
	{
		return 'NEVER_PAID';
	}
	else
	{
		$oneYearFromLastPaidDate = strtotime($row['paidthroughdate']);
		return date("F j, Y", $oneYearFromLastPaidDate);
	}
}


function InsertPayment($userid, $amt, $effectivedate)
{
	$sql = "INSERT INTO payment_tbl (user_id, amt, effectivedate) VALUES($userid, $amt, '$effectivedate')";
	//printf("$sql");
	if (!mysql_query($sql))
		return 0;	// INSERT DIDN'T WORK
	$_SESSION['paidup'] = 1;
	return 1;	// INSERT WORKED
}

function IsRequiredFieldPresent($fieldname, $fieldvalue)	
{
	if (strlen($fieldvalue) == 0)
	{
		printf("%s is a required field", $fieldname);
		return 0;
	}
	return 1;
}

/*
function IsUsernameValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Username", $fieldvalue))
		return 0;
	return 1;
}
*/

function IsPasswordValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Password", $fieldvalue))
		return 0;
	return 1;
}

function IsCPasswordValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Confim Password", $fieldvalue))
		return 0;
	return 1;
}

function IsEmailValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Email", $fieldvalue))
		return 0;
	if(!preg_match("/^[a-z0-9\å\ä\ö._-]+@[a-z0-9\å\ä\ö.-]+\.[a-z]{2,6}$/i", $fieldvalue))
	{
		printf("You must enter a valid email");
		return 0;
	}
	return 1;
}

function IsCourseNameValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Course Name", $fieldvalue))
		return 0;
	return 1;
}

function IsCityValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("City", $fieldvalue))
		return 0;
	return 1;
}

function IsStateValid($fieldvalue)	
{
	if ($fieldvalue == 0)
    {
        printf("State / Province is a required field");
		return 0;
    }
	return 1;
}

function IsTeeValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Tee Name", $fieldvalue))
		return 0;
	return 1;
}

function IsSlopeRatingValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Slope Rating", $fieldvalue))
		return 0;
	if (!is_numeric($fieldvalue))
	{
		printf("Slope Rating has to be numeric");
		return 0;
	}
	return 1;
}

function IsCourseRatingValid($fieldvalue)	
{
	if (!IsRequiredFieldPresent("Course Rating", $fieldvalue))
		return 0;
	if (!is_numeric($fieldvalue))
	{
		printf("Course Rating has to be numeric");
		return 0;
	}
	return 1;
}

function IsSlopeRatingFBValid($fieldvalue)	
{
	if (!is_numeric($fieldvalue) && strlen($fieldvalue) > 0)
	{
		printf("Slope Rating has to be numeric");
		return 0;
	}
	return 1;
}

function IsCourseRatingFBValid($fieldvalue)	
{
	if (!is_numeric($fieldvalue) && ($fieldvalue < 45) && strlen($fieldvalue) > 0)
	{
		printf("Course Rating has to be numeric");
		return 0;
	}
	return 1;
}


function IsCourseRatingLessThanSlope($course, $slope)
{
	if (($course < $slope) && ($course < 45))
		return 1;
	else
	{
		printf("<b>ERROR:  </b>Course rating must be less than slope rating.  <br><br>You might have them switched around, i.e. entered course for slope rating and slope for course rating<br><br>Make sure you are entering a 9 hole course rating and slope, not an 18 hole rating and slope.<br><br>Each 9 should be individually rated, if it is not evident on the scorecard, ask the clubhouse for the ratings.");
		return 0;
	}
}

function IsParValueForHoleValid($fieldname, $fieldvalue)	
{
	if ($fieldvalue < 3 || $fieldvalue > 5)
	{
		printf("%s has to be 3, 4 or 5", $fieldname);
		return 0;
	}
	return 1;
}

function IsScoreValueForHoleValid($fieldname, $fieldvalue)	
{
		
	if (!is_numeric($fieldvalue))
	{
		printf("%s has to be numeric and greater than zero", $fieldname);
		return 0;
	}
	
	if ($fieldvalue == 0)
	{
		printf("%s has to be numeric and greater than zero", $fieldname);
		return 0;
	}
	return 1;
}

function IsPuttValueForHoleValid($fieldname, $fieldvalue)	
{
		
	if (!is_numeric($fieldvalue) && (strlen(fieldvalue) > 0))
	{
		printf("%s has to be numeric or null", $fieldname);
		return 0;
	}
	
	return 1;
}

function IsGIRValid($fieldname, $fieldvalue)	
{
	if (strlen($fieldvalue) > 0)
	{
		if (!is_numeric($fieldvalue))
		{
			printf("%s has to be numeric or null", $fieldname);
			return 0;
		}
		
		if ($fieldvalue > 18)
		{
			printf("%s has to be less than 19", $fieldname);
			return 0;
		}
		
		if ($fieldvalue < 0)
		{
			printf("%s can not be less than zero", $fieldname);
			return 0;
		}
	}
	return 1;
}

function IsFairwaysHitValid($fieldname, $fieldvalue)	
{
	if (strlen($fieldvalue) > 0)
	{
		if (!is_numeric($fieldvalue))
		{
			printf("%s has to be numeric or null", $fieldname);
			return 0;
		}
		
		if ($fieldvalue > 18)
		{
			printf("%s has to be less than 19", $fieldname);
			return 0;
		}
		
		if ($fieldvalue < 0)
		{
			printf("%s can not be less than zero", $fieldname);
			return 0;
		}
	}
	return 1;
}

function IsPenaltiesValid($fieldname, $fieldvalue)	
{
	
	if (strlen($fieldvalue) > 0)
	{
		if (!is_numeric($fieldvalue))
		{
			printf("%s has to be numeric or null", $fieldname);
			return 0;
		}
		
		if ($fieldvalue < 0)
		{
			printf("%s can not be less than zero", $fieldname);
			return 0;
		}
	}
	return 1;
}

function ListCourses()
{
	$sql = "select name, id from course_tbl where userid = ";
	$sql .= $_SESSION['userid'];
	$result = mysql_query($sql) or die("Could not get a list of courses: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		printf("You have not entered any courses yet.  <br>Click <a href=\"addcourse.php\">here</a> to enter one");
	else
	{
	/*
		printf("<table class=\"ScoreCardTable\">");
		$rowCnt = 0;
		while ($row = mysql_fetch_array($result))
		{
			$classname = ($rowCnt % 2) ? 'CourseList2' : 'CourseList1';
			printf("<tr>");
			printf("<td class=\"$classname\">%s</td><td class=\"$classname\"><A HREF=\"courseadmin.php?ShowDetails=1&CourseID=%s\">edit</A></td><td class=\"$classname\"><A HREF=\"courseadmin.php?DeleteCourse=1&courseid=%s\" onclick=\"javascript:return confirm('Are you sure you want to delete?')\">delete</A></td>",$row["name"], $row["id"], $row["id"]);
			printf("</tr>");
			$rowCnt++;
		}
		printf("</table>");
	*/
        printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">");
        printf("<tr><td align=\"right\"><span class=\"NineHoleScoreFont\"><a href=addcourse.php?frompage=%s>Add Course</a></span>", $_SERVER['PHP_SELF']);
        ?>&nbsp&nbsp&nbsp<span class="NineHoleScoreFont"><a href=searchcourse.php>Search For Course</a></span></td></tr><?
        print("<tr><td>");
		printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"sortable\" id=\"sortable_table\">");

        print("<tr class=\"ScoreHistoryTDHeader\"><td><a href=\"javascript:void();\">Course</a></td><td></td><td></td></tr>");    
		$rowCnt = 0;
		while ($row = mysql_fetch_array($result))
		{
			$classname = ($rowCnt % 2) ? 'CourseList1' : 'CourseList2';
			printf("<tr class=\"$classname\">");
			printf("<td>%s</td><td><A HREF=\"courseadmin.php?ShowDetails=1&CourseID=%s\">edit</A>&nbsp&nbsp</td><td><A HREF=\"courseadmin.php?DeleteCourse=1&courseid=%s\" onclick=\"javascript:return confirm('Are you sure you want to delete?')\">delete</A></td>",ucwords(strtolower($row["name"])), $row["id"], $row["id"]);
			printf("</tr>");
			$rowCnt++;
		}
		printf("</table>");
        printf("</td></tr></table>");
	}
	
}


//
//  THIS DELETES THE COURSE PLUS ALL THE TEES AND SCORES ASSOCIATED WITH IT.
//
function DeleteCourse($courseid)
{
	$DeleteScoresSql = "delete from score_tbl where teeid in(select id from tee_tbl where courseid = $courseid);";
	$DeleteTeesSql = "delete from tee_tbl where courseid = $courseid;";
	$DeleteCourseSql = "delete from course_tbl where id = $courseid;";
	//printf("%s", $DeleteScoresSql);
	mysql_query($DeleteScoresSql) or die("Could not delete scores for this course: " . mysql_error());
	mysql_query($DeleteTeesSql) or die("Could not delete tees for this course: " . mysql_error());
	mysql_query($DeleteCourseSql) or die("Could not delete this course: " . mysql_error());
}

//
//  THIS DELETES THE TEE PLUS ALL THE SCORES ASSOCIATED WITH IT.
//
function DeleteTee($teeid)
{
	$DeleteScoresSql = "delete from score_tbl where teeid = $teeid;";
	$DeleteTeesSql = "delete from tee_tbl where id = $teeid;";
	mysql_query($DeleteScoresSql) or die("Could not delete scores for this course: " . mysql_error());
	mysql_query($DeleteTeesSql) or die("Could not delete tees for this course: " . mysql_error());
}

function getNumTeesForCourse($courseId)
{
	$sql = "select * from tee_tbl where courseid = $courseId;";
	$result = mysql_query($sql) or die("Could not select tees for course: " . mysql_error());
	return mysql_num_rows($result);
}

function DeleteScore($scoreid)
{
	$DeleteScoresSql = "delete from score_tbl where id = $scoreid;";
	mysql_query($DeleteScoresSql) or die("Could not delete score: " . mysql_error());
}




function ResultSetToXML($rs)
{
	$XML = '<?xml version="1.0" encoding="ISO-8859-1"?>';
	$XML .= "<resultset>";

	$NumberOfColumns = mysql_num_fields($rs);
	while ($row = mysql_fetch_array($rs))
	{
		$XML .= "<row>";
		for ($i=0; $i<$NumberOfColumns; $i++)
		{
			if (!is_null($row[$i]))
			{
				$ColumnName = mysql_field_name($rs,$i);
				$ColumnValue = htmlspecialchars($row[$i]);
				$XML .= sprintf("<%s>%s</%s>",$ColumnName, $ColumnValue, $ColumnName );
			}
		}
		$XML .= "</row>";
	}
	$XML .= "</resultset>";
	return $XML;
}

function formatPuttScore ( $a )
{
	if ( $a == "" )
		$a = "null";
	return $a;
	
}

function DisplayCommentForm()
{
	printf("<form action=\"comments.php\" method=\"POST\" name=\"commentForm\">", $postPage);
	printf("<table class=\"InstructionsTable\">");
	printf("<tr><td><textarea class=\"CommentBox\" type=\"text\" name=\"commentText\"></textarea></td></tr>");
	printf("<tr><td align=\"center\"><input type=\"submit\" name=\"SubmitComment\" value=\"Submit Comment\"></td></tr>");
	printf("</table> ");	
}

function ConnectToDB()
{
	$db = mysql_connect("localhost", "statgolf_mcgraw", "blu1duck*");
	mysql_select_db("sg_pvillecc_9",$db);
}

function InvalidLogin()
{
		DisplayGeneralPublicHeader();
		printf("<font color=\"red\">Invalid Login</font><br>");
		DisplayCommonFooter();
}

function GetUserNameFromID($id)
{
    // Verify password
		$sql = "select * from user_tbl where id = $id";
		$result = mysql_query($sql);
		$num_rows = mysql_num_rows($result);
		if ( $num_rows == 0 )
		{
			print("Can not find user name.");
			die;
		}
		else
		{
			// Get email and id from result set.
			extract(mysql_fetch_array($result));
            $name = $fname;
            $name .= " ";
            $name .= $lname;
			
			return $name;
        }
}

function ValidateCredentials($Email, $Password)
{
	ConnectToDB();
	
	if ($Email == "" || $Password == "")
	{
		InvalidLogin();
		return 0;
	}

	// Verify user name
	$sql = "select * from user_tbl where lcase(email) = lcase('$Email')";
	//printf("%s", $sql);
	$result = mysql_query($sql);
	$num_rows = mysql_num_rows($result);
	if ( $num_rows == 0 )
	{
    print("2<br>");
		InvalidLogin();
		return 0;
	}
	else
	{
		// Verify password
		$sql = "select id, email, fname, lname from user_tbl where lcase(email) = lcase('$Email') and password = '$Password'";
		$result = mysql_query($sql);
		$num_rows = mysql_num_rows($result);
		if ( $num_rows == 0 )
		{
            print("3<br>");
			InvalidLogin();
			return 0;
		}
		else
		{
			// Get email and id from result set.
			$row = mysql_fetch_array($result);
			$id = $row["id"];
			$email = $row["email"];
            $name = $row["fname"];
            $name .= " ";
            $name .= $row["lname"];
			
			// Register session key with the value 
			$_SESSION['userid'] = $id;
			$_SESSION['email'] = $email;
            $_SESSION['username'] = $name;
			
			// RECORD THE USER LOGIN			
			$sql = "insert into login_tbl (user_id) values ($id)";
			mysql_query($sql);
			
			if (UserPaidUp())
			{
				$_SESSION['paidup'] = 1;
				return 1;
			}
			else
			{
				return 0;
			}
			
		}
	}
}


function GenerateGiftCode()
{
	$elementArray = array(	'1', 
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9',
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z'
				);
				
	$giftcode = "";
	$arraySize = count($elementArray);
	for ($i = 0; $i < 10; $i++)
		$giftcode .= $elementArray[rand(0, $arraySize-1)];
	return $giftcode;
}

function InsertGiftCode($email="")
{
	$GiftCode = GenerateGiftCode();
	
	for ($i=0; $i<1000; $i++)
	{
		if (GiftCodeAlreadyExists($GiftCode))
			$GiftCode = GenerateGiftCode();
		else
			break;
	}
	
	if ( strlen($email) > 0 )
		$sql = "INSERT INTO giftcode_tbl (code, used, paid, createdbyemail) VALUES('$GiftCode', 0, 0, '$email')";
	else
		$sql = "INSERT INTO giftcode_tbl (code, used, paid) VALUES('$GiftCode', 0, 0)";
	
	//printf("$sql");
	if (!mysql_query($sql))
		return 0;	// INSERT DIDN'T WORK
	return $GiftCode;	// INSERT WORKED
}

function GiftCodeAlreadyExists($GiftCode)
{
	$sql = "select * from giftcode_tbl where code = '$GiftCode'";
	//printf("%s", $sql);
	$result = mysql_query($sql) or die("Could not check to see if user already exists: " . mysql_error());
	return mysql_num_rows($result);
}

function MarkGiftCodeAsPaid($gc)
{
	$sql = "update giftcode_tbl set paid = 1 where code = '$gc'";
	//printf("%s", $sql);
	if (!mysql_query($sql))
		return 0;	// INSERT DIDN'T WORK
	return 1;	// INSERT WORKED
}

function GetRandomQuoteID()
{
	$sql = "select count(*) num_rows from quote_tbl";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	$num_rows = $row["num_rows"];
	return rand(1, $num_rows);
}

function GetQuote()
{
	$golfQuoteId = GetRandomQuoteID();
	$sql = "select * from quote_tbl where id = $golfQuoteId";
	$result = mysql_query($sql);
	if ( mysql_num_rows($result) == 0 )
		return "No available golf quotes";
	$row = mysql_fetch_array($result);
	$quote = $row["quote"];
	$quote .= "<br>  ";
	$quote .= $row["author"];
	return $quote;
}


// THIS IS THE HEADER AND NAV BAR AND LOGIN PROMPT THAT APPEARS
// WHEN A USER IS NOT LOGGED IN OR ON PAYMENT SCREENS.
function DisplayGeneralPublicHeader()
{
    ?>
    		
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
    <title>StatGolf</title>
    <link rel="stylesheet" type="text/css"
    href="../media/style.css" />
    </head>
    <body>
        <div id="container">
            <div id="tophandicalc"></div>
            <div id="main_login">
    <?
}


function DisplayCommonHeader()
{

	// Connect to the db.
	ConnectToDB();
?>
	<html>
	<head>
	<title>StatGolf</title>
	<link rel="stylesheet" type="text/css" href="../media/style.css" />
	<!--[if IE]>
	<link rel="stylesheet" href='ie_fixes.css' type="text/css" media="screen, projection" />
	<![endif]-->
	<script language="JavaScript" src="scorecardevents.js"></script>
	<script language="JavaScript" src="coursesearchevents.js"></script>
    <script src="tablesort.js"></script>
	</head>
	<body>
	
	<div id="container">
		<div id="tophandicalc"></div>
			<div id="left">
			
			<h2>Navigation</h2>
				<div class="hr">
				<hr />
				</div>
			<p class="side">
			<a href="home.php">&rarr; Home</a><br />
			<a href="addscore.php">&rarr; Enter Score</a><br />
            <a href="courseadmin.php">&rarr; Golf Courses</a><br />
			<a href="stats.php">&rarr; Statistics</a><br />
			<a href="handicapcard.php">&rarr; Handicap Card</a><br />
            <a href="peerreview.php">&rarr; Peer Review</a><br />
            <a href="accountadmin.php">&rarr; Account Admin</a><br />
            <a href="instructions.php">&rarr; Instructions</a><br />
			<a href="logout.php">&rarr; Logout</a><br />
			<?
			if (inConfigName("ADMIN_USER", $_SESSION['email']))
				printf("<a href=\"statsandadmin.php\">&rarr; Stats & Admin</a><br />");
			?>
			</p><br />
			
			<h2>Quote</h2>
				<div class="hr">
				<hr />
				</div>
			<p class="side">
            <span class="NineHoleScoreFont">
			<?
			printf("%s", GetQuote());
			?>
			</p>
			</span>
			</div>
		
		<div id="main">
<?
}

function DisplayOnlyBanner()
{

	// Connect to the db.
	ConnectToDB();
?>
	<html>
	<head>
	<title>StatGolf</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script language="JavaScript" src="scorecard_events.js"></script>
	</head>
	<body>
	
	<div id="container">
		<div id="top"></div>
			
		
		<div id="main">
<?
}

function DisplayCommonFooter()
{
?>
        </div>
        <div id="footer"></div>
    </div>
</body>
</html>
<?
}

function calcFront ($row)
{
   $total = $row["hole1"];
   $total += $row["hole2"];
   $total += $row["hole3"];
   $total += $row["hole4"];
   $total += $row["hole5"];
   $total += $row["hole6"];
   $total += $row["hole7"];
   $total += $row["hole8"];
   $total += $row["hole9"];
   return $total;
}

function calcBack ($row)
{
   $total = $row["hole10"];
   $total += $row["hole11"];
   $total += $row["hole12"];
   $total += $row["hole13"];
   $total += $row["hole14"];
   $total += $row["hole15"];
   $total += $row["hole16"];
   $total += $row["hole17"];
   $total += $row["hole18"];
   return $total;
}

function calcFrontPar ($row)
{
   $total = $row["par1"];
   $total += $row["par2"];
   $total += $row["par3"];
   $total += $row["par4"];
   $total += $row["par5"];
   $total += $row["par6"];
   $total += $row["par7"];
   $total += $row["par8"];
   $total += $row["par9"];
   return $total;
}

function calcBackPar ($row)
{
   $total = $row["par10"];
   $total += $row["par11"];
   $total += $row["par12"];
   $total += $row["par13"];
   $total += $row["par14"];
   $total += $row["par15"];
   $total += $row["par16"];
   $total += $row["par17"];
   $total += $row["par18"];

   return $total;
}

function userPreExists ($email)
{
	$sql = "select * from user_tbl where lcase(email) = lcase('$email')";
	//printf("%s", $sql);
	$result = mysql_query($sql) or die("Could not check to see if user already exists: " . mysql_error());
	return mysql_num_rows($result);
}


function getESCValue($CourseHandicap)
{
	if ( $CourseHandicap == "N/A")
    {
        if (GetGender() == "M")
            $CourseHandicap = 18.2;
        else
            $CourseHandicap = 20.2;
    }
    if ( $CourseHandicap >= 20 )
        return 10;
	else if ( $CourseHandicap >= 15 )
		return 9;
	else if ( $CourseHandicap >= 10 )
		return 8;
	else if ( $CourseHandicap >= 5 )
		return 7;
	return "DOUBLE";
}
function UpdateAdjustedGrossScore($ScoreID, $AGS)
{
	$sql = "update score_tbl set adjustedgrossscore = $AGS where id = $ScoreID";
    mysql_query($sql) or die("Could not update adjusted gross score: " . mysql_error());
}

function GetAdjustedGrossScore($ScoreID)
{
	$sql = "select * from score_tbl where id = $ScoreID";
    //print("$sql");
	$result = mysql_query($sql) or die("Could not get score: " . mysql_error());
	$row = mysql_fetch_array($result);
    
    // Load up the score array
    $ScoreArray = array();
    for ( $i = 1; $i <= 9; $i++ )
    {
        $rowName = "hole$i";
        $ScoreArray[] = $row[$rowName];
    }
    //printf("<br><br>Score Array:<br>");
    //print_r($ScoreArray);
    
    // We need the tees that were played
    $TeeID = $row['teeid'];
    
    // We need the course handicap
    GetCourseHandicap($TeeID, $CourseHandicap);
     //printf("<br>Course Handicap:  %s<br>", $CourseHandicap);
    
    // We need to get the equitable stroke control number
    $ESCVal = getESCValue($CourseHandicap);
    //printf("<br>ESC Value:  %s<br>", $ESCVal);
    
    // Load up an array full of esc values to compare with the score array
    $ESCArray = array();
    if ($ESCVal == "DOUBLE")
    {
        $ESCArray = GetParArray($TeeID);
        for ($i = 0; $i < count($ESCArray); $i++)
        {
            $ESCArray[$i] = $ESCArray[$i] + 2;
        }
    }
    else
    {
        for ($i = 0; $i < 18; $i++)
        {
            $ESCArray[$i] = $ESCVal;
        }
    }
    //printf("<br><br>ESC Array:<br>");
    //print_r($ESCArray);


    
    // Make sure none of the scores in the score array are larger than their corresponding esc value in the esc array
    for ( $i = 0; $i < 9; $i++ )
    {
        if (strlen($ScoreArray[$i]) > 0 && $ScoreArray[$i] > $ESCArray[$i])
            $ScoreArray[$i] = $ESCArray[$i];
    }
    //printf("<br><br>Score Array Adjusted for ESC:<br>");
    //print_r($ScoreArray);
    
    // Sum the array and return the adjusted gross score
    return array_sum($ScoreArray);
    
}

// THIS FUNCTION CALCULATES THE HANDICAP DIFFERENTIAL FOR THE ROW PASSED TO IT.
function calcDifi($id)
{
	$ags = GetAdjustedGrossScore($id);
	$sql = "select tee.slope as slope, tee.rating as rating from tee_tbl tee, score_tbl score where tee.id = score.teeid and score.id = $id";
	$result = mysql_query($sql) or die("Could not get slope and rating of course: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		die("Could not get score associated to id passed to function.");
	$row = mysql_fetch_array($result);
	$rating = $row["rating"];
	$slope = $row["slope"];
	$difii = (($ags -$rating) * 113) / $slope;
	return $difii;
}

function NumberOfScoresToUse($NumberOfScoresAvailable)
{
    if ( $NumberOfScoresAvailable < 5 )
        return 0;
	else if ( $NumberOfScoresAvailable <= 6 )
        return 1;
	else if ( $NumberOfScoresAvailable <= 8 )
        return 2;
	else if ( $NumberOfScoresAvailable <= 10 )
	    return 3;
	else if ( $NumberOfScoresAvailable <= 12 )
	    return 4;
	else if ( $NumberOfScoresAvailable <= 14 )
	    return 5;
	else if ( $NumberOfScoresAvailable <= 16 )
	    return 6;
	else if ( $NumberOfScoresAvailable <= 17 )
	    return 7;
	else if ( $NumberOfScoresAvailable <= 18 )
	    return 8;
	else if ( $NumberOfScoresAvailable <= 19 )
        return 9;
	return 10;
}

function numDifsScoresToUse($uid, $id, &$numDifs, &$numScores)
{
	$sql = "select id from score_tbl where userid = '$uid' and id <= $id order by dateplayed desc";
	$result = mysql_query($sql) or die("Could not get score history when calculating handicap: " . mysql_error());
	$numAvailScores = mysql_num_rows($result);
	if ( $numAvailScores < 5 )
	{
		$numDifs = 0;
		$numScores = 0;
	}
	else if ( $numAvailScores <= 6 )
	{
		$numDifs = 1;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 8 )
	{
		$numDifs = 2;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 10 )
	{
		$numDifs = 3;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 12 )
	{
		$numDifs = 4;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 14 )
	{
		$numDifs = 5;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 16 )
	{
		$numDifs = 6;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 17 )
	{
		$numDifs = 7;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 18 )
	{
		$numDifs = 8;
		$numScores = $numAvailScores;
	}
	else if ( $numAvailScores <= 19 )
	{
		$numDifs = 9;
		$numScores = $numAvailScores;
	}
	else 
	{
		$numDifs = 10;
		$numScores = 20;
	}
	return $result;
}

function getMostRecentScore()
{
	$uid = $_SESSION['userid'];
	//$sql = "select id from score_tbl where userid = '$uid' order by dateplayed desc";
	$sql = "select id from score_tbl where userid = '$uid' order by id desc";
	$result = mysql_query($sql) or die("Could not get most recent score when calculating handicap: " . mysql_error());
	$numAvailScores = mysql_num_rows($result);
	if ( $numAvailScores == 0 )
		return "Not Enough Scores";
	else
	{
		$row = mysql_fetch_array($result);
		return $row["id"];
	}
}

function formatHandi($h)
{
	/*
	if ( $h == 0 )
		$h = "0.0";
	$pos = strpos($h, ".");
	if ( ($pos + 2) > (strlen($h) - 1) )	// make sure we don't substr past the end of the string.
		return $h;
	else
		return substr( $h, 0, ($pos + 2) );
	*/
	
	if ( $h == 0 )
	{
		$h = "";
	}
	else
	{
		$pos = strpos($h, ".");
		if ( ($pos + 2) > (strlen($h) - 1) )	// make sure we don't substr past the end of the string.
			return $h;
		else
			return substr( $h, 0, ($pos + 2) );
	}
}

function formatPuttHandi($h)
{
	/*
	if ( $h == 0 )
		$h = "0.0";
	$pos = strpos($h, ".");
	if ( ($pos + 2) > (strlen($h) - 1) )	// make sure we don't substr past the end of the string.
		return $h;
	else
		return substr( $h, 0, ($pos + 2) );
	*/
	
	if ( $h == 0 )
	{
		return $h;
	}
	else
	{
		$pos = strpos($h, ".");
		if ( ($pos + 2) > (strlen($h) - 1) )	// make sure we don't substr past the end of the string.
			return $h;
		else
			return substr( $h, 0, ($pos + 2) );
	}
}

function UpdateHandicapIndex()
{
    CalculateHandicapIndex($HandicapIndex, $Label);
    $uid = $_SESSION['userid'];

    $sql = "INSERT INTO handicap_tbl (num, userid, label) VALUES($HandicapIndex, $uid, '$Label')";
    //printf("SQL:  %s<br>", $sql);
    //print("Handicap Index:  $HandicapIndex<br>");
    if (!is_numeric($HandicapIndex))
    {
        $sql = "INSERT INTO handicap_tbl (num, userid) VALUES(null, $uid)";
    }
    //printf("SQL:  %s", $sql);
    mysql_query($sql) or die("Could not update handicap: " . mysql_error());
}





function CalculateHandicapIndex(&$HandicapIndex, &$Label)
{
    //Handicap Differential = (Adjusted Gross Score - USGA Course Rating) x 113 / Slope Rating
    //Step 2: Determine Handicap Differential(s); 
    //Step 3: Average the Handicap Differential(s) being used best 10 out of last 20 
    //Step 4: Multiply the average by.96; 
    //Step 5: Delete all numbers after the tenths' digit (truncate). Do not round to the nearest tenth. 
    
	$uid = $_SESSION['userid'];
	
	// GET AN ARRAY OF ALL DIFFERENTIALS TO USE
    $sql = "select ((os.total-os.rating)*113)/os.slope as differential from tools_officialscore os where userid = $uid order by dateplayed desc limit 20";
    //printf("%s<BR>", $sql);
    $result = mysql_query($sql) or die("Could not get differentials: " . mysql_error());
    $NumberOfScoresAvailable = mysql_num_rows($result);
    if ($NumberOfScoresAvailable < 1)
    {
        $HandicapIndex = "N/A";
        return;
    }
	$difArray = array();
    while ($row = mysql_fetch_array($result))
        $difArray[] = $row["differential"];

    // sort the array in ascending order    
    sort($difArray, SORT_NUMERIC);
    //print_r($difArray);

    // Find out how many scores we should use to calculate the handicap index.
	$NumScoresToUse = NumberOfScoresToUse($NumberOfScoresAvailable);

	if ( $NumScoresToUse == 0 )
	{
        $HandicapIndex = "N/A";
        return;
    }
	//printf("<br>Num of Scores to use:  %s<BR>", $NumScoresToUse);
    

	// AVERAGE THE "X" NUMBER OF LOWEST DIFFS AND CALC HANDI
	$total = 0;
	for ( $i = 0; $i < $NumScoresToUse; $i++ )
		$total += $difArray[$i];
    //printf("<br>Total:  %s<BR>", $total);
        
	$HandicapIndex = (($total / $NumScoresToUse) * .96);
    $Label = "N";
	//printf("Handicap Index:  %s", $HandicapIndex);
    
    
    //print("<br>Handicap Index Prior To Checking for Reduction:  $HandicapIndex");
    //ReduceForExceptionalTournamentScores($HandicapIndex, $Label);
}

function ReduceForExceptionalTournamentScores(&$CurrentHandicapIndex, &$Label)
{
    $uid = $_SESSION['userid'];
	
	// Get differentials for the past calander year, if there are not at least 20 we need to go back further to get 20 or failing 20 as many as we can.
    $sql = "select ((os.total-os.rating)*113)/os.slope as differential, os.* from tools_officialscore os where userid = $uid and dateplayed > DATE_SUB(CURDATE(), INTERVAL 1 YEAR) order by dateplayed desc";
    //printf("%s<BR>", $sql);
    $result = mysql_query($sql) or die("Could not get differentials: " . mysql_error());
    $NumberOfScoresAvailable = mysql_num_rows($result);
    if ($NumberOfScoresAvailable < 20)
    {
        $sql = "select ((os.total-os.rating)*113)/os.slope as differential, os.* from tools_officialscore os where userid = $uid order by dateplayed desc limit 20";
        //printf("%s<BR>", $sql);
        $result = mysql_query($sql) or die("Could not get differentials: " . mysql_error());
    }
    
    // Pull out all the tournament scores.
	$DifArray = array();
    while ($row = mysql_fetch_array($result))
    {
        extract($row);
        if (strpbrk($type, "T"))
            $DifArray[] = $differential;
    }
    //print_r($DifArray);
    
    $NumTournamentScores = count($DifArray);
    if ($NumTournamentScores < 2)
        return;  // Do Nothing, not enough tournament scores
    else
    {
        sort($DifArray, SORT_NUMERIC);  // sort the array in ascending order    
        //print_r($DifArray);
        if ( ($CurrentHandicapIndex - $DifArray[1]) < 3 )
            return;  // Do Nothing, tournament scores not low enough
        else
        {
            $avg = round ($CurrentHandicapIndex - (($DifArray[0] + $DifArray[1]) / 2), 1);
        }
    }
    
    if ($avg < 4)
        return;  // Do Nothing, avg tournament scores not low enough
        
    // set up handicap reduction table
    $_40 = array(1);
    $_45 = array(1.8, 1);
    $_50 = array(2.6, 1.9, 1);
    $_55 = array(3.4, 2.7, 1.9, 1);
    $_60 = array(4.1, 3.5, 2.8, 1.9, 1);
    $_65 = array(4.8, 4.3, 3.7, 2.9, 2, 1);
    $_70 = array(5.5, 5, 4.5, 3.8, 3, 2.1, 1);
    $_75 = array(6.2, 5.7, 5.3, 4.7, 3.9, 3.1, 2.2, 1);
    $_80 = array(6.8, 6.4, 6, 5.5, 4.8, 4.1, 3.2, 2.2);
    $_85 = array(7.4, 7.1, 6.7, 6.2, 5.7, 5, 4.2, 3.3);
    $_90 = array(8.1, 7.8, 7.4, 7, 6.5, 5.9, 5.2, 4.4);
    $_95 = array(8.7, 8.4, 8.1, 7.7, 7.3, 6.7, 6.1, 5.4);
    $_100 = array(9.2, 9, 8.8, 8.4, 8, 7.6, 7, 6.4);
    $_105 = array(9.8, 9.5, 9.4, 9.1, 8.7, 8.3, 7.8, 7.2);
    $_110 = array(10.4, 10.2, 10, 9.7, 9.4, 9.1, 8.6, 8.1);
    $_115 = array(11, 10.8, 10.6, 10.4, 10.1, 9.8, 9.4, 8.9);
    $_120 = array(11.5, 11.4, 11.2, 11, 10.7, 10.5, 10.1, 9.7);
    $_125 = array(12.1, 11.9, 11.8, 11.6, 11.4, 11.1, 10.8, 10.5);
    $_130 = array(12.6, 12.5, 12.4, 12.2, 12, 11.8, 11.5, 11.2);
    $_135 = array(13.2, 13.1, 12.9, 12.8, 12.6, 12.4, 12.2, 11.9);
    $_140 = array(13.7, 13.6, 13.5, 13.4, 13.2, 13, 12.8, 12.6);
    
    if ($avg >= 14.0)
    {
        $ReductionAmount = GetReductionAmount($_140, $NumTournamentScores);
    }
    else if ($avg >= 13.5)
    {
        $ReductionAmount = GetReductionAmount($_135, $NumTournamentScores);
    }
    else if ($avg >= 13)
    {
        $ReductionAmount = GetReductionAmount($_130, $NumTournamentScores);
    }
    else if ($avg >= 12.5)
    {
        $ReductionAmount = GetReductionAmount($_125, $NumTournamentScores);
    }
    else if ($avg >= 12.0)
    {
        $ReductionAmount = GetReductionAmount($_120, $NumTournamentScores);
    }
    else if ($avg >= 11.5)
    {
        $ReductionAmount = GetReductionAmount($_115, $NumTournamentScores);
    }
    else if ($avg >= 11.0)
    {
        $ReductionAmount = GetReductionAmount($_110, $NumTournamentScores);
    }
    else if ($avg >= 10.5)
    {
        $ReductionAmount = GetReductionAmount($_105, $NumTournamentScores);
    }
    else if ($avg >= 10.0)
    {
        $ReductionAmount = GetReductionAmount($_100, $NumTournamentScores);
    }
    else if ($avg >= 9.5)
    {
        $ReductionAmount = GetReductionAmount($_95, $NumTournamentScores);
    }
    else if ($avg >= 9.0)
    {
        $ReductionAmount = GetReductionAmount($_90, $NumTournamentScores);
    }
    else if ($avg >= 8.5)
    {
        $ReductionAmount = GetReductionAmount($_85, $NumTournamentScores);
    }
    else if ($avg >= 8)
    {
        $ReductionAmount = GetReductionAmount($_80, $NumTournamentScores);
    }
    else if ($avg >= 7.5)
    {
        $ReductionAmount = GetReductionAmount($_75, $NumTournamentScores);
    }
    else if ($avg >= 7)
    {
        $ReductionAmount = GetReductionAmount($_70, $NumTournamentScores);
    }
    else if ($avg >= 6.5)
    {
        $ReductionAmount = GetReductionAmount($_65, $NumTournamentScores);
    }
    else if ($avg >= 6)
    {
        $ReductionAmount = GetReductionAmount($_60, $NumTournamentScores);
    }
    else if ($avg >= 5.5)
    {
        $ReductionAmount = GetReductionAmount($_55, $NumTournamentScores);
    }
    else if ($avg >= 5)
    {
        $ReductionAmount = GetReductionAmount($_50, $NumTournamentScores);
    }
    else if ($avg >= 4.5)
    {
        $ReductionAmount = GetReductionAmount($_45, $NumTournamentScores);
    }
    else if ($avg >= 4)
    {
        $ReductionAmount = GetReductionAmount($_40, $NumTournamentScores);
    }
    
    if ($ReductionAmount != null)
    {
        $CurrentHandicapIndex = $CurrentHandicapIndex - $ReductionAmount;
        $Label = "R";
    }
}

function GetReductionAmount($ReductionArray, $NumTournamentScores)
{
    if ($NumTournamentScores >= 40)
    {
        if (count($ReductionArray) < 8)
            return null;
        return $ReductionArray[7];
    }
    else if ($NumTournamentScores >= 30)
    {
        if (count($ReductionArray) < 7)
            return null;
        return $ReductionArray[6];
    }
    else if ($NumTournamentScores >= 20)
    {
        if (count($ReductionArray) < 6)
            return null;
        return $ReductionArray[5];
    }
    else if ($NumTournamentScores >= 10)
    {
        if (count($ReductionArray) < 5)
            return null;
        return $ReductionArray[4];
    }
    else if ($NumTournamentScores >= 5)
    {
        if (count($ReductionArray) < 4)
            return null;
        return $ReductionArray[3];
    }
    else if ($NumTournamentScores >= 4)
    {
        if (count($ReductionArray) < 3)
            return null;
        return $ReductionArray[2];
    }
    else if ($NumTournamentScores >= 3)
    {
        if (count($ReductionArray) < 2)
            return null;
        return $ReductionArray[1];
    }
    else if ($NumTournamentScores >= 2)
    {
        if (count($ReductionArray) < 1)
            return null;
        return $ReductionArray[0];
    }
}

//
//  Returns an array of the official score ids used in the handicap calcualtion
//
function GetHandiIDs()
{
    //  Get the last revision date
    $LastRevisionDate = GetLastHandicapRevisionDate();
    if ($LastRevisionDate == null)
        return array();

    $uid = $_SESSION['userid'];
	
	// GET AN ARRAY OF ALL DIFFERENTIALS TO USE
    $sql = "select id, ((os.total-os.rating)*113)/os.slope as differential from tools_officialscore os where userid = $uid and dateplayed <= '$LastRevisionDate' order by dateplayed desc limit 20";
    $result = mysql_query($sql) or die("Could not get differentials: " . mysql_error());
    $NumberOfScoresAvailable = mysql_num_rows($result);
    if ($NumberOfScoresAvailable < 1)
        return array();
	$difArray = array();
    $idArray = array();
    while ($row = mysql_fetch_array($result))
    {
        $difArray[] = $row["differential"];
        $idArray[] = $row["id"];
    }
        
    // sort the array in ascending order    
    /*
    sort($difArray, SORT_NUMERIC);
    printf("DIF ARRAY PRIOR TO SORT:<br>");
    print_r($difArray);
    printf("<br>ID ARRAY PRIOR TO SORT:<br>");
    print_r($idArray);
    */
    array_multisort($difArray, SORT_NUMERIC, $idArray, SORT_ASC);
    /*
    printf("<br><br>DIF ARRAY AFTER SORT:<br>");
    print_r($difArray);
    printf("<br>ID ARRAY AFTER SORT:<br>");
    print_r($idArray);
    */
    // Find out how many scores we should use to calculate the handicap index.
	$NumScoresToUse = NumberOfScoresToUse($NumberOfScoresAvailable);
	if ( $NumScoresToUse == 0 )
		return array();
    //printf("<br>Num of Scores available:  %s<BR>", $NumberOfScoresAvailable);
	//printf("<br>Num of Scores to use:  %s<BR>", $NumScoresToUse);
        
        
	// AVERAGE THE "X" NUMBER OF LOWEST DIFFS AND CALC HANDI
    $IDs = array();
	$total = 0;
	for ( $i = 0; $i < $NumScoresToUse; $i++ )
		$IDs[] = $idArray[$i];
    
    //printf("<br><br>ID ARRAY BEFORE RETURN:<br>");    
    //print_r($IDs);
    return $IDs;

}


function getHandiIDArray()	// returns an array of golfscore ids that are used to calculate current handicap
{
	$uid = $_SESSION['userid'];
	
	$id = getMostRecentScore();
	if ( $id == "Not Enough Scores" )
		return array();

	
	// GET NUMBER OF HANDICAP DIFFERENTIALS AND NUMBER OF SCORES TO USE WHEN CALCULATING HANDICAP
	$result = numDifsScoresToUse($uid, $id, $numDifs, $numScores);
	if ( $numDifs == 0 )
		return array();
	
	// GET AN ARRAY OF ALL IDS TO USE
	$difArray = array();
	for ( $i = 0; $i < $numScores; $i++ )
	{
		$row = mysql_fetch_array($result);
		$difArray[$row["id"]] = calcDifi($row["id"]);
	}
	
	// SORT THE ARRAY FROM LOWEST TO HIGHEST DIFFERENTIAL
	asort($difArray, SORT_NUMERIC);
	reset($difArray);	// set the array pointer back to the first element
	
	$newArray = array();
	for ( $i = 0; $i < $numDifs; $i++ )
	{
		list($key, $val) = each($difArray);
		$newArray[] = $key;
	}	
	return $newArray;
}

function getParArray ( $teeid )
{
	$parArray = array();
	$sql = "select * from tee_tbl where id = $teeid";
	$result = mysql_query($sql) or die("Could not list of par values: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		die("Could not get course data for tee id %s" . $courseID);
	$row = mysql_fetch_array($result);
	$parArray[] = $row["par1"];
	$parArray[] = $row["par2"];
	$parArray[] = $row["par3"];
	$parArray[] = $row["par4"];
	$parArray[] = $row["par5"];
	$parArray[] = $row["par6"];
	$parArray[] = $row["par7"];
	$parArray[] = $row["par8"];
	$parArray[] = $row["par9"];
	$parArray[] = $row["par10"];
	$parArray[] = $row["par11"];
	$parArray[] = $row["par12"];
	$parArray[] = $row["par13"];
	$parArray[] = $row["par14"];
	$parArray[] = $row["par15"];
	$parArray[] = $row["par16"];
	$parArray[] = $row["par17"];
	$parArray[] = $row["par18"];
	return $parArray;
}


function getBestToWorstHoleArray ( $courseAverageArray, $teeid, $fromdate, $todate )
{
	$parArray = array();
	$parArray = getParArray($teeid);
	$difArray = array();	// score - par
	for ($i = 0; $i < 18; $i++)
	{
		if ( $courseAverageArray[$i] != "" && $courseAverageArray[$i] != 0 )
			$difArray[$i] = $courseAverageArray[$i] - $parArray[$i];
	}
	
		
	for ($i = 0; $i < count($difArray); $i++)
	{
		$difArray[$i] = formatHandi($difArray[$i]);
	}
	
	
	asort( $difArray, SORT_NUMERIC );
	
	$valKeySortedArray = array();
	$valKeySortedArray = sortArrayByKeyWithinVal($difArray);
	
	
   	$bestToWorstHoleArray = array();
	while (list($key, $val) = each($valKeySortedArray)) 
   		$bestToWorstHoleArray[] = $key + 1;
	
	
	return $bestToWorstHoleArray;
}

function printArray($theArray)
{
	while (list($key, $val) = each($theArray)) 
   		echo "$key = $val<br>";
	echo "<BR><BR>";
}

function sortArrayByKeyWithinVal($theArray)
{
	$masterArray = array();
	$masterArray[] = array();
	$masterArrayCnt = 0;
	$currentVal = current($theArray);
	while (list($key, $val) = each($theArray)) 
	{
		if ( $val != $currentVal )
		{
			$masterArray[] = array();
			$masterArrayCnt++;
			$currentVal = $val;
		}
		$masterArray[$masterArrayCnt][$key] = $val;
	}
	
	$rtnArray = array();
	for ($i = 0; $i < count($masterArray); $i++)
		ksort($masterArray[$i]);	
		
	for ($i = 0; $i < count($masterArray); $i++)
	{
		while (list($key, $val) = each($masterArray[$i])) 
			$rtnArray[$key] = $val;
	}
	
	return $rtnArray;
	//printf("<br>count:  %s<br>", count($masterArray));
}


function getWorstAverageHoleArray ( $courseAverageArray, $teeid, $fromdate, $todate )
{
	$parArray = array();
	$parArray = getParArray($teeid);
	$difArray = array();	// score - par
	for ($i = 0; $i < 9; $i++)
	{
		if ( $courseAverageArray[$i] != "" && $courseAverageArray[$i] != 0 )
			$difArray[$i] = $courseAverageArray[$i] - $parArray[$i];
	}
	
	for ($i = 0; $i < count($difArray); $i++)
	{
		$difArray[$i] = formatHandi($difArray[$i]);
	}
	
	arsort( $difArray, SORT_NUMERIC );
	
   	reset( $difArray );
   	$worstHoleArray = array();
   	$dif = current($difArray);
   	
   	while ($dif == current($difArray)) 
   	{
   		$worstHoleArray[] = (key($difArray) + 1);	// Add one to account for the zero index.
   		next($difArray);
	}

	sort($worstHoleArray, SORT_NUMERIC);	
	return $worstHoleArray;
}

function getBestAverageHoleArray ( $courseAverageArray, $teeid, $fromdate, $todate )
{
	$parArray = array();
	$parArray = getParArray($teeid);
	$difArray = array();	// score - par
	for ($i = 0; $i < 9; $i++)
	{
		if ( $courseAverageArray[$i] != "" && $courseAverageArray[$i] != 0 )
			$difArray[$i] = $courseAverageArray[$i] - $parArray[$i];
	}
	for ($i = 0; $i < count($difArray); $i++)
	{
		$difArray[$i] = formatHandi($difArray[$i]);
	}
	asort( $difArray, SORT_NUMERIC );
	
   	reset( $difArray );
   	$bestHoleArray = array();
   	$dif = current($difArray);
   	
   	while ($dif == current($difArray)) 
   	{
   		$bestHoleArray[] = (key($difArray) + 1);	// Add one to account for the zero index.
   		next($difArray);
	}
	
	sort($bestHoleArray, SORT_NUMERIC);	
	return $bestHoleArray;
	
	/*
	while (list($key, $val) = each($difArray)) 
	{
   		echo "$key = $val<br>";
	}
	echo "<BR>";
	reset( $difArray );
	*/	
}

function getPuttAverageForHole ($teeid, $holeNm, $fromdate, $todate)
{
	$total = 0;
	$avg = 0;
	$sql = "";
	if ( $fromdate != "" && $todate != "" )
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm is not null and dateplayed >= $fromdate and dateplayed <= $todate";
	else if ( $fromdate != "" )
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm is not null and dateplayed >= $fromdate";
	else if ( $todate != "" )
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm is not null and dateplayed <= $todate";
	else
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm is not null";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select putt average: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
	{
		//printf("Got here");
		return "";
	}
	else
	{
		while ($row = mysql_fetch_array($result))
		{
			$total += $row["score"];
		}
		$avg = ( $total / mysql_num_rows($result) );
		return $avg;
	}
}

function getAverageForHole ($teeid, $holeNm, $fromdate, $todate)
{
	$total = 0;
	$avg = 0;
	$sql = "";
	if ( $fromdate != "" && $todate != "" )
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm != 0 and $holeNm is not null and dateplayed >= $fromdate and dateplayed <= $todate";
	else if ( $fromdate != "" )
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm != 0 and $holeNm is not null and dateplayed >= $fromdate";
	else if ( $todate != "" )
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm != 0 and $holeNm is not null and dateplayed <= $todate";
	else
		$sql = "select $holeNm as score from score_tbl where teeid = $teeid and $holeNm != 0 and $holeNm is not null";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select average for hole: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		return "";
	else
	{
		while ($row = mysql_fetch_array($result))
		{
			$total += $row["score"];
		}
		$avg = ( $total / mysql_num_rows($result) );
		return $avg;
	}
}

function getAverageForFrontNine ($teeid, $fromdate, $todate)
{
	$total = 0;
	$avg = 0;
	$sql = "";
	if ( $fromdate != "" && $todate != "" )
		$sql = "select hole1 + hole2 + hole3 + hole4 + hole5 + hole6 + hole7 + hole8 + hole9 as score from score_tbl where teeid = $teeid and hole1 != 0 and hole1 is not null and hole2 != 0 and hole2 is not null and hole3 != 0 and hole3 is not null and hole4 != 0 and hole4 is not null and hole5 != 0 and hole5 is not null and hole6 != 0 and hole6 is not null and hole7 != 0 and hole7 is not null and hole8 != 0 and hole8 is not null and hole9 != 0 and hole9 is not null and dateplayed >= $fromdate and dateplayed <= $todate";
	else if ( $fromdate != "" )
		$sql = "select hole1 + hole2 + hole3 + hole4 + hole5 + hole6 + hole7 + hole8 + hole9 as score from score_tbl where teeid = $teeid and hole1 != 0 and hole1 is not null and hole2 != 0 and hole2 is not null and hole3 != 0 and hole3 is not null and hole4 != 0 and hole4 is not null and hole5 != 0 and hole5 is not null and hole6 != 0 and hole6 is not null and hole7 != 0 and hole7 is not null and hole8 != 0 and hole8 is not null and hole9 != 0 and hole9 is not null and dateplayed >= $fromdate";
	else if ( $todate != "" )
		$sql = "select hole1 + hole2 + hole3 + hole4 + hole5 + hole6 + hole7 + hole8 + hole9 as score from score_tbl where teeid = $teeid and hole1 != 0 and hole1 is not null and hole2 != 0 and hole2 is not null and hole3 != 0 and hole3 is not null and hole4 != 0 and hole4 is not null and hole5 != 0 and hole5 is not null and hole6 != 0 and hole6 is not null and hole7 != 0 and hole7 is not null and hole8 != 0 and hole8 is not null and hole9 != 0 and hole9 is not null and dateplayed <= $todate";
	else
		$sql = "select hole1 + hole2 + hole3 + hole4 + hole5 + hole6 + hole7 + hole8 + hole9 as score from score_tbl where teeid = $teeid and hole1 != 0 and hole1 is not null and hole2 != 0 and hole2 is not null and hole3 != 0 and hole3 is not null and hole4 != 0 and hole4 is not null and hole5 != 0 and hole5 is not null and hole6 != 0 and hole6 is not null and hole7 != 0 and hole7 is not null and hole8 != 0 and hole8 is not null and hole9 != 0 and hole9 is not null";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history for front nine: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		return "";
	else
	{
		while ($row = mysql_fetch_array($result))
		{
			$total += $row["score"];
		}
		$avg = ( $total / mysql_num_rows($result) );
		return $avg;
	}
}



function getPuttAverageForFrontNine ($teeid, $fromdate, $todate)
{
	$total = 0;
	$avg = 0;
	$sql = "";
	if ( $fromdate != "" && $todate != "" )
		$sql = "select putt1 + putt2 + putt3 + putt4 + putt5 + putt6 + putt7 + putt8 + putt9 as score from score_tbl where teeid = $teeid and putt1 is not null and putt2 is not null and putt3 is not null and putt4 is not null and putt5 is not null and putt6 is not null and putt7 is not null and putt8 is not null and putt9 is not null and dateplayed >= $fromdate and dateplayed <= $todate";
	else if ( $fromdate != "" )
		$sql = "select putt1 + putt2 + putt3 + putt4 + putt5 + putt6 + putt7 + putt8 + putt9 as score from score_tbl where teeid = $teeid and putt1 is not null and putt2 is not null and putt3 is not null and putt4 is not null and putt5 is not null and putt6 is not null and putt7 is not null and putt8 is not null and putt9 is not null and dateplayed >= $fromdate";
		//$sql = "select putt1 + putt2 + putt3 + putt4 + putt5 + putt6 + putt7 + putt8 + putt9 as score from score_tbl where teeid = $teeid and putt1 != 0 and putt1 is not null and putt2 != 0 and putt2 is not null and putt3 != 0 and putt3 is not null and putt4 != 0 and putt4 is not null and putt5 != 0 and putt5 is not null and putt6 != 0 and putt6 is not null and putt7 != 0 and putt7 is not null and putt8 != 0 and putt8 is not null and putt9 != 0 and putt9 is not null and dateplayed >= $fdate";
	else if ( $todate != "" )
		$sql = "select putt1 + putt2 + putt3 + putt4 + putt5 + putt6 + putt7 + putt8 + putt9 as score from score_tbl where teeid = $teeid and putt1 is not null and putt2 is not null and putt3 is not null and putt4 is not null and putt5 is not null and putt6 is not null and putt7 is not null and putt8 is not null and putt9 is not null and dateplayed <= $todate";
		//$sql = "select putt1 + putt2 + putt3 + putt4 + putt5 + putt6 + putt7 + putt8 + putt9 as score from score_tbl where teeid = $teeid and putt1 != 0 and putt1 is not null and putt2 != 0 and putt2 is not null and putt3 != 0 and putt3 is not null and putt4 != 0 and putt4 is not null and putt5 != 0 and putt5 is not null and putt6 != 0 and putt6 is not null and putt7 != 0 and putt7 is not null and putt8 != 0 and putt8 is not null and putt9 != 0 and putt9 is not null and dateplayed <= $tdate";
	else
		$sql = "select putt1 + putt2 + putt3 + putt4 + putt5 + putt6 + putt7 + putt8 + putt9 as score from score_tbl where teeid = $teeid and putt1 is not null and putt2 is not null and putt3 is not null and putt4 is not null and putt5 is not null and putt6 is not null and putt7 is not null and putt8 is not null and putt9 is not null";
		//$sql = "select putt1 + putt2 + putt3 + putt4 + putt5 + putt6 + putt7 + putt8 + putt9 as score from score_tbl where teeid = $teeid and putt1 != 0 and putt1 is not null and putt2 != 0 and putt2 is not null and putt3 != 0 and putt3 is not null and putt4 != 0 and putt4 is not null and putt5 != 0 and putt5 is not null and putt6 != 0 and putt6 is not null and putt7 != 0 and putt7 is not null and putt8 != 0 and putt8 is not null and putt9 != 0 and putt9 is not null";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history for front nine: " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		return "";
	else
	{
		while ($row = mysql_fetch_array($result))
		{
			$total += $row["score"];
		}
		$avg = ( $total / mysql_num_rows($result) );
		return $avg;
	}
}

function getCoursePuttAverageArray( $teeid, $fromdate, $todate )
{
	$sql = "";
	if ( $fromdate != "" && $todate != "" )
		$sql = "select * from score_tbl where teeid = $teeid and dateplayed >= '$fromdate' and dateplayed <= '$todate'";
	else if ( $fromdate != "" )
		$sql = "select * from score_tbl where teeid = $teeid and dateplayed >= '$fromdate'";
	else if ( $todate != "" )
		$sql = "select * from score_tbl where teeid = $teeid and dateplayed <= '$todate'";
	else
		$sql = "select * from score_tbl where teeid = $teeid";

	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	
	$scoreAvgArray = array();
	for ($i = 1; $i <= 9; $i++)
	{
		$holeNm = "putt";
		$holeNm .= $i;
		$scoreAvgArray[] = getPuttAverageForHole($teeid, $holeNm, $fromdate, $todate);
	}
	$scoreAvgArray[] = getPuttAverageForFrontNine($teeid, $fromdate, $todate);
	return $scoreAvgArray;
}

function getCourseAverageArray( $teeid, $fromdate, $todate )
{
	$sql = "";
	if ( $fromdate != "" && $todate != "" )
		$sql = "select * from score_tbl where teeid = $teeid and dateplayed >= '$fromdate' and dateplayed <= '$todate'";
	else if ( $fromdate != "" )
		$sql = "select * from score_tbl where teeid = $teeid and dateplayed >= '$fromdate'";
	else if ( $todate != "" )
		$sql = "select * from score_tbl where teeid = $teeid and dateplayed <= '$todate'";
	else
		$sql = "select * from score_tbl where teeid = $teeid";

	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	
	$scoreAvgArray = array();
	for ($i = 1; $i <= 9; $i++)
	{
		$holeNm = "hole";
		$holeNm .= $i;
		$scoreAvgArray[] = getAverageForHole($teeid, $holeNm, $fromdate, $todate);
	}
	$scoreAvgArray[] = getAverageForFrontNine($teeid, $fromdate, $todate);
	return $scoreAvgArray;
}

function getGreensArray( $teeid, $fromdate, $todate )
{
	$sql = "select * from score_tbl where teeid = $teeid";
	if ( strlen($fromdate) > 0 )
		$sql .= " and dateplayed >= '$fromdate' and dateplayed <= '$todate'";

	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	
	$greensArray = array();
	$greensHit = 0;
	$greensPossible = 0;
	while ($row = mysql_fetch_array($result))
	{
		if ($row["greens"] != null)
		{
			if ($row["hole1"] != null)
				$greensPossible += 9;
			if ($row["hole10"] != null)
				$greensPossible += 9;
			$greensHit += $row["greens"];
		}
	}
	$greensArray[] = $greensHit;
	$greensArray[] = $greensPossible;
	return $greensArray;
}

function getPercentFairways( $scoreid )
{
	$sql = "select * from score_tbl where id = $scoreid";
	
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	$row = mysql_fetch_array($result);
	
	$fairwaysArray = array();
	$fairwaysHit = 0;
	$fairwaysPossible = 0;
	$fairwaysFrontNine = getFairwaysPossible($row["teeid"], "Front");
	$fairwaysBackNine = getFairwaysPossible($row["teeid"], "Back");
	if ($row["fairways"] != null)
	{
		if ($row["hole1"] != null)
			$fairwaysPossible += $fairwaysFrontNine;
		if ($row["hole10"] != null)
			$fairwaysPossible += $fairwaysBackNine;
		$fairwaysHit += $row["fairways"];
		$perFairways = number_format(100*($fairwaysHit / $fairwaysPossible), 1);
		return $perFairways;
	}
	else
		return "N/A";
}



function getFairwaysArray( $teeid, $fromdate, $todate )
{
	$sql = "select * from score_tbl where teeid = $teeid";
	if ( strlen($fromdate) > 0 )
		$sql .= " and dateplayed >= '$fromdate' and dateplayed <= '$todate'";
		

	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	
	$fairwaysArray = array();
	$fairwaysHit = 0;
	$fairwaysPossible = 0;
	$fairwaysFrontNine = getFairwaysPossible($teeid, "Front");
	$fairwaysBackNine = getFairwaysPossible($teeid, "Back");
	while ($row = mysql_fetch_array($result))
	{
		if ($row["fairways"] != null)
		{
			if ($row["hole1"] != null)
				$fairwaysPossible += $fairwaysFrontNine;
			if ($row["hole10"] != null)
				$fairwaysPossible += $fairwaysBackNine;
			$fairwaysHit += $row["fairways"];
		}
	}
	$fairwaysArray[] = $fairwaysHit;
	$fairwaysArray[] = $fairwaysPossible;
    //print_r($fairwaysArray);
	return $fairwaysArray;
}

function getpenaltiesArray( $teeid, $fromdate, $todate )
{
	$sql = "select * from score_tbl where teeid = $teeid";
	if ( strlen($fromdate) > 0 )
		$sql .= " and dateplayed >= '$fromdate' and dateplayed <= '$todate'";
	
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	
	$penaltiesArray = array();
	$holesPlayed = 0;
	$penalties = 0;
	while ($row = mysql_fetch_array($result))
	{
		if ($row["penalties"] != null)
		{
			if ($row["hole1"] != null)
				$holesPlayed += 9;
			if ($row["hole10"] != null)
				$holesPlayed += 9;
			$penalties += $row["penalties"];
		}
	}
	$penaltiesArray[] = $penalties;
	$penaltiesArray[] = $holesPlayed;
	return $penaltiesArray;
}

function getFairwaysPossible( $teeid, $x )
{
	$sql = "select * from tee_tbl where id = $teeid";
	$result = mysql_query($sql) or die("Could not get course details: " . mysql_error());
	$row = mysql_fetch_array($result);
	$num = 0;
	if ($x == "Front")
	{
		if ($row["par1"] > 3)
			$num++;
		if ($row["par2"] > 3)
			$num++;
		if ($row["par3"] > 3)
			$num++;
		if ($row["par4"] > 3)
			$num++;
		if ($row["par5"] > 3)
			$num++;
		if ($row["par6"] > 3)
			$num++;
		if ($row["par7"] > 3)
			$num++;
		if ($row["par8"] > 3)
			$num++;
		if ($row["par9"] > 3)
			$num++;
	}
	else if ($x == "Back")
	{
		if ($row["par10"] > 3)
			$num++;
		if ($row["par11"] > 3)
			$num++;
		if ($row["par12"] > 3)
			$num++;
		if ($row["par13"] > 3)
			$num++;
		if ($row["par14"] > 3)
			$num++;
		if ($row["par15"] > 3)
			$num++;
		if ($row["par16"] > 3)
			$num++;
		if ($row["par17"] > 3)
			$num++;
		if ($row["par18"] > 3)
			$num++;
	}
	else
		die("Invalid second parm to function getFairwaysPossible()");
	return $num;
}


//
//  THIS FUNCTION WILL TELL YOU IF THE COURSE IS A NINE HOLE COURSE
//
function isNineHole( $teeid )
{
	$sql = "select * from tee_tbl where id = $teeid";
	//printf("%s", $sql);
	$result = mysql_query($sql) or die("Could not get course details: " . mysql_error());
	$row = mysql_fetch_array($result);
	if ($row["par10"] == null)
		return 1;
	if ($row["par11"] == null)
		return 1;
	if ($row["par12"] == null)
		return 1;
	if ($row["par13"] == null)
		return 1;
	if ($row["par14"] == null)
		return 1;
	if ($row["par15"] == null)
		return 1;
	if ($row["par16"] == null)
		return 1;
	if ($row["par17"] == null)
		return 1;
	if ($row["par18"] == null)
		return 1;
	return 0;
}

//
//  THIS FUNCTION WILL TELL YOU IF THE SCORE IS A 9 HOLE SCORE
//
function isNineHoleScore( $ScoreID )
{
	$sql = "select * from score_tbl where id = $ScoreID";
	$result = mysql_query($sql) or die("Could not get score: " . mysql_error());
	extract(mysql_fetch_array($result));
    if ($hole1 == null && $hole10 == null && $adjustedgrossscore > 0)
    {
		return 0;  // this score was entered as a quick score.  No individual scores, only an adjusted gross score.
    }
	if ($hole1 == null)
    {
		return 1;
    }
	if ($hole10 == null)
    {
		return 1;
    }
	return 0;
}



function getDateControl( $theDate="", $suffix="" )
{
	if ( $theDate == "" )
		$my_t=getdate();
	else
		$my_t=$theDate;
		
	$monthArray = array("Dummy", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
	printf("<select name=\"TheMonth%s\">", $suffix);
		for ($i = 1; $i <= 12; $i++)
		{
			if ($my_t[mon] == $i)
				printf("<option value=\"$i\" selected=\"selected\">$monthArray[$i]</option>");
			else
				printf("<option value=\"$i\">$monthArray[$i]</option>");
		}
	printf("</select>");
	
	printf("<select name=\"TheDay%s\">", $suffix);
		
		for ($i = 1; $i <= 31; $i++)
		{
			if ($my_t[mday] == $i)
				printf("<option value=\"$i\" selected=\"selected\">$i</option>");
			else
				printf("<option value=\"$i\">$i</option>");
		}
	printf("</select>");
	
	printf("<select name=\"TheYear%s\">", $suffix);

		for ($i = 2008; $i <= 2025; $i++)
		{
			if ($my_t[year] == $i)
				printf("<option value=\"$i\" selected=\"selected\">$i</option>");
			else
				printf("<option value=\"$i\">$i</option>");
		}
	printf("</select>");
}

function DisScoreEntryForm( $courseID, $postPage )
{
	$sql = "select ct.name as CourseName, tt.name as TeeName, tt.id as TeeID, tt.* from course_tbl ct, tee_tbl tt where tt.courseid = ct.id and ct.id = ";
	//$sql = "select ct.name as CourseName, tt.name as TeeName, tt.id as TeeID, tt.* from course_tbl ct LEFT JOIN tee_tbl tt on ct.id = tt.courseid where ct.id =";
	//$sql = "select * from course_tbl ct, tee_tbl tt where tt.courseid = ct.id and ct.id = ";
	$sql .= $courseID;
	//printf("%s", $sql);
	$result = mysql_query($sql) or die("Could not get selected course: " . mysql_error());
	$row = mysql_fetch_array($result);
	$teesArray = array();
	$teesArray = $row;
	
	printf("<form action=\"%s\" method=\"POST\" name=\"scorecard\">", $postPage);
	printf("<table class=\"CourseTable\">");
	printf("<td>");
	printf("<table>");
	printf("<tr>");
	printf("<tr><td><b>Course Name:</b> </td><td><input class=\"InputBoxWidth\" disabled type=\"text\" name=\"CourseName\" value=\"%s\"></td></tr>", $row["CourseName"]);
	printf("<tr><td><b>Tees:</b> </td><td>");
	
	printf("<select onchange=\"GetTees(this.value, PopulateScorecardParValues)\" class=\"InputBoxWidth\" name=\"Tees\">");
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
	printf("</td></tr>");
	printf("</table> ");
	
	printf("<table class=\"ScoreCardTable\">");
	?>
	<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
	<?
	printf("<tr><td><b>Hole:</b></td><td class=\"HoleNumberBox\">1</td><td class=\"HoleNumberBox\">2</td><td class=\"HoleNumberBox\">3</td><td class=\"HoleNumberBox\">4</td><td class=\"HoleNumberBox\">5</td><td class=\"HoleNumberBox\">6</td><td class=\"HoleNumberBox\">7</td><td class=\"HoleNumberBox\">8</td><td class=\"HoleNumberBox\">9</td><td class=\"HoleNumberBox\"><b>Out<b></td></tr>");
	printf("<tr><td><b>Par:</b></td>
	<td name=\"ParHole1\" class=\"HoleNumberBox\"><div id=\"ParHole1\">%s</div></td>
	<td name=\"ParHole2\" class=\"HoleNumberBox\"><div id=\"ParHole2\">%s</div></td>
	<td name=\"ParHole3\" class=\"HoleNumberBox\"><div id=\"ParHole3\">%s</div></td>
	<td name=\"ParHole4\" class=\"HoleNumberBox\"><div id=\"ParHole4\">%s</div></td>
	<td name=\"ParHole5\" class=\"HoleNumberBox\"><div id=\"ParHole5\">%s</div></td>
	<td name=\"ParHole6\" class=\"HoleNumberBox\"><div id=\"ParHole6\">%s</div></td>
	<td name=\"ParHole7\" class=\"HoleNumberBox\"><div id=\"ParHole7\">%s</div></td>
	<td name=\"ParHole8\" class=\"HoleNumberBox\"><div id=\"ParHole8\">%s</div></td>
	<td name=\"ParHole9\" class=\"HoleNumberBox\"><div id=\"ParHole9\">%s</div></td>
	<td name=\"Front9Total\" class=\"HoleNumberBox\"><div id=\"Front9Total\">%s</div></td></tr>", $row["par1"], $row["par2"], $row["par3"], $row["par4"], $row["par5"], $row["par6"], $row["par7"], $row["par8"], $row["par9"], calcFrontPar($row));
	?>
	<tr><td><b>Score:</b></td>
	    <td><input class="ScoreInputBox" type="text" name="par1" onKeyUp="HandleKeyUp(this, document.scorecard.par2)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par2" onKeyUp="HandleKeyUp(this, document.scorecard.par3)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par3" onKeyUp="HandleKeyUp(this, document.scorecard.par4)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par4" onKeyUp="HandleKeyUp(this, document.scorecard.par5)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par5" onKeyUp="HandleKeyUp(this, document.scorecard.par6)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par6" onKeyUp="HandleKeyUp(this, document.scorecard.par7)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par7" onKeyUp="HandleKeyUp(this, document.scorecard.par8)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par8" onKeyUp="HandleKeyUp(this, document.scorecard.par9)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="par9" onKeyUp="HandleKeyUp(this, document.scorecard.putt1)" onFocus="HandleFocus(this)"></td>
	    <td><input disabled class="ScoreInputBox" type="text" name="out" value="0"></td>
	</tr>
	
	<tr><td><b>Putts:</b></td>
	    <td><input class="ScoreInputBox" type="text" name="putt1" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt2)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt2" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt3)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt3" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt4)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt4" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt5)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt5" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt6)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt6" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt7)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt7" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt8)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt8" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt9)" onFocus="HandleFocus(this)"></td>
	    <td><input class="ScoreInputBox" type="text" name="putt9" onKeyUp="HandlePuttKeyUp(this, document.scorecard.<?if (!isNineHole($row["TeeID"])) printf("par10"); else printf("greensInReg"); ?>)" onFocus="HandleFocus(this)"></td>
	    <td><input disabled class="ScoreInputBox" type="text" name="puttout" value="0"></td>
	</tr>
	
	<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
	</table>
	<div id=secondnine>
	<table>
	<?
	if ( !isNineHole($courseID) )
	{
		printf("<tr><td><b>Hole:</b></td><td class=\"HoleNumberBox\">10</td><td class=\"HoleNumberBox\">11</td><td class=\"HoleNumberBox\">12</td><td class=\"HoleNumberBox\">13</td><td class=\"HoleNumberBox\">14</td><td class=\"HoleNumberBox\">15</td><td class=\"HoleNumberBox\">16</td><td class=\"HoleNumberBox\">17</td><td class=\"HoleNumberBox\">18</td><td class=\"HoleNumberBox\"><b>In</b></td></tr>");
		printf("<tr><td><b>Par:</b></td>
		<td name=\"ParHole10\" class=\"HoleNumberBox\"><div id=\"ParHole10\">%s</div></td>
		<td name=\"ParHole11\" class=\"HoleNumberBox\"><div id=\"ParHole11\">%s</div></td>
		<td name=\"ParHole12\" class=\"HoleNumberBox\"><div id=\"ParHole12\">%s</div></td>
		<td name=\"ParHole13\" class=\"HoleNumberBox\"><div id=\"ParHole13\">%s</div></td>
		<td name=\"ParHole14\" class=\"HoleNumberBox\"><div id=\"ParHole14\">%s</div></td>
		<td name=\"ParHole15\" class=\"HoleNumberBox\"><div id=\"ParHole15\">%s</div></td>
		<td name=\"ParHole16\" class=\"HoleNumberBox\"><div id=\"ParHole16\">%s</div></td>
		<td name=\"ParHole17\" class=\"HoleNumberBox\"><div id=\"ParHole17\">%s</div></td>
		<td name=\"ParHole18\" class=\"HoleNumberBox\"><div id=\"ParHole18\">%s</div></td>
		<td name=\"Back9Total\" class=\"HoleNumberBox\"><div id=\"Back9Total\">%s</div></td></tr>",$row["par10"], $row["par11"], $row["par12"], $row["par13"], $row["par14"], $row["par15"], $row["par16"], $row["par17"], $row["par18"], calcBackPar($row));
	}
	else
	{
		printf("<tr><td><b>Hole:</b></td><td class=\"HoleNumberBox\">10</td><td class=\"HoleNumberBox\">11</td><td class=\"HoleNumberBox\">12</td><td class=\"HoleNumberBox\">13</td><td class=\"HoleNumberBox\">14</td><td class=\"HoleNumberBox\">15</td><td class=\"HoleNumberBox\">16</td><td class=\"HoleNumberBox\">17</td><td class=\"HoleNumberBox\">18</td><td class=\"HoleNumberBox\"><b>In</b></td></tr>");
		printf("<tr><td><b>Par:</b></td>
		<td name=\"ParHole10\" class=\"HoleNumberBox\"><div id=\"ParHole10\">%s</div></td>
		<td name=\"ParHole11\" class=\"HoleNumberBox\"><div id=\"ParHole11\">%s</div></td>
		<td name=\"ParHole12\" class=\"HoleNumberBox\"><div id=\"ParHole12\">%s</div></td>
		<td name=\"ParHole13\" class=\"HoleNumberBox\"><div id=\"ParHole13\">%s</div></td>
		<td name=\"ParHole14\" class=\"HoleNumberBox\"><div id=\"ParHole14\">%s</div></td>
		<td name=\"ParHole15\" class=\"HoleNumberBox\"><div id=\"ParHole15\">%s</div></td>
		<td name=\"ParHole16\" class=\"HoleNumberBox\"><div id=\"ParHole16\">%s</div></td>
		<td name=\"ParHole17\" class=\"HoleNumberBox\"><div id=\"ParHole17\">%s</div></td>
		<td name=\"ParHole18\" class=\"HoleNumberBox\"><div id=\"ParHole18\">%s</div></td>
		<td name=\"Back9Total\" class=\"HoleNumberBox\"><div id=\"Back9Total\">%s</div></td></tr>",$row["par1"], $row["par2"], $row["par3"], $row["par4"], $row["par5"], $row["par6"], $row["par8"], $row["par8"], $row["par9"], calcFrontPar($row));
	}
	?>
		<tr><td><b>Score:</b></td>
		    <td><input class="ScoreInputBox" type="text" name="par10" onKeyUp="HandleKeyUp(this, document.scorecard.par11)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par11" onKeyUp="HandleKeyUp(this, document.scorecard.par12)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par12" onKeyUp="HandleKeyUp(this, document.scorecard.par13)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par13" onKeyUp="HandleKeyUp(this, document.scorecard.par14)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par14" onKeyUp="HandleKeyUp(this, document.scorecard.par15)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par15" onKeyUp="HandleKeyUp(this, document.scorecard.par16)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par16" onKeyUp="HandleKeyUp(this, document.scorecard.par17)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par17" onKeyUp="HandleKeyUp(this, document.scorecard.par18)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par18" onKeyUp="HandleKeyUp(this, document.scorecard.putt10)" onFocus="HandleFocus(this)"></td>
		    <td><input disabled class="ScoreInputBox" type="text" name="inn" value="0"></td>
		</tr>
		
		<tr><td><b>Putts:</b></td>
		    <td><input class="ScoreInputBox" type="text" name="putt10" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt11)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt11" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt12)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt12" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt13)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt13" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt14)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt14" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt15)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt15" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt16)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt16" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt17)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt17" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt18)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="putt18" onKeyUp="HandlePuttKeyUp(this, document.scorecard.greensInReg)" onFocus="HandleFocus(this)"></td>
		    <td><input disabled class="ScoreInputBox" type="text" name="puttinn" value="0"></td>
		</tr>
	
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<tr>
			<td></td>
		 	<td></td>
		 	<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td COLSPAN=3><b>Total Score:</b></td>
			<td><input disabled class="ScoreInputBox" type="text" name="total" value="0"></td>
		</tr>
	
		<tr>
			<td></td>
		 	<td></td>
		 	<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td COLSPAN=3><b>Total Putts:</b></td>
			<td><input disabled class="ScoreInputBox" type="text" name="putttotal" value="0"></td>
		</tr>
	</table>
	</div>
	<script language="JavaScript">
	<!--
	//GetTees(document.scorecard.Tees.value, PopulateScorecardParValues);
	//-->
	</script>
	<table>
		<?
	
	printf("<tr><td colspan=\"5\"><b>Num of Greens in Reg:</b>  </td><td colspan=\"6\"><input class=\"ScoreInputBox\" type=\"text\" name=\"greensInReg\" ></td></tr>");
	printf("<tr><td colspan=\"5\"><b>Num of Fairways Hit:</b>  </td><td colspan=\"6\"><input class=\"ScoreInputBox\" type=\"text\" name=\"fairwaysHit\" ></td></tr>");
	printf("<tr><td colspan=\"5\"><b>Num of Penalties:</b>  </td><td colspan=\"6\"><input class=\"ScoreInputBox\" type=\"text\" name=\"penalties\" ></td></tr>");
	printf("</table>");
	printf("<table>");
	printf("<tr><td><b>Comments:</b></td></tr>");
	printf("<tr><td><textarea class=\"ScoreCardCommentBox\" type=\"text\" name=\"commentText\"></textarea></td></tr>");
	printf("</table> ");	
	printf("<br>");
	printf("<table>");
	?>
	<?
	printf("</table>");
	printf("<input type=\"hidden\" value=\"%s\" name=\"CourseID\">", $_GET["CourseID"]);
	printf("<input type=\"hidden\" name=\"Back9HTML\">");
    print("<input type=\"checkbox\" name=\"Away\">Away Score<br>");
	print("<input type=\"checkbox\" name=\"Tournament\">Tournament Score<br>");
    extract($_SESSION);
    if (strstr($CallBackPage, "scores"))
        print("<input type=\"checkbox\" name=\"Penalty\">Penalty Score<br><br>");
    else
        print("<input type=\"checkbox\" name=\"Penalty\" disabled>Penalty Score<br><br><br>");
	printf("<input type=\"submit\" value=\"Save Score\" name=\"SubmitScore\">");
	printf("</table>");	
	
	printf("</form>");
	?>
<script language="JavaScript">
<!--
//alert(document.scorecard.par10.value);
document.scorecard.Back9HTML.value = document.getElementById("secondnine").innerHTML;
GetTees(document.scorecard.Tees.value, PopulateScorecardParValues);
//alert(document.scorecard.par10.value);
//-->
</script>
<?
}

function DisScore( $scoreID, $postPage )
{
		$scoreSql = "select * from course_tbl, tee_tbl, score_tbl where score_tbl.id = ";
		$scoreSql .= $scoreID;
		$scoreSql .= " and score_tbl.teeid = tee_tbl.id and tee_tbl.courseid = course_tbl.id";
		//printf("%s", $scoreSql);
		$scoreSqlResult = mysql_query($scoreSql) or die("Could not get detailed history of round: " . mysql_error());
		$scoreSqlRow = mysql_fetch_array($scoreSqlResult);
        extract($scoreSqlRow);

		$coursesSql = "select name, id from course_tbl where userid = ";
		$coursesSql .= $_SESSION['userid'];
		$coursesSqlResult = mysql_query($coursesSql) or die("Could not get a list of courses: " . mysql_error());
?>
		<table class="CourseTable">
		<form action="<?printf("%s", $postPage);?>" name="scorecard" method="POST">
		<td>
		<table class="ScoreCardTable">
		<tr>
		<tr><td><b>Course Name:</b> </td><td><select onChange="GetTeesForCourse(this.value, PopulateEditScorecard)" class="InputBoxWidth" name="courseid">
<?
		while ($coursesRow = mysql_fetch_array($coursesSqlResult))
		{
			if ($scoreSqlRow["courseid"] == $coursesRow["id"])
				printf("<option selected value=\"%s\">%s</option>",$coursesRow["id"],$coursesRow["name"]);
			else
				printf("<option value=\"%s\">%s</option>",$coursesRow["id"],$coursesRow["name"]);
		}
?>
		</select>
		</td></tr> 
<?


	$teeSql = "select * from tee_tbl tt where tt.courseid = ";
	$teeSql .= $scoreSqlRow["courseid"];
	//printf("%s", $teeSql);
	$teeSqlResult = mysql_query($teeSql) or die("Could not get tees: " . mysql_error());
	

	printf("<tr><td><b>Tees:</b> </td><td>");
	printf("<select onchange=\"GetTees(this.value, PopulateScorecardParValues)\" class=\"InputBoxWidth\" name=\"Tees\">");
		while ($teeRow = mysql_fetch_array($teeSqlResult))
		{
			if ($scoreSqlRow["teeid"] == $teeRow["id"])
				printf("<option selected value=\"%s\">%s</option>",$teeRow["id"],$teeRow["name"]);
			else
				printf("<option value=\"%s\">%s</option>",$teeRow["id"],$teeRow["name"]);
		}
	printf("</select>");







		$dbDate = $scoreSqlRow["dateplayed"];
		$formattedDate = substr($dbDate, 5, 2);
		$formattedDate .= "/";
		$formattedDate .= substr($dbDate, 8, 2);
		$formattedDate .= "/";
		$formattedDate .= substr($dbDate, 0, 4);
		printf("<tr><td><b>Date:</b>  </td><td>"); 
		getDateControl(getdate(strtotime($formattedDate)));
		printf("</td></tr>");
?>
		</table> 
		<table class="ScoreCardTable">
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<tr><td><b>Hole:</b></td><td class="HoleNumberBox">1</td><td class="HoleNumberBox">2</td><td class="HoleNumberBox">3</td><td class="HoleNumberBox">4</td><td class="HoleNumberBox">5</td><td class="HoleNumberBox">6</td><td class="HoleNumberBox">7</td><td class="HoleNumberBox">8</td><td class="HoleNumberBox">9</td><td class="HoleNumberBox">Out</td></tr>
<?
		printf("<input value=\"%s\" type=\"hidden\" name=\"id\">", $scoreID);
		//printf("<tr><td><b>Par:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", $scoreSqlRow["par1"], $scoreSqlRow["par2"], $scoreSqlRow["par3"], $scoreSqlRow["par4"], $scoreSqlRow["par5"], $scoreSqlRow["par6"], $scoreSqlRow["par7"], $scoreSqlRow["par8"], $scoreSqlRow["par9"], calcFrontPar($scoreSqlRow));
		printf("<tr><td><b>Par:</b></td>
		<td name=\"ParHole1\" class=\"HoleNumberBox\"><div id=\"ParHole1\">%s</div></td>
		<td name=\"ParHole2\" class=\"HoleNumberBox\"><div id=\"ParHole2\">%s</div></td>
		<td name=\"ParHole3\" class=\"HoleNumberBox\"><div id=\"ParHole3\">%s</div></td>
		<td name=\"ParHole4\" class=\"HoleNumberBox\"><div id=\"ParHole4\">%s</div></td>
		<td name=\"ParHole5\" class=\"HoleNumberBox\"><div id=\"ParHole5\">%s</div></td>
		<td name=\"ParHole6\" class=\"HoleNumberBox\"><div id=\"ParHole6\">%s</div></td>
		<td name=\"ParHole7\" class=\"HoleNumberBox\"><div id=\"ParHole7\">%s</div></td>
		<td name=\"ParHole8\" class=\"HoleNumberBox\"><div id=\"ParHole8\">%s</div></td>
		<td name=\"ParHole9\" class=\"HoleNumberBox\"><div id=\"ParHole9\">%s</div></td>
		<td name=\"Front9Total\" class=\"HoleNumberBox\"><div id=\"Front9Total\">%s</div></td></tr>", $scoreSqlRow["par1"], $scoreSqlRow["par2"], $scoreSqlRow["par3"], $scoreSqlRow["par4"], $scoreSqlRow["par5"], $scoreSqlRow["par6"], $scoreSqlRow["par7"], $scoreSqlRow["par8"], $scoreSqlRow["par9"], calcFrontPar($scoreSqlRow));
		//printf("<tr><td>Score:</td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par1\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par2\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par3\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par4\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par5\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par6\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par7\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par8\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par9\"></td><td><input disabled value=\"%s\" type=\"text\" class=\"ScoreInputBox\"></td></tr>", $scoreSqlRow["hole1"], $scoreSqlRow["hole2"], $scoreSqlRow["hole3"], $scoreSqlRow["hole4"], $scoreSqlRow["hole5"], $scoreSqlRow["hole6"], $scoreSqlRow["hole7"], $scoreSqlRow["hole8"], $scoreSqlRow["hole9"], calcFront($scoreSqlRow));
		?>
		
		<tr><td><b>Score:</b></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole1"]);?>" class="ScoreInputBox" type="text" name="par1" onKeyUp="HandleKeyUp(this, document.scorecard.par2)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole2"]);?>" class="ScoreInputBox" type="text" name="par2" onKeyUp="HandleKeyUp(this, document.scorecard.par3)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole3"]);?>" class="ScoreInputBox" type="text" name="par3" onKeyUp="HandleKeyUp(this, document.scorecard.par4)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole4"]);?>" class="ScoreInputBox" type="text" name="par4" onKeyUp="HandleKeyUp(this, document.scorecard.par5)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole5"]);?>" class="ScoreInputBox" type="text" name="par5" onKeyUp="HandleKeyUp(this, document.scorecard.par6)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole6"]);?>" class="ScoreInputBox" type="text" name="par6" onKeyUp="HandleKeyUp(this, document.scorecard.par7)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole7"]);?>" class="ScoreInputBox" type="text" name="par7" onKeyUp="HandleKeyUp(this, document.scorecard.par8)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole8"]);?>" class="ScoreInputBox" type="text" name="par8" onKeyUp="HandleKeyUp(this, document.scorecard.par9)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["hole9"]);?>" class="ScoreInputBox" type="text" name="par9" onKeyUp="HandleKeyUp(this, document.scorecard.putt1)" onFocus="HandleFocus(this)"></td>
		    <?
			    $total = $scoreSqlRow["hole1"] + $scoreSqlRow["hole2"] + $scoreSqlRow["hole3"] + $scoreSqlRow["hole4"] + $scoreSqlRow["hole5"] + $scoreSqlRow["hole6"] + $scoreSqlRow["hole7"] + $scoreSqlRow["hole8"] + $scoreSqlRow["hole9"];
			    if ( !$total )
			    	$total ="";
		    	    printf("<td><input disabled class=\"ScoreInputBox\" type=\"text\" name=\"out\" value=\"%s\"></td>", $total);
		    ?>
		</tr>
		
		<tr><td><b>Putts:</b></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt1"]);?>" class="ScoreInputBox" type="text" name="putt1" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt2)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt2"]);?>" class="ScoreInputBox" type="text" name="putt2" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt3)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt3"]);?>" class="ScoreInputBox" type="text" name="putt3" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt4)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt4"]);?>" class="ScoreInputBox" type="text" name="putt4" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt5)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt5"]);?>" class="ScoreInputBox" type="text" name="putt5" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt6)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt6"]);?>" class="ScoreInputBox" type="text" name="putt6" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt7)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt7"]);?>" class="ScoreInputBox" type="text" name="putt7" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt8)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt8"]);?>" class="ScoreInputBox" type="text" name="putt8" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt9)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $scoreSqlRow["putt9"]);?>" class="ScoreInputBox" type="text" name="putt9" onKeyUp="HandlePuttKeyUp(this, document.scorecard.<?if (!isNineHole($scoreSqlRow["courseid"])) printf("par10"); else printf("UpdateScore"); ?>)" onFocus="HandleFocus(this)"></td>
		    <?
			    $total = $scoreSqlRow["putt1"] + $scoreSqlRow["putt2"] + $scoreSqlRow["putt3"] + $scoreSqlRow["putt4"] + $scoreSqlRow["putt5"] + $scoreSqlRow["putt6"] + $scoreSqlRow["putt7"] + $scoreSqlRow["putt8"] + $scoreSqlRow["putt9"];
			    if ( !$total )
			    	$total ="";
		    	    printf("<td><input disabled class=\"ScoreInputBox\" type=\"text\" name=\"puttout\" value=\"%s\"></td>", $total);
		    ?>
		</tr>

		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		</table>
		<div id="secondnine">
		<table class="ScoreCardTable">
		<?
		if (!isNineHole($scoreSqlRow["teeid"]))
		{
?>
			<tr><td><b>Hole:</b></td><td class="HoleNumberBox">10</td><td class="HoleNumberBox">11</td><td class="HoleNumberBox">12</td><td class="HoleNumberBox">13</td><td class="HoleNumberBox">14</td><td class="HoleNumberBox">15</td><td class="HoleNumberBox">16</td><td class="HoleNumberBox">17</td><td class="HoleNumberBox">18</td><td class="HoleNumberBox">In</td></tr>
<?
			//printf("<tr><td><b>Par:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", $scoreSqlRow["par10"], $scoreSqlRow["par11"], $scoreSqlRow["par12"], $scoreSqlRow["par13"], $scoreSqlRow["par14"], $scoreSqlRow["par15"], $scoreSqlRow["par16"], $scoreSqlRow["par17"], $scoreSqlRow["par18"], calcBackPar($scoreSqlRow));
			printf("<tr><td><b>Par:</b></td>
			<td name=\"ParHole10\" class=\"HoleNumberBox\"><div id=\"ParHole10\">%s</div></td>
			<td name=\"ParHole11\" class=\"HoleNumberBox\"><div id=\"ParHole11\">%s</div></td>
			<td name=\"ParHole12\" class=\"HoleNumberBox\"><div id=\"ParHole12\">%s</div></td>
			<td name=\"ParHole13\" class=\"HoleNumberBox\"><div id=\"ParHole13\">%s</div></td>
			<td name=\"ParHole14\" class=\"HoleNumberBox\"><div id=\"ParHole14\">%s</div></td>
			<td name=\"ParHole15\" class=\"HoleNumberBox\"><div id=\"ParHole15\">%s</div></td>
			<td name=\"ParHole16\" class=\"HoleNumberBox\"><div id=\"ParHole16\">%s</div></td>
			<td name=\"ParHole17\" class=\"HoleNumberBox\"><div id=\"ParHole17\">%s</div></td>
			<td name=\"ParHole18\" class=\"HoleNumberBox\"><div id=\"ParHole18\">%s</div></td>
			<td name=\"Back9Total\" class=\"HoleNumberBox\"><div id=\"Back9Total\">%s</div></td></tr>",$scoreSqlRow["par10"], $scoreSqlRow["par11"], $scoreSqlRow["par12"], $scoreSqlRow["par13"], $scoreSqlRow["par14"], $scoreSqlRow["par15"], $scoreSqlRow["par16"], $scoreSqlRow["par17"], $scoreSqlRow["par18"], calcBackPar($scoreSqlRow));
			//printf("<tr><td>Score:</td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par10\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par11\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par12\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par13\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par14\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par15\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par16\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par17\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par18\"></td><td><input disabled value=\"%s\" type=\"text\" class=\"ScoreInputBox\"></td></tr>", $scoreSqlRow["hole10"], $scoreSqlRow["hole11"], $scoreSqlRow["hole12"], $scoreSqlRow["hole13"], $scoreSqlRow["hole14"], $scoreSqlRow["hole15"], $scoreSqlRow["hole16"], $scoreSqlRow["hole17"], $scoreSqlRow["hole18"], calcBack($scoreSqlRow));
			?>
			
			<tr><td><b>Score:</b></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole10"]);?>" class="ScoreInputBox" type="text" name="par10" onKeyUp="HandleKeyUp(this, document.scorecard.par11)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole11"]);?>" class="ScoreInputBox" type="text" name="par11" onKeyUp="HandleKeyUp(this, document.scorecard.par12)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole12"]);?>" class="ScoreInputBox" type="text" name="par12" onKeyUp="HandleKeyUp(this, document.scorecard.par13)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole13"]);?>" class="ScoreInputBox" type="text" name="par13" onKeyUp="HandleKeyUp(this, document.scorecard.par14)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole14"]);?>" class="ScoreInputBox" type="text" name="par14" onKeyUp="HandleKeyUp(this, document.scorecard.par15)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole15"]);?>" class="ScoreInputBox" type="text" name="par15" onKeyUp="HandleKeyUp(this, document.scorecard.par16)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole16"]);?>" class="ScoreInputBox" type="text" name="par16" onKeyUp="HandleKeyUp(this, document.scorecard.par17)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole17"]);?>" class="ScoreInputBox" type="text" name="par17" onKeyUp="HandleKeyUp(this, document.scorecard.par18)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["hole18"]);?>" class="ScoreInputBox" type="text" name="par18" onKeyUp="HandleKeyUp(this, document.scorecard.putt10)" onFocus="HandleFocus(this)"></td>
			    <?
				    $total = $scoreSqlRow["hole10"] + $scoreSqlRow["hole11"] + $scoreSqlRow["hole12"] + $scoreSqlRow["hole13"] + $scoreSqlRow["hole14"] + $scoreSqlRow["hole15"] + $scoreSqlRow["hole16"] + $scoreSqlRow["hole17"] + $scoreSqlRow["hole18"];
				    if ( !$total )
				    	$total ="";
			    	    printf("<td><input disabled class=\"ScoreInputBox\" type=\"text\" name=\"inn\" value=\"%s\"></td>", $total);
			    ?>
			</tr>
			
			<tr><td><b>Putts:</b></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt10"]);?>" class="ScoreInputBox" type="text" name="putt10" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt11)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt11"]);?>" class="ScoreInputBox" type="text" name="putt11" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt12)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt12"]);?>" class="ScoreInputBox" type="text" name="putt12" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt13)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt13"]);?>" class="ScoreInputBox" type="text" name="putt13" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt14)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt14"]);?>" class="ScoreInputBox" type="text" name="putt14" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt15)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt15"]);?>" class="ScoreInputBox" type="text" name="putt15" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt16)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt16"]);?>" class="ScoreInputBox" type="text" name="putt16" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt17)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt17"]);?>" class="ScoreInputBox" type="text" name="putt17" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt18)" onFocus="HandleFocus(this)"></td>
			    <td><input value="<?printf("%s", $scoreSqlRow["putt18"]);?>" class="ScoreInputBox" type="text" name="putt18" onKeyUp="HandlePuttKeyUp(this, document.scorecard.UpdateScore)" onFocus="HandleFocus(this)"></td>
			    <?
				    $total = $scoreSqlRow["putt10"] + $scoreSqlRow["putt11"] + $scoreSqlRow["putt12"] + $scoreSqlRow["putt13"] + $scoreSqlRow["putt14"] + $scoreSqlRow["putt15"] + $scoreSqlRow["putt16"] + $scoreSqlRow["putt17"] + $scoreSqlRow["putt18"];
				    if ( !$total )
				    	$total ="";
			    	    printf("<td><input disabled class=\"ScoreInputBox\" type=\"text\" name=\"puttinn\" value=\"%s\"></td>", $total);
			    ?>
			</tr>

			<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
			<tr>
				<td></td>
			 	<td></td>
			 	<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td COLSPAN=3><b>Total Score:</b></td>
				<td><input disabled class="ScoreInputBox" type="text" name="total" value="<?printf("%s", $scoreSqlRow["hole1"] + $scoreSqlRow["hole2"] + $scoreSqlRow["hole3"] + $scoreSqlRow["hole4"] + $scoreSqlRow["hole5"] + $scoreSqlRow["hole6"] + $scoreSqlRow["hole7"] + $scoreSqlRow["hole8"] + $scoreSqlRow["hole9"] + $scoreSqlRow["hole10"] + $scoreSqlRow["hole11"] + $scoreSqlRow["hole12"] + $scoreSqlRow["hole13"] + $scoreSqlRow["hole14"] + $scoreSqlRow["hole15"] + $scoreSqlRow["hole16"] + $scoreSqlRow["hole17"] + $scoreSqlRow["hole18"]);?>"></td>
			</tr>
			<tr>
				<td></td>
			 	<td></td>
			 	<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td COLSPAN=3><b>Total Putts:</b></td>
				<td><input disabled class="ScoreInputBox" type="text" name="putttotal" value="<?printf("%s", $scoreSqlRow["putt1"] + $scoreSqlRow["putt2"] + $scoreSqlRow["putt3"] + $scoreSqlRow["putt4"] + $scoreSqlRow["putt5"] + $scoreSqlRow["putt6"] + $scoreSqlRow["putt7"] + $scoreSqlRow["putt8"] + $scoreSqlRow["putt9"] + $scoreSqlRow["putt10"] + $scoreSqlRow["putt11"] + $scoreSqlRow["putt12"] + $scoreSqlRow["putt13"] + $scoreSqlRow["putt14"] + $scoreSqlRow["putt15"] + $scoreSqlRow["putt16"] + $scoreSqlRow["putt17"] + $scoreSqlRow["putt18"]);?>"></td>
			</tr>
		</table>
		</div>
		<script language="JavaScript">
		<!--
		//GetTees(document.scorecard.Tees.value, PopulateScorecardParValues);
		//-->
		</script>
		<table class="ScoreCardTable">
			<?
		}
		else
		{
		?>
			<tr><td><b>Hole:</b></td><td class="HoleNumberBox">10</td><td class="HoleNumberBox">11</td><td class="HoleNumberBox">12</td><td class="HoleNumberBox">13</td><td class="HoleNumberBox">14</td><td class="HoleNumberBox">15</td><td class="HoleNumberBox">16</td><td class="HoleNumberBox">17</td><td class="HoleNumberBox">18</td><td class="HoleNumberBox">In</td></tr>
<?
			//printf("<tr><td><b>Par:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", $scoreSqlRow["par10"], $scoreSqlRow["par11"], $scoreSqlRow["par12"], $scoreSqlRow["par13"], $scoreSqlRow["par14"], $scoreSqlRow["par15"], $scoreSqlRow["par16"], $scoreSqlRow["par17"], $scoreSqlRow["par18"], calcBackPar($scoreSqlRow));
			printf("<tr><td><b>Par:</b></td>
			<td name=\"ParHole10\" class=\"HoleNumberBox\"><div id=\"ParHole10\">%s</div></td>
			<td name=\"ParHole11\" class=\"HoleNumberBox\"><div id=\"ParHole11\">%s</div></td>
			<td name=\"ParHole12\" class=\"HoleNumberBox\"><div id=\"ParHole12\">%s</div></td>
			<td name=\"ParHole13\" class=\"HoleNumberBox\"><div id=\"ParHole13\">%s</div></td>
			<td name=\"ParHole14\" class=\"HoleNumberBox\"><div id=\"ParHole14\">%s</div></td>
			<td name=\"ParHole15\" class=\"HoleNumberBox\"><div id=\"ParHole15\">%s</div></td>
			<td name=\"ParHole16\" class=\"HoleNumberBox\"><div id=\"ParHole16\">%s</div></td>
			<td name=\"ParHole17\" class=\"HoleNumberBox\"><div id=\"ParHole17\">%s</div></td>
			<td name=\"ParHole18\" class=\"HoleNumberBox\"><div id=\"ParHole18\">%s</div></td>
			<td name=\"Back9Total\" class=\"HoleNumberBox\"><div id=\"Back9Total\">%s</div></td></tr>",$scoreSqlRow["par10"], $scoreSqlRow["par11"], $scoreSqlRow["par12"], $scoreSqlRow["par13"], $scoreSqlRow["par14"], $scoreSqlRow["par15"], $scoreSqlRow["par16"], $scoreSqlRow["par17"], $scoreSqlRow["par18"], calcBackPar($scoreSqlRow));
			//printf("<tr><td>Score:</td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par10\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par11\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par12\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par13\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par14\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par15\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par16\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par17\"></td><td><input value=\"%s\" class=\"ScoreInputBox\" type=\"text\" name=\"par18\"></td><td><input disabled value=\"%s\" type=\"text\" class=\"ScoreInputBox\"></td></tr>", $scoreSqlRow["hole10"], $scoreSqlRow["hole11"], $scoreSqlRow["hole12"], $scoreSqlRow["hole13"], $scoreSqlRow["hole14"], $scoreSqlRow["hole15"], $scoreSqlRow["hole16"], $scoreSqlRow["hole17"], $scoreSqlRow["hole18"], calcBack($scoreSqlRow));
			?>
			
			<tr><td><b>Score:</b></td>
			    <td><input class="ScoreInputBox" type="text" name="par10" onKeyUp="HandleKeyUp(this, document.scorecard.par11)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par11" onKeyUp="HandleKeyUp(this, document.scorecard.par12)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par12" onKeyUp="HandleKeyUp(this, document.scorecard.par13)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par13" onKeyUp="HandleKeyUp(this, document.scorecard.par14)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par14" onKeyUp="HandleKeyUp(this, document.scorecard.par15)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par15" onKeyUp="HandleKeyUp(this, document.scorecard.par16)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par16" onKeyUp="HandleKeyUp(this, document.scorecard.par17)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par17" onKeyUp="HandleKeyUp(this, document.scorecard.par18)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="par18" onKeyUp="HandleKeyUp(this, document.scorecard.putt10)" onFocus="HandleFocus(this)"></td>
			    <td><input disabled class="ScoreInputBox" type="text" name="inn"></td>
			</tr>
			
			<tr><td><b>Putts:</b></td>
			    <td><input class="ScoreInputBox" type="text" name="putt10" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt11)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt11" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt12)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt12" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt13)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt13" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt14)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt14" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt15)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt15" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt16)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt16" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt17)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt17" onKeyUp="HandlePuttKeyUp(this, document.scorecard.putt18)" onFocus="HandleFocus(this)"></td>
			    <td><input class="ScoreInputBox" type="text" name="putt18" onKeyUp="HandlePuttKeyUp(this, document.scorecard.UpdateScore)" onFocus="HandleFocus(this)"></td>
			    <td><input disabled class="ScoreInputBox" type="text" name="puttinn"></td>
			    
			</tr>

			<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
			<tr>
				<td></td>
			 	<td></td>
			 	<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td COLSPAN=3><b>Total Score:</b></td>
				<td><input disabled class="ScoreInputBox" type="text" name="total" value="<?printf("%s", $scoreSqlRow["hole1"] + $scoreSqlRow["hole2"] + $scoreSqlRow["hole3"] + $scoreSqlRow["hole4"] + $scoreSqlRow["hole5"] + $scoreSqlRow["hole6"] + $scoreSqlRow["hole7"] + $scoreSqlRow["hole8"] + $scoreSqlRow["hole9"]);?>"></td>
			</tr>
			<tr>
				<td></td>
			 	<td></td>
			 	<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td COLSPAN=3><b>Total Putts:</b></td>
				<td><input disabled class="ScoreInputBox" type="text" name="putttotal" value="<?printf("%s", $scoreSqlRow["putt1"] + $scoreSqlRow["putt2"] + $scoreSqlRow["putt3"] + $scoreSqlRow["putt4"] + $scoreSqlRow["putt5"] + $scoreSqlRow["putt6"] + $scoreSqlRow["putt7"] + $scoreSqlRow["putt8"] + $scoreSqlRow["putt9"]);?>"></td>
			</tr>
		</table>
		</div>
		<script language="JavaScript">
		<!--
		//GetTees(document.scorecard.Tees.value, PopulateScorecardParValues);
		//-->
		</script>
		<table class="ScoreCardTable">
			<?
		}
		
		
		
		
		
		
		
		
		
		
		
		printf("<tr><td colspan=\"5\"><b>Num of Greens in Reg:</b>  </td><td colspan=\"6\"><input class=\"ScoreInputBox\" type=\"text\" name=\"greensInReg\" value=\"%s\"></td></tr>", $scoreSqlRow["greens"]);
		printf("<tr><td colspan=\"5\"><b>Num of Fairways Hit:</b>  </td><td colspan=\"6\"><input class=\"ScoreInputBox\" type=\"text\" name=\"fairwaysHit\" value=\"%s\"></td></tr>", $scoreSqlRow["fairways"]);
		printf("<tr><td colspan=\"5\"><b>Num of Penalties:</b>  </td><td colspan=\"6\"><input class=\"ScoreInputBox\" type=\"text\" name=\"penalties\" value=\"%s\"></td></tr>", $scoreSqlRow["penalties"]);
?>
		</table>
<?
		printf("<table class=\"ScoreCardTable\">");
		printf("<tr><td><b>Comments:</b></td></tr>");
		printf("<tr><td><textarea class=\"ScoreCardCommentBox\" type=\"text\" name=\"commentText\">%s</textarea></td></tr>", $scoreSqlRow["comment"]);
		printf("<input type=\"hidden\" name=\"Back9HTML\">");
		printf("</table> ");	
?>

<script language="JavaScript">
<!--
document.scorecard.Back9HTML.value = document.getElementById("secondnine").innerHTML;
GetTees(document.scorecard.Tees.value, PopulateScorecardParValues);
//-->
</script>


		<br>
		<table class="ScoreCardTable">
        <?
            $checked = null;
            if (strpbrk($type, "A"))
                $checked = "checked"
        ?>
        <input type="checkbox" name="Away" <?print("$checked");?>>Away Score<br>
        <?
            $checked = null;
            if (strpbrk($type, "T"))
                $checked = "checked"
        ?>
        <input type="checkbox" name="Tournament" <?print("$checked");?>>Tournament Score<br>
        <?
            $checked = null;
            if (strpbrk($type, "P"))
            {
                $checked = "checked";
                print("<input type=\"checkbox\" $checked disabled>Penalty Score<input type=\"hidden\" name=\"Penalty\" value=\"1\"><br><br>");
            }
            else
                print("<input type=\"checkbox\" disabled>Penalty Score<br><br>");
        ?>
        
		<input type="submit" value="Update Score" name="UpdateScore">
		<input type="submit" value="Delete Score" name="DeleteScore" onClick="javascript:return confirm('Are you sure you want to delete?')">
		<br><br>Holes 1-9 or 10-18 required.
		</table>
		<td>
		</form>
		</table>
<?	
}

function DisCourseEntryForm($postPage)
{
?>
		<table class="CourseTable">
		<form action="<?printf("%s", $postPage);?>" method="POST" name="scorecard">
		<td>
		<table>
		<tr>
		<tr><td><b>Course Name:</b> </td><td><input class="InputBoxWidth" type="text" name="CourseName"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>City:</b>  </td><td><input class="InputBoxWidth" type="text" name="City"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>State/Province:</b>  </td><td>
                  <?  
            		$sql = "select * from state_tbl order by name asc;";
            		$result = mysql_query($sql) or die("Could not get list of states course: " . mysql_error());
            		printf("<select class=\"InputBoxWidth\" name=\"State\">");
                        print("<option selected value=\"0\">Choose One...</option>");
            			while ($staterow = mysql_fetch_array($result))
            			{
                            extract($staterow);
                            printf("<option value=\"%s\">%s</option>",$id,$name);
            			}
            		printf("</select>");
        ?>
        
        <span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td><b>Tee Name:</b>  </td><td><input class="InputBoxWidth" type="text" name="Tees"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>Course Rating:</b>  </td><td><input class="InputBoxWidth" type="text" name="CourseRating"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>Slope Rating:</b>  </td><td><input class="InputBoxWidth" type="text" name="SlopeRating"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		</table> 
        
		<table class="ScoreCardTable">
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<tr><td><b>Hole:</b></td><td class="HoleNumberBox">1</td><td class="HoleNumberBox">2</td><td class="HoleNumberBox">3</td><td class="HoleNumberBox">4</td><td class="HoleNumberBox">5</td><td class="HoleNumberBox">6</td><td class="HoleNumberBox">7</td><td class="HoleNumberBox">8</td><td class="HoleNumberBox">9</td></tr>
		
		<tr><td><b>Par:</b></td>
		    <td><input class="ScoreInputBox" type="text" name="par1" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par2" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par3" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par4" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par5" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par6" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par7" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par8" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par9" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewCourse)" onFocus="HandleFocus(this)"></td>
		    		    <td><input disabled class="ScoreInputBox" type="text" name="out" value="0"></td>
		</tr>
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		

		</table>
		<br>
		<table>
		<input type="submit" value="Save Course" name="StoreNewCourse">
		<br><br><span class="RequiredFieldIndicator">&nbsp*</span> Indicates a required field. Holes 1-9 are required.
		<br>
		</table>
		<td>
		</form>
		</table>
<?
}



function DisTeeEntryForm($courseid, $postPage)
{
		$sql = "select name, city, state from course_tbl where id = $courseid";
		$result = mysql_query($sql) or die("Could not get course details: " . mysql_error());
		$row = mysql_fetch_array($result);
?>
		<table class="CourseTable">
		<form action="<?printf("%s", $postPage);?>" method="POST" name="scorecard">
		<td>
		<table>
		<tr>
		<tr><td><b>Name:</b> </td><td><input value=<?printf("\"%s\"",$row["name"]);?> disabled="true" class="InputBoxWidth" type="text" name="CourseName"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>City:</b>  </td><td><input value=<?printf("\"%s\"",$row["city"]);?> disabled="true" class="InputBoxWidth" type="text" name="City"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>State/Province:</b>  </td><td><input value=<?printf("\"%s\"",$row["state"]);?> disabled="true" class="InputBoxWidth" type="text" name="State"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td><b>Tee Name:</b>  </td><td><input class="InputBoxWidth" type="text" name="Tees"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>Course Rating:</b>  </td><td><input class="InputBoxWidth" type="text" name="CourseRating"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>Slope Rating:</b>  </td><td><input class="InputBoxWidth" type="text" name="SlopeRating"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
        </table> 
		<table class="ScoreCardTable">
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<tr><td><b>Hole:</b></td><td class="HoleNumberBox">1</td><td class="HoleNumberBox">2</td><td class="HoleNumberBox">3</td><td class="HoleNumberBox">4</td><td class="HoleNumberBox">5</td><td class="HoleNumberBox">6</td><td class="HoleNumberBox">7</td><td class="HoleNumberBox">8</td><td class="HoleNumberBox">9</td></tr>
		
		<tr><td><b>Par:</b></td>
		    <td><input class="ScoreInputBox" type="text" name="par1" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par2" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par3" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par4" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par5" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par6" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par7" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par8" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    <td><input class="ScoreInputBox" type="text" name="par9" onKeyUp="HandleCourseKeyUp(this, document.scorecard.StoreNewTees)" onFocus="HandleFocus(this)"></td>
		    		    <td><input disabled class="ScoreInputBox" type="text" name="out" value="0"></td>
		</tr>
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>

		</table>
		<br>
		<table>
		<input type="submit" value="Save" name="StoreNewTees">
		<br><br><span class="RequiredFieldIndicator">&nbsp*</span> Indicates a required field. Holes 1-9 are required.
		</table>
		<td>
		<?printf("<input type=\"hidden\" value=\"%s\" name=\"courseid\">", $courseid);?>
		</form>
		</table>
<?
}





















function DisCourse( $courseID, $postPage )
{
		$sql = "select ct.*, ct.name as coursename from course_tbl ct where ct.id = ";
		$sql .= $courseID;
		//printf("%s",$sql);
		$result = mysql_query($sql) or die("Could not get selected course: " . mysql_error());
		$row = mysql_fetch_array($result);
		
		$sql = "select * from tee_tbl where courseid = ";
		$sql .= $courseID;
		$teeresult = mysql_query($sql) or die("Could not get tees for selected course: " . mysql_error());
		
		printf("<table class=\"CourseTable\">");
		printf("<form action=\"%s\" method=\"POST\" name=\"scorecard\">", $postPage);
		printf("<td>");
		printf("<table class=\"ScoreCardTable\">");
		printf("<tr>");
		/*
		printf("<tr><td>Course Name: </td><td><input class=\"InputBoxWidth\" type=\"text\" name=\"CourseName\" value=\"%s\"></td></tr>", $row["course_tbl.name"]);
		printf("<tr><td>Slope Rating:  </td><td><input class=\"InputBoxWidth\" type=\"text\" name=\"SlopeRating\" value=\"%s\"></td></tr>", $row["slope"]);
		printf("<tr><td>Course Rating:  </td><td><input class=\"InputBoxWidth\" type=\"text\" name=\"CourseRating\" value=\"%s\"></td></tr> ", $row["rating"]);
		printf("<tr><td>City:  </td><td><input class=\"InputBoxWidth\" type=\"text\" name=\"City\" value=\"%s\"></td></tr> ", $row["city"]);
		printf("<tr><td>State/Province:  </td><td><input class=\"InputBoxWidth\" type=\"text\" name=\"State\" value=\"%s\"></td></tr>", $row["state"]);
		printf("</table> ");
		printf("<table class=\"ScoreCardTable\">");
		printf("<tr><td>Hole:</td><td class=\"HoleNumberBox\">1</td><td class=\"HoleNumberBox\">2</td><td class=\"HoleNumberBox\">3</td><td class=\"HoleNumberBox\">4</td><td class=\"HoleNumberBox\">5</td><td class=\"HoleNumberBox\">6</td><td class=\"HoleNumberBox\">7</td><td class=\"HoleNumberBox\">8</td><td class=\"HoleNumberBox\">9</td></tr>");
		printf("<tr><td>Par:</td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par1\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par2\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par3\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par4\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par5\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par6\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par7\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par8\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par9\" value=\"%s\"></td></tr>", $row["par1"], $row["par2"], $row["par3"], $row["par4"], $row["par5"], $row["par6"], $row["par7"], $row["par8"], $row["par9"]);
		printf("<tr><td>Hole:</td><td class=\"HoleNumberBox\">10</td><td class=\"HoleNumberBox\">11</td><td class=\"HoleNumberBox\">12</td><td class=\"HoleNumberBox\">13</td><td class=\"HoleNumberBox\">14</td><td class=\"HoleNumberBox\">15</td><td class=\"HoleNumberBox\">16</td><td class=\"HoleNumberBox\">17</td><td class=\"HoleNumberBox\">18</td></tr>");
		printf("<tr><td>Par:</td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par10\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par11\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par12\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par13\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par14\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par15\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par16\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par17\" value=\"%s\"></td><td><input class=\"ScoreInputBox\" type=\"text\" name=\"par18\" value=\"%s\"></td></tr>", $row["par10"], $row["par11"], $row["par12"], $row["par13"], $row["par14"], $row["par15"], $row["par16"], $row["par17"], $row["par18"]);
		*/
				?>
		<tr><td><b>Course Name:</b> </td><td><input class="InputBoxWidth" type="text" name="CourseName" value="<?printf("%s", $row["coursename"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>City:</b>  </td><td><input class="InputBoxWidth" type="text" name="City" value="<?printf("%s", $row["city"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>State/Province:</b>  </td><td>
          <?  
            		$sql = "select * from state_tbl order by name asc;";
            		$result = mysql_query($sql) or die("Could not get list of states course: " . mysql_error());
            		printf("<select class=\"InputBoxWidth\" name=\"State\">");
                        print("<option selected value=\"0\">Choose One...</option>");
            			while ($staterow = mysql_fetch_array($result))
            			{
                            extract($staterow);
                            if ( $id == $row["state"])
                                printf("<option value=\"%s\" selected>%s</option>",$id,$name);
                            else
                                printf("<option value=\"%s\">%s</option>",$id,$name);
            			}
            		printf("</select>");
        ?>
            
            
            <span class="RequiredFieldIndicator">&nbsp*</span>
            </td></tr>
		<tr><td valign="top"><b>Tees:</b></td>
		<td>
			<table border="0" cellspacing="0" cellpadding="5">
			<?
				$rowCnt = 0;
				while ($CourseRows = mysql_fetch_array($teeresult))
				{
					$classname = ($rowCnt % 2) ? 'CourseList2' : 'CourseList1';
					printf("<tr class=\"$classname\"><td>%s</td><td>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<A HREF=\"courseadmin.php?EditTee=%s&courseid=%s\">Edit</A>&nbsp</td><td>&nbsp<A HREF=\"courseadmin.php?DeleteTee=%s&ShowDetails=1&CourseID=%s\" onclick=\"javascript:return confirm('Are you sure you want to delete?')\">Delete</A>&nbsp</td></tr>",$CourseRows["name"], $CourseRows["id"], $courseID, $CourseRows["id"], $courseID);
					$rowCnt++;
				}				
				printf("<tr><td colspan=\"3\"><A HREF=\"addtee.php?courseid=%s\">Add New Tee</A></td></tr>",$courseID);
			?>
			</table>
		</td></tr> 
		</table> 
		<?
		printf("<br>");
		printf("<table>");
		printf("<input type=\"submit\" value=\"Save\" name=\"SaveChangedCourse\">");
		?><br><br><span class="RequiredFieldIndicator">&nbsp*</span> indicates a required field.<?
		printf("</table>");
		printf("<td>");
		printf("<input type=\"hidden\" value=\"%s\" name=\"courseid\">", $courseID);
		printf("</form>");
		printf("</table>");
}

function DisTees( $teeid, $postPage )
{
		$sql = "select ct.*, tt.*, ct.name as coursename, tt.name as teename from tee_tbl tt, course_tbl ct where tt.id = ";
		$sql .= $teeid;
		$sql .= " and tt.courseid = ct.id;";
		$teeresult = mysql_query($sql) or die("Could not get tees:  " . mysql_error());
		$row = mysql_fetch_array($teeresult);
		
		printf("<table class=\"CourseTable\">");
		printf("<form action=\"%s\" method=\"POST\" name=\"scorecard\">", $postPage);
		printf("<td>");
		printf("<table>");
		printf("<tr>");
		?>
		<tr><td><b>Course Name:</b> </td><td><input disabled="true" class="InputBoxWidth" type="text" name="CourseName" value="<?printf("%s", $row["coursename"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>City:</b>  </td><td><input disabled="true" class="InputBoxWidth" type="text" name="City" value="<?printf("%s", $row["city"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>State/Province:</b>  </td><td><input disabled="true" class="InputBoxWidth" type="text" name="State" value="<?printf("%s", $row["state"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td><b>Tee Name:</b>  </td><td><input class="InputBoxWidth" type="text" name="Tees" value="<?printf("%s", $row["teename"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr>
		<tr><td><b>Course Rating:</b>  </td><td><input class="InputBoxWidth" type="text" name="CourseRating" value="<?printf("%s", $row["rating"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		<tr><td><b>Slope Rating:</b>  </td><td><input class="InputBoxWidth" type="text" name="SlopeRating" value="<?printf("%s", $row["slope"]);?>"><span class="RequiredFieldIndicator">&nbsp*</span></td></tr> 
		</table> 
		<table class="ScoreCardTable">
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<tr><td><b>Hole:</b></td><td class="HoleNumberBox">1</td><td class="HoleNumberBox">2</td><td class="HoleNumberBox">3</td><td class="HoleNumberBox">4</td><td class="HoleNumberBox">5</td><td class="HoleNumberBox">6</td><td class="HoleNumberBox">7</td><td class="HoleNumberBox">8</td><td class="HoleNumberBox">9</td><td class="HoleNumberBox">Out</td></tr>
		
		<tr><td><b>Par:</b></td>
		    <td><input value="<?printf("%s", $row["par1"]);?>" class="ScoreInputBox" type="text" name="par1" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par2"]);?>" class="ScoreInputBox" type="text" name="par2" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par3"]);?>" class="ScoreInputBox" type="text" name="par3" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par4"]);?>" class="ScoreInputBox" type="text" name="par4" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par5"]);?>" class="ScoreInputBox" type="text" name="par5" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par6"]);?>" class="ScoreInputBox" type="text" name="par6" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par7"]);?>" class="ScoreInputBox" type="text" name="par7" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par8"]);?>" class="ScoreInputBox" type="text" name="par8" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    <td><input value="<?printf("%s", $row["par9"]);?>" class="ScoreInputBox" type="text" name="par9" onKeyUp="HandleCourseKeyUp(this, document.scorecard.SaveChangedTees)" onFocus="HandleFocus(this)"></td>
		    		    <td><input disabled class="ScoreInputBox" type="text" name="out" value="<?printf("%s", calcFrontPar($row));?>"></td>
		</tr>
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<?
		printf("</table>");
		printf("<br>");
		printf("<table>");
		printf("<input type=\"submit\" value=\"Save\" name=\"SaveChangedTees\">");
		?><br><br><span class="RequiredFieldIndicator">&nbsp*</span> Indicates a required field. Holes 1-9 are required.<?
		printf("</table>");
		printf("<td>");
		printf("<input type=\"hidden\" value=\"%s\" name=\"teeid\">", $teeid);
		printf("<input type=\"hidden\" value=\"%s\" name=\"courseid\">", $_GET["courseid"]);
		printf("</form>");
		printf("</table>");
}

function getParForHole( $teeID, $holeNum )
{
	$parNum = "par$holeNum"; 
	$sql = "select $parNum from tee_tbl where id = $teeID and $parNum is not null";
	$result = mysql_query($sql) or die("Could not get par for the hole: " . mysql_error());
	$row = mysql_fetch_array($result);
	if ( mysql_num_rows($result) == 0 )
		die("Could not get par for the hole");
	else
		return $row["$parNum"];
}

function getNumOf( $scoreType, $teeID, $holeNum, $fromdate, $todate )
{
	$par = getParForHole( $teeID, $holeNum );
	
	$holeInOne = 1; 
	$doubleEagle = 0; 
	$eagle = 0; 
	$birdie = 0; 
	$bogie = 0;
	$double = 0;
	
	if ( $par == 3 )
	{
		$birdie = 2;
		$bogie = 4;
		$double = 5;
		
		// Double eagle doesn't exist for a par 3 and we would
		// catch an eagle when we report the hole in ones.
		if ( $scoreType == "DOUBLE_EAGLE" || $scoreType == "EAGLE" )
			return 0;
	}
	else if ( $par == 4 )
	{
		$eagle = 2;
		$birdie = 3;
		$bogie = 5;
		$double = 6;
		
		// Double eagle would be reported as hole in one.
		if ( $scoreType == "DOUBLE_EAGLE" )
			return 0;
	}
	else if ( $par == 5 )
	{
		$doubleEagle = 2;
		$eagle = 3;
		$birdie = 4;
		$bogie = 6;
		$double = 7;
	}
	else
	{
		die("Invalid par number for hole");
	}
	
	$holeName = "hole$holeNum"; 
	$sql = "select count(*) as cnt from score_tbl where teeid = $teeID and $holeName";
	if ( $scoreType == "HOLE_IN_ONE" )
		$sql .= " = $holeInOne";
	else if ( $scoreType == "DOUBLE_EAGLE" )
		$sql .= " = $doubleEagle";
	else if ( $scoreType == "EAGLE" )
		$sql .= " = $eagle";
	else if ( $scoreType == "BIRDIE" )
		$sql .= " = $birdie";
	else if ( $scoreType == "PAR" )
		$sql .= " = $par";
	else if ( $scoreType == "BOGIE" )
		$sql .= " = $bogie";
	else if ( $scoreType == "DOUBLE" )
		$sql .= " = $double";
	else
		$sql .= " > $double";
		
		
	if ( $fromdate != "" && $todate != "" )
		$sql .= " and dateplayed >= '$fromdate' and dateplayed <= '$todate'";
	else if ( $fromdate != "" )
		$sql .= " and dateplayed >= '$fromdate'";
	else if ( $todate != "" )
		$sql .= " and dateplayed <= '$todate'";
		
		
	$result = mysql_query($sql) or die("Could not get number of certain scores for the hole: " . mysql_error());
	$row = mysql_fetch_array($result);
	$cnt = $row["cnt"];
	if ( $cnt == 0 )
		return "0";
	else
		return $cnt;
}

function DisCourseStats( $teeid )
{
	
		// Find out if we should filter stats by a date range.
		$fromdate = "";
		$todate = "";
		if ( $_POST["dostatfilter"] )
		{
			

			//
			// FROM DATE
			//
			$fromdate .= sprintf("%u",$_POST["TheYearfrom"]); // YEAR
			if ($_POST["TheMonthfrom"] < 10)
				$fromdate .= sprintf("0%u",$_POST["TheMonthfrom"]); // MONTH
			else
				$fromdate .= sprintf("%u",$_POST["TheMonthfrom"]); // MONTH
			if ($_POST["TheDayfrom"] < 10)
				$fromdate .= sprintf("0%u",$_POST["TheDayfrom"]); // DAY
			else
				$fromdate .= sprintf("%u",$_POST["TheDayfrom"]); // DAY
			
	
	
			//
			// TO DATE
			//
			$todate .= sprintf("%u",$_POST["TheYearto"]); // YEAR
			if ($_POST["TheMonthto"] < 10)
				$todate .= sprintf("0%u",$_POST["TheMonthto"]); // MONTH
			else
				$todate .= sprintf("%u",$_POST["TheMonthto"]); // MONTH
			if ($_POST["TheDayto"] < 10)
				$todate .= sprintf("0%u",$_POST["TheDayto"]); // DAY
			else
				$todate .= sprintf("%u",$_POST["TheDayto"]); // DAY
			
			
		}

		// should contain [greens hit] [possible num of greens]
		
		$greensArray = array();
		$greensArray = getGreensArray($teeid, $fromdate, $todate);
		
		// should contain [fairways hit] [possible num of fairways]
		$fairwaysArray = array();
		$fairwaysArray = getfairwaysArray($teeid, $fromdate, $todate);
		
		// should contain [number of penalties] [holes played]
		$penaltiesArray = array();
		$penaltiesArray = getpenaltiesArray($teeid, $fromdate, $todate);
	
		$courseAverageArray = array();
		$courseAverageArray = getCourseAverageArray($teeid, $fromdate, $todate);
		
		$coursePuttAverageArray = array();
		$coursePuttAverageArray = getCoursePuttAverageArray($teeid, $fromdate, $todate);
		
		
		
		$worstHoleArray = array();
		$bestHoleArray = array();
		$worstHoleArray = getWorstAverageHoleArray($courseAverageArray, $teeid, $fromdate, $todate);
		$bestHoleArray = getBestAverageHoleArray($courseAverageArray, $teeid, $fromdate, $todate);
		$BestHoleString;
		if ( count($bestHoleArray) > 1 )
		{
			for ($i = 0; $i < count($bestHoleArray); $i++)
			{
				if ( ($i + 1) != count($bestHoleArray) )
				{
					$BestHoleString .= $bestHoleArray[$i];
					$BestHoleString .= ", ";
				}
				else
					$BestHoleString .= $bestHoleArray[$i];
			}
			printf("<br>");
		}
		else
			$BestHoleString = $bestHoleArray[0];
            

		$WorstHoleString;
		if ( count($worstHoleArray) > 1 )
		{
			for ($i = 0; $i < count($worstHoleArray); $i++)
			{
				if ( ($i + 1) != count($worstHoleArray) )
				{
					$WorstHoleString .= $worstHoleArray[$i];
					$WorstHoleString .= ", ";
				}
				else
					$WorstHoleString .= $worstHoleArray[$i];
			}
		}
		else
			$WorstHoleString = $worstHoleArray[0];
		
        if ($fromdate && $todate)
            $sql = "select max(os.total) as MaxScore from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid and os.dateplayed >= $fromdate and os.dateplayed <= $todate";
        else
            $sql = "select max(os.total) as MaxScore from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid";
       $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0)
            extract(mysql_fetch_array($result));
        
        
        if ($fromdate && $todate)
            $sql = "select min(os.total) as MinScore from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid and os.dateplayed >= $fromdate and os.dateplayed <= $todate";
        else
            $sql = "select min(os.total) as MinScore from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid";
       $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0)
            extract(mysql_fetch_array($result));
        
        if ($fromdate && $todate)
            $sql = "select avg(os.total) as ScoreAvg from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid and os.dateplayed >= $fromdate and os.dateplayed <= $todate";
        else
            $sql = "select avg(os.total) as ScoreAvg from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0)
            extract(mysql_fetch_array($result));
        $ScoreAvg = round($ScoreAvg);
        
        if ($fromdate && $todate)
            $sql = "select count(*) as NumFrontNines from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid and hole1 is not null and os.dateplayed >= $fromdate and os.dateplayed <= $todate";
        else
            $sql = "select count(*) as NumFrontNines from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid and hole1 is not null";

        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0)
            extract(mysql_fetch_array($result));

            
         
            
            
        if ($fromdate && $todate)
            $sql = "select sum(st.penalties) as NumPenalties from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid and os.dateplayed >= $fromdate and os.dateplayed <= $todate";
        else
            $sql = "select sum(st.penalties) as NumPenalties from tools_officialscore os, score_tbl st where st.officialscoreid = os.id and st.teeid = $teeid";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0)
            extract(mysql_fetch_array($result));
            
        if ($NumPenalties == 0)
            $PenaltiesPerNine = 0;
        if ($NumPenalties && $NumFrontNines)
            $PenaltiesPerNine = $NumPenalties / $NumFrontNines;
            
        
		printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\"><tr class=\"ScoreHistoryTDHeader\"><td colspan=\"2\"><b>SUMMARY</b></td></tr>");
        
        if (strlen($MaxScore) > 0)
            print("<TR class=\"CourseList2\"><TD>Best Score</td><td>$MinScore</TD></TR>");
        else
            print("<TR class=\"CourseList2\"><TD>Best Score</td><td><span class=\"NineHoleScoreFont\">No Data</span></TD></TR>");


        if (strlen($MinScore) > 0)
            print("<TR class=\"CourseList1\"><TD>Worst Score</td><td>$MaxScore</TD></TR>");
        else
            print("<TR class=\"CourseList1\"><TD>Worst Score</td><td><span class=\"NineHoleScoreFont\">No Data</span></TD></TR>");
            
        
        if (strlen($ScoreAvg) > 0)
            print("<TR class=\"CourseList2\"><TD>Average Score</td><td>$ScoreAvg</TD></TR>");
        else
            print("<TR class=\"CourseList2\"><TD>Average Score</td><td><span class=\"NineHoleScoreFont\">No Data</span></TD></TR>");
        
        
        $PuttAvg = round(getAvgPuttsPerGreenByCourse($_SESSION['userid'], $teeid, $fromdate, $todate), 1);
        if ($PuttAvg == -1)
            print("<TR class=\"CourseList1\"><TD>Avg Putts Per Green</td><td><span class=\"NineHoleScoreFont\">No Data</span></TD></TR>");
        else
            print("<TR class=\"CourseList1\"><TD>Avg Putts Per Green</td><td>$PuttAvg</TD></TR>");
            
        if (strlen($BestHoleString) > 0)    
            printf("<TR class=\"CourseList2\"><TD>Best Hole(s)</td><td>%s</TD></TR>", $BestHoleString);
        else
            print("<TR class=\"CourseList2\"><TD>Best Hole(s)</td><td><span class=\"NineHoleScoreFont\">No Data</span></TD></TR>");
            
            
        if (strlen($WorstHoleString) > 0)    
            printf("<TR class=\"CourseList1\"><TD>Worst Hole(s)</td><td>%s</TD></TR>", $WorstHoleString);
        else
            print("<TR class=\"CourseList1\"><TD>Worst Hole(s)</td><td><span class=\"NineHoleScoreFont\">No Data</span></TD></TR>");
        
        
		if ( $greensArray[1] == 0 )
			printf("<tr class=\"CourseList2\"><td>Greens In Regulation</td><td><span class=\"NineHoleScoreFont\">No Data</span></td></tr>");
		else
			printf("<tr class=\"CourseList2\"><td>Greens In Regulation</td><td>%s%%</td></tr>", round(($greensArray[0]/$greensArray[1]) * 100) );
			
		if ( $fairwaysArray[1] == 0 )
			printf("<tr class=\"CourseList1\"><td>Fairways Hit</td><td><span class=\"NineHoleScoreFont\">No Data</span></td></tr>");
		else
			printf("<tr class=\"CourseList1\"><td>Fairways Hit</td><td>%s%%</td></tr>", round(($fairwaysArray[0]/$fairwaysArray[1]) * 100) );
			
		if ( isset($PenaltiesPerNine) )
            printf("<tr class=\"CourseList2\"><td>Penalties Per Nine</td><td>%s per 9</td></tr>", round($PenaltiesPerNine, 1));
		else
            printf("<tr class=\"CourseList2\"><td>Penalties Per Nine</td><td><span class=\"NineHoleScoreFont\">No Data</span></td></tr>");
			
		print("</table><br>");
		
		
		$sql = "select concat(ct.name, '  (', tt.name, ')') as coursename, tt.*, ct.* from course_tbl ct, tee_tbl tt where tt.courseid = ct.id and tt.id = ";
		$sql .= $teeid;
		$result = mysql_query($sql) or die("Could not get stats: " . mysql_error());
		$row = mysql_fetch_array($result);
		printf("<table class=\"CourseTable\">");
		printf("<td>");
		printf("<table class=\"ScoreCardTable\">");
		printf("<tr class=\"ScoreHistoryTDHeader\"><td><b>Hole:</b></td><td class=\"HoleNumberBox\">1</td><td class=\"HoleNumberBox\">2</td><td class=\"HoleNumberBox\">3</td><td class=\"HoleNumberBox\">4</td><td class=\"HoleNumberBox\">5</td><td class=\"HoleNumberBox\">6</td><td class=\"HoleNumberBox\">7</td><td class=\"HoleNumberBox\">8</td><td class=\"HoleNumberBox\">9</td><td class=\"HoleNumberBoxTotal\"><b>Out</b></td></tr>");
		printf("<tr class=\"ScoreHistoryTDHeader\"><td><b>Par:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", $row["par1"], $row["par2"], $row["par3"], $row["par4"], $row["par5"], $row["par6"], $row["par7"], $row["par8"], $row["par9"], $row["par1"] +  $row["par2"] +  $row["par3"] +  $row["par4"] +  $row["par5"] +  $row["par6"] +  $row["par7"] +  $row["par8"] +  $row["par9"]);
		printf("<tr class=\"CourseList2\"><td><b>Avg:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", formatHandi($courseAverageArray[0]), formatHandi($courseAverageArray[1]), formatHandi($courseAverageArray[2]), formatHandi($courseAverageArray[3]), formatHandi($courseAverageArray[4]), formatHandi($courseAverageArray[5]), formatHandi($courseAverageArray[6]), formatHandi($courseAverageArray[7]), formatHandi($courseAverageArray[8]), formatHandi($courseAverageArray[18]));
		printf("<tr class=\"CourseList1\"><td><b>Putt Avg:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", formatPuttHandi($coursePuttAverageArray[0]), formatPuttHandi($coursePuttAverageArray[1]), formatPuttHandi($coursePuttAverageArray[2]), formatPuttHandi($coursePuttAverageArray[3]), formatPuttHandi($coursePuttAverageArray[4]), formatPuttHandi($coursePuttAverageArray[5]), formatPuttHandi($coursePuttAverageArray[6]), formatPuttHandi($coursePuttAverageArray[7]), formatPuttHandi($coursePuttAverageArray[8]), formatPuttHandi($coursePuttAverageArray[18]));
		printf("<tr class=\"CourseList2\"><td><b>Hole In Ones:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("HOLE_IN_ONE", $teeid, 1, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 2, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 3, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 4, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 5, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 6, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 7, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 8, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 9, $fromdate, $todate));
		printf("<tr class=\"CourseList1\"><td><b>Double Eagles:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("DOUBLE_EAGLE", $teeid, 1, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 2, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 3, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 4, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 5, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 6, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 7, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 8, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 9, $fromdate, $todate));
		printf("<tr class=\"CourseList2\"><td><b>Eagles:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("EAGLE", $teeid, 1, $fromdate, $todate), getNumOf("EAGLE", $teeid, 2, $fromdate, $todate), getNumOf("EAGLE", $teeid, 3, $fromdate, $todate), getNumOf("EAGLE", $teeid, 4, $fromdate, $todate), getNumOf("EAGLE", $teeid, 5, $fromdate, $todate), getNumOf("EAGLE", $teeid, 6, $fromdate, $todate), getNumOf("EAGLE", $teeid, 7, $fromdate, $todate), getNumOf("EAGLE", $teeid, 8, $fromdate, $todate), getNumOf("EAGLE", $teeid, 9, $fromdate, $todate));
		printf("<tr class=\"CourseList1\"><td><b>Birdies:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("BIRDIE", $teeid, 1, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 2, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 3, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 4, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 5, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 6, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 7, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 8, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 9, $fromdate, $todate));
		printf("<tr class=\"CourseList2\"><td><b>Pars:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("PAR", $teeid, 1, $fromdate, $todate), getNumOf("PAR", $teeid, 2, $fromdate, $todate), getNumOf("PAR", $teeid, 3, $fromdate, $todate), getNumOf("PAR", $teeid, 4, $fromdate, $todate), getNumOf("PAR", $teeid, 5, $fromdate, $todate), getNumOf("PAR", $teeid, 6, $fromdate, $todate), getNumOf("PAR", $teeid, 7, $fromdate, $todate), getNumOf("PAR", $teeid, 8, $fromdate, $todate), getNumOf("PAR", $teeid, 9, $fromdate, $todate));
		printf("<tr class=\"CourseList1\"><td><b>Bogies:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("BOGIE", $teeid, 1, $fromdate, $todate), getNumOf("BOGIE", $teeid, 2, $fromdate, $todate), getNumOf("BOGIE", $teeid, 3, $fromdate, $todate), getNumOf("BOGIE", $teeid, 4, $fromdate, $todate), getNumOf("BOGIE", $teeid, 5, $fromdate, $todate), getNumOf("BOGIE", $teeid, 6, $fromdate, $todate), getNumOf("BOGIE", $teeid, 7, $fromdate, $todate), getNumOf("BOGIE", $teeid, 8, $fromdate, $todate), getNumOf("BOGIE", $teeid, 9, $fromdate, $todate));
		printf("<tr class=\"CourseList2\"><td><b>Doubles:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("DOUBLE", $teeid, 1, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 2, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 3, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 4, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 5, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 6, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 7, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 8, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 9, $fromdate, $todate));
		printf("<tr class=\"CourseList1\"><td><b>Others:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("OTHER", $teeid, 1, $fromdate, $todate), getNumOf("OTHER", $teeid, 2, $fromdate, $todate), getNumOf("OTHER", $teeid, 3, $fromdate, $todate), getNumOf("OTHER", $teeid, 4, $fromdate, $todate), getNumOf("OTHER", $teeid, 5, $fromdate, $todate), getNumOf("OTHER", $teeid, 6, $fromdate, $todate), getNumOf("OTHER", $teeid, 7, $fromdate, $todate), getNumOf("OTHER", $teeid, 8, $fromdate, $todate), getNumOf("OTHER", $teeid, 9, $fromdate, $todate));
		
		?>
		<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<?
		if (!isNineHole($teeid))
		{
			
			printf("<tr class=\"ScoreHistoryTDHeader\"><td><b>Hole:</b></td><td class=\"HoleNumberBox\">10</td><td class=\"HoleNumberBox\">11</td><td class=\"HoleNumberBox\">12</td><td class=\"HoleNumberBox\">13</td><td class=\"HoleNumberBox\">14</td><td class=\"HoleNumberBox\">15</td><td class=\"HoleNumberBox\">16</td><td class=\"HoleNumberBox\">17</td><td class=\"HoleNumberBox\">18</td><td class=\"HoleNumberBoxTotal\"><b>In</b></td></tr>");
			printf("<tr class=\"ScoreHistoryTDHeader\"><td><b>Par:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", $row["par10"], $row["par11"], $row["par12"], $row["par13"], $row["par14"], $row["par15"], $row["par16"], $row["par17"], $row["par18"], $row["par10"] +  $row["par11"] +  $row["par12"] +  $row["par13"] +  $row["par14"] +  $row["par15"] +  $row["par16"] +  $row["par17"] +  $row["par18"]);
			printf("<tr class=\"CourseList2\"><td><b>Avg:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", formatHandi($courseAverageArray[9]), formatHandi($courseAverageArray[10]), formatHandi($courseAverageArray[11]), formatHandi($courseAverageArray[12]), formatHandi($courseAverageArray[13]), formatHandi($courseAverageArray[14]), formatHandi($courseAverageArray[15]), formatHandi($courseAverageArray[16]), formatHandi($courseAverageArray[17]), formatHandi($courseAverageArray[19]));
			printf("<tr class=\"CourseList1\"><td><b>Putt Avg:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", formatPuttHandi($coursePuttAverageArray[9]), formatPuttHandi($coursePuttAverageArray[10]), formatPuttHandi($coursePuttAverageArray[11]), formatPuttHandi($coursePuttAverageArray[12]), formatPuttHandi($coursePuttAverageArray[13]), formatPuttHandi($coursePuttAverageArray[14]), formatPuttHandi($coursePuttAverageArray[15]), formatPuttHandi($coursePuttAverageArray[16]), formatPuttHandi($coursePuttAverageArray[17]), formatPuttHandi($coursePuttAverageArray[19]));
			printf("<tr class=\"CourseList2\"><td><b>Hole In Ones:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("HOLE_IN_ONE", $teeid, 10, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 11, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 12, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 13, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 14, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 15, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 16, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 17, $fromdate, $todate), getNumOf("HOLE_IN_ONE", $teeid, 18, $fromdate, $todate));
			printf("<tr class=\"CourseList1\"><td><b>Double Eagles:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("DOUBLE_EAGLE", $teeid, 10, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 11, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 12, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 13, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 14, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 15, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 16, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 17, $fromdate, $todate), getNumOf("DOUBLE_EAGLE", $teeid, 18, $fromdate, $todate));
			printf("<tr class=\"CourseList2\"><td><b>Eagles:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("EAGLE", $teeid, 10, $fromdate, $todate), getNumOf("EAGLE", $teeid, 11, $fromdate, $todate), getNumOf("EAGLE", $teeid, 12, $fromdate, $todate), getNumOf("EAGLE", $teeid, 13, $fromdate, $todate), getNumOf("EAGLE", $teeid, 14, $fromdate, $todate), getNumOf("EAGLE", $teeid, 15, $fromdate, $todate), getNumOf("EAGLE", $teeid, 16, $fromdate, $todate), getNumOf("EAGLE", $teeid, 17, $fromdate, $todate), getNumOf("EAGLE", $teeid, 18, $fromdate, $todate));
			printf("<tr class=\"CourseList1\"><td><b>Birdies:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("BIRDIE", $teeid, 10, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 11, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 12, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 13, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 14, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 15, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 16, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 17, $fromdate, $todate), getNumOf("BIRDIE", $teeid, 18, $fromdate, $todate));
			printf("<tr class=\"CourseList2\"><td><b>Pars:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("PAR", $teeid, 10, $fromdate, $todate), getNumOf("PAR", $teeid, 11, $fromdate, $todate), getNumOf("PAR", $teeid, 12, $fromdate, $todate), getNumOf("PAR", $teeid, 13, $fromdate, $todate), getNumOf("PAR", $teeid, 14, $fromdate, $todate), getNumOf("PAR", $teeid, 15, $fromdate, $todate), getNumOf("PAR", $teeid, 16, $fromdate, $todate), getNumOf("PAR", $teeid, 17, $fromdate, $todate), getNumOf("PAR", $teeid, 18, $fromdate, $todate));
			printf("<tr class=\"CourseList1\"><td><b>Bogies:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("BOGIE", $teeid, 10, $fromdate, $todate), getNumOf("BOGIE", $teeid, 11, $fromdate, $todate), getNumOf("BOGIE", $teeid, 12, $fromdate, $todate), getNumOf("BOGIE", $teeid, 13, $fromdate, $todate), getNumOf("BOGIE", $teeid, 14, $fromdate, $todate), getNumOf("BOGIE", $teeid, 15, $fromdate, $todate), getNumOf("BOGIE", $teeid, 16, $fromdate, $todate), getNumOf("BOGIE", $teeid, 17, $fromdate, $todate), getNumOf("BOGIE", $teeid, 18, $fromdate, $todate));
			printf("<tr class=\"CourseList2\"><td><b>Doubles:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("DOUBLE", $teeid, 10, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 11, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 12, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 13, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 14, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 15, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 16, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 17, $fromdate, $todate), getNumOf("DOUBLE", $teeid, 18, $fromdate, $todate));
			printf("<tr class=\"CourseList1\"><td><b>Others:</b></td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td><td class=\"HoleNumberBox\">%s</td></tr>", getNumOf("OTHER", $teeid, 10, $fromdate, $todate), getNumOf("OTHER", $teeid, 11, $fromdate, $todate), getNumOf("OTHER", $teeid, 12, $fromdate, $todate), getNumOf("OTHER", $teeid, 13, $fromdate, $todate), getNumOf("OTHER", $teeid, 14, $fromdate, $todate), getNumOf("OTHER", $teeid, 15, $fromdate, $todate), getNumOf("OTHER", $teeid, 16, $fromdate, $todate), getNumOf("OTHER", $teeid, 17, $fromdate, $todate), getNumOf("OTHER", $teeid, 18, $fromdate, $todate));
			
			?>
			<tr><td><br></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
			<tr>
				<td></td>
			 	<td></td>
			 	<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td COLSPAN=3><b>Avg Score:</b></td>
				<td class="HoleNumberBox"><?printf("%s", formatHandi( $courseAverageArray[0] + $courseAverageArray[1]+ $courseAverageArray[2]+ $courseAverageArray[3]+ $courseAverageArray[4]+ $courseAverageArray[5]+ $courseAverageArray[6]+ $courseAverageArray[7]+ $courseAverageArray[8] + $courseAverageArray[9] + $courseAverageArray[10]+ $courseAverageArray[11]+ $courseAverageArray[12]+ $courseAverageArray[13]+ $courseAverageArray[14]+ $courseAverageArray[15]+ $courseAverageArray[16]+ $courseAverageArray[17] )); ?></td>
			</tr>
			<tr>
				<td></td>
			 	<td></td>
			 	<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td COLSPAN=3><b>Avg Putts:</b></td>
				<td class="HoleNumberBox"><? printf("%s", formatHandi( $coursePuttAverageArray[0] + $coursePuttAverageArray[1]+ $coursePuttAverageArray[2]+ $coursePuttAverageArray[3]+ $coursePuttAverageArray[4]+ $coursePuttAverageArray[5]+ $coursePuttAverageArray[6]+ $coursePuttAverageArray[7]+ $coursePuttAverageArray[8] + $coursePuttAverageArray[9] + $coursePuttAverageArray[10]+ $coursePuttAverageArray[11]+ $coursePuttAverageArray[12]+ $coursePuttAverageArray[13]+ $coursePuttAverageArray[14]+ $coursePuttAverageArray[15]+ $coursePuttAverageArray[16]+ $coursePuttAverageArray[17] )); ?></td>
			</tr>
			<?
		}
		printf("</table>");
		printf("</form>");
		printf("</table>");
		?>

		
		<form action="coursestats.php?ShowCourseStats=1&teeid=<?printf("%s", $teeid);?>" method="POST" name="statfilterform">
		<table class="FilterTable">
		<tr><td colspan="4"><b>View Stats...</b></td></tr>
		<tr><td><b>From:</b> </td><td></td> <td>
			<?
			if ($fromdate != "")
			{
				$theDate = substr($fromdate, 4, 2);
				$theDate .= "/";
				$theDate .= substr($fromdate, 6, 2);
				$theDate .= "/";
				$theDate .= substr($fromdate, 0, 4);
				//printf("got here1: %s  %s", $fromdate, $theDate);
				getDateControl(getdate(strtotime($fromdate)), "from");
			}
			else
			{
				//printf("got here2: %s", getMinDate()	);
				getDateControl(getdate(strtotime(getMinDate($teeid))), "from");
			}
			?>
		</td></tr>
		<tr><td><b>To:</b> </td><td></td>   <td>
			<?
			if ($todate != "")
			{
				$theDate = substr($todate, 4, 2);
					$theDate .= "/";
				$theDate .= substr($todate, 6, 2);
				$theDate .= "/";
				$theDate .= substr($todate, 0, 4);
				//printf("got here3: %s %s", $todate, $theDate);
				getDateControl(getdate(strtotime($todate)), "to");
			}
			else
			{
				//printf("got here4");
				getDateControl(getdate(strtotime(getMaxDate($teeid))), "to");
			}
			?>
		</td></tr>
		<tr><td colspan = "3"><input type="submit" name="dostatfilter" value="Apply Date Range"></td></tr>
		</table>
		</form>
		
		
		<?
}

function DisplayScorecard ( $type, $id = 0, $postpage = "" )
{
	if ( $type == "ENTER_ROUND" )
	{
		DisScoreEntryForm( $id, $postpage );
	}
	else if ( $type == "EDIT_ROUND" )
	{
		DisScore( $id, $postpage );
	}
	else if ( $type == "NEW_COURSE" )
	{
		DisCourseEntryForm( $postpage );
	}
	else if ( $type == "EDIT_COURSE" )
	{
		DisCourse( $id, $postpage );
	}
	else if ( $type == "COURSE_STATS" )
	{
		DisCourseStats( $id );
	}
}

function getMinDate($teeid = "")
{
	$sql = "SELECT min(dateplayed) mindate from score_tbl where userid = ";
	$sql .= $_SESSION['userid'];
	if ( $teeid != "" )
	{
		$sql .= sprintf(" and teeid = %s", $teeid);
	}
	//printf("$sql <br>");
	$result = mysql_query($sql) or die("Could not get mindate from list of scores: " . mysql_error());
	$row = mysql_fetch_array($result);
	$dbDate = $row["mindate"];
	$minDt = substr($dbDate, 5, 2);
	$minDt .= "/";
	$minDt .= substr($dbDate, 8, 2);
	$minDt .= "/";
	$minDt .= substr($dbDate, 0, 4);
	return $minDt;
}

function getMaxDate($teeid = "")
{
	$sql = "SELECT max(dateplayed) maxdate from score_tbl where userid = ";
	$sql .= $_SESSION['userid'];
	if ( $teeid != "" )
	{
		$sql .= sprintf(" and teeid = %s", $teeid);
	}
	//printf("$sql <br>");
	$result = mysql_query($sql) or die("Could not get maxdate from list of scores: " . mysql_error());
	$row = mysql_fetch_array($result);
	$dbDate = $row["maxdate"];
	$maxDt = substr($dbDate, 5, 2);
	$maxDt .= "/";
	$maxDt .= substr($dbDate, 8, 2);
	$maxDt .= "/";
	$maxDt .= substr($dbDate, 0, 4);
	return $maxDt;
}



//
//  STATISTICAL FUNCTIONS
//


function getMaxScore($uid)
{
	$sql = "select max(total) as maxscore from tools_officialscore where userid = $uid";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not get max score " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "No Scores";
	$row = mysql_fetch_array($result);
	return $row["maxscore"];
}

function getCourseAndTeeName($teeid)
{
	$sql = "select concat(ct.name, ' (', tt.name, ')') as courseandtee from course_tbl ct, tee_tbl tt where tt.courseid = ct.id and tt.id = $teeid";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not get course and tee name " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "Could not find course and tee name";
	$row = mysql_fetch_array($result);
	return $row["courseandtee"];
}

function getCourseID($teeid)
{
	$sql = "select courseid from tee_tbl where id = $teeid";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not get course id: " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "Could not find course id";
	$row = mysql_fetch_array($result);
	return $row["courseid"];
}

function SlopeAndRatingExist($teeid, $frontorback)
{
    $sql = "";
    if ($frontorback == "Front")
        $sql = "select slopef as slope, ratingf as rating from tee_tbl where id = $teeid";
    else
        $sql = "select slopeb as slope, ratingb as rating from tee_tbl where id = $teeid";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not get slope and rating info: " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "Could not find course id";
	$row = mysql_fetch_array($result);
    $slope = $row["slope"];
    $rating = $row["rating"];
    if ((strlen($slope) > 0) && (strlen($rating) > 0))
        return 1;
    else
        return 0;
}

function getAvgScore($uid, $fromdate="", $todate="")
{

	if (strlen($teeid) > 0)
		$sql = "select avg(score) as avgscore from score_tbl where userid = $uid and teeid = $teeid";
	else
		$sql = "select avg(score) as avgscore from score_tbl where userid = $uid";
		
	
	if ( strlen($fromdate) > 0 )
		$sql .= " and dateplayed >= $fromdate and dateplayed <= $todate";
	//printf("sql:  %s<br>", $sql);
	$result = mysql_query($sql) or die("Could not get avg score " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "No Scores";
	$row = mysql_fetch_array($result);
	return number_format($row["avgscore"], 1);
}

function getAvgOverallScore($uid)
{

    $sql = "select avg(total) as avgscore from tools_officialscore where userid = $uid";
	$result = mysql_query($sql) or die("Could not get avg score " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "No Scores";
	$row = mysql_fetch_array($result);
	return number_format($row["avgscore"], 1);
}

function getMinScore($uid)
{
	$sql = "select min(total) as minscore from tools_officialscore where userid = $uid";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not get min score " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "No Scores";
	$row = mysql_fetch_array($result);
	return $row["minscore"];
}

function getMinDatePlayed($uid)
{
	$sql = "select min(dateplayed) as mindateplayed from score_tbl where userid = $uid";
	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not get min dateplayed " . mysql_error());
	if (mysql_num_rows($result) == 0)
		return "No Scores";
	$row = mysql_fetch_array($result);
	return $row["mindateplayed"];
}

function getScoreHistory($uid, $fromdate="", $todate="")
{
	$sql = "select score from score_tbl where userid = $uid";
	if ( strlen($fromdate) > 0 )
		$sql .= " and dateplayed >= $fromdate and dateplayed <= $todate";
	$sql .= " order by dateplayed desc limit 100";
	$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
	//if (mysql_num_rows($result) == 0)
	//	return "No Scores";
	$scoresArray = array();
	
	while ($row = mysql_fetch_array($result))
		$scoresArray[] = $row["score"];

	return array_reverse($scoresArray);
}

function getLastScores($uid, $num, $teeid="")
{
	$sql = "";
	if ( strlen($teeid) > 0 )
		$sql = "select score, dateplayed as thedate, date_format(dateplayed,'%m/%d/%y') as dateplayed from score_tbl where userid = $uid and teeid = $teeid order by thedate desc limit $num;";
	else
		$sql = "select total as score, dateplayed as thedate, date_format(dateplayed,'%m/%d/%y') as dateplayed from tools_officialscore where userid = $uid order by thedate desc limit $num;";
	//echo $sql;
	$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
	//if (mysql_num_rows($result) == 0)
	//	return "No Scores";
	$scoresArray = array();
	
	//
	//  THIS GETS A LITTLE FUNKY TO ACCOUNT FOR PLAYING MULTIPLE ROUNDS IN ONE DAY
	//  WE NEED TO MAKE A DIFFERENT KEY FOR EACH KEY VALUE PAIR IN THE ASSOCIATIVE ARRAY
	//  SINCE OUR KEYS ARE DATES I'M APPENDING "(X)" ON THE END OF ANY DUPLICATES
	//
	$lastDatePlayed = "";
	$i = 1;
	while ($row = mysql_fetch_array($result))
	{
		if ($row["dateplayed"] == $lastDatePlayed)
		{
			$datePlayed = $row["dateplayed"];
			$datePlayed .= '(';
			$datePlayed .= $i;
			$datePlayed .= ')';
			$i++;
			$scoresArray[$datePlayed] = $row["score"];
		}
		else
		{
			$scoresArray[$row["dateplayed"]] = $row["score"];
			$lastDatePlayed = $row["dateplayed"];
			$i = 1;
		}
	}
	return array_reverse($scoresArray);
}


// THE ONLY DIFFERENCE FROM THE ABOVE FUNCTION IS THIS ONE RETURNS DATE AND TEEID AS KEY VALUE
// INSTEAD OF DATE AND SCORE.
function getDatesAndIds($uid, $num, $teeid="")
{
	$sql = "";
	if ( strlen($teeid) > 0 )
		$sql = "select id, dateplayed as thedate, date_format(dateplayed,'%m/%d/%y') as dateplayed from score_tbl where userid = $uid and teeid = $teeid order by thedate desc limit $num;";
	else
		$sql = "select id, dateplayed as thedate, date_format(dateplayed,'%m/%d/%y') as dateplayed from score_tbl where userid = $uid order by thedate desc limit $num;";
	//echo $sql;
	$result = mysql_query($sql) or die("Could not get ids " . mysql_error());
	//if (mysql_num_rows($result) == 0)
	//	return "No Scores";
	$scoresArray = array();
	
	//
	//  THIS GETS A LITTLE FUNKY TO ACCOUNT FOR PLAYING MULTIPLE ROUNDS IN ONE DAY
	//  WE NEED TO MAKE A DIFFERENT KEY FOR EACH KEY VALUE PAIR IN THE ASSOCIATIVE ARRAY
	//  SINCE OUR KEYS ARE DATES I'M APPENDING "(X)" ON THE END OF ANY DUPLICATES
	//
	$lastDatePlayed = "";
	$i = 1;
	while ($row = mysql_fetch_array($result))
	{
		if ($row["dateplayed"] == $lastDatePlayed)
		{
			$datePlayed = $row["dateplayed"];
			$datePlayed .= '(';
			$datePlayed .= $i;
			$datePlayed .= ')';
			$i++;
			$scoresArray[$datePlayed] = $row["id"];
		}
		else
		{
			$scoresArray[$row["dateplayed"]] = $row["id"];
			$lastDatePlayed = $row["dateplayed"];
			$i = 1;
		}
	}
	return array_reverse($scoresArray);
}

function getAvgPuttsPerGreen($uid, $fromdate="", $todate="")
{		
		$sql = "select * from score_tbl where userid = $uid";
		if ( strlen($fromdate) > 0 )
			$sql .= " and dateplayed >= $fromdate and dateplayed <= $todate";
		$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
		$puttTotal = 0;
		$puttCount = 0;
		while ($row = mysql_fetch_array($result))
		{
			for ($i = 1; $i < 19; $i++)
			{
				$colName = "putt";
				$colName .= $i;
				if (strlen($row[$colName]) > 0)
				{
					$puttTotal += $row[$colName];				
					$puttCount++;
				}
			}
		}
		if ($puttCount == 0)
			return -1;	// NONE WERE RECORDED DURING THIS TIME PERIOD
		else
		{
			$avg = ( $puttTotal / $puttCount );
			return number_format($avg, 1);
		}
}

function getAvgPuttsPerGreenByCourse($uid, $teeid, $fromdate="", $todate="")
{		
		$sql = "select * from score_tbl where userid = $uid and teeid = $teeid";
		if ( strlen($fromdate) > 0 )
			$sql .= " and dateplayed >= $fromdate and dateplayed <= $todate";
		$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
		$puttTotal = 0;
		$puttCount = 0;
		while ($row = mysql_fetch_array($result))
		{
			for ($i = 1; $i < 10; $i++)
			{
				$colName = "putt";
				$colName .= $i;
				if (strlen($row[$colName]) > 0)
				{
					$puttTotal += $row[$colName];				
					$puttCount++;
				}
			}
		}
		if ($puttCount == 0)
			return -1;	// NONE WERE RECORDED DURING THIS TIME PERIOD
		else
		{
			$avg = ( $puttTotal / $puttCount );
			return number_format($avg, 1);
		}
}

function getPuttsPerGreen($ScoreID)
{		
		$sql = "select * from score_tbl where id = $ScoreID";
		$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
		$puttTotal = 0;
		$puttCount = 0;
		while ($row = mysql_fetch_array($result))
		{
			for ($i = 1; $i < 19; $i++)
			{
				$colName = "putt";
				$colName .= $i;
				if (strlen($row[$colName]) > 0)
				{
					$puttTotal += $row[$colName];				
					$puttCount++;
				}
			}
		}
		if ($puttCount == 0)
			return "N/A";	// NONE WERE RECORDED DURING THIS TIME PERIOD
		else
		{
			$avg = ( $puttTotal / $puttCount );
			return number_format($avg, 1);
		}
}



function getPercentFairwaysHit($uid, $fromdate="", $todate="")
{	
		$sql = "select distinct teeid from score_tbl where userid = $uid";
		if ( strlen($fromdate) > 0 )
			$sql .= " and dateplayed >= $fromdate and dateplayed <= $todate";
		$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
		$fairwaysHit = 0;
		$fairwaysPossible = 0;
		while ($row = mysql_fetch_array($result))
		{
			$fairwaysArray = array();
			$fairwaysArray = getFairwaysArray($row[teeid], $fromdate, $todate);
			$fairwaysPossible += $fairwaysArray[1];
			$fairwaysHit += $fairwaysArray[0];
		}
	
		if ( $fairwaysPossible == 0)
			return -1;	// NONE WERE RECORDED DURING THIS TIME PERIOD
		else
		{ 
			$percent = number_format(100*( $fairwaysHit / $fairwaysPossible ), 1);
			$number = $percent;
			//$number .= "%";
			return $number;
		}
}

function getPercentGreensInRegulation($uid, $fromdate="", $todate="")
{	
		$sql = "select distinct teeid from score_tbl where userid = $uid";
		if ( strlen($fromdate) > 0 )
			$sql .= " and dateplayed >= $fromdate and dateplayed <= $todate";
		$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
		$greensHit = 0;
		$greensPossible = 0;
		while ($row = mysql_fetch_array($result))
		{
			$greensArray = array();
			$greensArray = getGreensArray($row[teeid], $fromdate, $todate);
			$greensPossible += $greensArray[1];
			$greensHit += $greensArray[0];
		}
		
		//printf("Greens Possible:  %s<br>", $greensPossible);
		//printf("Greens Hit:  %s<br>", $greensHit);
		
		if ( $greensPossible == 0)
			return -1;	// NONE WERE RECORDED DURING THIS TIME PERIOD
		else
		{ 
			$percent = number_format(100*( $greensHit / $greensPossible ), 1);
			$number = $percent;
			//$number .= "%";
			return $number;
		}
}

function getPercentGIR( $scoreid )
{
	$sql = "select * from score_tbl where id = $scoreid";

	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	
	$greensArray = array();
	$greensHit = 0;
	$greensPossible = 0;
	while ($row = mysql_fetch_array($result))
	{
		if ($row["greens"] != null)
		{
			if ($row["hole1"] != null)
				$greensPossible += 9;
			if ($row["hole10"] != null)
				$greensPossible += 9;
			$greensHit += $row["greens"];
		}
		else
			return "N/A";
	}
		
	$greensHit = number_format(100*($greensHit / $greensPossible), 1);
	return $greensHit;
}


function getPenaltiesPerNineHoles($uid, $fromdate="", $todate="")
{	
		$sql = "select distinct teeid from score_tbl where userid = $uid";
		if ( strlen($fromdate) > 0 )
			$sql .= " and dateplayed >= $fromdate and dateplayed <= $todate";
		$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
		$penalties = 0;
		$holesPlayed = 0;
		while ($row = mysql_fetch_array($result))
		{
			$penaltiesArray = array();
			$penaltiesArray = getpenaltiesArray($row[teeid], $fromdate, $todate);
			$holesPlayed += $penaltiesArray[1];
			$penalties += $penaltiesArray[0];
		}
		
		//printf("HolesPlayed:  %s<br>", $holesPlayed);
		//printf("Penalties:  %s<br>", $penalties);
	
		if ( $holesPlayed == 0)
			return -1;	// NONE WERE RECORDED DURING THIS TIME PERIOD
		else
		{ 
			$numOfNineHoleRounds = $holesPlayed / 9;
			$numOfPenaltiesPerNineHoleRound = $penalties / $numOfNineHoleRounds;
			return number_format($numOfPenaltiesPerNineHoleRound, 1);
		}
}

function getPenaltiesPerNineForRound($ScoreID)
{	
	$sql = "select * from score_tbl where id = $ScoreID";

	//printf("%s<br>", $sql);
	$result = mysql_query($sql) or die("Could not select score history " . mysql_error());
	if (mysql_num_rows($result) == 0)
    {
        print("No scores for that date range or course");
        DisplayCommonFooter();
        die();
    }
	
	$penaltiesArray = array();
	$holesPlayed = 0;
	$penalties = 0;
	$row = mysql_fetch_array($result);
	if ($row["penalties"] != null)
	{
		if ($row["hole1"] != null)
			$holesPlayed += 9;
		if ($row["hole10"] != null)
			$holesPlayed += 9;
		$penalties += $row["penalties"];
		$numOfNineHoleRounds = $holesPlayed / 9;
		$numOfPenaltiesPerNineHoleRound = $penalties / $numOfNineHoleRounds;
		return number_format($numOfPenaltiesPerNineHoleRound, 1);
	}
	else
	{
		return "N/A";
	}
}

function getAvgScoreByMonth($uid)
{	
	$theArray = array();
	$monthCount = 0;
	$firstDayOfMonth = mktime(0, 0, 0, date("m"), 1, date("y"));
	$FloorDate = strtotime("01/01/2003");
	$i = 0;
	while ($monthCount < 12 && $FloorDate < $firstDayOfMonth)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		
		$avgScore = getAvgScore($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $avgScore > 0 )
		{
			$theArray[date("M y", $firstDayOfMonth)]=$avgScore;
			$monthCount++;
		}
		$i++;
	}
	return array_reverse($theArray);
/*
	$theArray = array();
	for ($i=0; $i<12; $i++)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		
		$avgScore = getAvgScore($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $avgScore > 0 )
			$theArray[date("M y", $firstDayOfMonth)]=$avgScore;
	}
	return array_reverse($theArray);
	*/
}

function getAvgPuttsPerGreenByMonth($uid)	// GETS THE LAST 12 MONTHS
{	
	$theArray = array();
	$monthCount = 0;
	$firstDayOfMonth = mktime(0, 0, 0, date("m"), 1, date("y"));
	$FloorDate = strtotime("01/01/2003");
	$i = 0;
	while ($monthCount < 12 && $FloorDate < $firstDayOfMonth)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		
		$avgPutts = getAvgPuttsPerGreen($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $avgPutts > 0 )
		{
			$theArray[date("M y", $firstDayOfMonth)]=$avgPutts;
			$monthCount++;
		}
		$i++;
	}
	return array_reverse($theArray);



	/*
	$theArray = array();
	for ($i=0; $i<12; $i++)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		
		$avgPutts = getAvgPuttsPerGreen($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $avgPutts > 0 )
			$theArray[date("M y", $firstDayOfMonth)]=$avgPutts;
	}
	return array_reverse($theArray);
	*/
	
}

function getPercentFairwaysHitByMonth($uid)	// GETS THE LAST 12 MONTHS
{	

	$theArray = array();
	$monthCount = 0;
	$firstDayOfMonth = mktime(0, 0, 0, date("m"), 1, date("y"));  
	$FloorDate = strtotime("01/01/2003");
	$i = 0;
	while ($monthCount < 12 && $FloorDate < $firstDayOfMonth)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		
		$perFair = getPercentFairwaysHit($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $perFair > 0 )
		{
			$theArray[date("M y", $firstDayOfMonth)]=$perFair;
			$monthCount++;
		}
		$i++;
	}
	return array_reverse($theArray);
	
	/*
	$theArray = array();
	for ($i=0; $i<12; $i++)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		//$theArray[date("M", $firstDayOfMonth)]=getPercentFairwaysHit($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		
		$perFair = getPercentFairwaysHit($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $perFair > 0 )
			$theArray[date("M y", $firstDayOfMonth)]=$perFair;
	}
	return array_reverse($theArray);
	*/
}

function getPenaltiesPerNineHolesByMonth($uid)	// GETS THE LAST 12 MONTHS
{	

	$theArray = array();
	$monthCount = 0;
	$firstDayOfMonth = mktime(0, 0, 0, date("m"), 1, date("y"));
	$FloorDate = strtotime("01/01/2003");
	$i = 0;
	while ($monthCount < 12 && $FloorDate < $firstDayOfMonth)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		
		$penalties = getPenaltiesPerNineHoles($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $penalties > 0 )
		{
			$theArray[date("M y", $firstDayOfMonth)]=$penalties;
			$monthCount++;
		}
		$i++;
	}
	return array_reverse($theArray);

	/*
	$theArray = array();
	for ($i=0; $i<12; $i++)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		//$theArray[date("M", $firstDayOfMonth)]=getPenaltiesPerNineHoles($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		
		
		$penalties = getPenaltiesPerNineHoles($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $penalties > 0 )
			$theArray[date("M y", $firstDayOfMonth)]=$penalties;
	}
	return array_reverse($theArray);
	*/
}

function getPercentGreensInRegulationByMonth($uid)	// GETS THE LAST 12 MONTHS
{	
	
	$theArray = array();
	$monthCount = 0;
	$firstDayOfMonth = mktime(0, 0, 0, date("m"), 1, date("y"));
	$FloorDate = strtotime("01/01/2003");
	$i = 0;
	while ($monthCount < 12 && $FloorDate < $firstDayOfMonth)
	{
		$firstDayOfMonth = mktime(0, 0, 0, date("m")-$i, 1, date("y"));
		$lastDayOfMonth = mktime(0, 0, 0, date("m")-($i-1), 0, date("y"));
		//printf("First day of the month %s<br>", date("m/d/y", $firstDayOfMonth));
		//printf("Last day of the month %s<br>", date("m/d/y", $lastDayOfMonth));
		
		$perGIR = getPercentGreensInRegulation($uid, date("Ymd", $firstDayOfMonth), date("Ymd", $lastDayOfMonth));
		if ( $perGIR > 0 )
		{
			$theArray[date("M y", $firstDayOfMonth)]=$perGIR;
			$monthCount++;
		}
		$i++;
	}
	return array_reverse($theArray);
}


function DisplayQuickStats($uid)
{
	printf("<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">");

    printf("<tr class=\"ScoreHistoryTDHeader\"><td colspan=\"2\"><b>SUMMARY</b></td></tr>");
	printf("<tr class=\"CourseList2\"><td>");	
	$minScore = getMinScore($uid);
	if (strlen($minScore) == 0)
		$minScore = "N/A";
	printf("Best Score</td><td>%s</td></tr>", $minScore);
	
	printf("<tr class=\"CourseList1\"><td>");
	$avgScore = getAvgOverallScore($uid);
	if ($avgScore <= 0)
		$avgScore = "N/A";
	printf("Average Score</td><td>%s</td></tr>", round($avgScore));

	
	printf("<tr class=\"CourseList2\"><td>");
	$maxScore = getMaxScore($uid);
	if (strlen($maxScore) == 0)
		$maxScore = "N/A";
	printf("Worst Score</td><td>%s</td></tr>", $maxScore);

	
	printf("<tr class=\"CourseList1\"><td>");
	$avgPutts = getAvgPuttsPerGreen($uid);
	if ($avgPutts <= 0)
		$avgPutts = "N/A";
	printf("Avg Putts Per Green</td><td>%s</td></tr>", $avgPutts);

	
	printf("<tr class=\"CourseList2\"><td>");
	$fairways = getPercentFairwaysHit($uid);
	if ($fairways <= 0)
		$fairways = "N/A";
	else
		$fairways .= "%";
	printf("Fairways Hit</td><td>%s</td></tr>", $fairways);

	
	printf("<tr class=\"CourseList1\"><td>");
	$greens = getPercentGreensInRegulation($uid);
	if ($greens <= 0)
		$greens = "N/A";
	else
		$greens .= "%";
	printf("Greens in Regulation</td><td>%s</td></tr>", $greens);

	
	printf("<tr class=\"CourseList2\"><td>");
	$penalties = getPenaltiesPerNineHoles($uid);
	if ($penalties <= 0)
		$penalties = "N/A";
	printf("Penalties Per Nine</td><td>%s</td></tr>", $penalties);

	
	printf("</table>");
}




function getMaxArrayValue($arr)
{
	$count = count($arr);
	if ($count == 0)
		return "empty";
	$max = 0;
	for ($i = 0; $i < $count; $i++)
	{
		if ( $i == 0 )
			$max = current($arr);
		else if (current($arr) > $max)
			$max = current($arr);
		next($arr);
	}
	return $max;
}

function getMinArrayValue($arr)
{
	$count = count($arr);
	if ($count == 0)
		return "empty";
	$min = 0;
	for ($i = 0; $i < $count; $i++)
	{
		if ( $i == 0 )
			$min = current($arr);
		else if (current($arr) < $min)
			$min = current($arr);
		next($arr);
	}
	return $min;
}

function setRangeValues(&$minVal, &$maxVal, $percent)
{
	// MAKE IT X PERCENT SMALLER
	$minVal *= (1 - $percent);
	
	// DROP THE DECIMAL POINTS
	$minVal = floor($minVal);
	
	// MAKE IT X PERCENT BIGGER
	$maxVal *= (1 + $percent);
	
	// DROP THE DECIMAL POINTS
	$maxVal = ceil($maxVal);
}

function updateEmail($userId, $email)
{
	$sql = "update user_tbl set email = '$email' where id = '$userId'";
	$result = mysql_query($sql) or die("Could not update account with new email: " . mysql_error());;
}

function getConfigValue( $configName )
{
	$sql = "select * from config_tbl where name = '";
	$sql .= $configName;
	$sql .= "'";
	
	$result = mysql_query($sql) or die("Could not get config value:  " . mysql_error());
	$row = mysql_fetch_array($result);
	if ( mysql_num_rows($result) == 0 )
		die("Could not get config value.");
	else
		return $row["value"];
}


//
//  THIS FUNCTION IS SO YOU CAN HAVE A CONFIG NAME LIKE "ADMIN_USERS" WITH 
//  A BUNCH OF DIFFERENT VALID VALUES.  IN EFFECT A GROUP.
//
function inConfigName( $configName, $configValue )
{
	$sql = "select * from config_tbl where name = '$configName' and value = '$configValue'";
	
	$result = mysql_query($sql) or die("Could not get config value:  " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		return 0;
	return 1;
}

function SendEmail($to, $subject, $message)
{
	$headers = "From: CustomerCare@StatGolf.com\r\n";
	mail($to, $subject, $message, $headers);
}

function PrintGiftCertificate($gc)
{
?>
			<h1>Gift Certificate</h1> 
			<div id="paragraphtext">
			<b>THANK YOU FOR YOUR GIFT CERTIFICATE PURCHASE</b><br><br>
			
			This certificate is good for a 1 year membership to StatGolf, the premier golf handicap calculator and
			game analysis tool on the web.<br><br>
			
			Please print this page and present it to the recipient<br><br>
			
			<?printf("Use the following gift code when signing up or renewing an existing membership at <a href=\"www.statgolf.com\">www.statgolf.com</a>:  <b>%s</b>  ", $_GET['gc']);?>
			<br><br>We appreciate your business and hope you enjoy StatGolf.  If you have
			any questions or concerns please drop us a line at
			<a href="mailto:customercare@statgolf.com">customercare@statgolf.com</a>.
			</div>
<?
}

function getNumScores()
{
	$sql = "select * from score_tbl";
	
	$result = mysql_query($sql) or die("Could not get scores:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumScoresSince( $days )
{
	$sql = "select * from score_tbl where createdt >= CURDATE() - INTERVAL $days day;";
	
	$result = mysql_query($sql) or die("Could not get scores:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumScoresYesterday()
{
	$sql = "select * from score_tbl where createdt > CURDATE() - INTERVAL 1 day and createdt < CURDATE()";
	
	$result = mysql_query($sql) or die("Could not get scores:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumUsersSince( $days )
{
	$sql = "select * from user_tbl where createdt >= CURDATE() - INTERVAL $days day;";
	
	$result = mysql_query($sql) or die("Could not get users:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumUsersYesterday()
{
	$sql = "select * from user_tbl where createdt > CURDATE() - INTERVAL 1 day and createdt < CURDATE()";
	
	$result = mysql_query($sql) or die("Could not get users:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumUsers()
{
	$sql = "select * from user_tbl";
	
	$result = mysql_query($sql) or die("Could not get users:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumCourses()
{
	$sql = "select * from course_tbl;";
	
	$result = mysql_query($sql) or die("Could not get courses:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumCoursesSince( $days )
{
	$sql = "select * from course_tbl where createdt >= CURDATE() - INTERVAL $days day;";
	
	$result = mysql_query($sql) or die("Could not get users:  " . mysql_error());
	return mysql_num_rows($result);
}

function getNumCoursesYesterday()
{
 	$sql = "select * from course_tbl where createdt > CURDATE() - INTERVAL 1 day and createdt < CURDATE()";
	
	$result = mysql_query($sql) or die("Could not get users:  " . mysql_error());
	return mysql_num_rows($result);
}


function getUsersAndNumScores()
{
	$sql = "select ut.email as email, count(score) as num_scores, date_format(max(st.createdt), '%m/%d/%Y') as last_score, date_format(ut.createdt, '%m/%d/%Y') as acct_create_dt from user_tbl ut, score_tbl st where ut.id = st.userid group by ut.id order by num_scores desc limit 20";
	
	$result = mysql_query($sql) or die("Could not get Users and Num Scores:  " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		return 0;
	return $result;
}

function getStatesAndNumCourses()
{
	//$sql = "select s.name state, count(*) num_courses from state_tbl s, (select upper(name) name, upper(state) state  from course_tbl  group by name) c where (UPPER(s.abbr) = UPPER(c.state) or UPPER(s.name) = UPPER(c.state)) group by s.name order by s.name asc";
    $sql = "select s.name state, count(*) num_courses from state_tbl s, (select upper(name) name, upper(state) state  from course_tbl  group by name) c where (UPPER(s.abbr) = UPPER(c.state) or UPPER(s.name) = UPPER(c.state)) group by s.name order by num_courses desc";
	
	$result = mysql_query($sql) or die("Could not get States and Num Courses:  " . mysql_error());
	if ( mysql_num_rows($result) == 0 )
		return 0;
	return $result;
}

//
//  If you ever get stored procs, use this function.
//
/*
function CopyCourse($CID, $UID)
{
	$sql = "call CopyCourse($CID, $UID)";
	mysql_query($sql) or die("Could not get copy course:  " . mysql_error());
}
*/
function CopyCourse($CID, $UID)
{
	//
	//  COPY THE COURSE
	//
	$sql = "select * from course_tbl where id = $CID";
	$result = mysql_query($sql) or die("Could not get course " . mysql_error());
	$row = mysql_fetch_array($result);
	$coursename = $row["name"];
	$coursecity = $row["city"];
	$coursestate = $row["state"];
	$insert = "insert into course_tbl (userid, name, city, state) values ($UID, '$coursename', '$coursecity', $coursestate)";	
	//echo $insert;
	mysql_query($insert) or die("Could not copy course:  " . mysql_error());
	
	//
	//  COPY THE ASSOCIATED TEES
	//
	$sql = "select * from tee_tbl where courseid = $CID";
	//echo $sql;
	$result = mysql_query($sql) or die("Could not get tees " . mysql_error());
	$courseid = mysql_insert_id();
	while($row = mysql_fetch_array($result))
	{
		$name = $row['name']; 
		$slope = $row['slope']; 
		$rating = $row['rating'];
		$par1 = $row['par1'];
		$par2 = $row['par2'];
		$par3 = $row['par3']; 
		$par4 = $row['par4']; 
		$par5 = $row['par5']; 
		$par6 = $row['par6']; 
		$par7 = $row['par7']; 
		$par8 = $row['par8']; 
		$par9 = $row['par9']; 
		if (is_null($row['par1']))
			$par1 = "null";
		if (is_null($row['par2']))
			$par2 = "null";
		if (is_null($row['par3']))
			$par3 = "null";
		if (is_null($row['par4']))
			$par4 = "null";
		if (is_null($row['par5']))
			$par5 = "null";
		if (is_null($row['par6']))
			$par6 = "null";
		if (is_null($row['par7']))
			$par7 = "null";
		if (is_null($row['par8']))
			$par8 = "null";
		if (is_null($row['par9']))
			$par9 = "null";
		
		$insert = "INSERT INTO tee_tbl (courseid, name, slope, rating, par1, par2, par3, par4, par5, par6, par7, par8, par9) VALUES($courseid, '$name', $slope, $rating, $par1, $par2, $par3, $par4, $par5, $par6, $par7, $par8, $par9)";	
		//echo $insert;
		mysql_query($insert) or die("Could not copy tees:  " . mysql_error());	
	}
	
}

function GetMostRecentScores($num)
{
	ConnectToDB();
	$sql = "select date_format(s.dateplayed,'%b %D') dt, s.score score, c.name coursename, u.fname firstname, st.abbr state from score_tbl s, tee_tbl t, course_tbl c, user_tbl u, state_tbl st where s.teeid = t.id and t.courseid = c.id and s.userid = u.id and ((upper(c.state) = upper(st.name)) or (upper(c.state) = upper(st.abbr))) order by s.dateplayed desc, s.id desc limit $num";
	$result = mysql_query($sql) or die("Could not get scores " . mysql_error());
	return $result;
}
?>
