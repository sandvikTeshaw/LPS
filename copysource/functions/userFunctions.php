<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            userFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing user related functions
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
 * Function pulls a defined used from array
 *
 * @param array $userArray
 * @param integer $id
 * @return sting User's Name
 */
function showUserFromArray($userArray, $id) {
    
    foreach ( $userArray as $user ) {
        if ($user ['ID05'] == $id) {
            return $user ['NAME05'];
        }
    }
    return "Logistics Process Support";
}

/**
 * Function list a user's responsibilities, this is a list of groups of tickets that the user is responsible for
 *
 * @param integer $userId
 * @param array $userArray
 * @param string $parm
 */
function list_my_responsibilities($userId, $userArray, $parm) {
    global $conn, $CONO, $IMG_DIR;
    if ($parm == "actm") {
        $sql = "SELECT PRMD15, BACM13, NAMER5, COUN13 FROM CIL13J03 WHERE CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND ACTM13=$userId ORDER BY PRMD15 ASC";
    } elseif ($parm == "ops") {
        $sql = "SELECT PRMD15, BOPM13, NAME05, COUN13 FROM CIL13J03 WHERE CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND OPMG13=$userId ORDER BY PRMD15 ASC";
    }
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    //echo $sql;
    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        if ($rowCounter == 0) {
            if ($parm == "actm") {
                echo "<tr>";
                echo "<td class='boldBig'><u>Account Manager</u></td>";
                echo "<td><a href='editResponsibilities.php?parm=$parm&user=" . $_SESSION ['userID'] . "' target='_new'>Edit Back-Ups</a></td>";
                echo "</tr>";
            } elseif ($parm == "ops") {
                echo "<tr>";
                echo "<td class='boldBig'><u>Operations Manager</u></td>";
                echo "<td><a href='editResponsibilities.php?parm=$parm&user=" . $_SESSION ['userID'] . "' target='_new'>Edit Back-Ups</a></td>";
                echo "</tr>";
                
            }
            echo "<tr class='header'>";
            echo "<td class='header'>Country</td>";
            echo "<td class='header'>Back-Up</td>";
            echo "</tr>";
        }
        if ($rowCounter % 2) {
            echo "<tr>";
        } else {
            echo "<tr class='alternate'>";
        }
        echo "<td>" . trim ( $row ['PRMD15'] ) . "</td>";
        if ($parm == "ops") {
            echo "<td>" . trim ( $row ['NAME05'] ) . "</td>";
        } elseif ($parm == "actm") {
            echo "<td>" . trim ( $row ['NAMER5'] ) . "</td>";
        }
        echo "</tr>";
        
        $rowCounter ++;
    }
}

/*
 * get_ticket_requester_info
 *
 * Return requester information based on ticket ID
 *
 * @parm int ticketId   ticket ID
 *
 * @return array of Requester Information
 */
//DI868D - Added function to return requester information
function get_ticket_requester_info($ticketId) {
    global $conn, $CONO;
    $sql = "select RQID01, NAME05, EMAIL05, PASS05 FROM CIL01J32 WHERE ID01=$ticketId FETCH FIRST ROW ONLY";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    while ( $row = odbc_fetch_array ( $res ) ) {
        $requesterInfo ['id'] = $row ['RQID01'];
        $requesterInfo ['name'] = $row ['NAME05'];
        $requesterInfo ['email'] = $row ['EMAIL05'];
        $requesterInfo ['pass'] = $row ['PASS05'];
    }
    return $requesterInfo;
}

/**
 * D0097 - Added function for user deletion request
 * Function structures all request
 */
function send_deletion_request_mail( $employeeName, $supervisorName, $supervisorEmail, $groupConfirmation, $employmentConfirmation ) {
    
    global $conn, $CONO, $SITENAME, $mtpUrl, $FROM_MAIL, $LPS_RESOURCE_NAME, $LPS_RESOURCE_EMAIL ;
    
    $subject = "LPS User Deletion Request";
    
    $message = "\n\n<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>"
        . "Dear " . trim ( $LPS_RESOURCE_NAME ) . ",<br><br><br>"
            . "<b>A User Deletion Request has been posted</b><br><br>"
                . "<b>User to be Deleted:</b> $employeeName<br><br>"
                . "<b>Requested By:</b> $supervisorName<br><br><br><br>"
                . "<b><u>Confirmation Responses</u></b><br>"
                    . "<ul><li>Moved to another group at Sandvik? $groupConfirmation</li><br>"
                    . "<li>Still employed by Sandvik? $employmentConfirmation</li></ul>";
                    
                    //Sets up mail to use HTML formatting
                    $strHeaders = "MIME-Version: 1.0\r\n";
                    $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
                    $strHeaders .= "From: " . $FROM_MAIL . "\r\n";
                    
                    if ($cc) {
                        $strHeaders .= "cc: " . $supervisorEmail . "\r\n";
                    }
                    
                    mail ( $LPS_RESOURCE_EMAIL, $subject, $message, $strHeaders );
                    
}

/**
 * D0097b - Added function for user deletion request
 * Function structures all request
 */
function send_account_info_mail( $email ) {
    
    global $conn, $CONO, $SITENAME, $mtpUrl, $FROM_MAIL, $LPS_RESOURCE_NAME, $LPS_RESOURCE_EMAIL ;
    
    $userInfo = user_cookie_info($email);
    
    $subject = "LPS User Account Information";
    
    $message = "\n\n<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>"
        . "Dear " . trim ( $userInfo['NAME05'] ) . ",<br><br><br>"
            . "<b>Your LPS User Account Information is as Follows:</b><br><br>"
                . "<b>Email:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . trim( $userInfo['EMAIL05']) . "<br>"
                    . "<b>Password:</b>&nbsp;&nbsp;&nbsp;" . trim( $userInfo['PASS05']) . "<br><br><br><br>";
                    //Sets up mail to use HTML formatting
                    $strHeaders = "MIME-Version: 1.0\r\n";
                    $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
                    //$FROM_MAIL = str_replace ( "�", "@", trim($FROM_MAIL) );
                    $strHeaders .= "From: " . $FROM_MAIL . "\r\n";
                    
                    $to = trim( $userInfo['EMAIL05']);
                    
                    // $to = str_replace ( "�", "@", trim($to) );
                    //echo $to;
                    // echo $subject;
                    // echo $message;
                    // echo $strHeaders;

					//echo $to . "<hr>" . $subject . "<hr>" . $message . "<hr>" . $strHeaders;
                    mail ( $to, $subject, $message, $strHeaders );
                    
}

//D0359 - Added functionality for backup ID
function get_back_up_id( $userId ){
    global $conn;
    
    if( $userId > 0 ){
        $sql = "SELECT BACK05 FROM HLP05 WHERE ID05=$userId";
        
        $res = odbc_prepare( $conn, $sql );
        odbc_execute ( $res );
        
        
        while( $row = odbc_fetch_array( $res ) ){
            return $row['BACK05'];
            
        }
    }else{
        return 0;
        
    }
    
}



?>