<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            additionalItems.php<br>
 * Development Reference:   DI868<br>
 * Description:             Adds the ability for users to add multiple items with identical supporting information</b><br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *   D0129      TS	   10/14/2010   Initial Add
 *   D0301 		TS	   24/03/2011   Change Header and Menu calls
 *   D0260A		TS	   13/10/2011   Addition Pricing Info addition
 *   D0455	    TS	   16/11/2011	Change JS to meet Google chrome needs
 *   LP0027     KS     04/12/2017   LPS Unique ID creation<br>
 * 
 */
/**
 */
 
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
include 'copysource/functions/programFunctions.php';            //**LP0027
// require_once 'CW/cw.php';  Already added on programFunctions

if( !isset($conn) ){
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if (isset($conn) ) {
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
if ( isset($_SESSION['email']) && isset($_SESSION['password']) ){
    $userInfo [] = "";
    $userInfo = userInfo ( $_SESSION['email'], $_SESSION['password'] );
    $_SESSION['userID'] = $userInfo['ID05'];
    $_SESSION['name'] = $userInfo['NAME05'];
    $_SESSION['companyCode'] = $userInfo['CODE05'];
    $_SESSION['password'] = $userInfo['PASS05'];
    $_SESSION['email'] = $userInfo['EMAIL05'];
    
    if( !isset($_COOKIE["mtp"])){
        setcookie("mtp",$_SESSION['email'],time()+60*60*24*30);
    }
    
}elseif( isset($_SESSION['email']) ){

    $userInfo [] = "";
    $userInfo = user_cookie_info( $_SESSION['email'] );
    $_SESSION['userID'] = $userInfo['ID05'];
    $_SESSION['name'] = $userInfo['NAME05'];
    $_SESSION['companyCode'] = $userInfo['CODE05'];
    $_SESSION['password'] = $userInfo['PASS05'];
    $_SESSION['email'] = $_SESSION['email'];
    
}elseif ( isset($_COOKIE["mtp"]) ){

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


//D0455 - Added addItemRows function, addElement and removeElement functions are deprecated and no longer used
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<script type="text/javascript"> 

	function addItemRows(){

	
		var num = document.getElementById( 'addItemCount' ).value;

		if( num <= 10 ){
		var num2 = eval(num) + 1;
		
		document.getElementById( 'addItemCount' ).value = num2;

		document.getElementById( 'tblHeader' ).style.display = 'block';
		document.getElementById( 'row_'+ num ).style.display = 'block';
		}else{
			alert( "Sorry the maximum number of entries is 10" );
		}
		
	}
	function addElement() {
	  var ni = document.getElementById('myDiv');
	  var numi = document.getElementById('addItemCount');
	  var num = (document.getElementById('addItemCount').value -1)+ 2;
	  numi.value = num;
	  var newdiv = document.createElement('div');
	  var divIdName = 'my'+num+'Div';
	  newdiv.setAttribute('id',divIdName);
	  newdiv.innerHTML = 'Quantity: <input type=\'text\' name=\'quantity_'+num+'\' value=\'\' size=\'5\'>     Part: <input type=\'text\' name=\'part_'+num+'\' value=\'\'><a href=\'#\' onclick=\'removeElement('+num+')\' text-decoration:none>  Remove</a>';
	  ni.appendChild(newdiv);
	}
	function removeElement(divNum) {
		  var d = document.getElementById('myDiv');
		  var olddiv = document.getElementById('my' + divNum + 'Div');
		  document.getElementById('addItemCount').value = eval( document.getElementById('addItemCount').value - 1 );
		  d.removeChild(olddiv);
	}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
</head>

<?

//Display Header
//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );

//D0301 - Change Header
include_once 'copysource/header.php';
echo "<body>";
if( !$_SESSION['classArray'] && !$_SESSION['typeArray'] ){
    $_SESSION['classArray'] = get_classification_array();
    $_SESSION['typeArray'] = get_typeName_array();
}
//menuFrame( "MTP" );
//D0301 - Change Menu
include_once 'copysource/menu.php';
?>
<center>
<table width=70%>

<?php 
if( !isset( $_REQUEST['action'] ) ||  $_REQUEST['action'] == "" ){
	?>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class='boldBig'>Supporting Information</td></tr>
	<tr><td>&nbsp;</td></tr>
	<?php 
	$supportingInfo = get_supporting_information( $_REQUEST['ID01'] );
	foreach ( $supportingInfo as $sInformation ){
		?><tr>
			<td><b><?php echo $sInformation['name'];?>:</b>&nbsp;&nbsp;<?php echo $sInformation['value'];?></td>
		</tr>
		<?php 
	}
?>
	
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	</table>
	<table width=70%>
	<form method='post' action='additionalItems.php'>
	<tr><td><div id="myDiv"> </div></td></tr>
	
	<tr id='tblHeader' style='display:none'>
		<td width='13%'><b>Qty</b></td>
		<td><b>Item</b></td>
	</tr>
	<?php 
	//D0455 - Add inline inputs so that values are posted to HTTPRequest
	for( $i = 1; $i <= 10; $i++ ){
	?>
	<tr id='row_<?echo $i;?>' style='display:none'>
		<td><input type='text' name='quantity_<?echo $i;?>' id='quantity_<?echo $i;?>' maxlength='3' value='' class='small'/></td>
		<td><input type='text' name='part_<?echo $i;?>' id='part_<?echo $i;?>'value='' class='medium'/></td>
	</tr>
	<?php
	}
	
	//D0455 - href to call addItemRows JS function and added addItemCount input to store current line added.
	?>
	<tr>
		<td colspan='2'><a href='javascript:void(0)' onclick='addItemRows();'>Add Item</a></td>
	</tr>
	<tr>
		<td colspan='2'><input type='hidden' id='addItemCount' name='addItemCount' value='1'></a></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan='2'><input type='submit' value='Save Items'>
	<input type='hidden' name='ID01' value='<?php echo $_REQUEST['ID01'];?>'/>
	<input type='hidden' name='action' value='addItems'/></td></tr>
	</form>
</table>
<?php

}else{
	
	$ticketDetails = get_base_ticket_details( $_REQUEST['ID01'] );
	
	
	//Copy vars from parent ticket
	$TYPE01 = $ticketDetails['TYPE01'];
	$CLAS01 = $ticketDetails['CLAS01'];
	$LDES01 = $ticketDetails['LDES01'];
	$PRTY01 = $ticketDetails['PRTY01'];
	$RQID01 = $_SESSION['userID'];
	
	include 'prepareIssueInsert.php';
	
	$priority = get_priority( $PRTY01, "short");
	

	$ticketsAdded = "";

	for( $i = 1; $i <= $_REQUEST['addItemCount'] - 1; $i++ ){
		
		$mailFlag = true;
		
		if( isset( ${'part_' . $i})  ){
			$validPart = validatePartNumber( ${'part_' . $i} );
		
		
			if( $validPart > 0 ){
				
				$ticketDetails['DESC01'];
				
				$partNumber =  ${'part_' . $i};
				
				$DESC01 = $className . " - " . $typeName;
				$parentDesc = $ticketDetails['DESC01'];
	
				//Remove the Parent Part Number from Description
				$childDesc = substr( $parentDesc, strpos( $parentDesc, $DESC01 ) );
				
				//Append Part Number to Desciption
				$DESC01 = $partNumber . " - " . $childDesc;
				
				$emailArray = array();
				
				$sql = "SELECT TEXT10, ATTR10, NAME07, HTYP07 FROM CIL10 T1"
				     . " INNER JOIN CIL07 T2"
				     . " ON T1.ATTR10 = T2.ATTR07"
					 . " WHERE CAID10=" . $_REQUEST['ID01'];
					
				$res = odbc_prepare( $conn, $sql );
				odbc_execute ( $res );
				
				$insertFlag = true;
				
				$cil10InsertArray = array();
				
				while( $row = odbc_fetch_array( $res ) ){
					
					$text = trim($row['TEXT10']);
					
					if( trim($row['HTYP07']) == "PART" ){
						$text = ${'part_' . $i};
						$partNumber = ${'part_' . $i};
					}
					if( trim($row['NAME07']) == "Quantity in Error" || trim($row['NAME07']) == "Quantity Required"){
						$text = ${'quantity_' . $i};
					}
					
					
					if( trim($row['HTYP07']) == "SODP" ){
						if( strpos($text, " " ) ){
				            $orderNumber = substr( $text, 0, strpos( $text, " " ) );
				            $desnNumber = substr( $text, strpos( $text, " " ) );
				        }else{
				            $orderNumber = $text;
				        }
					}
					
					if ( trim($row['HTYP07']) == "COUN" ) {
					    if( isset( $attribValue )){
				            $marketArea = $attribValue;
					    }
				    } 
					
					if( $CLAS01 == 7 && trim( $row['ATTR10'] ) >= 465 && trim( $row['ATTR10'] ) <= 475 ){
					    
				        $receiveStockroom = $text;
				    }
					
					if( trim($row['NAME07']) == "Stockroom returned to" ){
		   
				        $returnedStockroom = getReturnedStockroom( $text );
				    }
				    
				    if ( trim($row['HTYP07']) == "REGN" ) {
				        $region = $text;
				    }    
					
				   //Push values onto the array to ensure that the insert is not false based on insertFlag
					array_push( $cil10InsertArray, $row['ATTR10'] . ", '$text', 'DSH', 'CIL', ''" );
	
				}
				//Check to see if both partnumber and ordernumber exist in insert, if so validate.
				if( isset($partNumber) && isset($orderNumber) ){
				    if( !isset( $desnNumber ) ){
				        $desnNumber = 0;
				    }
					$validCounter = validatePartOrder($partNumber, $orderNumber, $desnNumber, $TYPE01 );
					if( $validCounter > 0){
						$insertFlag = true;
					}else{
						$insertFlag = false;
					}
	
				}
				
				//Ensure that the insert should occur
				if( $insertFlag == true ){
					
					//Get next unique ID of main Table - CIL01
				    $nextID = 0;                                                                   //**LP0027
				    list ($nextID, $lastID) = getReferenceNumbersFromFile("CIL01");                //**LP0027
				    if ($nextID == 0){                                                             //**LP0027
				        echo "Ticket not created<br>";                                             //**LP0027
				        die();                                                                     //**LP0027
				    }                                                                              //**LP0027
					
					$ticketsAdded .= $nextID . ", ";
					
					//Insert the CIL01 record
					include 'insertCil01.php';
					
					//itterate through the CIL10 records to be inserted and append CIL10 ID and ID01 to sql
					foreach ($cil10InsertArray as $iArray ) {
						$nextCil10ID = get_next_unique_id( FACSLIB, 'CIL10', 'LINE10', "" );
						
						$insertSql = "INSERT INTO CIL10 VALUES("
								   . $nextCil10ID . ", $nextID, " . $iArray . ")";
						
						$resCil10Insert = odbc_prepare( $conn, $insertSql );
						odbc_execute ( $resCil10Insert );
	
					}
					
					//D0260A - Start of section********************************************************************
		        	$partInfoSql = "SELECT PCLS35, PLSC35, DSSP35, PREG35, PGMJ35 FROM PARTS WHERE CONO35='DI' AND PNUM35='$partNumber'";
		        	$partInfoRes = odbc_prepare( $conn, $partInfoSql );
		        	
		        	odbc_execute ( $partInfoRes );
		     	
		        	while( $partInsertRow = odbc_fetch_array( $partInfoRes ) ){
		      		
		        		$partInfoInsertSql = "INSERT INTO CIL33 VALUES(" . get_next_unique_id( FACSLIB, 'CIL33', 'ID33', "" )
		        						   . ",$nextID, '$partNumber', '" . trim($partInsertRow['PCLS35']) . "', '" . trim($partInsertRow['PLSC35'])
		        						   . "', '" . trim($partInsertRow['DSSP35']) . "', '" . trim($partInsertRow['PREG35']) . "', '" . trim($partInsertRow['PGMJ35']) . "',"
		        						   . "'','','' )";
			        	$insertPartInfoRes = odbc_prepare( $conn, $partInfoInsertSql );		//D0260A
			            odbc_execute( $insertPartInfoRes );	
		        	}
	        
	        		//D0260A - End of section********************************************************************
					
								
				}else{
					
					echo "The part number $partNumber is not valid for the order number $orderNumber.  "
					     . "Please verify the part and order numbers and re-enter the ticket using the correct information<br>";
					//Flag to not send mail
					$mailFlag = false;
				
				}
				
				
			}else{
				
				echo ${'part_' . $i}  . " is not a valid part number.  Please verfiy the part number and re-enter the ticket using the correct"
				. " information.<br>";
				//Flag to not send mail
				$mailFlag = false;
			}
		}else{
			$mailFlag = false;
		}
		
		if( !isset($partNumber)){
		    $partNumber = "";
		}
		if( !isset($orderNumber)){
		    $orderNumber = "";
		}
		if( !isset($region)){
		    $region = "";
		}
		if( !isset($marketArea)){
		    $marketArea = "";
		}
		if( !isset($desnNumber)){
		    $desnNumber = "";
		}
		if( !isset($receiveStockroom)){
		    $receiveStockroom = "";
		}
		if( !isset($returnedStockroom)){
		    $returnedStockroom = "";
		}
		if( $mailFlag ){
			notifications( $nextID, $CLAS01, $TYPE01, $emailArray, $partNumber, $orderNumber, $marketArea , $DESC01, $priority, $desnNumber, $receiveStockroom, $returnedStockroom, $region);
		}
	}
	
		$ticketsAdded = substr( $ticketsAdded, 0, -2 );
		
		echo "<center><br><br>Issue(s) <b>$ticketsAdded</b> Have Been Logged<br><br>";
		
		echo "<center>";
		echo "<table width=100% border=0>";
		echo "<tr><td>&nbsp;</td></tr>";
		echo "<tr>";
		echo "<form method='get' action='tickets.php'>";
		echo "<td class='center'><input type='submit' value='Continue'></td>";
		echo "<input type='hidden' name='CLAS09' value='$CLAS01'>";
		echo "<input type='hidden' name='type' value='$TYPE01'>";
		echo "<input type='hidden' name='stat' value='1'>";
		echo "<input type='hidden' name='PRTY01' value='$PRTY01'>";
		echo "</form>";
	
}
?>