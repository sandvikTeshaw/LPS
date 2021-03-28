<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceEscalationReasons.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceEscalationreasons.php allows system addministrators the ability to add, edit and delete EscalationReasons in LPS<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *   LP0053 -   AD     23.11.2018   LPS - Postpone Functionality -create-
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
		echo "<TD class='title'>Hold Escalation Reasons - Maintenance</TD>";
	echo "</TR>";
echo "</table>";
if( $action == "" ){

}elseif( $action == "add" ||  $action == "edit"  ){
	echo "<table width=70% cellpadding=0 cellspacing=0>";
	if( $action == "edit" ){
		$sql = "SELECT * FROM CIL47 WHERE";
		$sql .= " ID47=$code";
		$res = odbc_prepare( $conn, $sql );
		odbc_execute( $res );
		$marketName='';
		while( $row = odbc_fetch_array( $res ) ){
			$marketName = $row['DESC47'];
		}
	}
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<form method='post' action='maintenanceEscalationReasons.php'>";
		echo "<TR>";
			echo "<TD class='bold' width=15%>Code</TD>";
			if( $action == "add" ){
				//echo "<TD><input type='text' name='code' value='" . $code . "' maxsize='6' maxlength='3'></TD>";
			}else{
				echo "<TD class='bold'>$code</TD>";
			}
		echo "</TR>";
		echo "<TR>";
			echo "<TD class='bold' width=15%>Holding Escalation Reason</TD>";
			echo "<TD><input type='text' name='marketName' value='" . trim($marketName) . "' class='long' maxsize='50' maxlength='50'>";
		echo "</TR>";
		echo "<TR><TD>&nbsp</TD></TR>";
		echo "<TR>";
			echo "<TD colspan='2'><input type='submit' name='submit' value='Save Reason'></TD>";
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
	
		$maxSql="SELECT MAX(ID47) FROM CIL47";
	    $maxRes=odbc_prepare( $conn, $maxSql );
	    odbc_execute( $maxRes );	    
	    $row =  odbc_fetch_array( $maxRes );
		$code=$row['00001']+1;
		//Insert into AM file
		$insertAmSql = "INSERT INTO CIL47 VALUES(";
		$insertAmSql .= "'$code', '" . trim($marketName) . "', 0)";
		$amRes = odbc_prepare( $conn, $insertAmSql );
		odbc_execute( $amRes );
		
		echo "<br><br><b>New Escalation Hold Reason Has been Saved</b>";
		?>
		<meta http-equiv="refresh" content="1;url="maintenanceEscalationReasons.php" />
		<?
	
	

	
	
}elseif ( $action == "update" ){
	
	$updateSql = "UPDATE CIL47 SET DESC47='" . trim($marketName) . "' WHERE ";
	$updateSql .= " ID47=$code";

	$res = odbc_prepare( $conn, $updateSql );
	odbc_execute( $res );

	echo "<br><br><b>The reason for holding escalation has been updated</b>";
	?>
	<meta http-equiv="refresh" content="1;url="maintenanceEscalationReasons.php" />
	<?
	
}elseif( $action == "delete" ){

	$sql = "SELECT * FROM CIL47 WHERE";
	$sql .= " ID47='$code'";
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
    $inactive=1;
	while( $row = odbc_fetch_array( $res ) ){
	   if($row['ACTV47']>0) $inactive=0;   
	   }
	   $sqlu = "UPDATE CIL47 SET ACTV47=$inactive WHERE ID47=$code";
	   $resu = odbc_prepare( $conn, $sqlu );
	   odbc_execute( $resu );
	   //echo $sqlu;
	   
   
}
echo "<table width=90% cellpadding=0 cellspacing=0>";
echo "<TR><TD colspan='3'><a href='maintenanceEscalationReasons.php?action=add' title='Add a new Hold Escalation Reason'>[Add Hold Reason]  </a></TD></TR>";
$sql = "SELECT * FROM CIL47";

$res = odbc_prepare( $conn, $sql );
odbc_execute( $res );
echo "<TR class='header'>";
echo "<TD class='header' width=8%>CodeNr</TD>";
echo "<TD class='header' colspan='2'>Holding Escalation Reasons</TD>";
echo "<TD class='header' width=15%>Inactive</TD>";
echo "<TD class='header'></TD>";


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
    echo $row['ID47'];
    echo "</TD>";
    echo "<TD colspan='2'>";
    echo $row['DESC47'];
    echo "</TD>";
    echo "<TD>";
    echo $row['ACTV47']>0 ? "Inactive":"" ;
    echo "</TD>";
    echo "<TD width=8% class='right'>";
    echo "<a href='maintenanceEscalationReasons.php?action=edit&code=" . $row['ID47'] ."' alt='Edit Reason'><img src='$IMG_DIR/edit.gif' border=0  title='Edit Reason Text'></a>";
    echo "<a href='maintenanceEscalationReasons.php?action=delete&code=" . $row['ID47'] ."' alt='Desactivate/Activate Reason'><img src='$IMG_DIR/delete.gif' border=0  title='Toggle INACTIVE status'></a>";
    echo "</TD>";
    echo "</TR>";
}
echo "</table>";
