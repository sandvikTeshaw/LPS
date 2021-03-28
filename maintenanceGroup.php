<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUsers.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceGroup.php listing of user groups with links to maintenance pages such as add, edit and delete<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  LP0013     IS     04/05/2016  New page added for group maintenance.
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
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
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
?>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
	<TR>
		<TD>&nbsp</TD>
	</TR>
<?if( $action != "password"){ ?>
	<TR>
		<TD class='title'>Groups List</TD>
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
if( $action == "" || $action == "start" ){
	
	$sql = "SELECT * FROM CIL39";
	//LP00013
	//if( $_SESSION['authority'] != "S" ){
	//	$sql .= " AND ( SUPR31 = {$_SESSION ['userID']} OR SUPR31 = 0 )";
	//}
	$sql .= " ORDER by DESC39";
	
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
		
	?>
	<form method='post' action='maintenanceGroup.php' name='frm'>
	<table border='0' width='75%' cellpadding='0' cellspacing='0'>
	<TR>
    <TD><a href='maintenanceUserAdd.php'><b>[Register User]</b></a> &nbsp;&nbsp;
    <a href='maintenanceUser.php'><b>[User Maintenance]</b></a></TD>
    </TR>
	<TR class='header'>
		<TD class='header'>Group Name</TD>
	</TR>
	
	<?
	$authArray = authority_array();
	$rowCount = 0;
	while( $row = odbc_fetch_array( $res ) ){
		//LP0013
		$rowCount++;
    	if( $rowCount % 2 ){      
			echo "<TR class='alternate'>";
		}else{
			echo "<TR class=''>";
		}
		?>
			<td width=30%>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row['DESC39'];?></td>
			</tr><?php 
	}
}
ob_flush();

?>






