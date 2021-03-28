<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            validationFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions used for validation
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  LP0032      KS    08/01/2018    Change to LPS Expedite Ticket Validation Logic- Cancelled lines
 *  LP0055      AD    13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0055      KS    28/03/2019  fix 
 *  LP0055      AD    05/04/2019  fix2 
 *  LP0068      TS    05/04/2019  Supplier Functions 
 *  
 **/

/**
 * Function validates part number from System 21
 *
 * @param string $partNumber
 * @return integer $partCounter - Number of records found
 */
function validatePartNumber($partNumber) {
    global $conn, $CONO;
    
    $today = convert_to_jba_date ( date ( 'Ymd' ) );
    $partSql = "SELECT PNUM35 FROM PARTS WHERE CONO35='$CONO'";
    $partSql .= " AND PNUM35='" . trim ( $partNumber ) . "'";
    $partSql .= " AND ( EEDT35 = 0 or EEDT35 >= $today )";
    
    $partRes = odbc_prepare ( $conn, $partSql );
    odbc_execute ( $partRes );
    
    $partCounter = 0;
    while ( $partRow = odbc_fetch_array ( $partRes ) ) {
        $partCounter ++;
    }
    
    return $partCounter;
}

/**
 * Function validates Supplier number from System 21
 *
 * @param string $(supplier number)
 * @return integer $partCounter - Number of records found
 */
function validateSupplier($suppNumber, $type ) {//LP0055_AD
    global $conn, $CONO;//LP0055_AD
 
    if( $type == 130 ){
        //$today = convert_to_jba_date ( date ( 'Ymd' ) );//LP0055_AD
        $partSql = "SELECT VNDR01 FROM PMP01 WHERE CONO01='$CONO'";//LP0055_AD
        $partSql .= " AND VNDR01='" . trim ( $suppNumber ) . "'";//LP0055_AD
        //$partSql .= " AND ( EEDT35 = 0 or EEDT35 >= $today )";//LP0055_AD
    }elseif ( $type == 133 ){//LP0068 - Added check on PLP05 instead of PMP01 as they may not exist in PMP01
        
        $partSql = "SELECT CURN05 FROM PLP05 WHERE CONO05='$CONO' AND DSEQ05='000'";//LP0068
        $partSql .= " AND (SUPN05='" . trim($suppNumber) . "')";//LP0068
    }

    $partRes = odbc_prepare ( $conn, $partSql );//LP0055_AD
    odbc_execute ( $partRes );//LP0055_AD
    
    $partCounter = 0;//LP0055_AD
    while ( $partRow = odbc_fetch_array ( $partRes ) ) {//LP0055_AD
        $partCounter ++;//LP0055_AD
    }//LP0055_AD
    
    echo $partCounter;
    
    return $partCounter;//LP0055_AD
}


/**
 * Function validates Supplier part number from System 21
 *
 * @param string $partNumber(supplier part number)
 * @return integer $partCounter - Number of records found
 */

//**LP0055_KS  function validateSupplierPartNumber($partNumber) {//LP0055_AD
function validateSupplierPartNumber($supplierNumber,$partNumber) {//LP0055_KS
    ////LP0055_AD2 global $conn, $CONO;//LP0055_AD
    global $conn, $CONO,$internalPartNumber;//LP0055_AD2
    $internalPartNumber="";//LP0055_AD2
    $today = convert_to_jba_date ( date ( 'Ymd' ) );//LP0055_AD
    //**LP0055_KS  $partSql = "SELECT VCAT01 FROM PMP01 WHERE CONO01='$CONO'";//LP0055_AD
    ////LP0055_AD2 $partSql = "SELECT CONO01 FROM PMP01 WHERE CONO01='$CONO' ";            //**LP0055_KS
    $partSql = "SELECT CONO01,ITEM01 FROM PMP01 WHERE CONO01='$CONO' ";            //**LP0055_KS
    $partSql .= " AND VNDR01='" . trim($supplierNumber) . "' ";             //**LP0055_KS
    $partSql .= " AND ( ";                                                  //**LP0055_KS
    //LP0055_AD2   $partSql .= "     ( ";                                                  //**LP0055_KS
    //LP0055_AD2  $partSql .= " VCAT01 <> '' ";                                           //**LP0055_KS
    //LP0055_AD2 $partSql .= " AND VCAT01='" . trim ( $partNumber ) . "'";//LP0055_AD
    //LP0055_AD2 $partSql .= "  ) OR ( ";                                                //**LP0055_KS
    //LP0055_AD2 $partSql .= " VCAT01 = '' ";                                            //**LP0055_KS
    $partSql .= "  ";                                            //**LP0055_AD2
    //LP0055_AD2    $partSql .= " AND ITEM01='" . trim($partNumber) . "' ";                 //**LP0055_KS
    $partSql .= " ITEM01='" . trim($partNumber) . "' ";                 //**LP0055_AD2
    //LP0055_AD2 $partSql .= "  ) ";                                                     //**LP0055_KS
    $partSql .= "  ) ";                                                     //**LP0055_KS
    //$partSql .= " AND ( EEDT35 = 0 or EEDT35 >= $today )";//LP0055_AD
    
    $partRes = odbc_prepare ( $conn, $partSql );//LP0055_AD
    odbc_execute ( $partRes );//LP0055_AD
    
    $partCounter = 0;//LP0055_AD
    while ( $partRow = odbc_fetch_array ( $partRes ) ) {//LP0055_AD
        $partCounter ++;//LP0055_AD
        $internalPartNumber=$partRow['ITEM01'];//LP0055_AD2
    }//LP0055_AD
    
    return $partCounter;//LP0055_AD
}

/**
 * Function validates order number from System 21
 *
 * @param string $orderNumber
 * @param integer $type
 * @return integer $orderCounter - Number of records found
 */
function validateOrderNumber($orderNumber, $type) {
    global $conn, $CONO;
    
    if ($type != 42) {
        $orderSql = "SELECT ORDN56 FROM INP56LU3 WHERE CONO56='$CONO' AND ORDN56='" . trim ( $orderNumber ) . "'";
    } else {
        $orderSql = "SELECT ORDN55 FROM OEP55L07 WHERE CONO55='$CONO'";
        $orderSql .= "AND ORDN55='" . trim ( $orderNumber ) . "'";
    }
    
    
    
    $orderRes = odbc_prepare ( $conn, $orderSql );
    odbc_execute ( $orderRes );
    //echo $orderSql;
    $orderCounter = 0;
    while ( $orderRow = odbc_fetch_array ( $orderRes ) ) {
        
        
        $orderCounter ++;
    }
    
    
    return $orderCounter;
}

/**
 * Function validates order number and part number combinations from System 21
 *
 * @param string $part
 * @param string $order
 * @param string $desn
 * @param integer $type
 * @return integer $recordCount - Number of records found
 */
function validatePartOrder($part, $order, $desn, $type) {
    global $conn, $CONO;
    $sql = "SELECT count(DESN57)";
    $sql .= "FROM INP57 WHERE CONO57 = '$CONO' AND ORDN57='" . $order . "'";
    
    
    if (isset($type) && $type != 42) {
        if( !isset( $desn ) || $desn == "" || $desn == 0 ){
            
        }else{
            $sql .= "AND DESN57=$desn ";
        }
    }
    
    $sql .= "AND CATN57='" . trim ( $part ) . "'";
    
    $res = odbc_prepare ( $conn, $sql );
    if( odbc_execute ( $res ) ){
        
    }else{
        $handle = fopen("./sqlFailures/sqlFails.csv","a+");
        fwrite($handle, "181 - validationFunctions.php," . $sql . "\n" );
        fclose($handle);
        
    }
    
    
    $recordCount = 0;
    while ( $countRecords = odbc_fetch_array ( $res ) ) {
        $recordCount = $countRecords ['00001'];
    }
    
    if ($recordCount == 0 && ($type == 42 || $type == 43 || $type == 44)) {
        $sql = "SELECT count(ORDN55) FROM OEP55L02 WHERE CONO55='$CONO' AND CATN55='" . $part . "' AND ORDN55='" . $order . "'";
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
        
        while ( $countRecords = odbc_fetch_array ( $res ) ) {
            $recordCount = $countRecords ['00001'];
        }
    }
    
    if (($recordCount == 0) && ($type == 42)) {                                                                                     //**LP0032
        $sql = "SELECT count(ORDN55) FROM OEP55 WHERE CONO55='$CONO' AND CATN55='" . $part . "' AND ORDN55='" . $order . "'";       //**LP0032
        $res = odbc_prepare ( $conn, $sql );                                                                                         //**LP0032
        odbc_execute ( $res );                                                                                                       //**LP0032
                                                                                                                                    //**LP0032
        while ( $countRecords = odbc_fetch_array ( $res ) ) {                                                                        //**LP0032
            $recordCount = $countRecords ['00001'];                                                                                       //**LP0032
        }                                                                                                                           //**LP0032
    }                                                                                                                               //**LP0032
    
    
    
    return $recordCount;
    
}

function validate_customer($customerNumber, $deliverySequence, $cono) {
    global $conn;
    
    $sql = "SELECT count(*) as rowCount from CUSNAMES WHERE CONO05='$cono' AND CUSN05='$customerNumber' AND DSEQ05='$deliverySequence'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $counter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $counter = intval($row['ROWCOUNT']);
    }
    return $counter;
}

//LP0002 - Add validation to check if user is an active DRP manager.
function validate_drp_manager( $userId ){
    global $conn;
    
    $sql = "select ACTM20 FROM CIL20L01 WHERE ACTM20 = $userId";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $drp_manager = false;
    while( $row = odbc_fetch_array($res) ){
        $drp_manager = true;
    }
    
    return $drp_manager;
    
}

//LP0002 - Add to check if required questions have answers
function check_ticket_answers( $class, $type, $ticketId, $section ){
    global $conn;
    
    //LP0002 - Added $section
    $sql = "select * FROM CIL34  WHERE CLAS34 = $class AND TYPE34= $type AND REQD34='Y' AND SECN34=$section";
    
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $answerCounter = 0;
    $questionCounter = 0;
    
    while( $row = odbc_fetch_array($res) ){
        
        
        $questionCounter++;
        $sqlAnswers = "select ID36 FROM CIL36 WHERE QID36 = " . $row['ID34'] . " AND TID36=" . $ticketId;
        
        $resAnswers = odbc_prepare ( $conn, $sqlAnswers );
        odbc_execute ( $resAnswers );
        
        
        while( $rowAnswers = odbc_fetch_array($resAnswers) ){
            
            
            $answerCounter++;
        }
    }
    
    if( $answerCounter == $questionCounter ){
        $hasAnswers = true;
    }else{
        $hasAnswers = false;
    }
    
    
    
    return $hasAnswers;
    
}

