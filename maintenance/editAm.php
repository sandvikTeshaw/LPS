<?
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			editAM.php<br>
 * Development Reference:	DI868<br>
 * Description:				editAM.php allows system administrators to maintain account managers.<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY			    COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0359	  TS	  11/10/2011  Remove back-ups and director           
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
<title>Aftermarket Information Center</title>

<style type="text/css">
<!--
@import url(../copysource/styles.css);
-->
</style>
<script language="JavaScript" type="text/javascript"> 
<!--
function closeW()
{
   window.opener='X';
   window.open('','_parent','');
   window.close();
}
// -->

</script>
</head>

<body onunload="opener.location=('../maintenanceNotification.php')">
<?
include_once '../copysource/header.php';

if ($action == "") {
	
	$sql = "SELECT COUN13, CUSN13, LOTS13, ACTM13, OPMG13, DESC13 FROM CIL13J01 WHERE COUN13='$country'";
	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );
	
	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );
	$userArray = get_user_list ();
	
	?>
	<center>
<form method='post' action='<?
	echo $PHP_SELF;
	?>'>
<table width=60% cellpadding=0 cellspacing=0>
	<?
	echo trim ( "<TR><TD colspan='2' class='titleBig'>Account Manger Maintenance</TD></TR>" );
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	
	while ( ($row = odbc_fetch_array ( $res )) != false ) {
		echo "<TR>";
		echo "<TD class='boldMed'>Country:</TD>";
		echo "<TD><input type='text' name=countryName value='" . trim ( $row ['DESC13'] ) . "' size=75></TD>";
		echo "<TR>";
		echo "<TR>";
		echo "<TD class='boldMed'>Customer #:</TD>";
		echo "<TD><input type='text' name='customerNumber' value='" . trim ( $row ['CUSN13'] ) . "'></TD>";
		echo "<TR>";
		echo "<TR>";
		echo "<TD class='boldMed'>Lotus Notes Company:</TD>";
		echo "<TD><input type='text' name='lotusNumber' value='" . trim ( $row ['LOTS13'] ) . "'></TD>";
		echo "<TR>";
		echo "<TR>";
		echo "<TD class='boldMed'>Account Manager:</TD>";
		echo "<td><select name='accountManager'>";
		show_user_list ( $userArray, $row ['ACTM13'] );
		echo "</select></td>";
		echo "</TR>";
		echo "<TR>";
		echo "<TD class='boldMed'>Operations Manager:</TD>";
		echo "<td><select name='opsManager'>";
		show_user_list ( $userArray, $row ['OPMG13'] );
		echo "</select></td>";
		echo "</tr>";
	}
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD><input type='submit' value='Save'></TD></TR>";
	echo "<input type='hidden' name='action' value='save'>";
	echo "<input type='hidden' name='country' value='" . $row ['COUN13'] . "'>";
	?>
	</table>
</form>
</center>
	<?

} elseif ($action == "delete") {
	
	?>
	<center>
<form method='post' action='editAm.php'>
<table width=20% cellpadding=0 cellspacing=0>
	<?
	echo trim ( "<TR><TD colspan='2' class='titleBig'>Account Manger Maintenance</TD></TR>" );
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	
	$sql = "SELECT COUN13, CUSN13, LOTS13, ACTM13, OPMG13,  DESC13, BACM13, BOPM13, DIRC13, BDIR13 FROM CIL13J01 WHERE COUN13='$country'";
	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );
	while ( ($row = odbc_fetch_array ( $res )) != false ) {
		
		echo "<TR><TD><b>Delete: </b>" . $row ['DESC13'] . "</TD></TR>";
		echo "<TR><TD>";
		echo "<select name='delSelection'>";
		echo "<option SELECTED value='N'>No</option>";
		echo "<option value='Y'>Yes</option>";
		echo "</select>";
		echo "</TD></TR>";
		echo "<TR><TD>&nbsp</TD></TR>";
		echo "<TR><TD><input type='submit' value='Continue'><TD><TR>";
		echo "<input type='hidden' name='countryCode' value='" . $row ['COUN13'] . "'>";
		echo "<input type='hidden' name='action' value='deleteContinue'>";
	}
	?>
	</table>
	</form>
<?
} elseif ($action == "deleteContinue") {
	
	if ($delSelection == "Y") {
		$delSql = "DELETE FROM CIL13 WHERE COUN13='$countryCode'";
		$delRes = odbc_prepare ( $conn, $delSql );
		odbc_execute ( $delRes );
		
		?>
		<center><br></br>
<br></br>
<b>Country has been deleted</b><br></br>
<br></br>
		<?
	} else {
		?>
		<center><br></br>
<br></br>
<b>Country has <i>not</i> been deleted</b><br></br>
<br></br>
		<?
	}
	?>
	<input type='button' onclick="closeW()" value="Close Window"></input></center>
	<?
} else {
	
	$updateSql = "UPDATE CIL13 SET CUSN13='" . trim ( $customerNumber ) . "', LOTS13='$lotusNumber', ACTM13=$accountManager, OPMG13=$opsManager,";
	$updateSql .= "BACM13=0, BOPM13=0, DIRC13=0, BDIR13=0, DESC13='$countryName' WHERE COUN13='$country'";
	
	//echo $updateSql;
	$res = odbc_prepare ( $conn, $updateSql );
	odbc_execute ( $res );
	
	?>
	<center><br></br>
<br></br>
<b>Account have been updated</b><br></br>
<br></br>
<input type='button' onclick="closeW()" value="Close Window"></input></center>
</center>
</center>
</body>
</html>
<?php 

}
