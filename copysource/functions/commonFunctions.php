<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            commonFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related multiple LPS areas, commonly used functions.
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  LP0039      KS    28/03/2018  In the LPS "Register for LPS account" page please add hyperlinks to instruction guidelines (SPIDER 2.0)
 *  
 **/

/**
 * Function creates an array of classification information
 *
 * @return array of classifications
 */
function get_classification_array() {
    global $CONO, $conn;
    
    //D0301 - Changed from CIL09L01 to CIL09V01
    $classSql = "SELECT trim(ID09) as ID09, trim(CLAS09) as CLAS09, trim(CHCK09) as CHCK09 FROM CIL09V01";
    
    $classRes = odbc_prepare ( $conn, $classSql );
    odbc_execute ( $classRes );
    
    //echo $classRes;
    
    
    $classArray = array ();
    while ( $classRow = odbc_fetch_array ( $classRes ) ) {
        array_push ( $classArray, $classRow );
    }
    return $classArray;
    
}

/**
 * Function creates an array of Type information
 *
 * @return array of type information
 */
function get_typeName_array() {
    global $CONO, $conn;
    
    
    //D0301 - Change SQL statement for performance
    //$typeSql = "SELECT CLAS12, TYPE12, TYPE04, CLAS09, ID04 FROM CIL04J05 ORDER BY TYPE04";
    $typeSql = "SELECT CLAS12, TYPE12, TYPE04, ID04 FROM CIL04L00 T1"
        . " INNER JOIN CIL12 T2"
            . " ON T1.ID04 = T2.TYPE12"
                . " ORDER BY TYPE04";
                
                
                $typeRes = odbc_prepare ( $conn, $typeSql );
                odbc_execute ( $typeRes );
                
                //echo $classRes;
                
                
                $typeArray [] = "";
                $typePushArray = array ();
                $i = 0;
                while ( $typeRow = odbc_fetch_array ( $typeRes ) ) {
                    $typeArray [$typeRow ['CLAS12']] ['NAME'] [$i] = trim($typeRow ['TYPE04']);
                    $typeArray [$typeRow ['CLAS12']] ['ID'] [$i] = trim($typeRow ['ID04']);
                    $typeArray [$typeRow ['CLAS12']] ['CLASS'] [$i] = trim($typeRow ['CLAS12']);
                    array_push ( $typePushArray, $typeArray );
                    $i ++;
                }
                
                $m = 0;
                return $typePushArray;
}

/**
 * Function displays page footer
 *
 * @param string $fromPage
 */
function page_footer($fromPage) {
    echo "<center>";
    echo "<table width=100% cellpadding='0' cellspacing='0'>";
    echo "<tr><td>&nbsp</td></tr>";
    echo "<tr><td>&nbsp</td></tr>";
    echo "<tr><td>&nbsp</td></tr>";
    echo "<tr>";
    //D0114 - Removed feedback funcitonality
    //echo "<td class='dimCenter'>If you have any questions, comments or feedback with regards to the Logistics Process Support Application, <br>";
    //echo "please submit <a href='issueTracker.php' target='_new'>feedback</a>, contact Your Service Desk";
    echo "<td class='dimCenter'>If you have any problems with the Logistics Process Support application, <br>";
    echo "please contact your LPS Key User  defined in LPS Intranet page.";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</center>";
}

/**
 * Function retrieves and returns feedback infomation
 *
 * @param integer $id
 * @return array $feedbackRow - entire record of information based on id
 */
function get_feedback($id) {
    global $conn;
    
    $feedbackSql = "SELECT * FROM TRACKER01 WHERE ID01=$id";
    $feedbackRes = odbc_prepare ( $conn, $feedbackSql );
    odbc_execute ( $feedbackRes );
    
    while ( $feedbackRow = odbc_fetch_array ( $feedbackRes ) ) {
        return $feedbackRow;
    }
}

/**
 * Function retrieves and displays list of feedback information
 *
 * @param integer $id
 */
function list_feedback_attachments($id) {
    global $conn;
    
    $feedAttachSql = "SELECT LINK02, ANAM02 FROM TRACKER02 WHERE TRAK02=$id";
    $feedAttachRes = odbc_prepare ( $conn, $feedAttachSql );
    odbc_execute ( $feedAttachRes );
    
    while ( $feedAttachRow = odbc_fetch_array ( $feedAttachRes ) ) {
        echo "<tr>";
        echo "<td><a href='../../attachments/tracking/" . $feedAttachRow ['LINK02'] . "' target='_new'>" . $feedAttachRow ['ANAM02'] . "</a></td>";
        echo "</tr>";
    }
    
}

/*
 * Function returns ticket classification on id
 *
 * @parm int id     ticket id classification is required for
 *
 * @return classification
 */
function get_ticket_class($id) {
    global $conn, $CONO;
    $sql = "SELECT CLAS01 FROM CIL01L00 WHERE ID01=$id";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    //echo $sql;
    while ( $row = odbc_fetch_array ( $res ) ) {
        return $row ['CLAS01'];
    }
}

/**
 * Function retunr name of Ticket Type
 *
 * @param integer $typeId
 * @return string Name of Type
 */
function get_type_name($typeId) {
    global $conn, $CONO;
    
    if( isset( $typeId ) && $typeId != 0 && $typeId != "" ){
        $sql = "SELECT TYPE04 FROM CIL04L00 WHERE ID04 = $typeId";
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
        
        while ( $row = odbc_fetch_array ( $res ) ) {
            return trim ( $row ['TYPE04'] );
        }
    }
    
}

/**
 * Function returns name of Ticket Classification
 *
 * @param integer $classId
 * @return string Name of Classification
 */
function get_class_name($classId) {
    global $conn, $CONO;
    
    if( isset( $classId ) && $classId != 0 && $classId != "" ){
        $sql = "SELECT CLAS09 FROM CIL09L02 WHERE ID09 = $classId";
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
        
        //echo $sql;
        
        
        while ( $row = odbc_fetch_array ( $res ) ) {
            return trim ( $row ['CLAS09'] );
        }
    }
}

/**
 * Function calculates the date of 1 day ago, define by the $dateIn
 *
 * @param integer $dateIn
 * @return unknown
 */
function get_yesterday($dateIn) {
    $year = substr ( $dateIn, 0, 4 );
    $month = substr ( $dateIn, 4, 2 );
    $day = substr ( $dateIn, 6, 2 );
    $yesterday = mktime ( 0, 0, 0, $month, $day - 1, $year );
    
    return $yesterday;
}

/**
 * Funciton retunrs and array of types
 *
 * @return array of types
 */
function types_array() {
    global $conn, $CONO;
    
    $sql = "select ID04, TYPE04 FROM CIL04L00 ORDER BY ID04";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $typeArray = array ();
    while ( $row = odbc_fetch_array ( $res ) ) {
        $typeArray [$row ['ID04']] = $row ['TYPE04'];
    }
    return $typeArray;
    
}

/**
 * Funciton retunrs and array of classifications
 *
 * @return array of classifications
 */
function class_array() {
    global $conn, $CONO;
    
    $sql = "select ID09, CLAS09 FROM CIL09L02 ORDER BY ID09";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $classArray = array ();
    while ( $row = odbc_fetch_array ( $res ) ) {
        $classArray [$row ['ID09']] = $row ['CLAS09'];
    }
    return $classArray;
}

//D0341 - Created to ensure no duplicates.
function check_duplicate_array_vals( $value, $array, $elementName ){
    
    foreach($array as $arr => $v)
    {
        if ($v[ $elementName ] == $value)
            return true;
    }
    return false;
    
}

/**LP0019 change ended**/
function company_array() {
    global $conn, $CONO;
    
    $sql = "SELECT CODE27,CNAM27 FROM DSH27";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    $typeArray = array ();
    while ( $row = odbc_fetch_array ( $res ) ) {
        $compArray [$row ['CODE27']] = $row ['CNAM27'];
    }
    return $compArray;
    
}

/* LP0018 - Function update last login
 *
 */

function update_last_login(){
    global $conn;
    
    if( isset( $_SESSION ['userID'] ) && $_SESSION ['userID'] != "" && $_SESSION ['userID'] != 0 ){
        //$lastLoginSql = "UPDATE HLP05 SET LOGT05 = '" . time() . "' WHERE ID05 = ". $_SESSION ['userID'];
        $lastLoginSql = "UPDATE HLP05 SET LOGT05 = now() WHERE ID05 = ". $_SESSION ['userID'];
        
        
        $lastLoginRes = odbc_prepare ( $conn, $lastLoginSql);
        $result = odbc_execute( $lastLoginRes );
    
    }
}

function show_attachments($id) {
    global $conn, $CONO, $IMG_DIR, $attachmentsUrl;
    
    //**LP0039  $sql = "SELECT DATE07, UFILE07, FILE07, NAME05, ID07, USER07 FROM DSH07J01 WHERE KEY107='$id'";
    $sql = "select DATE07, UFILE07, FILE07, NAME05, ID07, USER07 ";     //**LP0039
    $sql .= " from DSH07 inner join HLP05 ";                            //**LP0039
    $sql .= "   on USER07 = ID05 ";                                     //**LP0039
    $sql .= " where KEY107='" . $id . "' ";                             //**LP0039
    $sql .= "   and PGID07 = 'CIL' ";                                   //**LP0039
    $sql .= "   and WBID07 = 'CIA' ";                                   //**LP0039
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    //echo $sql;
    echo "<center>";
    echo "<table width=90% cellpadding='0' cellspacing='0'>";
    
    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        if ($rowCounter == 0) {
            echo "<TR class='header'>";
            echo "<TD class='header'>Date</TD>";
            echo "<TD class='header'>File</TD>";
            echo "<TD class='header' colspan='2'>Author</TD>";
            echo "</TR>";
        }
        $rowCounter ++;
        if ($rowCounter % 2) {
            echo "<TR>";
        } else {
            echo "<TR class='alternate'>";
        }
        echo "<TD>" . formatDate ( $row ['DATE07'] ) . "</TD>";
        echo "<TD><a href='$attachmentsUrl/tickets/" . $row ['FILE07'] . "' target='_new'>" . $row ['UFILE07'] . "</a></TD>";
        echo "<TD>" . $row ['NAME05'] . "</TD>";
        if ($_SESSION ['userID'] == $row ['USER07'] || $_SESSION['authority'] == "S") {
            echo "<TD width=5%><a href='attachments.php?action=delete&deleteId=" . $row ['ID07'] . "' target='_new'><img src='$IMG_DIR/delete.gif' alt='Delete Attachment' border=0></TD>";
        } else {
            echo "<TD>&nbsp</TD>";
        }
        echo "</TR>";
    }
    echo "</table>";
    echo "</center>";
    
}
