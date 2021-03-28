<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			validateOrder.php<br>
 * Development Reference:	DI868<br>
 * Description:				validateOrder.php validates the orderNumber entered in ticket<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *   DI868C	  TS	  23/08/2008  Disable submit button<br>   
 */
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
// $conn = db2_pconnect ( "*LOCAL", "PHPSMCUSR", "PHPSMCUSR", $Options );
$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );

 ?>
<script type="text/javascript">
function checkOrderNumber( oNumber, oCount, attribNumber ){
	
    if( oCount == 0 ){
        eval( "window.opener.SODP_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.SODP_INVALID_" + attribNumber + ".style.display='block'" );
        //DI868D Added to disable submit button on invalid order number
        if( window.opener.detailsForm.submitButton ){
        	window.opener.detailsForm.submitButton.disabled = true;
        }
    }
    if( oCount > 0 ){
    	
        eval( "window.opener.SODP_" + attribNumber + ".style.display='block'" );
        eval( "window.opener.SODP_INVALID_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.detailsForm.ORDER_NUMBER.value='" + oNumber + "'" );
    
        //DI868D Added to re-enable submit button on invalid order number
        if( window.opener.detailsForm.submitButton ){
        	window.opener.detailsForm.submitButton.disabled = false;
        }
    }
}
</script>
<?php
 
	$orderCounter = validateOrderNumber( $orderNumber, $type );

?>
<body onload="checkOrderNumber( '<?echo trim($orderNumber);?>', '<?echo $orderCounter;?>', '<?echo trim($attrib);?>' );javascript:window.close();">

</body>