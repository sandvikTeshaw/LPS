<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            editResponsibilities.php<br>
 * Development Reference:   DI868<br>
 * Description:             LPS Escalation application Escalates tickets at specific intervals <br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  D0097		TS	  19/04/2010  re-write of escalation process<br>
 *  LP0042      KS    15/05/2018  LPS Audit File for Ticket Ownership and Action 
 *  LP0050      KS    01/08/2018  Create new LPS ticket type “Inbound Parts Not Assembled”
 * 
 */
/**
 */

global $conn, $SITENAME, $updateCIL01Sql, $strHeaders, $message, $updateCIL01_03Sql;



include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

$strHeaders = "MIME-Version: 1.0\r\n";
$strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$strHeaders .= "From: " . $FROM_MAIL;

$sendMailFlag = true;
$saveInserts = true;

set_time_limit ( 600 );

if (!isset($conn)) {

	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
    echo "Connection Failed";
}
//Setup escalation array variables to define time and priority
$escalationVars = array (array (8, 1 ), array (24, 2 ), array (48, 3 ), array (72, 4 ) );
array_push($escalationVars, array (240, 5));                                                         //**LP0050

//Setup arrays to hold the different escalation levels
$reminderEscalation = array ();
$esLevelEscalation = array ();


//Create an array of escalation levels, 10 is the max... this should never be exceeded
for( $e = 0; $e <= 10; $e++ ){
	${'eslv_' . $e} = array();
}


//Get attribute arrays so that we do not have to be retrieved for each call
$typesArray = types_array ();
$classArray = class_array ();

//Get list of users information in form $userInfoArray[ID]['name'] - $userInfoArray[ID]['email']

$userInfoArray = get_user_info_array_by_id_include_del ();


//Iterate through each priority
foreach ( $escalationVars as $eVars ) {
    
    //Sets the escalation date and time based on the $escalationVars array variables 
    $escaltationDateTime = get_escalation_date_time ( $eVars [0] );
    
    //Gets day before today and sets to yesterday
    $dayBefore = get_yesterday ( $escaltationDateTime ['date'] );
    
    //This sets yesterday
    $yesterday = strftime ( "%Y", $dayBefore ) . strftime ( "%m", $dayBefore ) . strftime ( "%d", $dayBefore );

    $date = $escaltationDateTime ['date'];
    $time = $escaltationDateTime ['time'];
    $prty = $eVars [1];
    
    //Call procedure to retrieve correct records based on EDAT01 (Escalation Date), ESTI01 (Escalation Time), PRTY01 (Priority)
    $priorityASql = "call " . PROGRAM_LIB . ".CIL01P08( $date, '$time', $prty, $yesterday )";
    
    $priorityARes = odbc_prepare ( $conn, $priorityASql );
    odbc_execute ( $priorityARes );

    while ( ($aRow = odbc_fetch_array ( $priorityARes )) != false ) {
        
        //Set time limit as this will take time to run and needs timeout extension to ensure completion of script
        set_time_limit ( 300 );
        
        if (trim($aRow ['CLAS01']) == 5 || trim($aRow ['CLAS01']) == 9 ) {
            //Do nothing here as this classification should not be included in the escalation
        } else {
            
            if( $aRow ['ESLV01'] == 0 ) {

                    //Check to see if it has been updated, if not then allow buffer period
                    //Also check to see if Priority A issue, if so then no buffer will be alloted
                    
                	//    UDAT01             PRTY01 
                    if ($aRow ['UDAT01'] == 0 && $aRow ['PRTY01'] != 1) {
                        
                        //Gets ticket creation date information
                        $creationYear = substr ( $aRow ['DATE01'], 0, 4 );
                        $creationMonth = substr ( $aRow ['DATE01'], 4, 2 );
                        $creationDay = substr ( $aRow ['DATE01'], 6, 2 );
                        $creationHour = substr ( $aRow ['TIME01'], 0, 2 );
                        $creationMinute = substr ( $aRow ['TIME01'], 2, 2 );
                        $creationSecond = substr ( $aRow ['TIME01'], 4, 2 );
                        
                        //Converts creation date and time into workable time
                        $creationWorkTime = mktime ( $creationHour, $creationMinute, $creationSecond, $creationMonth, $creationDay, $creationYear );
                        
                        //24 hour buffer period to allow users opportunity to respond to issue
                        $escalationBuffer = get_escalation_date_time ( 24 );
                        $escalationYear = substr ( $escalationBuffer ['date'], 0, 4 );
                        $escalationMonth = substr ( $escalationBuffer ['date'], 4, 2 );
                        $escalationDay = substr ( $escalationBuffer ['date'], 6, 2 );
                        $escalationHour = substr ( $escalationBuffer ['time'], 0, 2 );
                        $escalationMinute = substr ( $escalationBuffer ['time'], 2, 2 );
                        $escalationSecond = substr ( $escalationBuffer ['time'], 4, 2 );
                        
                        $escalationWorkTime = mktime ( $escalationHour, $escalationMinute, $escalationSecond, $escalationMonth, $escalationDay, $escalationYear );
                        
                        //Check to see if this is still in the buffer period
                        if ($creationWorkTime <= $escalationWorkTime) {                 
                            $firstStop = false;
                        } else {
                            $firstStop = true;
                        }
                    
                    }
                    if (!isset($firstStop) || $firstStop != true) {
                    	//Set reminders in reminder escalation array
                        array_push ( $reminderEscalation, $aRow );
                    }
            
            }else{ 
            	//Set escalations in escalation array to iterate through later in the application, this will provide optimized response
            	array_push ( ${'eslv_' . $aRow['ESLV01']} , $aRow );
            }
        }
    }
}

//**********************************  Start of Reminders Section ***************************************************************
//Itereate throught the reminder array and set up updates and emails
$cil02InsertArray = array();
foreach ( $reminderEscalation as $reminders ) {
    set_time_limit ( 300 );
    //Get priority Description
    $priority = get_priority ( $reminders ['PRTY01'], "short" );
    
    //Reset email vars
    $toEmail = "";
    $toName = "";
    
    if ($reminders ['CLAS01'] == 7) {
        //POFF01
        $toEmail = $userInfoArray [$reminders ['POFF01']] ['email'];
        $toName = $userInfoArray [$reminders ['POFF01']] ['name'];
        $toID = $reminders ['POFF01'];                                                                                                                          //**LP0042
    } else {
        //OWNR01
        $toEmail = $userInfoArray [$reminders ['OWNR01']] ['email'];
        $toName = $userInfoArray [$reminders ['OWNR01']] ['name'];
        $toID = $reminders ['OWNR01'];                                                                                                                          //**LP0042
    }
    
    $subject = "Reminder " . $priority . " " . trim ( $SITENAME ) . " #" . trim ( $reminders ['ID01'] ) . " " . $classArray [$reminders ['CLAS01']] . " - " . $typesArray [$reminders ['TYPE01']];
    
    //Send reminder to owner
    send_mail ( $toName, $toEmail, trim ( $reminders ['ID01'] ), $classArray [$reminders ['CLAS01']], $typesArray [$reminders ['TYPE01']], trim ( $reminders ['DESC01'] ), "", $sendMailFlag, $priority, $subject );
    
    //Concatonate IDs so that we can run a single update query  instead of multiples
    if ($updateCIL01Sql) {
        $updateCIL01Sql .= " OR ID01=" . trim ( $reminders ['ID01'] );
    } else {
        $updateCIL01Sql = " ID01=" . trim ( $reminders ['ID01'] );
    }
    if ($sendMailFlag == true) {
        mail ( $toEmail, $subject, $message, $strHeaders );
    }
   
    $insertCIL01EA = "insert into CIL01EA ";                                                                                                                    //**LP0042
    $insertCIL01EA .= " values(" . $reminders['ID01'] . ", " . date('Ymd') . ", '" . date('His') . "', 1, " . $toID . ", " . $reminders['OWNR01'] . " ";        //**LP0042
    $insertCIL01EA .= " ) ";                                                                                                                                    //**LP0042
    if ($saveInserts == true){                                                                                                                                  //**LP0042
        $cil01eaRes = odbc_prepare($conn, $insertCIL01EA);                                                                                                       //**LP0042
        odbc_execute($cil01eaRes);                                                                                                                               //**LP0042
    }                                                                                                                                                           //**LP0042
    
     //Add ID and comment to CIL02 insert array
     array_push( $cil02InsertArray, array( trim ( $reminders ['ID01'] ), "Reminder Sent to Owner "  . trim ( $toName )));
   
    //Check to see if Global Order Processing Issue
    if ($reminders ['CLAS01'] == 3) {
        //Check to see if PFC has responded to the issue
        if ($reminders ['KEY101'] != "Y" && $reminders ['KEY101'] != "N" && $reminders ['PFID01'] == 0) {
            //Reset email vars
            $toEmail = "";
            $toName = "";
            
            //PFID01
            if( isset( $userInfoArray [$reminders ['PFID01']] ['email']) && isset($userInfoArray [$reminders ['PFID01']] ['name'])) {
                $toEmail = $userInfoArray [$reminders ['PFID01']] ['email'];
                $toName = $userInfoArray [$reminders ['PFID01']] ['name'];
            }
            
            $subject = "Reminder " . $priority . " " . trim ( $SITENAME ) . " #" . trim ( $reminders ['ID01'] ) . " " . $classArray [$reminders ['CLAS01']] . " - " . $typesArray [$reminders ['TYPE01']];

            //Send Reminder to PFC
            send_mail ( $toName, $toEmail, trim ( $reminders ['ID01'] ), $classArray [$reminders ['CLAS01']], $typesArray [$reminders ['TYPE01']], trim ( $reminders ['DESC01'] ), "", $sendMailFlag, $priority, $subject );
            
            //Add ID and comment to CIL02 insert array
            array_push( $cil02InsertArray, array( trim ( $reminders ['ID01'] ), "Reminder Sent to PFC "  . trim ( $toName )) );
            
        }
    }
}

//Update query for CIL01, set ESLV01(Escalation level) to 1, this will allow for all reminders to be updated in CIL01 in 1 shot
if ($updateCIL01Sql) {
            
    $updateCIL01Sql = "UPDATE CIL01 SET ESLV01=1, EDAT01=" . date ( 'Ymd' ) . ", ESTI01='" . date ( 'His' ) . "' WHERE " . $updateCIL01Sql;
    if ($saveInserts == true) {
        $cil01Res = odbc_prepare ( $conn, $updateCIL01Sql );
        odbc_execute ( $cil01Res );
    } else {
        echo "<font color='#00FF00'>" . $updateCIL01Sql . "</font><hr>";
    }
    
    //Clear insert statement
    $updateCIL01Sql = "";
}

	//Initialize CIL02 max value for inserts
	$nextCIL02 = get_next_unique_id ( FACSLIB, "CIL02V01", "MAXID", "" ) - 1;
	
	//Set insert date and time
	$iDate = date ( 'Ymd' );
	$iTime = date ( 'H:i:s' );
	

//**********************************  End of Reminders Section ***************************************************************

//**********************************  Start of Escalation Section ***************************************************************
	//Walk through each escalation level
	for( $es = 1; $es <= 9; $es++ ){

		//Walk through each escalation level array
		foreach ( ${'eslv_'.$es} as $esLevel ) {
		    
			$escalate = true;
			
		    //Get priority description for mail
		    $priority = get_priority ( $esLevel ['PRTY01'], "short" );
		  
		    //Initialize the Supervisor ID to the current user, this will allow looping for supervisor to function as required
		    if( isset( $userInfoArray [$esLevel ['OWNR01']] ['id'] ) ){
		      $superId = $userInfoArray [$esLevel ['OWNR01']] ['id'];
		    }else{
		        $superId = 0;
		    }
		   
		    //Walk through levels of escalation to get correct supervisor
			for( $z = 1; $z<= $es; $z++ ){
				
			    if( ( isset($superId) || !isset($superId)) && isset($userInfoArray [$superId] ['super']) && $superId != $userInfoArray [$superId] ['super'] ){
					$superId = $userInfoArray [$superId] ['super'];
				}else{
					$escalate = false;
					break;
				}
			}
			
			//If there is a supervisor to escalate the issue to for the next level then do so, if not send a reminder to the current level supervisor
			if( $escalate == true ){
				
			    //Add Current ticket owner to email to inform them of the escalation to their supervisor
			    $ccMail = $userInfoArray [$esLevel ['OWNR01']] ['email'];
			    
			    //Set toName to supervisor who should recieve the escalation email. 
			    $toName = trim($userInfoArray [$superId] ['name']);
		
			    //Set toEmail to supervisor who should recieve the escalation email. 
			    $toEmail = trim($userInfoArray [$superId]['email']);
			    
			    if( $toName != "" ){
				    //Add ID and comment to CIL02 insert array
	     			array_push( $cil02InsertArray, array( trim ( $esLevel ['ID01'] ), "Issue Escalated to "  . trim ( $userInfoArray [$superId] ['name'] )));
	     			
	     			//Create subject line for mail to Supervisor
				    $subject = "Escalation " . $priority . " " . trim ( $SITENAME ) . " #" . trim ( $esLevel ['ID01'] ) . " " . $classArray [$esLevel ['CLAS01']] . " - " . $typesArray [$esLevel ['TYPE01']];
				    
				    //Call mail function to send mail
				    if ($toEmail) {
				        send_mail ( $toName, $toEmail, trim ( $esLevel ['ID01'] ), $classArray [$esLevel ['CLAS01']], $typesArray [$esLevel ['TYPE01']], trim ( $esLevel ['DESC01'] ), $ccMail, $sendMailFlag, $priority, $subject );
				    
				    }
			    }
			    
			 		//Concatonate IDs so that we can run a single update query  instead of multiples
				    if ($updateCIL01Sql) {
				        $updateCIL01Sql .= " OR ID01=" . trim ( $esLevel ['ID01'] );
				    } else {
				        $updateCIL01Sql = " ID01=" . trim ( $esLevel ['ID01'] );
				    }
			    
				    $insertCIL01EA = "insert into CIL01EA ";                                                                                                                //**LP0042
				    $insertCIL01EA .= " values(" . $esLevel['ID01'] . ", " . date('Ymd') . ", '" . date('His') . "', 2, " . $superId . ", " . $esLevel['OWNR01'] . " ";     //**LP0042
				    $insertCIL01EA .= " ) ";                                                                                                                                //**LP0042
				    if ($saveInserts == true){                                                                                                                              //**LP0042
				        $cil01eaRes = odbc_prepare($conn, $insertCIL01EA);                                                                                                   //**LP0042
				        odbc_execute($cil01eaRes);                                                                                                                           //**LP0042
				    }                                                                                                                                                       //**LP0042
				    
				    
			}else{
				
				//Add Current ticket owner to email to inform them of the escalation to their supervisor
			    if( isset( $userInfoArray [$esLevel ['OWNR01']] ['email']) ){
			     $ccMail = $userInfoArray [$esLevel ['OWNR01']] ['email'];
			    }else{
			        $ccMail = "";
			    }
			    
			    //Add super name and email to correct vars
			    if( isset( $userInfoArray [$superId] ['name'] )){
				    $toName = $userInfoArray [$superId] ['name'];
			    }else{
			        $toName = "";
			    }
			    
			    if( isset( $userInfoArray [$superId] ['email'] )){
				    $toEmail = $userInfoArray [$superId] ['email'];
			    }else{
			        $toEmail = "";
			    }
				
				if( $toName != "" ){
					/*
					array_push( $cil02InsertArray, array( trim ( $esLevel ['ID01'] ), "Reminder of Escalated issue sent to "  . trim ( $userInfoArray [$superId] ['name'] )));
					
					//Create subject line for mail to Supervisor
					$subject = "Reminder of Escalated Issue " . $priority . " " . trim ( $SITENAME ) . " #" . trim ( $esLevel ['ID01'] ) . " " . $classArray [$esLevel ['CLAS01']] . " - " . $typesArray [$esLevel ['TYPE01']];
				    
					//Call mail function to send mail
					if ($toEmail) {
				        send_mail ( $toName, $toEmail, trim ( $esLevel ['ID01'] ), $classArray [$esLevel ['CLAS01']], $typesArray [$esLevel ['TYPE01']], trim ( $esLevel ['DESC01'] ), $ccMail, $sendMailFlag, $priority, $subject );
				    
				    }
				    
					//Concatonate IDs so that we can run a single update query  instead of multiples
				    if ($resetCIL01Sql) {
				        $resetCIL01Sql .= " OR ID01=" . trim ( $esLevel ['ID01'] );
				    } else {
				        $resetCIL01Sql = " ID01=" . trim ( $esLevel ['ID01'] );
				    }
					*/
				}
			}
		
		}
		//Update query for CIL01, set ESLV01(Escalation level) to next escalation level
		if ($updateCIL01Sql) {
		    $esVal = $es + 1;
		    		    
		    $updateCIL01Sql = "UPDATE CIL01 SET ESLV01=$esVal, EDAT01=" . date ( 'Ymd' ) . ", ESTI01='" . date ( 'His' ) . "' WHERE " . $updateCIL01Sql;
		    if ($saveInserts == true) {
		        $cil01Res = odbc_prepare ( $conn, $updateCIL01Sql );
		        odbc_execute ( $cil01Res );
		        
		        echo $updateCIL01Sql . "<hr>";
		    } else {
		        echo "<font color='red'>" . $updateCIL01Sql . "</font><hr>";
		    }
		    
		    $updateCIL01Sql = "";
		}
		
		//Reset escalation date and time query for CIL01,
		if (isset($resetCIL01Sql)) {
		    $esVal = $es + 1;
		    $resetCIL01Sql = "UPDATE CIL01 SET EDAT01=" . date ( 'Ymd' ) . ", ESTI01='" . date ( 'His' ) . "' WHERE " . $resetCIL01Sql;
		    if ($saveInserts == true) {
		        $cil01ResetRes = odbc_prepare ( $conn, $resetCIL01Sql );
		        odbc_execute ( $cil01ResetRes );
		        echo $resetCIL01Sql . "<hr>";
		    } else {
		        echo "<font color='blue'>" . $resetCIL01Sql . "</font><hr>";
		    }
		    
		    $resetCIL01Sql = "";
		}
		
		//Initialize CIL02 max value for inserts
		$nextCIL02 = get_next_unique_id ( FACSLIB, "CIL02V01", "MAXID", "" ) - 1;
		
		//Set insert date and time
		$iDate = date ( 'Ymd' );
		$iTime = date ( 'H:i:s' );
		
	
	}
	
	echo date( 'H:i:s:u');
		//Walk through each CIL02 insert record
		foreach ( $cil02InsertArray as $c2 ){
			
			$nextCIL02++;
			$cil02sqlInsert = "INSERT INTO CIL02 values( $nextCIL02, $c2[0], '$c2[1]' , $iDate, '$iTime', 0, 'N')";
			if ($saveInserts == true) {
				$resCil02 = odbc_prepare( $conn, $cil02sqlInsert );
				
				if( odbc_execute( $resCil02 ) ){
					
				}else{
					echo "failed" . $cil02sqlInsert . "<hr>";
				}
			}else{
				echo $cil02sqlInsert . "<hr>";
			}
			
			//Clear insert statement
			$cil02sqlInsert = "";
		}
	echo date( 'H:i:s:u');

?>
<script language="javascript">
<!--

window.opener = self; 
setTimeout( "window.close()", 20000 ); 
//-->
</script>


