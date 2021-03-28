<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            define2.php<br>
 * Development Reference:   LP0055<br>
 * Description:             Dynamical definition
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP0055      KS    12/04/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0068      AD    24/04/2019  GLBAU-16824_LPS Vendor Change
 */
/**
 */

include_once 'config.php';

$GMMSCLTU = array();
$GMMVC= array();//LP0068_AD

if (!isset($conn)){
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect (SYSTEM, DB_USER, DB_PASS, $Options);
    $conn = odbc_connect (SYSTEM, DB_USER, DB_PASS);
}


$sql = "SELECT ATTR07 FROM CIL07 WHERE TYPE07= 130 AND PRNT07 = 0 ORDER BY ATTR07 FETCH FIRST 9 ROWS ONLY ";
$res = odbc_prepare($conn, $sql);
odbc_execute($res);

while ($row = odbc_fetch_array($res)){
    array_push($GMMSCLTU, $row['ATTR07']);
}

//LP0082_AD $sql = "SELECT ATTR07 FROM CIL07 WHERE TYPE07= 133 AND PRNT07 = 0 ORDER BY ATTR07 FETCH FIRST 9 ROWS ONLY ";//LP0068_AD
$sql = "SELECT ATTR07 FROM CIL07 WHERE TYPE07= 133 AND PRNT07 = 0 ORDER BY ATTR07 FETCH FIRST 15 ROWS ONLY ";//LP0082_AD
$res = odbc_prepare($conn, $sql);//LP0068_AD
odbc_execute($res);//LP0068_AD

while ($row = odbc_fetch_array($res)){//LP0068_AD
    array_push($GMMVC, $row['ATTR07']);//LP0068_AD
}//LP0068_AD
$temp=array();
$temp[0]=$GMMVC[1];//supplier lp0082_ad
$temp[1]=$GMMVC[0];//part lp0082_ad
$temp[2]=$GMMVC[9];//liability lp0082_ad
$temp[3]=$GMMVC[11];//coo lp0082_ad
$temp[4]=$GMMVC[12];//vdsr lp0082_ad
$temp[5]=$GMMVC[13];//stockfrom lp0082_ad
$temp[6]=$GMMVC[14];//stockto lp0082_ad
$temp[7]=$GMMVC[2];//minq lp0082_ad
$temp[8]=$GMMVC[3];//reorder lp0082_ad
$temp[9]=$GMMVC[4];//ref lp0082_ad
$temp[10]=$GMMVC[5];//curn lp0082_ad
$temp[11]=$GMMVC[6];//cost lp0082_ad
$temp[12]=$GMMVC[7];//disc lp0082_ad
$temp[13]=$GMMVC[8];//ltime lp0082_ad
$GMMVC=$temp;


?>