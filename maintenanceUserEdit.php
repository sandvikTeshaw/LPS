<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUserEdit.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceUserEdit.php application page for editting existing users<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0097     TJS     12/04/2010  re-write userMaintenance for new escalation<br>
 *  LP0018    AG      12/07/2017  Timezone adding and user attributes adding<br>
 *  LP0039    KS      29/03/2018  In the LPS "Register for LPS account" page please add hyperlinks to instruction guidelines (SPIDER 2.0)
 */
/**
 */
global $conn;
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';


if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
	echo "Connection Failed";
}

if ($_SESSION ['email']) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	
	if (! $_COOKIE ["mtp"]) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif ($_COOKIE ["mtp"]) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
}

if( !isset( $action ) ){
    $action = "";
}
?>

(
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>
<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<!-- Web Font -->
<link href="http://fonts.googleapis.com/css?family=Ubuntu:300,400,500" rel="stylesheet" type="text/css">
<script type="text/javascript" src="copysource/jquery.js"></script>
<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
<script type="text/javascript">
	function setEmail( tvalue ){
		
		var emailVar = tvalue.toLowerCase(); 
		emailVar = emailVar.replace(" ", ".");
		this.detailsForm.newEmail.value = emailVar + "@sandvik.com";
	}
	
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
	function deleteGroup( userID, groupID, groupName, requestType ){

				var answer = confirm( "Delete " + groupName + "?" );
				//alert("maintenanceUserDeleteFromGroup.php?action=delete&groupId=" + groupID + "&userId=" + userID);
				if( answer )
					window.open ( "maintenanceUserDeleteFromGroup.php?action=delete&groupID=" + groupID + "&userID=" + userID );
				else
					alert( groupName + " has not been deleted" )

	}
</script>
</head>
<?
//headerFrame ( $_SESSION ['name'] );
include_once 'copysource/header.php';
?><body><?php  


if ($_SESSION['userID']) {
	
	if (! $_SESSION ['classArray'] && ! $_SESSION ['typeArray']) {
		$_SESSION ['classArray'] = get_classification_array ();
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
	//menuFrame ( "MTP" );
	include_once 'copysource/menu.php';
}


if( $_REQUEST['userId'] ){
	
	$superId = get_supervisor_id( $_REQUEST['userId'] );
	$editInfo = user_info_by_id( $_REQUEST['userId']  );
	
	//LP0018 - User profile data and timezone adding
	$sqlUser = "SELECT * FROM HLP05 WHERE ID05 = " . $_REQUEST['userId'];
	$resUser = odbc_prepare ( $conn, $sqlUser );
	odbc_execute ( $resUser );
	$userData = odbc_fetch_array($resUser);
	
	$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	$zoneList = "";
	foreach ($tzlist as $zone) {
	    $zoneList .= "<option ";
	    if($zone == trim($userData['TIME05']))
	        $zoneList .= "SELECTED ";
	    $zoneList .= "value='" . $zone . "'>" . $zone . "</option>";
	}
	
if( $action == "" ){
?>
<center>
<table width=75% cellpadding='0' cellspacing='0'>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td class='title'>User Profile</td>
	</tr>
	<tr>
		<td colspan='4'><hr/></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
</table>
<?php 

	
	$userArray = get_user_list();
	$lastTime = relative_time(trim($editInfo['LOGT05']), false );
?>
<form method='post' action='maintenanceUserEdit.php'>
<table width=75% cellpadding='0' cellspacing='0'>
	<tr>
		<td class='boldCenter' colspan='4'>Last Login:<?php echo $lastTime;?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td class='bold'><font color='red'><b>*</b></font>Name:</td>
		<td><input type='text' id='name' name='name' value='<?php echo trim($editInfo['NAME05']);?>'/></td>
		<td class='bold'><font color='red'><b>*</b></font>Availability:</td>
		<td>
		<select name='availability'>
			<option value='Y'>Available</option>
			<option 
				<?php 
				if( trim($editInfo['AVAL05']) == "N" ){
					echo " SELECTED ";
				}
				?>
			value='N'>Out of Office</option>
		</select>
		</td>
	</tr>
	<tr>
		<td class='bold'><font color='red'><b>*</b></font>Email:</td>
		<td><input type='text' id='email' name='email' value='<?php echo trim($editInfo['EMAIL05']);?>'/></td>
		<td class='bold'>&nbsp; Phone:</td>
		<td><input type='text' name='phone' value='<?php echo trim($userData['PHONE05']);?>'/></td>
	</tr>
	<tr>
		<td class='bold'><font color='red'><b>*</b></font>Supervisor:</td>
		<td>
		<select id='supervisor' name='supervisor'>
			<?php show_user_list($userArray, $superId) ?>
		</select>
		<input type='hidden' name='currentSuper' id='currentSuper' value='<?php echo $superId;?>'/>
		</td>
		<td class='bold'><font color='red'><b>*</b></font>Back-Up:</td>
		<td>
		<select name='backup'>
			<?php show_user_list($userArray, trim($editInfo['BACK05'])) ?>
		</select>
		</td>
	</tr>
	<tr>		
	</tr>
	<tr>
		<td class='bold'><font color='red'><b>*</b></font>TimeZone:</td>
		<td>
		<select id='timezone' name='timezone'>
			<option value=''>Select the TimZone</option>
			<?php echo $zoneList;?>
		</select>
		</td>
		<td class='bold'><font color='red'><b>*</b></font>Company:</td>
		<td>
		<select name='company'>
			<?php list_company_code( trim($editInfo['CODE05']) )?>
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
	           $attrTextSql = "SELECT ID08, TEXT08 FROM HLP08 WHERE ATTR08=" . trim($rowAttr['ID06']) . " AND USER08=" . $_REQUEST['userId']
	                        . " ORDER BY ID08 DESC FETCH FIRST 1 ROW ONLY";
	 
	           $resAttrText = odbc_prepare ( $conn, $attrTextSql);
	           odbc_execute ( $resAttrText );
	           $usrAttText= odbc_fetch_array($resAttrText);
	           ?><td><input type='text' id='attr_<?php echo trim($rowAttr['ID06'])?>' name='attr_<?php echo trim($rowAttr['ID06'])?>' value='<?php echo trim($usrAttText['TEXT08']);?>'/></td><?php 
	       }else{
    	       $attrOptionsSql = "SELECT ID07, STXT07 FROM HLP07 WHERE ATTR07=" . trim($rowAttr['ID06']) . " AND ACTV07=1 AND STXT07 <> '' ORDER BY SORT07";
    	       $resAttrOpts = odbc_prepare ( $conn, $attrOptionsSql);
    	       odbc_execute ( $resAttrOpts );
    	       
    	       $optSelSql = "SELECT OPID08 FROM HLP08 WHERE ATTR08 =" . trim($rowAttr['ID06']) . " AND USER08= " . $_REQUEST['userId']
    	                  . " ORDER BY ID08 DESC FETCH FIRST 1 ROW ONLY";
    	   
    	       $resOptSel = odbc_prepare ( $conn, $optSelSql);
    	       odbc_execute ( $resOptSel );
    	       $optData = odbc_fetch_array($resOptSel);
    	       
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
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='4'><hr/></td>
	</tr>
	<tr>
		<td class='title' colspan='4' align='center'>Administrator</td>
	</tr>
	<tr>
		<td colspan='4'><hr/></td>
	</tr>
	<?php 
	if( $_SESSION['authority'] == "S" ){
	?>
	<tr>
		<td class='bold'>Authority:</td>
		<td>
		<?php 
		$authArray = authority_array();
				?><select name='authority'><?php 
				foreach ( $authArray as $authorities){
					echo "<option ";
						if( $authorities['value'] == trim($editInfo['AUTH05']) ){
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
	<tr>
		<td class='bold'>Password:</td>
		<td><input type='text' name='password' value='<?php echo trim($editInfo['PASS05']);?>'/></td>
	
	<?php 
	}else{
		?>
		<input type='hidden' name='authority' value='<?php echo trim($editInfo['AUTH05']);?>'>
		<input type='hidden' name='password' value='<?php echo trim($editInfo['PASS05']);?>'>
		<?php 
	}
	?>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td colspan='4'>
		<input type='submit' value='Save User' onclick='return validateEntry( <?php echo json_encode($reqAttribIdsArray)?>,<?php echo json_encode($reqAttribTypeArray)?>,<?php echo json_encode($reqAttribNameArray)?>, <?php echo json_encode($reqAttribPosition)?>)'/>
		<input type='hidden' name='action' value='save'/>
		<input type='hidden' name='userId' value='<?php echo $_REQUEST['userId'];?>'/>
		</td>
	</tr>
</table>
<br/>
<br/>
<br/>

<?php 
//$reqAttribIdsArray = array();
//$reqAttribTypeArray = array();
//$reqAttribNameArray = array();
//$reqAttribPosition = array();
//LP0013 Group Section
$sql = "SELECT *
	FROM CIL39
	INNER JOIN CIL40
	ON CIL39.ID39=CIL40.GRUP40 AND USER40 = " . $_REQUEST['userId'];
	$sql .= " ORDER by CIL39.DESC39";
	
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
?>
<table width=75% cellpadding='0' cellspacing='0' border=0>
	
	<tr>
		<td class='title' colspan='2'><hr/></td>
	</tr>
	<tr>
		<td class='title' colspan='2'>User's Groups</td>
	</tr>
	<tr>
		<td class='title' colspan='2'><hr/></td>
	</tr>
	<tr>
    	<td class="bold" align='left'><a href="maintenanceGroupAuthAdd.php?userID=<?php echo $_REQUEST['userId'];?>">Add Group Authentication</a></td>
        <td class="bold">&nbsp;</td>
    </tr>
    <tr>
		<td class='title'>&nbsp;</td>
	</tr>
	<tr>
    	<td class="bold">Group Name</td>
        <td class="bold">Action</td>
    </tr>
	<?php 
	$rowCount = 0;
	while( $row = odbc_fetch_array( $res ) ){
		//LP0013
		$rowCount++;
    	if( $rowCount % 2 ){      
			echo "<TR class='alternate'>";
		}else{
			echo "<TR class=''>";
		}
	?>
    	
        <td width=70%>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row['DESC39'];?></td>
			<td class='center'>
				<a href='maintenanceGroupAuthEdit.php?userID=<?php echo $_REQUEST['userId'];?>&groupID=<?php echo $row['GRUP40'];?>' title="Edit <?php echo  $row['DESC39'];?>">
				<img src='<?php echo $IMG_DIR;?>/edit.gif' border='0'>
				</a>
				<?php 
				if( $_SESSION['authority'] == "S" ){
				?>
					&nbsp;
					<a href='#' onclick='deleteGroup(<?php echo $row['USER40'];?>, <?php echo $row['GRUP40'];?>,  "<?php echo $row['DESC39'];?>", "delete")' title="Delete <?php echo $row['DESC39'];?>">
					<img src='<?php echo $IMG_DIR;?>/delete.gif' border='0'>
					</a>
				<?php 
				}else{
					?>
					&nbsp;
					<a href='#' onclick='deleteGroup(<?php echo $row['USER40'];?>, <?php echo $row['GRUP40'];?>,  "<?php echo $row['DESC39'];?>", "request")' title="Request <?php echo $row['DESC39'];?> be Removed">
					<img src='<?php echo $IMG_DIR;?>/delete.gif' border='0'>
					</a>
					<?php 
				}
				?>
			</td>
    </tr>
	<?php } ?>
</table>

</form>
	<?php 
	}else{
	    
	    $currentUserSql = "SELECT * FROM HLP05 WHERE ID05 ={$_REQUEST['userId']}";
	    $resCurrentInfo = odbc_prepare($conn, $currentUserSql);
	    odbc_execute( $resCurrentInfo);
	    $currentInfo = odbc_fetch_array($resCurrentInfo);
	    
	    
	    
	    $email = strtr($_REQUEST['email'], $GLOBALS['normalizeSaveChars']);
	    $updateHlp05 = "update HLP05 SET NAME05='{$_REQUEST['name']}', EMAIL05='$email', "
					 . " CODE05='{$_REQUEST['company']}', PHONE05='{$_REQUEST['phone']}', AUTH05='{$_REQUEST['authority']}',"
					 . " AVAL05='{$_REQUEST['availability']}', BACK05='{$_REQUEST['backup']}', TIME05='{$_REQUEST['timezone']}'";
					 
					 if( $_REQUEST['password'] ){
					 	$updateHlp05 .= ", PASS05='{$_REQUEST['password']}'";	
					 }
					 
	
					 $updateHlp05 .= " WHERE ID05={$_REQUEST['userId']}";
					 $res = odbc_prepare( $conn, $updateHlp05 );
					 odbc_execute( $res );
	
	//Setup audit inserts			 
					 
	$insert = false;
	$auditInsertSql = "INSERT INTO HLP05A VALUES";
	$date = date('Ymd');
	$time = date('His');
	
	if( trim($currentInfo['NAME05']) != $_REQUEST['name']){
	   $insert = true;
	   $auditInsertSql .= "( {$_REQUEST['userId']}, 'NAME05', '{$_REQUEST['name']}', '{$currentInfo['NAME05']}', $date, '$time', {$_SESSION['userID']} )";   
	}
	if( trim($currentInfo['CODE05']) != $_REQUEST['company']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	       $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'CODE05', '{$_REQUEST['company']}', '{$currentInfo['CODE05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( trim($currentInfo['PHONE05']) != $_REQUEST['phone']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'PHONE05', '{$_REQUEST['phone']}', '{$currentInfo['PHONE05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( trim($currentInfo['EMAIL05']) != $_REQUEST['email']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'EMAIL05', '{$_REQUEST['email']}', '{$currentInfo['EMAIL05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( trim($currentInfo['PASS05']) != $_REQUEST['password']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'PASS05', '{$_REQUEST['password']}', '{$currentInfo['PASS05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( trim($currentInfo['AVAL05']) != $_REQUEST['availability']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'AVAL05', '{$_REQUEST['availability']}', '{$currentInfo['AVAL05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( trim($currentInfo['BACK05']) != $_REQUEST['backup']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'BACK05', '{$_REQUEST['backup']}', '{$currentInfo['BACK05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( trim($currentInfo['AUTH05']) != $_REQUEST['authority']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'AUTH05', '{$_REQUEST['authority']}', '{$currentInfo['AUTH05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( trim($currentInfo['TIME05']) != $_REQUEST['timezone']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'TIME05', '{$_REQUEST['timezone']}', '{$currentInfo['TIME05']}', $date, '$time', {$_SESSION['userID']} )";
	}
	if( $_REQUEST['currentSuper'] != $_REQUEST['supervisor']){
	    if( $insert){
	        $auditInsertSql .= ",";
	    }else{
	        $insert = true;
	    }
	    $auditInsertSql .= "( {$_REQUEST['userId']}, 'SUPR31', '{$_REQUEST['supervisor']}', '{$_REQUEST['currentSuper']}', $date, '$time', {$_SESSION['userID']} )";
	}
	
	
	if( $insert){
	    $resAudit = odbc_prepare( $conn, $auditInsertSql);
	    odbc_execute( $resAudit);

	}
	 
	
	//LP0018 - Modified sql statment to include timezone and maintaining audit file HLP05A
	/*
        $sqlTimezone = "SELECT TIME05 FROM HLP05 WHERE ID05 = ". $userData['ID05'];
        $resTimezone = odbc_prepare($conn, $sqlTimezone);
        odbc_execute( $resTimezone );
        $data = odbc_fetch_array($resTimezone);
        
        if(isset($data['TIME05']) && $data['TIME05'] != '' && trim($data['TIME05']) != $_REQUEST['timezone']){
            $updateHlp05 .= ", TIME05 = '" . $_REQUEST['timezone'] . "'";
            $ID1 = 0;
            $maxSql1 = "SELECT ID05A FROM HLP05A ORDER BY ID05A DESC LIMIT 1";
            $maxRes1 = odbc_prepare($conn, $maxSql1);
            odbc_execute($maxRes1);
            while($maxRow1 = odbc_fetch_array($maxRes1)){
                $ID1 = $maxRow1['ID05A'];
            }
            $ID1++;
            $sql5A = "INSERT INTO HLP05A (ID05A, NAME05A, NTXT05A, OTXT05A, DATE05A, TIME05A, USER05A) " .
                "VALUES(".$ID1.", 'TIME05', '". trim($_REQUEST['timezone']) ."', '".trim($data['TIME05'])."', ".date ( 'Ymd' ).", ".date ( 'his' )."," . trim($_SESSION['userID']) .")";
            
            echo $sql5A . "<hr>";
            
            $res5A = odbc_prepare($conn, $sql5A);
            odbc_execute($res5A);
        }		 
		
		
		echo $updateHlp05 . "<hr>";
		*/
	

		$updateCIL31 = "update CIL31 SET SUPR31={$_REQUEST['supervisor']} WHERE EMPL31={$_REQUEST['userId']}";
		$res31 = odbc_prepare( $conn, $updateCIL31 );
		odbc_execute( $res31 );
		
		
		$userAttributesIDArray = array();
		
		$userAttributeIdsSql = "SELECT ID08, ATTR08, TEXT08, OPID08 FROM HLP08 WHERE USER08 = " . $_REQUEST['userId'];
		$resAttribIds = odbc_prepare( $conn, $userAttributeIdsSql);
		odbc_execute( $resAttribIds);
		
		$aArray[] = "";
		while( $attribIdRow = odbc_fetch_array( $resAttribIds ) ){
		    array_push($userAttributesIDArray, $attribIdRow['ATTR08'] );
		    $aArray[ $attribIdRow['ATTR08'] ]['TEXT'] = trim($attribIdRow['TEXT08']);
		    $aArray[ $attribIdRow['ATTR08'] ]['OPT'] = $attribIdRow['OPID08'];
		}
			
		
		//Walk through all variables to define attributes
		foreach( $_REQUEST as $key => $req ){
		    
		    $update = false;
		    $insert = false;
		    //Check to see if input is attribute type
		    if( substr($key, 0, 5) == "type_"){
		        
		        //Get Attribute Values
		        $attribNum = substr($key, 5 );
		        $attribName = "attr_" . $attribNum;
		        $attribSql = "";
		        $auditSql = "";
		        
		        if (in_array( $attribNum, $userAttributesIDArray )) {
		            $update = true;
		        }else{
		            $insert = true;
		        }

		        //Check if Text attribute
		        if( $_REQUEST[ $key ] == "T" ){
		            		           
		            if( trim($aArray[ $attribNum ]['TEXT'])  != $_REQUEST[ $attribName] ){
    		            if( $insert ){   
    		                $attribSql = "INSERT INTO HLP08 VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08", "ID08", "" ) . ", $attribNum," . $_REQUEST['userId'] . ", '" . $_REQUEST[ $attribName] . "', 0)";
    		                $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_REQUEST['userId'] . ", " . $attribNum . ", '" . $_REQUEST[ $attribName]. "'"
                                      . ", '', 0, 0, " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID'] . ")";
        
    		            }
    		            if( $update ){
    		                $attribSql= "UPDATE HLP08 set TEXT08 = '". $_REQUEST[ $attribName] . "' WHERE ATTR08 = $attribNum AND USER08 =" . $_REQUEST['userId'];
    		                
    		                $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_REQUEST['userId'] . ", " . $attribNum . ", '" . $_REQUEST[ $attribName]
    		                . "', '" . $aArray[ $attribNum ]['TEXT'] . "', 0, 0, " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID'] . ")";
    		                
    		            }
		            }
		            

		        }
		        
		        //Check if Select Option attribute
		        if( $_REQUEST[ $key ] == "S" ){
		            if( trim($aArray[ $attribNum ]['OPT'])  != $_REQUEST[ $attribName] ){
    		            if( $insert ){
    		                $attribSql = "INSERT INTO HLP08 VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08", "ID08", "" ) . ", $attribNum," . $_REQUEST['userId'] . ", '', " . $_REQUEST[ $attribName] .")";
    		                $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_REQUEST['userId'] . ", " . $attribNum . ","
    	                              . "'', ''," . $_REQUEST[ $attribName] . ", 0, " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID'] . ")";
    		            }
    		            if( $update ){
    		                if( $aArray[ $attribNum ]['OPT'] == "" ){
    		                    $lastVal = 0;
    		                }else{
    		                    $lastVal = $aArray[ $attribNum ]['OPT'];
    		                }
    		                
    		                $attribSql= "UPDATE HLP08 set OPID08 = '". $_REQUEST[ $attribName] . "' WHERE ATTR08 = $attribNum AND USER08 =" . $_REQUEST['userId'];
    		                $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_REQUEST['userId'] . ", " . $attribNum . ","
    	                              . "'', ''," . $_REQUEST[ $attribName] . "," . $aArray[ $attribNum ]['OPT'] . ", " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID'] . ")";
    		            }
		            }
		        }
		        
		        $reAttribAction = odbc_prepare( $conn, $attribSql);
		        if( odbc_execute( $reAttribAction)){
		            
		        }else{
		            $handle = fopen("./sqlFailures/sqlFails.csv","a+");
		            fwrite($handle, "745 - maintenanceUserEdit.php," . $attribSql . "\n" );
		            fclose($handle);
		        }
		        
		        $reAttribAuditAction = odbc_prepare( $conn, $auditSql);
		        if( odbc_execute( $reAttribAuditAction) ){
		            
		        }else{
		            $handle = fopen("./sqlFailures/sqlFails.csv","a+");
		            fwrite($handle, "754 - maintenanceUserEdit.php," . $auditSql . "\n" );
		            fclose($handle);
		        }

		        
		    }
		    
		}

		echo "<center>";
		echo "<br><br><b>User Changes Have Been Saved</b>";
	?>
		<meta http-equiv="refresh" content="1;url=maintenanceUser.php"/>
	<?
		
	}
	?>
<?php 
listHyperlinks();					//LP0039
}else{
	?>
	<br/><br/><b>No User has been selected</b>
	
	<a href="javascript:" onclick="history.go(-1); return false">Back</a>
	<?php 
}
?>
</center>
<?php
/*
//LP0018 - User profile enhancement; user attributes maintenance and adding
$ResultHTML = "<tbody><tr><td>Attribute</td><td>Value</td><td style='text-align: center;' >Delete</td></tr>";
$sqlAttrib = "SELECT * FROM HLP08 H8 ".
    "INNER JOIN HLP07 H7 ON H7.ID07 = H8.OPID08 ".
    "INNER JOIN HLP06 H6 ON H6.ID06 = H7.ATTR07 ".
    "WHERE H8.USER08 = " . $userData['ID05'];

$sqlCount = "SELECT COUNT(*) FROM HLP08 H8 ".
    "INNER JOIN HLP07 H7 ON H7.ID07 = H8.OPID08 ".
    "INNER JOIN HLP06 H6 ON H6.ID06 = H7.ATTR07 ".
    "WHERE H8.USER08 = " .  $userData['ID05'];
$resCount = odbc_prepare ( $conn, $sqlCount);
odbc_execute ( $resCount );
$count = db2_fetch_array($resCount);
if($count[0] < 1){

    $ResultHTML .= '<tr><td style="text-align: center;height: 30px;" colspan="3"><a href="profile_add_attrib.php?userID=' . $userData['ID05'] .'" class="button-type-link">ADD User Attribute</a></td></tr>';

} else {
    
    $resAttrib = odbc_prepare ( $conn, $sqlAttrib );
    odbc_execute ( $resAttrib );
    while ($rowAttrib = odbc_fetch_array($resAttrib)){
        $value = "";
        if(trim($rowAttrib['ATYP06']) == 'S'){
            $value = trim($rowAttrib['STXT07']);
        } else {
            $value = trim($rowAttrib['TEXT08']);
        }
    
        $ResultHTML .= <<<HTML
			  <tr><td style="text-align: center;">{$rowAttrib['ATTR06']}</td><td>{$value}</td>
        <td style="text-align: center;"><span class='delete-icon' id='{$rowAttrib['ID08']}'><img src='copysource/images/delete.png' alt='Delete' title='Delete'/></span></td></tr>
HTML;
    
    }   
}


?>
<script type="text/javascript">
$(document).ready(function(){
	$( ".delete-icon" ).each(function(index) {
	    $(this).on("click", function(){
	    	var status = $(this).attr('status');
	    	var statusTxt = status == 1? "Deactivate" : "Activate";
	    	if(confirm("Do you want to delete this User attribute?")){
	    		$("#loader").addClass("show");
	    		var id = $(this).attr('id');

	    		var postString = "method=del_attrib&ID=" + id;
	            $.ajax({
	                type: 'post',
	                url: 'ajax_services.php',
	                data: postString,
	                dataType: 'json',
	                success: function(result) {
	                    $("#loader").html("");
	                    console.log(result);
	                    if (result.CODE == 200) {
		                    $("#"+id).closest('tr').remove();
	                        $(".success").show();
	                        $("#loader").removeClass("show");
	                        $(".success").fadeOut(5000, "swing");
	                    }
	                    else{
	                        $(".error").show();
	                        $(".error").fadeOut(5000, "swing");
	                    }
	                }, error: function() {
	                    $(".error").show();
	                }
	            });
	    		
		    	console.log(id+" : "+status);
	    	}
	    });
	});	
});

</script>
<table width=100% cellpadding='0' cellspacing='0' border=0>
<TR><TD>&nbsp</TD></TR>
<TR><TD>&nbsp</TD></TR>
<tr><td class='titleBig' colspan='3'>User Attributes</td></tr>
<TR><TD colspan='3'><hr></td></TR>
<TR><TD>&nbsp</TD></TR>
<TR><TD width='25%'>&nbsp</TD></TR>
<tr><td style="float: right; margin-right: 15px;"><a href="profile_add_attrib.php?userID=<?php echo $userData['ID05']; ?>" class="button-type-link">ADD User Attribute</a></td></tr>
</table>
<div id="wrapper">
    	
        <div class="container">
        	
            <div class="col-md-8 col-sm-8 col-xs-8">
                    <div class="panel panel-default">
                    <div class="panel-body">
                    <table id="sla-table" class="data-table sla-data">
                        <?php echo $ResultHTML; ?>                         
                    </table>
                    </div>
                </div>
                <div class="clear"></div>
                    <div class="panel panel-default">
                        <div class="panel" style="">
                            <div class="loader" id="loader"></div>
                        </div>
                    </div>
                <div class="clear"></div>
                    <div class="panel panel-default">
                        <div class="panel" style="">
                            <div class="success" id="success" style="display: none;"><p>The Attribute is successfuly removed.</p></div>
                            <div class="error" id="error" style="display: none;"><p>Sorry! Some error occured. Try again!</p></div>
                        </div>
                    </div>
            </div><!--col md,sm 8-->
            <div class="clear"></div>
        </div><!--container-->
</div><!--wrapper-->
<div style="height:150px;"></div>

</body>
</html>
/*
 */
 

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
 
 
 