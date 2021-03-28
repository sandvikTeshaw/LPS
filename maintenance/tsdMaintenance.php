<?
/**
 * System Name:             Logistics Process Support<br>
 * Program Name:            tsdMaintenance.php<br>
 * Development Reference:   LP0054
 * Description:             tsdMaintenance.php allows system administrators to maintain TSD Resources<br>
 * from CIL49 KEY249= ITEM CLASS ID
 * from CIL49 KEY349= ITEM PGMJ ID   **** ASSUMED IF empty field then it mean person is for all ALL PGMJ
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY   COMMENT<br>
 *  --------  ------  -----------  ---------------------<br>
 *  LP0054     AD     20/05/2019 LP0054 - LPS - Create "Assign to ____" Buttons
 *  LP0084     AD     30/09/2019 LP0084 - LPS - Allow TSD's to be identified by Item Class and PGMJ Combination
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

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=TSD')">

<center>
<form method='post' action='tsdMaintenance.php'>
<table width="95%" cellpadding='0' cellspacing='0'>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan='2' class='titleBig'>TSD Maintenance</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<?
$pgmjEdit="ALL PGMJ";
$pgmjall='SELECTED'; //by default * is selected
if( $action == "add" || $action == "edit" ){
    
    $userArray = get_user_list ();
    
    if( $action == "edit" ){
        //LP0084_AD    $sql = "select ID32 , NAME32, CLAS32, RESP32 FROM CIL32 WHERE ID32='$regionId'";
//lp0054_Ad---------ID---ContactID--partClass-PGMJ-  Name -----------------
    $sql = "SELECT ID49,  USER49,    KEY249,  KEY349,   NAME05  FROM CIL49 T1 "
     . " INNER JOIN HLP05 T2"
     . " ON T1.USER49 = T2.ID05"
     . " WHERE ID49 = '$regionId'";
     
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    //echo $sql;
        while(($row = odbc_fetch_array( $res )) != false ){
            //lp0084_ad $regionName = $row['KEY249'];
            $class = $row['KEY249'];//lp0084_ad
            $pgmjEdit = $row['KEY349'];//lp0084_ad 

            if(trim($pgmjEdit)!='ALL PGMJ')$pgmjall="";//lp0084_ad 
 
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
    
    
    ?><?php //LP0084_AD ?>
    <tr><?php //LP0084_AD ?>
        <td class='bold'>Part Class:</td><?php //LP0084_AD ?>
        <td><?php //LP0084_AD ?>
        <select  style="width: 300px;" name="class"><?php //LP0084_AD ?>
        <option>Select Class</option><?php //LP0084_AD ?>
        <?list_Description_Brands( $class ); ?><?php //LP0084_AD ?>
        </select><?php //LP0084_AD ?>
        </td><?php //LP0084_AD ?>
    </tr><?php //LP0084_AD ?>
    
    <tr><?php //LP0084_AD ?>
        <td class='bold'>Part Group Major(PGMJ) :</td><?php //LP0084_AD ?>
        <td><?php //LP0084_AD ?>
        <select multiple style="width: 300px; height:300px" name="pgmj[]"><?php //LP0084_AD ?>
        <option <?php echo $pgmjall; ?> value="ALL PGMJ">ALL PGMJ</option><?php //LP0084_AD ?>
        <?list_Description_PGMJ( $pgmjEdit ); ?><?php //LP0084_AD ?>
        </select><?php //LP0084_AD ?>
        </td><?php //LP0084_AD ?>
    </tr><?php //LP0084_AD ?>

    <tr>
        <td class='bold'>Responsible:</td>
        <td>
        <?php //lp0084_ad <select name="responsible"> ?>
        <select style="width: 300px;" name="responsible"><?php //LP0084_AD ?>
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
    
    $updateSql = "UPDATE CIL49 SET KEY249='$class',KEY349='$pgmj[0]', USER49=$responsible WHERE ID49=$regionId";
   // echo $updateSql;
    $updateRes = odbc_prepare( $conn, $updateSql );
    odbc_execute( $updateRes );
    
    ?>
    
    <tr><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td class='bold'>TSD Resources have been updated</td></tr>
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
    if ( $class == "" ){ //lp0084_ad
        ?><tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>You Must Chose a Part Class</td></tr>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr>
        <?
    }elseif($responsible == "0"){ //lp0084_ad
        ?><tr><td>&nbsp;</td></tr> <?php //lp0084_ad ?>
        <tr><td>&nbsp;</td></tr><?php //lp0084_ad ?>
        <tr><td class='title'>You Must Chose a Responsible Person </td></tr><?php //lp0084_ad ?>
        <tr><td class='center'><input type='button' onclick="javascript:history.go(-1)" value="Back"></input></td></tr><?php //lp0084_ad ?>
        <? //lp0084_ad 
        
    
    }else{
        
        //$brandName = get_description_brand_name( $brandCode );
        foreach ($pgmj as $line){//lp0084_ad
            
        $next_id = get_next_unique_id ( FACSLIB, "CIL49", "ID49", "" );
        $insertSql = "INSERT INTO CIL49 VALUES(";
        //lp0084_ad      $insertSql .= $next_id . ",0,0,'TSD', '$regionName','','','', $responsible )";
        $insertSql .= $next_id . ",0,0,'TSD', '$class','$line','','', $responsible )";//lp0084_ad
        
        $insertRes = odbc_prepare( $conn, $insertSql );
        odbc_execute( $insertRes );
        
        //echo $insertSql;
        }//lp0084_ad
        
        ?>
        
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='title'>TSD has been added</td></tr>
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
        <tr><td class='center'><input type='button' onclick="opener.location=('../maintenanceNotification.php?maintenanceType=TSD');closeW()" value="Close Window"></input></td></tr>
        <?
}
?>
    </table>
    </form>
    </center>
    </body>
    </html>


