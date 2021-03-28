<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            validationFunctions.php<br>
 * Development Reference:   D0248<br>
 * Description:             functions to check to see if issues are duplicated
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *   D0248		TS	   06/01/2012	Global Material Management Ticket Validation<br>
 *   LP0022     TS     03/05/2017   Classification change for Expedite Tickets 
 *   LP0061     AD     12/12/2018   Duplicated Expediting ticket 
 */
/**
 */
 
function getDuplicatesIds( $type, $partNumber, $marketArea, $salesOrder ){
	global $conn;
	
		
		if( $type == 44 ){
			
			$text = $marketArea;
			$attr = 420;
		}elseif( $type == 43 ){
			$text = $marketArea;
			$attr = 421;
		}else{
			$text = $salesOrder;
			$attr = 376;
		}
		
		if( $type != 42 ){    //LP0022 - Add Type = 42 logic
		  $checkSql = "SELECT CAID10 FROM CIL10 WHERE TEXT10='$partNumber' AND"
			      . " CAID10 IN ( SELECT CAID10 FROM CIL10 WHERE TEXT10='$text' AND ATTR10=$attr )";
		}else{
		    
		    $checkSql = "SELECT CAID10 FROM CIL10 WHERE TEXT10='$partNumber' AND"
		    . " CAID10 IN ( SELECT CAID10 FROM CIL10 WHERE substr( TEXT10,1,7) ='$text' AND ATTR10=$attr )";
		    
		 
		}
		
		
		$checkRes = odbc_prepare( $conn, $checkSql );
        	
        odbc_execute ( $checkRes );
     	
        $issueArray = array();
        while( $row = odbc_fetch_array( $checkRes ) ){
        	array_push($issueArray, $row['CAID10']);
        }
	
	
	
	//Get Date 1 month ago.
	$date = date( 'Ymd' );
	$checkDate = date ('Ymd', strtotime ( '-1 month' , strtotime ( $date ) ) );
	
	//Set up sql to check IDs in 1 call
	$idSql = "";
	$idCount = 1;
	foreach ($issueArray as $iArray) {
		
		if( $idCount == 1 ){
			$idSql = "(ID01 = $iArray";
		}else{
			$idSql .= " OR ID01 = $iArray";
		}
		
		$idCount++;
	}
	if( $idSql != "" ){
		$idSql .= ")";
		
		$checkDateSql = "SELECT ID01 FROM CIL01 WHERE $idSql AND DATE01 >= $checkDate AND TYPE01 = $type";
		if($type==42){ // LP0061_AD
		    $checkDateSql = "SELECT ID01 FROM CIL01 WHERE $idSql AND STAT01<>5 AND TYPE01 = $type";	// LP0061_AD
		} // LP0061_AD
		$checkDateRes = odbc_prepare( $conn, $checkDateSql );
        	
        odbc_execute ( $checkDateRes );
     	
    
        while( $row = odbc_fetch_array( $checkDateRes ) ){
        	$duplicatRow= $row['ID01'];
        }
	}
	if( isset( $duplicatRow ) ){
	   return $duplicatRow;
	}else{
	    return "";
	}
 
}


function checkLatestPOInfo( $partNumber, $salesOrder, $counter ){
	global $conn;
	
	$poValues =  get_po_number($partNumber, $salesOrder );
	$poNumber = $poValues ['PO'];
	
	$useDate = date( 'Ymd' );
	$useDate = convert_to_jba_date( $useDate );
	
	if( $counter == 0 ){
		$CFLG09 = 'E';
	}else{
		$CFLG09 = 'S';
	}
	$receiptDateSql = "SELECT ORDN09, RECD09, CFLG09 FROM PMP09 WHERE CONO09 = 'DI' AND CFLG09 = '$CFLG09' AND ORDN09 = '$poNumber'"
					. " AND ITEM09 = '$partNumber'"; 
	$receiptDateRes = odbc_prepare( $conn, $receiptDateSql );
    odbc_execute ( $receiptDateRes );
    
    $stopFlag = false;
    
 	$returnArrayVals['PO'] = $poNumber;
    
    while( $row = odbc_fetch_array( $receiptDateRes ) ){
    	
    	
    	$returnArrayVals['RECEIPT_DATE'] = format_JBA_Date( $row['RECD09']);
    	$returnArrayVals['PO_FLAG'] = $row['CFLG09'];
    	
    	//Check to see if Receive date is smaller than today.
    
    	if( $row['RECD09'] < $useDate ){
    		$receiveDateStopFlag = false;
    	}else{
    		$receiveDateStopFlag = true;
    	}
    	
    	
    	$followDateSql = "SELECT DTLC03 FROM PMP03 WHERE CONO03 = 'DI' AND ORDN03 = '$poNumber'"
					. " AND ITEM03 = '$partNumber'";
		$followDateRes = odbc_prepare( $conn, $followDateSql );
    	odbc_execute ( $followDateRes );
    	
    	//Get Follow-up Date
    	while( $followRow = odbc_fetch_array( $followDateRes ) ){
    		
    		if( $followRow['DTLC03'] != 9999999 ){
    			
    			$followDate = str_replace("/", "", format_JBA_Date( $followRow['DTLC03'] ));
    			$returnArrayVals['FOLLOW_DATE'] = format_JBA_Date( $followRow['DTLC03'] );
    			
    			$followUpStopFlag = false;
    		}else{
    			
    			//This means that a follow-up date has not been entered so return stop
    			$followUpStopFlag = true;
    		}
    		
    	}
	   
    	//Add 45 or 10 days to DTLC03
    	if( $counter == 0 ){
    		$checkDate = date ('Ymd', strtotime ( '+45 days' , strtotime ( $followDate ) ) );
    	}else{
    		$checkDate = date ('Ymd', strtotime ( '+10 days' , strtotime ( $followDate ) ) );
    	}
    	
    	//Check to see is today ( date('Ymd') ) is larger than Follow-ip date +45 days (checkDate)
    	if( date( 'Ymd' ) < $checkDate  ){
    		
    		$checkDateStopFlag = true;
    		
    	}else{

    		$checkDateStopFlag = false;
    	}   	
	    	
    }
    
    if( $receiveDateStopFlag && $followUpStopFlag && $checkDateStopFlag ){
    
    	$returnArrayVals['STOP_FLAG'] = true;
    }else{
    	$returnArrayVals['STOP_FLAG'] = false;
    }
   
    
    return $returnArrayVals;
    
}


function checkAllocatedStock( $part, $salesOrder ){
	global $conn;
	
	$inp57Sql = "SELECT QTDS57 FROM INP57 WHERE CONO57 = 'DI' AND ORDN57='$salesOrder' AND CATN57='$part' AND PRIN57='2'";
	
	$inp57Res = odbc_prepare( $conn, $inp57Sql );
    odbc_execute ( $inp57Res );
    
    $inp57Stock = 0;
    while( $row = odbc_fetch_array( $inp57Res ) ){
    	
    	$inp57Stock = $row['QTDS57'];
    	
    }
    
    $oep55Sql = "SELECT QTAL55 FROM OEP55 WHERE CONO55 = 'DI' AND ORDN55='$salesOrder' AND CATN55='$part'";
    
	$oep55Res = odbc_prepare( $conn, $oep55Sql );
    odbc_execute ( $oep55Res );
    
    
    $oep55Stock = 0;
    while( $oep55Row = odbc_fetch_array( $oep55Res ) ){
  
    	$oep55Stock = $oep55Row['QTAL55'];
    }
    
    $totalAllocated = $inp57Stock + $oep55Stock;
    
	return $totalAllocated;
    
}


function validateHighPrioritySelection( $orderNumber ){
	global $conn;
	
	
	$criticalitySql = "SELECT COCT40 FROM OEP40EU WHERE CONO40 = 'DI' AND ORDN40='$orderNumber'";
	$criticalityRes = odbc_prepare( $conn, $criticalitySql );
    odbc_execute ( $criticalityRes );
    
    
    while( $row = odbc_fetch_array( $criticalityRes ) ){
    	return $row['COCT40'];
    }
	
}

