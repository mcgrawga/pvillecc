<?php 
session_start(); 
header("Cache-control: private"); // IE 6 Fix. 

include 'functions.php';

function DisplayPage()
{
    ConnectToDB();
	DisplayCommonHeader();
	printf("<h3>USGA Handicap Index Card</h3>");
    GetOfficialHandicapIndex($HandicapIndex, $HandicapIndexLabel);
    if ($HandicapIndex < 0)
    {
        $HandicapIndex *= -1;
        $HandicapIndex = "+ " . $HandicapIndex;
    }
	?>

	<table border="0" width="100%" cellspacing="1">
        <tr>
          <td width="37%">
<p align="left">
              <table cellSpacing="0" cellPadding="0" width="315" align="left" border="0">
					
                <tbody>
                  <tr>
                    <td width="14" height="0"></td>

                    <td width="217" height="0"></td>
                    <td width="63" height="0"></td>
                    <td width="21" height="0"></td>
                  </tr>
                  <tr>
                    <td colSpan="4"><img border="0" src="/publicsite/media/sgimages/compon3.gif"></td>
                  </tr>
                  <tr>
                    <td width="14" rowSpan="2"><img border="0" src="/publicsite/media/sgimages/compon4.gif"></td>

                    <td width="217" height="23" ><font face="Arial" size="1"><span class="ldata2">Name:</span><span class="ldata">&nbsp;
                      </span></font><b><font face="Arial" size="2"><?printf("%s",$_SESSION['username']);?></font></b></td>
                    <td colSpan="2"><img border="0" src="/publicsite/media/sgimages/compon12.gif"></td>
                  </tr>
                  <tr>
                    <td colSpan="2" height="57">
                      <table cellSpacing="0" cellPadding="0" width="100%" border="0" >
                        <tbody>

                          <tr>
                            <td colSpan="2" height="15"><font face="Arial" size="1"><span class="ldata2">Club:</span><span class="ldata">&nbsp;
                              </span></font><font face="Arial" color="#006EA5" size="2"><b><?printf("%s", GetClubName());?></b></font></td>
                          </tr>

                          
                          <tr>
                            <td colSpan="2" height="15"><font face="Arial" size="1"><span class="ldata2">USGA Handicap Index:</span><span class="ldata">&nbsp;
                              </span></font><font face="Arial" color="#006EA5" size="2"><b><?print("$HandicapIndex $HandicapIndexLabel");?></b></font></td>
                          </tr>
                          
                          <tr>
                            <td colSpan="2" height="15"><font face="Arial" size="1"></font><font face="Arial" size="1"><b><?printf("Effective Date:  %s", date("M d, Y", strtotime(GetLastHandicapRevisionDate())));?></b></font></td>
                          </tr>
                          
                        </tbody>
                      </table>
                    </td>
                    <td width="21"><img border="0" src="/publicsite/media/sgimages/compon6.gif"></td>
                  </tr>
                  <tr>
                    <td colSpan="4"><img border="0" src="/publicsite/media/sgimages/compon11.gif"></td>
                  </tr>

                  <tr>
                    <td width="14"><img border="0" src="/publicsite/media/sgimages/compon8.gif"></td>
                    <td colSpan="2" height="71">
                      <table border="0" width="100%" cellspacing="0" cellpadding="0" >
<?
    $uid .= $_SESSION['userid']; 
    $handiRevisionDate = GetLastHandicapRevisionDate();
	$sql = "select * from tools_officialscore where dateplayed < '$handiRevisionDate' and userid = $uid order by dateplayed desc limit 20";
	
	//printf("%s", $sql);
	$result = mysql_query($sql) or die("Could not get a list of scores: " . mysql_error());
	
    $ScoreArray = array();
    $IDArray = array();
    $TypeArray = array();
	if ( mysql_num_rows($result) != 0 )
	{
        // load up the arrays
        while ($row = mysql_fetch_array($result))
        {
            extract($row);
            $ScoreArray[] = $total;
            $IDArray[] = $id;
            $TypeArray[] = $type;
        }
     }       
  
        
    // Get an array of Official Score IDs used in handicap calculation so we can identify which scores to "asterick" on the card.
    $OfficialScoreIDArray = GetHandiIDs();
    
    for ($i=0; $i < 20; $i++)
    {
        if ($i < count($ScoreArray))
        {
            if ( (($i) % 5) == 0)
                printf("<tr><td width=\"10%%\" bgcolor=\"#FFC2A6\"><p align=\"center\"><font size=\"1\" face=\"Arial\">%s</font></td>", $i+1);
            if (in_array($IDArray[$i], $OfficialScoreIDArray))
                printf("<td width=\"18%%\" bgcolor=\"#FFC2A6\"><p align=\"center\"><font size=\"1\" face=\"Arial\">$ScoreArray[$i] %s*</font></td>", GetScoreType($TypeArray[$i]));
            else
                printf("<td width=\"18%%\" bgcolor=\"#FFC2A6\"><p align=\"center\"><font size=\"1\" face=\"Arial\">$ScoreArray[$i] %s</font></td>", GetScoreType($TypeArray[$i]));
            if ( (($i) % 5) == 4)
                print("<tr>");
        }
        else
        {
            if ( (($i) % 5) == 0)
                printf("<tr><td width=\"10%%\" bgcolor=\"#FFC2A6\"><p align=\"center\"><font size=\"1\" face=\"Arial\">%s</font></td>", $i+1);
            print("<td width=\"18%%\" bgcolor=\"#FFC2A6\"><p align=\"center\"><font size=\"1\" face=\"Arial\">N/A</font></td>");
            if ( (($i) % 5) == 4)
                print("<tr>");
        }
    }
?>
                        
                         

                      </table>
                    </td>
                    <td width="21"><img border="0" src="/publicsite/media/sgimages/compon9.gif"></td>
                  </tr>
                  <tr>
                    <td colSpan="4"><img border="0" src="/publicsite/media/sgimages/compon18.gif"></td>
                  </tr>
                  
                </tbody>
              </table>    
                </tbody>
              </table>   
    
    
    
    
    
    
    
    
    
    
    

	<?
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
