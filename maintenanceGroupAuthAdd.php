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
		<TD class='title'>Add Group Authentication </TD>
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
	$groupID = trim($_REQUEST['groupID']);
	$userID = trim($_REQUEST['userID']);
	
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
	
	$nextIDSql = "SELECT * FROM CIL40 ORDER BY ID40 DESC FETCH FIRST ROW ONLY";
	
	$resID = odbc_prepare($conn,$nextIDSql);
	odbc_execute($resID);
	while( $row = odbc_fetch_array( $resID ) ){
	    if($row['ID40']){
		$preID = $row['ID40'];
		$nextID = $preID + 1;
	    }else{
	        $nextID = 1;
	    }
	}
	if($nextID<=0) $nextID = 1;
	
	$ifExistSql = "SELECT * FROM CIL40 WHERE USER40 = '$userID' AND GRUP40 = '$groupID'";
	//echo $ifExistSql;
	$res = odbc_prepare( $conn, $ifExistSql );
	odbc_execute( $res );
	$row = odbc_fetch_array( $res );
	//echo count($row);
	if(empty($row)){
	//$rows = db2_num_rows($res);
	
	//if($rows <=0){
		$sql = "INSERT INTO CIL40
				VALUES('".$nextID."','".$userID."','".$groupID."','".$chkCreate."','".$chkEdit."','".$chkRead."','".$chkClose."') ";
		//echo $sql;
		//LP00013	
		$res = odbc_prepare( $conn, $sql );
		odbc_execute( $res );
		echo '
		<table border="0" width="75%" cellpadding="0" cellspacing="0">
		<TR class="header">
			<TD class="header">Group Added to User Profile</TD>
		</TR>
		<TR>
			<TD>&nbsp;</TD>
		</TR>
		<TR class="header">
			<TD class="header">Back to <a href="maintenanceUserEdit.php?userId='.$_REQUEST['userID'].'">User Maintenance Page</a></TD>
		</TR>
		</table>>
		';
	}else{
		echo '
		<table border="0" width="75%" cellpadding="0" cellspacing="0">
		<TR class="header">
			<TD class="header">Record alread exist</TD>
		</TR>
		<TR>
			<TD>&nbsp;</TD>
		</TR>
		<TR class="header">
			<TD class="header">Back to <a href="maintenanceUserEdit.php?userId='.$_REQUEST['userID'].'">User Maintenance Page</a></TD>
		</TR>
		</table>>
		';
	}
}
if( $action == "" || $action == "start" ){
	
	
	//LP0013
	$sql = "SELECT *
	FROM CIL39
	";
	//}
	$sql .= " ORDER by DESC39";
	
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
	$selectOptions = '<option value="0">Select Group</option>';
	while( $row = odbc_fetch_array( $res ) ){
		$selectOptions .= '<option value="'.$row['ID39'].'">'.$row['DESC39'].'</option>';
	}
	?>
	<form method='post' action='maintenanceGroupAuthAdd.php' name='frm'>
	<table border='0' width='75%' cellpadding='0' cellspacing='0'>
	<TR class='header'>
		<TD class='header'>Group Name</TD>
		<TD class='header'>Create</TD>
		<TD class='header'>Edit</TD>
		<TD class='header'>Read</TD>
		<TD class='header'>Close</TD>
	</TR>
	
	
        	<tr>
			<td width=30%>
			<select name="groupID">
            <?php echo $selectOptions;?>
            </select></td>
			<td width=30%><input type="checkbox" name="chkCreate" value="1" /></td>
            <td width=30%><input type="checkbox" name="chkEdit" value="1"  /></td>
            <td width=30%><input type="checkbox" name="chkRead" value="1"  /></td>
            <td width=30%><input type="checkbox" name="chkClose" value="1" /></td>
			</tr><?php 
	
	?>
    <tr>
		<td colspan='4'><input type='submit' value='Save Authentication'/>
		<input type='hidden' name='action' value='save'/>
		<input type='hidden' name='userID' value='<?php echo $_REQUEST['userID'];?>'/>
		</td>
	</tr>
    </table>
    </form>
<?php    
}
ob_flush();

?>






