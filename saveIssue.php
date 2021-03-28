<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            saveIssue.php<br>
 * Development Reference:   DI868<br>
 * Description:             saveIssue saves ticketinformation, attributes, retrieves the email notification workflow and send mail to 
 *                          correct users as well as CC users that have been selected at time of ticket entry.<br>
 *                          <b>Note: This page has many complex parts, before modification ensure that a firm understanding of the modification 
 *                          is acquired</b><br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *   DI868C     TS     15/07/2009   Added Original Owner funcitonality<br>
 *   DI932      TS     30/07/2009   Retunrs Functionality<br>
 *   D0180      TS     09/07/2010   Change to returns notification process<br>
 *   D0129      TS	   10/14/2010   Add ability to add multiple items with same supporting information<br>
 *   D0260		TS	   21/07/2011	Add Pricing field snapshot functionality<br>
 *   D0248		TS	   06/01/2012	Global Material Management Ticket Validation<br>
 *   LP0022     TS     03/05/2017   Classification change for Expedite Tickets<br>
 *   LP0027     KS     04/12/2017   LPS Unique ID creation<br>
 *   LP0032     KS     09/01/2018   Change to LPS Expedite Ticket Validation Logic- Cancelled lines
 *   LP0042     KS     29/05/2018   LPS Audit File for Ticket Ownership and Action 
 *   LP0057     TS     08/11/2018   LPS Recieving Stockroom notification fix for Classification = 7
 *   LP0061     AD     12/12/2018   Duplicated Expediting ticket 
 *   LP0055     AD     13/03/2019   GLBAU-15650_LPS Vendor Price Update_CR
 *   LP0068     AD     24/04/2019   GLBAU-16824_LPS Vendor Change
 *   LP0090     AD     24/04/2019   Uppercase Part Number Entry Fix
 */
/**
 */
global $conn, $email, $password, $SITE_TITLE, $mtpUrl, $IMG_DIR, $ALTERNATE_COLOR, $SITENAME, $class, $type, $prty, $stat;

include_once 'copysource/config.php';
include 'copysource/functions.php';
include 'copysource/validationFunctions.php';
include '../common/copysource/global_functions.php';
include 'copysource/functions/programFunctions.php';            //**LP0027
// require_once 'CW/cw.php';                                       //**LP0027

if( !$conn ){
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_connect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if ($conn ) {
} else {
    echo "Connection Failed";
}

// if( !$i_conn ){                                                  //**LP0027
//     $i_conn = i5_connect("localhost", DB_USER, DB_PASS);         //**LP0027
// }                                                                //**LP0027
// if ($i_conn ) {                                                  //**LP0027
// } else {                                                         //**LP0027
//     echo "Connection i5 Failed";                                 //**LP0027
// }                                                                //**LP0027



//Check Session emails and cookies for access to page
if ( isset( $_SESSION['email'] ) && isset( $_SESSION['password'] ) ){
    $userInfo [] = "";
    $userInfo = userInfo ( $_SESSION['email'], $_SESSION['password'] );
    $_SESSION['userID'] = $userInfo['ID05'];
    $_SESSION['name'] = $userInfo['NAME05'];
    $_SESSION['companyCode'] = $userInfo['CODE05'];
    $_SESSION['password'] = $userInfo['PASS05'];
    $_SESSION['email'] = $email;
    
    if( !isset($_COOKIE["mtp"])){
        setcookie("mtp",$_SESSION['email'],time()+60*60*24*30);
    }
    
}elseif( isset( $_SESSION['email'] ) ){

    $userInfo [] = "";
    $userInfo = user_cookie_info( $_SESSION['email'] );
    $_SESSION['userID'] = $userInfo['ID05'];
    $_SESSION['name'] = $userInfo['NAME05'];
    $_SESSION['companyCode'] = $userInfo['CODE05'];
    $_SESSION['password'] = $userInfo['PASS05'];
    $_SESSION['email'] = $_SESSION['email'];
    
}elseif ( $_COOKIE["mtp"] ){

    $userInfo [] = "";
    $userInfo = user_cookie_info( $_COOKIE["mtp"] );
    $_SESSION['userID'] = $userInfo['ID05'];
    $_SESSION['name'] = $userInfo['NAME05'];
    $_SESSION['companyCode'] = $userInfo['CODE05'];
    $_SESSION['password'] = $userInfo['PASS05'];
    $_SESSION['email'] = $_COOKIE["mtp"];
}else{
    
    error_mssg( "NONE");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
</head>
<body>
    <?

include_once 'copysource/header.php';

if ($_SESSION ['userID']) {
    
    if (!isset($_SESSION ['classArray'] )) {
        $_SESSION ['classArray'] = get_classification_array ();
    }
    if( !isset($_SESSION ['typeArray'])){
        $_SESSION ['typeArray'] = get_typeName_array ();
    }
    
    //include_once 'copysource/menu_2.php';
    include_once 'copysource/menu.php';
    //menuFrame ( $SITENAME );
}


//D0248 - Validate Global Material Management Issues before continuing to add tickets.

for($z = 1; $z <= $attributeCount; $z ++) {
	
    if( isset( ${'attribType_' . $z} ) && strtoupper(${'attribType_' . $z}) == "PART"  ){
        $submittedPartNumber = strtoupper(${trim ( strtolower ( ${'attribType_' . $z} ) ) . $z});
    }
    //*************************** LP0055_AD START ***************************************************
    if( isset( ${'attribType_' . $z} ) && strtoupper(${'attribType_' . $z}) == "SPRT"  ){
        $submittedSuppPartNumber = strtoupper(${trim ( strtolower ( ${'attribType_' . $z} ) ) . $z});
        $suppSql="SELECT ITEM01 FROM PMP01 WHERE CONO01='".$CONO."' AND VCAT01 ='".$submittedSuppPartNumber."' ORDER BY LDTE01 DESC FETCH FIRST ROW ONLY ";
        $resSuppSql = odbc_prepare ( $conn, $suppSql);
        odbc_execute ( $resSuppSql);
        $SuppSqlRow = odbc_fetch_array ( $resSuppSql);
        $submittedPartNumber = trim($SuppSqlRow['ITEM01']);
    }
    //************************** LP0055_AD END ***************************************************
    if( isset( ${'attribType_' . $z} ) && strtoupper(${'attribType_' . $z}) == "COUN"  ){
		$submittedMarketArea = ${trim ( strtolower ( ${'attribType_' . $z} ) ) . $z};
	}
	if( isset( ${'attribType_' . $z} ) && strtoupper(${'attribType_' . $z}) == "SODP"  ){
		$submittedSalesOrderNumber = ${trim ( strtolower ( ${'attribType_' . $z} ) ) . $z};
	}
	
	if( isset( ${'attribType_' . $z} ) && trim(${'attribId_' . $z}) == 382 ){
		$submittedQuantity = ${trim ( strtolower ( ${'attribType_' . $z} ) ) . $z};
	}
	
}
/*
if( $submittedSalesOrderNumber && $submittedPartNumber && $PRTY01 == 1 ){
	
	$orderPriority = validateHighPrioritySelection( $submittedSalesOrderNumber, $submittedPartNumber );
	
		?>
		<center>
			<br><br><br><br><br><br>Sorry, you can only enter an A - Unit Down - Criticality 1 ticket against an order with the criticality of 1.<br>
			<br>Please confirm order criticality and enter the ticket with the approprite Priority.<br>
		<br></center>
		<?php 
	
	die();
	
	
	
}
*/

//LP0022 - Check to see if item has been shipped or cancelled.
if( isset($TYPE01) && $TYPE01 == 42  ){
   
    $statusSql = "SELECT ORDN55, STAT55 FROM OEP55 WHERE CONO55='$CONO' AND CATN55='" . $submittedPartNumber. "' AND ORDN55='" . $submittedSalesOrderNumber. "'";
    $resStatus = odbc_prepare ( $conn, $statusSql);
        odbc_execute ( $resStatus);
       
        
        $itemOrderStatus = "X";                                                             //**LP0032                                                                                            
        while ( $statusRow = odbc_fetch_array ( $resStatus) ) {
           if ($itemOrderStatus <> ""){                                                     //**LP0032 
            $itemOrderStatus = trim($statusRow['STAT55']);
           }                                                                                //**LP0032 
        }
        
        
        if( $itemOrderStatus == "C" ){ 
        ?>
		<center>
			<br><br><br><br><br><br><b>This item on this order has already been shipped<br/>please contact your planner for further assistance.</b><br>
	
		<br></center>
		
		<?php
		  die();
        }else if( $itemOrderStatus == "X"  ){
            ?>
    		<center>
    			<br><br><br><br><br><br>This item on this order has already been cancelled, please contact your planner for further assistance.<br>
    	
    		<br></center>
    		
    		<?php
         
            die();
        }

   
    
}


if( isset($TYPE01) && ( $TYPE01 == 44 || $TYPE01 == 43 || $TYPE01 == 42 ) ){ //LP0022 - Expedite mods
	
	
    if( !isset($submittedSalesOrderNumber)){
        $submittedSalesOrderNumber = "";
    }
    
    if( !isset( $submittedPartNumber ) ){
        $submittedPartNumber = "";
    }
    
    if( !isset( $submittedMarketArea ) ){
        $submittedMarketArea = "";
    }
    
	//Validation of duplicate records
	if( $TYPE01 == 44 || $TYPE01 == 43 ){
		$duplicateRecord = getDuplicatesIds( $TYPE01, $submittedPartNumber, $submittedMarketArea, $submittedSalesOrderNumber);
	}else{
		
		$duplicateRecord = getDuplicatesIds( $TYPE01, $submittedPartNumber, $submittedMarketArea, $submittedSalesOrderNumber );
	}
	
	if( isset($duplicateRecord) && $duplicateRecord != "" && $duplicateRecord != 0 ){
		
		?>
		<center>
	<!-- LP0061 		<br><br><br><br><br><br>A duplicate issue has been entered in that past month.<br> -->
	 		<br><br><br><br><br><br>A duplicate issue has been entered.<br> <!-- LP0061 -->
	
			<br>Please reference ticket <a href='showticketDetails.php?ID01=<?php echo $duplicateRecord;?>'><?php echo $duplicateRecord;?></a><br>
		<br></center>
		<?php 
		die();
	}
	
	/**
	//Validation of Correct Exidite Date exists
	if( $TYPE01 == 42 ){
		
		for( $iCount = 0; $iCount <= 1; $iCount++ ){
			
			$validationArrayVals = checkLatestPOInfo( $submittedPartNumber, $submittedSalesOrderNumber, $iCount );
			
			if( $validationArrayVals['STOP_FLAG'] ){
				?>
				<center>
					<br/><br/><br/><br/><br/>Sorry, This ticket cannot be created.
					<br/>The receipt date, follow up date, and purchasing flag are accurate.<br/><br/>
		
					<br/><b>PO#: <?php echo $validationArrayVals['PO'];?></b><br/>
					<b>PO Flag: <?php echo $validationArrayVals['PO_FLAG'];?></b><br/>
					<b>Receipt Date#: <?php echo $validationArrayVals['RECEIPT_DATE'];?></b><br/>
					<b>Follow-Up Date#: <?php echo $validationArrayVals['FOLLOW_DATE'];?></b><br/>
				<br/></center>
				
				<?php 
				die();
			}
		
		}
		
		
		$stockAllocated = checkAllocatedStock( $submittedPartNumber, $submittedSalesOrderNumber );
		
		if( trim( $stockAllocated ) >= $submittedQuantity ){
			?>
				<center>
					<br/><br/><br/><br/><br/>Sorry, This ticket can't be created.
					<br/>Selected item has been allocated to your sales order.<br/><br/>
				<br/></center>
				
				<?php 
			
			die();
		}
		
		
		
	}
	**/
	
}



//D0129 - moved variable preperation to included page for re-usability
include 'prepareIssueInsert.php';

//Get next unique ID of main Table - CIL01
//**LP0027  $nextID = get_next_unique_id( FACSLIB, CIL01L00, ID01, "" );
$nextID = 0;                                                                   //**LP0027
list ($nextID, $lastID) = getReferenceNumbersFromFile("CIL01");                //**LP0027
if ($nextID == 0){                                                             //**LP0027
    echo "Ticket not created<br>";                                             //**LP0027
    die();                                                                     //**LP0027
}                                                                              //**LP0027

//D0129 - Moved insertsql and execution to include page for re-usability
include 'insertCil01.php';

//Get proprity description 
$priority = get_priority( $PRTY01, "short");

//Cycle through the issue attribute
for($a = 1; $a <= $attributeCount; $a++) {

    
    //DI932 - Add for Returns classification
    if( ${'attribName_' . $a} == "Stockroom returned to" ){
    
        $returnedStockroom = getReturnedStockroom( ${trim ( strtolower ( ${'attribType_' . $a} ) ) . $a} );
    }
    $attribSql = "";
    //Check to see if date attribute
    if (${'attribType_' . $a} == "DATE") {
        //date attribute need to concatonate year, month, day
        if( strlen(${'month' . $a}) == 1 ){
            $month = "0" . ${'month' . $a};
        }else{
            $month = ${'month' . $a};
        }
        if( strlen(${'day' . $a}) == 1 ){
            $day = "0" . ${'day' . $a};
        }else{
            $day = ${'day' . $a};
        }
        
        $attribValue = "";
        $attribValue = ${'year' . $a} . $month . $day;
        
    //DI932 - Added to support Returns functionality
    } elseif(${'attribType_' . $a} == "DICU" ) {
 
        $attribValue = ${trim ( strtolower ( ${'attribType_' . $a} ) ) . $a};
    
        if(${trim ( strtolower ( ${'attribType_' . $a}."b" ) ) . $a} ){
            
            //DI868J - Added functionality to store the desnNumber so it can be sent to other functions to correctly define the stockroom
            $sequenceNumber = ${trim ( strtolower ( ${'attribType_' . $a}."b" ) ) . $a};
            $attribValue .=  " " .  ${trim ( strtolower ( ${'attribType_' . $a}."b" ) ) . $a} ;
        }

    }elseif(${'attribType_' . $a} == "SODP" ) {
        
        $attribValue = ${trim ( strtolower ( ${'attribType_' . $a} ) ) . $a};
        if( isset( ${'attribType_' . $a} ) ){
            if(${trim ( strtolower ( ${'attribType_' . $a}."b" ) ) . $a} ){
                
                //DI868J - Added functionality to store the desnNumber so it can be sent to other functions to correctly define the stockroom
                $desnNumber = ${trim ( strtolower ( ${'attribType_' . $a}."b" ) ) . $a};
                $attribValue .=  " " .  ${trim ( strtolower ( ${'attribType_' . $a}."b" ) ) . $a} ;
            }
        }

        //*************************** LP0055_AD START*****************************************
    }elseif(${'attribType_' . $a} == "CURN" ) {
        
        $attribValue = ${'text' . $a};
        

        //*************************** LP0055_AD END*****************************************
        
    }else{
        
        
       
        $attribValue = "";
        $attribValue = ${trim ( strtolower ( ${'attribType_' . $a} ) ) . $a};
    }

    if( trim(${'attribType_' . $a}) == "PART" ){  //LP0090 - Fix to uppercase part numbers on entry
        
        $attribValue = strtoupper($attribValue);
    }
    
    
        if ($attribValue) {
            //create Insert sql, for attributes that do not already exist for issue
            $attribSql = "INSERT INTO CIL10 VALUES( " . get_next_unique_id ( FACSLIB, "CIL10", "LINE10", "" ) . ", $nextID, ";
            $attribSql .= ${'attribId_' . $a} . ", '$attribValue', 'DSH', 'CIL', '')";
        }
    //Check to see if sql was created
    if( $attribSql != "" ){
        //Execute attribute SQL
        if( $TEST_SITE != "Y" ){
            $attribRes = odbc_prepare ( $conn, $attribSql );
            odbc_execute ( $attribRes );
            //echo $attribSql . "<hr>";
        }else{
            echo $attribSql . "<hr>";
        }
    }
    
 //LP0055_AD   if (strtoupper(${attribType_ . $a}) == "PART" ) {
    if ((strtoupper(${'attribType_' . $a}) == "PART" )||(strtoupper(${'attribType_' . $a}) == "SPRT" )) { //LP0055_AD 
            
        $partNumber = $attribValue;
        if(strtoupper(${'attribType_' . $a}) == "SPRT" )$partNumber=$submittedPartNumber; //LP0055_AD 
        $DESC01 = $partNumber . " - " . $DESC01;
        
        $updateDescriptionSql = "UPDATE CIL01 SET DESC01='" . addslashes( $DESC01 ) . "' WHERE ID01 = $nextID";
        
        //D0260 - Start of section********************************************************************
        if( $CLAS01 == 8 || $CLAS01 == 5 ){

        	$partInfoSql = "SELECT PCLS35, PLSC35, DSSP35, PREG35, PGMJ35 FROM PARTS WHERE CONO35='DI' AND PNUM35='$partNumber'";
        	$partInfoRes = odbc_prepare( $conn, $partInfoSql );
        	
        	odbc_execute ( $partInfoRes );
     	
        	while( $partInsertRow = odbc_fetch_array( $partInfoRes ) ){
      		
        		$partInfoInsertSql = "INSERT INTO CIL33 VALUES(" . get_next_unique_id( FACSLIB, 'CIL33', 'ID33', "" )
        						   . ",$nextID, '$partNumber', '" . trim($partInsertRow['PCLS35']) . "', '" . trim($partInsertRow['PLSC35'])
        						   . "', '" . trim($partInsertRow['DSSP35']) . "', '" . trim($partInsertRow['PREG35']) . "', '" . trim($partInsertRow['PGMJ35']) . "',"
        						   . "'','','' )";

        	}
        }
        //D0260 - End of section********************************************************************
        
        
        if( $TEST_SITE != "Y" ){
            $updateDescriptionRes = odbc_prepare ( $conn, $updateDescriptionSql );
            odbc_execute ( $updateDescriptionRes );

            if( isset($partInfoInsertSql) ){												//D0260
	            $insertPartInfoRes = odbc_prepare( $conn, $partInfoInsertSql );		//D0260
	            odbc_execute( $insertPartInfoRes );									//D0260
            }
      
            

        }else{
            echo $updateDescriptionSql . "<hr>";
            
           
            if( $partInfoInsertSql ){		//D0260
            	echo $partInfoInsertSql;	//D0260
            }								//D0260
     
        }
        
    }
    if (strtoupper(${'attribType_' . $a}) == "SODP" ) {
        if( strpos($attribValue, " " ) ){
            $orderNumber = substr( $attribValue, 0, strpos( $attribValue, " " ) );
        }else{
            $orderNumber = $attribValue;
        }
    }
    if (strtoupper(${'attribType_' . $a}) == "COUN" ) {
        $marketArea = $attribValue;
    }       
    
    //D0180 - Added to define region attribute has value
    if (strtoupper(${'attribType_' . $a}) == "REGN" ) {
        $region = $attribValue;
    }    
    
    
    //echo $class . "== 7 &&" . trim(${attribId_ . $a}) . ">= 465 &&" .  trim(${attribId_ . $a}) . "<= 475";
    
    //LP0057 - Recieving Stockroom Fix - Start *******************************************
    //if( $CLAS01 == 7 && trim(${attribId_ . $a}) >= 465 && trim(${attribId_ . $a}) <= 475 ){ - Modified for LP0057
    
    if( $CLAS01 == 7 ){
       
        $recStockroomSql = "SELECT ATTR07 FROM CIL07 WHERE NAME07 = 'Receiving Stockroom'";
        $recStockroomRes = odbc_prepare ( $conn, $recStockroomSql );
        odbc_execute ( $recStockroomRes );
        
        while ( $recStockRow = odbc_fetch_array ( $recStockroomRes) ) {
            
            if( trim(${'attribId_' . $a}) == trim($recStockRow['ATTR07']) ){
                
                $receiveStockroom = $attribValue;
            
            }                                                               
        }
        
        
        //LP0057 - Recieving Stockroom Fix - End *******************************************
        
    }
    
}

$updateAttachmentsSql = "UPDATE DSH07 SET KEY107='$nextID' WHERE KEY107='$tempId'";

if( $TEST_SITE != "Y" ){
    $attachmentRes = odbc_prepare( $conn, $updateAttachmentsSql );
    odbc_execute( $attachmentRes );
}else{
    echo $updateAttachmentsSql . "<hr>";
}

//Send Carbon Copy emails
if( isset($ccSelected) && $ccSelected != "" && $ccSelected != 0 ){
    
        
        $message = "\n\n<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>";
        $message .= "Dear User,<br><br><br>";
        $message .= "You Have been Carbon Copied on a new " . $SITENAME . " ticket<br><br>";
        $message .= "Ticket#: " . $nextID . "<br>";
        $message .= "Classification: " . $className . "<br>";
        $message .= "Type: " . $typeName . "<br><br><br>";
        $message .= "To directly reference the ticket click the link below:<br><br>";
        $message .= "$mtpUrl/showTicketDetails.php?ID01=$nextID<br><br><br>";
        $message .= "Thank You<br>";
        $message .= $FROM_USER;
        $subject = "$SITENAME $priority Carbon Copy - #$nextID - $DESC01";
        
        $toUsers = $ccSelected;
        //Sets up mail to use HTML formatting
        $strHeaders = "MIME-Version: 1.0\r\n";
        $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $strHeaders .= "From: " . $FROM_MAIL;
        
        if( mail( $toUsers, $subject, $message, $strHeaders ) ){
            
        }else{
        
            $handle = fopen("./mailFailures/mailErrors.csv","a+");
            fwrite($handle, $toUsers . "," . $subject . "," . substr($message, 0, 100 ) . "\n" );
            fclose($handle);
        }
        

}


$emailArray = array();


//DI868J - Added $desnNumber parameter
//DI932 - Added $returnedStockroom parameter;
//D0180 - Added Region parameter
if( !isset( $marketArea )){
    $marketArea = "";
}
if( !isset( $receiveStockroom )){
    $receiveStockroom = "";
}
if( !isset( $returnedStockroom )){
    $returnedStockroom = "";
}
if( !isset( $region )){
    $region = "";
}
if( !isset( $orderNumber ) ){
    $orderNumber = "";
}
if( !isset( $desnNumber ) ){
    $desnNumber = "";
}
if( !isset( $partNumber ) ){
    $partNumber = "";
}


notifications( $nextID, $CLAS01, $TYPE01, $emailArray, $partNumber, $orderNumber, $marketArea , $DESC01, $priority, $desnNumber, $receiveStockroom, $returnedStockroom, $region);


$readCIL01 = "select * ";                                                                                                                   //**LP0042
$readCIL01 .= " from CIL01 ";                                                                                                               //**LP0042
$readCIL01 .= " where ID01 = " . $nextID . " ";                                                                                             //**LP0042
$cil01Res = odbc_prepare($conn, $readCIL01);                                                                                                 //**LP0042
odbc_execute($cil01Res);                                                                                                                     //**LP0042
while ($cil01Row = odbc_fetch_array($cil01Res)){                                                                                             //**LP0042
    $insertCIL01OA = "insert into CIL01OA ";                                                                                                //**LP0042
    $insertCIL01OA .= " VALUES ( " . $nextID . ", " . date('Ymd') . ", '" . date('His') . "', ";                                            //**LP0042
    $insertCIL01OA .= $_SESSION['userID'] . ", " . $cil01Row['OWNR01'] . ", 5, " . $_SESSION['userID'] . ")";                               //**LP0042
    $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                                       //**LP0042
    odbc_execute($cil01oaRes);                                                                                                               //**LP0042
}                                                                                                                                           //**LP0042

echo "<center><br><br>Issue <b>$nextID</b> Has Been Logged<br><br>";

echo "<center>";
echo "<table width=100% border=0>";
echo "<tr><td>&nbsp;</td></tr>";
echo "<tr>";
echo "<form method='get' action='tickets.php'>";
if( isset( $partNumber ) && $partNumber != "" && $CLAS01 != 17 ){
    echo "<td class='right'><input type='submit' value='Continue'></td>";
}else{
    echo "<td class='center'><input type='submit' value='Continue'></td>";
}
echo "<input type='hidden' name='CLAS09' value='$CLAS01'>";
echo "<input type='hidden' name='type' value='$TYPE01'>";
echo "<input type='hidden' name='stat' value='1'>";
echo "<input type='hidden' name='PRTY01' value='$PRTY01'>";
echo "</form>";
if( isset($partNumber) && $partNumber != "" && $CLAS01 != 17){
	echo "<form method='get' action='additionalItems.php'>";
	echo "<td><input type='submit' value='Add Additional Items'></td>";
	echo "<input type='hidden' name='ID01' value='$nextID'>";
	echo "</form>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td colspan='2' class='center'>Click Add Additional Items when you need to add additional";
	echo " Items with identical supporting information</td>";
	
}
echo "</tr>";
echo "</table>";
echo "</center>";
