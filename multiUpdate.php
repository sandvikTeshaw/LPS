<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            multiUpdate.php<br>
 * Development Reference:   LP0025<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP00025      TS    20/08/2017 Uplift of ticket listing
 *  LP0034       KS    23/01/2018 Private Message Functionality
 *  LP0025       KS    07/03/2018 LPS Queue - 2.0
 *  LP0044       AD    28/08/2018 Add Buttons to Queue - Logistics Complete & Send to Pricing
 *  LP0064       AD    28/08/2018 Export Fix
 *  LP0055       AD    08/04/2018 GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0055       KS    15/04/2019 fix
 *  LP0068       AD    24/04/2019  GLBAU-16824_LPS Vendor Change
 *  LP0054       AD    20/05/2019 LP0054 - LPS - Create "Assign to ____" Buttons
 *  LP0086       AD    16/10/2019 GLBAU-17773  LPS - Add Buttons to Parent Tickets on Mass Upload
 *  lp0087       AD    21/10/2019 Button assign to inventory Planner
 */
/**
 */

//ini_set('display_errors', 1); 
//ini_set('display_startup_errors', 1); 
//error_reporting(E_ALL);



include 'copysource/config.php';

if( $_SESSION ['userID'] == 1021 ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
include 'copysource/multiFunctions.php';                //**LP0025_KS
include 'copysource/superFunctions.php';                //**LP0025_KS

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--[if IE 8]>    <html class="no-js ie8 ie" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js ie9 ie" lang="en"> <![endif]-->
<!--[if gt IE 9]><![endif]-->
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE_TITLE;?></title>
<meta charset="utf-8">

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<!-- Web Font -->


<!-- Primary Page Layout
================================================== -->
<?php

if (!isset($conn)) {
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {
    
} else {
    echo "Connection Failed";
}

include_once 'copysource/header.php';

if ($_SESSION ['userID']) {
    
    if (! $_SESSION ['classArray'] ) {
        $_SESSION ['classArray'] = get_classification_array ();
    }
    if( ! $_SESSION ['typeArray']){
        $_SESSION ['typeArray'] = get_typeName_array ();
    }
    
    //include_once 'copysource/menu_2.php';
    include_once 'copysource/menu.php';
    //menuFrame ( $SITENAME );
}
if( isset($ticketIds) ){
    
    if( checkIfIsParentTicket($ticketIds[0]) ){
        $parentTicketID = $ticketIds[0];
    }
    if(checkIfIsParentTicket($ticketIds[0]))unset ($ticketIds[0]); //lp0086_ad      //remove parent ticket from list  
  
    if( !isset( $_REQUEST['submit'] ) ){
        $_REQUEST['submit'] = "";
    }
    if( isset( $_REQUEST['submitResolve'] ) &&  $_REQUEST['submitResolve'] == 'Resolve' ){
        $_REQUEST['submit'] = 'Resolve Tickets';
    }
    
    if($_REQUEST['submit'] == 'Resolve Tickets' ){                                                                                                              //**LP0025_KS
        $warningTickets = "";                                                                                                                                                       //**LP0025_KS
        $employeeArray = array();                                                                                                                                                   //**LP0025_KS
        array_push($employeeArray, $_SESSION ['userID'] );                                                                                                                          //**LP0025_KS
        if ($_SESSION ['authority'] != "S"){                                                                                                                                        //**LP0025_KS
            foreach ( $ticketIds as $selId ) {                                                                                                                                      //**LP0025_KS
                $ticketSQL =  "select * ";                                                                                                                                          //**LP0025_KS
                $ticketSQL .= " from CIL01 ";                                                                                                                                       //**LP0025_KS
                $ticketSQL .= " where ID01 = " . $selId . " ";                                                                                                                      //**LP0025_KS

                $ticketRes = odbc_prepare ( $conn, $ticketSQL );
                odbc_execute ( $ticketRes );
                
                while ($ticketRow = odbc_fetch_array( $ticketRes )){                                                                                                                   //**LP0025_KS
                    $superAuthority[] = "";                                                                                                                                         //**LP0025_KS
                    $superAuthArray = get_super_reports_authority($_SESSION['userID'], $employeeArray, $conn, $superAuthority, $ticketRow['RQID01'], 1);                            //**LP0025_KS
                    //**LP0025_KS
                    $pfcHasAnswers = check_ticket_answers($ticketRow['CLAS01'], $ticketRow['TYPE01'], $ticketRow['ID01'], "1" );                                                    //**LP0025_KS
                    //**LP0025_KS
                    if ($superAuthArray['requester'] != true){                                                                                                                      //**LP0025_KS
                        $warning = false;                                                                                                                                           //**LP0025_KS
                        if ($ticketRow['CLAS01'] == 3 && ((trim($ticketRow['KEY201']) == "" && trim($ticketRow['KEY201']) != "N/A") || ($_SESSION['authority'] == "P" ) ||          //**LP0025_KS
                        ($_SESSION['authority'] == "E" )) && $ticketRow['TYPE01'] != 24 && $_SESSION['userID'] != $ticketRow['RQID01'] ||                                   //**LP0025_KS
                        ($_SESSION['authority'] == "L" && $_SESSION['userID'] != $ticketRow['RQID01'])){                                                                    //**LP0025_KS
                            $warning = true;                                                                                                                                        //**LP0025_KS
                        }elseif (($ticketRow['CLAS01'] == 7 && $_SESSION['userID'] != $ticketRow['RQID01'] && $_SESSION['authority'] != "S")){                                      //**LP0025_KS
                            $warning = true;                                                                                                                                        //**LP0025_KS
                        }elseif (($ticketRow['CLAS01'] == 11 || ( $ticketRow['CLAS01'] == 3 && $ticketRow['TYPE01'] !=24)) && !$pfcHasAnswers) {                                    //**LP0025_KS
                            $warning = true;                                                                                                                                        //**LP0025_KS
                        }elseif ($ticketRow['CLAS01'] == 3 && $ticketRow['TYPE01'] == 24 && $_SESSION['userID'] != $ticketRow['RQID01'] && $_SESSION['authority'] != "S"){          //**LP0025_KS
                            $warning = true;                                                                                                                                        //**LP0025_KS
                        }elseif ((($_SESSION['userID'] != $ticketRow['RQID01']) && $_SESSION ['authority'] != "S"  &&  $superAuthArray['requester'] != true )){                     //**LP0025_KS
                            $warning = true;                                                                                                                                        //**LP0025_KS
                        }                                                                                                                                                           //**LP0025_KS
                        if ($ticketRow['CLAS01'] == 5 && $ticketRow['TYPE01'] == 75){                                                                                               //**LP0025_KS
                            $editAuth = getUserGroupAuth($_SESSION['userID'], 2, "EDIT");                                                                                           //**LP0025_KS
                            if (!$editAuth){                                                                                                                                        //**LP0025_KS
                                $warning = true;                                                                                                                                    //**LP0025_KS
                            }                                                                                                                                                       //**LP0025_KS
                        }                                                                                                                                                           //**LP0025_KS
                        
                        $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");                                                                                     //**LP0055_KS
                        if (!$editAuth){                                                                                                                                            //**LP0055_KS
                            $warning = true;                                                                                                                                        //**LP0055_KS
                        }                                                                                                                                                           //**LP0055_KS
                        
                        if ($warning == true){                                                                                                                                      //**LP0025_KS
                            if ($warningTickets == ""){                                                                                                                             //**LP0025_KS
                                $warningTickets = $selId;                                                                                                                           //**LP0025_KS
                            }else{                                                                                                                                                  //**LP0025_KS
                                $warningTickets .= ", " . $selId;                                                                                                                   //**LP0025_KS
                            }                                                                                                                                                       //**LP0025_KS
                        }                                                                                                                                                           //**LP0025_KS
                    }                                                                                                                                                               //**LP0025_KS
                }                                                                                                                                                                   //**LP0025_KS
            }                                                                                                                                                                       //**LP0025_KS
        }                                                                                                                                                                           //**LP0025_KS
        if ($warningTickets != ""){                                                                                                                                                 //**LP0025_KS
            $warningMsg = "<div class='error'>";                                                                                                                                    //**LP0025_KS
            $warningMsg .= "<b>WARNING</b> - One or more ticket cannot be resolved:";                                                                                               //**LP0025_KS
            $warningMsg .= $warningTickets;                                                                                                                                         //**LP0025_KS
            $warningMsg .= "</div>";                                                                                                                                                //**LP0025_KS
        }                                                                                                                                                                           //**LP0025_KS
    }                                                                                                                                                                               //**LP0025_KS
    
    //******************************************************************** START **LP0044_AD ************************************
    if($_REQUEST['submit'] == 'Logistics Complete' ){                                                                                                                                  //**LP0044_AD
        $warningTickets = "";                                                                                                                                                       //**LP0044_AD
        $employeeArray = array();
        $ticketIdsAction = array();//**LP0044_AD
        array_push($employeeArray, $_SESSION ['userID'] );                                                                                                                          //**LP0044_AD
        foreach ( $ticketIds as $selId ) {
            
            
            $ticketSQL =  "select * ";                                                                                                                                          //**LP0044_AD
            $ticketSQL .= " from CIL01 ";                                                                                                                                       //**LP0044_AD
            $ticketSQL .= " where ID01 = " . $selId . " ";                                                                                                                      //**LP0044_AD
           $ticketAction = "NoAction";
            
            $ticketRes = odbc_prepare($conn, $ticketSQL);                                                                                                                        //**LP0044_AD
            odbc_execute($ticketRes);
            
            
            while ($ticketRow = odbc_fetch_array($ticketRes)){                                                                                                                   //**LP0044_AD
                $superAuthority[] = "";                                                                                                                                         //**LP0044_AD
                $superAuthArray = get_super_reports_authority($_SESSION['userID'], $employeeArray, $conn, $superAuthority, $ticketRow['RQID01'], 1);                            //**LP0044_AD
                $pfcHasAnswers = check_ticket_answers($ticketRow['CLAS01'], $ticketRow['TYPE01'], $ticketRow['ID01'], "1" );//**LP0044_AD
                
                $attributeReturnArray [] = "";//**LP0044_AD
                $attributeValues [] = "";//**LP0044_AD
                $attributeValues = get_attribute_values ( $ticketRow ['ID01'] );//**LP0044_AD
                //Display the attributes and attribute information
                echo '<span style="display: none;"> ';//**LP0044_AD
                if( !isset( $orderArray ) ){
                    $orderArray = "";
                }
                $attributeReturnArray = display_attributes ( $ticketRow ['CLAS01'], $ticketRow ['TYPE01'], $attributeValues, $orderArray );  //**LP0044_AD
                echo '</span>';//**LP0044_AD
                //var_dump($attributeReturnArray);
                $plannerHasAnswers = check_ticket_answers(  $ticketRow ['CLAS01'] , $ticketRow ['TYPE01'], $ticketRow ['ID01'], "2" );//**LP0044_AD
                
                $warning = true;  //**LP0044_AD
                $ticketAction = "NoAction";//**LP0044_AD
                //**************************************************************
                if( ($superAuthArray['drp'])|| (($_SESSION['authority'] == "L" || $_SESSION['authority'] == "S") && ($ticketRow['CLAS01'] == 3  || $ticketRow['CLAS01'] == 8 ))){      //**LP0044_AD
                    $oNum = trim ( $attributeReturnArray['SODP'] );//**LP0044_AD
                    $orderNumB = substr ( $oNum, 0, strpos ( $oNum, " " ) );//**LP0044_AD
                    
                    if( count_records ( DATALIB, "OEP40", " WHERE CONO40 = '$CONO' AND ORDN40 = '$orderNumB' AND OSRC40 = '3'" ) > 0 ){//**LP0044_AD
                        //**LP0044_AD  $drpSubmitLabel = "DRP Complete";
                        $drpCount = 1;    //**LP0044_AD
                    }else{//**LP0044_AD
                        //**LP0044_AD  $drpSubmitLabel = "OBP Complete";
                        $drpCount = 0;  //**LP0044_AD
                    }//**LP0044_AD
                    
                    $drpSubmitLabel = "Logistics Complete";  //**LP0044_AD
                    
                    if( $plannerHasAnswers && $drpCount == 1 ){//**LP0044_AD
                        $ticketAction=  "drpComplete";$warning=false;//**LP0044_AD
                    }elseif ( $plannerHasAnswers && $drpCount == 0 ){//**LP0044_AD
                        $ticketAction=  "obpComplete";$warning=false;//**LP0044_AD
                    }//**LP0044_AD
                }//**LP0044_AD
                
                
                if(($superAuthArray['drp']) || (($_SESSION['authority'] == "L" || $_SESSION['authority'] == "S") && ($row['CLAS01'] == 5 || $row['CLAS01'] == 7) && $ticketRow ['TYPE01'] != 75)){ //**LP0086_AD
            //lp0086    if(($superAuthArray['drp']) || (($_SESSION['authority'] == "L" || $_SESSION['authority'] == "S") && $row['CLAS01'] == 5 && $ticketRow ['TYPE01'] != 75)){ //**LP0044_AD
                        $drpSubmitLabel = "Logistics Complete";  //**LP0044_AD
                    if($plannerHasAnswers){    //**LP0044_AD
                        $ticketAction= "priComplete";$warning=false;   //**LP0044_AD                                                                                                                                                                               //**LP0036
                    }                                                          //**LP0044_AD
                }                                                            //**LP0044_AD
                
                $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");                                 //**LP0055_KS
                if (!$editAuth){                                                                                        //**LP0055_KS
                    $warning = true;                                                                                    //**LP0055_KS
                }                                                                                                       //**LP0055_KS
                
                //**************************************************************
                if ($warning == true){                                                                                                                                      //**LP0044_AD
                    if ($warningTickets == ""){                                                                                                                             //**LP0044_AD
                        $warningTickets = $selId;                                                                                                                           //**LP0044_AD
                    }else{                                                                                                                                                  //**LP0044_AD
                        $warningTickets .= ", " . $selId;                                                                                                                   //**LP0044_AD
                    }                                                                                                                                                       //**LP0044_AD
                }                                                                                                                                                           //**LP0044_AD
            }//end tiket_row //**LP0044_AD
            array_push($ticketIdsAction, $ticketAction);//**LP0044_AD
        }                                                                                                                                                                       //**LP0044_AD
        //**LP0044_AD
        if ($warningTickets != ""){                                                                                                                                                 //**LP0044_AD
            $warningMsg = "<div class='error'>";                                                                                                                                    //**LP0044_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket cannot be Logistics Completed:";                                                                                               //**LP0044_AD
            $warningMsg .= $warningTickets;                                                                                                                                         //**LP0044_AD
            $warningMsg .= "</div>";                                                                                                                                                //**LP0044_AD
        }                                                                                                                                                                           //**LP0044_AD
    }                                                                                                                                                                               //**LP0044_AD
    
    if($_REQUEST['submit'] == 'Assign to Pricing' ){ 
        //**LP0044_AD
        $warningTickets = "";                                                                                                                                                       //**LP0044_AD
        //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0044_AD
            //**LP0044_AD
            $ticketSQL =  "select ID01,TYPE01,TEXT10,PCLS35,BRAN16,PRCC16,BPRC16,RSID01, CLAS01";         //**LP0044_AD                                                                                                                                 //**LP0044_AD
            $ticketSQL .= " from CIL01 JOIN CIL10 ON CAID10=ID01 JOIN CIL07 ON (ATTR07=ATTR10 AND HTYP07='PART')";    //serach for part numbers    //**LP0044_AD                                                                                                                               //**LP0044_AD
            $ticketSQL .= "  JOIN PARTS ON (CONO35='DI' AND PNUM35=TRIM(TEXT10)) ";         //find coresponding brand for part numbers //**LP0044_AD
            $ticketSQL .= "  LEFT JOIN CIL16 ON BRAN16=PCLS35 ";  //find coresponding contact person for brand //**LP0044_AD
            $ticketSQL .= " where ID01 = " . $selId . " "; //**LP0044_AD
            //echo '<br>';
            //echo $ticketSQL; //**LP0044_AD
            //echo '<br>';
            $ticketRes = odbc_prepare($conn, $ticketSQL);    //**LP0044_AD
            odbc_execute($ticketRes);//**LP0044_AD
            $warning=true;//**LP0044_AD        
            
            while ($ticketRow = odbc_fetch_array($ticketRes)){  //**LP0044_AD
                //echo '<br>';
                //var_dump($ticketRow);
                //echo '<br>';
                if($ticketRow['PRCC16']>0 || $ticketRow['BPRC16']>0)$warning=false;//**LP0044_AD
              //LP0055_AD2  if ($ticketRow['TYPE01']!=43 ||  $_SESSION ['userID'] == $ticketRow ['RSID01'])$warning=true;// dubious from childtiket.php //**LP0044_AD
                //**LP0055_KS  if (($ticketRow['TYPE01']!=43 || $ticketRow['TYPE01']!=130 )||  $_SESSION ['userID'] == $ticketRow ['RSID01'])$warning=true;// dubious from childtiket.php //LP0055_AD2
                //lp0068_ad if (($ticketRow['TYPE01'] != 43 && $ticketRow['TYPE01'] != 130 ) ||  $_SESSION ['userID'] == $ticketRow ['RSID01']){        //**LP0055_KS
                
               
              //lp0086  if ( $ticketRow['CLAS01'] !=5 && $ticketRow['TYPE01'] != 103 && $ticketRow['TYPE01'] != 43  ){        //**LP0068_ad
                if ( $ticketRow['CLAS01'] !=5 && $ticketRow['TYPE01'] != 103 && $ticketRow['TYPE01'] != 130 && $ticketRow['TYPE01'] != 133 && $ticketRow['TYPE01'] != 43  ){        //**LP0086_ad
                        
                    $warning=true;                                                                                                          //**LP0055_KS
                }                                                                                                                           //**LP0055_KS
                
                $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");                                                     //**LP0055_KS
                if (!$editAuth){                                                                                                            //**LP0055_KS
                    $warning = true;                                                                                                        //**LP0055_KS
                }                                                                                                                           //**LP0055_KS
                
                
            }//end select row
            if ($warning == true){               //**LP0044_AD
                if ($warningTickets == ""){       //**LP0044_AD
                    $warningTickets = $selId;      //**LP0044_AD
                }else{                              //**LP0044_AD
                    $warningTickets .= ", " . $selId;     //**LP0044_AD
                }         //**LP0044_AD
            }           //**LP0044_AD
        } //end tiket          //**LP0044_AD
        //**LP0044_AD
        if ($warningTickets != ""){                     //**LP0044_AD
            $warningMsg = "<div class='error'>";              //**LP0044_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Sent To Pricing:";      //**LP0044_AD
            $warningMsg .= $warningTickets;                   //**LP0044_AD
            $warningMsg .= "</div>";                            //**LP0044_AD
        }                                   //**LP0044_AD
    }                           //**LP0044_AD
    //********************************* LP0054_AD START ***********************************************************
    if($_REQUEST['submit'] == 'Assign to Requestor' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            $ticketSQL =  "select RQID01 ";         //**LP0054_AD                                                                                                                                 //**LP0044_AD
            $ticketSQL .= " from CIL01  ";  //find coresponding contact person
            $ticketSQL .= " where ID01 = " . $selId . " "; //**LP0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);    //**LP0054_AD
            odbc_execute($ticketRes);//**LP0054_AD
            $warning=true;//**LP0054_AD
               
            while ($ticketRow = odbc_fetch_array($ticketRes)){  //**LP0054_AD
                if($ticketRow['RQID01']>0 )$warning=false;//**LP0054_AD
                else {//**LP0054_AD
                    $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                    $warningTickets .= ", " . $selId;//**LP0054_AD
                }//**LP0054_AD
            }//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to Requestor:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }                                   //**LP0054_AD
    }
    if($_REQUEST['submit'] == 'Assign to Buyer' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            /*//lp0086
            $ticketSQL =  "select BUYR01 ";         //**LP0054_AD                                                                                                                                 //**LP0044_AD
            $ticketSQL .= " from CIL01  ";  //find coresponding contact person
            $ticketSQL .= " where ID01 = " . $selId . " "; //**LP0054_AD
            $ticketRes = db2_prepare($conn, $ticketSQL);    //**LP0054_AD
            db2_execute($ticketRes);//**LP0054_AD
            $warning=true;//**LP0054_AD
            while ($ticketRow = db2_fetch_assoc($ticketRes)){  //**LP0054_AD
                if($ticketRow['BUYR01']>0 )$warning=false;//**LP0054_AD
                
            *///lp0086
            if(findBuyer($selId)>0);//lp0086
                else {//**LP0054_AD
                    $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                    $warningTickets .= ", " . $selId;//**LP0054_AD
                }//**LP0054_AD
            //lp0086}//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to Buyer:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }                                   //**LP0054_AD
        
        
    }if($_REQUEST['submit'] == 'Assign to OBP' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            $ticketSQL =  "select POFF01 ";         //**LP0054_AD                                                                                                                                 //**LP0044_AD
            $ticketSQL .= " from CIL01  ";  //find coresponding contact person
            $ticketSQL .= " where ID01 = " . $selId . " "; //**LP0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);    //**LP0054_AD
            odbc_execute($ticketRes);//**LP0054_AD
            $warning=true;//**LP0054_AD
            
            while ($ticketRow = odbc_fetch_array($ticketRes)){  //**LP0054_AD
                if($ticketRow['POFF01']>0 )$warning=false;//**LP0054_AD
                else {//**LP0054_AD
                    $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                    $warningTickets .= ", " . $selId;//**LP0054_AD
                }//**LP0054_AD
            }//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to OBP:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }   //**LP0054_AD
        
        
        
    }
    if($_REQUEST['submit'] == 'Assign to PFC' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            $pfc=findPFC($selId);//**LP0054_AD
            $warning=true;//**LP0054_AD
            if($pfc>0 )$warning=false;//**LP0054_AD
            else {//**LP0054_AD
                $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                $warningTickets .= ", " . $selId;//**LP0054_AD
            }//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to PFC:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }   //**LP0054_AD
    }   //**LP0054_AD
    if($_REQUEST['submit'] == 'Assign to Freight' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            //$pfc=findFreightContact($selId);//**LP0054_AD
            
            $ticketSQL =  "select CLAS01, TYPE01 "                                                                                                                              //**LP0044_AD
            . " FROM CIL01 WHERE ID01 = " . $selId . " "; //**LP0054_AD
            
            $ticketRes = odbc_prepare($conn, $ticketSQL);    //**LP0054_AD
            odbc_execute($ticketRes);//**LP0054_AD
            
            
            while ( $ticketRow = odbc_fetch_array ( $ticketRes ) ) {
                if( $ticketRow['CLAS01'] != 7 ){
                    $pfc = findFreightContact($selId);//LP0054_AD
                }elseif( $ticketRow['TYPE01'] == 31 || $ticketRow['TYPE01'] == 32|| $ticketRow['TYPE01'] == 33 || $ticketRow['TYPE01'] == 34){
                    
                    $pfc = findFreightContactAttrib($selId, $ticketRow['TYPE01']);//LP0054_AD
                }
            }
            
            $warning=true;//**LP0054_AD
            if($pfc>0 )$warning=false;//**LP0054_AD
            else {//**LP0054_AD
                $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                $warningTickets .= ", " . $selId;//**LP0054_AD
            }//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to Freight:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }   //**LP0054_AD
    }   //**LP0054_AD
    if($_REQUEST['submit'] == 'Assign to TSD' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            $pfc=findTSD($selId);//**LP0054_AD
            $warning=true;//**LP0054_AD
            if($pfc>0 )$warning=false;//**LP0054_AD
            else {//**LP0054_AD
                $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                $warningTickets .= ", " . $selId;//**LP0054_AD
            }//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to TSD:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }   //**LP0054_AD
    }   //**LP0054_AD
    if($_REQUEST['submit'] == 'Assign to Warehouse' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            //$pfc=findWarehouseContact($selId);//**LP0054_AD
            $ticketSQL =  "select CLAS01, TYPE01 "                                                                                                                              //**LP0044_AD
                       . " FROM CIL01 WHERE ID01 = " . $selId . " "; //**LP0054_AD
                       
           $ticketRes = odbc_prepare($conn, $ticketSQL);    //**LP0054_AD
           odbc_execute($ticketRes);//**LP0054_AD
            
            
           while ( $ticketRow = odbc_fetch_array ( $ticketRes ) ) {
                
    
                if( $ticketRow['CLAS01'] != 7 ){
             
                    $pfc = findWarehouseContact($ID01);//LP0054_AD
                }else{
           
                    $pfc = findWarehouseContactAttrib($selId, $ticketRow['TYPE01']);
                }
            }
            $warning=true;//**LP0054_AD
            if($pfc>0 )$warning=false;//**LP0054_AD
            else {//**LP0054_AD
                $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                $warningTickets .= ", " . $selId;//**LP0054_AD
            }//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to Warehouse:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }   //**LP0054_AD
    }   //**LP0054_AD
    if($_REQUEST['submit'] == 'Assign to Sourcing' ){  //**LP0054_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0054_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0054_AD
            $pfc=findSrcContact($selId);//**LP0054_AD
            $warning=true;//**LP0054_AD
            if($pfc>0 )$warning=false;//**LP0054_AD
            else {//**LP0054_AD
                $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0054_AD
                $warningTickets .= ", " . $selId;//**LP0054_AD
            }//**LP0054_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0054_AD
            if (!$editAuth){                                       //**LP0054_AD
                $warning = true; //**LP0054_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0054_AD
            }                       //**LP0054_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0054_AD
            $warningMsg = "<div class='error'>";              //**LP0054_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to Sourcing:";      //**LP0054_AD
            $warningMsg .= $warningTickets;                   //**LP0054_AD
            $warningMsg .= "</div>";                            //**LP0054_AD
        }   //**LP0054_AD
    }   //**LP0054_AD
    if($_REQUEST['submit'] == 'Assign to IP' ){  //**LP0087_AD                                                                                                                                //**LP0044_AD
        $warningTickets = "";  //**LP0087_AD                                                                                                                                                      //**LP0044_AD
        foreach ( $ticketIds as $selId ) {//**LP0087_AD
            $pfc=findIPContact($selId);//**LP0087_AD
            $warning=true;//**LP0087_AD
            if($pfc>0 )$warning=false;//**LP0087_AD
            else {//**LP0087_AD
                $warningMsg .= " Contact person not defined for ticket ".$selId;//**LP0087_AD
                $warningTickets .= ", " . $selId;//**LP0087_AD
            }//**LP0087_AD
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");  //**LP0087_AD
            if (!$editAuth){                                       //**LP0087_AD
                $warning = true; //**LP0087_AD
                $warningMsg .= "You do not have permission to change ticket $selId (READ ONLY)"; //**LP0087_AD
            }                       //**LP0087_AD
            
        } //end tiket
        
        
        if ($warningTickets != ""){                     //**LP0087_AD
            $warningMsg = "<div class='error'>";              //**LP0087_AD
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be Assigned to Inventory Planner:";      //**LP0087_AD
            $warningMsg .= $warningTickets;                   //**LP0087_AD
            $warningMsg .= "</div>";                            //**LP0087_AD
        }   //**LP0087_AD
    }   //**LP0087_AD
    
    //********************************* LP0054_AD END *************************************************************
    
    
    if(($_REQUEST['submit'] == 'Add Comments') || ($_REQUEST['submit'] == 'Re-Assign') ){                           //**LP0055_KS                                                                                                         
        $warningTickets = "";                                                                                       //**LP0055_KS
        foreach ($ticketIds as $selTicket){                                                                         //**LP0055_KS
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");                                 //**LP0055_KS
            if (!$editAuth){                                                                                        //**LP0055_KS
               $warning = true;                                                                                     //**LP0055_KS
            }                                                                                                       //**LP0055_KS
            if (isset($warning) && $warning == true){                                                               //**LP0055_KS
               if ($warningTickets == ""){                                                                          //**LP0055_KS
                   $warningTickets = $selTicket;                                                                    //**LP0055_KS
               }else{                                                                                               //**LP0055_KS
                   $warningTickets .= ", " . $selTicket;                                                            //**LP0055_KS
               }                                                                                                    //**LP0055_KS
            }                                                                                                       //**LP0055_KS
        }                                                                                                           //**LP0055_KS
        if ($warningTickets != ""){                                                                                 //**LP0055_KS
            $warningMsg = "<div class='error'>";                                                                    //**LP0055_KS
            $warningMsg .= "<b>WARNING</b> - One or more ticket(s) cannot be changed without authorities :";        //**LP0055_KS
            $warningMsg .= $warningTickets;                                                                         //**LP0055_KS
            $warningMsg .= "</div>";                                                                                //**LP0055_KS
        }                                                                                                           //**LP0055_KS
    }                                                                                                               //**LP0055_KS
    
    
    //**LP0044_AD if( $_REQUEST['submit'] == 'Add Comments' || $_REQUEST['submit'] == 'Resolve Tickets'  ){
    //**LP0054_AD if( $_REQUEST['submit'] == 'Add Comments' || $_REQUEST['submit'] == 'Resolve Tickets' || $_REQUEST['submit'] == 'Logistics Complete' || $_REQUEST['submit'] == 'Send to Pricing' ){ //**LP0044_AD
    if( $_REQUEST['submit'] == 'Add Comments' || //**LP0054_AD
        $_REQUEST['submit'] == 'Resolve Tickets' || //**LP0054_AD
        $_REQUEST['submit'] == 'Logistics Complete' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to TSD' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to Buyer' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to PFC' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to Warehouse' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to Sourcing' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to Freight' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to OBP' || //**LP0054_AD
        $_REQUEST['submit'] == 'Assign to IP' || //**LP0087_AD
        $_REQUEST['submit'] == 'Assign to Requestor' ||
        $_REQUEST['submit'] == 'Assign to Pricing'
        ){ //**LP0054_AD
            //************************************************************************************* END **LP0044_AD ******************************************************************
        
        ?>
        <form method='post' action='saveMultiUpdate.php'>
        <div id="wrapper">
        	
            <div class="container">
            	
                <div class="col-md-8 col-sm-8 col-xs-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title">Multi Ticket Update</h3>
                        </div><!--panel heading-->
                        
                        <table class="data-table" >
                        <thead>
                        	<tr>&nbsp;</tr>
                        	<tr>&nbsp;</tr>
                            <th>Add Comment:</th>
                        	<tr>
                        		<td><textarea rows="5" cols="100" name='comment' id='comment'></textarea></td>
                        	</tr>
                        	
                        	<?php                                                              //**LP0034
                                echo "<tr>";                                                   //**LP0034
                                echo "<td>";                                                   //**LP0034
                        		if (($_SESSION ['authority'] != "E") and                       //**LP0034
                        		    ($_SESSION ['authority'] != "")    ){                      //**LP0034
            					    echo "<select name='private'>";                            //**LP0034
                					echo "  <option value='N'>Public</option>";                //**LP0034
                					echo "  <option value='Y'>Private</option>";               //**LP0034
                					echo "</select>";                                          //**LP0034
            					}else{                                                         //**LP0034 
                					echo "<input type='hidden' name='private' value='N'>";     //**LP0034
            					}                                                              //**LP0034
            					echo "</td>";                                                  //**LP0034
            					echo "</tr>";                                                  //**LP0034
            					?>															   <!-- LP0034 -->
            					
                        	</thead>
                       	</table>
                       	<table class="data-table">
                  			<tr>
                  					
                        		<td>
                        		<input type='hidden' name='parentTicketID' value='<?php echo $parentTicketID;?>'/>
                        		<input id='submit' type='submit' name = 'submit' value = 'Continue'></td>
                        		<td><input id='submit' type='submit' name = 'submit' value = 'Cancel'></td>
                        	</tr>
                        	<?php 
                        	foreach ( $ticketIds as $selId ) {
                        	    
                        	    ?><input type='hidden' name='ticketIds[]' id='ticketIds[]' value='<?php echo $selId;?>'/><?php
                        	}
                            
                        	//if( is_array( $ticketIdsAction) ){
                        	if( isset( $ticketIdsAction )){
                                foreach ( $ticketIdsAction as $selIdA ) {                                                                          //**LP0044_AD
                                    echo "  <input type='hidden' name='ticketIdsAction[]' id='ticketIdsAction[]' value='" . $selIdA . "'/>";       //**LP0044_AD
                                } //**LP0044_AD
                        	}
                        	//}
                        	?>
                        	
                        	<input type='hidden' name='saveAction' id='saveAction' value='<?php echo $_REQUEST['submit']?>'/>
                        </table>
                    </div>
                </div>
           </div>
      </div>
       </form>
    <?php 
    
    if( isset($warningMsg ) ){
        echo $warningMsg;                                                                                           //**LP0055_KS
    }//**LP0025_KS
    
    }elseif ($_REQUEST['submit'] == 'Re-Assign'){                                                                   //**LP0025_KS
                                                                                                                    //**LP0025_KS
        echo "<form method='post' action='saveMultiUpdate.php'>";                                                   //**LP0025_KS
        echo "<div id='wrapper'>";                                                                                  //**LP0025_KS
        echo "<div class='container'>";                                                                             //**LP0025_KS
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";                                                            //**LP0025_KS   
        echo "<div class='panel panel-default'>";                                                                   //**LP0025_KS
                                                                                                                    //**LP0025_KS
        echo " <div class='panel-heading'>";                                                                        //**LP0025_KS
        echo " <h3 class='panel-title'>Multi Ticket Update</h3>";                                                   //**LP0025_KS
        echo " </div><!--panel heading-->";                                                                         //**LP0025_KS
                                                                                                                    //**LP0025_KS
        echo " <table class='data-table'>";                                                                         //**LP0025_KS
        echo "      <thead>";                                                                                       //**LP0025_KS
        echo "     	    <tr>&nbsp;</tr>";                                                                           //**LP0025_KS
        echo "         	<tr>&nbsp;</tr>";                                                                           //**LP0025_KS
                                                                                                                    //**LP0025_KS
        echo "<th>Add Comment:</th>";                                                                               //**LP0025_KS
        echo "<tr>";                                                                                                //**LP0025_KS
        echo "<td><textarea rows='5' cols='100' name='comment' id='comment'></textarea></td>";                      //**LP0025_KS
        echo "</tr>";                                                                                               //**LP0025_KS
        echo "<tr>";                                                                                                //**LP0025_KS //**LP0034
        echo "<td>";                                                                                                //**LP0025_KS //**LP0034
        if (($_SESSION ['authority'] != "E") and                                                                    //**LP0025_KS //**LP0034
        ($_SESSION ['authority'] != "")    ){                                                                       //**LP0025_KS //**LP0034
            echo "<select name='private'>";                                                                         //**LP0025_KS //**LP0034
            echo "  <option value='N'>Public</option>";                                                             //**LP0025_KS //**LP0034
            echo "  <option value='Y'>Private</option>";                                                            //**LP0025_KS //**LP0034
            echo "</select>";                                                                                       //**LP0025_KS //**LP0034
        }else{                                                                                                      //**LP0025_KS //**LP0034
            echo "<input type='hidden' name='private' value='N'>";                                                  //**LP0025_KS //**LP0034
        }                                                                                                           //**LP0025_KS //**LP0034
        echo "</td>";                                                                                               //**LP0025_KS //**LP0034
        echo "</tr>";                                                                                               //**LP0025_KS //**LP0034
                                                                                                                    //**LP0025_KS
        echo "         	<tr>&nbsp;</tr>";                                                                           //**LP0025_KS
        echo "              <th>New owner:</th>";                                                                   //**LP0025_KS
        echo "        	</tr>";                                                                                     //**LP0025_KS
        echo "        	<tr>";                                                                                      //**LP0025_KS
        echo "         		<td>";                                                                                  //**LP0025_KS 
                                                                                                                    //**LP0025_KS
        echo "<select  name='newAssignee' class='form-control' style='width: 400px !important;'>";                  //**LP0025_KS
        $userArray = get_user_list();                                                                               //**LP0025_KS
        foreach ($userArray as $users){                                                                             //**LP0025_KS
            echo "<option ";                                                                                        //**LP0025_KS
            if (trim($users['ID05']) == trim($_SESSION ['userID'])) {                                               //**LP0025_KS
                echo "SELECTED ";                                                                                   //**LP0025_KS
            }                                                                                                       //**LP0025_KS
            echo "value='" . trim($users['ID05']) . "'>" . trim($users['NAME05']) . "</option>";                    //**LP0025_KS
        }                                                                                                           //**LP0025_KS
        echo "</select>";                                                                                           //**LP0025_KS
                                                                                                                    //**LP0025_KS
        echo "         		</td>";                                                                                 //**LP0025_KS 
        echo "         	</tr>";                                                                                     //**LP0025_KS
        echo "     	</thead>";                                                                                      //**LP0025_KS
        echo "	</table>";                                                                                          //**LP0025_KS
                                                                                                                    //**LP0025_KS
        echo " 	<table class='data-table'>";                                                                        //**LP0025_KS
        echo "  	<tr>";                                                                                          //**LP0025_KS
        echo "          <td><input id='submit' type='submit' name = 'submit' value = 'Continue'></td>";             //**LP0025_KS
        echo "     		<td><input id='submit' type='submit' name = 'submit' value = 'Cancel'></td>";               //**LP0025_KS
        echo "     	</tr>";                                                                                         //**LP0025_KS
                                                                                                                    //**LP0025_KS
        foreach ( $ticketIds as $selId ) {                                                                          //**LP0025_KS
            echo "  <input type='hidden' name='ticketIds[]' id='ticketIds[]' value='" . $selId . "'/>";             //**LP0025_KS
        }                                                                                                           //**LP0025_KS
                                                                                                                    //**LP0025_KS
        echo "      <input type='hidden' name='saveAction' id='saveAction' value='" . $_REQUEST['submit'] . "'/>";  //**LP0025_KS
        echo "  </table>";                                                                                          //**LP0025_KS
        echo "</div>";                                                                                              //**LP0025_KS
        echo "</div>";                                                                                              //**LP0025_KS
        echo "</div>";                                                                                              //**LP0025_KS
        echo "</div>";                                                                                              //**LP0025_KS
        echo "</form>";                                                                                             //**LP0025_KS
        
        if( isset($warningMsg ) ){
            echo $warningMsg;                                                                                           //**LP0055_KS
        }
    }elseif (  $_REQUEST['submit'] == 'Export'){ 
        
        //Get array of ticket attributes
        $attrArray = array();
        $attrSQL = "SELECT HTYP07, ATTR07, CLAS07, TYPE07 FROM CIL07 WHERE HTYP07='PART' OR HTYP07='SODP'";
        $attrRes = odbc_prepare($conn, $attrSQL);
        odbc_execute($attrRes);
        
        
        while ($attrRow = odbc_fetch_array( $attrRes )){
            
            $attrArray[ trim($attrRow['HTYP07']) ][ trim($attrRow['CLAS07']) ][ trim($attrRow['TYPE07']) ] = trim($attrRow['ATTR07']);
        }
        
        $userArray_delInc = get_user_list_del_included();
        $userArray = get_user_list();
        $classArray = class_array();
        $sClassArray = array( "9"=> "Returns", "3"=> "GOP", "5" => "Pricing", "7" => "Inbound", "8" => "GMM", "11" => "Regional" );
        $typeArray = types_array();
        $companyArray = company_array();
        
        
        $idCounter = 0;
        $idClause = "";
    
        foreach ( $ticketIds as $selTicket ){
        $idCounter++;
        
            if( $idCounter == 1 ){
                $idClause = " ( ID01 = $selTicket";
            }else{
                $idClause .= " OR ID01 = $selTicket";
            }
        }
        if( $idClause != "" ){
            $idClause .= ")";
        }
        //LP0064 - Add STAT01 to the Query
        //**LP0025_KS  $exportSQL = "SELECT ID01, CLAS01, TYPE01, PRTY01, DATE01, UDAT01, OWNR01, RQID01, POFF01, CODE01, TIME01, UTIM01, UPID01, STRC01, BUYR01 FROM CIL01 WHERE {$idClause}";
        $exportSQL = "SELECT ID01, CLAS01, TYPE01, PRTY01, DATE01, UDAT01, OWNR01, RQID01, POFF01, CODE01, TIME01, UTIM01, UPID01, STRC01, BUYR01 ";    //**LP0025_KS
        $exportSQL .= " , PCPD01, CPDT01, STAT01, PRNT01, CPTI01, PCPT01";                                                                                                             //**LP0025_KS
        $exportSQL .= " FROM CIL01 ";                                                                                                                   //**LP0025_KS    
        $exportSQL .= " WHERE {$idClause}";                                                                                                             //**LP0025_KS
       
        $rsExport = odbc_prepare($conn, $exportSQL);
        odbc_execute($rsExport);
        
        $filterUser = user_info_by_id( $_SESSION['userID'] );
        
        $fname = './filterExports/' . trim($filterUser['NAME05']) . '_export.csv';
        $fp = fopen( $fname, 'w+');
        //**LP0025_KS  $headerlineInsert = "TicketID,Company,Classification,Priority,Type,Item Number, Order Number, Requester, Date Created, Last Update, Last Updater, Owner, Planner, Stockroom, Buyer, Days Open, Last Comment" .  "\r\n";
        
        //**LP0025_KS  fwrite($fp, $headerlineInsert );
        
        $exportCSV = array();                               //**LP0025_KS
        $exportCSV[] = "Parent Ticket";                     //**LP0025_KS
        $exportCSV[] = "Ticket ID";                         //**LP0025_KS
        $exportCSV[] = "Company";                           //**LP0025_KS
        $exportCSV[] = "Classification";                    //**LP0025_KS
        $exportCSV[] = "Priority";                          //**LP0025_KS
        $exportCSV[] = "Type";                              //**LP0025_KS
        $exportCSV[] = "Item Number";                       //**LP0025_KS
        if($_REQUEST['queryType'] != "frontLine"){          //**LP0025_KS
            $exportCSV[] = "Supplier";                      //**LP0025_KS
        }                                                   //**LP0025_KS
        $exportCSV[] = "Order Number";                      //**LP0025_KS
        $exportCSV[] = "Requester";                         //**LP0025_KS
        $exportCSV[] = "Date Created";                      //**LP0025_KS
        $exportCSV[] = "Last Update";                       //**LP0025_KS
        $exportCSV[] = "Last Updater";                      //**LP0025_KS
        $exportCSV[] = "Owner";                             //**LP0025_KS
        $exportCSV[] = "Planner";                           //**LP0025_KS
        $exportCSV[] = "Stockroom";                         //**LP0025_KS
        $exportCSV[] = "Buyer";                             //**LP0025_KS
        $exportCSV[] = "Days Open";                         //**LP0025_KS
        $exportCSV[] = "Status";                            //**LP0025_KS
        if($_REQUEST['queryType'] != "frontLine"){          //**LP0025_KS
            $exportCSV[] = "Complete Date";                 //**LP0064_TS
            $exportCSV[] = "PFC Date";
            $exportCSV[] = "SLA Target";                    //**LP0025_KS
        }                                                   //**LP0025_KS
                               //**LP0025_KS
        $exportCSV[] = "Res.";                              //**LP0025_KS
        $exportCSV[] = "Last Comment";                      //**LP0025_KS
                                                            //**LP0025_KS
        fputcsv($fp, $exportCSV);                           //**LP0025_KS
        unset($exportCSV);                                  //**LP0025_KS
        
        
        while ($row = odbc_fetch_array( $rsExport )){
            
            $partAttr = "";
            $orderAttr = "";
            if( isset( $attrArray['PART'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ] ) ){
                $partAttr = $attrArray['PART'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ];
            }else{
                $partAttr = "";
            }
     
            if( isset($attrArray['SODP'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ]) ){
                $orderAttr = $attrArray['SODP'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ];
            }else{
                $orderAttr = "";
            }
            
            $attrWhereClause = " WHERE CAID10 = " . $row['ID01'];
            
            if( $partAttr != "" &&  $orderAttr != "" ){
                $attrWhereClause .= " AND ( ATTR10 = $partAttr OR ATTR10 = $orderAttr )";
            }elseif( $partAttr != "" &&  $orderAttr == "" ){
                $attrWhereClause .= " AND ( ATTR10 = $partAttr  )";
            }elseif($partAttr == "" &&  $orderAttr != ""  ){
                $attrWhereClause .= " AND ( ATTR10 = $orderAttr  )";
            }
            
            $itemSql = "SELECT TEXT10, ATTR10 from CIL10 $attrWhereClause";
            
            $rsItem = odbc_prepare($conn, $itemSql);
            odbc_execute($rsItem);
            

            //$item = db2_fetch_assoc($rsItem);
            $itemVal = "";
            $orderVal = "";
            while ($attrRowTxt = odbc_fetch_array( $rsItem )){
                if( $attrRowTxt['ATTR10'] == $partAttr ){
                    $itemVal = trim($attrRowTxt['TEXT10']);
                }elseif ( $attrRowTxt['ATTR10'] == $orderAttr ){
                    //$orderVal = trim($attrRowTxt['TEXT10']);
                    
                    if( strpos(trim($attrRowTxt['TEXT10']), " ") > 0 ){
                        $orderVal = substr(trim($attrRowTxt['TEXT10']), 0, strpos(trim($attrRowTxt['TEXT10']), " "));
                    }else{
                        $orderVal = trim($attrRowTxt['TEXT10']);
                    }
                }
            }
            
            
            $supplier = "";                                                         //**LP0025_KS
            $supplierSql = "call SUPLP01('$itemVal')";                              //**LP0025_KS
            $supplierRes = odbc_prepare ( $conn, $supplierSql );                     //**LP0025_KS
            odbc_execute ( $supplierRes );                                           //**LP0025_KS
            while ( $supplierRow = odbc_fetch_array ( $supplierRes ) ) {             //**LP0025_KS
                $supplier = trim($supplierRow['DSSP35']) . " - " . trim($supplierRow['SNAM05']);  //**LP0025_KS
            }                                                                       //**LP0025_KS
            
            $createDate = substr($row['DATE01'], 0,4 ) . "-" . substr($row['DATE01'], 4,2 ) . "-" . substr($row['DATE01'], 6,2 );
            $start = strtotime( DATE( "Y-m-d" ) );
            $end = strtotime( $createDate );
            $daysOpenVal = ceil(abs($end - $start) / 86400);
            
            $requesterVal = trim(showUserFromArray ( $userArray_delInc, $row['RQID01'] ));
            $ownerVal = trim(showUserFromArray ( $userArray_delInc, $row['OWNR01'] ));
            $plannerVal = trim(showUserFromArray ( $userArray_delInc, $row['POFF01'] ));
            $updaterVal = trim(showUserFromArray ( $userArray_delInc, $row['UPID01'] ));
            
            if( isset( $buyerNumber ) ){
                $buyerVal = trim(showUserFromArray ( $userArray_delInc, $buyerNumber));
            }else{
                $buyerVal = "";
            }
      
            $created = formatDate ( $row['DATE01'] ) . " " . substr($row['TIME01'], 0, 2) . ":" . substr($row['TIME01'], 2, 2) . ":" . substr($row['TIME01'], 4, 2);
            
            if( $row['UDAT01'] > 0 ){
                $lastUpdate = formatDate ( $row['UDAT01'] ) . " " . substr($row['UTIM01'], 0, 2) . ":" . substr($row['UTIM01'], 2, 2) . ":" . substr($row['UTIM01'], 4, 2);
            }else{
                $lastUpdate = "-";
            }
            $companyVal = $companyArray[ $row['CODE01'] ];
            $classificationVal = $classArray[$row['CLAS01']];
            $typeVal = $typeArray[ $row['TYPE01'] ];
            //**LP0025_KS  $stockroom = trim($row['STRC01']);
            $stockroom = "";
            $stockroom = stockrooms(trim($row['ID01']));            //**LP0025_KS
            if( $row['CPDT01'] > 0  ){
                $completed = formatDate($row['CPDT01']) . " " . substr($row['CPTI01'], 0, 2) . ":" . substr($row['CPTI01'], 2, 2) . ":" . substr($row['CPTI01'], 4, 2);                //**LP0025_KS
            }else{
                $completed =  "-";
            }
            if( $row['PCPD01'] > 0 ){
                $pfcDate = formatDate($row['PCPD01']) . " " . substr($row['PCPT01'], 0, 2) . ":" . substr($row['PCPT01'], 2, 2) . ":" . substr($row['PCPT01'], 4, 2);                //**LP0025_KS;                  //**LP0025_KS 
            }else{
                $pfcDate = "-";
            }
            
            if( $itemVal != "" && $orderVal != "" && $stockroom == "" ){  //**LP0064_TS
                $stkRoomSQL = "SELECT LOCD55 FROM OEP55"; //**LP0064_TS
                $stkRoomSQL .= " WHERE CONO55='DI' AND CATN55='$itemVal' AND ORDN55='$orderVal' AND STAT55 <>'X'";//**LP0064_TS
                $stkRoomRes = odbc_prepare($conn, $stkRoomSQL);          //**LP0064_TS                                                                                                                                      
                odbc_execute($stkRoomRes);                               //**LP0064_TS
                $stkRoomRow = odbc_fetch_array($stkRoomRes);             //**LP0064_TS
                $stockroom = trim($stkRoomRow['LOCD55']);               //**LP0064_TS
            }
            
            
            //SLA Logic                                                                                                                                                                                     //**LP0025_KS
            $slaTargetDate = "";                                                                                                                                                                            //**LP0025_KS
            $ticketDateTime = trim($row['DATE01']) . " " . trim($row['TIME01']);                                                                                                                            //**LP0025_KS
            $sqlSLA = "SELECT SLTM45, BDFL45 FROM CIL45 WHERE ACTV45 <> 0 " . " AND CLAS45 = ". trim($row['CLAS01']) ." AND TYPE45 = ". trim($row['TYPE01']). " AND PRTY45 = " . trim($row['PRTY01']);      //**LP0025_KS
            $rsSLA = odbc_prepare($conn, $sqlSLA);                                                                                                                                                           //**LP0025_KS
            odbc_execute($rsSLA);                                                                                                                                                                            //**LP0025_KS
            $SLA = odbc_fetch_array($rsSLA);                                                                                                                                                                 //**LP0025_KS
            if(count($SLA) > 1){                                                                                                                                                                            //**LP0025_KS
                $formatSLA = "+" . trim($SLA['SLTM45']) . " hours";                                                                                                                                         //**LP0025_KS
                //Only Business Days included                                                                                                                                                               //**LP0025_KS
                if($SLA['BDFL45'])                                                                                                                                                                          //**LP0025_KS
                {                                                                                                                                                                                           //**LP0025_KS
                    $effectiveDate = strtotime($formatSLA, strtotime($ticketDateTime));                                                                                                                     //**LP0025_KS
                    $finalDate = date("Y/m/d H:i:s",$effectiveDate);                                                                                                                                        //**LP0025_KS
                    $hours = hoursWithoutWeekend($ticketDateTime, $finalDate);                                                                                                                              //**LP0025_KS
                    $result = date("Y/m/d H:i:s", strtotime($finalDate)+$hours*3600);                                                                                                                       //**LP0025_KS
                    $weekDay = date("N", strtotime($result));                                                                                                                                               //**LP0025_KS
                    $slaTargetDate = $weekDay >= 6 ? date("Y/m/d g:i A", strtotime($result)+48*3600) : date("Y/m/d g:i A", strtotime($result));                                                             //**LP0025_KS
                }                                                                                                                                                                                           //**LP0025_KS
                else        //Weekend are also included                                                                                                                                                     //**LP0025_KS
                {                                                                                                                                                                                           //**LP0025_KS
                    $effectiveDate = strtotime($formatSLA, strtotime($ticketDateTime));                                                                                                                     //**LP0025_KS
                    $slaTargetDate = date("Y/m/d g:i A",$effectiveDate);                                                                                                                                    //**LP0025_KS
                }                                                                                                                                                                                           //**LP0025_KS
            }                                                                                                                                                                                               //**LP0025_KS
            
            //$resolved = 'Y';                                        //**LP0025_KS
            //if (checkTicketCompletion($row['ID01']) == false){      //**LP0025_KS
            //    $resolved = 'N';                                    //**LP0025_KS
            //}
            if( trim($row['STAT01']) == 2){
                $outputStat = "PFC";            //**LP0064_TS    
                $resolved = "No";               //**LP0064_TS    
            }elseif ( trim($row['STAT01']) >= 3 ){
                $outputStat = "Comp";           //**LP0064_TS    
                $resolved = "No";               //**LP0064_TS    
            }elseif( trim($row['STAT01']) <> 5 ){
     
                $outputStat = "Open";           //**LP0064_TS    
                $resolved = "No";               //**LP0064_TS    
            }else{
                $outputStat = "Resolved";         //**LP0064_TS    
                $resolved = "Yes";               //**LP0064_TS    
            }//**LP0025_KS
            
            if( $row['UDAT01'] > 0  ){
                $stepSql = "SELECT STEP02 from CIL02 WHERE CAID02 = " . $row['ID01'];
                if (($_SESSION ['authority'] == "E") or                       //**LP0034
                    ($_SESSION ['authority'] == "")   ){                      //**LP0034
                    $stepSql .= " AND PRVT02 = 'N' ";                         //**LP0034
                }                                                             //**LP0034 
                $rsStep= odbc_prepare($conn, $stepSql);
                odbc_execute($rsStep);
                while ($stepRow = odbc_fetch_array( $rsStep)){
                    $lastCommentVal = trim($stepRow['STEP02']);
                }
            }else{
                //**LP0025_KS  $lastCommentVal = "No Update";
                $lastCommentVal = "";                               //**LP0025_KS
                $updaterVal = "No Update";                          //**LP0025_KS
            }
            
            //**LP0025_KS  $exportDetails = $row['ID01'] . ",{$companyVal},{$classificationVal},{$row['PRTY01']},{$typeVal}, {$itemVal}, {$orderVal}, {$requesterVal},{$created},{$lastUpdate},"
            //**LP0025_KS  . "{$updaterVal}, {$ownerVal}, {$plannerVal}, {$stockroom}, {$buyerVal}, {$daysOpenVal}, {$lastCommentVal}\r\n";
                
            //**LP0025_KS  fwrite($fp, $exportDetails);
            
            $exportCSV = array();                               //**LP0025_KS
            $exportCSV[] = $row['PRNT01'];                        //**LP0025_KS
            $exportCSV[] = $row['ID01'];                        //**LP0025_KS
            $exportCSV[] = trim($companyVal);                   //**LP0025_KS
            $exportCSV[] = trim($classificationVal);            //**LP0025_KS
            $exportCSV[] = $row['PRTY01'];                      //**LP0025_KS
            $exportCSV[] = trim($typeVal);                      //**LP0025_KS
            $exportCSV[] = $itemVal;                            //**LP0025_KS
            if($_REQUEST['queryType'] != "frontLine"){          //**LP0025_KS
                $exportCSV[] = $supplier;                       //**LP0025_KS
            }                                                   //**LP0025_KS
            $exportCSV[] = $orderVal;                           //**LP0025_KS
            $exportCSV[] = $requesterVal;                       //**LP0025_KS
            $exportCSV[] = $created;                            //**LP0025_KS
            $exportCSV[] = $lastUpdate;                         //**LP0025_KS
            $exportCSV[] = $updaterVal;                         //**LP0025_KS
            $exportCSV[] = $ownerVal;                           //**LP0025_KS
            $exportCSV[] = $plannerVal;                         //**LP0025_KS
            $exportCSV[] = $stockroom;                          //**LP0025_KS
            $exportCSV[] = $buyerVal;                           //**LP0025_KS
            $exportCSV[] = $daysOpenVal;   
            $exportCSV[] = $outputStat;                           //**LP0064_KS
                                                                //**LP0025_KS
            if($_REQUEST['queryType'] != "frontLine"){    
                $exportCSV[] = $completed;                      //**LP0025_KS
                $exportCSV[] = $pfcDate;                        //**LP0025_KS
                $exportCSV[] = $slaTargetDate;                  //**LP0025_KS
            }                                                   //**LP0025_KS
            
            $exportCSV[] = $resolved;                           //**LP0025_KS
            $exportCSV[] = $lastCommentVal;                     //**LP0025_KS
                                                                //**LP0025_KS
            fputcsv($fp, $exportCSV);                           //**LP0025_KS
            unset($exportCSV);                                  //**LP0025_KS
            
        }
        fclose( $fp );
        
        sleep(10);
        ?>
        <div id="wrapper">
        
            <div class="container">
            
                <div class="col-md-8 col-sm-8 col-xs-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title">Multi Ticket Update</h3>
                        </div><!--panel heading-->
                        
                        <table class="data-table">
                        <table width='100%'>
                        	<tr>&nbsp;</tr>
                        	<tr>&nbsp;</tr>
                        	<tr>&nbsp;</tr>
                        	<tr>&nbsp;</tr>
                        	<tr><td class='titleBig'>Download Export Below</td></tr>
                        	<tr><td class='titleBig'><a href="<?php echo $mtpUrl;?>/filterExports/<?php echo trim($filterUser['NAME05']);?>_export.csv">Download</a></td></tr>	
                        </table>
                     </div>
                </div>
           </div>
      </div>
        <?php 
        
    }
}else{
    
    $warningMsg = "<div class='error'>";                                                                                                                                    //**LP0044_AD
    $warningMsg .= "<b>WARNING</b> - No Tickets have been selected";                                                                                               //**LP0044_AD                                                                                                                                       //**LP0044_AD
    $warningMsg .= "</div>";     
    
    echo $warningMsg;    
    
} 

odbc_close( $conn );


function stockrooms($ticket){                                           //**LP0025_KS
    global $conn;                                                       //**LP0025_KS
                                                                        //**LP0025_KS
    $sql = "select substr(c.NAME07, 1, 2) as STRC07 ";                  //**LP0025_KS
    $sql .= " from CIL07 a ";                                           //**LP0025_KS
    $sql .= "  inner join CIL10 b ";                                    //**LP0025_KS
    $sql .= "    on a.ATTR07 = b.ATTR10 ";                              //**LP0025_KS
    $sql .= "  inner join CIL07 c ";                                    //**LP0025_KS
    $sql .= "    on b.TEXT10 = c.ATTR07 ";                              //**LP0025_KS
    $sql .= " where a.NAME07 like '%Stockroom%' ";                      //**LP0025_KS
    $sql .= "   and b.CAID10 = " . $ticket . " ";                       //**LP0025_KS
                                                                        //**LP0025_KS
    $stmt = odbc_prepare($conn, $sql);                                   //**LP0025_KS
    $result = odbc_execute($stmt);                                       //**LP0025_KS
                                                                        //**LP0025_KS
    $stockrooms = "";                                                   //**LP0025_KS
    $idCounter = 0;                                                     //**LP0025_KS
    while ($row = odbc_fetch_array($stmt)){                              //**LP0025_KS
        if (++$idCounter > 1){                                          //**LP0025_KS
            $stockrooms .= " ";                                         //**LP0025_KS
        }                                                               //**LP0025_KS
        $stockrooms .= $row['STRC07'];                                  //**LP0025_KS
    }                                                                   //**LP0025_KS
    return $stockrooms;                                                 //**LP0025_KS
}                                                                       //**LP0025_KS





?>