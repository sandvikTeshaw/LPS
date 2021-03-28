<?
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			editAMDelivery.php<br>
 * Development Reference:	DI868<br>
 * Description:				editAMDelivery.php allows system administrators to maintain account managers by delivery sequence.  This is a subset page of editAM.php
 * 							and is access from a hyperlink in editAM.php<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY  				  DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 * 
 */
/**
 */
global $conn, $action, $customerNumber, $country, $deliverySequence, $director, $bDirector, $bOps, $bAccount, $opsManager, $lotusNumber, 
	$CONO, $accountManager;

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
<?if( $action != "" ){ ?>
<body>
<?
}
include_once '../copysource/header.php';
echo "<body>";


if ($action == "") {
?>
<center>
<table width="95%" cellpadding='0' cellspacing='0' >
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
      <tr><td colspan='2' class='titleBig'>Account Manger Site Maintenance - <?echo $customerNumber;?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><input type='button' onclick="closeW()" value="Close Window"/></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><a href='editAmDelivery.php?customerNumber=<?echo $customerNumber;?>&action=addSite&country=<?echo $country;?>'>Add Site</a></td></tr>
</table>
</center>
<?
	list_am_site_table( $country );
?>
<center>
<br/>
<br/>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
</table>
</center>
<?
	
}elseif( $action == "edit" ){

	$sql = "SELECT COUN1X, CUSN1X, LOTS1X, ACTM1X, OPMG1X, BACM1X, BOPM1X, DIRC1X, BDIR1X FROM CIL13XJ1 WHERE COUN1X='$country' AND DSEQ1X='$deliverySequence'";
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
	
	//echo $sql;
	$userArray = get_user_list ();
	
	?>
	<center>
<form method='post' action='editAmDelivery.php'>
<table width=60% cellpadding=0 cellspacing=0>
	<?
	echo trim("<tr><td colspan='2' class='titleBig'>Account Manger Site Maintenance</td></tr>");
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
		
	while (($row = odbc_fetch_array( $res )) != false ) {
		echo "<tr>";
			echo "<td class='boldMed'>Customer #:</td>";
			echo "<td>" . trim($row['CUSN1X']) . "</td>";
		echo "<tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Delivery Sequence:</td>";
			echo "<td>" . trim($deliverySequence) . "</td>";
		echo "<tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Lotus Notes Company:</td>";
			echo "<td><input type='text' name='lotusNumber' value='" . trim($row['LOTS1X']) . "'></td>";
		echo "<tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Account Manager:</td>";
			echo "<td><select name='accountManager'>";
				show_user_list( $userArray, $row['ACTM1X']);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Back-up Account Manager:</td>";
			echo "<td><select name='bAccount'>";
				show_user_list( $userArray, $row['BACM1X']);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Operations Manager:</td>";
			echo "<td><select name='opsManager'>";
				show_user_list( $userArray, $row['OPMG1X']);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Back-up Ops Manager:</td>";
			echo "<td><select name='bOps'>";
				show_user_list( $userArray, $row['BOPM1X']);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Director:</td>";
			echo "<td><select name='director'>";
				show_user_list( $userArray, $row['DIRC1X']);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Back-up Director:</td>";
			echo "<td><select name='bDirector'>";
				show_user_list( $userArray, $row['BDIR1X']);
			echo "</select></td>";
		echo "</tr>";
		
		
	}
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td><input type='submit' value='Save'></td></tr>";
	echo "<input type='hidden' name='action' value='updateSite'>";
	echo "<input type='hidden' name='country' value='" . $row['COUN1X'] .  "'>";
	echo "<input type='hidden' name='deliverySequence' value='$deliverySequence'>";
	echo "<input type='hidden' name='country' value='$country'>";
	?>
	</table>
</form>
</center>
	<?

} elseif( $action == "updateSite" ) {
	
	
			
		$updateSql = "UPDATE CIL13x SET LOTS1X='$lotusNumber', ACTM1X=$accountManager, OPMG1X=$opsManager,";
		$updateSql .= "BACM1X=$bAccount, BOPM1X=$bOps, DIRC1X=$director, BDIR1X=$bDirector WHERE COUN1X='$country' AND DSEQ1X='$deliverySequence'";
		
		//echo $updateSql;
		$res = odbc_prepare ( $conn, $updateSql );
		odbc_execute ( $res );
		
?>
<center><br/>
<br/>
<b>Account have been updated</b><br/><br/>
	<form method='post' action='editAmDelivery.php'>
	<input type='submit' value="Continue"/>
	<input type='hidden' name='action' value=''/>
	<input type='hidden' name='country' value='<?echo $country;?>'/>
	</form>
	<?
}elseif( $action == "addSite" ) {
	?>
	<center>
<form method='post' action='editAmDelivery.php'>
<table width=60% cellpadding=0 cellspacing=0>
	<? 
	echo "<tr><td colspan='2' class='titleBig'>Account Manger Site Maintenance</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";

		echo "<tr>";
			echo "<td class='boldMed'>Customer #:</td>";
			echo "<td>" . trim($customerNumber) . "</td>";
		echo "<tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Delivery Sequence:</td>";
			echo "<td><input type='text' name='deliverySequence' value=''></td>";
		echo "<tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Lotus Notes Company:</td>";
			echo "<td><input type='text' name='lotusNumber' value=''></td>";
		echo "<tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Account Manager:</td>";
			echo "<td><select name='accountManager'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Back-up Account Manager:</td>";
			echo "<td><select name='bAccount'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Operations Manager:</td>";
			echo "<td><select name='opsManager'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Back-up Ops Manager:</td>";
			echo "<td><select name='bOps'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Director:</td>";
			echo "<td><select name='director'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='boldMed'>Back-up Director:</td>";
			echo "<td><select name='bDirector'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td><input type='submit' value='Save'></td></tr>";
	echo "<input type='hidden' name='action' value='saveSite'>";
	echo "<input type='hidden' name='customerNumber' value='$customerNumber'>";
	echo "<input type='hidden' name='country' value='$country'>";
	?>
	</table>
</form>
</center>
<?	
}elseif( $action == "saveSite" ) {
	
	$insertSql = "INSERT INTO CIL13x VALUES (";
	$insertSql .= "'$CONO', '$customerNumber', '$deliverySequence', $lotusNumber, $accountManager, $opsManager, '$country', $bOps";
	$insertSql .= ", $bAccount, $director, $bDirector)";
	$res = odbc_prepare ( $conn, $insertSql );
	odbc_execute ( $res );
		
	
	?>
	<center><br/>
	<br/>
	<b>Site have been added</b><br/><br/>
	<form method='post' action='editAmDelivery.php'>
	<input type='submit' value="Continue"/>
	<input type='hidden' name='action' value=''/>
	<input type='hidden' name='country' value='<?echo $country;?>'/>
	</form>
	<?

}elseif( $action == "deleteSite" ) {
	$deleteSql = "DELETE FROM CIL13X WHERE COUN1X='$country' AND DSEQ1X='$deliverySequence'";
	$res = odbc_prepare ( $conn, $deleteSql );
	odbc_execute ( $res );
	
	?>
	<center><br/>
	<br/>
	<b>Site have been deleted</b><br/><br/>
	<form method='post' action='editAmDelivery.php'>
	<input type='submit' value="Continue"/>
	<input type='hidden' name='action' value=''/>
	<input type='hidden' name='country' value='<?echo $country;?>'/>
	</form>
	<?	
}
?>
</center>
</center>
</center>
</body>
</html>


