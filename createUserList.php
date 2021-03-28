<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            tickets.php<br>
 * Development Reference:   DI868<br>
 * Description:             tickets.php displays ticket listing defined by the from paramter<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *   D0301	  TS	  18/03/2011  Performance Issues (INITIAL)
 */
/**
 */

include 'copysource/config.php';
include '../common/copysource/global_functions.php';

if (! isset($conn) ) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_connect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
	
}


$queryName = strtoupper(trim($_REQUEST['resourceInput']));

$sql = "SELECT ID05, NAME05, EMAIL05 FROM HLP05 WHERE DEL05<> 'Y' AND UCASE(NAME05) LIKE '" . $queryName . "%' order by name05";
$res = odbc_prepare ( $conn, $sql );
odbc_execute ( $res );

while( $row = odbc_fetch_array( $res ) ){
	$name = trim($row['NAME05']);
	$uId = trim($row['ID05']);
	$email=trim($row['EMAIL05']);

	if( isset($_REQUEST['idvalue']) && $_REQUEST['idvalue']  != "email" ){
		
		if( $row['ID05'] == $_REQUEST['selectedUser'] ){
	        echo "obj.options[obj.options.length] = new Option('" . utf8_encode($name) . "','$uId', true, true );\n";
	    }else{
	    	echo "obj.options[obj.options.length] = new Option('" . utf8_encode($name) . "','$uId');\n";
	    }
	    
	}else{
		
	    if( isset( $row['ID05'] ) && isset( $_REQUEST['selectedUser'] ) && $row['ID05'] == $_REQUEST['selectedUser'] ){
	        echo "obj.options[obj.options.length] = new Option('" . utf8_encode($name) . "','$email', true, true );\n";
	    }else{
	    	echo "obj.options[obj.options.length] = new Option('" . utf8_encode($name) . "','$email');\n";
	    }
		
	}
    
}
//}

?>