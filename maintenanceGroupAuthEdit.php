<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceGroupAuthEdit.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceGroupAuthEdit.php listing of user groups with links to maintenance pages such as add, edit and delete<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  LP0013     IS     11/05/2016  New page added for group authentication maintenance.
 */
/**
 */
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
	

</script>
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

ob_start ( 'compressBuffer' );
//$action = $_REQUEST['action'];
?>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
	<TR>
		<TD>&nbsp</TD>
	</TR>
<? if( $action != "password"){ ?>
	<TR>
		<TD class='title'>Edit Group Authentication</TD>
	</TR>

<?
}else{
?>
	<TR>
		<TD class='title'>Lost Password</TD>
	</TR>
	
<?
}

?>
</table>
<?
if($action == "save"){
	
	if(isset($_REQUEST['chkCreate'])) 
	$chkCreate = $_REQUEST['chkCreate'];
	else
	$chkCreate = 0;
	
	if(isset($_REQUEST['chkRead'])) 
	$chkRead = $_REQUEST['chkRead'];
	else
	$chkRead = 0;
		
	if(isset($_REQUEST['chkEdit'])) 
	$chkEdit = $_REQUEST['chkEdit'];
	else
	$chkEdit = 0;
	
	if(isset($_REQUEST['chkClose'])) 
	$chkClose = $_REQUEST['chkClose'];
	else
	$chkClose = 0;
	
	$sql = "UPDATE CIL40
			SET 
			CRTE40 = '".$chkCreate."',
			EDIT40 = '".$chkEdit."',
			READ40 = '".$chkRead."',
			CLOS40 = '".$chkClose."'";
	
	$sql .= " where GRUP40 = '".$groupID."' AND USER40 = '".$userID."'";

	//LP00013	
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
	echo '
	<table border="0" width="75%" cellpadding="0" cellspacing="0">
	<TR class="header">
		<TD class="header">Authentication Updated</TD>
	</TR>
	<TR class="header">
		<TD class="header">&nbsp;</TD>
	</TR>
	<TR class="header">
		<TD class="header">Back to <a href="maintenanceUserEdit.php?userId='.$_REQUEST['userID'].'">User Maintenance Page</a></TD>
	</TR>
	';
}
if( $action == "" || $action == "start" ){
	$groupID = trim($_REQUEST['groupID']);
	$userID = trim($_REQUEST['userID']);
	
	$sql = "SELECT *
	FROM CIL39
	INNER JOIN CIL40
	ON CIL39.ID39=CIL40.GRUP40";
	
	$sql .= " where CIL40.GRUP40 = ".$groupID." AND CIL40.USER40 = ".$userID;
	//LP00013
	//if( $_SESSION['authority'] != "S" ){
	//	$sql .= " AND ( SUPR31 = {$_SESSION ['userID']} OR SUPR31 = 0 )";
	//}
	//$sql .= " ORDER by DESC39";
	
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
	
	?>
	<form method='post' action='maintenanceGroupAuthEdit.php' name='frm'>
	<table border='0' width='75%' cellpadding='0' cellspacing='0'>
	<TR class='header'>
		<TD class='header'>Group Name</TD>
		<TD class='header'>Create</TD>
		<TD class='header'>Edit</TD>
		<TD class='header'>Read</TD>
		<TD class='header'>Close</TD>
	</TR>
	
	<?
//	$authArray = authority_array();

	while( $row = odbc_fetch_array( $res ) ){
		//LP0013
	   // print_r($row);
		$rowCount++;
    	if( $rowCount % 2 ){      
			echo "<TR class='alternate'>";
		}else{
			echo "<TR class=''>";
		}
		
		?>
			<td width=30%><?php echo $row['DESC39'];?></td>
			<td width=30%><input type="checkbox" name="chkCreate" value="1" <?php echo $chkVal = ($row['CRTE40']==1)?'checked="checked"':""; ?>/></td>
            <td width=30%><input type="checkbox" name="chkRead" value="1" <?php echo $chkVal = ($row['READ40']==1)?'checked="checked"':""; ?> /></td>
            <td width=30%><input type="checkbox" name="chkEdit" value="1" <?php echo $chkVal = ($row['EDIT40']==1)?'checked="checked"':""; ?> /></td>
            <td width=30%><input type="checkbox" name="chkClose" value="1" <?php echo $chkVal = ($row['CLOS40']==1)?'checked="checked"':""; ?> /></td>
			</tr><?php 
	}
	?>
    <tr>
		<td colspan='4'><input type='submit' value='Save Authentication'/>
		<input type='hidden' name='action' value='save'/>
		<input type='hidden' name='userID' value='<?php echo $_REQUEST['userID'];?>'/>
        <input type='hidden' name='groupID' value='<?php echo $_REQUEST['groupID'];?>'/>
		</td>
	</tr>
    </table>
    </form>
<?php    
}
ob_flush();

?>






