<?
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			stockroomMaintenance.php<br>
 * Development Reference:	DI868<br>
 * Description:				stockroomMaintenance.php allows system administrators to maintain stockrooms<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY			    COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0359	  TS	  11/10/2011  Removed Back-up functionality
 *  LP0054     AD     20/05/2019 LP0054 - LPS - Create "Assign to ____" Buttons
 */
/**
 */
global $conn, $action, $stockroom, $CONO, $type;

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
<title>Logistics Process Support</title>

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
headerFrame ( $_SESSION ['name'] );
?>

<body
	onunload="opener.location=('../maintenanceNotification.php?maintenanceType=PFC')">

<center>
<table width="95%" cellpadding='0' cellspacing='0'>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='2' class='titleBig'>Stockroom Maintenance</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
<?

if ($action == "addStockroom" || $action == "editStockroom") {
	echo "<form method='post' action='stockroomMaintenance.php'>";
	$userArray = get_user_list ();
	
	if ($action == "editStockroom") {
		$sql = "select STRC20, STRN20, OPMG20, ACTM20, BOPM20, BACT20, DIRC20, BDIR20 FROM CIL20L01 WHERE CONO20='$CONO' AND STRC20='$stockroom'";
		$res = odbc_prepare ( $conn, $sql );
		odbc_execute ( $res );
		
		//echo $sql;
		while ( ($row = odbc_fetch_array ( $res )) != false ) {
			$stockroomCode = $row ['STRC20'];
			$stockroomName = $row ['STRN20'];
			$opsManager = $row ['OPMG20'];
			$accountManager = $row ['ACTM20'];
			$bOps = $row ['BOPM20'];
			$bAccount = $row ['BACT20'];
			$director = $row ['DIRC20'];
			$bDirector = $row ['BDIR20'];
			$freightContact=0;
			$warehouseContact=0; 
		}
		$sql2 = "SELECT USER49 FROM CIL49 WHERE KEY149 = 'FRE' AND KEY249 ='".$stockroomCode."'";//lp0054_ad
		//echo $sql2;
		$res2 = odbc_prepare ( $conn, $sql2 );//lp0054_ad
		odbc_execute ( $res2 );//lp0054_ad
		while ( ($row2 = odbc_fetch_array ( $res2 )) != false ) {//lp0054_ad
		    $freightContact=$row2['USER49'];//lp0054_ad
		}//lp0054_ad
		$sql3 = "SELECT USER49 FROM CIL49 WHERE KEY149 = 'WAR' AND KEY249 ='".$stockroomCode."'";//lp0054_ad
		$res3 = odbc_prepare ( $conn, $sql3 );//lp0054_ad
		odbc_execute ( $res3 );//lp0054_ad
		while ( ($row3 = odbc_fetch_array ( $res3 )) != false ) {//lp0054_ad
		    $warehouseContact=$row3['USER49'];//lp0054_ad
		}//lp0054_ad
		
		
		
		?>
	<tr>
		<td class='bold'>Stockroom Code:</td>
		<td><?
		echo trim ( $stockroomCode );
		?></td>
	</tr>
	<?
	} else {
		?>
	<tr>
		<td class='bold'>Stockroom Code:</td>
		<td><input type='text' name='stockroomCode'
			value='<?
		echo trim ( $stockroomCode );
		?>' maxlength='2' size='2' /></td>
	</tr>
	<?
	}
	?>
	<tr>
		<td class='bold'>Stockroom Name:</td>
		<td><input type='text' name='stockroomName'
			value='<?
	echo trim ( $stockroomName );
	?>' /></td>
	</tr>
	<tr>
		<td class='bold'>Operations Manager:</td>
		<td><select name="opsManager">
		<?
	show_user_list ( $userArray, trim ( $opsManager ) );
	?>
		</select></td>
	</tr>
	<tr>
		<td class='bold'>Account Manager:</td>
		<td><select name="accountManager">
		<?
	show_user_list ( $userArray, trim ( $accountManager ) );
	?>
		</select></td>
	</tr>
	<tr><?php //lp0054_AD ?>
		<td class='bold'>Freight Contact:</td><?php //lp0054_AD ?>
		<td><select name="freightContact"><?php //lp0054_AD ?>
		<?
		show_user_list ( $userArray, trim ( $freightContact ) );//lp0054_AD
	?>
		</select></td><?php //lp0054_AD ?>
	</tr><?php //lp0054_AD ?>
	<tr><?php //lp0054_AD ?>
		<td class='bold'>Warehouse Contact:</td><?php //lp0054_AD ?>
		<td><select name="warehouseContact"><?php //lp0054_AD ?>
		<?
		show_user_list ( $userArray, trim ( $warehouseContact ) );//lp0054_AD
	?>
		</select></td><?php //lp0054_AD ?>
	</tr><?php //lp0054_AD ?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><input type='submit' value='Continue' /></td>
	</tr>
	<?
	if ($action == "editStockroom") {
		echo "<input type='hidden' name='action' value='updateStockroom'/>";
		echo "<input type='hidden' name='stockroomCode' value='$stockroomCode'/>";
	} else {
		echo "<input type='hidden' name='action' value='saveStockroom'/>";
	
	}
	
	echo "</form>";
} elseif ($action == "updateStockroom") {
	
   //LP0054_AD $updateSql = "UPDATE CIL20 SET STRN20='$stockroomName', OPMG20=$opsManager, ACTM20=$accountManager, BOPM20=0, BACT20=0, DIRC20=0, BDIR20=0 WHERE STRC20='$stockroomCode'";
    $updateSql = "UPDATE CIL20 SET STRN20='$stockroomName', OPMG20=$opsManager, ACTM20=$accountManager, BOPM20=$freightContact, BACT20=0, DIRC20=0, BDIR20=0 WHERE STRC20='$stockroomCode'";//LP0054_AD
    
	$updateRes = odbc_prepare ( $conn, $updateSql );
	odbc_execute ( $updateRes );
	
	//echo $updateSql;
	
	if ($freightContact!=0)//lp0054_ad
	{//lp0054_ad
	    $delSql2 = " DELETE FROM CIL49 WHERE KEY149='FRE' AND KEY249= '".$stockroomCode."'";//lp0054_ad
	    $delRes2 = odbc_prepare ( $conn, $delSql2 );//lp0054_ad
	    odbc_execute ($delRes2);//lp0054_ad
	    $insertSql2 = "INSERT INTO CIL49 VALUES(".get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" ) . ", 0, 0, 'FRE', '".$stockroomCode."' ,'','','', ".$freightContact.")";//lp0054_ad
	    $insertRes2 = odbc_prepare ( $conn, $insertSql2 );//lp0054_ad
	    odbc_execute ( $insertRes2 );	//lp0054_ad
	}//lp0054_ad
	if ($warehouseContact!=0)//lp0054_ad
	{//lp0054_ad
	    $delSql3 = " DELETE FROM CIL49 WHERE KEY149='WAR' AND KEY249= '".$stockroomCode."'";//lp0054_ad
	    $delRes3 = odbc_prepare ( $conn, $delSql3 );//lp0054_ad
	    odbc_execute ($delRes3);//lp0054_ad
	    $insertSql3 = "INSERT INTO CIL49 VALUES(".get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" ) . ", 0, 0, 'WAR', '".$stockroomCode."' ,'','','', ".$warehouseContact.")";//lp0054_ad
	    $insertRes3 = odbc_prepare ( $conn, $insertSql3 );//lp0054_ad
	    odbc_execute ( $insertRes3 );	//lp0054_ad
	}//lp0054_ad
	
	?>
	
	<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>Stockroom has been updates</td>
</tr>
<tr>
	<td class='center'><input type='button' onclick="closeW()"
		value="Close Window"></input></td>
</tr>
	<?

} elseif ($action == "saveStockroom") {
	
	$countRecords = count_records ( FACSLIB, "CIL20L01", "WHERE CONO20='$CONO' AND STRC20='$stockroomCode'" );
	
	if ($countRecords > 0) {
		
		?><tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>Stockroom code already exists</td>
</tr>
<tr>
	<td class='center'><input type='button'
		onclick="javascript:history.go(-1)" value="Back"></input></td>
</tr>
		<?
	
	} else {
		
		$insertSql = "INSERT INTO CIL20 VALUES(";
		$insertSql .= "'$CONO', '$stockroomCode', '$stockroomName', $opsManager, $accountManager, 0, 0, 0, 0 )";//LP0054_AD
	//LP0054_AD	$insertSql .= "'$CONO', '$stockroomCode', '$stockroomName', $opsManager, $accountManager, 0, 0, 0, 0 )";
		
		$insertRes = odbc_prepare ( $conn, $insertSql );
		odbc_execute ( $insertRes );
		if ($freightContact!=0)//lp0054_ad
		{//lp0054_ad
		    $delSql2 = " DELETE FROM CIL49 WHERE KEY149='FRE' AND KEY249= '".$stockroomCode."'";//lp0054_ad
		    $delRes2 = odbc_prepare ( $conn, $delSql2 );//lp0054_ad
		    odbc_execute ($delRes2);//lp0054_ad
		    $insertSql2 = "INSERT INTO CIL49 VALUES(".get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" ) . ", 0, 0, 'FRE', '".$stockroomCode."' ,'','','', ".$freightContact.")";//lp0054_ad
		    $insertRes2 = odbc_prepare ( $conn, $insertSql2 );//lp0054_ad
		    odbc_execute ( $insertRes2 );	//lp0054_ad
		}//lp0054_ad
		if ($warehouseContact!=0)//lp0054_ad
		{//lp0054_ad
		    $delSql3 = " DELETE FROM CIL49 WHERE KEY149='WAR' AND KEY249= '".$stockroomCode."'";//lp0054_ad
		    $delRes3 = odbc_prepare ( $conn, $delSql3 );//lp0054_ad
		    odbc_execute ($delRes3);//lp0054_ad
		    $insertSql3 = "INSERT INTO CIL49 VALUES(".get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" ) . ", 0, 0, 'WAR', '".$stockroomCode."' ,'','','', ".$warehouseContact.")";//lp0054_ad
		    $insertRes3 = odbc_prepare ( $conn, $insertSql3 );//lp0054_ad
		    odbc_execute ( $insertRes3 );	//lp0054_ad
		}//lp0054_ad
		?>
		
		<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>Stockroom has been added</td>
</tr>
<tr>
	<td class='center'><input type='button' onclick="closeW()"
		value="Close Window"></input></td>
</tr>
		<?
	}

} elseif ($action == "deleteStockroom") {
	
	$deleteSql = "DELETE FROM CIL20 WHERE STRC20='$stockroom'";
	$deleteRes = odbc_prepare ( $conn, $deleteSql );
	odbc_execute ( $deleteRes );
	
	?>
		<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>Stockroom has been Deleted</td>
</tr>
<tr>
	<td class='center'><input type='button'
		onclick="opener.location=('../maintenanceNotification.php?maintenanceType=PFC');closeW()"
		value="Close Window"></input></td>
</tr>
		<?
} elseif ($action == "addPfc" || $action == "editPfc") {
	$userArray = get_user_list ();
	
	if ($action == "editPfc") {
		$sql = "SELECT STRC2X, TYPE2X, PFC2X, BPFC2X, TYPE04 FROM CIL20XJ03 WHERE STRC2X='$stockroom' AND TYPE2X=$type";
		$res = odbc_prepare ( $conn, $sql );
		odbc_execute ( $res );
		
		//echo $sql;
		while(($row = odbc_fetch_array ( $res )) != false ){
			$pfc = $row ['PFC2X'];
			$backUp = $row ['BPFC2X'];
			$typeName = $row ['TYPE04'];
			$type = $row ['TYPE2X'];
		}
	}
	
	echo "<form method='post' action='stockroomMaintenance.php'>";
	?>
<tr>
	<td class='bold'>Stockroom Code:</td>
	<td><?
	echo trim ( $stockroom );
	?></td>
</tr>
	<?
	if (! $_SESSION ['typeArray']) {
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
	?>
	<tr>
	<td class='bold'>Type:</td>
	<?
	if ($action != "editPfc") {
		?>
		<td><select name='type'>
			
		<?
		echo "<option value=''>Select Type</option>";
		$x = 0;
		foreach ( $_SESSION ['typeArray'] as $types ) {
			if ($types [3] ['NAME'] [$x] != "") {
				echo "<option value=" . $types [3] ['ID'] [$x] . ">" . trim ( $types [3] ['NAME'] [$x] ) . "</option>";
			}
			$x ++;
		}
		?>
		</select></td>
	<?
	} else {
		?>

			<td><?
		echo trim ( $typeName );
		?></td>
	<?echo "<input type='hidden' name='type' value='$type'>";
	}
	?>
	</tr>

<tr>
	<td class='bold'>PFC:</td>
	<td><select name="pfc">
		<?
	show_user_list ( $userArray, $pfc );
	?>
		</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td><input type='submit' value='Continue'/></td>
</tr>
	<?
	if ($action == "addPfc") {
		echo "<input type='hidden' name='action' value='savePfc'/>";
	} else {
		echo "<input type='hidden' name='action' value='updatePfc'/>";
	}
		echo "<input type='hidden' name='stockroomCode' value='$stockroom'/>";

} elseif ($action == "savePfc") {
	
	$countRecords = count_records ( FACSLIB, "CIL20X", "WHERE STRC2X='$stockroomCode' AND TYPE2X=$type" );
	if ($countRecords > 0) {
		
		?><tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>PFC type already exists for this stockroom</td>
</tr>
<tr>
	<td class='center'><input type='button'
		onclick="javascript:history.go(-1)" value="Back"></input></td>
</tr>
		<?
	
	} else {
		
		$insertSql = "INSERT INTO CIL20X VALUES(";
		$insertSql .= "'$stockroomCode', $type, $pfc, 0)";
		$insertRes = odbc_prepare ( $conn, $insertSql );
		odbc_execute ( $insertRes );
		
		//echo $insertSql;
		

		?>
		
		<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>PFC has been added</td>
</tr>
<tr>
	<td class='center'><input type='button' onclick="closeW()"
		value="Close Window"></input></td>
</tr>
		<?
	}

} elseif ($action == "updatePfc") {
	$updateSql = "UPDATE CIL20X SET PFC2X=$pfc, BPFC2X=0 WHERE STRC2X='$stockroomCode' AND TYPE2X=$type";
	$updateRes = odbc_prepare ( $conn, $updateSql );
	odbc_execute ( $updateRes );
	
	//echo $updateSql;
	?>
	
	<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>PFC has been updates</td>
</tr>
<tr>
	<td class='center'><input type='button' onclick="closeW()"
		value="Close Window"></input></td>
</tr>
	<?

} elseif ($action == "deletePfc") {
	
	$deleteSql = "DELETE FROM CIL20X WHERE STRC2X='$stockroom' AND TYPE2X=$type";
	$deleteRes = odbc_prepare ( $conn, $deleteSql );
	odbc_execute ( $deleteRes );
	
	?>
		<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class='title'>PFC has been Deleted</td>
</tr>
<tr>
	<td class='center'><input type='button'
		onclick="opener.location=('../maintenanceNotification.php?maintenanceType=PFC');closeW()"
		value="Close Window"></input></td>
</tr>
		<?
	
}
?>
</table>
</center>
</body>
</html>