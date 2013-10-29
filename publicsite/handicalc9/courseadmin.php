<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
	DisplayCommonHeader();
    printf("<br><h3>Golf Courses</h3>");
	if ( $_GET["ShowDetails"])
	{
		if ( $_GET["DeleteTee"])
			DeleteTee($_GET["DeleteTee"]);
		DisplayScorecard( "EDIT_COURSE", $_GET['CourseID'], $_SERVER['PHP_SELF'] );
	}
	else if ( $_GET["EditTee"])
	{
		DisTees( $_GET['EditTee'], $_SERVER['PHP_SELF'] );
	}
	else if ( $_POST["SaveChangedTees"])
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

		//if ( !IsRequiredFieldPresent("Hole 1 par value", $_POST['par1']) || !IsParValueForHoleValid("Hole 1 par value", $_POST['par1']) )
		//	return;
		
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
	
	
	
	
	
		$sql = "update tee_tbl set name = '";
		$sql .= $_POST["Tees"];
		$sql .= "', slope = ";
		$sql .= $_POST["SlopeRating"];
		$sql .= ", rating = ";
		$sql .= $_POST["CourseRating"];
		$sql .= ", par1 = ";
		$sql .= $_POST["par1"];
		$sql .= ", par2 = ";
		$sql .= $_POST["par2"];
		$sql .= ", par3 = ";
		$sql .= $_POST["par3"];
		$sql .= ", par4 = ";
		$sql .= $_POST["par4"];
		$sql .= ", par5 = ";
		$sql .= $_POST["par5"];
		$sql .= ", par6 = ";
		$sql .= $_POST["par6"];
		$sql .= ", par7 = ";
		$sql .= $_POST["par7"];
		$sql .= ", par8 = ";
		$sql .= $_POST["par8"];
		$sql .= ", par9 = ";
		$sql .= $_POST["par9"];
		
        
        
		$sql .= " where id = ";
		$sql .= $_POST["teeid"];
		//printf("%s", $sql);
		mysql_query($sql) or die("Could not save the changes to the tees: " . mysql_error());
		//DisTees( $_POST["teeid"], $_SERVER['PHP_SELF'] );
		DisplayScorecard( "EDIT_COURSE", $_POST["courseid"], $_SERVER['PHP_SELF'] );
		//printf("Tee Changes Saved.");
	}
	else if ( $_POST["SaveChangedCourse"])
	{
		//
		//  FORM VALIDATION
		//
		if ( !IsCourseNameValid($_POST['CourseName']) )
			return;
		if ( !IsCityValid($_POST['City']) )
			return;
		if ( !IsStateValid($_POST['State']) )
			return;
			
			
			
			
		$sql = "update course_tbl set name = '";
		$sql .= $_POST["CourseName"];
		$sql .= "', city = '";
		$sql .= $_POST["City"];
		$sql .= "', state = ";
		$sql .= $_POST["State"];
		$sql .= " where id = ";
		$sql .= $_POST["courseid"];
		//printf("%s", $sql);
		mysql_query($sql) or die("Could not save the course changes: " . mysql_error());
		//DisplayScorecard( "EDIT_COURSE", $_POST["courseid"], $_SERVER['PHP_SELF'] );
		//printf("Course Changes Saved.");
		ListCourses();
	}
	else
	{
		
		if ( $_GET["DeleteCourse"])
			DeleteCourse($_GET["courseid"]);
		ListCourses();
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
