<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            autoResolve.php<br>
 * Development Reference:   LO0069<br>
 * Description:             LPS Automatic changing status to resolve for tickets meeting specific conditions <br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
/**
 */

global $conn, $SITENAME, $updateCIL01Sql, $strHeaders, $message, $updateCIL01_03Sql;

include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

$sendMailFlag = true;
$saveInserts = true;

set_time_limit ( 600 );

if (!isset($conn)) {

    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if (isset($conn)) {

} else {
    echo "Connection Failed";
}

$autoResSql="select HOUR48,ID01,CPDT01,CPTI01,RQID01 from cil48  ".
            "join cil01 on TYPE48= TYPE01 ".//Type condition
            "AND STAT01=4 ".//Status completed=4
            "AND RQID01=OWNR01 ".//requester= owner
            "AND PRIO48=PRTY01 ";//priority condition
//echo $autoResSql;
$autoResRes = odbc_prepare( $conn, $autoResSql );
odbc_execute( $autoResRes );
while( $row = odbc_fetch_array( $autoResRes ) ){
    //var_dump($row);
    //Gets ticket completion date
    
    $Year = substr ( $row ['CPDT01'], 0, 4 );
    $Month = substr ( $row ['CPDT01'], 4, 2 );
    $Day = substr ( $row ['CPDT01'], 6, 2 );
    $Hour = substr ( $row ['CPTI01'], 0, 2 );
    if( isset( $row ['CTPI01'] ) ){
        $Hour = substr ( $row ['CPTI01'], 0, 2 );
        $Minute = substr ( $row ['CTPI01'], 2, 2 );
        $Second = substr ( $row ['CTPI01'], 4, 2 );
    }else{
        $Hour = 0;
        $Minute = 0;
        $Second = 0;
    }

    
    
    //Converts date and time into workable time
    $completeTime = mktime ( $Hour, $Minute, $Second, $Month, $Day, $Year );//seconds since 1970
    
    if((time()-$completeTime)/60/60>$row['HOUR48']){ //expired time check
        //echo $row['ID01'];
        
            $lastActionSql="select DATE02,TIME02 FROM CIL02 WHERE CAID02=".$row['ID01']." ORDER BY CONCAT(DATE02,TIME02) DESC";
            $lastActionRes = odbc_prepare( $conn, $lastActionSql );
            odbc_execute( $lastActionRes );
            $lastRow=odbc_fetch_array( $lastActionRes);
            if( isset($lastRow['DATE02']) && isset($row['TIME02'])){
                if($lastRow['DATE02']==$row['CPDT01']&&($lastRow['CPTI01']==str_replace(":","",$row['TIME02']))){  //condition for last coment timestamp=completion time
     
                    $ticketDescSql = "SELECT DESC01 FROM CIL01 WHERE ID01 = ".$row['ID01'];
                    $rsDesc = odbc_prepare($conn, $ticketDescSql);
                    odbc_execute($rsDesc);
                    
                    while( $descRow = odbc_fetch_array($rsDesc)){
                        $ticketDescription = trim($descRow['DESC01']);
                    }
                    
                    
                    $DESC01 = str_replace( " **** COMPLETE ****", "", $ticketDescription);
                    $CDAT01 = date ( 'Ymd' );
                    $CTIM01 = date ( 'His' );
                    $DESC01 = strtr($DESC01, $GLOBALS['normalizeSaveChars']);		//i-2294568
                    
                    $updateCIL01Sql = "UPDATE CIL01 SET DESC01 = '$DESC01', STAT01=5, CDAT01={$CDAT01}, CTIM01='{$CTIM01}', UDAT01={$CDAT01}, UTIM01='{$CTIM01}', UPID01=" . "0"
                        . " WHERE ID01 = ".$row['ID01'];
                    
                    $rsTicketRes = odbc_prepare($conn, $updateCIL01Sql);
                    odbc_execute($rsTicketRes);
                    
                    $insertCIL01OA = "insert into CIL01OA ";                                                                                                
                    $insertCIL01OA .= " VALUES ( " . $row['ID01'] . ", " . date('Ymd') . ", '" . date('His') . "', ";                                        
                    $insertCIL01OA .= $row['RQID01'] . ", " . $row['RQID01'] . ", 7, " . 0 . ")";                                            
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                                      
                    odbc_execute($cil01oaRes);                                                                                                              
                    
                    $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id ( FACSLIB, "CIL02", "ID02", "" )  ." , ".$row['ID01'].", '"
                    ."Autoresolved" . "', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . 0 . ", 'N')";
                    $rsStep = odbc_prepare($conn, $insertStepSql);
                    odbc_execute($rsStep);
                    echo $row['ID01']," Ticket Autoresolved ";
                }
            }
        };
        echo "<hr />";
}

?>


