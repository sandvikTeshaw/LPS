<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            shipmentFunctions.php<br>
 * Development Reference:   LP0006<br>
 * Description:             This is the LPS shipment functions file
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP0006     AG     10/03/2017  Display Ticket Shipping Info
 *  LP0006a    TS     11/04/2017  Fix Despatch Date 
 */
/**
 */
//LP0013

function display_ticket_ship_info( $orderNum, $desnNumber, $conn ){

    ?>
    <table border=0 cellspacing=0 cellpadding=0 width=100%>
        <tr class='header'>
            <td class='header' width='10%'>Pack-list Reference</td>
            <td class='header' width='30%'>Carrier</td>
            <td class='header' width='20%'>Despatch<br/>Date</td>
            <td class='header' width='20%'>Delivery<br/>Terms</td>
            <td class='header' width='20%'>Delivery<br/>Method</td>
        </tr>
        <tr>
    <?php 
    $consCounter = 0;
    if( isset( $orderNum ) && isset( $desnNumber ) && $desnNumber != "" ){
        $oep68Sql = "SELECT CONS68 FROM OEP68U WHERE CONO68 = 'DI' AND ORDN68='$orderNum' AND DESN68=" . $desnNumber;
        $oep68Res = odbc_prepare ( $conn, $oep68Sql );
        odbc_execute ( $oep68Res );
        
        
        while ( $oep68Row = odbc_fetch_array ( $oep68Res ) ) {
            $consCounter++;
            ?><td><?php echo trim ( $oep68Row['CONS68'] );?></td><?php 
        }
    }
    if( $consCounter == 0 ){
        ?><td>-</td><?php   
    }
    //****Start LP0006a Section********************
    $inp56Sql = "SELECT cast( INVN56 as CHAR(20) CCSID 285) AS INVN56 FROM INP56 WHERE CONO56='DI' AND ORDN56 ='$orderNum' AND DESN56=$desnNumber";
    $inp56Res = odbc_prepare($conn, $inp56Sql);
    odbc_execute($inp56Res);
    $inv = odbc_fetch_array($inp56Res);
    $invoiceNum = trim($inv['INVN56']);
    
    if( isset( $inv['INVN56'] ) ){
        $inp65Sql = "SELECT DLDT65 FROM OEP65 WHERE CONO65='DI' AND INVN65 ='" .$inv['INVN56'] . "'";
    
        $inp65Res = odbc_prepare($conn, $inp65Sql);
        odbc_execute($inp65Res);
        $rDate = odbc_fetch_array($inp65Res);
        $despatchDate = trim($rDate['DLDT65']);
    
    }
    //****End LP0006a Section ********************
    
    $dypSql = "SELECT CAR410, DSDT10, TDEL10, ADPD10 FROM DYP10 WHERE CONO10 = 'DI' AND SUBSTR(DCRE10,1,7)='$orderNum' AND SUBSTR(DCRE10,8,2)='$desnNumber'";
    $dypRes = odbc_prepare ( $conn, $dypSql );
    odbc_execute ( $dypRes );
    while ( $dypRow = odbc_fetch_array ( $dypRes ) ) {
    
        ?>
                <td><?php echo get_shipment_carrier( $conn, trim($dypRow['CAR410']));?></td>
                <td>
                    <?php 
                    //****Start LP0006a Section********************
                    if( trim($dypRow['DSDT10']) > 0 ){
                        echo format_JBA_Date( trim($dypRow['DSDT10']));
                   }else{
                       if( isset( $despatchDate ) ){
                            echo format_JBA_Date( $despatchDate );
                       }
                   }
                   //****End LP0006a Section ********************
                    ?>
                </td>
                <td><?php echo get_delivery_terms( $conn, trim($dypRow['TDEL10']) );?></td>
                <td><?php echo get_delivery_method( $conn, trim($dypRow['ADPD10']));?></td>
            <?php 
            }
            ?>
            </tr>
            </table>
            <?php 
}

function get_shipment_carrier($conn, $carId ){
    $carrierSql = "SELECT PRMD15 FROM INP15 WHERE PRMT15='CARR' AND PSAR15='$carId'";
    $carrierRes = odbc_prepare ( $conn, $carrierSql );
    odbc_execute ( $carrierRes );
    
    while ( $carrierRow = odbc_fetch_array ( $carrierRes ) ) {
        return trim($carrierRow['PRMD15']);
    }
    
}

function get_delivery_terms($conn, $termId ){
    $delTermSql = "SELECT PRMD15 FROM INP15 WHERE PRMT15='TDEL' AND PSAR15='$termId'";
    $delTermRes = odbc_prepare ( $conn, $delTermSql );
    odbc_execute ( $delTermRes );

    while ( $delTermRow = odbc_fetch_array ( $delTermRes ) ) {
        return trim($delTermRow['PRMD15']);
    }

}
function get_delivery_method($conn, $methId ){
    $delMethSql = "SELECT PRMD15 FROM INP15 WHERE PRMT15='MODE' AND PSAR15='$methId'";
    $delMethRes = odbc_prepare ( $conn, $delMethSql );
    odbc_execute ( $delMethRes );

    while ( $delMethRow = odbc_fetch_array ( $delMethRes ) ) {
        return trim($delMethRow['PRMD15']);
    }

}
		
?>