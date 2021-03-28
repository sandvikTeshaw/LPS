<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceMarket.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceMarket.php allows system addministrators the ability to add, edit and delete market Areas in LPS<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 * 
 */
/**
 */
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
?>
<?
if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if ($conn) {

} else {
	echo "Connection Failed";
}

if ($email) {
	$userInfo [] = "";
	$userInfo = userInfo ( $email, $password );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	$_SESSION ['email'] = $email;
	$_SESSION ['password'] = $password;
	
	if (! $_COOKIE ["mtp"]) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}
} elseif ($_SESSION ['email']) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	
	if (! $_COOKIE ["mtp"]) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif ($_COOKIE ["mtp"]) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
} else {

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
@import url(copysource/styles.css);
-->
</style>
</head>
<?
//headerFrame ( $_SESSION ['name'] );
include_once 'copysource/header.php';
echo "<body>";


if ($_SESSION ['userID']) {
	
	if (! $_SESSION ['classArray'] && ! $_SESSION ['typeArray']) {
		$_SESSION ['classArray'] = get_classification_array ();
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
	//menuFrame ( "MTP" );
	include_once 'copysource/menu.php';
}
echo "<center>";
echo "<table width=90%>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR>";
		echo "<TD class='title'>Marekt Area Maintenance</TD>";
	echo "</TR>";
echo "</table>";
if( $action == "" ){
	echo "<table width=90% cellpadding=0 cellspacing=0>";
		echo "<TR><TD colspan='3'><a href='maintenanceMarket.php?action=add'>[Add Market]</a></TD></TR>";
		$sql = "SELECT cast( PSAR15 as CHAR(10) CCSID 285) AS PSAR15, cast( PRMD15 as CHAR(30) CCSID 285) AS PRMD15 FROM DESC WHERE";
		$sql .= " CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY' ORDER BY PRMD15 ASC";
		
		$res = odbc_prepare( $conn, $sql );
		odbc_execute( $res );
		echo "<TR class='header'>";
				echo "<TD class='header' width=10%>Code</TD>";
				echo "<TD class='header' colspan='2'>Market Area</TD>";
		echo "</TR>";
		$rowCounter = 0;
		while( $row = odbc_fetch_array( $res ) ){
			$rowCounter++;
			
			if( $rowCounter % 2 ){
				echo "<TR>";
			}else{
				echo "<TR class='alternate'>";
			}
				echo "<TD>";
					echo $row['PSAR15'];
				echo "</TD>";
				echo "<TD>";
					echo $row['PRMD15'];
				echo "</TD>";
				echo "<TD width=5% class='right'>";
					echo "<a href='maintenanceMarket.php?action=edit&code=" . $row['PSAR15'] ."' alt='Edit Market'><img src='$IMG_DIR/edit.gif' border=0></a>"; 
					echo "<a href='maintenanceMarket.php?action=delete&code=" . $row['PSAR15'] ."' alt='Delete Market'><img src='$IMG_DIR/delete.gif' border=0></a>"; 
				echo "</TD>";
			echo "</TR>";
		}
	echo "</table>";
}elseif( $action == "add" ||  $action == "edit"  ){
	echo "<table width=70% cellpadding=0 cellspacing=0>";
	if( $action == "edit" ){
		$sql = "SELECT cast( PRMD15 as CHAR(30) CCSID 285) AS PRMD15 FROM DESC WHERE";
		$sql .= " CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY' AND PSAR15='$code'";
		$res = odbc_prepare( $conn, $sql );
		odbc_execute( $res );
		while( $row = odbc_fetch_array( $res ) ){
			$marketName = $row['PRMD15'];
		}
	}
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<form method='post' action='maintenanceMarket.php'>";
		echo "<TR>";
			echo "<TD class='bold' width=15%>Code</TD>";
			if( $action == "add" ){
				echo "<TD><input type='text' name='code' value='" . $code . "' maxsize='6' maxlength='3'></TD>";
			}else{
				echo "<TD class='bold'>$code</TD>";
			}
		echo "</TR>";
		echo "<TR>";
			echo "<TD class='bold' width=15%>Market Area</TD>";
			echo "<TD><input type='text' name='marketName' value='" . trim($marketName) . "' class='long' maxsize='30' maxlength='30'>";
		echo "</TR>";
		echo "<TR><TD>&nbsp</TD></TR>";
		echo "<TR>";
			echo "<TD colspan='2'><input type='submit' name='submit' value='Save Market'></TD>";
		echo "</TR>";
		if( $action == "add" ){
			echo "<input type='hidden' name='action' value='save'>";
		}else{
			echo "<input type='hidden' name='action' value='update'>";
			echo "<input type='hidden' name='code' value='$code'>";
		}
	
	echo "</form>";
	echo "</table>";
}elseif ( $action == "save" ){
	
	$marketCount = count_records( DATALIB, "DESC", "WHERE CONO15='$CONO' AND PRMT15='CTRY' AND PSAR15='$code'");

	if( $marketCount == 0 ){
		
		//Insert into Descritions file
		$insertSql = "INSERT INTO INP15 VALUES(";
		$insertSql .= " '$CONO', 'CTRY', '$code', '" . trim($marketName) . "', 0, '', '', 0, 0, '', '', '', '', '', 0, '', '', '', '', '', '' )";
	
		$res = odbc_prepare( $conn, $insertSql );
		odbc_execute( $res );
		
		//Insert into AM file
		$insertAmSql = "INSERT INTO CIL13 VALUES(";
		$insertAmSql .= "'$code', '', 0, 0, '$CONO', 0, 0, 0, '" . trim($marketName) . "', 0, 0)";
		$amRes = odbc_prepare( $conn, $insertAmSql );
		odbc_execute( $amRes );
		
		echo "<br><br><b>Market Area Has been Saved</b>";
		?>
		<meta http-equiv="refresh" content="1;url="maintenanceMarket.php" />
		<?
	}else{
		echo "<br><br><b>Market Area Code Already Exists</b>";
		?>
		<meta http-equiv="refresh" content="1;url="maintenanceMarket.php?action=add&marketName=<?echo $marketName;?>" />
		<?
	}

	
	
}elseif ( $action == "update" ){
	
	$updateSql = "UPDATE DESC SET PRMD15='" . trim($marketName) . "' WHERE CONO15='$CONO' AND PRMT15='CTRY'";
	$updateSql .= " AND PSAR15='$code'";

	$res = odbc_prepare( $conn, $updateSql );
	odbc_execute( $res );

	echo "<br><br><b>Market Area Has been Updated</b>";
	?>
	<meta http-equiv="refresh" content="1;url="maintenanceMarket.php" />
	<?
	
}elseif( $action == "delete" ){

	$sql = "SELECT cast( PRMD15 as CHAR(30) CCSID 285) AS PRMD15 FROM DESC WHERE";
	$sql .= " CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY' AND PSAR15='$code'";
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );

	while( $row = odbc_fetch_array( $res ) ){
		
	echo "<center>";
	echo "<form method='post' action='maintenanceMarket.php'>";
	echo "<table width=85%>";
		echo "<TR>";
			echo "<TD CLASS='bold'>Market Area:</TD>";
			echo "<TD>" . $row['PRMD15'] . "</TD>";
		echo "</TR>";
		echo "<TR>";
			echo "<TD class='bold'>Confirm Delete</TD>";
			echo "<TD>";
				echo "<select name='delete'>";
					list_yesNo( "N" );
				echo "</select></TD>";
		echo "</TR>";
		echo "<TR><TD>&nbsp</TD></TR>";
		echo "<TR>";
			echo "<TD colspan='2'><input type='submit' name='submit' value='Continue'></TD>";
		echo "</TR>";
	echo "</table>";
	echo "<input type='hidden' name='action' value='deleteConfirmation'>";
	echo "<input type='hidden' name='code' value='$code'>";
	echo "</form>";
	echo "</center>";
	
	}
}elseif( $action == "deleteConfirmation" ){

	if( $delete == "Y" ){
		
		$deleteSql = "DELETE FROM INP15 WHERE CONO15='$CONO' AND PRMT15='CTRY'";
		$deleteSql .= " AND PSAR15='$code'";
		
		$res = odbc_prepare( $conn, $deleteSql );
	
		odbc_execute( $res );
		
		echo "<br><br><b>Market Area Has been Deleted</b>";
	}else{
		echo "<br><br><b>Market Area Has <i>Not</i> been Deleted</b>";
	}
	?>
	<meta http-equiv="refresh" content="1;url="maintenanceMarket.php" />
	<?
	
}
