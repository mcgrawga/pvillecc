<!--
var xmlHttp
function ucwords( str ) {
    return str.replace(/^(.)|\s(.)/g, function ( $1 ) { return $1.toUpperCase ( ); } );
}
function GetXmlHttpObject()
{
	var xmlHttp=null;
	
	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
		// Internet Explorer
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

function StateCourseSearch(StateID)
{ 
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request");
	 	return;
	} 
	var url="courseserver.php";
	url=url+"?id="+ StateID;
	//alert(url);
	xmlHttp.onreadystatechange=StateCourseSearchCBK;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}


function StateCourseSearchCBK()  // this is a call back function
{
	if (xmlHttp.readyState==4)
	{ 
		//alert("Back from Server");
        //alert(xmlHttp.responseText);
		var xmlDoc=xmlHttp.responseXML.documentElement;
		var CourseArray = xmlDoc.getElementsByTagName("row");
		
		//alert(CourseArray.length);
		var html = "<br><br>";
		if (CourseArray.length <= 0)
		{
			html += "No Results Found.";
		}
		else
		{
			html += "<form name=\"courseform\" action=\"searchcourse.php\" method=\"POST\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"sortable\" id=\"sortable_table\"><tr class=\"ScoreHistoryTDHeader\"><td></td><td><b>Course</b></td><td><b>City</b></td></tr>";
		}
		
		for (i = 0; i < CourseArray.length; i++)
		{
			var classname = (i % 2) ? 'CourseList2' : 'CourseList1';
			var CourseID = CourseArray[i].getElementsByTagName("id")[0].childNodes[0].nodeValue;
			var CourseName = CourseArray[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
            var	CourseCity = ""
            if (CourseArray[i].getElementsByTagName("city")[0].childNodes.length > 0)
                var	CourseCity = CourseArray[i].getElementsByTagName("city")[0].childNodes[0].nodeValue;
			//var CourseNameArray = CourseName.split(" ");
			
			html += "<tr class=\"" + classname + "\">";
			html += "<td><input type=\"checkbox\" name=\"coursecbx[]\" value=\"" + CourseID + "\"></td><td>" + ucwords(CourseName.toLowerCase()) + "</td><td>" + ucwords(CourseCity.toLowerCase()) + "</td></tr>";
			if ( i == (CourseArray.length - 1))
            {
				html += "</table><br><input type=\"submit\" name=\"AddCourseToProfile\" value=\"Add To My Account\"></form>";
            }
		}
		//alert("got here 2");
		document.getElementById("search_results").innerHTML = html;
	}
}
/*
function CopyCourse()
{ 
	alert("CopyCourse()");
	
	// THIS IS TO COMPENSATE FOR A WEIRD BUG.  IF YOU ONLY HAVE ONE CHECK BOX
	// IT IS NOT REPRESENTED AS AN ARRAY.  SO CHECKING IT'S LENGTH IS UNDEFINED.
	if (document.courseform.coursecbx.length)
	{
		for (i=0;i<document.courseform.coursecbx.length;i++)
		{
			if ( document.courseform.coursecbx[i].checked==true )
				alert (document.courseform.coursecbx[i].value);
		}
	}
	else
	{
		if ( document.courseform.coursecbx.checked==true )
				alert (document.courseform.coursecbx.value);
	}		
}
function CopyCourseCBK()  // this is a call back function
{
	if (xmlHttp.readyState==4)
	{ 
		//alert("Back from Server");
		var xmlDoc=xmlHttp.responseXML.documentElement;
		var CourseArray = xmlDoc.getElementsByTagName("row");
		
		//alert(CourseArray.length);
		var html = "<br><br>";
		if (CourseArray.length <= 0)
		{
			html += "No Results Found.";
		}
		else
		{
			html += "<table>";
		}
			
		for (i = 0; i < CourseArray.length; i++)
		{
			var CourseID = CourseArray[i].getElementsByTagName("course_id")[0].childNodes[0].nodeValue;
			var CourseName = CourseArray[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
			var CourseCity = CourseArray[i].getElementsByTagName("city")[0].childNodes[0].nodeValue;
			html += "<tr>";
			//html += "<td><div id=\"" + CourseID + "\"><a href=\"#\" onclick=\"ShowCourseDetail(" + CourseID + ", '" + CourseName + "', 'COLLAPSED')\">";
			html += "<td>" + CourseName + "</td><td>" + CourseCity + "</td><td><a href=\"#\" onclick=\"CopyCourse(" + CourseID + ")\">   Add to my account</a></td><tr>";
		}
		//alert("got here 2");
		html += "</table>";
		document.getElementById("search_results").innerHTML = html;
	}
}
function ShowCourseDetail(TagID, CourseName, ExpandStatus)
{ 
	//alert(CourseName);
	//alert(ExpandStatus);
	if (ExpandStatus == 'COLLAPSED')
	{
		html = "<a href=\"#\" onclick=\"ShowCourseDetail(" + TagID + ", '" + CourseName + "', 'EXPANDED')\">";
		html +=  CourseName;
		html += "</a><br>Des Moines, Iowa<br><a href=\"add to profile\">Copy Course To My Account</a><br><br>";
	}
	else
	{
		html = "<a href=\"#\" onclick=\"ShowCourseDetail(" + TagID + ", '" + CourseName + "', 'COLLAPSED')\">";
		html +=  CourseName;
		html += "</a>";
	}
	document.getElementById(TagID).innerHTML = html;
}
*/






//-->