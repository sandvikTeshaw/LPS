<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            search_supplier.php<br>
 * Development Reference:   LP0077<br>
 * Description:             It search for matched supplier name
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP0077      AD      01/07/2019    GLBAU-17554_LPS Inbound PO not mentioned
 */
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

$pNumber = $_REQUEST['pNumber'];

// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );

set_time_limit ( 300 );



if( !$pNumber || $pNumber == ""  ){
    
    $sql="SELECT supn05 , snam05 FROM PLP05L04
         WHERE  cono05 = 'DI' and dseq05='000' and
            (upper(snam05) like '%".strtoupper(trim($_REQUEST['term']))."%' or
             upper(supn05) like '%".strtoupper(trim($_REQUEST['term']))."%')
        FETCH FIRST 15 ROWS ONLY";
    //echo $sql;
   
}else{
    $sql = "SELECT SUPN05 , SNAM05 FROM PLP05L04 WHERE CONO05='DI' AND DSEQ05='000' "
         . " AND SUPN05 in ( SELECT VNDR01 FROM PMP01L1U WHERE CONO01='DI' AND ITEM01 = '$pNumber')";
    
    
}


$res = odbc_prepare ( $conn, $sql );
odbc_execute ( $res );
$a=array();
while($row = odbc_fetch_array ( $res )){
    $line['id']=trim($row['SUPN05']);
    $line['label']= trim($row['SUPN05']) . " - " . trim($row['SNAM05'] );
    array_push($a, $line);
}


echo json_encode($a);
?>
