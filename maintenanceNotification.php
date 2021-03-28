<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            maintenanceNotification.php<br>
 * Development Reference:   DI868<br>
 * Description:             maintenanceNotification.php allows system administrators the maintain all notification matrixes.
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  DI932      TS     30/07/2009    Add new classfunctionality
 * 	D0185	   TS	  27/07/2010	Add Inbound default resources
 * 	D0481	   TS     12/01/2012	Regional Contacts<br>
 *  GLBAU8595  IS     04/10/2015    Change definition of OUTBound planner
 *  LP0054     AD     20/05/2019 LP0054 - LPS - Create "Assign to ____" Buttons
 *  LP0084     AD     30/09/2019 LP0084 - LPS - Allow TSD's to be identified by Item Class and PGMJ Combination
 *  LP0085     AD     08/10/2019    GLBAU-18097_4 LPS Tickets Aurora under regional order process support
 *  lp0087     AD     21/10/2019    Button assign to inventory Planner
 */
/**
 */
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

if (!isset($conn) ) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
}

if (isset($conn)) {
    
} else {
    echo "Connection Failed";
}
if ($email) {
    $userInfo [] = "";
    $userInfo = userInfo ( $email, $password );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['companyCode'] = $userInfo ['CODE05'];
    $_SESSION ['email'] = $email;
    $_SESSION ['password'] = $password;
    
    if (! $_COOKIE ["mtp"]) {
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }
} elseif ($_SESSION ['email']) {
    
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
</head>
<body>
<?

include_once 'copysource/header.php';

//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );


	if( !$_SESSION ['classArray'] ){
	 	$_SESSION ['classArray'] = get_classification_array ();
	}
	if( !$_SESSION ['typeArray'] ){
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
//menuFrame ( $SITENAME );
include_once 'copysource/menu.php';


if( !isset( $maintenanceType ) ){
    $maintenanceType = "";
}

?>

<center>
<table width=95% cellpadding='0' cellspacing='0'>
    <TR>
        <TD>&nbsp</TD>
    </TR>
    <TR>
        <TD class='title'>Notification Maintenance</TD>
    </TR>
</table>
<form method='get' action=''>
<table border='0' width='95%'>
    <tr>
        <td><select name='maintenanceType'>
<?
            echo "<option";
            if ($maintenanceType == "" || $maintenanceType == "A") {
                echo " SELECTED";
            }
            //GLBAU8595 changed outbount planner to outboundplanner misc.
            echo " value='A'>Outbound Planner Misc</option>";
/*LP0084            echo "<option";
            if ($maintenanceType == "B") {
                echo " SELECTED";
            }
            echo " value='B'>TOD's</option>";   *///LP0084_AD
            echo "<option";
            if ($maintenanceType == "PFC") {
                echo " SELECTED";
            }
            echo " value='PFC'>Point of First Contact (PFC)</option>";
            echo "<option";
            if ($maintenanceType == "PRI") {
                echo " SELECTED";
            }
            echo " value='PRI'>Pricing Contacts by Brand</option>";
            echo "<option";
            if ($maintenanceType == "BUY"){
                echo " SELECTED";
            }
            echo " value='BUY'>Purchase Officer</option>";
            
            //DI932 - Added new class functionality
            echo "<option";
            if ($maintenanceType == "RET"){
                echo " SELECTED";
            }
            echo " value='RET'>Global Returns</option>";
            
            //D0185 - Added new Inbound functionality
            echo "<option";
            if ($maintenanceType == "INB"){
                echo " SELECTED";
            }
            echo " value='INB'>Inbound - Default Responsible</option>";
            
             //D0481 - Added regional option
            echo "<option";
            if ($maintenanceType == "REG"){
                echo " SELECTED";
            }
            echo " value='REG'>Regional Order Process</option>";
            
            //GLBAU8595 - Added New Value OutBound Planner as type OBP
            echo "<option";
            if ($maintenanceType == "OBP"){
                echo " SELECTED";
            }
            echo " value='OBP'>OutBound Planner</option>";
            
            echo "<option";//lp0054_ad
            if ($maintenanceType == "TSD"){//lp0054_ad
                echo " SELECTED";//lp0054_ad
            }//lp054_ad
            echo " value='TSD'>Technical Suport Desk</option>";//lp0054_ad
            
            echo "<option";//lp0054_ad
            if ($maintenanceType == "FRE"){//lp0054_ad
                echo " SELECTED";//lp0054_ad
            }//lp054_ad
            echo " value='FRE'>Freight Contacts</option>";//lp0054_ad
            
            echo "<option";//lp0054_ad
            if ($maintenanceType == "SRC"){//lp0054_ad
                echo " SELECTED";//lp0054_ad
            }//lp054_ad
            echo " value='SRC'>Sourcing Contacts</option>";//lp0054_ad
            echo "<option";//lp0085_ad
            if ($maintenanceType == "LPC"){//lp0085_ad
                echo " SELECTED";//lp0085_ad
            }//lp085_ad
            echo " value='LPC'>Local Purchasing Contacts</option>";//lp0085_ad
            
            echo "<option";//lp0087_ad
            if ($maintenanceType == "INP"){//lp0087_ad
                echo " SELECTED";//lp0087_ad
            }//lp087_ad
            echo " value='INP'>Inventory Planner</option>";//lp0087_ad
            
            
    echo "</select>";
    echo "<input type='image' src='$IMG_DIR/go.gif' class='go'></TD>";
echo "</TR>";
echo "</table>";
echo "</form>";

if( $maintenanceType == "A" || $maintenanceType == "" ){
    list_am_table();
}elseif ( $maintenanceType == "B" ){
    list_TOD_table();
}elseif ( $maintenanceType == "PFC" ){
    list_opmg_table();
}elseif ( $maintenanceType == "PRI" ){
    list_pricing_contacts();
    
//DI932 - Added new class functionality
}elseif ( $maintenanceType == "RET" ){
    list_global_returns_contacts(); 

//D0185 - Added for Inbound default resources
}elseif ( $maintenanceType == "INB" ){
    list_inbound_default_contacts();
//D0481 - Added for Regional Contact
}elseif ( $maintenanceType == "REG" ){
    list_regional_contacts();
//GLBAU8595 - Added for Outbound Planner
}elseif ( $maintenanceType == "OBP" ){
    list_planner_table();
}elseif ( $maintenanceType == "TSD" ){//lp0054_ad
    list_tsd_table();//lp0054_ad
}elseif ( $maintenanceType == "FRE" ){//lp0054_ad
    list_freight_table();//lp0054_ad
}elseif ( $maintenanceType == "SRC" ){//lp0054_ad
    list_sourcing_table();//lp0054_ad
}elseif ( $maintenanceType == "LPC" ){//lp0085_ad
    list_localpurchasing_table();//lp0085_ad
}elseif ( $maintenanceType == "INP" ){//lp0087_ad
    list_inventoryplanner_table();//lp0087_ad
}else{
    list_buyers();
}
if( !isset($fromPage) ){
    $fromPage = "";
}
page_footer( $fromPage );
