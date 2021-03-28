<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            s21PartsFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to s21 Parts and part information
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification
 *  LP0046      KS    21/06/2018    Auto Create Cost Check ticket
 *  LP0084     AD     30/09/2019 LP0084 - LPS - Allow TSD's to be identified by Item Class and PGMJ Combination
 *
 **/

/**
 * Function retunrs part description dependant on $partNumber
 *
 * @param string $partNumber
 * @return array of part information
 */
function get_part_description($partNumber) {
    global $CONO, $conn;
    
    $sql = "SELECT PDES35 FROM PARTS WHERE CONO35 = '$CONO' AND PNUM35 = '" . trim ( $partNumber ) . "'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        
        return $row ['PDES35'];
    }
}

/**
 * Funciton returns the brand of a $partNumber
 *
 * @param string $partNumber
 * @return string BandOfPartNumber
 */
function get_part_brand($partNumber) {
    global $conn, $CONO;
    $sql = "SELECT PCLS35 FROM PARTS WHERE CONO35='$CONO' AND PNUM35='" . trim ( $partNumber ) . "'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    while ( $row = odbc_fetch_array ( $res ) ) {
        return $row ['PCLS35'];
    }
}


function get_part_info($partNumber){                                                                            //**LP0046
    global $CONO, $conn;                                                                                        //**LP0046
    //**LP0046
    $sql = " select b.*, a.* ";                                                                                 //**LP0046
    $sql .= " from INP35 a ";                                                                                   //**LP0046
    $sql .= " left join INP35EU b ";                                                                            //**LP0046
    $sql .= "  on   a.CONO35 = b.CONO35 ";                                                                      //**LP0046
    $sql .= "   and a.PNUM35 = b.PNUM35 ";                                                                      //**LP0046
    $sql .= " where a.CONO35 = '" . $CONO . "' ";                                                               //**LP0046
    $sql .= "   and a.PNUM35 = '" . trim($partNumber) . "' ";                                                   //**LP0046
    $res = odbc_prepare($conn, $sql);                                                                            //**LP0046
    odbc_execute($res);                                                                                          //**LP0046
    //**LP0046
    while($row = odbc_fetch_array($res)){                                                                        //**LP0046
        return $row;                                                                                            //**LP0046
    }                                                                                                           //**LP0046
}                                                                                                               //**LP0046


function list_Description_Brands($brandCode) {
    global $conn, $CONO;
    
    $sql = "SELECT PSAR15, PRMD15 FROM DESC WHERE CONO15='$CONO' AND PRMT15='PCLS' ORDER BY PRMD15";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        
        ?><option value='<?
        echo $row ['PSAR15'];
        ?>'
                                        <?
        if ($brandCode == $row ['PSAR15']) {
            echo " SELECTED ";
        }
        ?>><?
     //lp0084_ad   echo $row ['PRMD15'];
        echo $row ['PSAR15']." ".$row ['PRMD15']; //lp0084
        ?>
        </option>
    <?
    }
}
function list_Description_PGMJ($brandCode) {//lp0084
    global $conn, $CONO;//lp0084
    
    $sql = "SELECT PSAR15, PRMD15 FROM DESC WHERE CONO15='$CONO' AND PRMT15='PGMJ' ORDER BY PRMD15";//lp0084
    $res = odbc_prepare ( $conn, $sql );//lp0084
    odbc_execute ( $res );//lp0084
    
    while ( $row = odbc_fetch_array ( $res ) ) {//lp0084
        
        ?><option value='<?//lp0084
        
        echo $row ['PSAR15'];//lp0084
        ?>'       <?//lp0084
         if ($brandCode == $row ['PSAR15']) {//lp0084
                          echo " SELECTED ";//lp0084
            }//lp0084
         ?>><?//lp0084        
        echo $row ['PSAR15']." ".$row ['PRMD15']; //lp0084      
        ?>    </option>    <?//lp0084
    }//lp0084
}//lp0084

function get_description_brand_name($brandCode) {
    
    global $conn, $CONO;
    
    $sql = "SELECT PRMD15 FROM DESC WHERE CONO15='$CONO' AND PRMT15='PCLS'";
    $sql .= " AND PSAR15='$brandCode'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        return $brandCode." - ".$row ['PRMD15'];
 //lp0084_ad       return $row ['PRMD15'];
    }
    
    return "";
    
}
function get_description_PGMJ($brandCode) {//lp0084
    
    global $conn, $CONO;//lp0084
    if($brandCode=="ALL PGMJ")return "ALL PGMJ";//lp0084
    $sql = "SELECT PRMD15 FROM DESC WHERE CONO15='$CONO' AND PRMT15='PGMJ'";//lp0084
    $sql .= " AND PSAR15='$brandCode'";//lp0084
    $res = odbc_prepare ( $conn, $sql );//lp0084
    odbc_execute ( $res );//lp0084
    while ( $row = odbc_fetch_array ( $res ) ) {//lp0084
        return $brandCode." - ".$row ['PRMD15'];//lp0084
        //lp0084_ad       return $row ['PRMD15'];
    }//lp0084
    
    return "";//lp0084
    
}//lp0084