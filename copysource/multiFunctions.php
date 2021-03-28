<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            multiFunctions.php<br>
 * Development Reference:   LP0025<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP00025      TS    20/08/2017 Uplift of ticket listing
 *   LP0086       AD    16/10/2019 GLBAU-17773  LPS - Add Buttons to Parent Tickets on Mass Upload
 */
/**
 */

function getRequester( $ticketId ){
    global $CONO, $conn;
    
    $reqSql = "SELECT RQID01 FROM CIL01 WHERE ID01={$ticketId}";
    $resReq= odbc_prepare ( $conn, $reqSql);
    odbc_execute ( $resReq);
    
    
    while ( $row = odbc_fetch_array ( $resReq) ) {
        
        $requester = trim( $row['RQID01'] );
    }
    
    return $requester;
    
}

function checkTicketCompletion( $ticketId ){
    global $CONO, $conn;
    
    $completeSql = "SELECT CLAS01, TYPE01, ID01 FROM CIL01 WHERE ID01={$ticketId}";
    $resComplete= odbc_prepare ( $conn, $completeSql);
    odbc_execute ($resComplete);
    
    
    
    while ( $row = odbc_fetch_array ( $resComplete) ) {
        
        // if( $row['TYPE01'] == 3 ){
        $pfcHasAnswers = check_ticket_answers(  $row ['CLAS01'] , $row ['TYPE01'], $row ['ID01'], "1" );
        $plannerHasAnswers = check_ticket_answers(  $row ['CLAS01'] , $row ['TYPE01'], $row ['ID01'], "2" );
        //}else{
        //    $pfcHasAnswers = true;
        //    $plannerHasAnswers = true;
        //}
        
    }
    
    if( $pfcHasAnswers == false || $plannerHasAnswers == false ){
        return false;
    }else{
        return true;
    }
    
}
/*
 * Function check if ticket is a partent to other tickets (return true if it is a parent)
 */
function checkIfIsParentTicket( $ticketId ){  //lp0086_ad
    global $CONO, $conn;//lp0086_ad
    
    $completeSql = "SELECT CHLF01 FROM CIL01 WHERE ID01={$ticketId}";//lp0086_ad
    $resComplete= odbc_prepare ( $conn, $completeSql);//lp0086_ad
    odbc_execute ($resComplete);//lp0086_ad
    while ( $row = odbc_fetch_array ( $resComplete) ) {//lp0086_ad
        //var_dump($row);
        if ($row['CHLF01']==1) return true;   //lp0086_ad
    }//lp0086_ad
    return false;//lp0086_ad
}//lp0086_ad


