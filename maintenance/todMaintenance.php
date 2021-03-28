<?
/**
 * System Name:             Logistics Process Support<br>
 * Program Name:            todMoaintenance.php<br>
 * Development Reference:   DI868<br>
 * Description:             todMoaintenance.php allows system administrators to maintain TODs<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY                  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  D0395	  TS	  11/10/2011	Removed Back-up functionality
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
<title>Aftermarket Information Center</title>

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

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=B')">

<center>
<form method='post' action='todMaintenance.php'>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>TOD Maintenance</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
if( $action == "addTod" || $action == "editTod" ){
    
    $userArray = get_user_list ();
    
    if( $action == "editTod" ){
    $sql = "select BRAN15, DESC15, TODR15, BTOD15 FROM CIL15L01 WHERE CONO15='$CONO' AND BRAN15='$brand'";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    //echo $sql;
        while(($row = odbc_fetch_array( $res )) != false ){
            $brandCode = $row['BRAN15'];
            $brandName = $row['DESC15'];
            $responsible = $row['TODR15'];
            $backUp = $row['BTOD15'];
        }
    ?>
    <tr>
        <td class='bold'>Brand:</td>
        <td><?echo $brandCode;?></td>
        <input type='hidden' name='brandCode' value='<?echo $brandCode;?>'>
    </tr>
    <?
    }else{
    ?>
    
    <tr>
        <td class='bold'>Brand:</td>
        <td>
          <select name='brandCode'>
              <option value=''>Select Brand</option>
              <?php list_Description_Brands( $brandCode );?>
          </select>
        </td>
    </tr>
    <?
    }
    
    if( $action == "editTod" ){
?>
    <tr>
        <td class='bold'>Brand Name:</td>
        <td><input type='text' name='brandName' value='<?echo trim($brandName);?>'/></td>
    </tr>
    <?} ?>
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
    <?if( $action == "editTod" ){ ?>
    <input type='hidden' name='action' value='updateBrand'/>
    <?}else{?>
    <input type='hidden' name='action' value='saveBrand'/>
    <?}?>
    <input type='hidden' name='brand' value='<?echo $brand;?>'/>
    </td></tr>

    
    
<?
}elseif( $action == "updateBrand" ){
    
    $updateSql = "UPDATE CIL15 SET BRAN15='$brandCode', DESC15='$brandName', TODR15=$responsible, BTOD15=0 WHERE BRAN15='$brand'";
    $updateRes = odbc_prepare( $conn, $updateSql );
    odbc_execute( $updateRes );
    
    ?>
    
    <tr><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td class='bold'>TOD has been updates</td></tr>
    <tr><td><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
    <?
    
}elseif( $action == "saveBrand" ){
    
    $countRecords = count_records( FACSLIB, "CIL15L01", "WHERE CONO15='$CONO' AND BRAN15='$brandCode'" );
    
    if( $countRecords > 0 ){
        
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>Brand code already exists</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
        
    }elseif ( $brandCode == "" ){
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>You Must Select a Brand</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
    
    }else{
        
        $brandName = get_description_brand_name( $brandCode );
    
        $insertSql = "INSERT INTO CIL15 VALUES(";
        $insertSql .= "'$CONO', '$brandCode', '', $responsible, '$brandName', 0 )";
        
        $insertRes = odbc_prepare( $conn, $insertSql );
        odbc_execute( $insertRes );
        
        //echo $insertSql
        
        
        ?>
        
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>TOD has been added</td></tr>
        <tr><td class='center'><input type='button' onclick="closeW()" value="Close Window"></input></td></tr>
        <?
    }
    
}elseif( $action == "deleteTod"){
    
        $deleteSql = "DELETE FROM CIL15 WHERE BRAN15='$brand'";
        $deleteRes = odbc_prepare( $conn, $deleteSql );
        odbc_execute( $deleteRes );
        
        ?>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>TOD has been Deleted</td></tr>
        <tr><td class='center'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=B');closeW()" value="Close Window"></input></td></tr>
        <?
}
?>
    </table>
    </form>
    </center>
    </body>
    </html>


