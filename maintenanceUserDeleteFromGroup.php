<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUserDelete.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceUserDeleteFromGroup.php application page for deleting users, sets DEL05 flag to 'Y'<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  LP0013     IS     17/05/2016  remove group from user profile<br>  
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


if( $_REQUEST['action'] == "delete" ){
	//if( $_SESSION['authority'] == "S" ){
		
		$deleteSql = "Delete from CIL40 where USER40='".$_REQUEST['userID']."' AND GRUP40='".$_REQUEST['groupID']."'";
	
		$res = odbc_prepare( $conn, $deleteSql );
		odbc_execute( $res );
		
		$returnComment = "Group has been deleted from User's Profile";
		
	//}else{
	//	$returnComment = "You do not have access to perform this action";
	//}	
}else{
	
	$returnComment = "You do not have access to perform this action";
}
	
?>	
<body onload='refreshParent()'>
<body>
<center>
<table width=100%>
	<tr>
		<td align='center'><?php echo $returnComment;?></td>
	</tr>
	<tr>
		<td align='center'><a href='#' onclick='closeCurrent()'>Continue</a></td>
	</tr>
	
</table>
</center>
</body>
	
