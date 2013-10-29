<!--
s1 = "";
s2 = "";


function IsNumeric(sText)
{
   var ValidChars = "0123456789";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
   
}


function IsParValue(sText)
{
   var ValidChars = "345";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
   
}


function IsPuttValue(sText)
{
   var ValidChars = "0123456789";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
}

function HandleFocus(theFormElement) 
{
	s1 = theFormElement.value;
}

function is18Hole() 
{
	ind = 0;
	for ( i = 0; i < window.document.forms[0].length; i++)
	{
		if ( window.document.forms[0].elements[i].name == "par10")
		{
			ind = 1;
			break;
		}
	}
	return ind;
}


function calcPuttTotals()
{	
	var totalOut = 0;
	var totalIn = 0;
	var total = 0;
	if (!isNaN( parseInt(document.scorecard.putt1.value) ))
		totalOut += parseInt(document.scorecard.putt1.value);
	if (!isNaN( parseInt(document.scorecard.putt2.value) ))
		totalOut += parseInt(document.scorecard.putt2.value);
	if (!isNaN( parseInt(document.scorecard.putt3.value) ))
		totalOut += parseInt(document.scorecard.putt3.value);
	if (!isNaN( parseInt(document.scorecard.putt4.value) ))
		totalOut += parseInt(document.scorecard.putt4.value);
	if (!isNaN( parseInt(document.scorecard.putt5.value) ))
		totalOut += parseInt(document.scorecard.putt5.value);
	if (!isNaN( parseInt(document.scorecard.putt6.value) ))
		totalOut += parseInt(document.scorecard.putt6.value);
	if (!isNaN( parseInt(document.scorecard.putt7.value) ))
		totalOut += parseInt(document.scorecard.putt7.value);
	if (!isNaN( parseInt(document.scorecard.putt8.value) ))
		totalOut += parseInt(document.scorecard.putt8.value);
	if (!isNaN( parseInt(document.scorecard.putt9.value) ))
		totalOut += parseInt(document.scorecard.putt9.value);
	document.scorecard.puttout.value = totalOut;
	
	if ( is18Hole() )
	{
		if (!isNaN( parseInt(document.scorecard.putt10.value) ))
			totalIn += parseInt(document.scorecard.putt10.value);
		if (!isNaN( parseInt(document.scorecard.putt11.value) ))
			totalIn += parseInt(document.scorecard.putt11.value);
		if (!isNaN( parseInt(document.scorecard.putt12.value) ))
			totalIn += parseInt(document.scorecard.putt12.value);
		if (!isNaN( parseInt(document.scorecard.putt13.value) ))
			totalIn += parseInt(document.scorecard.putt13.value);
		if (!isNaN( parseInt(document.scorecard.putt14.value) ))
			totalIn += parseInt(document.scorecard.putt14.value);
		if (!isNaN( parseInt(document.scorecard.putt15.value) ))
			totalIn += parseInt(document.scorecard.putt15.value);
		if (!isNaN( parseInt(document.scorecard.putt16.value) ))
			totalIn += parseInt(document.scorecard.putt16.value);
		if (!isNaN( parseInt(document.scorecard.putt17.value) ))
			totalIn += parseInt(document.scorecard.putt17.value);
		if (!isNaN( parseInt(document.scorecard.putt18.value) ))
			totalIn += parseInt(document.scorecard.putt18.value);
		document.scorecard.puttinn.value = totalIn;
		document.scorecard.putttotal.value = (totalIn + totalOut);
	}
	
}


function calcTotals()
{	
	var totalOut = 0;
	var totalIn = 0;
	var total = 0;
	if (!isNaN( parseInt(document.scorecard.par1.value) ))
		totalOut += parseInt(document.scorecard.par1.value);
	if (!isNaN( parseInt(document.scorecard.par2.value) ))
		totalOut += parseInt(document.scorecard.par2.value);
	if (!isNaN( parseInt(document.scorecard.par3.value) ))
		totalOut += parseInt(document.scorecard.par3.value);
	if (!isNaN( parseInt(document.scorecard.par4.value) ))
		totalOut += parseInt(document.scorecard.par4.value);
	if (!isNaN( parseInt(document.scorecard.par5.value) ))
		totalOut += parseInt(document.scorecard.par5.value);
	if (!isNaN( parseInt(document.scorecard.par6.value) ))
		totalOut += parseInt(document.scorecard.par6.value);
	if (!isNaN( parseInt(document.scorecard.par7.value) ))
		totalOut += parseInt(document.scorecard.par7.value);
	if (!isNaN( parseInt(document.scorecard.par8.value) ))
		totalOut += parseInt(document.scorecard.par8.value);
	if (!isNaN( parseInt(document.scorecard.par9.value) ))
		totalOut += parseInt(document.scorecard.par9.value);
	document.scorecard.out.value = totalOut;
	
	if ( is18Hole() )
	{
		if (!isNaN( parseInt(document.scorecard.par10.value) ))
			totalIn += parseInt(document.scorecard.par10.value);
		if (!isNaN( parseInt(document.scorecard.par11.value) ))
			totalIn += parseInt(document.scorecard.par11.value);
		if (!isNaN( parseInt(document.scorecard.par12.value) ))
			totalIn += parseInt(document.scorecard.par12.value);
		if (!isNaN( parseInt(document.scorecard.par13.value) ))
			totalIn += parseInt(document.scorecard.par13.value);
		if (!isNaN( parseInt(document.scorecard.par14.value) ))
			totalIn += parseInt(document.scorecard.par14.value);
		if (!isNaN( parseInt(document.scorecard.par15.value) ))
			totalIn += parseInt(document.scorecard.par15.value);
		if (!isNaN( parseInt(document.scorecard.par16.value) ))
			totalIn += parseInt(document.scorecard.par16.value);
		if (!isNaN( parseInt(document.scorecard.par17.value) ))
			totalIn += parseInt(document.scorecard.par17.value);
		if (!isNaN( parseInt(document.scorecard.par18.value) ))
			totalIn += parseInt(document.scorecard.par18.value);
		document.scorecard.inn.value = totalIn;
		document.scorecard.total.value = (totalIn + totalOut);
	}
	
}


function HandleKeyUp(theFormElement, nextFormElement) 
{
	s2 = theFormElement.value;
	if ( s1 != s2 && s2 != "" )
	{
		calcTotals();
		if ( !IsNumeric(s2) )	
			theFormElement.value = s1;
		else
			nextFormElement.focus();
	}	
} 

function HandlePuttKeyUp(theFormElement, nextFormElement) 
{
	s2 = theFormElement.value;
	if ( s1 != s2 && s2 != "" )
	{
		calcPuttTotals();
		if ( !IsPuttValue(s2) )	
			theFormElement.value = s1;
		else
		{
			if (nextFormElement)
				nextFormElement.focus();
			else
				document.scorecard.greensInReg.focus()
		}
	}
} 

function HandleCourseKeyUp(theFormElement, submitButton) 
{
	s2 = theFormElement.value;
	if ( s1 != s2 )
	{
		calcTotals();
		if ( !IsParValue(s2) )	// not numeric
			theFormElement.value = s1;
		else
		{
			if (s2 > 1)
			{
				for ( i = 0; i < window.document.forms[0].length; i++)
				{
					if ( window.document.forms[0].elements[i] == theFormElement)
					{
						if ( window.document.forms[0].elements[i + 1] == document.scorecard.out )
						{
							if ( is18Hole() )
								document.scorecard.par10.focus()
							else
								submitButton.focus()
								
						}
						else if ( window.document.forms[0].elements[i + 1] == document.scorecard.inn )
						{
							submitButton.focus()
						}
						else
						{
							if ( (i+1) == window.document.forms[0].elements.length )
								window.document.forms[0].elements[0].focus();
							else
								window.document.forms[0].elements[i + 1].focus();
						}
						break;
					}
				}
			}
		}
	}
} 



//
//  EVERYTHING BELOW HERE IS AJAX STUFF
//


var xmlHttp

function GetTees(TeeID, CallBackFunction)
{ 
	//alert(TeeID);
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request");
	 	return;
	} 
	//var url="http://localhost/statgolf/gettees.php";
	var url="gettees.php";
	url=url+"?id="+ TeeID;
	//xmlHttp.onreadystatechange=PopulateTees;
	xmlHttp.onreadystatechange=CallBackFunction;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function GetTeesForCourse(CourseID, CallBackFunction)
{
	//alert(TeeID);
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request");
	 	return;
	} 
	//var url="http://localhost/statgolf/getteesforcourse.php";
	var url="getteesforcourse.php";
	url=url+"?id="+ CourseID;
	xmlHttp.onreadystatechange=CallBackFunction;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function PopulateEditScorecard()
{
	if (xmlHttp.readyState==4)
	{ 
		var xmlDoc=xmlHttp.responseXML.documentElement;
		var TeeArray = xmlDoc.getElementsByTagName("row");
		
		//
		//  REMOVE ALL THE OPTIONS FROM THE TEE DROP DOWN LIST
		//
		while(document.scorecard.Tees.options.length > 0)
			document.scorecard.Tees.remove(0);

			
		//
		//  ADD ALL THE TEES FOR THE SELECTED COURSE TO THE TEE DROP DOWN LIST
		//	
		for (i = 0; i < TeeArray.length; i++)
		{
			var optn = document.createElement("OPTION");
			optn.text = TeeArray[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
			optn.value = TeeArray[i].getElementsByTagName("id")[0].childNodes[0].nodeValue;
			document.scorecard.Tees.options.add(optn);
			//alert(TeeArray[i].getElementsByTagName("id")[0].childNodes[0].nodeValue);
			//alert(TeeArray[i].getElementsByTagName("name")[0].childNodes[0].nodeValue);
		}
		
		//
		//  POPULATE PAR VALUES FOR THE DEFAULT TEE FOR THE COURSE
		//	
		GetTees(TeeArray[0].getElementsByTagName("id")[0].childNodes[0].nodeValue, PopulateScorecardParValues);
		
	}
}


function PopulateScorecardParValues() 
{ 
	if (xmlHttp.readyState==4)
	{ 
		//document.scorecard.ParHole9.value = xmlHttp.responseText;
		//document.scorecard.ParHole9.innerHTML = "6";
		
		
		var xmlDoc=xmlHttp.responseXML.documentElement;
		FrontNineTotal = parseInt(0);
		document.getElementById("ParHole1").innerHTML = xmlDoc.getElementsByTagName("par1")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par1")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole2").innerHTML = xmlDoc.getElementsByTagName("par2")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par2")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole3").innerHTML = xmlDoc.getElementsByTagName("par3")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par3")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole4").innerHTML = xmlDoc.getElementsByTagName("par4")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par4")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole5").innerHTML = xmlDoc.getElementsByTagName("par5")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par5")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole6").innerHTML = xmlDoc.getElementsByTagName("par6")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par6")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole7").innerHTML = xmlDoc.getElementsByTagName("par7")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par7")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole8").innerHTML = xmlDoc.getElementsByTagName("par8")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par8")[0].childNodes[0].nodeValue);
		document.getElementById("ParHole9").innerHTML = xmlDoc.getElementsByTagName("par9")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par9")[0].childNodes[0].nodeValue);
		document.getElementById("Front9Total").innerHTML = FrontNineTotal;
		
		var x=xmlDoc.getElementsByTagName("par10");
		//alert(x.length);
		if (x.length == 0)
		{
			//document.scorecard.Back9HTML.value = document.getElementById("secondnine").innerHTML;
			//alert(document.scorecard.Back9HTML.value);
			document.getElementById("secondnine").innerHTML = "";
		} 
		else
		{
			//alert(document.scorecard.Back9HTML.value);
			if ( document.scorecard.Back9HTML.value != "" )
			{
			//	alert("Got Here");
				//document.getElementById("secondnine").innerHTML = document.scorecard.Back9HTML.value;
			}
			//document.getElementById("secondnine").innerHTML = document.scorecard.Back9HTML.value;

			BackNineTotal = parseInt(0);
			document.getElementById("ParHole10").innerHTML = xmlDoc.getElementsByTagName("par10")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par10")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole11").innerHTML = xmlDoc.getElementsByTagName("par11")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par11")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole12").innerHTML = xmlDoc.getElementsByTagName("par12")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par12")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole13").innerHTML = xmlDoc.getElementsByTagName("par13")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par13")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole14").innerHTML = xmlDoc.getElementsByTagName("par14")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par14")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole15").innerHTML = xmlDoc.getElementsByTagName("par15")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par15")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole16").innerHTML = xmlDoc.getElementsByTagName("par16")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par16")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole17").innerHTML = xmlDoc.getElementsByTagName("par17")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par17")[0].childNodes[0].nodeValue);
			document.getElementById("ParHole18").innerHTML = xmlDoc.getElementsByTagName("par18")[0].childNodes[0].nodeValue;
			BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par18")[0].childNodes[0].nodeValue);
			document.getElementById("Back9Total").innerHTML = BackNineTotal;
		}
		
	} 
}

function PopulateCourseEditParValues() 
{ 
	if (xmlHttp.readyState==4)
	{ 
		
		var xmlDoc=xmlHttp.responseXML.documentElement;
		FrontNineTotal = parseInt(0);
		
		document.scorecard.SlopeRating.value = xmlDoc.getElementsByTagName("slope")[0].childNodes[0].nodeValue;
		document.scorecard.CourseRating.value = xmlDoc.getElementsByTagName("rating")[0].childNodes[0].nodeValue;
		
		document.scorecard.par1.value = xmlDoc.getElementsByTagName("par1")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par1")[0].childNodes[0].nodeValue);
		document.scorecard.par2.value = xmlDoc.getElementsByTagName("par2")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par2")[0].childNodes[0].nodeValue);
		document.scorecard.par3.value = xmlDoc.getElementsByTagName("par3")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par3")[0].childNodes[0].nodeValue);
		document.scorecard.par4.value = xmlDoc.getElementsByTagName("par4")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par4")[0].childNodes[0].nodeValue);
		document.scorecard.par5.value = xmlDoc.getElementsByTagName("par5")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par5")[0].childNodes[0].nodeValue);
		document.scorecard.par6.value = xmlDoc.getElementsByTagName("par6")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par6")[0].childNodes[0].nodeValue);
		document.scorecard.par7.value = xmlDoc.getElementsByTagName("par7")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par7")[0].childNodes[0].nodeValue);
		document.scorecard.par8.value = xmlDoc.getElementsByTagName("par8")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par8")[0].childNodes[0].nodeValue);
		document.scorecard.par9.value = xmlDoc.getElementsByTagName("par9")[0].childNodes[0].nodeValue;
		FrontNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par9")[0].childNodes[0].nodeValue);
		document.scorecard.out.value = FrontNineTotal;
		
		BackNineTotal = parseInt(0);
		document.scorecard.par10.value = xmlDoc.getElementsByTagName("par10")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par10")[0].childNodes[0].nodeValue);
		document.scorecard.par11.value = xmlDoc.getElementsByTagName("par11")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par11")[0].childNodes[0].nodeValue);
		document.scorecard.par12.value = xmlDoc.getElementsByTagName("par12")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par12")[0].childNodes[0].nodeValue);
		document.scorecard.par13.value = xmlDoc.getElementsByTagName("par13")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par13")[0].childNodes[0].nodeValue);
		document.scorecard.par14.value = xmlDoc.getElementsByTagName("par14")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par14")[0].childNodes[0].nodeValue);
		document.scorecard.par15.value = xmlDoc.getElementsByTagName("par15")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par15")[0].childNodes[0].nodeValue);
		document.scorecard.par16.value = xmlDoc.getElementsByTagName("par16")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par16")[0].childNodes[0].nodeValue);
		document.scorecard.par17.value = xmlDoc.getElementsByTagName("par17")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par17")[0].childNodes[0].nodeValue);
		document.scorecard.par18.value = xmlDoc.getElementsByTagName("par18")[0].childNodes[0].nodeValue;
		BackNineTotal +=  parseInt(xmlDoc.getElementsByTagName("par18")[0].childNodes[0].nodeValue);
		document.scorecard.out.value = BackNineTotal;
		document.scorecard.total.value = BackNineTotal + FrontNineTotal;
	} 
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







//-->