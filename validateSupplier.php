<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			validatePart.php<br>
 * Development Reference LP0055_AD<br>
 * Description:				validateSupplier.php validates the Supplier Number entered in ticket<br>
 *
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 * 	--------  ------  ----------  ----------------------------------<br>
 *  LP0068      TS     19/06/2019   Add Type in function call
 *  LP0083      AD     23/09/2019   Add currency validation (autofill) GLBAU-18154
 */
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );

?>
<script type="text/javascript">
function checkPartNumber( pNumber, pCount, attribNumber, oNumber, oAttrib ){
    if( pCount == 0 ){
        eval( "window.opener.SUPPLIER_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.SUPPLIER_INVALID_" + attribNumber + ".style.display='block'" );

        window.opener.detailsForm.submitButton.disabled = true;
    }
    if( pCount > 0 ){
        eval( "window.opener.SUPPLIER_" + attribNumber + ".style.display='block'" );
        eval( "window.opener.SUPPLIER_INVALID_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.detailsForm.SUPPLIER_NUMBER.value='" + pNumber + "'" );
        <?php //lp0083_ad
         if($type==130){//lp0083_ad
             $partSql = "SELECT CURN05 FROM PLP05 WHERE CONO05='DI' AND DSEQ05='000'";//lp0083_ad
             $partSql .= " AND (SUPN05='" . trim($partNumber) . "')";//lp0083_ad
             $partRes = odbc_prepare ( $conn, $partSql );//lp0083_ad
             odbc_execute ( $partRes );//lp0083_ad
             $row= odbc_fetch_array ( $partRes );//lp0083_ad
             $supplierPartDefaultCurrencyCode=$row['CURN05']; //lp0083_ad
             
            ?>//lp0083_ad
       			 eval( "window.opener.detailsForm.text6.value='" +"<?php echo $supplierPartDefaultCurrencyCode; ?>"+"'" );//lp0083_ad
            <?php //lp0083_ad
         }//lp0083_ad
        ?>//lp0083_ad
       
        window.opener.detailsForm.submitButton.disabled = false;
    }
}
</script>
<?php

    if( $orderNumber == "" ){
		$partCounter = validateSupplier( $partNumber, $type ); //LP0068 - Added Type
		$orderNumber = 0;
    }else{
    	while( strlen( $orderNumber) < 7 ){
    		$orderNumber = "0" . $orderNumber;
    	}
    	$partCounter = validatePartOrder( $partNumber, $orderNumber, $desnNumber, $type );
    }
   	
//javascript:window.close();
?>
<body onload="checkPartNumber( '<?echo trim($partNumber);?>', '<?echo $partCounter;?>', '<?echo trim($attrib);?>', '<?echo trim($orderNumber);?>', '<?echo trim($orderAttrib);?>' );javascript:window.close();">

</body>
