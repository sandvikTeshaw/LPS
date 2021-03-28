<?
/**
 * System Name:             Logistics Process Support<br>
 * Program Name:            lpcMaintenance.php<br>
 * Development Reference:   LP0085
 * Description:             lpcMaintenance.php allows system administrators to maintain LPC Resources<br>
 * from CIL49 KEY249= COUNTRY ID
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY   COMMENT<br>
 *  --------  ------  -----------  ---------------------<br>
 */
/**
 */
global $conn, $action, $strc;

include_once '../copysource/config.php';
include '../copysource/functions.php';
include '../../common/copysource/global_functions.php';

if (! $conn) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Logistics Process Support</title>

<style type="text/css">
<!--
@import url(../copysource/styles.css);
-->
</style>
<script language="JavaScript" type="text/javascript"> 
<!--
function closeW()
{
   window.opener='X';
   window.open('','_parent','');
   window.close();
}
// -->

</script>
</head>
<?
include_once '../copysource/header.php';
?>

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=LPC')">

<center>
<form method='post' action='lpcMaintenance.php'>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>LocalPurchase Contacts Maintenance</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
if( $action == "add" || $action == "edit" ){
    
    $userArray = get_user_list ();
    
    if( $action == "edit" ){
//          ---------ID---ContactID--Country-  Name -----------------
    $sql = "SELECT ID49,  USER49,    KEY249,    NAME05  FROM CIL49 T1 "
     . " INNER JOIN HLP05 T2"
     . " ON T1.USER49 = T2.ID05"
     . " WHERE ID49 = '$regionId'";
     
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    //echo $sql;
        while(($row = odbc_fetch_array( $res )) != false ){
            //lp0084_ad $regionName = $row['KEY249'];
            $country = $row['KEY249'];//lp0084_ad
            $responsible = $row['USER49'];
        } 
        echo "<input type='hidden' name='regionId' value='$regionId'>";	
    }
 /*LP0084   ?>
    
    <tr>
        <td class='bold'>Part Class:</td>
        <td>
          <input type='text' name='regionName' value='<?echo $regionName;?>'/>
        </td>
    </tr>
    <?
*///LP00084    
    
    
    ?><?php //LP0085_AD ?>
    <tr><?php //LP0085_AD ?>
        <td class='bold'>Country:</td><?php //LP0085_AD ?>
        <td><?php //LP0085_AD ?>
        <select  style="width: 300px;" name="country"><?php //LP0085_AD ?>
        <option>Select Country</option><?php //LP0085_AD ?>
        <?get_country_listing( $country ); ?><?php //LP0085_AD ?>
        </select><?php //LP0085_AD ?>
        </td><?php //LP0085_AD ?>
    </tr><?php //LP0085_AD ?>
    

    <tr>
        <td class='bold'>Responsible:</td>
        <td>
        <?php //lp0084_ad <select name="responsible"> ?>
        <select style="width: 300px;" name="responsible"><?php //LP0085_AD ?>
        <?show_user_list( $userArray, $responsible); ?>
        </select>
        </td>
    </tr>
    <?php 
    
    ?>
    <tr><td>&nbsp;</td></tr>
    <tr><td><input type='submit' value='Continue'/>
    <?if( $action == "edit" ){ ?>
    <input type='hidden' name='action' value='update'/>
    <?}else{?>
    <input type='hidden' name='action' value='save'/>
    <?}?>
    </td></tr>

    
    
<?
}elseif( $action == "update" ){
    
    $updateSql = "UPDATE CIL49 SET KEY249='$country', USER49=$responsible WHERE ID49=$regionId";
   // echo $updateSql;
    $updateRes = odbc_prepare( $conn, $updateSql );
    odbc_execute( $updateRes );
    
    ?>
    
    <tr><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td class='bold'>Local Purchase Contact have been updated</td></tr>
    <tr><td><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
    <?
    
}elseif( $action == "save" ){
    /* lp0084_ad
    $countRecords = count_records( FACSLIB, "CIL49", "WHERE KEY249='$regionName' AND ACTF32='Y'" );
    
    if( $countRecords > 10000 ){  //limit keept for posible future use
        
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Region already exists</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
        
    }elseif ( $regionName == "" ){ 
    *///lp0084_ad
    //var_dump($class);
    if ( $country == "" ){ //lp0084_ad
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>You Must Chose a Country/Region</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
    }elseif($responsible == "0"){ //lp0084_ad
        ?><tr><td>&nbsp;</td></tr> <?php //lp0084_ad ?>
        <tr><td>&nbsp;</td></tr><?php //lp0084_ad ?>
        <tr><td class='title'>You Must Chose a Responsible Person </td></tr><?php //lp0084_ad ?>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr><?php //lp0084_ad ?>
        <? //lp0084_ad 
        
    
    }else{
        
            
        $next_id = get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" );
        $insertSql = "INSERT INTO CIL49 VALUES(";
        //lp0084_ad      $insertSql .= $next_id . ",0,0,'TSD', '$regionName','','','', $responsible )";
        $insertSql .= $next_id . ",0,0,'LPC', '$country','','','', $responsible )";//lp0084_ad
        
        $insertRes = odbc_prepare( $conn, $insertSql );
        odbc_execute( $insertRes );
        
        //echo $insertSql;
        
        ?>
        
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Local Purchase Contact has been added</td></tr>
        <tr><td class='center'><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
        <?
    }
    
}elseif( $action == "delete"){
    
        $deleteSql = "DELETE CIL49 WHERE ID49='$regionId'";
        $deleteRes = odbc_prepare( $conn, $deleteSql );
        odbc_execute( $deleteRes );
        
        ?>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Link has been Deleted</td></tr>
        <tr><td class='center'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=LPC');closeW()" value="Close Window"></input></td></tr>
        <?
}
?>
    </table>
    </form>
    </center>
    </body>
    </html>


