<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            index.php<br>
 * Development Reference:   DI868<br>
 * Description:             LPS Application main page
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  D0097      TJS    22/04/2010  Change for escalation mods<br>
 *  D0281	   TS	  11/02/2011  Deleted users can log into system<br>
 *  D0301 	   TS	  18/03/2011  LPS Performance Change<br>
 *  D0247 	   TS	  01/04/2011  Link LPS User profile to Notification Screen
 *  D0455	   TS	  15/11/2011  LPS Browser Compatability
 *  
 * 
 */
/**
 */

global $conn, $email, $password, $SITE_TITLE, $mtpUrl, $IMG_DIR, $ALTERNATE_COLOR, $SITENAME, $class, $type, $prty, $stat;

include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';


//D0301 - Added to compress output to remove all white space
ob_start("compressBuffer");


//echo($_SERVER[�HTTP_USER_AGENT�]) . "<hr>";

if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
}

if ($conn) {

} else {
    echo "Connection Failed";
}

if (isset($email)) {
    $userInfo [] = "";
    //D0281 - Changed function
    $userInfo = user_info_no_deletes ( $email, $password );
    $_SESSION ['userID'] =      trim($userInfo ['ID05']);
    $_SESSION ['name'] =        trim($userInfo ['NAME05']);
    $_SESSION ['companyCode'] = trim($userInfo ['CODE05']);
    $_SESSION ['authority'] =   trim($userInfo ['AUTH05']);
    $_SESSION ['email'] =       trim($email);
    $_SESSION ['password'] =    trim($password);
    
    if ( !isset( $_COOKIE ["mtp"]) ){
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }
} elseif( isset($_SESSION ['email']) ) {

    $userInfo [] = "";
    //D0281 - Changed function
    $userInfo = user_cookie_info_no_deletes( $_SESSION ['email'] );
    $_SESSION ['userID'] =      trim($userInfo ['ID05']);
    $_SESSION ['name'] =        trim($userInfo ['NAME05']);
    $_SESSION ['companyCode'] = trim($userInfo ['CODE05']);
    $_SESSION ['email'] =       trim($_SESSION ['email']);
    $_SESSION ['authority'] =   trim($userInfo ['AUTH05']);
    
    if (!isset($_COOKIE ["mtp"])) {
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }

} elseif (isset($_COOKIE ["mtp"])) {
    
    $userInfo [] = "";
    //D0281 - Changed function
    $userInfo = user_cookie_info_no_deletes ( $_COOKIE ["mtp"] );
    $_SESSION ['userID'] =      trim($userInfo ['ID05']);
    $_SESSION ['name'] =        trim($userInfo ['NAME05']);
    $_SESSION ['companyCode'] = trim($userInfo ['CODE05']);
    $_SESSION ['authority'] =   trim($userInfo ['AUTH05']);
    $_SESSION ['email'] =       trim($_COOKIE ["mtp"]);
} else {

}


//D0097 - Change Javascript function userMaintenance to send to different URL
//D0455 - Added <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /> to head section
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        <?echo $SITE_TITLE;?>
    </title>
    <style type="text/css">
        @import url("copysource/styles.css");
    </style>
    <script type="text/javascript">
        function setFocus() {
            document.frm.email.focus();
        }

        function unSelect() {}

        function userMaintenance(parm) {

            var siteUrl = this.frm.siteUrl.value;

            if (parm == "register"){
                window.location.href = siteUrl + '/maintenanceUserAdd.php?action=' + parm
            }else{
                window.location.href = siteUrl + '/lostPassword.php?action=' + parm
           	}


        }

        function profileValidation() {

            var failedFlag = 0;
            var alertMessage = "Please fill in the following information:\n\r";

            if (document.getElementById('super').value == 0 || document.getElementById('super').value == "") {
                alertMessage = alertMessage + "Select Supervisor\n\r";
                failedFlag = 1;
            }
            if (document.getElementById('backup').value == 0 || document.getElementById('backup').value == "") {
                alertMessage = alertMessage + "Select Back-up\n\r";
                failedFlag = 1;
            }
            if (document.getElementById('name').value == 0 || document.getElementById('name').value == "") {
                alertMessage = alertMessage + "Enter Name\n\r";
                failedFlag = 1;
            }
            if (failedFlag == 1) {
                alert(alertMessage);
                return false;
            } else {
                return true;
            }
        }
    </script>
</head>
<?
if (!isset($email) && !isset($_SESSION ['email'])) {
    ?>

<body onload='setFocus()'>
    <?
}else{
	
	//D0247 - Added to check to see if has supervisor or not, if not require profileValidation to be entered.
	//D0247 - if( get_supervisor_id( $_SESSION['userID']) == 0 ){ 	- 	Removed due to change of logic
	    		
	if( $userInfo ['RSRC05'] == "V" ){		//D0247 - Added to validate without changing the current escalation paths.
		include_once 'profileValidation.php';
	    		
	    die();
	}
    ?>

    <body>
        <?
}


if ( isset( $_SESSION ['userID']) ) {
    
    include_once 'copysource/header.php';
    
    if (!isset( $_SESSION ['classArray']) ) {
        $_SESSION ['classArray'] = get_classification_array ();
    }
    if( !isset($_SESSION ['typeArray'])){
        $_SESSION ['typeArray'] = get_typeName_array ();
    }
    
    	include_once 'copysource/menu.php';

    //menuFrame ( $SITENAME );
}

    ?>
        <center>
            <form method='post' name='frm' action='index.php'>
                <table border='0' width='100%'>

                    <tr>
                        <td><input type='hidden' name='siteUrl' value='<?
    echo $mtpUrl;
    ?>' />&nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <?
    if (!isset($email) && !isset( $_SESSION ['email'])) {
    ?>
                        <td colspan='3' class='center'><img src='<?
        echo $IMG_DIR;
        ?>/lpsImg.gif' /></td>
                        <?
    }else{
            ?>
                        <td colspan='3' class='center'><img src='<?
        echo $IMG_DIR;
        ?>/lpsImgSmall.gif' /></td>
                        <?
    
    }
    if( isset($_SESSION['email']) ){
?>
                    </tr>
                </table>
                <center>
                    <table width=70% cellpadding=0 cellspacing=0>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <?
        //$userArray = get_user_info_array_by_id();
        $messageSql = "SELECT MESS28, DATE28, AUTH28, TYPE28 FROM CIL28L00 WHERE DEL28 = 'N' AND EXPR28>=" . date( 'Ymd' ) . " ORDER BY TYPE28, PREC28, ID28";
        // $messageRes = db2_prepare( $conn, $messageSql );
        $messageRes = odbc_prepare( $conn, $messageSql );
        odbc_execute( $messageRes );
        $prevType =  "";
        $messageCounter = 0;
        while(($messageRow = odbc_fetch_array( $messageRes )) != false )
        {
            $messageCounter++;
            if( $messageRow['TYPE28'] != $prevType || $prevType == "" ){
                $messageArray = get_message_type_array();
                echo "<tr><td>&nbsp</td></tr>";
                echo "<tr class='headerBlue'>";
                    echo "<td class='headerWhite' colspan='3'>" . $messageArray[$messageRow['TYPE28']] . "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo "<td width=5%>&nbsp</td>";
                    echo "<td class='alternateBack' width=65% bgcolor=>Message</td>";
                    //echo "<td class='alternateBack' width=20%>From</td>";
                    echo "<td class='alternateBack' width=10%>Date</td>";
                echo "</tr>";
                $prevType = $messageRow['TYPE28'];
            }
            if( $messageCounter %2 ){
                $backColor = "#FFFFFF";
            }else{
                $backColor = $ALTERNATE_COLOR;
            }
                echo "<tr>";
                echo "<td>&nbsp</td>";
                echo "<td bgcolor='$backColor'>" . $messageRow['MESS28'] . "</td>";
                //echo "<td bgcolor='$backColor' valign='top'>" . $userArray[$messageRow['AUTH28]]['name'] . "</td>";
                $showDate = formatDate($messageRow['DATE28']);
                echo "<td bgcolor='$backColor' valign='top'>" . $showDate . "</td>";
            echo "</tr>";
        }
        ?>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>

                        <?
    }
if (!isset($email) && !isset($_SESSION ['email'])) {
    ?>

                        <center>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td width=35%>&nbsp;</td>
                                <td align='left'>Email:</td>
                                <td><input type='text' name='email' value='@sandvik.com' /></td>
                            </tr>
                            <tr>

                                <td>&nbsp;</td>
                                <td align='left'>Password:</td>
                                <td><input type='password' name='password' value='' /></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td colspan='2' align='left'><input type=submit value='Continue' /></td>
                            </tr>
            </form>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>

            <form name='actionForm'>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan='3' align='center'><input type='button' value='Register For <?
    echo $SITENAME;
    ?> Account' onclick="userMaintenance('register')"; /> <input type='button' value='Lost Password' onclick="userMaintenance('password')"; /></td>
                </tr>
        </center>
        </form>
        <!--
     <tr>
        <td>&nbsp;</td>
    </tr>
     <tr>
        <td>&nbsp;</td>
    </tr>
    
    <tr>
    <td>
    <center><font size='10'>LPS is currently under maintenance<br/>We are working hard to restore service.</font></center>
    </td>
    </tr>
    -->
        <?

    page_footer ( "login" );
} else {
    
    
    if ($userInfo ['ID05'] || $_SESSION ['userID']) {
        if (! $_SESSION ['userID']) {
            $_SESSION ['userID'] = $userInfo ['ID05'];
        }
        
        update_last_login();
        
        //display_favorites ( $_SESSION ['userID'], $userInfo ['EMAIL05'] );
    } else {
        setcookie("mtp", "", time()-3600);
        $_SESSION['userID'] = "";
        $_SESSION['name'] = "";
        $_SESSION['email'] = "";
        $_SESSION['password'] = "";
        $_SESSION['authority'] = "";
        $_SESSION['classArray'] = "";
        $_SESSION['typeArray'] = "";
        
        if( $email ){
            error_mssg ( "INVALID" );
        }else{
            error_mssg ( "NONE" );
        }
        
    }
    
    page_footer ( "main" );
}
?>
        </table>
        </center>
    </body>
</html>
