<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			validateInvoice.php<br>
 * Development Reference:	LP0021<br>
 * Description:				validateInvoice.php validates the Invoice Numbers entered in ticket<br>
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
function checkInvoiceNumber( invCount, attribNumber){

    if( invCount == 0 ){
        eval( "window.opener.INVN_" + attribNumber + ".style.display='none'" );
        eval( "window.opener.INVN_INVALID_" + attribNumber + ".style.display='block'" );

        window.opener.detailsForm.submitButton.disabled = true;
    }else{

    	eval( "window.opener.INVN_" + attribNumber + ".style.display='block'" );
        eval( "window.opener.INVN_INVALID_" + attribNumber + ".style.display='none'" );
        
    	window.opener.detailsForm.submitButton.disabled = false;

    }
    
}
</script>
<?php
    	
    $inp65Sql = "SELECT count(INVN65) FROM OEP65 WHERE CONO65='DI' AND INVN65 ='$invoiceNumber'";

    $inp65Res = odbc_prepare($conn, $inp65Sql);
    odbc_execute($inp65Res);
    $rCount = odbc_fetch_array($inp65Res);
    $invCounter = $rCount['00001'];


?>
<body onload="checkInvoiceNumber( '<?echo $invCounter;?>', '<?echo trim($attrib);?>');javascript:window.close();">

</body>

