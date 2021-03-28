<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUserDelete.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceUserDelete.php application page for deleting users, sets DEL05 flag to 'Y'<br>
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


if( $_REQUEST['action'] == "delete" ){
	if( $_SESSION['authority'] == "S" ){
		
		$deleteSql = "update HLP05 SET DEL05='Y' WHERE ID05={$_REQUEST['empId']}";
		$res = odbc_prepare( $conn, $deleteSql );
		odbc_execute( $res );
		
		$returnComment = "User has been deleted";
		
	}else{
		$returnComment = "You do not have access to perform this action";
	}
}elseif( $_REQUEST['action'] == "request" ){
	
	$userName = user_name_by_id( $_REQUEST['empId'] );
	
	?>
	<form method='post' action='maintenanceUserDelete.php'>
		<center>
		<table width='50%'>
			<tr>
				<td>Has <?php echo $userName;?> moved to another group at Sandvik?</td>
				<td>
					<select name='groupConfirmation'>
						<option value='Yes' Selected>Yes</option>
						<option value='No'>No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Is <?php echo $userName;?> still employed by Sandvik?</td>
				<td>
					<select name='employmentConfirmation'>
						<option value='Yes' Selected>Yes</option>
						<option value='No'>No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
				<input type='submit' value='continue'/>
				<input type='hidden' name='empId' value='<?php echo $_REQUEST['empId'];?>'>	
				<input type='hidden' name='action' value='requestConfirmed'>	
				</td>
			</tr>
		</table>
		</center>
	</form>
	<?php 
	die();
	
	
}elseif( $_REQUEST['action'] == "requestConfirmed" ){
	
	$userArray = get_user_info_array_by_id();
	$employeeName = $userArray[$_REQUEST['empId']]['name'];

	$supervisorName = $userArray[ $_SESSION['userID']]['name'];
	$supervisorEmail  = $userArray[ $_SESSION['userID']]['email'];
	
	send_deletion_request_mail( $employeeName, $supervisorName, $supervisorEmail, $groupConfirmation, $employmentConfirmation );
	
	$returnComment = "Your Request has been submitted to an LPS resource";
	
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
	
