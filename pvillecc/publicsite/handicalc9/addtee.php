<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
	printf("<br><br><h3>Add a New Set of Tees.</h3>");
	if ( $_POST['StoreNewTees'] )
	{
	
		//
		//  FORM VALIDATION
		//
		if ( !IsTeeValid($_POST['Tees']) )
		{
			DisplayCommonFooter();
			return;
		}
		if ( !IsSlopeRatingValid($_POST['SlopeRating']) )
		{
			DisplayCommonFooter();
			return;
		}
		if ( !IsCourseRatingValid($_POST['CourseRating']) )
		{
			DisplayCommonFooter();
			return;
		}
		if ( !IsCourseRatingLessThanSlope($_POST['CourseRating'], $_POST['SlopeRating']) )
		{
			DisplayCommonFooter();
			return;
		}
        if ( !IsSlopeRatingFBValid($_POST['SlopeRatingF']) )
		{
			DisplayCommonFooter();
			return;
		}
		if ( !IsCourseRatingFBValid($_POST['CourseRatingF']) )
		{
			DisplayCommonFooter();
			return;
		}
        if ( !IsSlopeRatingFBValid($_POST['SlopeRatingB']) )
		{
			DisplayCommonFooter();
			return;
		}
		if ( !IsCourseRatingFBValid($_POST['CourseRatingB']) )
		{
			DisplayCommonFooter();
			return;
		}
        if ($_POST['CourseRatingF'] && $_POST['SlopeRatingF'])
        {
            if ( !IsCourseRatingLessThanSlope($_POST['CourseRatingF'], $_POST['SlopeRatingF']) )
    		{
    			DisplayCommonFooter();
    			return;
    		}
        }
        if ($_POST['CourseRatingB'] && $_POST['SlopeRatingB'])
        {
            if ( !IsCourseRatingLessThanSlope($_POST['CourseRatingB'], $_POST['SlopeRatingB']) )
    		{
    			DisplayCommonFooter();
    			return;
    		}
        }
		
		// holes 1 - 18 are required and must be valid.	
		for ($i = 1; $i < 10; $i++)
		{
			$postVarName = "par$i";
			$fieldName = "Hole $i par value";
			if ( !IsRequiredFieldPresent($fieldName, $_POST[$postVarName]) || !IsParValueForHoleValid($fieldName, $_POST[$postVarName]) )
            {
                DisplayCommonFooter();
				return;
            }
		}
	
		// Connect to the db.
		ConnectToDB();
	
		
		$sql = "insert into tee_tbl (courseid, name, slope, rating, par1, par2, par3, par4, par5, par6, par7, par8, par9) values (";
		$sql .= $_POST['courseid'];
		$sql .= ", '";
		$sql .= $_POST['Tees'];
		$sql .= "', ";
		$sql .= $_POST['SlopeRating'];
		$sql .= ", ";
		$sql .= $_POST['CourseRating'];
		$sql .= ", ";
		$sql .= $_POST['par1'];
		$sql .= ", ";
		$sql .= $_POST['par2'];
		$sql .= ", ";
		$sql .= $_POST['par3'];
		$sql .= ", ";
		$sql .= $_POST['par4'];
		$sql .= ", ";
		$sql .= $_POST['par5'];
		$sql .= ", ";
		$sql .= $_POST['par6'];
		$sql .= ", ";
		$sql .= $_POST['par7'];
		$sql .= ", ";
		$sql .= $_POST['par8'];
		$sql .= ", ";
		$sql .= $_POST['par9'];
		$sql .= ")";
		//printf("%s", $sql);
		mysql_query($sql) or die("Could not add new tees: " . mysql_error());
		
	
		//printf("Tee successfully added.");

		?>
		<meta http-equiv="Refresh" content="0; URL=./courseadmin.php?ShowDetails=1&CourseID=<?printf("%s", $_POST['courseid']);?>">
		<?
	}
	else
	{
		DisTeeEntryForm( $_GET['courseid'], $_SERVER['PHP_SELF'] );
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
