<?
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			pricingContactMaintenance.php<br>
 * Development Reference:	DI868<br>
 * Description:				pricingContactMaintenance.php allows system administrators to pricing contacts<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY			    COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0359	  TS	  11/10/2011  Removed Back-up functionality
 *  LP0048    KS      17/07/2018  Cannot delete Pricing Contact - bug in pricingContactMaintenance.php [p-5747473]
 *  
 */
/**
 */
global $conn, $action, $CONO, $brand;

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
<?
include_once '../copysource/header.php';
?>

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=PRI')">

<center>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>Pricing Contact Maintenance</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
if( $action == "addPricingContact" || $action == "editPricingContact" ){
	echo "<form method='post' action='pricingContactMaintenance.php'>";
	$userArray = get_user_list ();
	
	if( $action == "editPricingContact" ){
	$sql = "select BRAN16, DESC16, PRCC16, BPRC16 FROM CIL16J02 WHERE CONO16='$CONO' AND BRAN16='$brand'";
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
	
	//echo $sql;
		while(( $row = odbc_fetch_array( $res )) != false ){
			$brandCode = $row[0];
			$brandName = $row[1];
			$responsible = $row[2];
			$backUp = $row[3];
		}
	?>
	<tr>
		<td class='bold'>Brand Code:</td>
		<td><?echo $brandCode;?>
		<input type='hidden' name='brandCode' value='<?echo $brandCode;?>'/></td>
	</tr>
	<?
	}else{
	?>
	<tr>
		<td class='bold'>Brand Code:</td>
		<td><input type='text' name='brandCode' value='<?echo $brandCode;?>' maxlength='3' size='3'/></td>
	</tr>
	<?
	}
?>
	<tr>
		<td class='bold'>Brand Name:</td>
		<td><input type='text' name='brandName' value='<?echo $brandName;?>' size='50'/></td>
	</tr>
	<tr>
		<td class='bold'>Responsible:</td>
		<td>
		<select name="responsible">
		<?show_user_list( $userArray, $responsible); ?>
		</select>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><input type='submit' value='Continue'/></td></tr>
	<?if( $action == "editPricingContact" ){ 
		echo "<input type='hidden' name='action' value='updatePricingContact'/>";
		echo "<input type='hidden' name='brandCode' value='$brand'/>";
	}else{
		echo "<input type='hidden' name='action' value='savePricingContact'/>";
	}
		
	
	echo "</form>";


}elseif( $action == "updatePricingContact" ){
	
	$updateSql = "UPDATE CIL16 SET DESC16='$brandName', PRCC16=$responsible, BPRC16=0 WHERE BRAN16='$brandCode'";
	$updateRes = odbc_prepare( $conn, $updateSql );
	odbc_execute( $updateRes );
	
	?>
	
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class='title'>Pricing Contact has been updates</td></tr>
	<tr><td class='center'><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
	<?
	
}elseif( $action == "savePricingContact" ){

	
	$countRecords = count_records( FACSLIB, "CIL16L01", "WHERE CONO16='$CONO' AND BRAN16='$brandCode'" );
	
	if( $countRecords > 0 ){
		
		?><tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td class='title'>Pricing Contact already exists</td></tr>
		<tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
		<?
		
	}else{
	
		$insertSql = "INSERT INTO CIL16 VALUES(";
		$insertSql .= "'$CONO', '$brandCode', '', $responsible, '$brandName', 0 )";
		
		$insertRes = odbc_prepare( $conn, $insertSql );
		odbc_execute( $insertRes );
		
		//echo $insertSql
		
		
		?>
		
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td class='title'>Pricing Contact has been added</td></tr>
		<tr><td class='center'><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
		<?
	}
	
}elseif( $action == "deletePricingContact"){
	
		//**LP0048  $deleteSql = "DELETE FROM CIL16 WHERE BRAN16='$brandCode'";
		$deleteSql = "DELETE FROM CIL16 WHERE BRAN16='$brand'";   //**LP0048
		$deleteRes = odbc_prepare( $conn, $deleteSql );
		odbc_execute( $deleteRes );
		
		?>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td class='title' colspan='2'>Pricing Contact has been Deleted</td></tr>
		<tr><td class='center' colspan='2'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=PRI');closeW()" value="Close Window"></input></td></tr>
		<?
}
?>
</table></center>
</body>
</html>



