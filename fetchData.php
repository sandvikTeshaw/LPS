<?php 

include 'copysource/config.php';


if(isset($_REQUEST['search'])){
    
    if (isset($conn)) {
    }else{
        $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
    }
    if (!isset($conn)) {
        echo "Connection Failed";
    }

    if( isset($search) && $search != "" ){
        $nameSql = "SELECT ID05, NAME05 FROM HLP05 WHERE UCASE(NAME05) like '".strtoupper($search)."%' AND DEL05 <> 'Y'";
    
        $res = odbc_prepare ( $conn, $nameSql );
        odbc_execute ( $res );
        $response = array();
        while ( $row = odbc_fetch_array ( $res ) ) {
            $response[] = array("value"=>$row['ID05'],"label"=>trim($row['NAME05']));
        }
        echo json_encode($response);
    }
}

exit;

