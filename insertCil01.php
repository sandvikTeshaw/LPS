<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            insertCil01.php<br>
 * Development Reference:   D0129<br>
 * Description:             Creates CIL01 insert and executes<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *   D0129      TS	   10/14/2010   Initial Add
 *   i-2294568  TS	   20/03/2013	Character Set Change
 *   LP0020     TS     15/06/2017   Completetion enhancement
 *   LP0029     TS     04/12/2017   Mass Upload
 *   LP0027     TS     07/12/2017   CIL01 Duplicates Fix
 * 
 */
/**
 */

$LDES01 = strtr($LDES01, $GLOBALS['normalizeSaveChars']);		//i-2294568
$DESC01 = strtr($DESC01, $GLOBALS['normalizeSaveChars']);		//i-2294568

//*****Start LP0029*****
if(  !isset( $_REQUEST['parentId']) || $_REQUEST['parentId'] == "" ){
    
    $parentId = 0;
    $childFlag = 0;
}else{
    $parentId = $_REQUEST['parentId'];
    $childFlag = 1;
    
}

if( !isset( $MODN01 )){
    $MODN01 = "";
}
if( !isset( $IMPT01 )){
    $IMPT01 = "";
}
if( !isset( $RSPN01 )){
    $RSPN01 = "";
}
if( !isset( $SLMN01)){
    $SLMN01 = "";
}
if( !isset( $SNAM01 )){
    $SNAM01 = "";
}
if( !isset( $PREG01 )){
    $PREG01 = "";
}
if( !isset( $PLSC01 )){
    $PLSC01 = "";
}
if( !isset( $STRC01 )){
    $STRC01 = "";
}
if( !isset( $CHCE01 )){
    $CHCE01 = "";
}
if( !isset( $ACTN01 )){
    $ACTN01 = "";
}
if( !isset( $CUSN01 )){
    $CUSN01 = "";
}
if( !isset( $ELVL01)){
    $ELVL01 = "";
}
if( !isset( $DSEQ01 )){
    $DSEQ01 = "";
}
if( !isset( $KEY101 )){
    $KEY101 = "";
}
if( !isset( $KEY201 )){
    $KEY201 = "";
}
if( !isset( $KEY301)){
    $KEY301 = "";
}
if( !isset( $KEY401 )){
    $KEY401 = "";
}
if( !isset( $EFLA01 )){
    $EFLA01 = "";
}
if( !isset( $ENAM01 )){
    $ENAM01 = "";
}
if( !isset( $PCPT01 )){
    $PCPT01 = "";
}
if( !isset( $DCPT01 )){
    $DCPT01 = "";
}
if( !isset( $CPTI01 )){
    $CPTI01 = "";
}
if( !isset( $UPLD01 )){
    $UPLD01 = "";
}
if( !isset( $LDES01)){
    $LDES01 = "";
}
if( !isset( $RQID01)){
    $RQID01 = 0;
}
if( !isset( $PRTY01)){
    $PRTY01 = 0;
}
if( !isset( $TYPE01)){
    $TYPE01 = 0;
}
if( !isset( $CLAS01)){
    $CLAS01 = 0;
}


//*****End LP0029*****

//Create Insert SQL query for CIL01 / main table
//LP0020 - Added $PCPD01,'$PCPT01', $DCPD01, '$DCPT01', $DPFL01, $CPDT01, '$CPTI01'
//LP0029 - $parentId & $childFlag added
$insertCIL01Sql = "";  //LP0027                                     
$insertCIL01Sql = "INSERT INTO CIL01 VALUES( "
				. $nextID . ",$RQID01,'$DESC01', '$LDES01' ," . date( 'Ymd' ) . ", '" . date('His') . "', 0, '', "
				. "$STAT01, $PRTY01, $RSID01, $TYPE01, $CDAT01, $CTIM01, $RESP01, $OCCR01, $DUED01, '$MODN01', '$UPLD01',"
				. "$LSTP01, " . $_SESSION['companyCode'] . ", " . $_SESSION['userID'] . ", '$IMPT01', $CLAS01, '$RSPN01', "
				. "'$SLMN01', '$SNAM01', '$PREG01','$PLSC01', '$STRC01', '$CHCE01', '$ACTN01', '$CUSN01', '$ELVL01', '$DSEQ01',"
				. "'$KEY101', '$KEY201', '$KEY301', '$KEY401', '$EFLA01', '$ENAM01', $EMDA01, $BUYR01, $OWNR01, $POFF01, '$ESTI01',"
				. "$EDAT01, $ESID01, $ESLV01, $PFID01, $UPID01, $OOWN01, $PCPD01,'$PCPT01', $DCPD01, '$DCPT01', $DPFL01, $CPDT01, '$CPTI01', $parentId, $childFlag )";

//Execute insert SQL
if( $TEST_SITE != "Y" ){
    $res = odbc_prepare( $conn, $insertCIL01Sql );
    if( odbc_execute( $res ) ){
        //echo "success<hr>";
        //echo $insertCIL01Sql;
    }else{
        
        echo "Ticket Entry failed" . "<hr>";
        str_replace( "INSERT INTO CIL01", "", $insertCIL01Sql );
        echo $insertCIL01Sql;
        die();
    }
}else{
    
    echo $insertCIL01Sql . "<hr>";
    
}

?>