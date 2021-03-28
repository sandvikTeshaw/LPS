<?php

include_once '../copysource/config.php';
include '../copysource/functions.php';
include '../../common/copysource/global_functions.php';

// error_reporting(E_ALL);
//ini_set('display_errors', 1);

global $conn;



if (! $conn) {
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
    echo "Connection Failed";
}

$userArray = get_user_info_array_by_id();
$answerArray = getSurveyAnswersArray();
$questionArray = getSurveyQuestionsArray();


$startDate = $syear . str_pad($_REQUEST['smonth'], 2, '0', STR_PAD_LEFT) . str_pad($_REQUEST['sday'], 2, '0', STR_PAD_LEFT);
$endDate = $eyear . str_pad($_REQUEST['emonth'], 2, '0', STR_PAD_LEFT) . str_pad($_REQUEST['eday'], 2, '0', STR_PAD_LEFT);


$commentArray= getUserCommentsArray( $startDate, $endDate );


$setName = $_REQUEST['fileName'];

$fname = './surveyDetails/' . $setName . '.csv';


if( $fp = fopen( $fname, 'a+')){
    
}else{
    echo "failed open <hr>";
}



$headerlineInsert = "Ticket,User,Date,Question,Answer,Comment" . "\r\n";
fwrite($fp, $headerlineInsert );


$surveyDetailsSql   = "select ID01, DATE43, USER43, AID43, QID43 FROM OSLDIPDATL.CIL01 T1"
                    . " LEFT JOIN OSLDIPDATL.CIL43 T2"
                    . " ON T1.ID01 = T2.TID43"
                    . " WHERE DATE01 >= $startDate and DATE01 <= $endDate and STAT01 = 5"
                    . " ORDER BY ID01";
   

                    //echo $surveyDetailsSql. "<hr>";
$res = odbc_prepare( $conn, $surveyDetailsSql );
odbc_execute( $res );


while( $row = odbc_fetch_array( $res ) ){
    
    if( $row['QID43'] != "" ){
    $lineInsert = trim($row['ID01']) . "," .  trim($userArray[ $row['USER43'] ]['name']) . "," .  trim($row['DATE43']) .
    "," .  trim($questionArray[$row['QID43']]['question']) . "," .  trim( $answerArray[ $row['AID43']]['answer'] ) . "," .  trim($commentArray[$row['ID01']]['comment']) . "\r\n";
    }else{
        $lineInsert = trim($row['ID01']) . "\r\n";
    }
    fwrite($fp, $lineInsert);
    
}

fclose( $fp );

//javascript:window.close();

?>
<body onload="javascript:window.close();">

</body>

<?php 
function getSurveyAnswersArray(){
    global $conn;
    
    $sql = "SELECT ID42, ATXT42 FROM CIL42";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $answerArray = array();
    
    while ( $row = odbc_fetch_array( $res ) ) {
        $answerArray[$row['ID42']]['answer'] = trim($row['ATXT42']);

    }
    
    return $answerArray;
    
}

function getSurveyQuestionsArray(){
    global $conn;
    
    $sql = "SELECT ID41, QTXT41 FROM CIL41";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $questionArray = array();
    
    while ( $row = odbc_fetch_array( $res ) ) {
        $questionArray[$row['ID41']]['question'] = trim($row['QTXT41']);
        
    }
    
    return $questionArray;
    
}

function getUserCommentsArray( $sdate, $endDate ){
    global $conn;
    
    $sql = "SELECT TID44, CTXT44 FROM CIL44 WHERE DATE44 >= $sdate and DATE44 <= $endDate";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $commentArray = array();
    
    while ( $row = odbc_fetch_array( $res ) ) {
        $commentArray[$row['TID44']]['comment'] = trim($row['CTXT44']);
        
    }
    
    return $commentArray;
    
}


?>


		
		
		