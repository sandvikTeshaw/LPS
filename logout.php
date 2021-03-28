<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			logout.php<br>
 * Development Reference:	DI868<br>
 * Description:				logout.php logs user out of LPS system and deletes all sessions vars and cookies<br>
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

setcookie("mtp", "", time()-3600);

if (!isset( $conn )) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {
    
} else {
    echo "Connection Failed";
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
//menuFrame( "MTP" );

if( isset( $_SESSION['userID'] ) || isset( $_SESSION['email'] ) ){
	$_SESSION['userID'] = "";
	$_SESSION['name'] = "";
	$_SESSION['email'] = "";
	$_SESSION['password'] = "";
	$_SESSION['authority'] = "";
	$_SESSION['classArray'] = "";
	$_SESSION['typeArray'] = "";
	
	session_destroy();
}
	
?>

<center><h1>You Have Successfully Logged Out</h1></center>

<meta http-equiv="refresh" content="2;url=index.php">


