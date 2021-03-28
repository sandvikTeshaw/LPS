<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUserAdd.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceUserAdd.php application page for adding new users<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0097     TJS     12/04/2010  re-write userMaintenance for new escalation<br>  
 *  D0247     TS	  01/02/2011  Modification
 *  LP0018    AG      07/07/2017  Adding user specific timezones
 *  LP0039    KS      29/03/2018  In the LPS "Register for LPS account" page please add hyperlinks to instruction guidelines (SPIDER 2.0)
 */
/**
 */

global $conn;
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($conn)) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
}

if (isset($conn)) {

} else {
	echo "Connection Failed";
}

if( !isset($_SESSION ['email'])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif( isset($_COOKIE ["mtp"])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
}

?>
<!DOCTYPE html>
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
<script type="text/javascript">

	function validateEntry( attrIds, attrTypes, attrNames, $attrPosition ){

		var subEmail = document.getElementById("email").value;
		var subName = document.getElementById("name").value;

		var subTimezone = document.getElementById("timezone");
		var subTimezoneValue = subTimezone.options[subTimezone.selectedIndex].value;

		var subSuper = document.getElementById("supervisor");
		var subSupervalue = subSuper.options[subSuper.selectedIndex].value;


		var missingMsg = "";
		
		if( subEmail == "" ){
			missingMsg += " - Email Address\n";
		}
		if( subName == "" ){
			missingMsg += " - Name\n";
		}
		if( subSupervalue == 0 || subSupervalue == "" ){
			missingMsg += " - Select Supervisor\n";
		}
		if( subTimezoneValue == 0 || subTimezoneValue == "" ){
			missingMsg += " - Select Timezone\n";
		}

		var attrTypesLength = attrTypes.length;

		for (var i = 0; i < attrTypesLength; i++) {
			if( attrTypes[i] == "T" ){
				var atName = "attr_" + eval( attrIds[i]  );
				if( document.getElementById( atName ).value == "" ){
					missingMsg += " - " + attrNames[i] + "\n";
				}
			}
			if( attrTypes[i] == "S" ){
				
				var atName = "attr_" + eval( attrIds[i]  );

				var atSelTmp = document.getElementById( atName );
				var atSelTmpVal = atSelTmp.options[atSelTmp.selectedIndex].value;


				if( atSelTmpVal == "" || atSelTmpVal == 0 ){
					missingMsg += " - " + attrNames[i] + "\n";
				}
			}
			
		}
		
		

		if( missingMsg != "" ){

			missingMsg = "You are missing the following require information\n" + missingMsg
			missingMsg += "Please enter information and try again\n";
			alert( missingMsg );
			return false;
		}
	
		return true;

	}
</script>
</head>
<?
//headerFrame ( $_SESSION ['name'] );
include_once 'copysource/header.php';
?><body><?php  

if (isset($_SESSION['userID'])) {
	
	if (! $_SESSION ['classArray'] && ! $_SESSION ['typeArray']) {
		$_SESSION ['classArray'] = get_classification_array ();
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
	//menuFrame ( "MTP" );
	include_once 'copysource/menu.php';
}
if( empty( $_REQUEST ) ){
   
    $_REQUEST['userId'] = $_SESSION['userID'];
    $_REQUEST['action'] = "" ;
    
}
if( !isset( $_REQUEST['userId'] )){
    $_REQUEST['userId'] = $_SESSION['userID'];
}


?>
<center>
<table width=65% cellpadding='0' cellspacing='0'>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td class='title'>Add User</td>
	</tr>
	<tr>
		<td colspan='4'><hr/></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
</table>
<?php 
	$userArray = get_user_list();
	//LP0018 - Timezone field adding
	$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	$zoneList = "";
	foreach ($tzlist as $zone) {
	    $zoneList .= "<option value='" . $zone . "'>" . $zone . "</option>";
	}
	
	if( !isset( $_REQUEST['action'] ) || $_REQUEST['action'] == "" || $_REQUEST['action'] == "register" ){
	    
	    if( !isset( $company ) ){
	        $company = 0;
	    }
	?>
<form method='post' action='maintenanceUserAdd.php'>
<table width=65% cellpadding='0' cellspacing='0'>
	<tr>
		<td class='bold'><font color='red'>*</font>Name:</td>
		<td><input type='text' name='name' id='name' value=''/></td>
		<td class='bold'><font color='red'>*</font>Email:</td>
		<td><input type='text' name='email' id='email' value='@sandvik.com'/></td>
	</tr>
	<tr>
		<td class='bold'><font color='red'>*</font>Company:</td>
		<td>
		<select id='company' name='company'>
			<?php list_company_code( $company )?>
		</select>
		</td>
		<td class='bold'>&nbsp;Phone:</td>
		<td><input type='text' id='phone' name='phone' value=''/></td>
	</tr>
	<tr>
		<td class='bold'><font color='red'>*</font>Supervisor:</td>
		<td>
		<select id='supervisor' name='supervisor'>
			<?php show_user_list($userArray, $_SESSION['userID'] );?>
		</select>
		</td>
		<td class='bold'><font color='red'>*</font>Availability:</td>
		<td>
		<select id='availability' name='availability'>
			<option value='Y'>Available</option>
			<option value='N'>Out of Office</option>
		</select>
		</td>
	</tr>
	<tr>
		<td class='bold'><font color='red'>*</font>TimeZone:</td>
		<td>
		<select id='timezone' name='timezone'>
			<option value=''>Select the TimZone</option>
			<?php echo $zoneList;?>
		</select>
		</td>
		<td class='bold'><font color='red'>*</font>Back-Up:</td>
		<td>
		<select id='backup' name='backup'>
			<?php show_user_list($userArray,0 ) ?>
		</select>
		</td>
	</tr>
	<?php 
	   $reqAttribIdsArray = array();
	   $reqAttribTypeArray = array();
	   $reqAttribNameArray = array();
	   $reqAttribPosition = array();
	   
	   $attrCounter = 0;
	   $sqlAttributes = "SELECT * FROM HLP06 WHERE ACTV06=1 ORDER BY SORT06";
	   $resAttributes = odbc_prepare ( $conn, $sqlAttributes);
	   odbc_execute ( $resAttributes);
	   
	   while( $rowAttr = odbc_fetch_array($resAttributes) ){
	       
	       if( $attrCounter == 0 || $attrCounter % 2 == 0 ){
	           
	           if( $attrCounter > 0 ){
	               ?>
    	           </tr>
    	           <?php
	           }
	           ?>
	           <tr>
	           <?php 
	       }
	       ?>
	       		<td class='bold'>
	       			<?php 
	       			if( $rowAttr['REQD06'] == 1 ){
	       			    array_push($reqAttribIdsArray, trim($rowAttr['ID06']));
	       			    array_push($reqAttribTypeArray, trim($rowAttr['ATYP06']));
	       			    array_push($reqAttribNameArray, trim($rowAttr['ATTR06']));
	       			    array_push($reqAttribPosition, $attrCounter );
	       			     ?>
	       			     <font color='red'><b>*</b></font>
	       			    
	       			     <?php 
	                }else{ 
	                   ?><b>&nbsp;</b><?php 	       			    
	       			}?>
	       			<?php echo trim($rowAttr['ATTR06']);?>:
	       			 <input type='hidden' name='type_<?php echo trim($rowAttr['ID06'])?>' id='type_<?php echo trim($rowAttr['ID06'])?>' value='<?php echo trim($rowAttr['ATYP06'])?>'/>
	       		</td>	       
	       <?php 
	       
	       if( trim($rowAttr['ATYP06']) == "T" ){
	           if( (isset( $_REQUEST['userId'] ) && $_REQUEST['userId'] != "" && $_REQUEST['userId'] != 0)
	               || ( isset( $_SESSION['userID']) && $_SESSION['userID'] != "" && $_SESSION['userID'] != 0 )){
    	           $attrTextSql = "SELECT ID08, TEXT08 FROM HLP08 WHERE ATTR08=" . trim($rowAttr['ID06']) . " AND USER08=" . $_REQUEST['userId'];
    	           $resAttrText = odbc_prepare ( $conn, $attrTextSql);
    	           if( odbc_execute ( $resAttrText ) ){
    	               
    	           }else{
    	               $handle = fopen("./sqlFailures/sqlFails.csv","a+");
    	               fwrite($handle, "286 - maintenanceUserAdd.php," . $attrTextSql . "\n" );
    	               fclose($handle);
    	           }
    	           $usrAttText= odbc_fetch_array($resAttrText);
	           }else{
	               $usrAttText['TEXT08'] = "";
	           }
	           ?><td><input type='text' id='attr_<?php echo trim($rowAttr['ID06'])?>' name='attr_<?php echo trim($rowAttr['ID06'])?>' value='<?php echo trim($usrAttText['TEXT08']);?>'/></td><?php 
	       }else{
    	       $attrOptionsSql = "SELECT ID07, STXT07 FROM HLP07 WHERE ATTR07=" . trim($rowAttr['ID06']) . " AND ACTV07=1 AND STXT07 <> '' ORDER BY SORT07";
    	       $resAttrOpts = odbc_prepare ( $conn, $attrOptionsSql);
    	       if( odbc_execute ( $resAttrOpts )){
    	           
    	       }else{
    	           $handle = fopen("./sqlFailures/sqlFails.csv","a+");
    	           fwrite($handle, "298 - maintenanceUserAdd.php," . $answersSql . "\n" );
    	           fclose($handle);
    	       }
    	       
    	       if( (isset( $_REQUEST['userId'] ) && $_REQUEST['userId'] != "" && $_REQUEST['userId'] != 0)
    	           || ( isset( $_SESSION['userID']) && $_SESSION['userID'] != "" && $_SESSION['userID'] != 0 )){
        	       $optSelSql = "SELECT OPID08 FROM HLP08 WHERE ATTR08 =" . trim($rowAttr['ID06']) . " AND USER08= " . $_REQUEST['userId'];
        	       $resOptSel = odbc_prepare ( $conn, $optSelSql);
        	       if( odbc_execute ( $resOptSel )){
        	           
        	       }else{
        	           $handle = fopen("./sqlFailures/sqlFails.csv","a+");
        	           fwrite($handle, "308 - maintenanceUserAdd.php," . $optSelSql . "\n" );
        	           fclose($handle);
        	       }
        	       $optData = odbc_fetch_array($resOptSel);
    	       }else{
    	           $optData['OPID08'] = 0;
    	       }
    	       
    	       ?>
	           <td>
	           		<select id='attr_<?php echo trim($rowAttr['ID06'])?>' name='attr_<?php echo trim($rowAttr['ID06'])?>'>
	           			<option value='0'>Select <?php echo trim($rowAttr['ATTR06'])?></option>
	           		<?php 
	           		  while ( $rowOptions = odbc_fetch_array( $resAttrOpts) ){
	           		      ?><option value='<?php echo trim($rowOptions['ID07'])?>' <?php echo ( trim($optData['OPID08']) ==  trim($rowOptions['ID07']) ) ? 'selected' : ''; ?> ><?php echo trim($rowOptions['STXT07']);?></option><?php 
	           		  }
	           		?>
	           	
	           
	           		</select>
	           </td>
	           <?php      
	       }

	       $attrCounter++;
	   }
	
	?>
	</tr>
	<?php 
	if( isset( $_SESSION['authority'] ) && $_SESSION['authority'] == "S" ){
	?>
	<tr>
		<td class='bold'>Authority:</td>
		<td>
		<?php 
		$authArray = authority_array();
				?><select name='authority'><?php 
				foreach ( $authArray as $authorities){
					echo "<option ";
						if( $authorities['value'] == '' ){
							echo "SELECTED ";	
						}
					echo "value='" . $authorities['value'] . "'>";
						echo $authorities['description'];
					echo "</option>";
				}
				?>
				</select>
		</td>
	</tr>
	<?php 
	}else{
		?>
		<tr>
			<td class='bold'><font color='red'>*</font>Password:</td>
			<td><input type='text' name='password' value=''/></td>
		</tr>
		<input type='hidden' name='authority' value='E'>
		<?php 
	}
	?>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
		<input type='submit' value='Save User' onclick='return validateEntry( <?php echo json_encode($reqAttribIdsArray)?>,<?php echo json_encode($reqAttribTypeArray)?>,<?php echo json_encode($reqAttribNameArray)?>, <?php echo json_encode($reqAttribPosition)?>)'/>
		<input type='hidden' name='action' value='save'/>
		<input type='hidden' name='from' value='<?php echo $_REQUEST['action'];?>'/>
		<input type='hidden' name='userId' value='<?php echo $_REQUEST['userId'];?>'/>
		</td>
	</tr>
</table>
</form>
	<?php  
    listHyperlinks();					//LP0039
}elseif( $_REQUEST['action'] == "save" ){
		

		//D0247 - Moved replace statement before count call
		//$emailRequest = str_replace ( "@", "�", trim($_REQUEST['email']) );

		$emailRequest = trim(strtolower($email));
		$emailRequest= strtr($emailRequest, $GLOBALS['normalizeSaveChars']);

		
		$recordCount = count_records( FACSLIB, "HLP05", " WHERE LCASE(EMAIL05)='" . $emailRequest . "'" ); 
		
		if( !isset( $_REQUEST['password'] ) || trim($_REQUEST['password']) == "" ){
		    $setPassword = "sandvik";
		}else{
		    $setPassword = trim($_REQUEST['password']);
		}
		
		if( trim($_REQUEST['authority']) == "" ){
		    $setAuthority = "E";
		}else{
		    $setAuthority= trim($_REQUEST['authority']);
		}
		
		//This line needs to be commented out when moved to production**************************************************
		//$emailRequest = str_replace ( "�", "@", trim($_REQUEST['email']) );
		//******************************************************************************************************************
		
		if( $recordCount == 0 ){
			$next_id = get_next_unique_id( FACSLIB, "HLP05", "ID05", "" );
			//LP0018 - Updated insert statement to include timezone value too.
			$insert = "insert into HLP05 values( $next_id, '" . trim($_REQUEST['name']) . "', '" . trim($_REQUEST['company']) . "', '"
	                . trim($_REQUEST['phone']) . "', '" . $emailRequest  . "', '$setPassword', 'N', 'N', '"
					. trim($_REQUEST['availability']) . "', " . trim($_REQUEST['backup']) . ", '$setAuthority', '" . trim($_REQUEST['timezone']) . "', now() )";    

	
			$res = odbc_prepare( $conn, $insert );
			odbc_execute( $res );
			
			if( trim($_REQUEST['supervisor']) == "" ){
				$super = 0;
			}else{
				$super = trim($_REQUEST['supervisor']);
			}
			
			$insert31 = "insert into CIL31 values( $super,$next_id )";
			$res31 = odbc_prepare( $conn, $insert31 );
			odbc_execute( $res31 );

		//$updateCIL31 = "update CIL31 SET SUPR31={$_REQUEST['supervisor']} WHERE EMPL31={$_REQUEST['userId']}";
		//$res31 = odbc_prepare( $conn, $updateCIL31 );
		//odbc_execute( $res31 );
			//Walk through all variables to define attributes
			
			
			foreach( $_REQUEST as $key => $req ){
			    
			    if( substr($key, 0, 5) == "type_"){
			        
			        //Get Attribute Values
			        $attribNum = substr($key, 5 );
			        $attribName = "attr_" . $attribNum;
			        $attribSql = "";
			        $auditSql = "";
			        
			        //Check if Text attribute
			        if( $_REQUEST[ $key ] == "T" ){

			            $attribSql = "INSERT INTO HLP08 VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08", "ID08", "" ) . ", $attribNum," . $next_id. ", '" . $_REQUEST[ $attribName] . "', 0)";

			        }
			        
			        //Check if Select Option attribute
			        if( $_REQUEST[ $key ] == "S" ){
			         
			            $attribSql = "INSERT INTO HLP08 VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08", "ID08", "" ) . ", $attribNum," . $next_id. ", '', " . $_REQUEST[ $attribName] .")";
			        }
			     
			        
			        $reAttribAction = odbc_prepare( $conn, $attribSql);
			        odbc_execute( $reAttribAction);
			        
			        
			    }
			 
			}
	
			echo "<center>";
			echo "<br><br><b>User Changes Have Been Added</b>";
			
			if( $_REQUEST['from'] != "register" ){
		?>
			<meta http-equiv="refresh" content="1;url=maintenanceUser.php"/>
		<?
			}else{
				?>
				<meta http-equiv="refresh" content="1;url=index.php?email=<?php echo trim($_REQUEST['email']);?>&password=<?php echo $_REQUEST['password'];?>"/>
				<?php 
			}
		}else{
			
			//D0247 - Added new section to re-enable users.
		    $dupUserInfo = user_cookie_info($email);
			
				echo "<center>";
				echo "<br><br><b>Email address already exists in LPS</b><br>";
			if( $dupUserInfo['DEL05'] == "Y" ){
					echo "<b>This account has been disabled.<br></b>";
					echo "<b>Would You like to re-enable this account?<br></b>";
					echo "<hr>";
					?>
					
					<form method='post' action='maintenanceUserAdd.php' onsubmit="return validateEntry()">
					<table>
						<tr>
							<td class='bold'><font color='red'>*</font>Name:</td>
							<td><input type='text' name='name' value='<?php echo trim( $dupUserInfo['NAME05']);?>'/></td>
						</tr>
						<tr>
							<td class='bold'><font color='red'>*</font>Email:</td>
							<td><input type='text' name='email' value='<?php echo trim($email);?>' disabled='true'/></td>
						</tr>
						<tr>
							<td class='bold'><font color='red'>*</font>Company:</td>
							<td>
							<select name='company'>
								<?php list_company_code($dupUserInfo['CODE05'])?>
							</select>
							</td>
						</tr>
						<tr>
							<td class='bold'>Phone:</td>
							<td><input type='text' name='phone' value='<?php echo $dupUserInfo['PHONE05'];?>'/></td>
						</tr>
						<tr>
							<td class='bold'><font color='red'>*</font>Supervisor:</td>
							<td>
							<select name='supervisor'>
								<?php show_user_list($userArray, $_SESSION['userID'] );?>
							</select>
							</td>
						</tr>
						<tr>
							<td class='bold'><font color='red'>*</font>Availability:</td>
							<td>
							<select name='availability'>
								<option SELECTED value='Y'>Available</option>
								<option value='N'>Out of Office</option>
							</select>
							</td>
						</tr>
						<tr>
                        <td class='bold'><font color='red'>*</font>TimeZone:</td>
                        <td>
                        <select name='timezone'>
                        	<option value=''>Select the TimZone</option>
                        	<?php echo $zoneList;?>
                        	</select>
                        	</td>
                        </tr>
						<tr>
							<td class='bold'><font color='red'>*</font>Back-Up:</td>
							<td>
							<select name='backup'>
								<?php show_user_list($userArray, $dupUserInfo['BACK05'] ) ?>
							</select>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='2'>
							<input type='submit' value='Re-Enable Account'/>
							<input type='hidden' name='action' value='reenable'/>
							<input type='hidden' name='from' value='<?php echo $_REQUEST['action'];?>'/>
							<input type='hidden' name='emailRequest' value='<?php echo trim($email);?>'/>
							</form>
							<form method='post' action='index.php'>
								<input type='submit' value='Cancel'/>
							</form>
							</td>
				
						</tr>
					</table>
					
					<?php 
			}else{
					
				?>	
					<table>
					<tr>
						<td colspan='2'>
							<form method='post' action='maintenanceUserAdd.php'">
							<input type='submit' value='Send Password'/>
							<input type='hidden' name='action' value='sendpassword'/>
							<input type='hidden' name='from' value='<?php echo $_REQUEST['action'];?>'/>
							<input type='hidden' name='emailRequest' value='<?php echo $emailRequest;?>'/>
							</form>
							<form method='post' action='index.php'>
								<input type='submit' value='Cancel'/>
							</form>
							</td>
					</tr>
					</table>
				<?php 
				
			}
			
		}
		
	}elseif( $_REQUEST['action'] == "reenable" ){
		
		echo "<center>";
		echo "<br><br><b>Account has been re-enabled.<br></b>";
		echo "<b>Password will be emailed to your account<br></b>";
		echo "<hr>";
		
		
		$dupUserInfo = user_cookie_info($_REQUEST['emailRequest']);
		
		//Update HLP05 so user is no longer deleted
		$updateSql = "UPDATE HLP05 SET DEL05 = 'N' WHERE ID05=" . trim($dupUserInfo ['ID05']);
		$res05 = odbc_prepare( $conn, $updateSql );
		odbc_execute( $res05 );

		
		//Delete current supervisor link
		$delete31 = "DELETE FROM CIL31 WHERE EMPL31=" . trim($dupUserInfo ['ID05']);
		$resDel31 = odbc_prepare( $conn, $delete31 );
		odbc_execute( $resDel31 );
		
		//Create new supoervisor link
		$insert31 = "insert into CIL31 values( " . $_REQUEST['supervisor'] . ", " . trim($dupUserInfo ['ID05']) . " )";
		$res31 = odbc_prepare( $conn, $insert31 );
		odbc_execute( $res31 );
		
		
		//Set session vars so user will automatically be logged into system
		$_SESSION ['userID'] =      trim($dupUserInfo ['ID05']);
	    $_SESSION ['name'] =        trim($dupUserInfo ['NAME05']);
	    $_SESSION ['companyCode'] = trim($dupUserInfo ['CODE05']);
	    $_SESSION ['authority'] =   trim($dupUserInfo ['AUTH05']);
	    $_SESSION ['email'] =       trim($dupUserInfo['EMAIL05']);
	    $_SESSION ['password'] =    trim($dupUserInfo['PASS05']);

	    send_account_info_mail( trim($dupUserInfo['EMAIL05']) ); 
	
		?>
			<meta http-equiv="refresh" content="1;url=index.php"/>
		<?php 
		
		
	}else{
		echo "<center>";
		echo "<br><br><b>Account information has been sent.<br></b>";
		
		$emailAddr = $_REQUEST['emailRequest'];
		 send_account_info_mail( $emailAddr ); 
	
		?>
			<meta http-equiv="refresh" content="0;url=index.php"/>
		<?php 
		
		
	}
	?>

</center>
</body>
</html>

<?php                                                                                                                                   //**LP0039
function listHyperlinks(){                                                                                                              //**LP0039
    global $conn;                                                                                                                       //**LP0039
    $attachementsFolder = "../../attachments/documents/";                                                                               //**LP0039                                                                                                                          //**LP0039
                                                                                                                                        //**LP0039
    $sql = "select * ";                                                                                                                 //**LP0039
    $sql .= " from DSH07 ";                                                                                                             //**LP0039
    $sql .= " where WBID07 = 'DOC' ";                                                                                                   //**LP0039
    $sql .= "   and PGID07 = 'CIL' ";                                                                                                   //**LP0039
    $sql .= "   and KEY207 = 'USER' ";                                                                                                  //**LP0039
    $sql .= " order by UFILE07 ";                                                                                                       //**LP0039
                                                                                                                                        //**LP0039
    $res = odbc_prepare($conn, $sql);                                                                                                    //**LP0039
    odbc_execute($res);                                                                                                                  //**LP0039
                                                                                                                                        //**LP0039
    echo "<hr />";                                                                                                                      //**LP0039
                                                                                                                                        //**LP0039
    while (($row = odbc_fetch_array($res)) <> false){                                                                                    //**LP0039
                                                                                                                                        //**LP0039
        if (trim($row['LINK07'] == 'Y')){                                                                                               //**LP0039
            echo "<a target='_blank' href='" . trim($row['FILE07']) . "'>" . trim($row['UFILE07']) . "</a>";                            //**LP0039
        }else{                                                                                                                          //**LP0039
            echo "<a target='_blank' href='" . $attachementsFolder . trim($row['FILE07']) . "'>" . trim($row['UFILE07']) . "</a>";      //**LP0039
        }                                                                                                                               //**LP0039
        echo "<br />";                                                                                                                  //**LP0039
                                                                                                                                        //**LP0039
    }                                                                                                                                   //**LP0039
                                                                                                                                        //**LP0039
}                                                                                                                                       //**LP0039
?>
