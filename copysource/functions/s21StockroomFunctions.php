<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            s21StockroomFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to s21 stockrooms and stockroom information
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
 * Function returns stockroom name
 *
 * @param string $STCK
 * @return string Name of Stockroom
 */
function get_stockroom_desc($STCK) {
    global $conn, $CONO;
    $sql = "SELECT cast( STRN20 as CHAR(25) CCSID 285) AS PRMD15 FROM INP20L01 WHERE CONO20 = '$CONO' AND STRC20 = '$STCK'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        return $row ['PRMD15'];
    }
}

/**
 * Function retunrs an array of stockrooms
 *
 * @return array of stockrooms
 */
function get_stockroom_array() {
    global $conn, $CONO;
    
    $sql = "SELECT cast( STRC20 as CHAR(2) CCSID 285) AS STRC20 FROM INP20L01 WHERE CONO20='DI'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $stockRoomArray = array ();
    while ( $row = odbc_fetch_array ( $res ) ) {
        array_push ( $stockRoomArray, $row ['STRC20'] );
    }
    
    return $stockRoomArray;
}