<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            profileValidationResponse.php<br>
 * Development Reference:   D0247<br>
 * Description:             LPS Profile Validation Page
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  D0247 	   TS	  25/02/2011  Link LPS User profile to Notification<br>
 * 
 */
/**
 */
 
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

if (! $conn) {
        // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

	$hlp05Update = "UPDATE HLP05 SET NAME05='" . $_REQUEST['name'] . "', CODE05=" . $_REQUEST['code'] . ","
			 	. " AVAL05='" . $_REQUEST['availability'] . "', BACK05=" . $_REQUEST['backup'] . ", RSRC05 = 'N'"
			 	. " WHERE ID05=" . $_SESSION['userID'];
			 
	//echo $hlp05Update . "<hr>";
	$res = odbc_prepare ( $conn, $hlp05Update );
    odbc_execute ( $res );
    
    if( count_records ( FACSLIB, "CIL31", " WHERE EMPL31 = " .$_SESSION['userID'] ) > 0 ){
    	
    	$cil31Update = "UPDATE CIL31 SET SUPR31=" . $_REQUEST['super'] . " WHERE EMPL31 = " . $_SESSION['userID'];
    	
    	//echo $cil31Update;
    	$res31 = odbc_prepare ( $conn, $cil31Update );
    	odbc_execute ( $res31 );
    	
    	
    }else{
    	$cil31Update = "INSERT INTO CIL31 VALUES( " .  $_REQUEST['super'] . "," .  $_SESSION['userID'] . ")";
    	
    	//echo $cil31Update;
    	
    	$res31 = odbc_prepare ( $conn, $cil31Update );
    	odbc_execute ( $res31 );
    	
    }
    
    $_SESSION['superId'] = $_REQUEST['super'];
?>
    
   <meta http-equiv="refresh" content="0;url=index.php"/>

    