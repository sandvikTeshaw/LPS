<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			validatePart.php<br>
 * Development Reference LP0055_AD<br>
 * Description:				validateSupplierPart.php validates the Supplier partNumber entered in ticket<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  LP0055      KS    28/03/2019  fix  
 *  LP0055      AD    05/04/2019  fix2  
 */
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
global $internalPartNumber;//LP0055_AD2
// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );

 ?>
<script type="text/javascript">
function checkPartNumber( pNumber, pCount, attribNumber, oNumber, internalPartNumber ){
    if( pCount == 0 ){
        eval( "window.opener.SUPPLIER_PART_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.SUPPLIER_PART_INVALID_" + attribNumber + ".style.display='block'" );

        window.opener.detailsForm.submitButton.disabled = true;
    }
    if( pCount > 0 ){
        eval( "window.opener.SUPPLIER_PART_" + attribNumber + ".style.display='block'" );
        eval( "window.opener.SUPPLIER_PART_INVALID_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.detailsForm.part10.value='" +  internalPartNumber + "'" );//LP0055_AD2
       
        window.opener.detailsForm.submitButton.disabled = false;
    }
}
</script>
<?php

    //**LP0055_KS  if( $orderNumber == "" ){
	//**LP0055_KS   	$partCounter = validateSupplierPartNumber( $partNumber );
    $partCounter = validateSupplierPartNumber($supplierNumber, $partNumber);                     //**LP0055_KS
		$orderNumber = 0;
    //**LP0055_KS  }else{
    //**LP0055_KS   	while( strlen( $orderNumber) < 7 ){
    //**LP0055_KS 		$orderNumber = "0" . $orderNumber;
    //**LP0055_KS  }
    //**LP0055_KS   	$partCounter = validatePartOrder( $partNumber, $orderNumber, $desnNumber, $type );
    //**LP0055KS  }
   	
//javascript:window.close();
?>
<body onload="checkPartNumber( '<?echo trim($partNumber);?>', '<?echo $partCounter;?>', '<?echo trim($attrib);?>', '<?echo trim($orderNumber);?>', '<?echo trim($internalPartNumber);?>' );javascript:window.close();">

</body>
