<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            userGroupFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing user group related functions
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  LP0055      AD    13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0055      KS    16/04/2019  getUserTicketAuth 
 *  
 **/

function getUserGroupAuth($userID, $groupID, $authType = "READ"){
    //authTypes CRTE,EDIT,READ,CLOS
    global $conn;
    $sql ="";
    if( isset( $userID ) && $userID != "" && isset( $groupID ) && $groupID != "" ){
        $sql = "select * from CIL40 where USER40=$userID AND GRUP40=$groupID";
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
        while($row = odbc_fetch_array ( $res )){
            if($authType == "CRTE" && $row['CRTE40']==1){
                return true;
            }else if($authType == "EDIT" && $row['EDIT40']==1){
                return true;
            }else if($authType == "READ" && $row['READ40']==1){
                return true;
            }else if($authType == "CLOS" && $row['CLOS40']==1){
                return true;
            }else{
                return false;
            }
        }
    }
    return false;//LP0055_AD2
}


function getUserTicketAuth($userID, $ticketID, $authType){                  //**LP0055_KS
    global $conn;                                                           //**LP0055_KS
    $authority = true;                                                      //**LP0055_KS
    $sql ="";                                                               //**LP0055_KS
    $sql = "select * from CIL01 where ID01='" . $ticketID . "' ";           //**LP0055_KS
    $res = odbc_prepare ( $conn, $sql );                                     //**LP0055_KS
    odbc_execute ( $res );                                                   //**LP0055_KS
    if ($row = odbc_fetch_array ( $res )){                                   //**LP0055_KS
        switch ($row['TYPE01']){                                            //**LP0055_KS
            case 130:                                                       //**LP0055_KS
                $authority = getUserGroupAuth($userID, 3, $authType);       //**LP0055_KS
                break;                                                      //**LP0055_KS
        }                                                                   //**LP0055_KS
    }                                                                       //**LP0055_KS
    return $authority;                                                      //**LP0055_KS
}                                                                           //**LP0055_KS

