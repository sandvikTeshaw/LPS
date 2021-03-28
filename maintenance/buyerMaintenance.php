<?php
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			buyerMaintenance.php<br>
 * Development Reference:	DI868<br>
 * Description:				buyerMaintenance.php allows system administrators to maintain LPS buyers.  There is a link between System 21 buyers
 * 							and LPS which is run everytime this page is opened.<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY     DD/MM/YYYY			    COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0359	  TS	  11/10/2011  Removed Back-up functionality
 */
/**
 */
global $conn, $action, $SITE_TITLE, $plan;

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
<title><?
echo $SITE_TITLE;
?></title>

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
<?
include_once '../copysource/header.php';
?>

<body
	onunload="opener.location=('../maintenanceNotification.php?maintenanceType=Buyer')">

<center>

<form method="post" action="buyerMaintenance.php">
<table width="95%" cellpadding='0' cellspacing='0'>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='2' class='titleBig'>Buyer Maintenance</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
<?
if ($action == "addBuyer" || $action == "editBuyer") {
	
	$userArray = get_user_list ();
	
	$sql = "select PLAN25, USER25, BBUP25, ACTV25, EXID25, PAID25 FROM CIL25 WHERE PLAN25='$plan'";
	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );
	

	while(( $row = odbc_fetch_array( $res )) != false ) {
		
		$planner = trim($row ['PLAN25']);
		$purchaseOfficer = trim($row ['USER25']);
		$bPurchaseOfficer = trim($row ['BBUP25']);
		$active = trim($row ['ACTV25']);
		$expedite = trim($row ['EXID25']);
		$priceAvailability = trim($row ['PAID25']);
	}
	?>
	<tr>
		<td class='bold'>Planner #:</td>
		<td><?
	echo $planner;
	?></td>
	</tr>
	<tr>
		<td class='bold'>Purchase Officer:</td>
		<td><select name="purchaseOfficer">
		<?
	show_user_list ( $userArray, $purchaseOfficer );
	?>
		</select></td>
	</tr>
	<tr>
		<td class='bold'>Exepiditor:</td>
		<td><select name="expedite">
		<?
	show_user_list ( $userArray, $expedite );
	?>
		</select></td>
	</tr>
	<tr>
		<td class='bold'>Price Availability:</td>
		<td><select name="priceAvailability">
		<?
	show_user_list ( $userArray, $priceAvailability );
	?>
		</select></td>
	</tr>
	<tr>
	
		<td class='bold'>Active:</td>
		<td><select name="active">
			<?
	list_yesNo ( $active );
	?>
		</select></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><input type="submit" value="Continue"/>
			<input type='hidden' name='action' value='updateBuyer'/>
			<input type='hidden' name='planner' value='<?
				echo $planner;
			?>'/>
		
		</td>
	</tr>	
	
	
<?
} elseif ($action == "updateBuyer") {
	
	$updateSql = "UPDATE CIL25 SET USER25=$purchaseOfficer, BBUP25=0, EXID25=$expedite, ";
	$updateSql .= "PAID25=$priceAvailability, BEXP25=0, BPRI25=0, ACTV25='$active', MANG25=0, SUPR25=0,";
	$updateSql .= " DIRC25=0, BMAN25=0, BSUP25=0, BDIR25=0 WHERE PLAN25=$planner";
	$updateRes = odbc_prepare ( $conn, $updateSql );
	odbc_execute ( $updateRes );
	
	
	?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class='title'>Buyer has been updates</td>
	</tr>
	<tr>
		<td class='center'><input type='button' onclick="closeW()"
			value="Close Window"></input></td>
	</tr>
	<?
}
?>

</table>
</form>
</center>
</body>
</html>
