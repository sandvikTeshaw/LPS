<?
/**
 * System Name:             Logistics Process Support<br>
 * Program Name:            inpMoaintenance.php<br>
 * Development Reference:   LP0087<br>
 * Description:             inpMoaintenance.php allows system administrators to maintain inventory planners<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY                  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 */
/**
 */
global $conn, $action, $accountManager, $bAccount, $CONO, $brand;

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
<title>Inventory Planners Maintenance</title>

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

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=INP')">

<center>
<form method='post' action='inpMaintenance.php'>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td width="20%" >&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>Add Inventory Planner</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
if( $action == "add"  ){
    
    $userArray = get_user_list ();
    
{
    ?>
    
    <tr>
        <td class='bold'>Responsible:</td>
        <td>
          <select name='responsible'>
              <?php show_user_list( $userArray, -1); //-1 no preselected person?>
          </select>
        </td>
    </tr>
        <tr><td><input type='submit' value='Continue'/></tr>
        <input type='hidden' name='action' value='save'/>
    <?
    }
    
}elseif( $action == "save" ){
    
         
    $next_id = get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" );
    $insertSql = "INSERT INTO CIL49 VALUES(";
    $insertSql .= $next_id . ",0,0,'INP', '','','','', $responsible )";
    
    
        $insertRes = odbc_prepare( $conn, $insertSql );
        odbc_execute( $insertRes );
        
        //echo $insertSql
        
        
        ?>
        
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Inventory Planner has been added</td></tr>
        <tr><td class='center'><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
        <?
    
}elseif( $action == "delete"){
    
        $deleteSql = "DELETE CIL49 WHERE ID49='$regionId'";
        $deleteRes = odbc_prepare( $conn, $deleteSql );
        odbc_execute( $deleteRes );
        
        ?>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Inventory Planner has been Deleted</td></tr>
        <tr><td class='center'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=INP');closeW()" value="Close Window"></input></td></tr>
        <?
}
?>
    </table>
    </form>
    </center>
    </body>
    </html>


