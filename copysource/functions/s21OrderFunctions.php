<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            s21OrderFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related System21 Orders and Invoices
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
 * Function retrieves and returns and array of Purchase Order Information
 *
 * @param string Part Number $PNUM35
 * @param string DI Order Number $DINUM
 * @return Array of Purchase order Information
 */
function get_po_number($PNUM35, $DINUM) {
    global $conn, $CONO;
    
    $poSql = "SELECT cast( SREF71 as CHAR(20) CCSID 285) AS SREF71, cast( SUPT71 as CHAR(20) CCSID 285) AS SUPT71 "
        . " FROM INP71 WHERE CONO71 = '$CONO' AND CATN71 = '$PNUM35' AND substr(DREF71,1,7)='$DINUM'";

        $poRes = odbc_prepare ( $conn, $poSql );
        odbc_execute ( $poRes );
        
        $COUNTER = 0;
        while ( $poRow = odbc_fetch_array ( $poRes ) ) {
            $PO = substr ( $poRow ['SREF71'], 0, 7 );
            $FLAG = $poRow ['SUPT71'];
            $DINUM = $PO;
            
            while ( trim ( $FLAG ) == "D" && $COUNTER <= 10 ) {
                
                $DRP_NUM = $PO;
                $flagReturn = po_flag_info ( $PNUM35, $PO );
                
                $PO = $flagReturn ['PO'];
                $FLAG = $flagReturn ['FLAG'];
                
                $COUNTER ++;
            }
        }

            if( isset( $PO )){
                $poVals['PO'] = $PO;
            }else{
                $poVals['PO'] = "";
            }
            if( isset( $DRP_NUM )){
                $poVals['DRP'] = $DRP_NUM;
            }else{
                $poVals['DRP'] = "";
            }
            if( isset( $FLAG )){
                $poVals['FLAG'] = $FLAG;
            }else{
                $poVals['FLAG'] = "";
            }
   
        
        return $poVals;
}

/**
 * Function retrieves and returns array of purchase order flags
 *
 * @param string $PNUM35
 * @param string $PO
 * @return Array of purchase order flags
 */
function po_flag_info($PNUM35, $PO) {
    global $CONO, $conn;
    $flagSql = "SELECT cast( SREF71 as CHAR(20) CCSID 285) AS SREF71, cast( SUPT71 as CHAR(20) CCSID 285) AS SUPT71 FROM INP71 WHERE CONO71 = '$CONO' AND CATN71 = '$PNUM35' AND substr(DREF71,1,7)='$PO'";
    $flagRes = odbc_prepare ( $conn, $flagSql );
    odbc_execute ( $flagRes );
    
    //echo $flagSql;
    while ( $flagRow = odbc_fetch_array ( $flagRes ) ) {
        $PO = substr ( $flagRow ['SREF71'], 0, 7 );
        $FLAG = $flagRow ['SUPT71'];
    }
    if( isset( $PO) ){
        $flagVals ['PO'] = $PO;
    }else{
        $flagVals ['PO'] = "";
    }
    if( isset( $FLAG) ){
        $flagVals ['FLAG'] = $FLAG;
    }else{
        $flagVals ['FLAG'] = "";
    }

    return $flagVals;
}

/**
 * Get and return due date information
 *
 * @param Part Number $pnum
 * @param Purchase Order $PO
 * @return Array of Due Date Information
 */
//D109 - Change to get Receipt
//D0171 - Fix SQL to return correct Receipt date
function get_receipt_date_info($pnum, $PO) {
    global $CONO, $conn;
    //Out parms RECD09, CON_FLAG
    
    // - D0171 - Removed -
    //$receiptDateSql = "SELECT cast( RECD09 as CHAR(20) CCSID 285) AS RECD09, cast( CFLG09 as CHAR(20) CCSID 285) AS CFLG09 FROM PMP09L11 WHERE CONO09 = '$CONO' AND ITEM09 = '$pnum' AND ORDN09='" . trim ( $PO ) . "' FETCH FIRST 1 ROW ONLY";
    
    //D0171 - Added new query
    $receiptDateSql = "SELECT cast( RECD09 as CHAR(20) CCSID 285) AS RECD09, cast( CFLG09 as CHAR(20) CCSID 285) AS CFLG09, cast( DUED09 as CHAR(20) CCSID 285) AS DUED09 "
        . " FROM PMP09L11"
            . " WHERE CONO09 = '$CONO' AND ITEM09 = '$pnum' AND ORDN09='" . trim ( $PO ) . "'"
                . " ORDER BY RECD09 DESC FETCH FIRST 1 ROW ONLY";
                
                $rRes = odbc_prepare ( $conn, $receiptDateSql );
                odbc_execute ( $rRes );
                
                //echo $dueDateSql;
                
                $rInfo [] = "";
                $rInfo ['RDAT'] = "";
                $rInfo ['DDAT'] = "";
                $rInfo ['FLAG'] = "";
                while ( $rRow = odbc_fetch_array ( $rRes ) ) {
                    $rInfo ['RDAT'] = $rRow ['RECD09'];
                    $rInfo ['DDAT'] = $rRow ['DUED09'];
                    $rInfo ['FLAG'] = $rRow ['CFLG09'];
                    
                }
                return $rInfo;
}

/**
 * Get and Return Follow-Up date
 *
 * @param Part Number $pnum
 * @param Purchase Order $po
 * @return Follow-up date
 */
function get_follow_up_date($pnum, $po) {
    global $conn, $CONO;
    //Out Parms DTLC03
    $followSql = "SELECT DTLC03 FROM PMP03V01 WHERE ITEM03 = '$pnum' AND ORDN03='$po' ORDER BY DTLC03 ASC FETCH FIRST 1 ROW ONLY";
    
    $followRes = odbc_prepare ( $conn, $followSql );
    odbc_execute ( $followRes );
    
    //echo $followSql;
    
    
    while ( $followRow = odbc_fetch_array ( $followRes ) ) {
        return $followRow ['DTLC03'];
    }
}

/*
 * Returns an array of receiving information
 *
 * @parm integer $partNumber Part Number entered by user
 * @parm varchar $orderNumber Order Number from iSeries
 * @return $receivingInfo an array of receiving information from Index VENDRL01
 */
function get_receiving_info($partNumber, $orderNumber) {
    global $CONO, $conn;
    //Out Parms PDES35, VNDR09, PLAN09, PLNN06, SNAM05, VCAT03, PRUM09, ORDP09, SSEQ02
    if ($_SESSION ['userID'] == "1021") {
        echo "Before Recieving Info SQL:" . date ( 'H:i:s' );
    }
    
    if ($orderNumber) {
        $receiverSql = "SELECT PDES35, VNDR09, PLAN09, PLNN06, SNAM05, cast( VCAT03 as CHAR(20) CCSID 285) AS VCAT03";
        $receiverSql .= ", PRUM09, ORDP09, DICD02, SSEQ02";
        $receiverSql .= " FROM VENDRL01 WHERE CONO35 = '$CONO'";
        $receiverSql .= " AND PNUM35 = '" . strtoupper ( $partNumber ) . "' AND";
        $receiverSql .= " ORDN09 = '" . strtoupper ( $orderNumber ) . "' FETCH FIRST 1 ROW ONLY";
    } else {
        //$receiverSql = "SELECT PDES35, VNDR09, PLAN09, PLNN06, SNAM05, cast( VCAT03 as CHAR(20) CCSID 285) AS VCAT03";
        //$receiverSql .= ", PRUM09, ORDP09, DICD02, SSEQ02";
        //$receiverSql .= " FROM VENDRL01 WHERE CONO35 = '$CONO'";
        //$receiverSql .= " AND PNUM35 = '" . strtoupper ( $partNumber ) . "' FETCH FIRST 1 ROW ONLY";
        
        $receiverSql    = "SELECT PDES35, VNDR09, PLAN09, PLNN06, SNAM05, cast( VCAT03 as CHAR(20) CCSID 285) AS VCAT03, PLAN35"
            . ", PRUM09, ORDP09"
                . " FROM INP35 T1"
                    . " INNER JOIN PMP09 T2"
                        . " ON T1.PNUM35 = T2.ITEM09 AND T1.PLAN35 = T2.PLAN09"
                            . " INNER JOIN PMP06 T3"
                                . " ON T1.PLAN35 = T3.PLAN06"
                                    . " INNER JOIN PLP05 T4"
                                        . " ON T2.VNDR09 = T4.SUPN05"
                                            . " INNER JOIN PMP03 T5"
                                                . " ON T2.ITEM09 = T5.ITEM03 AND T2.ORDN09 = T5.ORDN03"
                                                    . " WHERE CONO35 = '$CONO'"
                                                    . " AND PNUM35 = '" . strtoupper ( $partNumber ) . "' FETCH FIRST 1 ROW ONLY";
                                                    
    }

    
    $receiveRes = odbc_prepare ( $conn, $receiverSql );
    odbc_execute ( $receiveRes );
    
    
    $receiveInfo [] = "";
    $count = 0;
    $receiveInfo['PDES'] = "";
    $receiveInfo ['VNDR'] = "";
    $receiveInfo ['PLAN'] = "";
    $receiveInfo ['PRUM'] = "";
    $receiveInfo ['ORDP'] = "";
    $receiveInfo ['PLNN'] = "";
    $receiveInfo ['SNAM'] = "";
    $receiveInfo ['VCAT'] = "";
    $receiveInfo ['DICD'] = "";
    $receiveInfo ['SSEQ'] = "";
    
    
    while ( $receiveRow = odbc_fetch_array ( $receiveRes ) ) {
        $count++;
        if( isset( $receiveRow ['PDES35'] ) ){
            $receiveInfo ['PDES'] = $receiveRow ['PDES35'];
        }else{
            $receiveInfo ['PDES'] = "";
        }
        if( isset( $receiveRow ['VNDR09'] ) ){
            $receiveInfo ['VNDR'] = $receiveRow ['VNDR09'];
        }else{
            $receiveInfo ['VNDR'] = "";
        }
        if( isset( $receiveRow ['PLAN09'] ) ){
            $receiveInfo ['PLAN'] = $receiveRow ['PLAN09'];
        }else{
            $receiveInfo ['PLAN'] = "";
        }
        if( isset( $receiveRow ['PRUM09'] ) ){
            $receiveInfo ['PRUM'] = $receiveRow ['PRUM09'];
        }else{
            $receiveInfo ['PRUM'] = "";
        }
        if( isset( $receiveRow ['ORDP09'] ) ){
            $receiveInfo ['ORDP'] = $receiveRow ['ORDP09'];
        }else{
            $receiveInfo ['ORDP'] = "";
        }
        if( isset( $receiveRow ['PLNN06'] ) ){
            $receiveInfo ['PLNN'] = $receiveRow ['PLNN06'];
        }else{
            $receiveInfo ['PLNN'] = "";
        }
        if( isset( $receiveRow ['SNAM05'] ) ){
            $receiveInfo ['SNAM'] = $receiveRow ['SNAM05'];
        }else{
            $receiveInfo ['SNAM'] = "";
        }
        if( isset( $receiveRow ['VCAT03'] ) ){
            $receiveInfo ['VCAT'] = $receiveRow ['VCAT03'];
        }else{
            $receiveInfo ['VCAT'] = "";
        }
        if( isset( $receiveRow ['DICD02'] ) ){
            $receiveInfo ['DICD'] = $receiveRow ['DICD02'];
        }else{
            $receiveInfo ['DICD'] = "";
        }
        if( isset( $receiveRow ['SSEQ02'] ) ){
            $receiveInfo ['SSEQ'] = $receiveRow ['SSEQ02'];
        }else{
            $receiveInfo ['SSEQ'] = "";
        }
        
    }
    
    return $receiveInfo;
}

/**
 * Function creates an array of orders numbers
 *
 * @return array of order numbers
 */
function set_orderNumber_array() {
    global $CONO, $conn;
    
    $orderSql = "SELECT ORDN56 FROM INP56L01 WHERE CONO56='$CONO'";
    
    $orderRes = odbc_prepare ( $conn, $orderSql );
    odbc_execute ( $orderRes );
    
    //echo $dropSql;
    
    
    $orderArray = array ();
    while ( $orderRow = odbc_fetch_array ( $orderRes ) ) {
        array_push ( $orderArray, $orderRow ['ORDN56'] );
    }
    return $orderArray;
}

/**
 * Function returns customer reference for an order number
 *
 * @param string $WHERE_CLAUSE
 * @return string Customer Order Reference
 */
function get_customer_ordernumber($WHERE_CLAUSE) {
    global $conn, $CONO;
    
    $sql = "SELECT CUSO40 FROM OEP40L01 $WHERE_CLAUSE";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    //echo $sql;
    while ( $row = odbc_fetch_array ( $res ) ) {
        return $row ['CUSO40'];
    }
}

