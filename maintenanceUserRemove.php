<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUserRemove.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceUserRemove.php application page for removing users from current supervisor<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0097     TJS     12/04/2010  re-write userMaintenance for new escalation<br>  
 */
/**
 */
?>

<head>
<script type="text/javascript">
	function refreshParent(){
		window.opener.location.reload();
	}
	function closeCurrent(){
		self.close();
	}
</script>
</head>
<?php 
global $conn;
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

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

if( $_SESSION['authority'] == "S" || is_supervisor( $_SESSION['userID'] ) ){
	
	$removeSql = "update CIL31 SET SUPR31=0 WHERE EMPL31={$_REQUEST['empId']}";
	
	$res = odbc_prepare( $conn, $removeSql );
	odbc_execute( $res );
	
?>	
<body onload='refreshParent()'>
<body>
<center>
<table width=100%>
	<tr>
		<td align='center'>User has been removed</td>
	</tr>
	<tr>
		<td align='center'><a href='#' onclick='closeCurrent()'>Continue</a></td>
	</tr>
	
</table>
</center>
</body>
	
<?php 
}
?>
