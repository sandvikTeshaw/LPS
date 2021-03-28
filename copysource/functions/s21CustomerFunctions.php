<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            s21CustomerFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to s21 customers and customer information
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
 * Function returns array of Customer Information
 *
 * @param string $orderNumber
 * @param string $desnNumber
 * @return array of Customer Information from OEP70LU3
 */
//LP0002 - Added $desnNumber parameter
function get_customers_showTicket($orderNumber) {
    global $conn, $CONO;
    
    $sql = "SELECT CUSN70,DSEQ70";
    $sql .= " FROM OEP70LU3 WHERE CONO70='$CONO' AND ORDN70='$orderNumber'";
    //echo $sql;
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $customerInfo = array();
    // echo $sql;exit;
    while ( $row = odbc_fetch_array ( $res ) ) {
        // echo "result:<pre>";print_r($row);exit;
        $customerInfo ['CUSN70'] = $row ['CUSN70'];
        $customerInfo ['DSEQ70'] = $row ['DSEQ70'];
    }
    
    return $customerInfo;
    
}

/**
 * Function returns array of Customer Information
 *
 * @param string $orderNumber
 * @param string $desnNumber
 * @return array of Customer Information
 */
//DI868J - Added $desnNumber parameter
function get_order_customer_site_number($orderNumber, $desnNumber) {
    global $conn, $CONO;
    
    ////DI868J - functionality to check to see if despatch number exists, if it does get the stockroom from the invoice rather than the order
    if (! $desnNumber) {
        $sql = "SELECT CUSN40, DSEQ40,";
        $sql .= " LOCD40 FROM ORDHDORD WHERE CONO40='$CONO' AND ORDN40='$orderNumber'";
        
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
        
        //echo $sql;
        while ( $row = odbc_fetch_array ( $res ) ) {
            $customerInfo [0] = $row ['CUSN40'];
            $customerInfo [1] = $row ['DSEQ40'];
            $customerInfo [2] = $row ['LOCD40'];
        }
        
    } else {
        //DI868J - Added sql from invoice and paramter for depatch number
        $sql = "SELECT cast( CUSN57 as CHAR(8) CCSID 285) AS CUSN57,cast( DSEQ57 as CHAR(3) CCSID 285) AS DSEQ57,";
        $sql .= " cast( LOCD57 as CHAR(2) CCSID 285) AS LOCD57 FROM INP57 WHERE CONO57='$CONO' AND ORDN57='$orderNumber'";
        $sql .= " AND DESN57='$desnNumber'";
        
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
        
        //echo $sql;
        while ( $row = odbc_fetch_array ( $res ) ) {
            $customerInfo [0] = $row ['CUSN57'];
            $customerInfo [1] = $row ['DSEQ57'];
            $customerInfo [2] = $row ['LOCD57'];
        }
        
    }
    //echo $sql;
    
    
    return $customerInfo;
    
}

function get_customer_by_orderNumber( $orderNumber ){
    global $conn, $CONO;
    
    $orderNum = substr ( $orderNumber, 0, strpos ( $orderNumber, " " ) );
    $sql = "SELECT CUSN40, DSEQ40, CNAM05";
    $sql .= " FROM OEP40 T1";
    $sql .= " inner join CUSNAMES T2";
    $sql .= " on T1.CUSN40=T2.CUSN05 AND T1.DSEQ40 = T2.DSEQ05";
    $sql .= " WHERE CONO40='$CONO' AND ORDN40='$orderNum'";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $customerInfoArray = array();
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        $customerInfoArray['customerNumber'] = $row['CUSN40'];
        $customerInfoArray['site'] = $row['DSEQ40'];
        $customerInfoArray['customerName'] = $row['CNAM05'];
    }
    
    return $customerInfoArray;
}
