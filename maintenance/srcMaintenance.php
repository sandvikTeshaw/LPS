<?
/**
 * System Name:             Logistics Process Support<br>
 * Program Name:            srcMaintenance.php<br>
 * Development Reference:   LP0054
 * Description:             srcMaintenance.php allows system administrators to maintain Sourcing Resources<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY   COMMENT<br>                           
 *  --------  ------  -----------  ---------------------<br>
 *  LP0054     AD     20/06/2019 LP0054 - LPS - Create "Assign to ____" Buttons
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

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=SRC')">

<center>
<form method='post' action='srcMaintenance.php'>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>Sourcing Contacts Maintenance</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
if( $action == "add" || $action == "edit" ){
    
    $userArray = get_user_list ();
    
    if( $action == "edit" ){
    $sql = "select ID32 , NAME32, CLAS32, RESP32 FROM CIL32 WHERE ID32='$regionId'";
//lp0054_Ad---------ID---ContactID--partClass-- Name -----------------
    $sql = "SELECT ID49,  USER49,    KEY249,   NAME05  FROM CIL49 T1 "
     . " INNER JOIN HLP05 T2"
     . " ON T1.USER49 = T2.ID05 AND T1.KEY149 = 'SRC' "
     . " WHERE ID49 = '$regionId'";
     
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    //echo $sql;
        while(($row = odbc_fetch_array( $res )) != false ){
            $regionName = $row['KEY249'];
            $responsible = $row['USER49'];
        } 
        echo "<input type='hidden' name='regionId' value='$regionId'>";	
    }
    ?>
    
    <tr>
        <td class='bold'>Supplier Number: </td>
        <td>
          <input type='text' name='regionName' value='<?echo $regionName;?>'/>
        </td>
    </tr>
    <?
    
   ?>
    <tr>
        <td class='bold'>Responsible:</td>
        <td>
        <select name="responsible">
        <?show_user_list( $userArray, $responsible); ?>
        </select>
        </td>
    </tr>
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
    
    $updateSql = "UPDATE CIL49 SET KEY249='$regionName', USER49=$responsible WHERE ID49=$regionId";

    $updateRes = odbc_prepare( $conn, $updateSql );
    odbc_execute( $updateRes );
    
    ?>
    
    <tr><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td class='bold'>Sourcing Resources have been updated</td></tr>
    <tr><td><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
    <?
    
}elseif( $action == "save" ){
    
    $countRecords = count_records( FACSLIB, "CIL49", "WHERE KEY249='$regionName' AND KEY129='SRC'" );
    
    if( $countRecords > 1){  //limit keept for posible future use
        
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Supplier already exists</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
        
    }elseif ( $regionName == "" ){
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>You Must Enter a Supplier Number</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
    
    }else{
        
        //$brandName = get_description_brand_name( $brandCode );
    
        $next_id = get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" );
        $insertSql = "INSERT INTO CIL49 VALUES(";
        $insertSql .= $next_id . ",0,0,'SRC', '$regionName','','','', $responsible )";
        
        $insertRes = odbc_prepare( $conn, $insertSql );
        odbc_execute( $insertRes );
        
        //echo $insertSql
        
        
        ?>
        
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Sourcing Contact has been added</td></tr>
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
        <tr><td class='title'>Sourcing contact has been Deleted</td></tr>
        <tr><td class='center'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=SRC');closeW()" value="Close Window"></input></td></tr>
        <?
}
?>
    </table>
    </form>
    </center>
    </body>
    </html>


