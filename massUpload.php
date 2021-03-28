<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            massUpload.php<br>
 * Development Reference:   LP0025<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP00026      TS    20/08/2017 Initial Dev
 *  LP00065      AD    16/01/2019 Missing Part Information section on Child Ticket
 *  LP0055       AD    13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0055       KS    12/04/2019  fix
 *  LP0068       AD    24/04/2019  GLBAU-16824_LPS Vendor Change
 *  LP0074       AD    19/07/2019  LPS data fields connection with S21 - upper/lower case sensitivity
 *  LP0082       AD    18/09/2019  Amendment / enhancement to Vender change LPS ticket
 */
/**
 */

include 'copysource/config.php';
include 'copysource/define.php';
include 'copysource/define2.php';           //**LP0055_KS
include 'copysource/functions.php';
include 'copysource/functions/massUploadFunctions.php';
include 'copysource/validationFunctions.php';
include 'copysource/functions/programFunctions.php';
include '../common/copysource/global_functions.php';
// require_once 'CW/cw.php';
require_once 'copysource/classes/simplexlsx.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--[if IE 8]>    <html class="no-js ie8 ie" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js ie9 ie" lang="en"> <![endif]-->
<!--[if gt IE 9]><![endif]-->
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE_TITLE;?></title>
<meta charset="utf-8">

<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>

<!-- Web Font -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    
    <script>
    
$(document).ready(function(){

    $( "select[name='classification']" ).change(function () {

        var classID = $(this).val();
        var postString = "method=class_upload&id=" + classID;
        if(classID) {
            $.ajax({
                url: 'ajax_services.php',
                dataType: 'Json',
                data: postString, 
                success: function(data) {
                    $('select[name="type"]').empty();
                    $.each(data, function(key, value) {
                        $('select[name="type"]').append('<option value="'+ key +'">'+ value +'</option>');
                    });
                }
            });
    
    
        }else{
            $('select[name="type"]').empty();
        }
    });
});
</script>
</head>
<?php 
if (! $conn) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {
    
} else {
    echo "Connection Failed";
}

// if( !$i_conn ){                                                  
//     $i_conn = i5_connect("localhost", DB_USER, DB_PASS);         
// }                                                               
// if ($i_conn ) {                                                  
// } else {                                                         
//     echo "Connection i5 Failed";                                 
// }                                                                


include_once 'copysource/header.php';

if( !isset($_REQUEST['classification'] )){
    $_REQUEST['classification'] = 0;
}

if( !isset($_REQUEST['test']) ){
    $_REQUEST['test'] = "";
}

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

?>
<body>
<div id="wrapper">
	<div class="container">
		<div class="col-md-8 col-sm-8 col-xs-8">
			<div class="panel panel-default">
            	<div class="panel-heading">
                <h3 class="panel-title">Mass Upload - <font size="2">
                	<a href='uploadTemplates.php' target="_blank">Upload Templates</a> - 
                	<a href='uploadOptions.php' target="_blank">Upload Field Options</a>
                	</font></h3>

                </div>
			<div class="panel-body">
<?php 
if( !isset( $_REQUEST['continue'] ) || $_REQUEST['continue'] == "" ){
    $userArray = get_user_list ();
?>

			<form class="" id="continue_details" name="continue_details" action="massUpload.php" method="post" enctype="multipart/form-data">
    		<div class="box-padding2">
        	<div class="form-row">
        	<div class="form-row-left">
                <label>Requester:</label>
                    <select name="requester" class="form-control"> 
                        <option value="">--- Select Requester ---</option>
                        <?php
                        foreach ( $userArray as $users) {
                            ?><option <?php if( $users ['ID05'] == $_SESSION['userID'] ) echo"selected"; ?> value='<?php echo trim ( $users ['ID05'] );?>'>
                                 <?php echo trim ( $users ['NAME05'] );?></option>\n<?php 
                            }
                        ?>
    
    
                    </select>
                </div>
            
            <div class="clear"></div>
            <div class="form-row-left">
                <label>Select Priority:</label>
                    <select name="priority" class="form-control">
                        <?php priority_select_box ( "" );?>
                    </select>
                </div>
       			<div class="clear"></div>
            <div class="form-row-left">
                <label>Select Class:</label>
                    <select name="classification" class="form-control">
                        <option value="">--- Select Classification ---</option>
                        <?php
                            
                            foreach ($uploadClasses as $key => $class) {
                                ?><option <?php if($_REQUEST['classification'] == $key ) echo"selected"; ?> value='<?php echo $key;?>'>
                                 <?php echo $class;?></option>\n<?php 
                            }
                        ?>
    
    
                    </select>
                </div>
       			<div class="clear"></div>
    			<div class="form-row-left">
                      <label>Select Type:</label>
                <select name="type" id="type" class="form-control" style="width:350px">
                </select>
            	</div>
            	<div class="clear"></div>
            	<div class="form-row-left">
               		<label>Select File:</label>
      				<input type="file" name="uploadFile" id="uploadFile"/>
      			</div>
      			<div class="clear"></div>
      			<div class="form-row">
                	<label>Short Description:</label>
                    <input class="uploadText" type='text' name="shortDesc" id="shortDesc" value="" style="width:600px"/> 
                </div>
                <div class="clear"></div>
      			<div class="form-row">
                	<label>Long Description:</label>
                
                    <label style="width:600px"><textarea name='longDesc' cols='75' rows='5'></textarea></label>
                </div>
            
            <div class="clear"><br/></div>
            	<div class="clear"></div>
                        <div class="form-row">
                        <label class="hidden-xs"></label>
                        <input id="continue" name="continue" type="submit" class="login-btn next-btn" value="Continue"/>
                        <input id="test" name="test" type="hidden" value="<?php echo $_REQUEST['test'];?>"/>
                        </div>
                        <div class="clear"></div>
      			</div>
      			
    </div>
</div>
</div>
</div>
</div>
</div>
<?php 
    
    die();
}else{
    

    if( $_SESSION['userID'] == 1021 && $_REQUEST['test'] == true ){
        error_reporting(E_ALL);
        ini_set('display_errors', 1); 
    }
    
    //Column and require/optional setup
    //Note******
    // optionalColumns is zero based, starting at column "0"
    // columnCount is zero based, starting at column "0"
    // pfNum is column number of partNumber, is zero based, starting at column "0 (does not exist for GOP Classification)
    // maNum is column number of Market Area, is zero based, starting at column "0"
    // fbNum is column number of Feedback, is zero based, starting at column "0
    // sfbNum is column number of Source of Feedback, is zero based, starting at column "0
    $columnCount = 0;
    $maNum = 0;
    $pfNum = 0;
    $fbNum = 0;
    $sfbNum = 0;
    $stkroom = 0;
	$rReason = 0;
	$optionalColumns = array();
	$optionalFlag = false;
    if( $_REQUEST['classification'] == 3 ){
        $columnCount = 4;

    }elseif ($_REQUEST['classification'] == 5 ){
        
        $pfNum = 0;     // Always first column in GPA
        
        switch ( $_REQUEST['type']) {
            case '60':
                $columnCount = 6;
                $maNum = 6;
                $pfNum2 = 3;
                break;
            case '61':
                $columnCount = 8;
                $optionalFlag = true;
                $optionalColumns = array( 4 );
                $maNum = 8;
                break;
            case '62':
                $columnCount = 8;
                $maNum = 8;
                $fbNum = 7;
                break;
            case '74':
                $columnCount = 5;
                $maNum = 5;
                $fbNum = 3;
                $sfbNum = 4;
                break;
            case '75':
                $columnCount = 5;
                $optionalFlag = true;
                $optionalColumns = array( 3 );
                $fbNum = 5;
                break;
        }
            
    }elseif ($_REQUEST['classification'] == 7 ){
        
        $pfNum = 2;     // Always 3rd (2nd Zero Based) column in GPA
        $stkroom = 1;     // Always 3rd (2nd Zero Based) column in GPA
        
        switch ( $_REQUEST['type']) {
            case '31':
                $columnCount = 3;
                break;
            case '32':
                $columnCount = 3;
                break;
            case '33':
                $columnCount = 4;
                $optionalFlag = true;
                $optionalColumns = array( 4 );
                break;
        }
        
    }elseif ($_REQUEST['classification'] == 8 ){
        
        $pfNum = 0;     // Always 3rd (2nd Zero Based) column in GPA
        
        switch ( $_REQUEST['type']) {
            case '43':

                $columnCount = 3;
                $maNum = 3;
                $rReason = 2;
                break;
            case '55':
                $columnCount = 1;
                $rReason = 1;
                break;
            case '56':
                $columnCount = 0;
                break;
            case '57':
                $columnCount = 0;
                break;
            case '130'://LP0055_AD
                $columnCount = 9;//LP0055_AD
                unset($pfNum);//no part number for this type//LP0055_AD
                $optionalFlag = true;//LP0055_AD
                $optionalColumns = array( 2,3,4,5,6,7,8);      //LP0055_AD
                break;//LP0055_AD
            case '133'://LP0068_AD
                $pfNum = 1; 
                $columnCount = 14;//LP0068_AD
                $optionalFlag = true;//LP0068_AD
    //lp0082_ad            $optionalColumns = array( 2,3,7);      //LP0068_AD
                $optionalColumns =  array( 5,6,7,8,12);    //lp0082_ad  
                break;//LP0068_AD
        }
        
    }
 
    ?>
    <div class="box-padding2">
    <div class="clear"><br/></div>
		<div>      
    			<label><b>Classification:&nbsp;</b></label>
    		<label><?php echo get_class_name( $_REQUEST['classification'] );?></label>
    	</div>
    <div class="clear"></div>
        <div>
        	<label><b>Type: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></label>
        	<label><?php echo get_type_name( $_REQUEST['type'] );?></label>
        </div>

    
	<?php 
	
	   if ( $xlsx = SimpleXLSX::parse($_FILES['uploadFile']['tmp_name']) ) {
	       
	       list( $num_cols, $num_rows ) = $xlsx->dimension();
	       
	    //$handle = fopen($_FILES['uploadFile']['tmp_name'], "r");
	    
	    
	    $invalidLines = array();
	    $invalidPart = array();
	    $invalidOrder = array();
	    $invalidFeedback = array();
	    $invalidCurrentConsumption = array();
	    $invalidPotentialConsumption = array();
	    $incompleteData = array();
	    $invalidMarket = array();
	    $invalidRoot = array();
	    $successLines = array();
	    $optionsBuildArray = array();
	    $lineCount = 0;
	    $failedLineCount = 0;
	    $incompleteLineCount = 0;
	    
	   
	    //Setup Market Area build for required types
	    if( $maNum != 0  ){
	        $marketAreaArray = array();
	        $marketAreaArray = buildMarketAreaArray();
	    }
	    
	    //Set up arrays of validation data to minimize database calls
	    if( $_REQUEST['classification'] == 3){
	        $parentId = 0;
	        $rootCauseArray = array();
	        $rootCauseArray= buildRootCauseArray( $_REQUEST['type'] );
	    }elseif ($_REQUEST['classification'] == 5 ){
	        
	        
	        if( $_REQUEST['type'] == 62 ){
	            $currentConsumptionValuesArray = array();
	            $currentConsumptionValuesArray = buildConsumptionArray( 62, "Current Annual Consumption");
	            
	            $potentialConsumptionValuesArray = array();
	            $potentialConsumptionValuesArray = buildConsumptionArray( 62, "Potential Annual Consumption");

	        }
	        
	        if( $fbNum != 0  ){
	            $optionsBuildArray = array();
	            $optionsBuildArray = buildOptionsArray( $_REQUEST['type'], "Reason for Feedback");
	        }

	        if( $sfbNum != 0 ){
	            $sourceFeedBack = array();
	            $sourceFeedBack = buildOptionsArray( $_REQUEST['type'], "Source of Feedback");
	        }
	        
	    }
	    
	    if( $stkroom != 0  ){
	        $optionsBuildArray = array();
	        $optionsBuildArray = buildOptionsArray( $_REQUEST['type'], "Receiving Stockroom");
	    }
	    
	    if( $rReason != 0  ){
	        $optionsBuildArray = array();
	        $optionsBuildArray = buildOptionsArray( $_REQUEST['type'], "Reason for Request");
	    }
	    
	    $validLineCount = 0;
	    $invalidDataArray = array();
	    $missingDataArray = array();
	    $duplicateDataArray = array();
	    $dupRowCount = 0;
	    
	    foreach ( $xlsx->rows( 1 ) as $data )
	    {
	        $validLineCount++;
	        
	        
	        $emtpyFlag = false;
	        
	        if($validLineCount != 1 ){
	            
	            //Call to check if any missing required data
	            $missingColumnArray = missingLines( $data, $columnCount, $optionalFlag, $optionalColumns );
	            
	            if($_REQUEST['type'] ==133){//lp0082_ad
	                if((strtoupper( $data[4])=="YES")&&($data[5]=='' || $data[6]==''))array_push($missingColumnArray,"StockRoom missing");//lp0082_ad
	            }//lp0082_ad
	            
	            //Call to validate all data 
	            $invalidEntryArray = validateLine( $_REQUEST['classification'], $_REQUEST['type'], $data );
	           
	            
	           //Check for Missing Data entries and build array for error display 
	           if (empty($missingColumnArray)) {
	               
	               //There are no missing values
	           }else{
	               
	               $emtpyFlag = true;
	               
	               $mCols = "";
	               foreach ($missingColumnArray as $missingColumn ){
	                   $mCols .= $missingColumn . ",";
	               }
	               $mCols = substr($mCols, 0, -1 );
	               $missingDataArray[$validLineCount] = array( $mCols );

	           }

	           //Check Invalid Data entries and build array for error display 
	           if ( empty($invalidEntryArray) ) {
	            
	               //Validation of duplicate records
	               $duplicateRecord = 0;
	               if( $_REQUEST['type'] == 44 || $_REQUEST['type'] == 43 ){
	                 
	                   //Call to check to see if a duplicate entry exists
	                   
	                   $duplicateRecord = getDuplicatesIds( $_REQUEST['type'], $data[$pfNum], $data[$maNum], 0);
	                   
	              
	                   if( $duplicateRecord > 0 ){
	                    
	                       $dupRowCount++;
	                       //Add Part Number to array to display later.
	                       $duplicateDataArray[$validLineCount]['part'] = $data[$pfNum];
	                       
	                       //Add Market Area to array to display later.
	                       $duplicateDataArray[$validLineCount]['market'] = $data[$maNum];
	                       
	                       //Add existing ticket to array to display later.
	                       $duplicateDataArray[$validLineCount]['existTicket'] = $duplicateRecord;
	                       
	                   }
	               }
	               
	    
	               
	               
	           }else{
	               $inputErrorFlag = true;
	               $eVals = "";
	               foreach ($invalidEntryArray as $errorValues ){
	                   $eVals .= $errorValues . ",";
	               }
	               $eVals = substr($eVals, 0, -1 );
	               $invalidDataArray[$validLineCount] = array( $eVals );
	           }
	           
	        }else{
	         
	         
	        }
	    }
	    
	    if( $emtpyFlag == true || $inputErrorFlag == true ){
	        
	        ?>
	        <center>
	        <table width='60%'>
	        	<H3>Upload Failure</H3>
	        <?php 
    	    if( $emtpyFlag == true ){
    	        ?>
    	        <tr><td colspan='2'><hr/></td></tr>
    	        <tr><td colspan='2'><b><font size='4'>Missing Data</font></b></td></tr><?php 
    	        ?><tr>
    	        	<td><b>Error Row</b></td>
    	        	<td><b>Column(s)</b></td>
    	        </tr><?php 
    	        foreach ( $missingDataArray as $key => $missData ){
    	            
    	            ?>
    	            <tr>
    	            	<td><?php echo $key;?></td>
    	            	<td>
    	            <?php 
        	            foreach ($missingDataArray[$key] as $mCols ){
        	                echo $mCols;
        	            }
    	            ?>
    	            </td>
    	            </tr>
    	            <?php 
    	        }
    	    }
    	    
    	    if( $inputErrorFlag == true ){
    	        
    	        ?>
    	        <tr><td colspan='2'><hr/></td></tr>
    	        <tr><td colspan='2'><font size='4'><b>Invalid Data</b></font></td></tr><?php
    	        ?><tr>
    	        	<td><b>Error Row</b></td>
    	        	<td><b>Error(s)</b></td>
    	        </tr><?php 
    	        
    	        foreach ( $invalidDataArray as $key => $invalidData ){
    	            
    	            ?><tr>
    	            	<td><?php echo $key;?></td>
    	            	<td>
    	            <?php 
    	            foreach ($invalidDataArray[$key] as $errVals ){
    	                echo $errVals;
    	            }
    	            ?>
    	            </td>
    	            </tr>
    	            <?php 
    	        }
    	    }
    	    
    	    ?>
    	    
    	    <tr><td colspan='2'><hr/></td></tr>
    	    <tr><td colspan='2'>&nbsp;</td></tr>
    	    <tr><td colspan='2'>&nbsp;</td></tr>
    	    <tr><td colspan='2'><font size='4'><b>Please Correct Errors and Re-Submit</b></font></td></tr>
    	        
    	    </table>
    	    </center>
    	    <?php 
    	    die();
	    }
	    

	    if( $dupRowCount > 0 ){
	        $uploadLineCount = $num_rows - $dupRowCount;
	    }else{
	        
	       $uploadLineCount = $num_rows;
	    }
	    
	    if( $_REQUEST['classification'] == 3 ){    //GOP tickets do not need parent ID so incrament is not needed.
	        //Add line for parent
	        $uploadLineCount--;
	    }
	    
	    
	    //Get number of lines
	    //$nextID = 475218;                                                                   
	    list ($nextID, $lastID) = getReferenceNumbersFromFile("CIL01", $uploadLineCount );
	    if ($nextID == 0){                                                             
	        echo "Ticket not created<br>";                                             
	        die();                                                                     
	    }                                                                             
	    
	    $LDES01 = $_REQUEST['longDesc'];
	    $shortDescription = $_REQUEST['shortDesc'];
	    $TYPE01 = $_REQUEST['type'];
	    $CLAS01 = $_REQUEST['classification'];
	    $RQID01 = $_REQUEST['requester'];
	    $PRTY01 = $_REQUEST['priority'];
	    
	    
	    include 'prepareIssueInsert.php';
	    
	    $parentDesc = "Parent - " . $DESC01;
	    $OWNR01 = $_REQUEST['requester'];
	    $OOWN01 = $_REQUEST['requester'];
	    $RSID01 = $_REQUEST['requester'];
	    
	    //Insert Parent record
	    $parentId = $nextID;
	    
	    
	    if( !isset( $MODN01 )){
	        $MODN01 = "";
	    }
	    if( !isset( $IMPT01 )){
	        $IMPT01 = "";
	    }
	    if( !isset( $RSPN01 )){
	        $RSPN01 = "";
	    }
	    if( !isset( $SLMN01)){
	        $SLMN01 = "";
	    }
	    if( !isset( $SNAM01 )){
	        $SNAM01 = "";
	    }
	    if( !isset( $PREG01 )){
	        $PREG01 = "";
	    }
	    if( !isset( $PLSC01 )){
	        $PLSC01 = "";
	    }
	    if( !isset( $STRC01 )){
	        $STRC01 = "";
	    }
	    if( !isset( $CHCE01 )){
	        $CHCE01 = "";
	    }
	    if( !isset( $ACTN01 )){
	        $ACTN01 = "";
	    }
	    if( !isset( $CUSN01 )){
	        $CUSN01 = "";
	    }
	    if( !isset( $ELVL01)){
	        $ELVL01 = "";
	    }
	    if( !isset( $DSEQ01 )){
	        $DSEQ01 = "";
	    }
	    if( !isset( $KEY101 )){
	        $KEY101 = "";
	    }
	    if( !isset( $KEY201 )){
	        $KEY201 = "";
	    }
	    if( !isset( $KEY301)){
	        $KEY301 = "";
	    }
	    if( !isset( $KEY401 )){
	        $KEY401 = "";
	    }
	    if( !isset( $EFLA01 )){
	        $EFLA01 = "";
	    }
	    if( !isset( $ENAM01 )){
	        $ENAM01 = "";
	    }
	    if( !isset( $PCPT01 )){
	        $PCPT01 = "";
	    }
	    if( !isset( $DCPT01 )){
	        $DCPT01 = "";
	    }
	    if( !isset( $CPTI01 )){
	        $CPTI01 = "";
	    }
	    if( !isset( $UPLD01 )){
	        $UPLD01 = "";
	    }
	    if( !isset( $LDES01)){
	        $LDES01 = "";
	    }
	    if( !isset( $RQID01)){
	        $RQID01 = 0;
	    }
	    if( !isset( $PRTY01)){
	        $PRTY01 = 0;
	    }
	    if( !isset( $TYPE01)){
	        $TYPE01 = 0;
	    }
	    if( !isset( $CLAS01)){
	        $CLAS01 = 0;
	    }
	    
	    
	    //Decision was made that GOP mass uploaded tickets will not be grouped by child / parent relationship.  All GOP tickets will be handles as individual tickets
	    if( $_REQUEST['classification'] != 3 ){
	    $insertParentCIL01Sql = "";  //Initialize CIL01 SQL
	    $insertParentCIL01Sql = "INSERT INTO CIL01 VALUES( "
                        . $nextID . ",$RQID01,'$parentDesc', '$LDES01' ," . date( 'Ymd' ) . ", '" . date('His') . "', 0, '', "
                        . "$STAT01, $PRTY01, $RSID01, $TYPE01, $CDAT01, $CTIM01, $RESP01, $OCCR01, $DUED01, '$MODN01', '$UPLD01',"
                        . "$LSTP01, " . $_SESSION['companyCode'] . ", " . $_SESSION['userID'] . ", '$IMPT01', $CLAS01, '$RSPN01', "
                        . "'$SLMN01', '$SNAM01', '$PREG01','$PLSC01', '$STRC01', '$CHCE01', '$ACTN01', '$CUSN01', '$ELVL01', '$DSEQ01',"
                        . "'$KEY101', '$KEY201', '$KEY301', '$KEY401', '$EFLA01', '$ENAM01', $EMDA01, $BUYR01, $OWNR01, $POFF01, '$ESTI01',"
                        . "$EDAT01, $ESID01, $ESLV01, $PFID01, $UPID01, $OOWN01, $PCPD01,'$PCPT01', $DCPD01, '$DCPT01', $DPFL01, $CPDT01, '$CPTI01', 0, 1 )";
	    }
    
    
        //echo $insertParentCIL01Sql. "<hr>";
        $parentRes = odbc_prepare ( $conn, $insertParentCIL01Sql );
        odbc_execute ( $parentRes );
	    
	    
	    
	    //Removed the header line
	    //$headers = fgetcsv($handle, 1000, ",");
	    
	    if( $_REQUEST['classification'] == 3){
	        $parentId = 0;
	      
	    }
	    
	    foreach ( $xlsx->rows( 1 ) as $data ) 
	    {
	        $lineCount++;
	        $partNumber = "";
	        $orderNumber = "";
	        $marketArea = "";
	        $childDesc = "";
	        $desnNumber = "";
	        $receiveStockroom = "";
	        $returnedStockroom = "";
	        $region = "";
	        $OOWN01 = 0;
	        $OWNR01 = 0;
	        if($_REQUEST['type'] ==130){//lp0083_ad  auto fill currency
	            $partSql = "SELECT CURN05 FROM PLP05 WHERE CONO05='DI' AND DSEQ05='000'";//lp0083_ad
	            $partSql .= " AND (SUPN05='" . trim($data[0]) . "')";//lp0083_ad
	            $partRes = odbc_prepare ( $conn, $partSql );//lp0083_ad
	            odbc_execute ( $partRes );//lp0083_ad
	            $row= odbc_fetch_array ( $partRes );//lp0083_ad
	            $data[5]=$row['CURN05']; //lp0083_ad
	        }//lp0083_ad
	        
	        
	        //Check if line number is in duplicate line array
	        $duplicateRowExists = false;
	        
	        if( $dupRowCount > 0 ){
	            foreach ( $duplicateDataArray as $key => $dupCheck){
	                
	                if( $key == $lineCount ){
	                 
	                    $duplicateRowExists = true;
	                    
	                }
	            }
	            
	        }
	        
	        if( $lineCount != 1 && $duplicateRowExists != true ){
	        
	           //echo $lineCount . "<hr>";
	        //Set next ID
	        $nextID++;
	            
	        
	            if( $_REQUEST['classification'] == 3 ){
    	                
    	               
                    $orderNumber = $data[0];
                    $desnNumber = $data[2];
                    //lp0074_ad                     $partNumber = $data[1];
                    $partNumber = strtoupper($data[1]); //lp0074_ad 
                    
                    
                    //lp0074_ad  $childDesc = $data[1] . " - " . $DESC01;
                    $childDesc = strtoupper($data[1]) . " - " . $DESC01;//lp0074_ad
                    
                    $insertChildCIL01Sql = "";  //Initialize CIL01 SQL
                    $insertChildCIL01Sql = "INSERT INTO CIL01 VALUES( "
                        . $nextID . ",$RQID01,'$childDesc', '$LDES01' ," . date( 'Ymd' ) . ", '" . date('His') . "', 0, '', "
                    . "$STAT01, $PRTY01, $RSID01, $TYPE01, $CDAT01, $CTIM01, $RESP01, $OCCR01, $DUED01, '$MODN01', '$UPLD01',"
                    . "$LSTP01, " . $_SESSION['companyCode'] . ", " . $_SESSION['userID'] . ", '$IMPT01', $CLAS01, '$RSPN01', "
                    . "'$SLMN01', '$SNAM01', '$PREG01','$PLSC01', '$STRC01', '$CHCE01', '$ACTN01', '$CUSN01', '$ELVL01', '$DSEQ01',"
                    . "'$KEY101', '$KEY201', '$KEY301', '$KEY401', '$EFLA01', '$ENAM01', $EMDA01, $BUYR01, $OWNR01, $POFF01, '$ESTI01',"
                    . "$EDAT01, $ESID01, $ESLV01, $PFID01, $UPID01, $OOWN01, $PCPD01,'$PCPT01', $DCPD01, '$DCPT01', $DPFL01, $CPDT01, "
                    . "'$CPTI01', $parentId, 0 )";
                    
                    //echo $insertChildCIL01Sql. "<hr>";
                    $childRes = odbc_prepare ( $conn, $insertChildCIL01Sql );
                    odbc_execute ( $childRes );
                    
                    
                    if( $_REQUEST['type'] == 14 ){
                        $attrArray = $GOPShortAttribs;
                    }elseif ($_REQUEST['type'] == 19 ){
                        $attrArray = $GOPOverAttribs;
                    }elseif( $_REQUEST['type'] == 23 ){
                        $attrArray = $GOPDamagedAttribs;
                    }
                    
                    $attrCounter = 0;
                    foreach( $attrArray as $attribs ){
                        if( $attrCounter == 0 ){
                            $attribValue = $data[0] . " " . str_pad($data[2], 2, "0", STR_PAD_LEFT);
                        }elseif( $attrCounter == 1 ){
                            //lp0074_ad                         $attribValue = $data[1];
                            $attribValue = strtoupper($data[1]);   //lp0074_ad   
                        }elseif ( $attrCounter == 2){
                            $attribValue = $data[3];
                        }else{
                            $attribValue = $rootCauseArray[$data[4]];
                        }
                        
                        $insertCil10Sql = "";
                        $insertCil10Sql = "INSERT INTO CIL10 VALUES(". get_next_unique_id ( FACSLIB, "CIL10", "LINE10", "" ) . ", $nextID,"
                                        . $attribs . ", '$attribValue', 'DSH', 'CIL', '')";
                        
                        //echo $insertCil10Sql . "<hr>";
                        $childAttrRes = odbc_prepare ( $conn, $insertCil10Sql );
                        odbc_execute ( $childAttrRes );
                        
                        $attrCounter++;
                    }
                  
                    array_push( $successLines,  $nextID);
                    
                    $emailArray = array();
                    notifications( $nextID, $CLAS01, $TYPE01, $emailArray, $partNumber, $orderNumber, $marketArea , $childDesc, $PRTY01, $desnNumber, $receiveStockroom, $returnedStockroom, $region);
                    
	            }else{
	              
	                
	                //LP0074_AD             $partNumber = $data[ $pfNum];
	                if( !isset( $data[$pfNum])){
	                    $data[$pfNum] = "";
	                }
	                $partNumber = strtoupper($data[$pfNum]);  //LP0074_AD  
	                
	                if( $maNum != 0 ){
	                    $marketArea = $data[ $maNum ];
	                }

	                        if( $_REQUEST['type'] == 60 ){
	                            $attrArray = $GPASimilarAttribs;
	                            $childDesc = $data[0] . " - " . $data[3] . "-" . $DESC01;
	                        }elseif( $_REQUEST['type'] == 61 ){
	                            $attrArray = $GPACompetitorFeedAttribs;
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            
	                        }elseif( $_REQUEST['type'] == 62 ){
	                            
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            $attrArray = $GPACustomerFeedAttribs;
	                            
	                            //Consumption Attribute ID
	                            $data[1] = getAttributeSaveID( $_REQUEST['type'], $attrArray[1],$data[1]);

	                            //Potential Attribute ID
	                            $data[2] = getAttributeSaveID( $_REQUEST['type'], $attrArray[2],$data[2]);
	                            
	                            //Feedback Attribute ID
	                            $data[7] = getAttributeSaveID( $_REQUEST['type'], $attrArray[7],$data[7]);
                            

	                        }
	                        elseif( $_REQUEST['type'] == 74 ){
	                            
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            $attrArray = $GPASandvikFeedAttribs;
	                            
	                            //Feedback Attribute ID
	                            $data[3] = getAttributeSaveID( $_REQUEST['type'], $attrArray[3],$data[3]);
	                            
	                            //Source Attribute ID
	                            $data[4] = getAttributeSaveID( $_REQUEST['type'], $attrArray[4],$data[4]);
	                            
	                            
	                            
	                        }elseif( $_REQUEST['type'] == 75 ){
	                            
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            $attrArray = $GPACostCheck;
	                            
	                            //Feedback Attribute ID
	                            $data[5] = getAttributeSaveID( $_REQUEST['type'], $attrArray[5],$data[5]);
	                            
	                            
	                        }elseif( $_REQUEST['type'] == 31 || $_REQUEST['type'] == 32  || $_REQUEST['type'] == 33){
	                            
	                            $childDesc = $data[2] . "-" . $DESC01;
	                            
	                            if( $_REQUEST['type'] == 31 ){
	                               $attrArray = $INBShort;
	                            }elseif ($_REQUEST['type'] == 32 ){
	                               $attrArray = $INBOver;
	                            }else{
	                                $attrArray = $INBDamaged;
	                            }
	                            
	                   
	                            $data[1] = getAttributeSaveID( $_REQUEST['type'], $attrArray[1],$data[1]);
	                            
	                        }elseif( $_REQUEST['type'] == 43){
	                            
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            $attrArray = $GMMPA;
	                            
	                            $data[2] = getAttributeSaveID( $_REQUEST['type'], $attrArray[2],$data[2]);
	                            
	                        }elseif( $_REQUEST['type'] == 55 ){
	                            
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            $attrArray = $GMMOrigin;
	                            
	                            $data[1] = getAttributeSaveID( $_REQUEST['type'], $attrArray[1],$data[1]);
	                            
	                        }elseif( $_REQUEST['type'] == 56 ){
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            $attrArray = $GMMLongTerm;
	                            
	                        }elseif( $_REQUEST['type'] == 57 ){
	                            $childDesc = $data[0] . "-" . $DESC01;
	                            $attrArray = $GMMMSDS;
	                            
	                        }elseif( $_REQUEST['type'] == 130 ){//LP0055_AD
	                            $childDesc = $data[1] . "-" . $DESC01;//LP0055_AD
	                            $attrArray = $GMMSCLTU;//LP0055_AD
	                        
            	            }elseif( $_REQUEST['type'] == 133 ){//LP0068_AD
            	                $childDesc = $data[1] . "-" . $DESC01;//LP0068_AD
            	                $attrArray = $GMMVC;//LP0068_AD
	            }
	            
	            
	            
	            if( !isset($MODN01) ){
	                $MODN01 = "";
	            }
	            if( !isset($UPLD01) ){
	                $UPLD01 = "";
	            }
	            if( !isset($IMPT01) ){
	                $IMPT01 = "";
	            }
	            if( !isset($RSPN01) ){
	                $RSPN01 = "";
	            }
	            if( !isset($SLMN01) ){
	                $SLMN01 = "";
	            }
	            if( !isset($SNAM01) ){
	                $SNAM01 = "";
	            }
	            if( !isset($PREG01) ){
	                $PREG01 = "";
	            }
	            if( !isset($PLSC01) ){
	                $PLSC01 = "";
	            }
	            if( !isset($STRC01) ){
	                $STRC01 = "";
	            }
	            if( !isset($CHCE01) ){
	                $CHCE01 = "";
	            }
	            if( !isset($ACTN01) ){
	                $ACTN01 = "";
	            }
	            if( !isset($CUSN01) ){
	                $CUSN01 = "";
	            }
	            if( !isset($ELVL01) ){
	                $ELVL01 = "";
	            }
	            if( !isset($DSEQ01) ){
	                $DSEQ01 = "";
	            }
	            if( !isset($KEY101) ){
	                $KEY101 = "";
	            }
	            if( !isset($KEY201) ){
	                $KEY201 = "";
	            }
	            if( !isset($KEY301) ){
	                $KEY301 = "";
	            }
	            if( !isset($KEY401) ){
	                $KEY401 = "";
	            }
	            if( !isset($EFLA01) ){
	                $EFLA01 = "";
	            }
	            if( !isset($ENAM01) ){
	                $ENAM01 = "";
	            }
	            if( !isset($PCPT01) ){
	                $PCPT01 = "";
	            }
	            if( !isset($DCPT01) ){
	                $DCPT01 = "";
	            }
	            if( !isset($CPTI01) ){
	                $CPTI01 = "";
	            }

	                        $insertChildCIL01Sql = "";  //Initialize CIL01 SQL
	                        $insertChildCIL01Sql = "INSERT INTO CIL01 VALUES( "
                            . $nextID . ",$RQID01,'$childDesc', '$LDES01' ," . date( 'Ymd' ) . ", '" . date('His') . "', 0, '', "
                            . "$STAT01, $PRTY01, $RSID01, $TYPE01, $CDAT01, $CTIM01, $RESP01, $OCCR01, $DUED01, '$MODN01', '$UPLD01',"
                            . "$LSTP01, " . $_SESSION['companyCode'] . ", " . $_SESSION['userID'] . ", '$IMPT01', $CLAS01, '$RSPN01', "
                            . "'$SLMN01', '$SNAM01', '$PREG01','$PLSC01', '$STRC01', '$CHCE01', '$ACTN01', '$CUSN01', '$ELVL01', '$DSEQ01',"
                            . "'$KEY101', '$KEY201', '$KEY301', '$KEY401', '$EFLA01', '$ENAM01', $EMDA01, $BUYR01, $OWNR01, $POFF01, '$ESTI01',"
                            . "$EDAT01, $ESID01, $ESLV01, $PFID01, $UPID01, $OOWN01, $PCPD01,'$PCPT01', $DCPD01, '$DCPT01', $DPFL01, $CPDT01, "
                            . "'$CPTI01', $parentId, 0 )";
                            
                            //echo $insertChildCIL01Sql. "<hr>";
                            $childRes = odbc_prepare ( $conn, $insertChildCIL01Sql );

                            if( odbc_execute ( $childRes ) ){
                                
                            }else{
                                $handle = fopen("./sqlFailures/sqlFails.csv","a+");
                                fwrite($handle, "893 - massUpload.php," . $insertChildCIL01Sql . "\n" );
                                fclose($handle);
                            }
                            
	                        $attrCounter = 0;
	                        $lastAtrib=0;
	                        foreach( $attrArray as $attribs ){
	                            $lastAtrib=$attribs;//LP0055_AD2
	                            $attribValue = $data[$attrCounter];
	                            if($attrCounter==$pfNum)$attribValue=strtoupper($attribValue);  //lp0074_AD
	                            if( $_REQUEST['type'] == 133){//vendor change //lp0082_AD
	                                if($attrCounter==2){//liability //lp0082_AD
	                                    if(strtoupper($attribValue)=="YES")$attribValue=$attribs+1; //lp0082_AD
	                                    else $attribValue=$attribs+2; //lp0082_AD
	                                } //lp0082_AD
	                                if($attrCounter==4){//vdsr //lp0082_AD
	                                    if(strtoupper($attribValue)=="YES")$attribValue=$attribs+1; //lp0082_AD
	                                    else $attribValue=$attribs+2; //lp0082_AD
	                                } //lp0082_AD
	                                if($attrCounter==5){//stockfrom //lp0082_AD
	                                    $sql="SELECT NAME07,ATTR07 FROM cil07 WHERE PRNT07=".$attribs;//lp0082_AD
	                                    $res = odbc_prepare ( $conn, $sql );//lp0082_AD
	                                    odbc_execute ( $res );//lp0082_AD
	                                    while ( $attributeValueRow = odbc_fetch_array ( $res ) ) {//lp0082_AD
	                                        if (substr($attributeValueRow['NAME07'],0,2)==substr($attribValue,0,2)){//lp0082_AD
	                                            $attribValue=$attributeValueRow['ATTR07'];//lp0082_AD
	                                            break;//lp0082_AD
	                                        }//lp0082_AD
	                                        //echo $attributeValueRow['NAME07'],"-",$attribValue,"<br>";//lp0082_AD
	                                    }//lp0082_AD
	                                } //lp0082_AD
	                                if($attrCounter==6){//stockfrom //lp0082_AD
	                                    $sql="SELECT NAME07,ATTR07 FROM cil07 WHERE PRNT07=".$attribs;//lp0082_AD
	                                    $res = odbc_prepare ( $conn, $sql );//lp0082_AD
	                                    odbc_execute ( $res );//lp0082_AD
	                                    while ( $attributeValueRow = odbc_fetch_array ( $res ) ) {//lp0082_AD
	                                        if (substr($attributeValueRow['NAME07'],0,2)==substr($attribValue,0,2)){//lp0082_AD
	                                            $attribValue=$attributeValueRow['ATTR07'];//lp0082_AD
	                                            break;//lp0082_AD
	                                        }//lp0082_AD
	                                        //echo $attributeValueRow['NAME07'],"-",$attribValue,"<br>";//lp0082_AD
	                                    }//lp0082_AD
	                                } //lp0082_AD
	                            } //lp0082_AD
	                            $insertCil10Sql = "";
	                            $insertCil10Sql = "INSERT INTO CIL10 VALUES(". get_next_unique_id ( FACSLIB, "CIL10", "LINE10", "" ) . ", $nextID,"
	                            . $attribs . ", '$attribValue', 'DSH', 'CIL', '')";
	                            
	                            //echo $insertCil10Sql . "<hr>";
	                            $childAttrRes = odbc_prepare ( $conn, $insertCil10Sql );
	                           odbc_execute ( $childAttrRes );
	                            $attrCounter++;
	                        }
	                           
            //*************************************************** LPS0065_AD START *************************************************************************

	                        if(  $_REQUEST['classification'] == 8 ||  $_REQUEST['classification'] == 5 ){
	        //*************************************************** LPS0055_AD START *************************************************************************
	                            if(  $_REQUEST['type'] == 130){//LP0055_AD2
	                        /*//LP0055_AD2        $submittedSuppPartNumber=$data[1];
	                            $suppSql="SELECT ITEM01 FROM PMP01 WHERE CONO01='DI' AND VCAT01 ='".$submittedSuppPartNumber."' ORDER BY LDTE01 DESC FETCH FIRST ROW ONLY ";
	                            $resSuppSql = odbc_prepare ( $conn, $suppSql);
	                            odbc_execute ( $resSuppSql);
	                            $SuppSqlRow = odbc_fetch_array ( $resSuppSql);
	                            $partNumber = trim($SuppSqlRow['ITEM01']);
	                        */    //LP0055_AD2
      //LP0074_AD	                                $partNumber=trim($data[1]);//LP0055_AD2
	                                $partNumber=strtoupper(trim($data[1])); //LP0074_AD	                                
	                                $attribValue = $partNumber;//LP0055_AD2
	                                $attribs=$lastAtrib+1;//LP0055_AD2
	                                $insertCil10Sql = "";//LP0055_AD2
	                                $insertCil10Sql = "INSERT INTO CIL10 VALUES(". get_next_unique_id ( FACSLIB, "CIL10", "LINE10", "" ) . ", $nextID,"
	                                . $attribs . ", '$attribValue', 'DSH', 'CIL', '')";//LP0055_AD2
	                                
	                                //echo $insertCil10Sql . "<hr>";
	                                $childAttrRes = odbc_prepare ( $conn, $insertCil10Sql );//LP0055_AD2
	                                odbc_execute ( $childAttrRes );//LP0055_AD2
	                            }//LP0055_AD2
	        //*************************************************** LPS0055_AD END *************************************************************************
	                            $partInfoSql = "SELECT PCLS35, PLSC35, DSSP35, PREG35, PGMJ35 FROM PARTS WHERE CONO35='DI' AND PNUM35='$partNumber'";
	                                   $partInfoRes = odbc_prepare( $conn, $partInfoSql );
	                                   
	                                   odbc_execute ( $partInfoRes );
	                                   
	                                   while( $partInsertRow = odbc_fetch_array( $partInfoRes ) ){
	                                       
	                                       $partInfoInsertSql = "INSERT INTO CIL33 VALUES(" . get_next_unique_id( FACSLIB, 'CIL33', 'ID33', "" )
	                                       . ",$nextID, '$partNumber', '" . trim($partInsertRow['PCLS35']) . "', '" . trim($partInsertRow['PLSC35'])
	                                       . "', '" . trim($partInsertRow['DSSP35']) . "', '" . trim($partInsertRow['PREG35']) . "', '" . trim($partInsertRow['PGMJ35']) . "',"
	                           . "'','','' )";
	                                      $insertPartInfoRes = odbc_prepare( $conn, $partInfoInsertSql );		//D0260
	                                      odbc_execute( $insertPartInfoRes );		
	                                   }
	                              
	                               }
	                           
            //*************************************************** LPS0065_AD END *************************************************************************
	                           
	                        
	                        array_push( $successLines,  $nextID);
	                        
	                        $emailArray = array();
	                        notifications( $nextID, $CLAS01, $TYPE01, $emailArray, $partNumber, $orderNumber, $marketArea , $childDesc, $PRTY01, $desnNumber, $receiveStockroom, $returnedStockroom, $region);
	                        

	            }
	            
	        }
	    }
	    
	   }
	    //fclose($handle);
	    
	    ?>
	    
	   <center>
	        <table width='60%'>
	        	<H3>Upload Successful</H3>
    	        <tr><td><hr/></td></tr>
    	    <?php 
    	    if( $_REQUEST['classification'] != 3 ){
        	    ?>
        	    <tr><td align='center'><b><font size='4'>Parent Ticket ID</font></b></td></tr>
        	    <tr><td align='center'><b><?php echo $parentId;?></b></td></tr>
        	    <tr><td>&nbsp;</td></tr>
        	    <tr><td><hr/></td></tr>
        	    <tr><td>&nbsp;</td></tr>
        	    <tr><td align='center'><b><font size='3'>Children</font></b></td></tr>
        	    
        	    <?php 
        	    if( $dupRowCount > 0 ){
        	        
        	        ?>
        	        <tr><td><b>NOTICE: The following duplicat tickets already exist.</b></td></tr>
        	        <tr>
        	        	<td colspan='6'>
        	        	<table width='100%'>
        	        	<tr>
        	        		<td>Line</td>
        	        		<td>Part Number</td>
        	        		<td>Market Area</td>
        	        		<td>Existing Ticket</td>
        	        	</tr>
        	        <?php 
        	        
        	        foreach ( $duplicateDataArray as $key => $duplicate ){
        	            ?>
        	            <tr>
        	            	<td><?php echo $key;?></td>
        	            	<td><?php echo $duplicate['part'];?></td>
        	            	<td><?php echo $duplicate['market'];?></td>
        	            	<td><a href='showticketDetails.php?ID01=<?php echo $duplicate['existTicket'];?>' target='_blank'><?php echo $duplicate['existTicket'];?></td>
        	            </tr>
        	            <?php   
        	        }
        	        ?>
        	        </table></td></tr>
        	        <?php 
        	        
        	    }
    			
    			 
    	    }else{
    	      
    	    }
    	    ?>
    	    
    	    <table width='60%'>
    	    <?php 
    	    if( $_REQUEST['classification'] != 3 ){
    	        ?><tr><td><b><br/></b></td></tr>
    	       	<tr><td><b><br/></b></td></tr><?php
    	       ?><tr><td><b>Children Tickets Created.</b></td></tr><?php 
    	       
    	    }
    	    ?>
    	    
    	    <tr>
    	    <?php 
    	    $successCounter = 0;
    	    foreach ( $successLines as $success ){
    	        
    	        $successCounter++;
    	        if( $successCounter % 10 == 0 ){
    	            ?>
    	            </tr><tr>
    	            <?php 
    	        }
    	        
    	        ?><td><?php echo $success;?></td><?php 
    	    }
    	    ?>
    	    </tr>
    	     </table>
			
<?php }?>
<div class="clear"><br/></div>
<div class="clear"><br/></div>
<form action="tickets2.php" method='post'>
	<div class="clear"></div>
    <div class="form-row">
    <label class="hidden-xs"></label>
    <input id="continue" name="continue" type="submit" class="login-btn next-btn" value="Continue"/>
    </div>
    <div class="clear"></div>
</form>

</body>
</html>



