<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUserChangeSuper.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceUserChangeSuper.php application page for changing the users supervisor<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0097     TJS     12/04/2010  re-write userMaintenance for new escalation<br>  
 */
/**
 */

include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
global $conn;

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
<script type="text/javascript">
	function closeCurrent( newSuper, userId ){
		var id = "supr_" + userId;
		window.opener.document.getElementById( id ).value = newSuper;
	    self.close();
	}
</script>
</head>
<?php 


if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
	echo "Connection Failed";
}

if ($_SESSION ['email']) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	
	if (! $_COOKIE ["mtp"]) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif ($_COOKIE ["mtp"]) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
}
?>
<body>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td class='title'>Change Supervisor</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
</table>
<?php 

if( !isset( $_SESSION['authority']) ){
    $_SESSION['authority'] = "";
}
if( !isset(  $_REQUEST['action']) ){
    $_REQUEST['action'] = "";
}

if( ( $_SESSION['authority'] == "S" || is_supervisor( $_SESSION['userID'] )) && $_REQUEST['action'] == ""  ){
	
	$superName = user_name_by_id( $_REQUEST['superId'] );
	$empName = user_name_by_id( $_REQUEST['empId'] );
	
	$userArray = get_user_list();
	?>
	<form method='post' action='maintenanceUserChangeSuper.php'>
	<table width=50% cellpadding='0' cellspacing='0'>
	
		<tr>
			<td class='bold'>User:</td>
			<td><?php echo $empName;?></td>
		</tr>
		<tr>
			<td class='bold'>Current Supervisor:</td>
			<td><?php echo $superName;?></td>
		</tr>
		<tr>
			<td class='bold'>New Supervisor:</td>
			<td>
			<select name='super'>
			<?php show_user_list($userArray, trim($_REQUEST['superId'])) ?>
		</select>
			</td>
		</tr>
		<tr>
			<td><input type='submit' value='Save Supervisor'/>
			<input type='hidden' name='action' value='save'>
			<input type='hidden' name='empId' value='<?php echo $_REQUEST['empId'];?>'>
			</td>
		</tr>		
	</table>
	</form>
	
	<?php 
}elseif(( $_SESSION['authority'] == "S" || is_supervisor( $_SESSION['userID'] ))){
	
	$newSuperName = user_name_by_id( $_REQUEST['super'] );
	$updatSql = "update CIL31 SET SUPR31={$_REQUEST['super']} WHERE EMPL31={$_REQUEST['empId']}";
	
	$res = odbc_prepare( $conn, $updatSql );
	odbc_execute( $res );
	
?>	

<body onload='closeCurrent( "<?php echo $newSuperName?>", <?php echo $_REQUEST['empId']?>)'>

</body>
	
<?php 
}
?>
