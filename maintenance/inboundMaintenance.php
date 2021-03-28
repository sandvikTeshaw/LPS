<?php

?><?
/**
 * System Name:             Logistics Process Support<br>
 * Program Name:            inboundMaintenance.php<br>
 * Development Reference:   D0185<br>
 * Description:             inboundMaintenance.php allows system administrators to maintain Global Returns Resources<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY   COMMENT<br>                           
 *  --------  ------  -----------  ---------------------<br>
 *  D0185      TS     30/07/2009    Add new class functionality 
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

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=INB')">

<center>
<form method='post' action='inboundMaintenance.php'>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>Inbound Default Responsible Maintenance</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
$strc = $_REQUEST['strc'];

if( $action == "add" || $action == "edit" ){
    
    $userArray = get_user_list ();
    
    if( $action == "edit" ){
    $sql = "select ID29 , NAME07, RESP29 FROM CIL29 T1"
     	 . " INNER JOIN CIL07 T2"
     	 . " ON T1.BACK29 = T2.ATTR07"
     	 . " WHERE ID29=$strc";
    
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    //echo $sql;
        while(($row = odbc_fetch_array( $res )) != false ){
            $stockRoomName = $row['NAME07'];
            $responsible = $row['RESP29'];
            $attribId = $row['BACK29'];
        } 
        echo "<input type='hidden' name='strc' value='$strc'>";	
    ?>
	    <tr>
	        <td class='bold'>Stockroom:</td>
	        <td>
	        	<?php echo $stockRoomName;?>
	          <input type='hidden' name='stockRoomId' value='<?echo $attribId;?>'/>
	        </td>
	    </tr>
    <?php 
    }else{
    	?>
    	 <tr>
	        <td class='bold'>Stockroom:</td>
	        <td>
	        	<select name='stockRoomId'>
	        		<option value=''>Select Stock Room</option>
	        <?php 
	        $attribSql = "SELECT * FROM CIL07 T1 "
	        		   . " WHERE PRNT07=475 AND ATTR07 NOT IN "
	        		   . " ( SELECT BACK29 FROM CIL29 T2)"; 
	        		   
	      	$attribRes = odbc_prepare( $conn, $attribSql );
    		odbc_execute( $attribRes );
	        		   
	        while(($attribRow = odbc_fetch_array( $attribRes )) != false ){
	        	?><option value='<?php echo $attribRow['ATTR07'];?>'><?php echo $attribRow['NAME07'];?></option>
	       	<?php
	        }
	        ?></select>
	        <?php 
    } 
	        		  
	        ?>
	        	
	        </td>
	    </tr>
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
    
    $updateSql = "UPDATE CIL29 SET RESP29='$responsible' WHERE ID29=$strc";

    $updateRes = odbc_prepare( $conn, $updateSql );
    odbc_execute( $updateRes );
    
    ?>
    
    <tr><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td class='bold'>Default Responsible has Been Updated</td></tr>
    <tr><td><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
    <?
    
}elseif( $action == "save" ){
    
    $countRecords = count_records( FACSLIB, "CIL29", "WHERE BACK29=$stockRoomId" );
   
    
    if( $countRecords > 0 ){
        
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Responsible already setup</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
        
    }else{
        
    
        $next_id = get_next_unique_id ( FACSLIB, "CIL29", "ID29" );
        $insertSql = "INSERT INTO CIL29 VALUES(";
        $insertSql .= $next_id . ", 7, $responsible, $stockRoomId, '', '' )";
        
        $insertRes = odbc_prepare( $conn, $insertSql );
        odbc_execute( $insertRes );
        
     
        
        
        ?>
        
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Responsible has been added</td></tr>
        <tr><td class='center'><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
        <?
    }
    
}elseif( $action == "delete"){
    
        $deleteSql = "DELETE FROM CIL29 WHERE ID29=$strc";
   
        $deleteRes = odbc_prepare( $conn, $deleteSql );
        odbc_execute( $deleteRes );
        
        ?>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Responsiblity has been Deleted</td></tr>
        <tr><td class='center'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=INB');closeW()" value="Close Window"></input></td></tr>
        <?
}
?>
    </table>
    </form>
    </center>
    </body>
    </html>


