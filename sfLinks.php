<?php 

global $conn, $email, $password, $SITE_TITLE, $mtpUrl, $IMG_DIR, $ALTERNATE_COLOR, $SITENAME, $class, $type, $prty, $stat;

include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

if (!isset($conn)) {
    
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}


$userInfo = user_cookie_info_no_deletes( $_GET['email'] );

$_SESSION ['userID'] =      trim($userInfo ['ID05']);
$_SESSION ['name'] =        trim($userInfo ['NAME05']);
$_SESSION ['companyCode'] = trim($userInfo ['CODE05']);
if( isset( $_SESSION ['email'] )){
    $_SESSION ['email'] =       trim($_SESSION ['email']);
}else{
    $_SESSION ['email'] =       trim($userInfo ['EMAIL05']);
}
$_SESSION ['authority'] =   trim($userInfo ['AUTH05']);

if (!isset($_COOKIE ["mtp"])) {
    setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
}


header("Location: http://lps.sandvik.com/production/smc/global/lps/showTicketDetails.php?from=sfRequest&email=" . $_GET['email'] ."&ID01=" . $_GET['ID01']);

?>