<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            insertIssue.php<br>
 * Development Reference:   D0129<br>
 * Description:             Prepares CIL01 insert variables<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *   D0129      TS	   10/14/2010   Initial Add
 *   LP0020     TS     15/06/2017   Completetion enhancement
 */
/**
 */


//Initialize Missing vars
$STAT01 = 1;
$RSID01 = 0;
$CDAT01 = 0;
$CTIM01 = 0;
$RESP01 = 0;
$OCCR01 = 0;
$DUED01 = 0;
$LSTP01 = 0;
$EMDA01 = 0;
$BUYR01 = 0;
$OWNR01 = 0;
$POFF01 = 0;
//Escalation user ID
$ESID01 = 0;

//PFC Identifier
$PFID01 = 0;

//Set escalation level to 0
$ESLV01 = 0;

//DI868C - Added set to 0 so that insert will function 
//Set Original Owner ID to 0
$OOWN01 = 0;

//Updater ID, set to session id of the user
$UPID01 = $_SESSION['userID'];

//Escalation date and time, last escalated.... set to entry so can be used in validation;
$EDAT01 = date( 'Ymd' );
$ESTI01 = date('His');


$typeName = get_type_name( $TYPE01 );
if( isset( $CLAS01 )){
    $className = get_class_name( $CLAS01 );
}

//Replace single quotes to create database friendly sql
//DI868L - Added functionality to change a single quote to a double single quote so that sql insert will not fail.
if( isset( $LDES01) ){
    $LDES01 = str_replace( "'", "''", $LDES01 );
}

$DESC01 = $className . " - " . $typeName;
//DI868G - Append short desctiption to current short description
if( isset($shortDescription) ){
    $DESC01 .= " $shortDescription";
}


//Move single quote functionality
$DESC01 = str_replace( "'", "''", $DESC01 );

//DI868G - End 
//LP0020 - Start Change
$PCPD01 = 0;
$DCPD01 = 0;
$DPFL01 = 0;
$CPDT01 = 0;
//LP0020 - Start Change



