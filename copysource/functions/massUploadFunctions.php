<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            massUploadFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP00029      TS    20/08/2017 Initial Dev
 *  LP0055       AD    13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0055       KS    28/03/2019 fix
 *  LP0068       AD    24/04/2019  GLBAU-16824_LPS Vendor Change
 *  LP0074       AD    19/07/2019  LPS data fields connection with S21 - upper/lower case sensitivity 
 *  LP0082       AD    18/09/2019  Amendment / enhancement to Vender change LPS ticket 
 *  LP0083       AD    03/10/2019  Enhancement to LPS ticket Supplier cost and lead time update -Currency validation  
 *
 */
/**
 */

function buildRootCauseArray( $type ){
    global $conn;
    
    $sql = "SELECT ATTR07 FROM CIL07 WHERE trim(NAME07)='Root Cause' AND TYPE07=$type";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    
    $recordCount = 0;
    while ( $row = odbc_fetch_array( $res ) ) {
        
        $attrIDSql = "SELECT ATTR07, NAME07 FROM CIL07 WHERE PRNT07=" . $row['ATTR07'];
        $attrRes = odbc_prepare ( $conn, $attrIDSql );
        odbc_execute ( $attrRes );
       
        
        $rootCause[] = "";
        while ( $attrRow = odbc_fetch_array ( $attrRes ) ) {
        
            $rootCause[trim($attrRow['NAME07'])] = trim($attrRow['ATTR07']);
            
        }
    
    }
    
    return $rootCause;
}

function buildMarketAreaArray( ){
    global $conn, $CONO;

    $sql = "SELECT PSAR15, PRMD15 FROM DESC WHERE CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY' ORDER BY PRMD15 ASC";
    $countryRes = odbc_prepare ( $conn, $sql );
    odbc_execute ( $countryRes );

    $marketArea[] = "";
    while ( $countryRow = odbc_fetch_array ( $countryRes ) ) {
        $marketArea[trim($countryRow['PSAR15'])] = trim($countryRow['PRMD15']);
   
    }

    return $marketArea;
    
}

function checkRootCause( $submittedRoot ){
    global $rootCauseArray;
    
    $valid = 0;
    foreach ( $rootCauseArray as $key => $root ){
        
        //echo $key . "-" . $root . "-" . $submittedRoot . "<hr>";
        if( trim(strtolower($key)) == trim( strtolower($submittedRoot)) ){
            $valid = 1;
            return $valid;   
        }
    }
    
    return $valid;
    
}
function checkMarketArea( $submittedMarket ){
    global $marketAreaArray;
 
    $valid = 0;
    foreach ( $marketAreaArray as $key => $root ){
        
        if( trim(strtolower($key)) == trim( strtolower($submittedMarket)) ){
            $valid = trim($key);
            return 1;
        }
        if( trim(strtolower($root)) == trim( strtolower($submittedMarket)) ){
            $valid = trim($key) ;
            return 1;
        }
      
    }
    
    return $valid;
    
}

function buildConsumptionArray( $type, $name ){
    global $conn, $CONO;
    
    $sql = "SELECT ATTR07 FROM CIL07 WHERE trim(NAME07)='${name}' AND TYPE07=$type";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array( $res ) ) {
        $attrIDSql = "SELECT ATTR07, NAME07 FROM CIL07 WHERE PRNT07=" . $row['ATTR07'];
        $attrRes = odbc_prepare ( $conn, $attrIDSql );
        odbc_execute ( $attrRes );
        
        
        $consumptionArray[] = "";
        while ( $attrRow = odbc_fetch_array ( $attrRes ) ) {
            
            $consumptionArray[trim($attrRow['ATTR07'])] = trim($attrRow['NAME07']);
            
        }
    }
    
    
    return $consumptionArray;
    
    
}

function validateConsumption( $inputConsumption, $element ){
    global $conn, $CONO, $currentConsumptionValuesArray, $potentialConsumptionValuesArray;

    if( $element == 1 ){
        $consumptionArray = $currentConsumptionValuesArray;
    }elseif ( $element == 2 ){

        $consumptionArray = $potentialConsumptionValuesArray;
    }

    
    $valid = 0;
    foreach ( $consumptionArray as $key => $conValue ){
        if( $conValue == $inputConsumption ){
            
            $conReturnValue = $key;
            return 1;
        }

    }
    
    return $valid;

}

function buildOptionsArray( $type, $name ){
    global $conn, $CONO;
    
    $sql = "SELECT ATTR07 FROM CIL07 WHERE trim(NAME07)='${name}' AND TYPE07=$type";
    
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array( $res ) ) {
        $attrIDSql = "SELECT ATTR07, NAME07 FROM CIL07 WHERE PRNT07=" . $row['ATTR07'];
        
        
        $attrRes = odbc_prepare ( $conn, $attrIDSql );
        odbc_execute ( $attrRes );
        
        
        $returnOptionsArray[] = "";
        while ( $attrRow = odbc_fetch_array ( $attrRes ) ) {
            
            $returnOptionsArray[trim($attrRow['ATTR07'])] = trim($attrRow['NAME07']);
            
        }
    }
    
 
    return $returnOptionsArray;
        
}

function validateOptions( $feedBackInput, $name ){
    global $optionsBuildArray, $sourceFeedBack;
    
    $valid = 0;
    if( $name == "options"){
        $fbArray = $optionsBuildArray;
    }elseif ($name = "source" ){
        $fbArray = $sourceFeedBack;
    }
    foreach ( $fbArray as $key => $root ){
        
        if( trim(strtolower($key)) == trim( strtolower($feedBackInput)) ){
            $valid = trim($key);
            return 1;
        }
        if( trim(strtolower($root)) == trim( strtolower($feedBackInput)) ){
            $valid = trim($key) ;
            return 1;
        }
        
    }
    
    return 0;
    
}

function missingLines( $lineData, $columnCount, $optionalFlag, $optionalColumns ){
    
    $errorColumnArray = array();
    
    for( $x = 0; $x <= $columnCount - 1; $x++ ){
        
        if( !$optionalFlag || in_array( $x, $optionalColumns ) == false ){

            if( empty(trim($lineData[$x])) && gettype($lineData[$x]) != "integer" ){
                
                array_push($errorColumnArray, $x );
            }
        }
        
    }

    return $errorColumnArray;
}

function validateLine( $class, $type, $lineData ){
    global $conn, $CONO, $rootCauseArray, $pfNum, $maNum, $pfNum2, $fbNum, $sfbNum, $stkroom, $rReason;
    
    $validResponseArray = array();
    //Only GOP validates Part & Order combination
    
    
    
    //ValidatePartNumber for all classes but GOP
    if( $class != 3 && $type!=130){
    //lp0074_ad    $validPart = validatePartNumber( $lineData[ $pfNum ] );
        $validPart = validatePartNumber( strtoupper($lineData[ $pfNum ]) ); //lp0074_ad
        
        if( $validPart == 0 ){
            array_push($validResponseArray, "Invalid Part Number");
        }
    }
    //***************************************** LP0055_AD START ***************************************************
    if( $type==130){
        //**LP0055_KS  $validPart = validateSupplierPartNumber( $lineData[ 1 ] );
        //lp0076_ad $validPart = validateSupplierPartNumber($lineData[0], $lineData[1]);        //**LP0055_KS
        $validPart = validateSupplierPartNumber(strtoupper($lineData[0]), $lineData[1]);        //lp0074_ad
        
        if( $validPart == 0 ){
            //**LP0055_KS2  array_push($validResponseArray, "Invalid Part Number");
            array_push($validResponseArray, "Item and Supplier conbination does not exist");    //**LP0055_KS2
        }
        //lp0083_ad      $validPart = validateSupplier( $lineData[ 1 ] );//LP0068_AD
        $validPart = validateSupplier( $lineData[ 0 ],$type );//LP0083_AD
        
        if( $validPart == 0 ){
            array_push($validResponseArray, "Invalid Supplier (Number)");
        }
        
    }
    //***************************************** LP0055_AD END ***************************************************
    if( $type==133){//LP0068_AD
  //lp0082_ad      $validPart = validateSupplier( $lineData[ 1 ] );//LP0068_AD
        $validPart = validateSupplier( $lineData[ 0 ],$type );//LP0082_AD
        
        if( $validPart == 0 ){//LP0068_AD
            array_push($validResponseArray, "Invalid Supplier (Number)");//LP0068_AD
        }//LP0068_AD
        
        
    }    //LP0068_AD
    if( $type == 60 ){
        
        $validPart2 = validatePartNumber( $lineData[ $pfNum2 ] );
        
        if( $validPart2 == 0 ){
            array_push($validResponseArray, "Invalid Similar Part Number");
        }
    }
    
    if( $type == 62 ){
        
        $validCurrentConsumption = validateConsumption( $lineData[1], 1 );
        $validPotentialConsumption = validateConsumption( $lineData[2], 2 );
        
        
        if( $validCurrentConsumption == 0 ){
            array_push($validResponseArray, "Invalid Current Consumption");
        }
        if( $validPotentialConsumption == 0 ){
            array_push($validResponseArray, "Invalid Potential Consumption");
        }
       
        
    }
    
    if( $fbNum != 0 ){
        
        $validFeedBackReason = validateOptions( $lineData[ $fbNum ], "options" );
        if( $validFeedBackReason == 0 ){
            array_push($validResponseArray, "Invalid Feedback");
        }  
    }
    
    if( $sfbNum != 0 ){
        
        $sValidFeedBackReason = validateOptions( $lineData[ $sfbNum ], "source" );
        if( $sValidFeedBackReason == 0 ){
            array_push($validResponseArray, "Invalid Source of Feedback");
        }
        
    }
    
    if( $stkroom != 0 ){
        
        $validStockroom = validateOptions( $lineData[ $stkroom ], "options" );
        if( $validStockroom == 0 ){
            array_push($validResponseArray, "Invalid Receiving Stockroom");
        }
    }
    
    if( $rReason != 0 ){
        
        $validrReason = validateOptions( $lineData[ $rReason ], "options" );
        if( $validrReason == 0 ){
            array_push($validResponseArray, "Invalid Reason for Request");
        }
    }
    
    
    if( $maNum != 0 ){

        $validMarketArea = checkMarketArea( $lineData[ $maNum ] );

        if( $validMarketArea == 0 ){

            array_push($validResponseArray, "Invalid Market Area");
        }
        
    }
    
    if( $class == 3 ){
        
     //lp0074_ad   $validPartOrder = validatePartOrder( $lineData[1], $lineData[0], $lineData[2], $type);
        $validPartOrder = validatePartOrder( strtoupper($lineData[1]), $lineData[0], $lineData[2], $type);//lp0074_ad 
        $validRootCause = checkRootCause( $lineData[4] );
        
        if( $validPartOrder == 0 ){
            array_push($validResponseArray, "Invalid Part or Order Number");
        }
        if( $validRootCause == 0 ){
            array_push($validResponseArray, "Invalid Root Cause");
        }

    }
    
    
return $validResponseArray;
}

function getAttributeSaveID( $type, $parent, $attrName){
    global $conn, $CONO;

    $sql = "SELECT ATTR07 FROM CIL07 WHERE trim(NAME07)='$attrName' AND TYPE07=$type AND PRNT07=$parent";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array( $res ) ) {
        $retVal = trim($row['ATTR07']);
        
    }
    
    return $retVal;
    
}

?>