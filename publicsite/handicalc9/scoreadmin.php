<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function validatePostVar($a)
{
	if ( $a == "" )
		$a = "null";
	return $a;
}

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<h3>Score Detail.</h3>");
	if ($_POST["UpdateScore"])
	{
        $type = "I";
        if ($_POST["Tournament"])
            $type .= "T";
        if ($_POST["Away"])
            $type .= "A";
        if ($_POST["Penalty"])
            $type .= "P";
	/*
	
		if ( 	!( $_POST["par1"] && $_POST["par2"] && $_POST["par3"] && $_POST["par4"] && $_POST["par5"] && $_POST["par6"] && $_POST["par7"] && $_POST["par8"] && $_POST["par9"] && $_POST["par10"] && $_POST["par11"] && $_POST["par12"] && $_POST["par13"] && $_POST["par14"] && $_POST["par15"] && $_POST["par16"] && $_POST["par17"] && $_POST["par18"] ) && 
			!( $_POST["par1"] && $_POST["par2"] && $_POST["par3"] && $_POST["par4"] && $_POST["par5"] && $_POST["par6"] && $_POST["par7"] && $_POST["par8"] && $_POST["par9"]) && 
			!( $_POST["par10"] && $_POST["par11"] && $_POST["par12"] && $_POST["par13"] && $_POST["par14"] && $_POST["par15"] && $_POST["par16"] && $_POST["par17"] && $_POST["par18"] ) )
		{
			printf("here");
			printf("You must enter a complete front 9, a complete back 9 or a complete 18 hole score.");
			return;
		}
			
	*/
		$SCORE_TYPE = "";
        if ( $_POST["par1"] && $_POST["par2"] && $_POST["par3"] && $_POST["par4"] && $_POST["par5"] && $_POST["par6"] && $_POST["par7"] && $_POST["par8"] && $_POST["par9"])
			$SCORE_TYPE = "F";
		else
		{
			printf("You must enter a complete front 9, a complete back 9 or a complete 18 hole score.  No partials");
            DisplayCommonFooter();
			return;
		}
        
			$par1 = validatePostVar( $_POST["par1"] );
			$par2 = validatePostVar( $_POST["par2"] );
			$par3 = validatePostVar( $_POST["par3"] );
			$par4 = validatePostVar( $_POST["par4"] );
			$par5 = validatePostVar( $_POST["par5"] );
			$par6 = validatePostVar( $_POST["par6"] );
			$par7 = validatePostVar( $_POST["par7"] );
			$par8 = validatePostVar( $_POST["par8"] );
			$par9 = validatePostVar( $_POST["par9"] );
			$putt1 = validatePostVar( $_POST["putt1"] );
			$putt2 = validatePostVar( $_POST["putt2"] );
			$putt3 = validatePostVar( $_POST["putt3"] );
			$putt4 = validatePostVar( $_POST["putt4"] );
			$putt5 = validatePostVar( $_POST["putt5"] );
			$putt6 = validatePostVar( $_POST["putt6"] );
			$putt7 = validatePostVar( $_POST["putt7"] );
			$putt8 = validatePostVar( $_POST["putt8"] );
			$putt9 = validatePostVar( $_POST["putt9"] );
			$greens = validatePostVar( $_POST["greensInReg"] );
			$fairways = validatePostVar( $_POST["fairwaysHit"] );
			$penalties = validatePostVar( $_POST["penalties"] );
			$tot = ($_POST["par1"] + $_POST["par2"] + $_POST["par3"] + $_POST["par4"] + $_POST["par5"] + $_POST["par6"] + $_POST["par7"] + $_POST["par8"] + $_POST["par9"]);
			
			//
			// FORMAT THE DATE FOR SQL
			//
			$theDate = sprintf("%u",$_POST["TheYear"]); // YEAR
			if ($_POST["TheMonth"] < 10)
				$theDate .= sprintf("0%u",$_POST["TheMonth"]); // MONTH
			else
				$theDate .= sprintf("%u",$_POST["TheMonth"]); // MONTH
			if ($_POST["TheDay"] < 10)
				$theDate .= sprintf("0%u",$_POST["TheDay"]); // DAY
			else
				$theDate .= sprintf("%u",$_POST["TheDay"]); // DAY
			
			//$theComment = preg_replace("/\'/", "''", $_POST["commentText"]);
			$theComment = $_POST["commentText"];
			$sql = "update score_tbl set type = '$type', comment = '$theComment', hole1 = $par1, hole2 = $par2, hole3 = $par3, hole4 = $par4, hole5 = $par5, hole6 = $par6, hole7 = $par7, hole8 = $par8, hole9 = $par9, putt1 = $putt1, putt2 = $putt2, putt3 = $putt3, putt4 = $putt4, putt5 = $putt5, putt6 = $putt6, putt7 = $putt7, putt8 = $putt8, putt9 = $putt9, greens = $greens, fairways = $fairways, penalties = $penalties";
					
			$sql .= ", teeid = ";
			$sql .= $_POST["Tees"];
			$sql .= ", dateplayed = ";
			$sql .= $theDate;
			$sql .= ", score = ";
			$sql .= $tot;
			$sql .= " where id = ";
			$sql .= $_POST["id"];			
		
		
		
		
        $ScoreID = $_POST["id"];
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
            printf("<form action=\"scoreadmin.php\" method=\"POST\"");
        	printf("<input type=\"submit\" name=\"ConfirmOutOfSeasonScore\" value=\"Post This Score\">");
            printf("<input type=\"hidden\" name=\"SQL\" value=\"$sql\">");
            printf("<input type=\"hidden\" name=\"ScoreID\" value=\"$ScoreID\">");
        	printf("</form> ");
            DisplayCommonFooter();
            return;
        }
        //print("$sql");
		$result = mysql_query($sql) or die("Could not update score: " . mysql_error());
        
        // We've updated the actual score, now calculate the Adjusted Gross Score and update the scoring record with it.
        $AGS = GetAdjustedGrossScore($ScoreID);
        //printf("Adjusted Gross Score:  %s",$AGS);
        UpdateAdjustedGrossScore($ScoreID, $AGS);
        
        //  Now that we have updated the scoring record with AGS, we can update the official score
        UpdateOfficialScore($ScoreID);
        
        //  Update the Handicap
        UpdateHandicapIndex();

		?>
		<meta http-equiv="Refresh" content="0; URL=./home.php">
		<?
        
	}
    else if ($_POST["ConfirmOutOfSeasonScore"])
	{
        //$sql = preg_replace("/\\\/", "", $_POST["SQL"]);
		$sql = $_POST["SQL"];
        $ScoreID = $_POST["ScoreID"];
        //printf("ScoreID:  $ScoreID");
		$sql = stripslashes($sql);
		//print("$sql");
        $result = mysql_query($sql) or die("Could not update score: " . mysql_error());
        
        // We've updated the actual score, now calculate the Adjusted Gross Score and update the scoring record with it.
        $AGS = GetAdjustedGrossScore($ScoreID);
        //printf("Adjusted Gross Score:  %s",$AGS);
        UpdateAdjustedGrossScore($ScoreID, $AGS);
        
        //  Now that we have updated the scoring record with AGS, we can update the official score
        UpdateOfficialScore($ScoreID);
        
        //  Update the Handicap
        UpdateHandicapIndex();

        ?>
		<meta http-equiv="Refresh" content="0; URL=./home.php">
		<?

    }
	else if ($_POST["DeleteScore"])
	{
        DeleteOfficialScore($_POST["id"]);
        UpdateHandicapIndex();
		//printf("Score successfully deleted.");
		?>
		<meta http-equiv="Refresh" content="0; URL=./home.php">
		<?
	}
	else
	{
		DisplayScorecard( "EDIT_ROUND", $_GET["scoreID"], $_SERVER['PHP_SELF'] );
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
