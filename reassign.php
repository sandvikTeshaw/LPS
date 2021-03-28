<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			reassign.php<br>
 * Development Reference:	DI868<br>
 * Description:				profile.php allows users to reassign their current open tickets that they are the owner for<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 * 	D0555	  TS	  09/05/2012  re-write for re-assign issue
 *  LPS0004   IS      13/11/2015 
 *  LP0034    KS      23/01/2018  Private Message Functionality 
 *  LP0042    KS      01/06/2018  LPS Audit File for Ticket Ownership and Action 
 * 
 */
/**
 */

include 'copysource/config.php';
//include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

$visible = "N"; //**LP0034

$ticketList = explode( ",", $ticketList );

$id02Query = "";
$tickCounter = 0;


$userInfoName = user_name_by_id( $_SESSION['userID'] );
$currentUser = user_name_by_id( $ticketUser );
$newUser = user_name_by_id( $newResource );

foreach ( $ticketList as $ticks ){

	
	if( ${chk_ . $ticks} == "on" ){
		
		$tickCounter++;
		//LPS0004
		//$updateSql = "UPDATE CIL01 SET OWNR01=$newResource, POFF01=$newResource, RSID01=$newResource"
		//       	   . " WHERE ID01=$ticks";
		//$res = odbc_prepare( $conn, $updateSql );
		//odbc_execute( $res );
		//$actionResponse = "Ticket re-assigned from $currentUser to $newUser by " . $userInfoName;
		
		$readCIL01 = "select * ";                                                                                                                   //**LP0042
		$readCIL01 .= " from CIL01 ";                                                                                                               //**LP0042
		$readCIL01 .= " where ID01 = " . $ticks . " ";                                                                                              //**LP0042
		
		if( $_REQUEST['selection'] == "owned" ){
		    $cil01Res = odbc_prepare($conn, $readCIL01);                                                                                                 //**LP0042
		    odbc_execute($cil01Res);                                                                                                                     //**LP0042
		    while ($cil01Row = odbc_fetch_array($cil01Res)){                                                                                             //**LP0042
		        $insertCIL01OA = "insert into CIL01OA ";                                                                                                //**LP0042
		        $insertCIL01OA .= " VALUES ( " . $ticks . ", " . date('Ymd') . ", '" . date('His') . "', ";                                             //**LP0042
		        $insertCIL01OA .= $cil01Row['OWNR01'] . ", " . $newResource . ", 1, " . $_SESSION['userID'] . ")";                                      //**LP0042
		        $cil01oaRes = odbc_prepare($conn, $insertCIL01OA);                                                                                       //**LP0042
		        odbc_execute($cil01oaRes);                                                                                                               //**LP0042
		    }                                                                                                                                           //**LP0042
		    $updateSql = "UPDATE CIL01 SET OWNR01=$newResource, POFF01=$newResource, RSID01=$newResource". " WHERE ID01=$ticks";
		    $res = odbc_prepare( $conn, $updateSql );
		    odbc_execute( $res );
		    $actionResponse = "Ticket re-assigned from $currentUser to $newUser by " . $userInfoName;
		}else{
		    $updateSql = "UPDATE CIL01 SET RQID01=$newResource " . " WHERE ID01=$ticks";
		    $res = odbc_prepare( $conn, $updateSql );
		    odbc_execute( $res );
		    $actionResponse = "Requester change  from $currentUser to $newUser by " . $userInfoName;
		}
		
		
		$next02Id = get_next_unique_id ( FACSLIB, "CIL02L01", "ID02", "" );
		

			$id02Query = "INSERT INTO CIL02 VALUES( $next02Id, $ticks, '"
					       . $actionResponse . "', " . date ( 'Ymd' ) . ", '" . date ( 'H:i:s' ) . "', "
						   . $_SESSION ['userID'] . ", '$visible')";
	
		
		$cil02res = odbc_prepare( $conn, $id02Query );
		odbc_execute( $cil02res );
		
		//echo $id02Query . "<hr>";
	}

}

echo "<br><br><br>";
echo "<center>";
echo "Tickets have been reassigned";
?>
		<br><input type='button' name='continue' value='Continue' onClick="javascript:history.go(-1)">
	<?
	

	
?>