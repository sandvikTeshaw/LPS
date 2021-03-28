<?
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceMessage.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceMessage.php allows system addministrators the ability to add, edit and delete messages posted on the
 * 							main page of LPS application.  Messages are listed in type, precedence and date and have an expiration date that
 * 							once passed will stop displaying on main page.<br>
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
if (!isset($conn)) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if (isset($conn)) {

} else {
	echo "Connection Failed";
}

if (isset($email)) {
	$userInfo [] = "";
	$userInfo = userInfo ( $email, $password );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	$_SESSION ['email'] = $email;
	$_SESSION ['password'] = $password;
	
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}
} elseif (isset($_SESSION ['email'])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif( isset($_COOKIE ["mtp"])) {
	
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
<script language="Javascript1.2" src="<?echo $mtpUrl;?>/copysource/editor/editor.js"></script>
<script>
_editor_url = "<?echo $mtpUrl;?>/copysource/editor/";
</script>
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
		echo "<TD class='title'>Message Maintenance</TD>";
	echo "</TR>";
echo "</table>";
if( !isset($archive) ){
    $archive = false;
}
if( !isset($action) || $action == "" ){
	$userArray = get_user_info_array_by_id();
	echo "<table width=90% cellpadding=0 cellspacing=0>";
		echo "<TR><TD colspan='3'><a href='maintenanceMessage.php?action=add'>[Add Message]</a></TD><TD colspan='4' class='right'>";
		if( $archive != "true" ){
			echo "<a href='maintenanceMessage.php?archive=true'>[View Archives]</a>";
		}else{
			echo "<a href='maintenanceMessage.php?archive=false'>[View Current]</a>";
		}
		echo "</TD></TR>";
		$exDate = get_escalation_date_time( 24 );
		if( !isset($archive) ||  $archive != "true" ){
			$sql = "SELECT MESS28, PREC28, DATE28, AUTH28, ID28, TYPE28, EXPR28 FROM CIL28L00 WHERE DEL28 = 'N' AND EXPR28 > '20081201' ORDER BY TYPE28, PREC28, ID28";
		}else{
			$sql = "SELECT MESS28, PREC28, DATE28, AUTH28, ID28, TYPE28, EXPR28 FROM CIL28L00 WHERE DEL28 = 'N' AND EXPR28 < '" . date('Ymd') . "' ORDER BY TYPE28, PREC28, ID28";
		}
		$res = odbc_prepare( $conn, $sql );
		odbc_execute( $res );
		//echo $sql;
		echo "<TR class='header'>";
				echo "<TD class='header'>Type</TD>";
				echo "<TD class='header'>Precedence</TD>";
				echo "<TD class='header'>Message</TD>";
				echo "<TD class='header'>Author</TD>";
				echo "<TD class='header'>Date</TD>";
				echo "<TD class='header' colspan='2'>Expiry</TD>";
		echo "</TR>";
		$rowCounter = 0;
		while( $row = odbc_fetch_array( $res ) ){
			$rowCounter++;
			
			if( $rowCounter % 2 ){
				echo "<TR>";
			}else{
				echo "<TR class='alternate'>";
			}
				$messageArray = get_message_type_array();
				echo "<TD width=5%>";
					echo $messageArray[$row['TYPE28']];
				echo "</TD>";
				echo "<TD width=2% class='center'>";
					echo $row['PREC28'];
				echo "</TD>";
				echo "<TD>";
					echo $row['MESS28'];
				echo "</TD>";
				echo "<TD width=15%>";
					echo $userArray[$row['AUTH28']]['name'];
				echo "</TD>";
				$showDate = formatDate($row['DATE28']);
				echo "<TD width=8%>";
					echo $showDate;
				echo "</TD>";
				$showDate = formatDate($row['EXPR28']);
				echo "<TD width=8%>";
					echo $showDate;
				echo "</TD>";
				
				echo "<TD width=5% class='right'>";
					echo "<a href='maintenanceMessage.php?action=edit&id=" . $row['ID28'] ."' alt='edit message'><img src='$IMG_DIR/edit.gif' border=0></a>"; 
					echo "<a href='maintenanceMessage.php?action=delete&id=" . $row['ID28'] ."' alt='delete message'><img src='$IMG_DIR/delete.gif' border=0></a>"; 
				echo "</TD>";
			echo "</TR>";
			
				
		}
	echo "</table>";	
}elseif( $action == "save" ){
		
	if( strlen( $eMonth ) == 1 ){
		$eMonth = "0" . $eMonth;
	}
	if( strlen( $eDay ) == 1 ){
		$eDay = "0" . $eDay;
	}
	
	$expiryDate = $eYear . $eMonth . $eDay;

	$nextId = get_next_unique_id( FACSLIB, "CIL28L00", "ID28", "" );
	str_replace( "'", "''", $message );
	$insertSql = "INSERT INTO CIL28 VALUES(";
	$insertSql .= "'$CONO', $nextId, '" . trim($message) . "', $precedence, " . $_SESSION['userID'] . "," . date( 'Ymd') . ",'N', $type, " . date( 'His') . ", $expiryDate )";
	
	
	$res = odbc_prepare( $conn, $insertSql );
	odbc_execute( $res );

//echo $insertSql . "<hr>";
	echo "<br><br><b>Message Has been Saved</b>";
	?>
	<meta http-equiv="refresh" content="1;url="maintenanceMessage.php" />
	<?
}elseif( $action == "delete" ){
	
	$sql = "SELECT MESS28 FROM CIL28L01 WHERE ID28 = $id";
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );

	while( $row = odbc_fetch_array( $res ) ){
		
	echo "<center>";
	echo "<form method='post' action='maintenanceMessage.php'>";
	echo "<table width=85%>";
		echo "<TR>";
			echo "<TD CLASS='bold'>Message:</TD>";
			echo "<TD>" . $row['MESS28'] . "</TD>";
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
			echo "<TD colspan='2'><input type='submit' name='submit' value='Save Message'></TD>";
		echo "</TR>";
	echo "</table>";
	echo "<input type='hidden' name='action' value='confirmDelete'>";
	echo "<input type='hidden' name='id' value='$id'>";
	echo "</form>";
	echo "</center>";
	
	}
}elseif( $action == "add" || $action == "edit" ){
	
	if( $action == "edit" ){
		$sql = "SELECT MESS28, PREC28, ID28, TYPE28, EXPR28 FROM CIL28L01 WHERE ID28 = $id";
		$res = odbc_prepare( $conn, $sql );
		odbc_execute( $res );
		
		while( $row = odbc_fetch_array( $res ) ){
			$id = $row['ID28'];
			$message = $row['MESS28'];
			$precedence = $row['PREC28'];
			$type = $row['TYPE28'];
			$eYear = substr( $row['EXPR28'], 0, 4 );
			$eMonth = substr( $row['EXPR28'], 4, 2 );
			$eDay = substr( $row['EXPR28'], 6, 2 );
		}
	}
	echo "<form method='post' action='maintenanceMessage.php'>";
	echo "<table width=90%>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
		echo "<TR>";
			echo "<TD class='boldTop'>Message:</TD>";
			echo "<TD>";
				echo "<textarea name='message' id='message' style='width:100%; height:100'>$message</textarea>";
			echo "</TD>";
			?>
			<script language="javascript1.2" defer>
				editor_generate('message');
			</script> 
			<?
		echo "</TR>";
		echo "<TR>";
			echo "<TD class='bold'>Precedence:</TD>";
			echo "<TD>";
				echo "<select name='precedence'>";
					list_precedence( 1, 10, $precedence );
				echo "</select>";
			echo "</TD>";
		echo "</TR>";
		echo "<TR>";
			echo "<TD class='bold'>Type:</TD>";
			echo "<TD>";
				echo "<select name='type'>";
					list_message_types( $type );
				echo "</select>";
			echo "</TD>";
		echo "</TR>";
		echo "<TR>";
			echo "<TD class='bold'>Expiry Date:</TD>";
			echo "<TD>";
				select_date_listing("eYear", "eMonth", "eDay", $eYear, $eMonth, $eDay );
			echo "</TD>";
		echo "</TR>";
		echo "<TR><TD>&nbsp</TD></TR>";
		echo "<TR>";
			echo "<TD colspan='2'><input type='submit' name='submit' value='Save Message'></TD>";
		echo "</TR>";
	echo "</table>";
	if( $action == "add" ){
		echo "<input type='hidden' name='action' value='save'>";
	}else{
		echo "<input type='hidden' name='action' value='update'>";
		echo "<input type='hidden' name='id' value='$id'>";
	}
	echo "</form>";
}elseif( $action == "update" ){
	
	if( strlen( $eMonth ) == 1 ){
		$eMonth = "0" . $eMonth;
	}
	if( strlen( $eDay ) == 1 ){
		$eDay = "0" . $eDay;
	}
	
	$expiryDate = $eYear . $eMonth . $eDay;
	
	$updateSql = "UPDATE CIL28 SET MESS28='$message', TYPE28=$type, PREC28='$precedence', AUTH28=" . $_SESSION['userID'] . ", DATE28=" . date('Ymd') . ", EXPR28=$expiryDate";
	$updateSql .= ", TIME28='" . date( 'His' ) . "' WHERE ID28=$id";
	$res = odbc_prepare( $conn, $updateSql );
	odbc_execute( $res );

	echo "<br><br><b>Message Has been Updated</b>";
	?>
	<meta http-equiv="refresh" content="1;url="maintenanceMessage.php" />
	<?
	
}elseif( $action == "confirmDelete" ){
	
	if( $delete == "Y" ){
		
		$deleteSql = "DELETE FROM CIL28 WHERE ID28=" . $id;
		$res = odbc_prepare( $conn, $deleteSql );
		odbc_execute( $res );
		echo "<br><br><b>Message Has been Deleted</b>";
		?>
		<meta http-equiv="refresh" content="1;url="maintenanceMessage.php" />
		<?
		
	}else{
		
		echo "<br><br><b>Message Has <i>Not</i> been Deleted</b>";
		?>
		<meta http-equiv="refresh" content="1;url="maintenanceMessage.php" />
		<?
		
	}
	
}



