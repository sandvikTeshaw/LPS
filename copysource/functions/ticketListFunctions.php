<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            ticketListFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to ticket listings
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  
 **/


/**
 * Function creates an array of ticket information to be returned
 *
 * @param string $stat
 * @param string $whereClause
 * @param integer $class
 * @param integer $type
 * @param integer $prty
 * @param string $orderBy
 * @param string $from
 * @param integer $listUser
 * @return array $ticketListArray
 */
function list_tickets($stat, $whereClause, $class, $type, $prty, $orderBy, $from, $listUser) {
    global $conn, $keywords, $companyCode, $classification, $requester, $status, $searchMethod, $startDate, $endDate, $source, $eslv;
    
    if (! $prty) {
        $prty = 1;
    }
    if (! $stat || $stat < 5 ) {
        //$stat = 1;
        $stat = 'STAT01 < 5';
    }else{
        $stat = 'STAT01 = 5';
    }

    //Call Stored procedure to get ticket listing
    if ($prty && $class && $type && $prty && $from != "myTickets") {
        
        //D0301 - Change call from Procedure to SQL
        //$sql = "call " . PROGRAM_LIB . ".CIL01P01( $class, $type, $prty, $stat )";
        
        $sql = "SELECT ID01, DESC01, DATE01, UDAT01, PRTY01, RQID01 , UPID01 FROM CIL01L03"
        . " WHERE CLAS01 = $class AND TYPE01 = $type AND PRTY01=$prty AND $stat FETCH FIRST 300 ROWS ONLY";
        
        $stmt = odbc_prepare ( $conn, $sql );
        $result = odbc_execute ( $stmt );
        
        
        $ticketListArray = array ();
        while ( $row = odbc_fetch_array ( $stmt ) ) {
            array_push ( $ticketListArray, $row );
        }
    } elseif ($from == "myTickets" || $from == "userTickets") {
        if ($from == "myTickets") {
            $ticketUserId = $_SESSION ['userID'];
        } else {
            $ticketUserId = $listUser;
        }
        if ($stat != 5) {
            //$sql = "call " . PROGRAM_LIB . ".CIL01P02( $ticketUserId, $stat )";
            
            $sql = "SELECT ID01, DESC01, DATE01, UDAT01, PRTY01, RQID01, UPID01 FROM CIL01L02"
                . " WHERE ( RQID01 = $ticketUserId OR RSID01 = $ticketUserId OR OWNR01 = $ticketUserId OR ESID01 = $ticketUserId OR"
                . " ( PFID01 = $ticketUserId AND KEY101 <> 'N' AND KEY101 <> 'Y' ) )AND $stat ORDER BY PRTY01 , ID01";
                
        } else {
            $sql = "call CIL01P07( $ticketUserId )";
        }
        
        $stmt = odbc_prepare ( $conn, $sql );
        $result = odbc_execute ( $stmt );
        
        $ticketListArray = array ();
        while ( $row = odbc_fetch_array ( $stmt ) ) {
            array_push ( $ticketListArray, $row );
        }
        if( !empty( $ticketListArray )){
 
            //$ticketListArray = sortArrayByField ( $ticketListArray, 0, true );
            
        }
    } elseif ($from == "myResources") {
        
        $ticketUserId = $_SESSION ['userID'];
        
        if ($stat != 5) {
            //$sql = "call " . PROGRAM_LIB . ".CIL01P09( $ticketUserId, $stat )";
            
            $sql = "SELECT ID01, DESC01, DATE01, UDAT01, PRTY01, RQID01, UPID01, OWNR01 FROM CIL01L09"
                . " WHERE RESP01=" . $_SESSION ['userID'] . " AND OWNR01<>" . $_SESSION ['userID'] . " AND POFF01<>"
                    . " " . $_SESSION ['userID'] . "  AND $stat";
                    
        }
        $stmt = odbc_prepare ( $conn, $sql );
        $result = odbc_execute ( $stmt );
        
        $ticketListArray = array ();
        while ( $row = odbc_fetch_array ( $stmt ) ) {
            array_push ( $ticketListArray, $row );
        }
        if( !empty( $ticketListArray )){
            
            $ticketListArray = sortArrayByField ( $ticketListArray, 0, true );
        
        }
        
    }
    //LP0016 - Start of Outbound planner change *************************************************
    elseif($from == "myPlannerTickets"){
        
        $plannerSql = "SELECT PLAN25 from CIL25 WHERE USER25 = " . $_SESSION ['userID'];
        $plannerRes= odbc_prepare ( $conn, $plannerSql);
        odbc_execute ( $plannerRes );
        
        $plannerClause = "";
        $plannerCount = 0;
        while ( $plannerRow = odbc_fetch_array ( $plannerRes) ) {
            
            if( $plannerCount == 0 ){
                $plannerClause = "(" . $plannerRow['PLAN25'];
            }else{
                $plannerClause .= "," . $plannerRow['PLAN25'];
            }
            $plannerCount++;
        }
        if( isset( $plannerClause ) && $plannerClause != "" ){
            $plannerClause .= ")";
        }
        
        if( $plannerCount > 0 ){
 
        
            $sql = "SELECT ID01, DESC01, DATE01, UDAT01, PRTY01, RQID01, UPID01, OWNR01 FROM CIL01L09" . " WHERE BUYR01 in $plannerClause AND STAT01=1";
    
            
            $stmt = odbc_prepare ( $conn, $sql );
            $result = odbc_execute ( $stmt );
            
            $ticketListArray = array ();
            while ( $row = odbc_fetch_array ( $stmt ) ) {
                array_push ( $ticketListArray, $row );
            }
            
            if( !empty( $ticketListArray )){
          
                $ticketListArray = sortArrayByField ( $ticketListArray, 0, true );
           
            }
        
        }
        
        
        //LP0016 - End of Outbound planner change ***************************************************
    }elseif ($from == "advancedSearch") {
        
        //D0128 - Removal of seach elements and setup
        
        $keywords = trim ( $keywords );
        
        //Initialize sql query
        $sql = "SELECT * FROM CIL01 WHERE ";
        
        
        //D0128 - Start - Change the entire structure of the advanced search setup****************************************************************************
        if ($source == "DESC") {
            
            //Create cil01Sql query based on description sent from advanced search page
            $cil01Sql = "select * from CIL01L08 WHERE ( DESC01 = '$keywords' OR DESC01 LIKE '%$keywords'"
            . " OR DESC01 like '$keywords%') AND STAT01=$status";
            
            $cil01Res = odbc_prepare( $conn, $cil01Sql );
            odbc_execute( $cil01Res );
            
            //Set counter to 0
            $idCounter = 0;
            while( $cil01Row = odbc_fetch_array( $cil01Res ) ){
                $idCounter++;
                
                    //Add id of selected file to query holder
                    if( $idCounter == 1 ){
                            $idSqlAddition = " ( ID01 = ${cil01Row['ID01']}";
                    }else{
                        $idSqlAddition .= " OR ID01 = ${cil01Row['ID01']}";
                }
            }
            
    //D0246 - Start - Return Search Functionality *****************************************************************
}elseif( $source == "RETURN" ){
    
    $cil01Sql = "select CAID10 from cil10"
        . " where ATTR10=522"
            . " and text10='" . strtoupper($keywords) . "'";
            
            
            $cil01Res = odbc_prepare( $conn, $cil01Sql );
            odbc_execute( $cil01Res );
            
            //Set counter to 0
            $idCounter = 0;
            while( $cil01Row = odbc_fetch_array( $cil01Res ) ){
                
                $idCounter++;
                
                //Add id of selected file to query holder
                if( $idCounter == 1 ){
                    $idSqlAddition = " ( ID01 = ${cil01Row['CAID10']}";
            }else{
                $idSqlAddition .= " OR ID01 = ${cil01Row['CAID10']}";
}
}

}
//D0246 - End - Return Search Functionality *******************************************************************
else {
    
    //Create cil10Sql query based on description sent from advanced search page
    if( !$classification ){
        $cil10Sql = "select * from CIL10L03 T1 "
            . "inner join CIL07L04 T2 "
                . "on T1.ATTR10 = T2.ATTR07 "
                    . "WHERE text10 ='$keywords' or text10 like '$keywords%' or text10 like '%$keywords'";
    }else{
        $cil10Sql = "select * from CIL10L03 T1 "
            . "inner join CIL07L05 T2 "
                . "on T1.ATTR10 = T2.ATTR07 "
                    . "WHERE (TEXT10 ='$keywords' or TEXT10 like '$keywords%' or TEXT10 like '%$keywords') "
                    . "AND CLAS07=$classification";
    }
    
    
    $cil10Res = odbc_prepare( $conn, $cil10Sql );
    odbc_execute( $cil10Res );
    
    //Set counter to 0
    $idCounter = 0;
    while( $cil10Row = odbc_fetch_array( $cil10Res ) ){
        $idCounter++;
        
        //Add id of selected file to query holder
        if( $idCounter == 1 ){
            $idSqlAddition = " ( ID01 = ${cil10Row['CAID10']}";
    }else{
        $idSqlAddition .= " OR ID01 = ${cil10Row['CAID10']}";
}
}

}

//Append ids to sql
if( isset($idSqlAddition)){
    $idSqlAddition = $idSqlAddition . ")";
    $sql = $sql . $idSqlAddition;
    
    
    if( $classification ){
        if( $idSqlAddition ){
            $sql .= " AND CLAS01 = $classification";
        }else{
            $sql .= " CLAS01 = $classification";
        }
    }
    
    if( $startDate ){
        if( $idSqlAddition || $classification ){
            $sql .= " AND DATE01 >= $startDate";
        }else{
            $sql .= " DATE01 >= $startDate";
        }
    }
    
    if( $endDate ){
        if( $idSqlAddition || $classification || $startDate ){
            $sql .= " AND DATE01 <= $endDate";
        }else{
            $sql .= " DATE01 <= $endDate";
        }
    }
    
    if( $companyCode ){
        if( $idSqlAddition || $classification || $startDate || $endDate ){
            $sql .= " AND CODE01 = $companyCode";
        }else{
            $sql .= " CODE01 = $companyCode";
        }
    }
    if( $requester ){
        if( $idSqlAddition || $classification || $startDate || $companyCode ){
            $sql .= " AND RQID01 = $requester";
        }else{
            $sql .= " RQID01 = $requester";
        }
    }
    //A0246 - Added Return to if statement
    if( $source == "PART" || $source == "RETURN"){
        if( $idSqlAddition || $classification || $startDate || $companyCode || $requester ){
            $sql .= " AND STAT01 = $status";
        }else{
            $sql .= " STAT01 = $status";
        }
    }
}

//D0128 - End - Change the entire structure of the advanced search setup****************************************************************************

if( isset( $idSqlAddition ) && $idSqlAddition != "" ){

        $stmt = odbc_prepare ( $conn, $sql );
        $result = odbc_execute ( $stmt );
        
        $ticketListArray = array ();
        while ( $row = odbc_fetch_array ( $stmt ) ) {
            array_push ( $ticketListArray, $row );
        }
    }
}
//D0270 - Start of Supervisor logic **************************************************************
elseif( $from == "superreports" ){
    
    $ticketUserId = $listUser;
    
    if( $eslv == "0" ){
        $whereClause = " AND ESLV01 = 0";
    }elseif( $eslv == "1" ){
        $whereClause = " AND ESLV01 = 1";
    }
    elseif( $eslv == "2" ){
        $whereClause = " AND ESLV01 > 1";
    }else{
        $whereClause = "";
    }
    
    $sql = "SELECT ID01, DESC01, DATE01, UDAT01, PRTY01, RQID01, UPID01 FROM CIL01L02"
        . " WHERE OWNR01 = $ticketUserId  AND $stat $whereClause ORDER BY PRTY01 , ID01";
        
        
        $stmt = odbc_prepare ( $conn, $sql );
        $result = odbc_execute ( $stmt );
        
        $ticketListArray = array ();
        while ( $row = odbc_fetch_array ( $stmt ) ) {
            array_push ( $ticketListArray, $row );
        }
        
}
//D0270 - End of Supervisor logic **************************************************************
    if( isset($ticketListArray) && (is_array($ticketListArray) || is_object( $ticketListArray) )){
        return $ticketListArray;
    }else{
        return NULL;
    }
}

function basic_ticket_list( $whereClause ){
    global $conn;
    
    $sql = "SELECT ID01, DESC01 FROM CIL01 $whereClause";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $ticketArray = array();
    while ( $row = odbc_fetch_array( $res ) ) {
        
        array_push( $ticketArray, $row );
        
    }
    
    return $ticketArray;
    
}

