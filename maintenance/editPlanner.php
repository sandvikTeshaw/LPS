<?
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			editPlanner.php<br>
 * Development Reference:	DI868<br>
 * Description:				editPlanner.php change details of Outbound planner.<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY			    COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  GLBAU-8595	  IS	  24/10/2015  Edit outbound planner           
 */
/**
 */
global $conn, $action, $country, $delSelection, $countryCode, $accountManager, $lotusNumber, $customerNumber, $opsManager, $opsManager, $bAccount, $bDirector, $bExpedite, $bManger, $countryName, $director, $bOps;

include_once '../copysource/config.php';
include '../copysource/functions.php';
include '../../common/copysource/global_functions.php';

if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(../copysource/styles.css);
-->
</style>
<script language="JavaScript" type="text/javascript"> 
//<!--
function closeW()
{
   window.opener='X';
   window.open('','_parent','');
   window.close();
}
//-->

</script>
</head>

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=OBP')">
<? 
include_once '../copysource/header.php';

//if ($action == "") {
	
	$sql = "SELECT ID38, SLMN38, ACTV38, PLAN38, OPMG38 FROM CIL38 WHERE SLMN38='$id'";
	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );
	//$row = odbc_fetch_array ( $res );
	//print_r($row);
	
	$userArray = get_user_list ();
	
//}	
    if( $_REQUEST['action'] != "save" ){
        	?>
        	<center>
        <form method='post' action='<?
        	echo $PHP_SELF;
        	?>'>
        <table width=60% cellpadding=0 cellspacing=0>
        	<?
        	echo trim ( "<TR><TD colspan='2' class='titleBig'>Outbound Planner Maintenance</TD></TR>" );
        	echo "<TR><TD>&nbsp</TD></TR>";
        	echo "<TR><TD>&nbsp</TD></TR>";
        	while ( ($row = odbc_fetch_array ( $res )) != false ) {
        		echo "<TR>";
        		echo "<TD class='boldMed'>Planner Code:</TD>";
        		echo "<TD>" .  trim ( $row ['SLMN38'] ) . "</TD>";
        		echo "</TR>";
        		echo "<TR>";
        		echo "<TD class='boldMed'>Outbound Planner:</TD>";
        		echo "<td><select name='accountManager'>";
        		show_user_list ( $userArray, trim($row ['PLAN38']) );
        		echo "</select></td>";
        		echo "</TR>";
        		echo "<TR>";
        		echo "<TD class='boldMed'>Operations Manager:</TD>";
        		echo "<td><select name='opsManager'>";
        		show_user_list ( $userArray, trim($row ['OPMG38']) );
        		echo "</select></td>";
        		echo "</tr>";
        		echo "<TR>";
        		echo "<TD class='boldMed'>Active:</TD>";
        		echo "<td><select name='active'>";
        		echo "<option val='Yes'>Yes</option>";
        		echo "<option val='No'>No</option>";
        		echo "</select></td>";
        		echo "</tr>";
        		echo "<input type='hidden' name='id38' value='" . trim ( $row ['ID38'] )  . "'>";
        	}
        	echo "<TR><TD>&nbsp</TD></TR>";
        	echo "<TR><TD><input type='submit' value='Continue'></TD></TR>";
        	echo "<input type='hidden' name='action' value='save'>";
        	echo "<input type='hidden' name='plannerID' value='" . $id . "'>";
        	?>
        	</table>
        </form>
        </center>
 <?php 
    }else{
        
        if( !$_REQUEST['accountManager'] ){
            $acmId = 0;
            
        }else{
            $acmId = $_REQUEST['accountManager'];
        }
        
        if( !$_REQUEST['opsManager'] ){
            $opmgId = 0;
        
        }else{
            $opmgId = $_REQUEST['opsManager'];
        }
        
        
        
        $updateSql = "UPDATE CIL38 set PLAN38=" . $_REQUEST['accountManager'] . " , OPMG38 = " . $_REQUEST['opsManager'] . " WHERE ID38 = " . $_REQUEST['id38'];
        
        $updateRes = odbc_prepare ( $conn, $updateSql );
        odbc_execute ( $updateRes );
        
       // echo $updateSql . "<hr>";
        
 ?>

	<center><br></br>
<br></br>
<b>Outbound Planner details updated.</b><br></br>
<br></br>
<input type='button' onclick="closeW()" value="Close Window"></input></center>
</center>

<?php 
   }
 ?>
 </center>
</body>
</html>
