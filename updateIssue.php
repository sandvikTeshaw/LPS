<?

/**
 * System Name:             Logistics Process Support
 * Program Name:            updateIssue.php<br>
 * Development Reference:   DI868<br>
 * Description:             updateIssue.php updates a ticket with information changed by user and logs in history file the action
 *                          that was performed.  On resolution of the tickets define the notification flow and send mail to correct users<br>
 *                          <b>Note: This page has complex parts, before modification ensure that a firm understanding of the modification
 *                          is acquired</b><br>
 *
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 * DI868A     TS      19/08/2008  Fix to allow comments with<br>
 * DI868B     TS      22/08/2008  Resource Email Functionality<br>
 * DI868D     TS      22/08/2008  Requester Email Functionality<br>
 * DI868K     TS      19/12/2008  Check and save postpone<br>
 * DI932      TS      30/07/2009  Returns Functionality<br>
 * D0160      TS	  20/05/2010  Fix for losing delivery sequence<br>
 * D0455 	  TS 	  24/11/2011  Browser compatibility change<br>
 * D0249 	  TS	  02/11/2012  Change so only Requester can resolve an issue
 * I-2294568  ts	  13/03/2013  Character Set Change0
 * EC-D0249   TS      09/07/2013  Fix for issues
 * D0341	  TS	  14/08/2013  Changes for PFC and Planner logic for updating owner
 * i-2623511  TS      08/08/2014  Change for Vendor email problem
 * LP0002      IS     25/06/2015    Workflow issue after GLBAU-3542
 * LP0017     TS      03/02/2017  Survey Logic
 * LP0020     TS      08/06/2017  Completion Enhancement
 * LP0033     KS      05/04/2018  Change to LPS out of office functionality (SPIDER 2.0)
 * LP0036     KS      19/04/2018  Add new button in LPS "Logistics Complete" to record action timestamp (SPIDER 2.0)
 * LP0042     KS      15/05/2018  LPS Audit File for Ticket Ownership and Action
 * LP0029     TS      30/08/2018  Add function to close parent when all children are closed.
 * LP0051     AD      11/09/2018  Removal of default "my ticekt" view after action taken from LPS Queue
 * LP0044     AD      28/08/2018  Add Buttons to Queue - Logistics Complete & Send to Pricing
 * LP0053     AD      19/11/2018  Postpone Functionality
 * LP0055     AD      13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 * LP0055     KS      12/04/2019  fix
 * LP0078     AD      31/05/2019  fix CIL01OA ticket transfer audit file is showing false transfers
 * LP0054     AD      11/06/2019  LP0054 - LPS - Create "Assign to ____" Buttons
 * LP0080     TS      02/09/2019  Fix to send emails when ticket has been complete
 *  lp0087     AD     21/10/2019    Button assign to inventory Planner
 *  LP0086       AD    15/11/2019 GLBAU-17773  LPS - Add Buttons to Parent Tickets on Mass Upload
 */
/**
 */


include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
require_once 'copysource/mail/class.phpmailer.php';							//i-2623511
include 'copysource/superFunctions.php';//i-2623511



if (!isset($conn)) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_connect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if ($conn) {
    
} else {
    echo "Connection Failed";
}


if (isset($email)) {
    
    $userInfo [] = "";
    $userInfo = userInfo ( $email, $password );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['companyCode'] = $userInfo ['CODE05'];
    $_SESSION ['authority'] = $userInfo ['AUTH05'];
    $_SESSION ['email'] = $email;
    $_SESSION ['password'] = $password;
    
    if (! $_COOKIE ["mtp"]) {
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }
} elseif (isset($_SESSION ['email'])) {
    
    $userInfo [] = "";
    $userInfo = user_cookie_info ( $_SESSION ['email'] );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['companyCode'] = $userInfo ['CODE05'];
    $_SESSION ['email'] = $_SESSION ['email'];
    $_SESSION ['authority'] = $userInfo ['AUTH05'];
    
    if (!isset($_COOKIE ["mtp"])) {
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }
    
} elseif (isset($_COOKIE ["mtp"])) {
    
    $userInfo [] = "";
    $userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['companyCode'] = $userInfo ['CODE05'];
    $_SESSION ['authority'] = $userInfo ['AUTH05'];
    $_SESSION ['email'] = $_COOKIE ["mtp"];
} else {
    
}

if( !isset( $ID01 ) ){
    
    ?>
    <br><br><br><center>
    We seem to have encountered a problem, please try your submission again
    <a href="#" onclick="location.href = document.referrer; return false;">Try Again</a>
    <?php   
    
}

if( !isset( $STAT01 ) ){
    $STAT01 = "";
}

$saveSTAT01 = $STAT01;          //**LP0042
$assign = false;
$drpComplete = false;
$obpComplete = false;
$priComplete = false;

if ($STAT01 == "assign") {
    $STAT01 = 1;
    $assign = true;
    if (! $actionResponse) {
        $actionResponse = "Assign Resource";
    }
}
//********************************************* //**LP0044_AD START **************************************************
$reassignedFailed  = "";//**LP0044_AD
if ($STAT01 == "topricing") {//**LP0044_AD
    $STAT01 = 1;//**LP0044_AD
    $assign = true;//**LP0044_AD
    if (! $actionResponse) {//**LP0044_AD
        $actionResponse = "Assign to Pricing";
    }
    // echo '$RQID01=',$RQID01;
    $selId=$ID01;
    $reassignedMain  = "";//**LP0044_AD
    $reassignedBackup  = "";//**LP0044_AD
    
    $ticketSQL =  "select ID01,TYPE01,TEXT10,PCLS35,BRAN16,PRCC16,BPRC16,RSID01 ";         //**LP0044_AD
    $ticketSQL .= " from CIL01 JOIN CIL10 ON CAID10=ID01 JOIN CIL07 ON (ATTR07=ATTR10 AND HTYP07='PART')";    //serach for part numbers    //**LP0044_AD                                                                                                                               //**LP0044_AD
    $ticketSQL .= "  JOIN PARTS ON (CONO35='DI' AND PNUM35=TRIM(TEXT10)) ";         //find coresponding brand for part numbers //**LP0044_AD
    $ticketSQL .= "  LEFT JOIN CIL16 ON BRAN16=PCLS35 ";  //find coresponding contact person for brand //**LP0044_AD
    $ticketSQL .= " where ID01 = " . $selId . " FETCH FIRST ROW ONLY"; //**LP0044_AD
    //echo $ticketSQL; //**LP0044_AD
    //echo '<br>';
    $ticketRes = odbc_prepare($conn, $ticketSQL);    //**LP0044_AD
    odbc_execute($ticketRes);//**LP0044_AD
    $warning=true; //**LP0044_AD
    $mainContact=0;//**LP0044_AD
    $bkContact=0;//**LP0044_AD
    $assignee=0;//**LP0044_AD
    $partNr="";//**LP0044_AD
    while ($ticketRow = odbc_fetch_array($ticketRes)){  //**LP0044_AD
        
        $partNr=$ticketRow['TEXT10'];//**LP0044_AD
        //LP0055_AD2 if($ticketRow['TYPE01']==43 &&  $_SESSION ['userID'] != $ticketRow ['RSID01']){ //**LP0044_AD
        
        
        //lp0086   if(($ticketRow['TYPE01']==43 ||$ticketRow['TYPE01']==130 || $ticketRow ['TYPE01'] == 103 || $ticketRow ['TYPE01'] == 60 || $ticketRow ['TYPE01'] == 61 || $ticketRow ['TYPE01'] == 62 || $ticketRow ['TYPE01'] == 74 || $ticketRow ['TYPE01'] == 75)){ //LP0055_AD2
        if(($ticketRow['TYPE01']==43 ||$ticketRow['TYPE01']==130 ||$ticketRow['TYPE01']==133 || $ticketRow ['TYPE01'] == 103 || $ticketRow ['TYPE01'] == 60 || $ticketRow ['TYPE01'] == 61 || $ticketRow ['TYPE01'] == 62 || $ticketRow ['TYPE01'] == 74 || $ticketRow ['TYPE01'] == 75)){ //LP0086
            
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
                odbc_execute($userRes);//**LP0044_AD
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
                // echo "<BR>No Pricing Contact available for this part number ". $ticketRow['TEXT10']."<BR>";//**LP0044_AD
                if($actionResponse == "Sent to Pricing")$actionResponse="";//**LP0044_AD
            }  //**LP0044_AD
        }//**LP0044_AD
    }//end row          //**LP0044_AD
    //echo "<br>ASIGNEE/Bk-".$assignee." - ".$bkContact;
    if($assignee==0){             //nothing to assign//**LP0044_AD
        $reassignedFailed.=' '.$partNr;//**LP0044_AD
    }//**LP0044_AD
    else{//**LP0044_AD
        if(($_SESSION['authority'] == "L" )|| ($_SESSION['authority'] == "S" )){//**LP0044_AD
            $RSID01=$assignee;//**LP0044_AD
        }//**LP0044_AD
    }//**LP0044_AD
}        //**LP0044_AD

//********************************************* //**LP0044_AD END **************************************************

//LP0002 begin
if ($STAT01 == "pfc") {
    $STAT01 = 2;               //LP0020
    $PCPD01= date ( 'Ymd' );    //LP0020
    $PCPT01 = date ( 'His' );   //LP0020
    
    $pfcChecked = true;
    if (! $actionResponse) {
        $actionResponse = "PFC Action Complete";
    }
}

if ($STAT01 == "drpComplete") {
    $STAT01 = 3;        //LP0020 - Changed from 1 to 3
    $drpComplete = true;
    if (! $actionResponse) {
        //**LP0036  $actionResponse = "DRP Action Complete";
        $actionResponse = "Logistics Action Complete (DRP)";    //**LP0036
        
        
    }
}
if ($STAT01 == "obpComplete") {
    $STAT01 = 4;        //LP0020 - Changed from 1 to 3
    $obpComplete = true;
    
    if (! $actionResponse) {
        //**LP0036  $actionResponse = "DRP Action Complete";
        $actionResponse = "Logistics Action Complete";        //**LP0036
    }
}

// LP0002 End

if ($STAT01 == "priComplete") {                                 //**LP0036
    $STAT01 = 4;                                                //**LP0036
    $priComplete = true;                                        //**LP0036
    if (! $actionResponse) {                                    //**LP0036
        $actionResponse = "Pricing Action Complete";            //**LP0036
    }                                                           //**LP0036
}                                                               //**LP0036



// D0341 Begin
if ($STAT01 == "pfcComplete") {
    
    $STAT01 = 2;               //LP0020
    $RSID01 = $POFF01;
    $pfcComplete = true;
    
    $PCPD01= date ( 'Ymd' );    //LP0020
    $PCPT01 = date ( 'His' );   //LP0020
    
    if(!$actionResponse) {
        $actionResponse = "PFC Action Complete";
    }else{
        $actionResponse = "PFC Action Complete - " . $actionResponse;
    }
}
// D0341 End
//******************************** LP0054_AD START ***************************************************************************
if ($STAT01 == "totsd") {//LP0054_AD
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    $RSID01 = findTSD($ID01);//LP0054_AD
    if (! $actionResponse) {//LP0054_AD
        $actionResponse = "Assign To TSD ";//LP0054_AD
    }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "topfc") {//LP0054_AD
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    $RSID01 = findPFC($ID01);//LP0054_AD
    if (! $actionResponse) {//LP0054_AD
        $actionResponse = "Assign To PFC ";//LP0054_AD
    }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "toobp") {//LP0054_AD
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    $RSID01 = $POFF01;//LP0054_AD
    if (! $actionResponse) {//LP0054_AD
        $actionResponse = "Assign To OBP ";//LP0054_AD
    }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "tobuyer") {//LP0054_AD
    
    if(trim($BUYR01) == 0 ){
        
        $partSql = "SELECT PLAN35, TEXT10 FROM CIL10 T1 "
            . "INNER JOIN CIL07 T2 "
                . "ON T1.ATTR10 = t2.ATTR07 "
                    . "INNER JOIN PARTS T3 "
                        . "ON T1.TEXT10 = T3.PNUM35 AND CONO35='DI' "
                            . "WHERE CAID10=$ID01 AND HTYP07='PART' ";
                            
                            $partRes = odbc_prepare($conn, $partSql);
                            odbc_execute($partRes);
                            
                            while ($partRow = odbc_fetch_array($partRes)){
                                
                                $BUYR01 = trim($partRow['PLAN35']);
                                
                            }
                            
    }
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    $psql="SELECT USER25 FROM CIL25 WHERE PLAN25=".trim($BUYR01);//LP0054_AD
    $pres= odbc_prepare($conn, $psql);   //LP0054_AD
    odbc_execute($pres);//LP0054_AD
    while ($prow = odbc_fetch_array($pres)){ //LP0054_AD
        $RSID01 = $prow['USER25'];}//LP0054_AD
        if (! $actionResponse) {//LP0054_AD
            $actionResponse = "Assign To Buyer ";//LP0054_AD
        }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "torequester") {//LP0054_AD
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    $RSID01 = $RQID01;//LP0054_AD
    if (! $actionResponse) {//LP0054_AD
        $actionResponse = "Assign To Requester ";//LP0054_AD
    }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "tofreight") {//LP0054_AD
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    
    if( $CLAS01 != 7 ){
        
        $RSID01 = findFreightContact($ID01);//LP0054_AD
    }elseif( $TYPE01 == 31 || $TYPE01 == 32|| $TYPE01 == 33 || $TYPE01 == 34){
        
        $RSID01 = findFreightContactAttrib($ID01, $TYPE01);//LP0054_AD
    }
    
    if (! $actionResponse) {//LP0054_AD
        $actionResponse = "Assign To Freight ";//LP0054_AD
    }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "towar") {//LP0054_AD
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    
    if( $CLAS01 != 7 ){
        $RSID01 = findWarehouseContact($ID01);//LP0054_AD
    }else{
        $RSID01 = findWarehouseContactAttrib($ID01, $TYPE01);
    }
    
    if (! $actionResponse) {//LP0054_AD
        $actionResponse = "Assign To Warehouse ";//LP0054_AD
    }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "tosrc") {//LP0054_AD
    $STAT01 = 1;//LP0054_AD
    $assign = true;//LP0054_AD
    $RSID01 = findSrcContact($ID01);//LP0054_AD
    if (! $actionResponse) {//LP0054_AD
        $actionResponse = "Assign To Sourcing ";//LP0054_AD
    }//LP0054_AD
}//LP0054_AD
elseif  ($STAT01 == "toip") {//LP0087_AD
    $STAT01 = 1;//LP0087_AD
    $assign = true;//LP0087_AD
    $RSID01 = findIPContact($ID01);//LP0054_AD
    if (! $actionResponse) {//LP0087_AD
        $actionResponse = "Assign To Inventory Planner ";//LP0087_AD
    }//LP0087_AD
}//LP0087_AD

$assigneeInfo = user_info_by_id ( $RSID01 );//**LPS0054_AD
if( $assigneeInfo['AVAL05'] == 'N' ){//**LPS0054_AD
    $backId = trim(get_back_up_id( $RSID01  ));	// Get Expedite BackupId//**LPS0054_AD
    $backInfo = user_info_by_id( $backId );//**LPS0054_AD
    if( trim($backInfo['AVAL05']) == "Y" ){//**LPS0054_AD
        $RSID01 = $backId;//**LPS0054_AD
    }//**LPS0054_AD
}//**LPS0054_AD

//******************************** LP0054_AD END ***************************************************************************

if ($currentOwnerId != $RSID01 && $RSID01 != 0 && $RSID01 != "" && $assign != true) {
    $assign = true;
    $actionResponse = "Assign Resource - " . $actionResponse;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?
echo $SITE_TITLE;
?></title>

<style type="text/css">

<!--
@import url(copysource/styles.css);
-->
.survey{
	list-style-type: none;
	padding: 0;
}
.surveyTitle{
	list-style-type: none;
	font-weight: bold;
	font-size: 25px;
	padding-bottom: 10px;
}
ul p{
	color: red;
	width: 20%;
	float: left;
}
.surveyList{
	width:60%;
	float: auto;
	text-align: left;
	font-size: 15px;
	font-weight: bold;

}
.radioLabel{
    width:100%;
	padding-left: 0;
	overflow: hidden;
}
input .radio{
	margin-right: 0;
	padding-right: 0;
	text-align: left;
	display: inline; 
	overflow: hidden;
}
.radioInput{
	width:30%;
	padding-left: 0;
	float: left;
    overflow: hidden;
}
.buttonClass{
	display:block;
	width:205px;
	overflow: hidden; 
}
.buttonForm{
	width:100px;
	overflow: hidden;
	float: left;
	
}
.addInfoStyle {
	width: 57%;
	font-weight: bold;
	font-size: 15px;
	text-align: left;
}


	



</style>
<script type="text/javascript">
function setFocus(){
    document.frm.email.focus();
}
</script>
</head>
<body>
<?


include_once 'copysource/header.php';

//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );


	if( !isset($_SESSION ['classArray']) ){
	 	$_SESSION ['classArray'] = get_classification_array ();
	}
	if( !isset($_SESSION ['typeArray']) ){
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
	
$newOBP = 0;                                            //**LP0033	
if (isset($_REQUEST['newOBP'])){                        //**LP0033
    $newOBP = $_REQUEST['newOBP'];                      //**LP0033
}                                                       //**LP0033
	
//menuFrame ( $SITENAME );
include_once 'copysource/menu.php';


$answersSql = "SELECT * FROM CIL34 T1"
            . " LEFT JOIN CIL36 T2"
            . " ON T1.ID34 = T2.QID36 AND T2.TID36 = $ID01"
            . " WHERE T1.CLAS34 = $CLAS01 AND T1.TYPE34 = $TYPE01";

$answerRes = odbc_prepare ( $conn, $answersSql );
if( odbc_execute ( $answerRes ) ){
    
}else{
    $handle = fopen("./sqlFailures/sqlFails.csv","a+");
    fwrite($handle, "532 - updateIssues.php," . $answersSql . "\n" );
    fclose($handle);
}
while( $ansRow = odbc_fetch_array( $answerRes )){
    $ansID = $ansRow['ID34'];

    if( $ansRow['QTYP34'] == "SEL" ||  $ansRow['QTYP34'] == "RAD"  ){
        if( isset( $_REQUEST['quest_' . $ansID ] ) && $_REQUEST['quest_' . $ansID ] != "0" && $_REQUEST['quest_' . $ansID ] !== NULL ){
            if( $ansRow['AID36'] === NULL ){
                if( $_REQUEST['quest_' . $ansID ]  ){
                    $insertAnswer = "INSERT INTO CIL36 VALUES( " . get_next_unique_id ( FACSLIB, "CIL36", "ID36", "" ) . ", " . $_REQUEST['quest_' . $ansID ] . ", " . $ansRow['ID34'] . ", '', $ID01)";
                    $insertAnswerRes = odbc_prepare ( $conn, $insertAnswer );
                    odbc_execute ( $insertAnswerRes );
                }
            }else{
                if( isset($_REQUEST['quest_' . $ansID ] ) && isset($ansRow['ID36'])) {
                    $updateAnswer = "UPDATE CIL36 SET AID36 = " . trim($_REQUEST['quest_' . $ansID ]) . " WHERE ID36 = " . trim($ansRow['ID36']);
                    $UpdateAnswerRes = odbc_prepare ( $conn, $updateAnswer );
                    
                    
                    if( odbc_execute ( $UpdateAnswerRes ) ){
                        
                    }else{
                        
                        $handle = fopen("./sqlFailures/sqlFails.csv","a+");
                        fwrite($handle, "555 - updateIssues.php," . $updateAnswer . "\n" );
                        fclose($handle);
                    }
                }
            }
        }
    }elseif( $ansRow['QTYP34'] == "TXT" ){
        if( isset( $_REQUEST['quest_' . $ansID ] ) && $_REQUEST['quest_' . $ansID ] != "" && $_REQUEST['quest_' . $ansID ] !== NULL ){
            if( $ansRow['AID36'] === NULL ){
                $insertAnswer = "INSERT INTO CIL36 VALUES( " . get_next_unique_id ( FACSLIB, "CIL36", "ID36", "" ) . ", 0, " . $ansRow['ID34'] . ", '" . trim($_REQUEST['quest_' . $ansID ]) ."', $ID01)";
                $insertAnswerRes = odbc_prepare ( $conn, $insertAnswer );
                if( odbc_execute ( $insertAnswerRes ) ){
                    
                }else{
                    
                    $handle = fopen("./sqlFailures/sqlFails.csv","a+");
                    fwrite($handle, "571 - updateIssues.php," . $insertAnswer . "\n" );
                    fclose($handle);
                }

            }else{

                $updateAnswer = "UPDATE CIL36 SET TEXT36 = '" . trim($_REQUEST['quest_' . $ansID ]) . "' WHERE ID36 = " . $ansRow['ID36'];
                $UpdateAnswerRes = odbc_prepare ( $conn, $updateAnswer );
                if( odbc_execute ( $UpdateAnswerRes ) ){
                    
                }else{
                    
                    $handle = fopen("./sqlFailures/sqlFails.csv","a+");
                    fwrite($handle, "584 - updateIssues.php," . $updateAnswer . "\n" );
                    fclose($handle);
                }
            }
        }

    }
}


$employeeArray = array();
array_push($employeeArray, $_SESSION ['userID'] );
$superAuthority[] = "";

$superAuthArray = get_super_reports_authority( $_SESSION ['userID'], $employeeArray, $conn, $superAuthority, $RQID01,1);


//D0249 - Added logic so only requester or System admin can resolve issue.
if ($STAT01 == 5 && ($_SESSION ['userID'] == $RQID01 || $_SESSION ['authority'] == "S" || $superAuthArray['requester'] == true ) ) {		//EC-D0249 - Bracket issue, nested ending ) in wrong place.
    
	$DESC01 = str_replace( " **** COMPLETE ****", "", $DESC01 );
    $CDAT01 = date ( 'Ymd' );
    $CTIM01 = date ( 'His' );
    $mssg = "RESOLVED";

//LP0002 - Added $drpComplete to conditional statement to add ***Complete**** to DRP actioned tickets
//**LP0036  } elseif( ($STAT01 == 5 && ($_SESSION ['userID'] != $RQID01)) ||  ( $drpComplete == true ) || ( $obpComplete == true )){
} elseif( ($STAT01 == 5 && ($_SESSION ['userID'] != $RQID01)) ||  ( $drpComplete == true ) || ( $obpComplete == true ) || ( $priComplete == true )){            //**LP0036
    

	//This logic takes resolution from Non-Requester and changes to Complete and resets STAT01 = 1, so is still open
	//Adds OCCR01 = 1, this will allow for reporting and stats and still able to keep tickeet open
	//Adds Complete comment in description which is shown in ticket listing

	$DESC01 = str_replace( " **** COMPLETE ****", "", $DESC01 );
	$DESC01 .= " **** COMPLETE ****";

	if( $drpComplete == true ){
	    $STAT01 = 3;    //LP0020 - Changed from 1 to 3 - DRP Complete
	    $DCPD01= date ( 'Ymd' );   //LP0020 
	    $DCPT01= date ( 'His' );   //LP0020 
	    
	}else{
	    $STAT01 = 4;    //LP0020 - Changed from 1 to 4 - Complete
	    $CPDT01= date ( 'Ymd' );   //LP0020 
	    $CPTI01= date ( 'His' );   //LP0020 
	}
	$CDAT01 = 0;
	$CTIM01 = "";
	$mssg = "COMPLETE";

	$assign = true;
	$complete = true;
	$RSID01 = $RQID01;
	$OCCR01 = 1;		//EC-D0249 - Set OCCR01

	$actionResponse = "COMPLETE - " . $actionResponse;

}else {

	$DESC01 = str_replace( " **** COMPLETE ****", "", $DESC01 );

    $CDAT01 = 0;
    $CTIM01 = "";
    $mssg = "UPDATED";
}

$typeName = get_type_name ( $TYPE01 );
$className = get_class_name ( $CLAS01 );


//DI868K -  Added postpone check
/*//LP0053_AD //disabling old postpone functionality
//Check to see if escalation has been postponed
if ($postpone != 0) {
    switch ($postpone) {
        case 1 :
            $postponeInfo = get_postpone_date_time ( 24 );
            $emda01 = 1;
            break;
        case 2 :
            $postponeInfo = get_postpone_date_time ( 48 );
            $emda01 = 2;
            break;
        case 3 :
            $postponeInfo = get_postpone_date_time ( 72 );
            $emda01 = 3;
            break;
        default :
            break;
    }
    $escalationDate = $postponeInfo ['date'];
} else {*///LP0053_AD //disabling old postpone functionality
    $escalationDate = date ( 'Ymd' );
       //}//LP0053_AD //disabling old postpone functionality

    //****************LP0053_AD START *****************

    $emda01=0;//LP0053_AD
    if(isset($ck_holdEscalation) and  ($saveSTAT01 != "assign"))//LP0053_AD
        if($postponeReason>8)//LP0053_AD
        {//LP0053_AD
            $postponeReasonTxt="";//LP0053_AD
            $sqlpr= "SELECT * FROM CIL47 WHERE ID47=".($postponeReason-8);//LP0053_AD
            $prRes = odbc_prepare ( $conn, $sqlpr);//LP0053_AD
            odbc_execute ( $prRes );//LP0053_AD
            while( $prRow = odbc_fetch_array( $prRes )){//LP0053_AD
                $postponeReasonTxt=$prRow['DESC47'];//LP0053_AD
            }//LP0053_AD
            echo "<center>**** ESCALATION ON HOLD ".$postponeReasonTxt." ****</center>";//LP0053_AD
            $emda01=$postponeReason;//LP0053_AD
            $next02Id = get_next_unique_id ( FACSLIB, "CIL02", "ID02", "" );//LP0053_AD
            $insertHoldSql = "INSERT INTO CIL02 VALUES( $next02Id, $ID01, '";//LP0053_AD
            $insertHoldSql .= "ESCALATION ON HOLD ".$postponeReasonTxt. "', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '$visible')";//LP0053_AD
            
            $insertHoldRes = odbc_prepare ( $conn, $insertHoldSql );//LP0053_AD
            odbc_execute ( $insertHoldRes );//LP0053_AD
            
        }//LP0053_AD
    if(isset($postponedBefore) and ! isset($ck_holdEscalation) )//LP0053_AD
    {//LP0053_AD
        echo "<center>**** ESCALATION HAS BEEN REENABLED  ****</center>";//LP0053_AD
        $next02Id = get_next_unique_id ( FACSLIB, "CIL02", "ID02", "" );//LP0053_AD
        $insertHoldSql = "INSERT INTO CIL02 VALUES( $next02Id, $ID01, '";//LP0053_AD
        $insertHoldSql .= "ESCALATION HAS BEEN REENABLED', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '$visible')";//LP0053_AD            
        $insertHoldRes = odbc_prepare ( $conn, $insertHoldSql );//LP0053_AD
        odbc_execute ( $insertHoldRes );//LP0053_AD
            
    }//LP0053_AD
    
    //****************LP0053_AD END *******************
    
//D0301 - Added to set RSID if blank
    if( !isset( $RSID01 ) || $RSID01 == "" || $RSID01 == 0 ){
        if( isset( $CURRENT_RSID01 ) ){
	       $RSID01 = $CURRENT_RSID01;
        }else{
            $RSID01 = 0;
        }
}

$currentRSID = $RSID01;
    $resourceAvail = "N";
    $count = 0;
    while( $resourceAvail == "N" || $count == 10 ){
        $count++;
       $resourceInfo = user_info_by_id ( $RSID01 );
       $resourceAvail = trim($resourceInfo['AVAL05']);
       if( $resourceAvail == "N" ){
           if( trim($resourceInfo['BACK05']) != 0 ){
                $RSID01 = trim($resourceInfo['BACK05']);
           }else{
                break;
           }
       }else{
        break;
       }
       if( $count >= 10 ){
           $RSID01 =  $currentRSID;
           break;
       }
    }


$DESC01 = strtr($DESC01, $GLOBALS['normalizeSaveChars']);		//i-2294568

//D0249 - Added to flag complete or not 1=complete, 0=open;
if( !isset($OCCR01) ){
	$OCCR01 = 0;		//EC-D0249 - Set OCCR01 = 0 when not existing, this will include when resolved.
}
$getOwnerSql="SELECT OWNR01,POFF01 FROM CIL01 WHERE ID01=$ID01";//LP0078_AD
$getOwnerRes = odbc_prepare ( $conn, $getOwnerSql);//LP0078_AD
odbc_execute ( $getOwnerRes );//LP0078_AD
$actualOwner=0;
while( $getOwnerRow = odbc_fetch_array( $getOwnerRes )){//LP0078_AD
    $actualOwner=$getOwnerRow['OWNR01'];//LP0078_AD
    if($CLAS01==6)$actualOwner=$getOwnerRow['POFF01'];//LP0078_AD;
}  //LP0078_AD
//Update Sql for Issue
$updateSql = "UPDATE CIL01 SET DESC01 = '$DESC01', RQID01=$RQID01, CLAS01=$CLAS01, TYPE01=$TYPE01, PRTY01=$PRTY01, RSID01=$RSID01,";
$updateSql .= "STAT01=$STAT01, CDAT01=$CDAT01, CTIM01='$CTIM01', UDAT01=" . date ( 'Ymd' ) . ", UTIM01='" . date ( 'His' ) . "', ";
$updateSql .= "EDAT01=$escalationDate, ESTI01='" . date ( 'His' ) . "', ESLV01=0, UPID01=" . $_SESSION ['userID'] . ", OCCR01=" . $OCCR01;		//EC-D0249 - Added OCCR01 to SQL


if ( isset($KEY101) && isset( $OLD_KEY101 ) && $KEY101 != $OLD_KEY101) {

    $updateSql .= ", KEY101='$KEY101', KEY301='" . $_SESSION ['userID'] . "'";
}
if (isset($KEY201) && isset( $KEY201 ) && $KEY201 != $KEY201) {
    $updateSql .= ", KEY201='$KEY201', KEY401='" . $_SESSION ['userID'] . "'";
}
if (isset($CHCE01)) {
    $updateSql .= ", CHCE01='$CHCE01', IMPT01='" . addslashes ( $IMPT01 ) . "'";
}

$newOwner = $currentOwnerId;                                                                    //**LP0042

if ( ($assign == true || $RSID01 != 0) &&  $drpComplete != true ) {     //LS0002 - Added $drpComplete to if statement to ensure the issue is assigne to Requester
	// D0341 Point of First Contact Changes - DS
	//    $updateSql .= ", OWNR01=$RSID01, POFF01=$RSID01"; // D0341
   $updateSql .= ", OWNR01=$RSID01 ";
   //Check to see if Not class 3 or if Owner is PFC
   $newOwner = $RSID01;                                                                         //**LP0042
   
   if ($CLAS01 != 3 || ( isset( $POFF01) && isset( $OWNR01 ) && $POFF01 == $OWNR01 )) {
       if ($newOBP == 0){                                   //**LP0033                                    
       $updateSql .= ", POFF01=$RSID01 ";
       }                                                    //**LP0033
   }
   
} elseif( isset($pfcComplete) && $pfcComplete == true ){       //D0341 - Added to resource ticket to Purchase Office (Planner)

    //$updateSql .= ",OWNR01=$POFF01";                                      //LP0020
    $updateSql .= ",OWNR01=$POFF01, PCPD01=$PCPD01, PCPT01='$PCPT01'";       //LP0020
    $newOwner = $POFF01;                                                                         //**LP0042
    
}elseif( isset($drpComplete) && $drpComplete == true ){       //LS0002

    //$updateSql .= ",OWNR01=$RQID01";                                      //LP0020
    $updateSql .= ",OWNR01=$RQID01, DCPD01=$DCPD01, DCPT01='$DCPT01'";       //LP0020
    $newOwner = $RQID01;                                                                         //**LP0042
    
} elseif( isset($pfcChecked) && $pfcChecked == true ){       //LP0002 changes begin
   
       $amInfo = get_am_info_by_order($orderNumber,$desnNumber,$requestdInfo="ID");
       $updateSql .= ", OWNR01=$amInfo";
       $newOwner = $amInfo;                                                                      //**LP0042
}
if( $STAT01== 2 ){ 
    $updateSql .= ",PCPD01=$PCPD01, PCPT01='{$PCPT01}'";       //LP0020
}


if( $STAT01== 4 ){                                         //LP0020
    $updateSql .= ",CPDT01=$CPDT01, CPTI01='{$CPTI01}'";      //LP0020
}                                                          //LP0020

//DI868K  - Added for postpose flag
//if ($emda01 != 0) { //LP0053_AD
    $updateSql .= ", EMDA01=$emda01";
//}//LP0053_AD

if ($newOBP != 0){                                   //**LP0033 
    $updateSql .= ", POFF01 = " . $newOBP . " ";     //**LP0033   
}                                                    //**LP0033       
    
$updateSql .= " WHERE ID01=$ID01";


//echo $updateSql;


//Execute issue update sql
//echo $updateSql;
$updateRes = odbc_prepare ( $conn, $updateSql );
if( odbc_execute ( $updateRes ) ){
    
}else{
    
    $handle = fopen("./sqlFailures/sqlFails.csv","a+");
    fwrite($handle, "823 - updateIssues.php," . $updateSql . "\n" );
    fclose($handle);
}


$CTYP01 = 0; 
if( !isset( $CURRENT_RSID01 )){
    $CURRENT_RSID01 = 0;
}
if (($saveSTAT01 == "1") and ( $RSID01 != $CURRENT_RSID01)){                                                                                 //**LP0042
    $CTYP01 = 1;                                                                                                                            //**LP0042
}elseif (($saveSTAT01 == "assign") and ($RSID01 != $CURRENT_RSID01)){                                                                       //**LP0042
    $CTYP01 = 1;                                                                                                                            //**LP0042
}elseif ($saveSTAT01 == "pfcComplete"){                                                                                                     //**LP0042
    $CTYP01 = 2;                                                                                                                            //**LP0042
}elseif (($saveSTAT01 == "obpComplete") or ($saveSTAT01 == "drpComplete")){                                                                 //**LP0042
    $CTYP01 = 3;                                                                                                                            //**LP0042
}elseif ($saveSTAT01 == "topricing"){                                                                                                       //**LP0042
    $CTYP01 = 4;                                                                                                                            //**LP0042
}elseif ($saveSTAT01 == "priComplete"){                                                                                                     //**LP0042
    $CTYP01 = 6;                                                                                                                            //**LP0042
}elseif ($saveSTAT01 == "5"){                                                                                                               //**LP0042
    $CTYP01 = 7;                                                                                                                            //**LP0042
}                                                                                                                                           //**LP0042
if($newOwner==0)$newOwner=$currentOwnerId; //**LP0078_AD
//LP0078_AD if ($CTYP01 != 0){                                                                                                                          //**LP0042
if (($CTYP01 != 0)||$newOwner!=$actualOwner){  //**LP0078_AD                                                                                                                        //**LP0042
        $insertCIL01OA = "insert into CIL01OA ";                                                                                                //**LP0042
    $insertCIL01OA .= " values (" . $ID01 . ", " . date('Ymd') . ", '" . date('His') . "', ";                                               //**LP0042
//LP0078    $insertCIL01OA .= $currentOwnerId . ", " . $newOwner . ", " . $CTYP01 . ", " . $_SESSION['userID'] . ") ";                              //**LP0042
    $insertCIL01OA .= $actualOwner . ", " . $newOwner . ", " . $CTYP01 . ", " . $_SESSION['userID'] . ") ";                              //**LP0042
    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                                       //**LP0042
    if( odbc_execute($cil01oaRes) ){
        
    }else{
        $handle = fopen("./sqlFailures/sqlFails.csv","a+");
        fwrite($handle, "882 - updateIssues.php," . $insertCIL01OA . "\n" );
        fclose($handle);
    }
      
    
}                                                                                                                                           //**LP0042

//D0341 - Start - Removed CIL18 functionality, deprecated by CIL34 - CIL36 functionality******************************
//Check to see if Class is Global Order Processing
//If it is then save PFC answers
/*
if ($CLAS01 == 3) {
    for($i = 1; $i <= $checkCounter; $i ++) {
        if (${check_ . $checkCounter}) {
            if (${check_ . $checkCounter} == "N") {
                $chkScore = ${SCORE_ . $checkCounter} * - 1;
            } else {
                $chkScore = ${SCORE_ . $checkCounter};
            }
            //Check to see if the CIL18x record exists
            if (${counter18X_ . $checkCounter}) {
                $update18XSql = "UPDATE CIL18X SET SCOR18X=$chkScore WHERE CAID18X=$ID01 AND CHCK18X=" . ${CHCKID_ . $checkCounter};
                $update18XRes = odbc_prepare ( $conn, $update18XSql );
                odbc_execute ( $update18XRes );
            } else {
                $insert18XSql = "INSERT INTO CIL18X VALUES(";
                $insert18XSql .= " '$CONO', $ID01, " . ${CHCKID_ . $checkCounter} . ", $chkScore )";
                $insert18XRes = odbc_prepare ( $conn, $insert18XSql );
                odbc_execute ( $insert18XRes );
            }
        }
    }

}
*/
//D0341 - End - Removed CIL18 functionality, deprecated by CIL34 - CIL36 functionality******************************

//Insert Query for Steps(Ticket History)


$actionResponse = str_replace ( "$", "�", $actionResponse );

$oldPriority = get_priority ( $currentPriority, "short" );

if ($currentPriority != $PRTY01 && $CLAS01 != 9 ) {
    $actionResponse = "<font color='red'><b>Priority has been changed from $oldPriority</b></font><br>" . $actionResponse;
}

//DI868A Added so that comments with single quotes can be added to DB, addslashes does not work properly native on iSeries
$actionResponse = str_replace ( "'", "''", $actionResponse );
$next02Id = get_next_unique_id ( FACSLIB, "CIL02", "ID02", "" );
$insertStepSql = "INSERT INTO CIL02 VALUES( $next02Id, $ID01, '";
$insertStepSql .= $actionResponse . "', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', " . $_SESSION ['userID'] . ", '$visible')";

//echo $insertStepSql;
//Execute Step insert sql
$insertStepRes = odbc_prepare ( $conn, $insertStepSql );

if(odbc_execute ( $insertStepRes ) ){
    
}else{
    $handle = fopen("./sqlFailures/sqlFails.csv","a+");
    fwrite($handle, "913 - updateIssues.php," . $insertStepSql . "\n" );
    fclose($handle);
}

//Cycle through the issue attribute
for($a = 1; $a <= $attributeCount; $a ++) {
    //Check to see if date attribute
    if (${'attribType_' . $a} == "DATE") {
        //date attribute need to concatonate year, month, day
        if (strlen ( ${'month' . $a} ) == 1) {
            $month = "0" . ${'month' . $a};
        } else {
            $month = ${'month' . $a};
        }
        if (strlen ( ${'day' . $a} ) == 1) {
            $day = "0" . ${'day' . $a};
        } else {
            $day = ${'day' . $a};
        }

        $attribValue = ${'year' . $a} . $month . $day;
    } else {
        if (${'attribType_' . $a} == "SODP") {
            if( $CLAS01 != 8 && $TYPE01 !=42 ) {  
                $attribValue = ${trim ( strtolower ( ${'attribType_' . $a} ) ) . $a} . " " . ${'sodpb' . $a};
                $orderNumber = $attribValue;
            }

        //D0160 - Added to find DI Customer Number and Delivery Sequence attributes
        }else if (${'attribType_' . $a} == "DICU") {
            //Added to append Customer Number and Delivery Sequence
            $attribValue = ${trim ( strtolower ( ${'attribType_' . $a} ) ) . $a} . " " . ${'dicub' . $a};
            //$orderNumber = $attribValue;
        }else if (${'attribType_' . $a} == "CURN") {//LP0055_AD2
            //Added to append Curency code (type text)//LP0055_AD2
            $attribValue = ${"text" . $a};//LP0055_AD2         
        }else {
            $attribValue = ${trim ( strtolower ( ${'attribType_' . $a} ) ) . $a};
            if (${'attribType_' . $a} == "PART") {
                $partNumber = $attribValue;
            }
        }
    }

    //check to see if attribute already exists in db for issue
    $attribSql = "";
    
    if (isset( ${'attribExist_' . $a} ) && ${'attribExist_' . $a} == "Y") {

        if( isset( $attribValue )) {
        //Create update sql, for attribute that already exists for issue
            $attribSql = "Update CIL10L01 SET TEXT10='" . trim ( $attribValue ) . "' WHERE CAID10='$ID01' AND ATTR10=" . ${'attribId_' . $a};
        }
    } else {
        //check to see if attribute was entered
        if ($attribValue) {
            //create Insert sql, for attributes that do not already exist for issue
            $attribSql = "INSERT INTO CIL10 VALUES( " . get_next_unique_id ( FACSLIB, "CIL10", "LINE10", "" ) . ", $ID01, ";
            $attribSql .= ${'attribId_' . $a} . ", '$attribValue', 'DSH', 'CIL', '')";
        }
    }
    //Execute attribute SQL
    //echo $attribSql . "<hr>";
    if( isset( $attribSql ) && $attribSql != "" ){
        $attribRes = odbc_prepare ( $conn, $attribSql );
        odbc_execute ( $attribRes );
    }
}

//DI868D - Added to send email notification of ticket resolution to Requester
if ($STAT01 == 5) {

    $parentSql = "SELECT PRNT01 FROM CIL01 WHERE ID01=$ID01";
    $parentRes = odbc_prepare ( $conn, $parentSql );
    odbc_execute ( $parentRes );
    
    while ($prntRow = odbc_fetch_array($parentRes)){  
        $parentID = $prntRow['PRNT01'];
    }
    if( $parentID ){
        
        $childOpenCountSQL = "SELECT ID01 FROM CIL01 WHERE PRNT01 = $parentID and STAT01 < 5";
        $chldCountRes = odbc_prepare($conn, $childOpenCountSQL);                                                                                       //**LP0042
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
            
        }
        
        
    }
    
    $requesterInfo = get_ticket_requester_info ( $ID01 );

    //Encrypt password send in address
    $encryptedPassword = base64_encode ( $requesterInfo ['pass'] );
    $toUsers = trim ( $requesterInfo ['email'] );
 
    //Start of Temp Change for Ceva and DHL for Server Change ******************************************
    $tmpUser = strtoupper(  $toUsers );
    if ( ( strpos( $tmpUser, 'DHL' ) !== false ) ) {
        $tmpUrl = $mtpUrl;
        $mtpUrl = "http://sedas5.is.sandvik.com:89/production/smc/global/lps"; 
    }
    //End of Temp Change for Ceva and DHL for Server Change ******************************************
    
    //LP0080 - Remove ( \n\n ) from email, causes PHP error
    $message = "<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>";
    $message .= "Dear " . $requesterInfo ['name'] . ",<br><br><br>";
    $message .= "Your " . $SITENAME . " ticket has been resolved<br><br>";
    $message .= "Ticket#: " . $ID01 . "<br>";
    $message .= "Classification: " . $className . "<br>";
    $message .= "Type: " . $typeName . "<br><br><br>";
    $message .= "To directly reference the ticket click the link below:<br><br>";
    $message .= "<a href='$mtpUrl/showTicketDetails.php?ID01=$ID01&email=$toUsers&epass=$encryptedPassword'>View Ticket</a><br><br>";
    $message .= "Thank You<br>";
    $message .= $FROM_USER;
    $subject = "$SITENAME Resolved - #$ID01 - $DESC01";

    //Sets up mail to use HTML formatting
    $strHeaders = "MIME-Version: 1.0\r\n";
    $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $strHeaders .= "From: " . $FROM_MAIL;

    if( mail( $toUsers, $subject, $message, $strHeaders ) ){
        
    }else{
        $handle = fopen("./mailFailures/mailErrors.csv","a+");
        fwrite($handle, "1031 - UpdateIssue," . $toUsers . "," . $subject . "," . substr($message, 0, 100 ) . "\n" );
        fclose($handle);
    }
    
    //Start of Temp Change for Ceva and DHL for Server Change ******************************************
    if( isset( $tmpUrl ) ){
        $mtpUrl = $tmpUrl;
    }
    //End of Temp Change for Ceva and DHL for Server Change ******************************************
}
//Send Carbon Copy emails
if ($ccSelected) {
    
    //LP0080 - Remove ( \n\n ) from email, causes PHP error
    $message = "<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>";
    $message .= "Dear User,<br><br><br>";
    $message .= "You Have been Carbon Copied on an updated " . $SITENAME . " ticket<br><br>";
    $message .= "Ticket#: " . $ID01 . "<br>";
    $message .= "Classification: " . $className . "<br>";
    $message .= "Type: " . $typeName . "<br><br><br>";
    $message .= "To directly reference the ticket click the link below:<br><br>";
    $message .= "$mtpUrl/showTicketDetails.php?ID01=$ID01<br><br><br>";
    $message .= "Thank You<br>";
    $message .= $FROM_USER;
    $subject = "$SITENAME Update Carbon Copy - #$ID01 - $DESC01";

    $toUsers = $ccSelected;
    //Sets up mail to use HTML formatting
    $strHeaders = "MIME-Version: 1.0\r\n";
    $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $strHeaders .= "From: " . $FROM_MAIL;

    if( mail( $toUsers, $subject, $message, $strHeaders ) ){
        
    }else{
        $handle = fopen("./mailFailures/mailErrors.csv","a+");
        fwrite($handle, "10670 - ccSelected - UpdateIssue," . $toUsers . "," . $subject . "," . substr($message, 0, 100 ) . "\n" );
        fclose($handle);
    }

}

//DI868B Start of added resource email functionality********************************************

if ($assign) {

    //get resource userInfo

    $resourceInfo = user_info_by_id ( $RSID01 );


    $encryptedPassword = base64_encode ( $resourceInfo ['PASS05'] );
    $resourceEmail = trim ( $resourceInfo ['EMAIL05'] );
    
    //Start of Temp Change for Ceva and DHL for Server Change ******************************************
    $tmpUser = strtoupper(  $resourceEmail );
    if ( ( strpos( $tmpUser, 'DHL' ) !== false ) ) {
        $tmpUrl = $mtpUrl;
        $mtpUrl = "http://sedas5.is.sandvik.com:89/production/smc/global/lps";
    }
    //End of Temp Change for Ceva and DHL for Server Change ******************************************
    

    //LP0080 - Remove ( \n\n ) from email, causes PHP error
    $message = "<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>";
    $message .= "Dear " . $resourceInfo ['NAME05'] . ",<br><br><br>";

    //EC-D0249 - Added complete logic and changed messages
    if( !isset($complete) ){
    	$message .= "You Have been resourced on an updated " . $SITENAME . " ticket<br><br>";
    	$subject = $SITENAME . " Update Assigned Resource- #" . $ID01 . "-" . $DESC01;
    }else{
    	$message .= "Your " . $SITENAME . " ticket has been complete and is waiting for you to confirm and resolve.<br><br>";
    	$subject = $SITENAME . " Ticket #" . $ID01  . " - COMPLETE and awaiting your action";
    }
    $message .= "Ticket#: " . $ID01 . "<br>";
    $message .= "Classification: " . $className . "<br>";
    $message .= "Type: " . $typeName . "<br><br><br>";
    $message .= "To directly reference the ticket click the link below:<br><br>";
    $message .= "<a href='$mtpUrl/showTicketDetails.php?ID01=$ID01&email=$resourceEmail&epass=$encryptedPassword'>View Ticket</a><br><br>";
    $message .= "Thank You<br>";
    $message .= $FROM_USER;


    //Sets up mail to use HTML formatting
    $strHeaders = "MIME-Version: 1.0\r\n";
    $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $strHeaders .= "From: " . $FROM_MAIL . "\r\n";

    
    //echo $message;
    if( $TEST_SITE == "Y" ){ //lp0088_ad
        echo "Mail sent to :".$resourceEmail;//lp0088_ad
    } else{   //lp0088_ad
        
        if( isset( $resourceEmail ) && $resourceEmail != "" ){
            if( mail( $resourceEmail, $subject, $message, $strHeaders ) ){
                
            }else{
                $handle = fopen("./mailFailures/mailErrors.csv","a+");
                fwrite($handle, "assign - UpdateIssue," . $resourceEmail . "," . $subject . "," . substr($message, 0, 100 ) . "\n" );
                fclose($handle);
            }
        }
    }
    
    //Start of Temp Change for Ceva and DHL for Server Change ******************************************
    if( isset( $tmpUrl ) ){
        $mtpUrl = $tmpUrl;
    }
    //End of Temp Change for Ceva and DHL for Server Change ******************************************
    
}
//DI868B End of added resource email functionality*************************************************


//Check Supplier Email Functionality

if( !isset( $VEND_CHK ) ){
    $VEND_CHK = "off";
}
if ($VEND_CHK == "on" && $VEND_EMAIL != "" && ($CLAS01 == 7 || $CLAS01 == 8)) {

    $typeName = get_type_name ( $TYPE01 );
    $senderInfo = user_email_name_by_id ( $_SESSION ['userID'] );

    $to_email = $VEND_EMAIL;

    if ($TYPE01 != 43 && $TYPE01 != 42) {
      if ($TYPE01 == 130){                                                      //**LP0055_KS
        $subject = "Cost and Leadtime Update";                                  //**LP0055_KS
      }                                                                         //**LP0055_KS         
      else{                                                                     //**LP0055_KS
        $subject = "$SITE_COMPANY Shipping - PO# $orderNumber";
      }                                                                         //**LP0055_KS
        $body = "Dear $VEND_CONT,<br><br><br><br>";
      
    } elseif ($TYPE01 == 43) {

        $subject = "$SITE_COMPANY Price and Availabilty - Part# $partNumber";
        $body = "Dear Dear Supplier,<br><br>";

    } elseif ($TYPE01 == 42) {

        $subject = "$SITE_COMPANY Expedite - Part# $partNumber";
        $body = "Dear $VEND_CONT,<br><br>";
    }

    $F_LETTER = substr ( $typeName, 0, 1 );
    if ($F_LETTER != "A" && $F_LETTER != "E" && $F_LETTER != "I" && $F_LETTER != "O" && $F_LETTER != "U" && $F_LETTER != "Y") {

        $body .= "We have a ";

    } else {
        $body .= "We have an ";

    }

    $body .= trim ( $typeName ) . " request, $SITENAME reference# $ID01<br><br>";

    if ($TYPE01 != "43" && $TYPE01 != 42 && $TYPE01 != 44) {

      if ($TYPE01 != 130){                                                      //**LP0055_KS
        $body .= "Order Number: $orderNumber<br>";
      }                                                                         //**LP0055_KS  
        $body .= "Part Number: $partNumber<br><br><br>";
    }
    if ($TYPE01 == 43 || $TYPE01 == 42 || $TYPE01 == 44) {

        if ($TYPE01 != 42) {
            //Retreive Supplier Information
            $supplierSql = "call " . PROGRAM_LIB . ".SUPLP01( $partNumber )";
            $supplierRes = odbc_prepare ( $conn, $supplierSql );
            odbc_execute ( $supplierRes );
            while ( $supplierRow = odbc_fetch_array ( $supplierRes ) ) {
                $supplierNumber = trim ( $supplierRow [0] );
                $supplierName = trim ( $supplierRow [2] );
                $partDescription = trim ( $supplierRow [1] );
            }

            //Get supplier price and currency code
            $supplierCostArray = get_supplier_cost ( $partNumber, $supplierNumber );
            $supplierPrice = trim ( $supplierCostArray ['price'] );
            $supplierCurr = trim ( $supplierCostArray ['currency'] );
            $supplierItemNumber = trim ( $supplierCostArray ['itemNumber'] );

        } else {
            $relatedInfo [] = "";
            $receivingInfo [] = "";
            $relatedInfo = get_po_number ( $partNumber, $orderNumber );

            if ($relatedInfo ['PO']) {
                $receivingInfo = get_receiving_info ( $partNumber, $relatedInfo ['PO'] );
            } else {
                $receivingInfo = get_receiving_info ( $partNumber, "" );
            }
            $partDescription = trim ( $receivingInfo ['PDES'] );
            $supplierItemNumber = trim ( $receivingInfo ['VCAT'] );
            $planner = trim ( $receivingInfo ['PLAN'] ) . " - " . trim ( $receivingInfo ['PLNN'] );

        }

        $body .= "Our Part Number: $partNumber<br>";
        if ($TYPE01 == 43) {

            $body .= "Price: " . $supplierPrice . $supplierCurr . "<br><br><br>";
        } else {
            $body .= "Description: " . $partDescription . "<br>";
            $body .= "Supplier Part Number: " . $supplierEmailArray ['VCAT'] . "<br>";
            $body .= "Buyer: " . $planner . "<br>";
            //$body .= "PO#: " . $supplierEmailArray['PO'] . "<br>";


            if ($drpNumber != "0") {
                //$body .= "DRP#: " . $supplierEmailArray['DRP'] . "<br>";
            }
            if ($followUpDate != "0") {
                //$body .= "Follow-up Date: " . $supplierEmailArray['FOLLOW'] . "<br>";
            }
            if ($dueDate != "0") {
                //$body .= "Due Date: " . $supplierEmailArray['DUE'] . "<br>";
            }
        }
    }
    if ($VEND_INFO) {
        $body .= "<br><br>Other Important Information:<br>";
        $body .= "$VEND_INFO<br><br><br>";
    }

    $body .= "Best Regards,<br>";
    $body .= $senderInfo ['NAME05'] . "<br><br><br>";

    $body .= "$COMPANY_NAME<br><br>";
    $body .= "This e-mail is confidential and it is intended only for the addressees. Any review, dissemination,";
    $body .= " distribution, or copying of this message by persons or entities other than the intended recipient is prohibited.";
    $body .= " If you have received this e-mail in error, kindly notify us immediately by telephone or e-mail and delete the message";
    $body .= " from your system. The sender does not accept liability for any errors or omissions in the contents of this message";
    $body .= " which may arise as a result of the e-mail transmission.";

    if ($EMAIL_ATTACH == "on") {

		//i-2623511  - Removed headers due to the use of new class function
        //$strHeaders =	"From: " . $senderInfo ['EMAIL05']  . "\r\n";
        //$strHeaders .=	"MIME-Version: 1.0\r\n"
        //       ."Content-Type: multipart/mixed; boundary=Attachment";


        $attachmentSql = "SELECT FILE07, UFILE07 FROM DSH07 WHERE KEY107='" . $ID01 . "' AND PGID07='CIL'";
        $attachRes = odbc_prepare ( $conn, $attachmentSql );
        odbc_execute ( $attachRes );


        $attachCounter = 0;

        //i-2623511 (START) - New PHPMailer class added **********************************************************************************************
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->From = $senderInfo ['EMAIL05'];
        $mail->FromName = $senderInfo ['NAME05'];
        $mail->addAddress( $to_email );     // Add a recipient
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(true);

        while ( $attachRow = odbc_fetch_array ( $attachRes ) ) {

            $attachCounter ++;

            $mail->addAttachment("../../attachments/tickets/" . trim ( $attachRow ['FILE07'] ) );         // Add attachments

        }

		if(!$mail->send()) {
			echo 'Email could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			//echo 'Message has been sent';
		}

		//i-2623511 (START) - New PHPMailer class End **********************************************************************************************


    } else {

        //Sets up mail to use HTML formatting
        $strHeaders = "MIME-Version: 1.0\r\n";
        $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $strHeaders .= "From: " . $senderInfo ['EMAIL05'];

        if( $TEST_SITE == "Y" ){ //lp0088_ad
            echo "Mail sent to :".$to_email;//lp0088_ad
        } else{  //lp0088_ad            
            if( mail( $to_email, $subject, $body, $strHeaders ) ){
                
            }else{
                $handle = fopen("./mailFailures/mailErrors.csv","a+");
                fwrite($handle, "1327 - UpdateIssue," . $to_email . "," . $subject . "," . substr($body, 0, 100 ) . "\n" );
                fclose($handle);
            }
        }

    }

    $to_email = str_replace ( "@", "�", $to_email );
    $nextVendor = get_next_unique_id ( FACSLIB, "CIL24L00", "ID24", "" );
    $vendorInsertSql = "INSERT INTO CIL24 VALUES( $nextVendor, $ID01, '$VEND_CONT', '$to_email', " . date ( 'Ymd' ) . "," . $_SESSION ['userID'];
    $vendorInsertSql .= ", '" . addslashes ( trim ( $VEND_INFO ) ) . "', $next02Id )";
    $vendRes = odbc_prepare ( $conn, $vendorInsertSql );
    odbc_execute ( $vendRes );

}

//D0249 - Added $mssg varibable to display correct action in output
//if ($STAT01 != 5) {
//    echo "<center><br><br>Issue <b>$ID01</b> Has Been $mssg<br><br>";
//} else {
    echo "<center><br><br>Issue <b>$ID01</b> Has Been $mssg<br><br></center>";
    if($reassignedFailed!='')echo "BUT for this ".$reassignedFailed." part number no price responsible person is defined <br>"; //**LP0044_AD
    //LP0017 Start ******************************
echo "<center>";
if ($STAT01 == 5) {
//D0455 - Added new section to return to myTickets if from myTickets (else condidtion)
//if( $from != "myTickets"){
    ?>
    
	<form method='post' action='surveyResponse.php'>
    <?php
//}else{
 
	//<form method='get' action='tickets.php?from=myTickets&stat=1'>	<?php
//}

    $surveyCounter = 0;
    $surveySql = "SELECT * FROM CIL41 WHERE ACTF41 = 1";
    $surveyRes = odbc_prepare ( $conn, $surveySql );
    odbc_execute ( $surveyRes );
    
    while( $surveyRow = odbc_fetch_array( $surveyRes )){
        $surveyCounter++;
        
    }
    
    if( $surveyCounter > 0 ){
        
    ?><ul>
        <li class='surveyTitle'>
        <?php echo $SURVEY_HEADING;?>
        </li>
    </ul>
    <?php 
    $surveySql2 = "SELECT * FROM CIL41 WHERE ACTF41 = 1 ORDER BY QSRT41";
    $surveyRes2 = odbc_prepare ( $conn, $surveySql2 );
    odbc_execute ( $surveyRes2 );
    while( $surveyRow2 = odbc_fetch_array( $surveyRes2 )){
    
        ?>
        <ul class='surveyList'>
            <li class='survey'>
            <?php echo $surveyRow2['QTXT41'];?>
            </li>
            <div class='radioLabel'>
            <?php 
            $answerSql = "SELECT * FROM CIL42 WHERE ACTF42 = 1 and QID42 = " . $surveyRow2['ID41'] . " ORDER BY ASRT42";
            $answerRes = odbc_prepare ( $conn, $answerSql );
            odbc_execute ( $answerRes );
            
            while( $answerRow = odbc_fetch_array( $answerRes )){
                ?>
                <div class='radioInput'>
                <input type='radio' name='q_<?php echo $surveyRow2['ID41']?>[]' value='<?php echo $answerRow['ID42'];?>' class='radio'/>
                
                <?php echo $answerRow['ATXT42'];?>
                </div>
                <?php 
            }
            ?>
        </div>
        </ul>
        <br/>
        
        <?php 
    
    }
    
    ?>
   
    <div class='addInfoStyle'>
        <?php echo trim($SURVEY_ADDITIONAL_INFO_LABEL);?>
    </div>
    <div class='addInfoStyle'><textarea rows="4" cols="75" name='addInfo' id='addInfo'></textarea></div>
    
    <?php 
    }

//}


    echo "<input type='hidden' name='CLAS09' value='$CLAS01'>";
    echo "<input type='hidden' name='type' value='$TYPE01'>";
    echo "<input type='hidden' name='PRTY01' value='$PRTY01'>";
	echo "<input type='hidden' name='from' value='$from'>";
	echo "<input type='hidden' name='ID01' value='$ID01'>";



?>
    <div class='buttonClass'>
       <br>
    <input type='hidden' name='stat' value='1'>
	<input type='submit' name='' value='Submit' class='buttonForm'>
	</form>
<?php 
}else{
    ?><div class='buttonClass'>
       <br>
      <?php 
}
?>
<!--//LP0051_AD  	<form method='get' action='tickets.php?stat=1&CLAS09=<?php echo $CLAS01;?>&type=<?php echo $TYPE01;?>&PRTY01=<?php echo $PRTY01;?>' class='buttonForm'>-->
	<form method='get' action='tickets2.php' class='buttonForm'><!--//LP0051_AD -->
	<input type='hidden' name='stat' value='1'>
	<input type='hidden' name='CLAS09' value='<?php echo $CLAS01;?>' />
    <input type='hidden' name='type' value='<?php echo $TYPE01;?>' />
    <input type='hidden' name='PRTY01' value='<?php echo $PRTY01;?>' />
	<input type='hidden' name='from' value='<?php echo $from;?>' />
	<input type='hidden' name='ID01' value='<?php echo $ID01;?>' />
	<input type='submit' name='' value='Continue' class='buttonForm'>
	</form>
	</div>
	 </center>


