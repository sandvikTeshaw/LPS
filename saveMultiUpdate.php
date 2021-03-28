<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            saveMultiUpdate.php<br>
 * Development Reference:   LP0025<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP00025      TS    20/08/2017 Uplift of ticket listing
 *  LP0034       KS    23/01/2018 Private Message Functionality
 *  LP0025       KS    12/03/2018 LPS Queue - 2.0
 *  LP0042       KS    01/06/2018 LPS Audit File for Ticket Ownership and Action
 *  LP0044       AD    28/08/2018 Add Buttons to Queue - Logistics Complete & Send to Pricing
 *  LP0029       TS    Change for Mass Upload
 *  LP0072       TS    Fix for backup when new assignee is out of office
 *  LP0055       KS    GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0054       AD    20/05/2019 LP0054 - LPS - Create "Assign to ____" Buttons
 *  lp0087       AD     21/10/2019    Button assign to inventory Planner
 *  LP0086_2   AD    01/11/2019 GLBAU-17773  LPS - Add Buttons to Parent Tickets on Mass Upload(fix)
 */
/**
 */


include 'copysource/config.php';
include 'copysource/functions.php';
include 'copysource/superFunctions.php';
include 'copysource/multiFunctions.php';
include '../common/copysource/global_functions.php';

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

//**LP0034  $private = "N";

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

$updateIdsArray = array(); //Moved to include to all functions
$updatedIds = "";
$authErrorIds = "";
$authRequesteIds = "";                                                                                                                                              //**LP0025_KS
$incompleteIds = "";

//**LP0044_AD if( $_REQUEST['submit'] == 'Continue' ){
if( isset($_REQUEST['submit']) &&  $_REQUEST['submit'] == 'Continue' || (isset( $_REQUEST['STAT01']) && $_REQUEST['STAT01'] == 'topricing' )){// for usage in single ticket mode //**LP0044_AD
    if( $_REQUEST['saveAction']  == "Add Comments" ){
        
       
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){
  
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");                                                     //**LP0055_KS
            if (!$editAuth){                                                                                                            //**LP0055_KS
                continue;                                                                                                               //**LP0055_KS
            }                                                                                                                           //**LP0055_KS
            
            
            $insertComment = addslashes($_REQUEST['comment']);
            
            $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id ( FACSLIB, "CIL02", "ID02", "" )  ." , $selTicket, '$insertComment', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '$private')";
           $rsStep = odbc_prepare($conn, $insertStepSql);
           odbc_execute($rsStep);
           
           $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' ) . ", ESTI01='" . date ( 'His' ) . "'"
                            . ", ESLV01=0, UPID01=" . $_SESSION ['userID'] . " WHERE ID01 = $selTicket";
           $rsUpdate = odbc_prepare($conn, $updateTicketSql);
           odbc_execute($rsUpdate);
           
        }
       
    
    ?>
        <div id="wrapper">
        
            <div class="container">
            
            <div class="col-md-8 col-sm-8 col-xs-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                    <h3 class="panel-title">Multi Ticket Update</h3>
                    </div><!--panel heading-->
                    
                    <table width='100%'>
                    	<tr>&nbsp;</tr>
                    	<tr>&nbsp;</tr>
                    	<tr>&nbsp;</tr>
                    	<tr>&nbsp;</tr>
                    	<tr><td class='titleBig'>Tickets have been updated</td></tr>
                    </table>
               </div>
           </div>
           </div>
      </div>
      <!-- //**LP0025_KS  <meta http-equiv="refresh" content="1;URL=tickets2.php?from=menu&status=1"> --!>
      
      <?php 
    echo "<meta http-equiv='refresh' content='1;URL=tickets2.php?from=saveMultiUpdate&status=1'>";      //**LP0025_KS
    
    die();
    }elseif ( isset($_REQUEST['saveAction']) && $_REQUEST['saveAction'] == "Resolve Tickets" ){
        
        $employeeArray = array();
        array_push($employeeArray, $_SESSION ['userID'] );
       
        if( isset( $_REQUEST['ticketIds'] ) ){
            foreach ( $_REQUEST['ticketIds'] as $selTicket ){
                $ticketSQL =  "select * ";                                                                                                                                      //**LP0025_KS
                $ticketSQL .= " from CIL01 ";                                                                                                                                   //**LP0025_KS
                $ticketSQL .= " where ID01 = " . $selTicket . " ";                                                                                                              //**LP0025_KS
                $ticketRes = odbc_prepare($conn, $ticketSQL);                                                                                                                    //**LP0025_KS
                odbc_execute($ticketRes);                                                                                                                                        //**LP0025_KS
                $warning = true;                                                                                                                                                //**LP0025_KS
                $ticketOwner = "0";                                                                                                                                             //**LP0042
                while ($ticketRow = odbc_fetch_array($ticketRes)){                                                                                                               //**LP0025_KS
                    $ticketOwner = $ticketRow['OWNR01'];                                                                                                                        //**LP0042
                    $parentID  = $ticketRow['PRNT01'];                                                                                                                          //**LP0029
                    
                    $warning = false;                                                                                                                                           //**LP0025_KS
                    $superAuthority[] = "";                                                                                                                                     //**LP0025_KS
                    $superAuthArray = get_super_reports_authority($_SESSION['userID'], $employeeArray, $conn, $superAuthority, $ticketRow['RQID01'], 1);                        //**LP0025_KS
                                                                                                                                                                                //**LP0025_KS
                    $pfcHasAnswers = check_ticket_answers($ticketRow['CLAS01'], $ticketRow['TYPE01'], $ticketRow['ID01'], "1" );                                                //**LP0025_KS
                                                                                                                                                                                //**LP0025_KS
                    if ($superAuthArray['requester'] == true){                                                                                                                  //**LP0025_KS
                        $warning = false;                                                                                                                                       //**LP0025_KS
                    }elseif ($ticketRow['CLAS01'] == 3 && ((trim($ticketRow['KEY201']) == "" && trim($ticketRow['KEY201']) != "N/A") || ($_SESSION['authority'] == "P" ) ||     //**LP0025_KS
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
                            $warning = true;                                                                                                                                   //**LP0025_KS
                        }                                                                                                                                                       //**LP0025_KS
                    }                                                                                                                                                           //**LP0025_KS
                    $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");                                                     //**LP0055_KS
                    if (!$editAuth){                                                                                                            //**LP0055_KS
                        $warning = true;                                                                                                        //**LP0055_KS
                    }                                                                                                                           //**LP0055_KS
                    
                 
                }                                                                                                                                                               //**LP0025_KS
                                                                                                                                                                                //**LP0025_KS
                
                
                
                //Get requester from ticket - Only Requester of Super can resolve ticket
                $ticketRequester = getRequester( $selTicket );
                
                //**LP0025_KS  if( $ticketRequester == $_SESSION['userID'] ){
                //**LP0025_KS      $validRequester = true;
                //**LP0025_KS  }else{
                //**LP0025_KS      $validRequester = false;
                //**LP0025_KS  }
                
                $superAuthority[] = "";
                $validSupervisorArr = get_super_reports_authority( $_SESSION ['userID'], $employeeArray, $conn, $superAuthority, $ticketRequester,1);
                $validSuper = $validSupervisorArr['requester'];
                
                $validComplete = checkTicketCompletion( $selTicket );                                                                                                           //**LP0025_KS
                
                //**LP0025_KS  if( $validRequester == true || $validSuper == true ){
                if( $warning == false || $validSuper == true || $_SESSION ['authority'] == "S"){                                                                                //**LP0025_KS
                    
                    //**LP0025_KS  $validComplete = checkTicketCompletion( $selTicket );
                    
                    //**LP0025_KS  if( $validComplete == true ){
                    if( $validComplete == true || $validSuper == true || $_SESSION ['authority'] == "S"){                                                                       //**LP0025_KS
                        
                        $ticketDescSql = "SELECT DESC01 FROM CIL01 WHERE ID01 = {$selTicket}";
                        $rsDesc = odbc_prepare($conn, $ticketDescSql);
                        odbc_execute($rsDesc);
                        
                        while( $descRow = odbc_fetch_array($rsDesc)){
                            $ticketDescription = trim($descRow['DESC01']);
                        }
                         
                        
                        $DESC01 = str_replace( " **** COMPLETE ****", "", $ticketDescription);
                        $CDAT01 = date ( 'Ymd' );
                        $CTIM01 = date ( 'His' );
                        $DESC01 = strtr($DESC01, $GLOBALS['normalizeSaveChars']);		//i-2294568
                        
                        $updateCIL01Sql = "UPDATE CIL01 SET DESC01 = '$DESC01', STAT01=5, CDAT01={$CDAT01}, CTIM01='{$CTIM01}', UDAT01={$CDAT01}, UTIM01='{$CTIM01}', UPID01=" . $_SESSION ['userID']
                                        . " WHERE ID01 = {$selTicket}";
                        
                        $rsTicketRes = odbc_prepare($conn, $updateCIL01Sql);
                        odbc_execute($rsTicketRes);
                        
                        $insertCIL01OA = "insert into CIL01OA ";                                                                                                //**LP0042
                        $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";                                         //**LP0042
                        $insertCIL01OA .= $ticketOwner . ", " . $ticketOwner . ", 7, " . $_SESSION['userID'] . ")";                                             //**LP0042
                        $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                                       //**LP0042
                        odbc_execute($cil01oaRes);                                                                                                               //**LP0042
                        
                        $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id ( FACSLIB, "CIL02", "ID02", "" )  ." , $selTicket, '"
                        . $_REQUEST['comment'] . "', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '$private')";
                        $rsStep = odbc_prepare($conn, $insertStepSql);
                        odbc_execute($rsStep);
                        
                        $updatedIds .= $selTicket . ",";
                        array_push($updateIdsArray, $selTicket);
       
                    }else{
                        
                    
                        $incompleteIds .= $selTicket . ",";
                        
                    }
                    
                    
                }else{
                    if( $validComplete == true){                                                                                                                                //**LP0025_KS
                        //**LP0025_KS  $authRequesteIds.= $selTicket . ",";                                                                                                                    //**LP0025_KS
                        $authErrorIds .= $selTicket . ",";                                                                                                                      //**LP0025_KS
                    }else{                                                                                                                                                      //**LP0025_KS
                        //**LP0025_KS  $authErrorIds.= $selTicket . ",";
                        $incompleteIds .= $selTicket . ",";                                                                                                                     //**LP0025_KS
                    }                                                                                                                                                           //**LP0025_KS
                }
    
            }
        }
        
        if( !isset($parentID) ){
            $parentID = $_REQUEST['parentTicketID'];
            $CDAT01 = date ( 'Ymd' );
            $CTIM01 = date ( 'His' );
            $updatedIds .= $parentID . ",";
            array_push($updateIdsArray, $parentID);
        }
        
        //****START******* - LP0029 - Check to see if parent and if all children are closed.  If all closed then close parent.
        if( $parentID ){
         
            $childOpenCountSQL = "SELECT ID01 FROM CIL01 WHERE PRNT01 = $parentID and STAT01 < 5";
            
            $chldCountRes = odbc_prepare($conn, $childOpenCountSQL);                                                                                      
            odbc_execute($chldCountRes); 
            
            $chOpenCounter = 0;
            while( $chlCountRow = odbc_fetch_array($chldCountRes)){
                
                $chOpenCounter++;
            }
            
 
            
            if( $chOpenCounter == 0 ){
                $prntCloseSql = "UPDATE CIL01 SET STAT01=5, CDAT01={$CDAT01}, CTIM01='{$CTIM01}', UDAT01={$CDAT01}, UTIM01='{$CTIM01}', UPID01=" . $_SESSION ['userID']
                . " WHERE ID01 = {$parentID}";
                $prntCloseRes = odbc_prepare($conn, $prntCloseSql);                                                                                       //**LP0042
                odbc_execute($prntCloseRes); 
                
                $insertParentResolve = "INSERT into CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );
                $insertParentResolve .= "," . $parentID . ", 'Resolve Parent - " . $_REQUEST['comment'] . "'";
                $insertParentResolve .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", 'N')";
                
                $prntRes = odbc_prepare($conn, $insertParentResolve);
                odbc_execute($prntRes); 
               
            }
            
            //****END******* - LP0029 
            
            
        }
        ?>
        
        <div id="wrapper">
        
            <div class="container">     
                <div class="col-md-8 col-sm-8 col-xs-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title">Multi Ticket Update</h3>
                        </div><!--panel heading-->
                    
                    <table width='100%'>
                        <tr><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td></tr>
                        <?php 
                        if( $updatedIds <> "" ){
                        ?>
                            <tr><td class='titleBig'><big>The following ticket(s) have been resolved.</big></td></tr>
                            <tr><td class='title'><?php echo substr($updatedIds, 0 , -1);?></td></tr>
                        <?php 
                        }
                        if( $incompleteIds <> "" ){
                            ?>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <!--  //**LP0025_KS  <tr><td class='titleBig'><big>The following ticket(s) could not be resolved because they are not complete.</big></td></tr>               -->
                            <!--  //**LP0025_KS  <tr><td class='title'><?php //**LP0025_KS  echo substr($incompleteIds, 0 , -1);?></td></tr>		                                                      -->
                        <?php 
                            echo "<tr><td class='titleBig'><big>Ticket(s) </big> " . substr($incompleteIds, 0 , -1) . " <big> can't be resolved or completed due to incomplete or missing data </big></td></tr>";    //**LP0025_KS
                        }
                        if( $authErrorIds <> "" ){
                            ?>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td class='titleBig'><big>You are not authorized to resolve the following ticket(s).</big></td></tr>
                            <tr><td class='title'><?php echo substr($authErrorIds, 0 , -1);?></td></tr>
                        <?php 
                        }
                        if( $authRequesteIds <> "" ){                                                                                                                                       //**LP0025_KS
                            echo "<tr><td>&nbsp;</td></tr>";                                                                                                                                //**LP0025_KS
                            echo "<tr><td>&nbsp;</td></tr>";                                                                                                                                //**LP0025_KS
                            echo "<tr><td class='titleBig'><big>Ticket(s) </big> " . substr($authRequesteIds, 0 , -1) . " <big> can only be resolved by the requester </big></td></tr>";    //**LP0025_KS
                        }                                                                                                                                                                   //**LP0025_KS
                        ?>
                        </table>
                        <?php 
                        if( $updatedIds <> "" ){
                            ?>
                            <form method='post' action='multiSurveyResponse.php'>
                            <?php 
                            foreach ( $updateIdsArray as $selId ) {
                        	    
                        	    ?><input type='hidden' name='ticketIds[]' id='ticketIds[]' value='<?php echo $selId;?>'/><?php
                        	}
                        	?>
                        	<table class="data-table">
                      			<tr>
                            		<td><input id='submit' type='submit' name = 'submit' value = 'Continue'></td>
                            	</tr>
                        	</form>
                            <?php 

                        }
                        ?>
                        
                    
                    </div>
                </div>
            </div>
        </div>
       <?php 
       //*********************************************************************LPS0044_AD BEGIN**********************************************************************************************
    }elseif (isset( $_REQUEST['saveAction'] ) && $_REQUEST['saveAction']  == "Logistics Complete"){    //**LP0044_AD                                                                                                       //**LP0044_AD
        //**LP0044_AD
        $employeeArray = array();                                                                                                                                           //**LP0044_AD
        array_push($employeeArray, $_SESSION ['userID'] );                                                                                                                  //**LP0044_AD
        $authErrorIds = "";                                                                                                                                                 //**LP0044_AD
        $authRequesteIds = "";                                                                                                                                              //**LP0044_AD
        $incompleteIds = "";                                                                                                                                                //**LP0044_AD
        $updatedIds = "";                                                                                                                                                   //**LP0044_AD
        $updateIdsArray = array();                                                                                                                                          //**LP0044_AD
        
        //**LP0044_AD
        $i =0;
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){            //**LP0044_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");                                                     //**LP0055_KS
            if (!$editAuth){                                                                                                            //**LP0055_KS
                continue;                                                                                                               //**LP0055_KS
            }                                                                                                                           //**LP0055_KS
            
            
            $ticketAction = $_REQUEST['ticketIdsAction'][$i];//**LP0044_AD
            //echo $ticketAction;
            $i++;//**LP0044_AD
            $STAT01=$ticketAction;//**LP0044_AD
            $CTYP01 = 0;
            $warning = true;
            if ($STAT01 == "drpComplete") {//**LP0044_AD
                $STAT01 = 3;       //**LP0044_AD - Changed from 1 to 3
                $CTYP01 = 3;//**LP0044_AD
                $warning =false;//**LP0044_AD
                $drpComplete = true;//**LP0044_AD
                if (! $actionResponse) {//**LP0044_AD
                    //**LP0044_AD  $actionResponse = "DRP Action Complete";
                    $actionResponse = "Logistics Action Complete (DRP)";    //**LP0044_AD
                    
                    
                }//**LP0044_AD
            }//**LP0044_AD
            if ($STAT01 == "obpComplete") {//**LP0044_AD
                $CTYP01 = 3;//**LP0044_AD
                $STAT01 = 4;        //**LP0044_AD
                $warning =false;//**LP0044_AD
                $obpComplete = true;//**LP0044_AD
                
                if (!isset($actionResponse) && $actionResponse != "" ) {//**LP0044_AD
                    //**LP0044_AD  $actionResponse = "DRP Action Complete";
                    $actionResponse = "Logistics Action Complete";        //**LP0044_AD
                }//**LP0044_AD
            }//**LP0044_AD
            
            if ($STAT01 == "priComplete") {  //**LP0044_AD
                $STAT01 = 4; //**LP0044_AD
                $CTYP01 = 6; //**LP0044_AD
                $priComplete = true;  //**LP0044_AD
                $warning =false;//**LP0044_AD
                if (! $actionResponse) {                                   //**LP0044_AD
                    $actionResponse = "Pricing Action Complete";            //**LP0044_AD
                }                                                           //**LP0044_AD
            }                                                               //**LP0044_AD
            
            
            
            
            
            $ticketSQL =  "select * ";                                                                                                                                      //**LP0044_AD
            $ticketSQL .= " from CIL01 ";                                                                                                                                   //**LP0044_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";                                                                                                              //**LP0044_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);                                                                                                                    //**LP0044_AD
            odbc_execute($ticketRes);                                                                                                                                        //**LP0044_AD
            //**LP0044_AD
            $ticketOwner = "0";                                                                                                                                    //**LP0044_AD
            
            while ($ticketRow = odbc_fetch_array($ticketRes)){                                                                                                               //**LP0044_AD
                //**************************************************************
                $superAuthority[] = "";                                                                                                                                         //**LP0044_AD
                $superAuthArray = get_super_reports_authority($_SESSION['userID'], $employeeArray, $conn, $superAuthority, $ticketRow['RQID01'], 1);                            //**LP0044_AD
                $pfcHasAnswers = check_ticket_answers($ticketRow['CLAS01'], $ticketRow['TYPE01'], $ticketRow['ID01'], "1" );//**LP0044_AD
                
                $attributeReturnArray [] = "";//**LP0044_AD
                $attributeValues [] = "";//**LP0044_AD
                $attributeValues = get_attribute_values ( $ticketRow ['ID01'] );//**LP0044_AD
                //Display the attributes and attribute information
                
                if( !isset($orderArray) ){
                    $orderArray = "";
                }
                echo '<span style="display: none;"> ';//**LP0044_AD
                $attributeReturnArray = display_attributes ( $ticketRow ['CLAS01'], $ticketRow ['TYPE01'], $attributeValues, $orderArray );  //**LP0044_AD
                echo '</span>';//**LP0044_AD
                //var_dump($attributeReturnArray);
                $plannerHasAnswers = check_ticket_answers(  $ticketRow ['CLAS01'] , $ticketRow ['TYPE01'], $ticketRow ['ID01'], "2" );//**LP0044_AD
                
                $warning = true;  //**LP0044_AD
                $ticketAction = "NoAction";//**LP0044_AD
                
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
                
                
           //lp0086     if(($superAuthArray['drp']) || (($_SESSION['authority'] == "L" || $_SESSION['authority'] == "S") && $ticketRow['CLAS01'] == 5 && $ticketRow ['TYPE01'] != 75)){ //**LP0044_AD
                if(($superAuthArray['drp']) || (($_SESSION['authority'] == "L" || $_SESSION['authority'] == "S") && ($ticketRow['CLAS01'] == 5 || $ticketRow['CLAS01'] == 7)&& $ticketRow ['TYPE01'] != 75)){ //**LP0086_AD
                        $drpSubmitLabel = "Logistics Complete";  //**LP0044_AD
                    if($plannerHasAnswers){    //**LP0044_AD
                        $ticketAction= "priComplete";$warning=false;   //**LP0044_AD                                                                                                                                                                               //**LP0036
                    }                                                          //**LP0044_AD
                }                                                            //**LP0044_AD
                
                
                //**************************************************************   //**LP0044_AD
            };                                                                                                                                                              //**LP0044_AD
            $ticketRequester = getRequester( $selTicket );                                                                                                                  //**LP0044_AD
            $superAuthority[] = "";                                                                                                                                         //**LP0044_AD
            $validSupervisorArr = get_super_reports_authority( $_SESSION ['userID'], $employeeArray, $conn, $superAuthority, $ticketRequester,1);                           //**LP0044_AD
            $validSuper = $validSupervisorArr['requester'];                                                                                                                 //**LP0044_AD
            if( $warning == false ){                                                                                //**LP0044_AD
                $ticketDescSql = "SELECT * FROM CIL01 WHERE ID01 = {$selTicket}";                                                                                      //**LP0044_AD
                $rsDesc = odbc_prepare($conn, $ticketDescSql);//**LP0044_AD
                odbc_execute( $rsDesc );//**LP0044_AD               
                while( $descRow = odbc_fetch_array($rsDesc)){                                                                                                                //**LP0044_AD
                    $ticketDescription = trim($descRow['DESC01']);                                                                                                          //**LP0044_AD
                    $RQID01 = $descRow['RQID01'];//**LP0044_AD
                    $OWNR01 = $descRow['OWNR01'];//**LP0044_AD
                    
                }                                                                                                                                                          //**LP0044_AD
                $ticketDescription = str_replace( "**** COMPLETE ****", "", $ticketDescription );//**LP0044_AD
                $DESC01 = $ticketDescription." **** COMPLETE ****";                                                                                                         //**LP0044_AD
                $CDAT01 = date ( 'Ymd' );                                                                                                                                   //**LP0044_AD
                $CTIM01 = date ( 'His' );                                                                                                                                   //**LP0044_AD
                $DESC01 = strtr($DESC01, $GLOBALS['normalizeSaveChars']); //**LP0044_AD
                
                $updateCIL01Sql = "UPDATE CIL01 SET DESC01 = '$DESC01', STAT01={$STAT01}, OWNR01={$RQID01}, POFF01={$RQID01}, CDAT01={$CDAT01}, CTIM01='{$CTIM01}', CPDT01={$CDAT01}, CPTI01='{$CTIM01}', UDAT01={$CDAT01}, UTIM01='{$CTIM01}', UPID01=" . $_SESSION ['userID']//**LP0044_AD
                . " WHERE ID01 = {$selTicket}";                                                                                                                             //**LP0044_AD
                //**LP0044_AD
                $rsTicketRes = odbc_prepare($conn, $updateCIL01Sql);                                                                                                         //**LP0044_AD
                odbc_execute($rsTicketRes);                                                                                                                                  //**LP0044_AD
                $insertCIL01OA = "insert into CIL01OA ";                                                                                                                    //**LP0044_AD
                $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";                                                             //**LP0044_AD
                $insertCIL01OA .= $OWNR01 . ", " . $RQID01 . ", $CTYP01, " . $_SESSION['userID'] . ")";                                                                 //**LP0044_AD
                $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                                                           //**LP0044_AD
                odbc_execute($cil01oaRes);//**LP0044_AD
                if($_REQUEST['comment']=='')$reqComment="Logistics Action Complete";
                
                $reqComment = "COMPLETE - ".$_REQUEST['comment'];//**LP0044_AD
                
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id ( FACSLIB, "CIL02", "ID02", "" )  ." , $selTicket, '"                                     //**LP0044_AD
                . $reqComment . "', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '$private')";       //?????private?          //**LP0044_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);                                                                                                               //**LP0044_AD
                odbc_execute($rsStep);                                                                                                                                       //**LP0044_AD
                $updatedIds .= $selTicket . ",";                                                                                                                            //**LP0044_AD
                array_push($updateIdsArray, $selTicket);                                                                                                                    //**LP0044_AD
            }else{                                                                                                                                                          //**LP0044_AD
                if( $validComplete == true){                                                                                                                                //**LP0044_AD
                    $authErrorIds .= $selTicket . ",";                                                                                                                      //**LP0044_AD
                }else{                                                                                                                                                      //**LP0044_AD
                    $incompleteIds .= $selTicket . ",";                                                                                                                     //**LP0044_AD
                }                                                                                                                                                           //**LP0044_AD
            }                                                                                                                                                               //**LP0044_AD
            
        }                                                                                                                                                                   //**LP0044_AD
        ?><!-- //**LP0044_AD  -->
        
        <div id="wrapper"><!-- //**LP0044_AD  -->
        
            <div class="container">     <!-- //**LP0044_AD  -->
                <div class="col-md-8 col-sm-8 col-xs-8"><!-- //**LP0044_AD  -->
                    <div class="panel panel-default"><!-- //**LP0044_AD  -->
                        <div class="panel-heading"><!-- //**LP0044_AD  -->
                        <h3 class="panel-title">Multi Ticket Update</h3><!-- //**LP0044_AD  -->
                        </div><!--panel heading--><!-- //**LP0044_AD  -->
                    
                    <table width='100%'>
                        <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                        <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                        <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                        <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                        <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                        <?php 
                        if( $updatedIds <> "" ){
                        ?><!-- //**LP0044_AD  -->
                            <tr><td class='titleBig'><big>The following ticket(s) have been completed.</big></td></tr><!-- //**LP0044_AD  -->
                            <tr><td class='title'><?php echo substr($updatedIds, 0 , -1);?></td></tr><!-- //**LP0044_AD  -->
                        <?php 
                        }
                        if( $incompleteIds <> "" ){
                            ?><!-- //**LP0044_AD  -->
                            <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                            <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                        <?php 
                        echo "<tr><td class='titleBig'><big>Ticket(s) </big> " . substr($incompleteIds, 0 , -1) . " <big> can't be completed due to incomplete or missing data </big></td></tr>";    //**LP0044_AD
                        }
                        if( $authErrorIds <> "" ){
                            ?><!-- //**LP0044_AD  -->
                            <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                            <tr><td>&nbsp;</td></tr><!-- //**LP0044_AD  -->
                            <tr><td class='titleBig'><big>You are not authorized to complete the following ticket(s).</big></td></tr><!-- //**LP0044_AD  -->
                            <tr><td class='title'><?php echo substr($authErrorIds, 0 , -1);?></td></tr><!-- //**LP0044_AD  -->
                        <?php 
                        }
                        if( $authRequesteIds <> "" ){                                                                                                                                      //**LP0044_AD
                            echo "<tr><td>&nbsp;</td></tr>";                                                                                                                               //**LP0044_AD
                            echo "<tr><td>&nbsp;</td></tr>";                                                                                                                               //**LP0044_AD
                            echo "<tr><td class='titleBig'><big>Ticket(s) </big> " . substr($authRequesteIds, 0 , -1) . " <big> can only be completed by the requester </big></td></tr>";  //**LP0044_AD
                        }                                                                                                                                                                   //**LP0044_AD
                        ?><!-- //**LP0044_AD  -->
                        </table><!-- //**LP0044_AD  -->                   
                    </div><!-- //**LP0044_AD  -->
                </div><!-- //**LP0044_AD  -->
            </div><!-- //**LP0044_AD  -->
        </div><!-- //**LP0044_AD  -->
            <meta http-equiv="refresh" content="3;URL=tickets2.php?from=saveMultiUpdate&status=1"> <!-- //**LP0044_AD  -->
        
       <?php 
    }elseif (isset( $_REQUEST['saveAction'] ) && ( $_REQUEST['saveAction']  == "Assign to Pricing")){    //**LPS0044_AD  
        $reassignedMain  = "";//**LP0044_AD
        $reassignedBackup  = "";//**LP0044_AD
        $reassignedFailed  = "";//**LP0044_AD
        foreach ( $_REQUEST['ticketIds'] as $selId ){        //**LPS0044_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selId, "EDIT");                                                         //**LP0055_KS
            if (!$editAuth){                                                                                                            //**LP0055_KS
                continue;                                                                                                               //**LP0055_KS
            }                                                                                                                           //**LP0055_KS
            
            $ticketSQL =  "select ID01,TYPE01,TEXT10,PCLS35,BRAN16,PRCC16,BPRC16,RSID01, CLAS01";         //**LP0044_AD               
            $ticketSQL .= " from CIL01 JOIN CIL10 ON CAID10=ID01 JOIN CIL07 ON (ATTR07=ATTR10 AND HTYP07='PART')";    //serach for part numbers    //**LP0044_AD                                                                                                                               //**LP0044_AD
            $ticketSQL .= "  JOIN PARTS ON (CONO35='DI' AND PNUM35=TRIM(TEXT10)) ";         //find coresponding brand for part numbers //**LP0044_AD
            $ticketSQL .= "  LEFT JOIN CIL16 ON BRAN16=PCLS35 ";  //find coresponding contact person for brand //**LP0044_AD
            $ticketSQL .= " where ID01 = " . $selId . " "; //**LP0044_AD
            //echo $ticketSQL; //**LP0044_AD
            //echo '<br>';
            $ticketRes = odbc_prepare($conn, $ticketSQL);    //**LP0044_AD 
            odbc_execute($ticketRes);//**LP0044_AD
            $warning=true; //**LP0044_AD
            $mainContact=0;//**LP0044_AD
            $bkContact=0;//**LP0044_AD
            $assignee=0;//**LP0044_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){  //**LP0044_AD 
               
                //**LP0055_KS  if($ticketRow['TYPE01']==43 &&  $_SESSION ['userID'] != $ticketRow ['RSID01']){ //**LP0044_AD
              
            //lp0086    if ( $ticketRow['CLAS01'] ==5 || $ticketRow['TYPE01'] == 103 || $ticketRow['TYPE01'] == 43  ){       //**LP0055_KS
                if ( $ticketRow['CLAS01'] ==5 || $ticketRow['TYPE01'] == 103 || $ticketRow['TYPE01'] == 43 || $ticketRow['TYPE01'] == 130 || $ticketRow['TYPE01'] == 133  ){       //**LP0086
                        //echo '<br>';
                    //var_dump($ticketRow);
                    //echo '<br>';
                    if($ticketRow['PRCC16']>0 ){//**LP0044_AD
                        $selectUserSQL="select ID05,NAME05,AVAL05,BACK05 from HLP05 where ID05=".$ticketRow['PRCC16'];//**LP0044_AD
                        $userRes = odbc_prepare($conn, $selectUserSQL);    //**LP0044_AD                                                                                                                    //**LP0044_AD
                        odbc_execute($userRes);//**LP0044_AD
                        
                        if($userRow = odbc_fetch_array($userRes))//**LP0044_AD
                            if($userRow['AVAL05']=='Y') $mainContact=$ticketRow['PRCC16']; //main contact//**LP0044_AD
                            elseif ($userRow['BACK05']>0) $bkContact=$userRow['BACK05'];  //backup person from user table   //**LP0044_AD
                    }//**LP0044_AD
                    if ( $ticketRow['BPRC16']>0 &&  $bkContact==0 && $assignee==0){
                        $selectUserSQL="select ID05,NAME05,AVAL05,BACK05 from HLP05 where ID05=".$ticketRow['BPRC16'];//**LP0044_AD
                        $userRes = odbc_prepare($conn, $selectUserSQL);    //**LP0044_AD                                                                                                                    //**LP0044_AD
                        odbc_execute($userRes);//**LP00  44_AD
                        if($userRow = odbc_fetch_array($userRes))//**LP0044_AD
                            if($userRow['AVAL05']=='Y') $bkContact=$ticketRow['BPRC16']; // Backup from price contacts//**LP0044_AD
                            elseif ($userRow['BACK05']>0) $bkContact=$userRow['BACK05']; // Backup of backup   //**LP0044_AD
                    }    //**LP0044_AD
                    //**LP0044_AD
                    if ($mainContact>0 ){//**LP0044_AD
                        $assignee=$mainContact;//**LP0044_AD
                        $reassignedMain.=' '.$selId;//**LP0044_AD
                        break;//**LP0044_AD
                    }//**LP0044_AD
                    elseif ($bkContact>0 ){//**LP0044_AD
                        $assignee=$bkContact;//**LP0044_AD
                        $reassignedBackup.=' '.$selId;//**LP0044_AD
                        break;//**LP0044_AD
                    }//**LP0044_AD
                    else{ //**LP0044_AD
                        echo "<BR>No Pricing Contact available for this part number ". $ticketRow['TEXT10']."<BR>";//**LP0044_AD
                    }  //**LP0044_AD
                }//**LP0044_AD
            }//end row          //**LP0044_AD  
            //echo "<br>".$assignee." - ".$bkContact;
            
            if(($assignee==0) && (($_SESSION['authority'] == "L" )|| ($_SESSION['authority'] == "S" )))             //nothing to assign//**LP0044_AD
               $reassignedFailed.=' '.$selId;//**LP0044_AD
                                                              //**LP0044_AD
               else{ //**LP0044_AD
           
               $ticket2SQL =  "select * ";                                                                                                       //**LP0044_AD
               $ticket2SQL .= " from CIL01 ";                                                                                                    //**LP0044_AD
               $ticket2SQL .= " where ID01 = " . $selId . " ";                                                                               //**LP0044_AD
               $ticket2Res = odbc_prepare($conn, $ticket2SQL);                                                                                     //**LP0044_AD
               odbc_execute($ticket2Res);                                                                                                         //**LP0044_AD
               while ($ticket2Row = odbc_fetch_array($ticket2Res)){                                                                                //**LP0044_AD
                   $insertCIL01OA = "insert into CIL01OA ";                                                                                     //**LP0044_AD
                   $insertCIL01OA .= " VALUES ( " . $selId . ", " . date('Ymd') . ", '" . date('His') . "', ";                              //**LP0044_AD
                   $insertCIL01OA .= $ticket2Row['OWNR01'] . ", " .$assignee . ", 4, " . $_SESSION['userID'] . ")";              //**LP0044_AD
                   $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                            //**LP0044_AD
                   odbc_execute($cil01oaRes);  
                   //echo "INzert=".$insertCIL01OA;
               }                                                                                                                                //**LP0044_AD
               
               if( $_REQUEST['comment']=='')$_REQUEST['comment']="Sent to Pricing";
               $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );                                //**LP0044_AD
               $insertStepSql .= "," . $selId . ", '" . $_REQUEST['comment'] . "'";                                                         //**LP0044_AD
               $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";     //**LP0044_AD
               $rsStep = odbc_prepare($conn, $insertStepSql);                                                                                    //**LP0044_AD
               odbc_execute($rsStep);                                                                                                            //**LP0044_AD
               $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );  //**LP0044_AD
               $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];                          //**LP0044_AD
               $updateTicketSql .= ", OWNR01=" . $assignee . ",STAT01=1 WHERE ID01 = $selId";    //**LP0044_AD
               //echo $updateTicketSql;
               $rsUpdate = odbc_prepare($conn, $updateTicketSql);                                                                                //**LP0044_AD
               odbc_execute($rsUpdate);                                                                                                          //**LP0044_AD
               $updatedIds .= $selTicket . ",";                                                                                                 //**LP0044_AD
               array_push($updateIdsArray, $selTicket); //**LP0044_AD
               
           }  
         } //end <ticket>       </ticket>//**LP0044_AD
        
        
        
        
        ?>
        
        <div id="wrapper"><!-- LP0044_AD -->
        
            <div class="container">     <!-- LP0044_AD -->
                <div class="col-md-8 col-sm-8 col-xs-8"><!-- LP0044_AD -->
                    <div class="panel panel-default"><!-- LP0044_AD -->
                        <div class="panel-heading"><!-- LP0044_AD -->
                        <h3 class="panel-title">Multi Ticket Update</h3><!-- LP0044_AD -->
                        </div><!--panel heading--><!-- LP0044_AD -->
                    
                    <table width='100%'><!-- LP0044_AD -->
                        <tr><td>&nbsp;</td></tr><!-- LP0044_AD -->
                        <tr><td>&nbsp;</td></tr><!-- LP0044_AD -->
                        <tr><td>&nbsp;</td></tr><!-- LP0044_AD -->
                        <tr><td>&nbsp;</td></tr><!-- LP0044_AD -->
                        <tr><td>&nbsp;</td></tr><!-- LP0044_AD -->
                        <?php 
                        if(  $reassignedMain<> "" ){
                        ?>
                            <tr><td class='titleBig'><big>The following ticket(s) have been reassigned to Price responsible(s).</big></td></tr>
                            <tr><td class='title'><?php echo $reassignedMain;?></td></tr>
                        <?php 
                        }
                        if( $reassignedBackup <> "" ){
                            ?>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td>&nbsp;</td></tr>
                        <?php 
                        echo "<tr><td class='titleBig'><big>Ticket(s) </big> " .$reassignedBackup . " <big> has been reassigned to Price responsible(s) backup person(s) </big></td></tr>";    //**LP0044_AD
                        }
                        if( $reassignedFailed <> "" ){
                            ?>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td class='titleBig'><big>Reassignement not possible for folowing ticket(s).</big></td></tr>
                            <tr><td class='title'><?php echo $reassignedFailed;?></td></tr>
							<?php } ?>
                        </table><!-- LP0044_AD -->
                    
                    </div><!-- LP0044_AD -->
                </div><!-- LP0044_AD -->
            </div><!-- LP0044_AD -->
        </div><!-- LP0044_AD -->
          <meta http-equiv="refresh" content="4;URL=tickets2.php?from=saveMultiUpdate&status=1"> <!-- LP0044_AD -->
        
       <?php
        
            
       //*******************************************************************//**LPS0044_AD END**********************************************************************************************
       //*******************************************************************//**LPS0054_AD START********************************************************************************************
    }elseif ( isset( $_REQUEST['saveAction']  ) && $_REQUEST['saveAction']  == "Assign to Requestor" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01 ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                $assignID=$ticketRow['RQID01'];//**LPS0054_AD
                $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                odbc_execute($cil01oaRes);//**LPS0054_AD
            }//**LPS0054_AD
            
            $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
            $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
            $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
            $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
            odbc_execute($rsStep);//**LPS0054_AD
            $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
            $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
            $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
            $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
            odbc_execute($rsUpdate);//**LPS0054_AD
            $updatedIds .= $selTicket . ",";//**LPS0054_AD
            array_push($updateIdsArray, $selTicket);//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
    }elseif ( isset( $_REQUEST['saveAction'] ) && $_REQUEST['saveAction']  == "Assign to Buyer" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            /*lp0086
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01 JOIN CIL25 ON BUYR01=PLAN25 ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                $assignID=$ticketRow['USER25'];//**LPS0054_AD
            *///lp0086
  
            $assignID=findBuyer($selTicket);//lp0086
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0054_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
                    //**LPS0054_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0054_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0054_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0054_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0054_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0054_AD
                    if( $back['availability'] == "Y" ){//**LPS0054_AD
                        $assignID = $backId;//**LPS0054_AD
                    }//**LPS0054_AD
                }//**LPS0054_AD
                if($assignID>0){//**LPS0054_AD
                
                $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                odbc_execute($cil01oaRes);//**LPS0054_AD
                }
           //lp0086 }//**LPS0054_AD
            if($assignID>0){//**LPS0054_AD
                
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
                odbc_execute($rsStep);//**LPS0054_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
                odbc_execute($rsUpdate);//**LPS0054_AD
                $updatedIds .= $selTicket . ",";//**LPS0054_AD
                array_push($updateIdsArray, $selTicket);//**LPS0054_AD
              }//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
    }elseif ( isset($_REQUEST['saveAction']) && $_REQUEST['saveAction']  == "Assign to OBP" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01  ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                $assignID=$ticketRow['POFF01'];//**LPS0054_AD
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0054_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
                    //**LPS0054_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0054_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0054_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0054_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0054_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0054_AD
                    if( $back['availability'] == "Y" ){//**LPS0054_AD
                        $assignID = $backId;//**LPS0054_AD
                    }//**LPS0054_AD
                }//**LPS0054_AD
                
                if($assignID>0){//**LPS0054_AD
                    $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                    $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                    $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                    odbc_execute($cil01oaRes);//**LPS0054_AD
                    }//**LPS0054_AD
            }//**LPS0054_AD
            if($assignID>0){//**LPS0054_AD
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
                odbc_execute($rsStep);//**LPS0054_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
                odbc_execute($rsUpdate);//**LPS0054_AD
                $updatedIds .= $selTicket . ",";//**LPS0054_AD
                array_push($updateIdsArray, $selTicket);//**LPS0054_AD
                }//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
    }elseif ( isset( $_REQUEST['saveAction'] ) && $_REQUEST['saveAction']  == "Assign to PFC" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01  ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                $assignID=findPFC($selTicket);//**LPS0054_AD
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0054_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
                    //**LPS0054_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0054_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0054_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0054_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0054_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0054_AD
                    if( $back['availability'] == "Y" ){//**LPS0054_AD
                        $assignID = $backId;//**LPS0054_AD
                    }//**LPS0054_AD
                }//**LPS0054_AD
                
                if($assignID>0){//**LPS0054_AD
                    $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                    $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                    $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                    odbc_execute($cil01oaRes);//**LPS0054_AD
                }//**LPS0054_AD
            }//**LPS0054_AD
            if($assignID>0){//**LPS0054_AD
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
                odbc_execute($rsStep);//**LPS0054_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
                odbc_execute($rsUpdate);//**LPS0054_AD
                $updatedIds .= $selTicket . ",";//**LPS0054_AD
                array_push($updateIdsArray, $selTicket);//**LPS0054_AD
            }//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
    }elseif ( isset( $_REQUEST['saveAction'] ) && $_REQUEST['saveAction']  == "Assign to Freight" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01  ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
    
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                
                //$assignID=findFreightContact($selTicket);//**LPS0054_AD
         
                if( $ticketRow['CLAS01'] != 7 ){
            
                    $assignID = findFreightContact($selTicket);//LP0054_AD
                }elseif( $ticketRow['TYPE01'] == 31 || $ticketRow['TYPE01'] == 32|| $ticketRow['TYPE01'] == 33 || $ticketRow['TYPE01'] == 34){
    
                    $assignID = findFreightContactAttrib($selTicket, $ticketRow['TYPE01']);//LP0054_AD
                }
                
         
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0054_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
                    //**LPS0054_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0054_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0054_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0054_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0054_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0054_AD
                    if( $back['availability'] == "Y" ){//**LPS0054_AD
                        $assignID = $backId;//**LPS0054_AD
                    }//**LPS0054_AD
                }//**LPS0054_AD
                
                if($assignID>0){//**LPS0054_AD
                    $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                    $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                    $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                    odbc_execute($cil01oaRes);//**LPS0054_AD
                }//**LPS0054_AD
            }//**LPS0054_AD
            if($assignID>0){//**LPS0054_AD
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
                odbc_execute($rsStep);//**LPS0054_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
                odbc_execute($rsUpdate);//**LPS0054_AD
                $updatedIds .= $selTicket . ",";//**LPS0054_AD
                array_push($updateIdsArray, $selTicket);//**LPS0054_AD
            }//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
    }elseif ( isset( $_REQUEST['saveAction'] ) && $_REQUEST['saveAction']  == "Assign to TSD" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01  ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                $assignID=findTSD($selTicket);//**LPS0054_AD
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0054_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
                    //**LPS0054_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0054_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0054_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0054_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0054_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0054_AD
                    if( $back['availability'] == "Y" ){//**LPS0054_AD
                        $assignID = $backId;//**LPS0054_AD
                    }//**LPS0054_AD
                }//**LPS0054_AD
                
                if($assignID>0){//**LPS0054_AD
                    $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                    $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                    $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                    odbc_execute($cil01oaRes);//**LPS0054_AD
                }//**LPS0054_AD
            }//**LPS0054_AD
            if($assignID>0){//**LPS0054_AD
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
                odbc_execute($rsStep);//**LPS0054_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
                odbc_execute($rsUpdate);//**LPS0054_AD
                $updatedIds .= $selTicket . ",";//**LPS0054_AD
                array_push($updateIdsArray, $selTicket);//**LPS0054_AD
            }//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
        
    }elseif ( isset( $_REQUEST['saveAction'] ) && $_REQUEST['saveAction']  == "Assign to Warehouse" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01  ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                
                
                if( $ticketRow['CLAS01'] != 7 ){
                    
                    $assignID=findWarehouseContact($selTicket);//**LPS0054_AD
                }else{
                    
                    $assignID = findWarehouseContactAttrib($selTicket, $ticketRow['TYPE01']);
                }
                
                
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0054_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
                    //**LPS0054_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0054_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0054_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0054_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0054_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0054_AD
                    if( $back['availability'] == "Y" ){//**LPS0054_AD
                        $assignID = $backId;//**LPS0054_AD
                    }//**LPS0054_AD
                }//**LPS0054_AD
                
                if($assignID>0){//**LPS0054_AD
                    $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                    $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                    $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                    odbc_execute($cil01oaRes);//**LPS0054_AD
                }//**LPS0054_AD
            }//**LPS0054_AD
            if($assignID>0){//**LPS0054_AD
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
                odbc_execute($rsStep);//**LPS0054_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
                odbc_execute($rsUpdate);//**LPS0054_AD
                $updatedIds .= $selTicket . ",";//**LPS0054_AD
                array_push($updateIdsArray, $selTicket);//**LPS0054_AD
            }//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
    }elseif ( isset($_REQUEST['saveAction']) && $_REQUEST['saveAction']  == "Assign to IP" ){//**LPS0087_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0087_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0087_AD
            if (!$editAuth){//**LPS0087_AD
                continue;//**LPS0087_AD
            }//**LPS0087_AD
            
            $assignID=0;//**LPS0087_AD
            $ticketSQL =  "select * ";//**LPS0087_AD
            $ticketSQL .= " from CIL01  ";//**LPS0087_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0087_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0087_AD
            odbc_execute($ticketRes);//**LPS0087_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0087_AD
                
                
                    
                    $assignID=findIPContact($selTicket);//**LPS0087_AD
                
                
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0087_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0087_AD
                    //**LPS0087_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0087_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0087_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0087_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0087_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0087_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0087_AD
                    if( $back['availability'] == "Y" ){//**LPS0087_AD
                        $assignID = $backId;//**LPS0087_AD
                    }//**LPS0087_AD
                }//**LPS0087_AD
                
                if($assignID>0){//**LPS0087_AD
                    $insertCIL01OA = "insert into CIL01OA ";//**LPS0087_AD
                    $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0087_AD
                    $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0087_AD
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0087_AD
                    odbc_execute($cil01oaRes);//**LPS0087_AD
                }//**LPS0087_AD
            }//**LPS0087_AD
            if($assignID>0){//**LPS0087_AD
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0087_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0087_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0087_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0087_AD
                odbc_execute($rsStep);//**LPS0087_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0087_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0087_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0087_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0087_AD
                odbc_execute($rsUpdate);//**LPS0087_AD
                $updatedIds .= $selTicket . ",";//**LPS0087_AD
                array_push($updateIdsArray, $selTicket);//**LPS0087_AD
            }//**LPS0087_AD
        }//**LPS0087_AD
        
        echo "<div id='wrapper'>";//**LPS0087_AD
        echo "<div class='container'>";//**LPS0087_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0087_AD
        echo "<div class='panel panel-default'>";//**LPS0087_AD
        echo "<div class='panel-heading'>";//**LPS0087_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0087_AD
        echo "</div><!--panel heading-->";//**LPS0087_AD
        echo "<table width='100%'>";//**LPS0087_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0087_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0087_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0087_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0087_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0087_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0087_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0087_AD
        echo "</table>";//**LPS0087_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0087_AD
        echo "</div>";//**LPS0087_AD
        echo "</div>";//**LPS0087_AD
        echo "</div>";//**LPS0087_AD
        echo "</div>";//**LPS0087_AD
        
        
    }elseif ( isset($_REQUEST['saveAction']) && $_REQUEST['saveAction']  == "Assign to Sourcing" ){//**LPS0054_AD
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){//**LPS0054_AD
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");//**LPS0054_AD
            if (!$editAuth){//**LPS0054_AD
                continue;//**LPS0054_AD
            }//**LPS0054_AD
            
            $assignID=0;//**LPS0054_AD
            $ticketSQL =  "select * ";//**LPS0054_AD
            $ticketSQL .= " from CIL01  ";//**LPS0054_AD
            $ticketSQL .= " where ID01 = " . $selTicket . " ";//**LPS0054_AD
            $ticketRes = odbc_prepare($conn, $ticketSQL);//**LPS0054_AD
            odbc_execute($ticketRes);//**LPS0054_AD
            while ($ticketRow = odbc_fetch_array($ticketRes)){//**LPS0054_AD
                $assignID=findSrcContact($selTicket);//**LPS0054_AD
                $assigneeInfo = user_info_by_id ( $assignID );//**LPS0054_AD
                if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
                    //**LPS0054_AD - Start - Get Backup Info ********** *************************
                    $backId = trim(get_back_up_id( $assignID  ));	// Get Expedite BackupId//**LPS0054_AD
                    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
                    $back['name'] = trim($backInfo['NAME05']);//**LPS0054_AD
                    $back['email'] = trim($backInfo['EMAIL05']);//**LPS0054_AD
                    $back['pass'] = trim($backInfo['PASS05']);//**LPS0054_AD
                    $back['availability'] = trim($backInfo['AVAL05']);//**LPS0054_AD
                    if( $back['availability'] == "Y" ){//**LPS0054_AD
                        $assignID = $backId;//**LPS0054_AD
                    }//**LPS0054_AD
                }//**LPS0054_AD
                
                if($assignID>0){//**LPS0054_AD
                    $insertCIL01OA = "insert into CIL01OA ";//**LPS0054_AD
                    $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";//**LPS0054_AD
                    $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";//**LPS0054_AD
                    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);//**LPS0054_AD
                    odbc_execute($cil01oaRes);//**LPS0054_AD
                }//**LPS0054_AD
            }//**LPS0054_AD
            if($assignID>0){//**LPS0054_AD
                $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );//**LPS0054_AD
                $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";//**LPS0054_AD
                $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";//**LPS0054_AD
                $rsStep = odbc_prepare($conn, $insertStepSql);//**LPS0054_AD
                odbc_execute($rsStep);//**LPS0054_AD
                $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' );//**LPS0054_AD
                $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];//**LPS0054_AD
                $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";//**LPS0054_AD
                $rsUpdate = odbc_prepare($conn, $updateTicketSql);//**LPS0054_AD
                odbc_execute($rsUpdate);//**LPS0054_AD
                $updatedIds .= $selTicket . ",";//**LPS0054_AD
                array_push($updateIdsArray, $selTicket);//**LPS0054_AD
            }//**LPS0054_AD
        }//**LPS0054_AD
        
        echo "<div id='wrapper'>";//**LPS0054_AD
        echo "<div class='container'>";//**LPS0054_AD
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";//**LPS0054_AD
        echo "<div class='panel panel-default'>";//**LPS0054_AD
        echo "<div class='panel-heading'>";//**LPS0054_AD
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";//**LPS0054_AD
        echo "</div><!--panel heading-->";//**LPS0054_AD
        echo "<table width='100%'>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo "  <tr><td>&nbsp;</td></tr>";//**LPS0054_AD
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";//**LPS0054_AD
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";//**LPS0054_AD
        echo "</table>";//**LPS0054_AD
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        echo "</div>";//**LPS0054_AD
        
        
        
        //*******************************************************************//**LPS0054_AD END**********************************************************************************************
       
    }elseif ( isset($_REQUEST['saveAction']) && $_REQUEST['saveAction']  == "Re-Assign" ){                                                                                    //**LP0025_KS
                                                     
        //LP0072 - Backup fix Start *************************************
        $assigneeInfo = user_info_by_id ( $_REQUEST['newAssignee'] );
        
        if( $assigneeInfo['AVAL05'] == 'N' ){
            //LP0072 - Start - Get Backup Info ********** *************************
            $backId = trim(get_back_up_id( $_REQUEST['newAssignee']  ));	// Get Expedite BackupId
            $backInfo = user_info_by_id( $backId );
            $back['name'] = trim($backInfo['NAME05']);
            $back['email'] = trim($backInfo['EMAIL05']);
            $back['pass'] = trim($backInfo['PASS05']);
            $back['availability'] = trim($backInfo['AVAL05']);
            //**LP0072 
            
            if( $back['availability'] == "Y" ){
                $assignID = $backId;
            }else{
                $assignID = $_REQUEST['newAssignee'];
            }
        }else{
            $assignID = $_REQUEST['newAssignee'];
        }
        //LP0072 - Backup fix End  *************************************
        
        foreach ( $_REQUEST['ticketIds'] as $selTicket ){                                                                                   //**LP0025_KS
            
            $editAuth = getUserTicketAuth($_SESSION['userID'], $selTicket, "EDIT");                                                         //**LP0055_KS
            if (!$editAuth){                                                                                                                //**LP0055_KS
                continue;                                                                                                                   //**LP0055_KS
            }                                                                                                                               //**LP0055_KS
                       
            
            $ticketSQL =  "select * ";                                                                                                      //**LP0042
            $ticketSQL .= " from CIL01 ";                                                                                                   //**LP0042
            $ticketSQL .= " where ID01 = " . $selTicket . " ";                                                                              //**LP0042
            $ticketRes = odbc_prepare($conn, $ticketSQL);                                                                                    //**LP0042
            odbc_execute($ticketRes);                                                                                                        //**LP0042
            while ($ticketRow = odbc_fetch_array($ticketRes)){                                                                               //**LP0042
                //LP0072 - Modified changed AssignID
                $insertCIL01OA = "insert into CIL01OA ";                                                                                    //**LP0042
                $insertCIL01OA .= " VALUES ( " . $selTicket . ", " . date('Ymd') . ", '" . date('His') . "', ";                             //**LP0042
                $insertCIL01OA .= $ticketRow['OWNR01'] . ", " . $assignID . ", 1, " . $_SESSION['userID'] . ")";             //**LP0042
                $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                           //**LP0042
                odbc_execute($cil01oaRes);                                                                                                   //**LP0042
            }                                                                                                                               //**LP0042
            
                                                                                                                                            //**LP0025_KS
            $insertStepSql = "INSERT INTO CIL02 VALUES(" . get_next_unique_id(FACSLIB, "CIL02", "ID02", "" );                               //**LP0025_KS
            $insertStepSql .= "," . $selTicket . ", '" . $_REQUEST['comment'] . "'";                                                        //**LP0025_KS
            $insertStepSql .= ", " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '" . $private . "')";    //**LP0025_KS
            $rsStep = odbc_prepare($conn, $insertStepSql);                                                                                   //**LP0025_KS
            odbc_execute($rsStep);                                                                                                           //**LP0025_KS
                                                                                                                                            //**LP0025_KS
                                                                                                                                            
            //LP0072 - Modified changed AssignID
            $updateTicketSql = "UPDATE CIL01 SET UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', EDAT01=" . date ( 'Ymd' ); //**LP0025_KS
            $updateTicketSql .= ", ESTI01='" . date ( 'His' ) . "'" . ", ESLV01=0, UPID01=" . $_SESSION ['userID'];                         //**LP0025_KS
            $updateTicketSql .= ", OWNR01=" . $assignID . " WHERE ID01 = $selTicket";                                        //**LP0025_KS
            $rsUpdate = odbc_prepare($conn, $updateTicketSql);                                                                               //**LP0025_KS
            odbc_execute($rsUpdate);                                                                                                         //**LP0025_KS
                                                                                                                                            //**LP0025_KS
            $updatedIds .= $selTicket . ",";                                                                                                //**LP0025_KS
            array_push($updateIdsArray, $selTicket);                                                                                        //**LP0025_KS
        }                                                                                                                                   //**LP0025_KS
                                                                                                                                            //**LP0025_KS
        echo "<div id='wrapper'>";                                                                                                          //**LP0025_KS
                                                                                                                                            //**LP0025_KS
        echo "<div class='container'>";                                                                                                     //**LP0025_KS
        echo "<div class='col-md-8 col-sm-8 col-xs-8'>";                                                                                    //**LP0025_KS
        echo "<div class='panel panel-default'>";                                                                                           //**LP0025_KS
        echo "<div class='panel-heading'>";                                                                                                 //**LP0025_KS
        echo "  <h3 class='panel-title'>Multi Ticket Update</h3>";                                                                          //**LP0025_KS
        echo "</div><!--panel heading-->";                                                                                                  //**LP0025_KS
                                                                                                                                            //**LP0025_KS
        echo "<table width='100%'>";                                                                                                        //**LP0025_KS
        echo "  <tr><td>&nbsp;</td></tr>";                                                                                                  //**LP0025_KS
        echo "  <tr><td>&nbsp;</td></tr>";                                                                                                  //**LP0025_KS
        echo "  <tr><td>&nbsp;</td></tr>";                                                                                                  //**LP0025_KS
        echo "  <tr><td>&nbsp;</td></tr>";                                                                                                  //**LP0025_KS
        echo "  <tr><td>&nbsp;</td></tr>";                                                                                                  //**LP0025_KS
        echo " 	<tr><td class='titleBig'><big>The following ticket(s) have been updated.</big></td></tr>";                                  //**LP0025_KS
        echo "  <tr><td class='title'>" . substr($updatedIds, 0 , -1) . "</td></tr>";                                                       //**LP0025_KS
        echo "</table>";                                                                                                                    //**LP0025_KS
        echo "<meta http-equiv='refresh' content='2;URL=tickets2.php?from=saveMultiUpdate&status=1'>";                                      //**LP0025_KS
                                                                                                                                            //**LP0025_KS
        echo "</div>";                                                                                                                      //**LP0025_KS
        echo "</div>";                                                                                                                      //**LP0025_KS
        echo "</div>";                                                                                                                      //**LP0025_KS
        echo "</div>";                                                                                                                      //**LP0025_KS
        
    }

    
}else{
    ?>
    <center><br><br>No updates have been made<br><br>
    <!-- //**LP0025_KS <meta http-equiv="refresh" content="1;URL=tickets2.php?from=menu&status=1"> -->
    <meta http-equiv="refresh" content="1;URL=tickets2.php?from=saveMultiUpdate&status=1"> <!-- //**LP0025_KS  -->
    </center>
    <?php 
    
}

odbc_close( $conn );

?>