<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            s21SupplierFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to s21 supplier and supplier information
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  
 **/
/**
 * Function retunrs an array of supplier cost information
 *
 * @param string $partNumber
 * @param string $supplierNumber
 * @return array of Supplier Cost Information
 */
function get_supplier_cost($partNumber, $supplierNumber) {
    global $conn, $CONO;

    $sql = "SELECT CURP01, CURC01, ";
    $sql .= " VCAT01";
    $sql .= " FROM SUPITEMS WHERE CONO01='DI' AND VNDR01='$supplierNumber' AND ITEM01='$partNumber'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
 
    while ( $row = odbc_fetch_array ( $res ) ) {
        $supplierCost['price'] = number_format ( $row['CURP01'], 2 );
        $supplierCost['currency'] = $row ['CURC01'];
        $supplierCost['itemNumber'] = $row ['VCAT01'];

    }
    if( !isset($supplierCost['price']) ){
        $supplierCost['price'] = 0;
    }
    if( !isset($supplierCost['currency']) ){
        $supplierCost['currency'] = "";
    }
    if( !isset($supplierCost['itemNumber']) ){
        $supplierCost['itemNumber'] = "";
    }
    return $supplierCost;

}

/**
 * Function returns supplier information defined by $partNumber and $orderNumber
 *
 * @param integer $id
 * @param string $partNumber
 * @param string $orderNumber
 * @return array of Supplier Information
 */
function get_supplier_email_vars($id, $partNumber, $orderNumber) {
    global $conn, $CONO;
    
    $supplierArray [] = "";
    
    $snum_sql = "SELECT DSSP35 FROM PARTS WHERE CONO35='$CONO' AND PNUM35 = '" . trim ( $partNumber ) . "' ORDER BY ESDT35 DESC FETCH FIRST 5 ROWS ONLY";
    $snumRes = odbc_prepare ( $conn, $snum_sql );
    odbc_execute ( $snumRes );
    while ( $row = odbc_fetch_array ( $snumRes ) ) {
        $SNUM = trim ( $row ['DSSP35'] );
    }
    
    $info_sql = "SELECT SNAM05 FROM PLP05 WHERE CONO05='$CONO' AND SUPN05='" . trim ( $partNumber ) . "' AND DSEQ05 = '000' FETCH FIRST 10 ROWS ONLY";
    $infoRes = odbc_prepare ( $conn, $info_sql );
    odbc_execute ( $infoRes );
    while ( $row = odbc_fetch_array ( $infoRes ) ) {
        $SNAM = trim ( $row ['SNAM05'] );
    }
    
    $pricing_sql = "SELECT CURP01, CURC01  FROM PMP01 WHERE CONO01 = '$CONO' AND ITEM01 = '" . trim ( $partNumber ) . "' AND VNDR01 = '$SNUM' ORDER BY LDTE01 DESC FETCH FIRST 1 ROW ONLY";
    $pricingRes = odbc_prepare ( $conn, $pricing_sql );
    odbc_execute ( $pricingRes );
    while ( $row = odbc_fetch_array ( $pricingRes ) ) {
        $CURP = trim ( $row ['CURP01'] );
        $CURC = trim ( $row ['CURC01'] );
    }
    
    $CURP = number_format ( $CURP, 2, '.', '' );
    
    $supplierArray ['SNUM'] = $SNUM;
    $supplierArray ['SNAM'] = $SNAM;
    $supplierArray ['CURP'] = $CURP;
    $supplierArray ['CURC'] = $CURC;
    
    $sql_po = "SELECT cast( SREF71 as CHAR(20) CCSID 285) AS SREF71, cast( SUPT71 as CHAR(1) CCSID 285) AS SUPT71 FROM INP71L01 WHERE CONO71 = '$CONO' AND CATN71 = '" . trim ( $partNumber ) . "' AND substr(DREF71,1,7)='$orderNumber' FETCH FIRST 10 ROWS ONLY";
    $poRes = odbc_prepare ( $conn, $sql_po );
    odbc_execute ( $poRes );
    //echo $sql_po;
    while ( $row = odbc_fetch_array ( $poRes ) ) {
        $PO = substr ( trim ( $row ['SREF71'] ), 0, 7 );
        $FLAG = trim ( $row ['SUPT71'] );
        
        $DINUM = $PO;
        $Flag_count = 0;
        while ( $FLAG == 'D' && $Flag_count <= 10 ) {
            $Flag_count ++;
            $DRP_NUM = $PO;
            
            $drp_sql = "SELECT cast( SREF71 as CHAR(20) CCSID 285) AS SUPT71, cast( SUPT71 as CHAR(1) CCSID 285) AS SUPT71 FROM INP71L01 WHERE CONO71 = '$CONO' AND CATN71 = '" . trim ( $partNumber ) . "' AND substr(DREF71,1,7)='$PO' FETCH FIRST 10 ROWS ONLY";
            $drpRes = odbc_prepare ( $conn, $drp_sql );
            odbc_execute ( $drpRes );
            
            while ( $row = odbc_fetch_array ( $drpRes ) ) {
                $PO = substr ( trim ( $row ['SUPT71'] ), 0, 7 );
                $FLAG = trim ( $row ['SUPT71'] );
            }
        }
        
    }
    if ($PO == "") {
        $PO = 0;
    }
    if ($DRP_NUM == "") {
        $DRP_NUM = 0;
    }
    
    $follow_sql = "SELECT DTLC03 FROM PMP03 WHERE CONO03 = '$CONO' AND ITEM03 = '" . trim ( $partNumber ) . "' AND ORDN03='$PO' AND DTLC03 <> 9999999 ORDER BY DTLC03 ASC FETCH FIRST 10 ROWS ONLY";
    $followRes = odbc_prepare ( $conn, $follow_sql );
    odbc_execute ( $followRes );
    while ( $row = odbc_fetch_array ( $followRes ) ) {
        $FOLLOW_DATE = trim ( $row ['DTLC03'] );
    }
    if ($FOLLOW_DATE == "") {
        $FOLLOW_DATE = 0;
    }
    
    $supplierArray ['DRP'] = $DRP_NUM;
    $supplierArray ['PO'] = $PO;
    $supplierArray ['FOLLOW'] = $FOLLOW_DATE;
    
    $due_sql = "SELECT PDAT09 FROM PMP09 WHERE CONO09 = '$CONO' AND ITEM09 = '" . trim ( $partNumber ) . "' AND ORDN09='$PO' FETCH FIRST 10 ROWS ONLY";
    $dueRes = odbc_prepare ( $conn, $due_sql );
    odbc_execute ( $dueRes );
    while ( $row = odbc_fetch_array ( $dueRes ) ) {
        $DUE_DATE = trim ( $row ['PDAT09'] );
    }
    if ($DUE_DATE == "") {
        $DUE_DATE = 0;
    }
    
    $CILJ02_sql = "SELECT PDES35, PLAN09, PLNN06, ";
    $CILJ02_sql .= "cast( VCAT03 as CHAR(20) CCSID 285) AS VCAT03, PLAN09, SSEQ02 ";
    $CILJ02_sql .= "FROM CIL01J36 ";
    $CILJ02_sql .= "WHERE CONO35 = '$CONO' AND PNUM35  = '" . strtoupper ( trim ( $partNumber ) ) . "' AND ORDN09  = '" . strtoupper ( $PO ) . "' FETCH FIRST 10 ROWS ONLY";
    $cilRes = odbc_prepare ( $conn, $CILJ02_sql );
    odbc_execute ( $cilRes );
    //echo $CILJ02_sql;
    while ( $row = odbc_fetch_array ( $cilRes ) ) {
        $PDES35 = trim ( $row ['PDES35'] );
        $PLAN09 = trim ( $row ['PLAN09'] );
        $PLNN06 = trim ( $row ['PLNN06'] );
        $VCAT03 = trim ( $row ['VCAT03'] );
        $SSEQ02 = trim ( $row ['PLAN09'] );
    }
    if ($PDES35 == "") {
        $PDES35 = 0;
    }
    if ($VCAT03 == "") {
        $VCAT03 = 0;
    }
    if ($PLAN09 == "" && $PLNN06 == "") {
        $PLAN09 = 0;
    }
    
    $supplierArray ['DRP'] = $DRP_NUM;
    $supplierArray ['PO'] = $PO;
    $supplierArray ['FOLLOW'] = format_JBA_Date ( $FOLLOW_DATE );
    $supplierArray ['DUE'] = format_JBA_Date ( $DUE_DATE );
    
    $supplierArray ['DESCRIPTION'] = $PDES35;
    $supplierArray ['VCAT'] = $VCAT03;
    $supplierArray ['PLANNER'] = $PLAN09 . "-" . $PLNN06;
    
    return $supplierArray;
}

/**
 * Function creates and returns an array of vendor email information
 *
 * @param integer $ticketId
 * @return array of vendor email information
 */
function get_vendor_email_details($ticketId) {
    global $conn, $CONO;
    
    $sql = "select ENAM24, EMAIL24, EDAT24, SID24, TEXT24, STID24 FROM CIL24L01 WHERE CAID24=$ticketId ORDER BY EDAT24, STID24";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    while ( $row = odbc_fetch_array ( $res ) ) {
        return $row;
    }
    
}