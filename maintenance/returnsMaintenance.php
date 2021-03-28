<?
/**
 * System Name:             Logistics Process Support<br>
 * Program Name:            returnsMaintenance.php<br>
 * Development Reference:   DI932<br>
 * Description:             returnsMaintenance.php allows system administrators to maintain Global Returns Resources<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY   COMMENT<br>                           
 *  --------  ------  -----------  ---------------------<br>
 *  DI932      TS     30/07/2009    Add new class functionality 
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

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=RET')">

<center>
<form method='post' action='returnsMaintenance.php'>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>Global Returns Maintenance</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
if( $action == "add" || $action == "edit" ){
    
    $userArray = get_user_list ();
    
    if( $action == "edit" ){
    $sql = "select ID32 , NAME32, CLAS32, RESP32 FROM CIL32 WHERE ID32='$regionId'";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    //echo $sql;
        while(($row = odbc_fetch_array( $res )) != false ){
            $regionName = $row['NAME32'];
            $responsible = $row['RESP32'];
        } 
        echo "<input type='hidden' name='regionId' value='$regionId'>";	
    }
    ?>
    
    <tr>
        <td class='bold'>Region:</td>
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
    
    $updateSql = "UPDATE CIL32 SET NAME32='$regionName', RESP32=$responsible WHERE ID32=$regionId";

    $updateRes = odbc_prepare( $conn, $updateSql );
    odbc_execute( $updateRes );
    
    ?>
    
    <tr><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td class='bold'>Global Returns Resources have been updated</td></tr>
    <tr><td><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
    <?
    
}elseif( $action == "save" ){
    
    $countRecords = count_records( FACSLIB, "CIL32", "WHERE NAME32='$regionName' AND ACTF32='Y'" );
    
    if( $countRecords > 0 ){
        
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Region already exists</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
        
    }elseif ( $regionName == "" ){
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>You Must Enter a Region Name</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
    
    }else{
        
        //$brandName = get_description_brand_name( $brandCode );
    
        $next_id = get_next_unique_id ( FACSLIB, "CIL32", "ID32", "" );
        $insertSql = "INSERT INTO CIL32 VALUES(";
        $insertSql .= $next_id . ", '$regionName', 9, 'Y', $responsible )";
        
        $insertRes = odbc_prepare( $conn, $insertSql );
        odbc_execute( $insertRes );
        
        //echo $insertSql
        
        
        ?>
        
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Region has been added</td></tr>
        <tr><td class='center'><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
        <?
    }
    
}elseif( $action == "delete"){
    
        $deleteSql = "UPDATE CIL32 SET ACTF32='N' WHERE ID32='$regionId'";
        $deleteRes = odbc_prepare( $conn, $deleteSql );
        odbc_execute( $deleteRes );
        
        ?>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Region has been Deleted</td></tr>
        <tr><td class='center'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=RET');closeW()" value="Close Window"></input></td></tr>
        <?
}
?>
    </table>
    </form>
    </center>
    </body>
    </html>


