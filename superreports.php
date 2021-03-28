<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			superreports.php<br>
 * Development Reference:	D0270<br>
 * Description:				list tickets of a supervisors reports<br>
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

if (!isset($conn)) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if (isset($conn)) {

} else {
	echo "Connection Failed";
}
/*
 * TODO fix this later
 * 
 */
if (isset($_SESSION ['email'])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif (isset($_COOKIE ["mtp"])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
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

<?php 

include_once 'copysource/header.php';

$useragent = $_SERVER['HTTP_USER_AGENT'];

if ($_SESSION ['userID']) {
    
    if (! $_SESSION ['classArray'] ) {
        $_SESSION ['classArray'] = get_classification_array ();
    }
    if( ! $_SESSION ['typeArray']){
        $_SESSION ['typeArray'] = get_typeName_array ();
    }
    
    include_once 'copysource/menu.php';
}
?>

<center>

<table width=90% cellpadding=0 cellspacing=0>
	<TR>
		<td>&nbsp</td>
	</TR>
	<TR>
		<td>&nbsp</td>
	</TR>
	<TR><TD class='title' colspan='6'>My Reports</TD></TR>
	<TR>
		<td>&nbsp</td>
	</TR>
	<TR class='header'>
		<td class='header'>Employee</td>
		<td class='header'>Supervisor</td>
		<td class='headerCenter'>Open Tickets<br/>Not Escalated</td>
		<td class='headerCenter'>Reminder Tickets</td>
		<td class='headerCenter'>Escalated Tickets</td>
		<td class='headerCenter'>Total Open</td>
	</TR>
	<?php 
	   $employeeArray = array();
	   array_push($employeeArray, $_SESSION ['userID'] );
	   $rowColorFlag = get_super_reports( $_SESSION ['userID'], 0, $employeeArray );
	?>
</table>
	




