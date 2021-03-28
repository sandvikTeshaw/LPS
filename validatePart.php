L<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			validatePart.php<br>
 * Development Reference:	DI868<br>
 * Description:				validatePart.php validates the partNumber entered in ticket<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
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

var pageLocation = 0;
var str = String( eval(  window.opener.location ) );
var pageLocation = str.indexOf("showTicketDetails.php");


    if( pCount == 0 ){
        eval( "window.opener.PART_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.PART_INVALID_" + attribNumber + ".style.display='block'" );
     
        if( typeof oNumber === "undefined" || oNumber <= 0 ){
            
        }else{

        	eval( "window.opener.SODP_" + oAttrib + ".style.display='none'" );
        	eval( "window.opener.SODP_INVALID_" + oAttrib + ".style.display='block'" );
        }

		if( pageLocation < 0 ){
			
        	window.opener.detailsForm.submitButton.disabled = true;
		}

    }

    if( pCount > 0 ){
        eval( "window.opener.PART_" + attribNumber + ".style.display='block'" );
        eval( "window.opener.PART_INVALID_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.detailsForm.PART_NUMBER.value='" + pNumber + "'" );
        
		if( typeof oNumber === "undefined" || oNumber <= 0 ){
            
        }else{
        	eval( "window.opener.SODP_" + oAttrib + ".style.display='block'" );
        	eval( "window.opener.SODP_INVALID_" + oAttrib + ".style.display='none'" );
        }

        if( pageLocation < 0 ){
        	window.opener.detailsForm.submitButton.disabled = false;
        }
    }

}
</script>
<?php

if( !isset($orderNumber ) ){
    $orderNumber = "";
}
if( !isset($orderAttrib ) ){
    $orderAttrib = "";
}


    if( $orderNumber == "" ){
		$partCounter = validatePartNumber( $partNumber );
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
