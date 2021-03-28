<?
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			addCountry.php<br>
 * Development Reference:	DI868<br>
 * Description:				addCountry.php allows system administrators to countries to be used in account manager maintenance.<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY  	DD/MM/YYYY			    COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0359	  TS	  11/10/2011	Removed back-up functionality
 */
/**
 */

global $countryCode, $conn, $action, $lotusNumber, $CONO, $bDirector, $accountManager, $countryName, $customerNumber, $director,
	$opsManager, $bAccount, $bOps;

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

if( $action == "" ){
	$userArray = get_user_list ();
	
?>
	<center>
<form method='post' action='addCountry.php'>
<table width=60% cellpadding=0 cellspacing=0>
	<?
	echo "<TR><TD colspan='2' class='titleBig'>Add Country</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";

		echo "<TR>";
			echo "<TD class='boldMed'>Country Code:</TD>";
			echo "<TD><input type='text' name='countryCode' value=''></TD>";
		echo "<TR>";
		echo "<TR>";
			echo "<TD class='boldMed'>Country:</TD>";
			echo "<TD><input type='text' name='countryName' value='' size=75></TD>";
		echo "<TR>";
		echo "<TR>";
			echo "<TD class='boldMed'>Customer #:</TD>";
			echo "<TD><input type='text' name='customerNumber' value=''></TD>";
		echo "<TR>";
		echo "<TR>";
			echo "<TD class='boldMed'>Lotus Notes Company:</TD>";
			echo "<TD><input type='text' name='lotusNumber' value=''></TD>";
		echo "<TR>";
		echo "<TR>";
			echo "<TD class='boldMed'>Account Manager:</TD>";
			echo "<td><select name='accountManager'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</TR>";
		echo "<TR>";
			echo "<TD class='boldMed'>Operations Manager:</TD>";
			echo "<td><select name='opsManager'>";
				show_user_list( $userArray, 0);
			echo "</select></td>";
		echo "</tr>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD><input type='submit' value='Save Country'></TD></TR>";
	echo "<input type='hidden' name='action' value='save'>";
	?>
	</table>
</form>
</center>
	<?
}else{

	$countryCode = strtoupper($countryCode);

	$countryCount = count_records( FACSLIB, "CIL13", "WHERE COUN13='$countryCode'");
	
	if( $countryCount == 0 ){
		if( $lotusNumber == "" ){
			$lotusNumber = 0;
		}
		$insertSql = "INSERT INTO CIL13 values( '$countryCode', '$customerNumber', $lotusNumber, $accountManager, '$CONO', $opsManager, 0";
		$insertSql .= ",0,'" . addslashes($countryName) . "', 0, 0)"; 
		$res = odbc_prepare( $conn, $insertSql );
		odbc_execute( $res );
		//echo $insertSql;
		
		?>
		<center><br/>
		<br/>
		<b>Country Has Been Added</b><br/><br/>
		<input type='button' onclick="closeW()" value="Close Window"/></center>
		<?
	}else{
		echo "<center>";
		echo "<form method='get' action='addCountry.php'>";
		echo "<br><br><b>Country code already exists</b>";
		echo "<br><input type='submit' value='Back'>";
		echo "<input type='hidden' name='action' value=''>";
		echo "</form>";
		echo "</center>";
			
	}
}
?>
</body>
</html>
