<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            profile.php<br>
 * Development Reference:   DI868<br>
 * Description:             profile.php allows users to maintain their own profile information<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  DI868A   TS      26/05/2009  Backup enhancement
 *  LP0001   TS      18/11/2013  SQL Problem
 *  LP0018   AG      07/07/2017  Timezone adding and user attributes adding
 *  LP0039   KS      29/03/2018  In the LPS "Register for LPS account" page please add hyperlinks to instruction guidelines (SPIDER 2.0)
 */
/**
 */
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

global $conn, $action, $CONO, $email, $code, $newPass, $availability, $SITE_TITLE;

if( !isset($conn) ){
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
//This is a change
if (isset($conn) ) {

} else {
    echo "Connection Failed";
}
if ( isset($_SESSION['email'] )){
    $userInfo [] = "";
    $userInfo = user_cookie_info( $_SESSION['email'] );
    $_SESSION['userID'] = $userInfo['ID05'];
    $_SESSION['name'] = $userInfo['NAME05'];
    $_SESSION['email'] = $_SESSION['email'];
    //LP0018 - Update Last login
    update_last_login();
    if( !isset($_COOKIE["mtp"])){
        setcookie("mtp",$_SESSION['email'],time()+60*60*24*30);
    }

}elseif ( isset( $_COOKIE["mtp"] ) ){
    $userInfo [] = "";
    $userInfo = user_cookie_info( $_COOKIE["mtp"] );
    $_SESSION['userID'] = $userInfo['ID05'];
    $_SESSION['name'] = $userInfo['NAME05'];
    $_SESSION['email'] = trim($_COOKIE["mtp"]);


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

function validateEntry( attrIds, attrTypes, attrNames, $attrPosition ){
	

	var subEmail = document.getElementById("email").value;
	var subName = document.getElementById("name").value;

	var subTimezone = document.getElementById("timezone");
	var subTimezoneValue = subTimezone.options[subTimezone.selectedIndex].value;

	var subSuper = document.getElementById("super");
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

<!-- *** START D0455 - Change menu and header to new versions -->
<body>
<?

include_once 'copysource/header.php';

//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );


	if( !$_SESSION ['classArray'] ){
	 	$_SESSION ['classArray'] = get_classification_array ();
	}
	if( !$_SESSION ['typeArray'] ){
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
//menuFrame ( $SITENAME );
include_once 'copysource/menu.php';

//*** END D0455 - Change menu and header to new versions

    $userInfo = user_cookie_info( $_SESSION['email'] );
    
    //LP0018 - Timezone field adding
    $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    $zoneList = "";
    foreach ($tzlist as $zone) {
        $zoneList .= "<option ";
        if($zone == trim($userInfo['TIME05']))
            $zoneList .= "SELECTED ";
            $zoneList .= "value='" . $zone . "'>" . $zone . "</option>";
    }
    
    $superId = get_supervisor_id( $_SESSION['userID']);	//D0247 - Added to populate current supervisor
    
    if( $action == "" ){
        $userArray = get_user_list();
        ?>
        <center>
        <form method='post' name='detailsForm' action='profile.php'">
        <table width=75% cellpadding='2' cellspacing='2' border=0>
        <TR><TD>&nbsp;</TD></TR>
        <TR><TD>&nbsp;</TD></TR>
        <tr>
        	<td class='titleBig' colspan='4'>My Profile</td>
        </tr>
        <tr>
		<td colspan='4'><hr/></td>
    	</tr>
    	<tr><td>&nbsp;</td></tr>
    	<tr><td>&nbsp;</td></tr>
    	<!--
    	<tr>  
        <TD class='bold'>Last Login:</TD>
        <TD><?php echo $userInfo['LOGT05'] . " - " . relative_time($userInfo['LOGT05']);?></TD>
        </TR>
        -->
        <TR><TD>&nbsp;</TD></TR>
        <tr>
            <td class='bold'><font color='red'>*</font>Name:</TD>
            <td><input type='text' name='name' id='name' value='<?php echo trim($userInfo['NAME05']);?>'></TD>
            <td class='bold'><font color='red'>*</font>Email:</TD>
            <td><input type='text' name='email' id='email' value='<?php echo trim($userInfo['EMAIL05']);?>'></TD>
        </tr>
        <tr>
       		<td class='bold'><font color='red'>*</font>Company Code:</td>
        <td>
            <select name='code' id='code' class='long'>
                <?php list_company_code( $userInfo['CODE05'] )?>
            </select>
        </td>
        <td class='bold'><font color='red'>*</font>Availability:</td>
        <td>
            <select id='availability' name='availability'>
                <?php list_availability( trim($userInfo['AVAL05']) )?>
            </select>
        </td>
        </tr>
        <tr>
        <td class='bold'><font color='red'>*</font>TimeZone:</td>
        <td>
        	<select id='timezone' name='timezone'>
        		<option value=''>Select the TimeZone</option>
              		<?php echo $zoneList;?>        
        	</select>
        </td>
        <td class='bold'><font color='red'>*</font>Back-up:</td>
        <td>
		<select id='backup' name='backup'>
			<?php show_user_list($userArray, trim($userInfo['BACK05'])) ?>
		</select>
		</td>
        </tr>
         <tr>
			<td class='bold'><font color='red'>*</font>Supervisor:</td>
			<td>
			<select name='super' id='super' class='long'>
				<?php show_user_list($userArray, $superId );?>
			</select>
			<input type='hidden' name='currentSuper' id='currentSuper' value='<?php echo $superId;?>'/>
			</td>
			<td class='bold'>&nbsp;Phone:</td>
			<td><input type='text' id='phone' name='phone' value='<?php echo trim($userInfo['PHONE05'])?>'/></td>
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
	           $attrTextSql = "SELECT ID08, TEXT08 FROM HLP08 WHERE ATTR08=" . trim($rowAttr['ID06']) . " AND USER08=" . $_SESSION['userID'];
	           $resAttrText = odbc_prepare ( $conn, $attrTextSql);
	           odbc_execute ( $resAttrText );
	           $usrAttText= odbc_fetch_array($resAttrText);
	           ?><td><input type='text' id='attr_<?php echo trim($rowAttr['ID06'])?>' name='attr_<?php echo trim($rowAttr['ID06'])?>' value='<?php echo trim($usrAttText['TEXT08']);?>'/></td><?php 
	       }else{
    	       $attrOptionsSql = "SELECT ID07, STXT07 FROM HLP07 WHERE ATTR07=" . trim($rowAttr['ID06']) . " AND ACTV07=1 AND STXT07 <> '' ORDER BY SORT07";
    	       $resAttrOpts = odbc_prepare ( $conn, $attrOptionsSql);
    	       odbc_execute ( $resAttrOpts );
    	       
    	       $optSelSql = "SELECT OPID08 FROM HLP08 WHERE ATTR08 =" . trim($rowAttr['ID06']) . " AND USER08= " . $_SESSION['userID'];
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
		<tr><td>&nbsp;</td></tr>
		<td colspan='4'><hr/></td>
    	</tr>
    	<tr><td>&nbsp;</td></tr>
        <tr>
        <td class='bold'>New Password:</td>
        <td><input type='password' name='newPass' value=''></td>
        </tr>
        <tr>
        <td class='bold'>Confirm New Password:</td>
        <td><input type='password' name='confNewPass' value=''></td>
        </tr>
        	
        <tr><TD>&nbsp;</TD></TR>
        <input type='hidden' name='action' value='saveProfile'>
        <tr><TD>&nbsp;</TD>
        <TD>
        	<input type='submit' value='Save Profile' onclick='return validateEntry( <?php echo json_encode($reqAttribIdsArray)?>,<?php echo json_encode($reqAttribTypeArray)?>,<?php echo json_encode($reqAttribNameArray)?>, <?php echo json_encode($reqAttribPosition)?>)'>
      	</TD>
      	</TR>
        </table>
        </form>
        <table width=50% cellpadding='0' cellspacing='0'>
			
			<?php 
            $acmCount = count_records( FACSLIB, "CIL13L02", "WHERE CONO13 = '$CONO' AND ACTM13=" . $_SESSION['userID'] );
            $opsCount = count_records( FACSLIB, "CIL13L01", "WHERE CONO13 = '$CONO' AND OPMG13=" . $_SESSION['userID'] );


            if( $acmCount > 0 || $opsCount > 0 ){
            ?>
            <tr>
            <td class='titleBig' colspan='3'>My Responsibilities</td>
            </tr>
            <tr>
                <td colspan='3'><hr></td>
            </tr>
            <?php 
            }
                
            if( $acmCount > 0 ){
                list_my_responsibilities( $_SESSION['userID'], $userArray, "actm" );
            }
            if( $opsCount > 0 ){
                list_my_responsibilities( $_SESSION['userID'], $userArray, "ops" );
            }
            ?>

        </table>
        </center>
		<?php         
		listHyperlinks();					//LP0039
    }else{
      
        $currentUserSql = "SELECT * FROM HLP05 WHERE ID05 ={$_SESSION['userID']}";
        $resCurrentInfo = odbc_prepare($conn, $currentUserSql);
        odbc_execute( $resCurrentInfo);
        $currentInfo = odbc_fetch_array($resCurrentInfo);
        
        //DI868B - Added $_REQUEST to variables is sql to ensure correct values are obtained.
        $email = strtr($_REQUEST['email'], $GLOBALS['normalizeSaveChars']);
        $updateSql= "update HLP05 SET NAME05='{$_REQUEST['name']}', EMAIL05='" .trim($email)."', "
        . " CODE05='{$_REQUEST['code']}', PHONE05='{$_REQUEST['phone']}',"
        . " AVAL05='{$_REQUEST['availability']}', BACK05='{$_REQUEST['backup']}', TIME05='{$_REQUEST['timezone']}', LOGT05=now()";
        
        if( $newPass ){
            $updateSql .= ", PASS05='" . trim($newPass) . "'";
        }

        $updateSql .= " WHERE ID05=" . $_SESSION['userID'];

        $res = odbc_prepare( $conn, $updateSql );
        odbc_execute( $res );
        
        
        //Setup audit inserts
        
        $insert = false;
        //$startInsertId = get_next_unique_id ( FACSLIB, "HLP05A", "ID05A", "" ) - 1;
        $startInsertId = $_SESSION['userID'];
        $auditInsertSql = "INSERT INTO HLP05A VALUES";
        $date = date('Ymd');
        $time = date('His');
        
        if( trim($currentInfo['NAME05']) != $_REQUEST['name']){
            $insert = true;
            $auditInsertSql .= "( {$_SESSION['userID']}, 'NAME05', '{$_REQUEST['name']}', '{$currentInfo['NAME05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( trim($currentInfo['CODE05']) != $_REQUEST['code']){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'CODE05', '{$_REQUEST['code']}', '{$currentInfo['CODE05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( trim($currentInfo['PHONE05']) != $_REQUEST['phone']){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'PHONE05', '{$_REQUEST['phone']}', '{$currentInfo['PHONE05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( trim($currentInfo['EMAIL05']) != trim($email)){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'EMAIL05', '" . trim($email) . "', '{$currentInfo['EMAIL05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( trim($currentInfo['PASS05']) != $newPass && $newPass != "" ){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'PASS05', '{$newPass}', '{$currentInfo['PASS05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( trim($currentInfo['AVAL05']) != $_REQUEST['availability']){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'AVAL05', '{$_REQUEST['availability']}', '{$currentInfo['AVAL05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( trim($currentInfo['BACK05']) != $_REQUEST['backup']){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'BACK05', '{$_REQUEST['backup']}', '{$currentInfo['BACK05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( trim($currentInfo['TIME05']) != $_REQUEST['timezone']){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'TIME05', '{$_REQUEST['timezone']}', '{$currentInfo['TIME05']}', $date, '$time', {$_SESSION['userID']} )";
        }
        if( $_REQUEST['currentSuper'] != $_REQUEST['super']){
            if( $insert){
                $auditInsertSql .= ",";
            }else{
                $insert = true;
            }
            $auditInsertSql .= "( {$_SESSION['userID']}, 'SUPR31', '{$_REQUEST['super']}', '{$_REQUEST['currentSuper']}', $date, '$time', {$_SESSION['userID']} )";
        }
        
        
        if( $insert){
            $resAudit = odbc_prepare( $conn, $auditInsertSql);
            odbc_execute( $resAudit);
            
        }
        

        //D0247 - Added Supervisor Logic
	     if( count_records ( FACSLIB, "CIL31", " WHERE EMPL31 = " . $_SESSION['userID'] ) > 0 ){

	    	$cil31Update = "UPDATE CIL31 SET SUPR31=" . $_REQUEST['super'] . " WHERE EMPL31 = " . $_SESSION['userID'];
	    	$res31 = odbc_prepare ( $conn, $cil31Update );
	    	odbc_execute ( $res31 );


	    }else{
	    	$cil31Update = "INSERT INTO CIL31 VALUES( " .  $_REQUEST['super'] . "," .  $_SESSION['userID'] . ")";
	    	$res31 = odbc_prepare ( $conn, $cil31Update );
	    	odbc_execute ( $res31 );

	    }
        
	    if( isset( $_REQUEST['super'] ) ){
    	   $_SESSION['superId'] = $_REQUEST['super'];
	    }

    	$userAttributesIDArray = array();
    	
    	$userAttributeIdsSql = "SELECT ID08, ATTR08, TEXT08, OPID08 FROM HLP08 WHERE USER08 = " . $_SESSION['userID'];
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
    	                    $attribSql = "INSERT INTO HLP08 VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08", "ID08", "" ) . ", $attribNum," . $_SESSION['userID']. ", '" . $_REQUEST[ $attribName] . "', 0)";
    	                    $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_SESSION['userID']. ", " . $attribNum . ", '" . $_REQUEST[ $attribName]. "'"
			    . ", '', 0, 0, " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID'] . ")";
			    
    	                }
    	                if( $update ){
    	                    $attribSql= "UPDATE HLP08 set TEXT08 = '". $_REQUEST[ $attribName] . "' WHERE ATTR08 = $attribNum AND USER08 =" . $_SESSION['userID'];
    	                    
    	                    $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_SESSION['userID']. ", " . $attribNum . ", '" . $_REQUEST[ $attribName]
    	                    . "', '" . $aArray[ $attribNum ]['TEXT'] . "', 0, 0, " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID']. ")";
    	                    
    	                }
    	            }
    	            
    	            
    	        }
    	        
    	        //Check if Select Option attribute
    	        if( $_REQUEST[ $key ] == "S" ){
    	            if( trim($aArray[ $attribNum ]['OPT'])  != $_REQUEST[ $attribName] ){
    	                if( $insert ){
    	                    $attribSql = "INSERT INTO HLP08 VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08", "ID08", "" ) . ", $attribNum," . $_SESSION['userID']. ", '', " . $_REQUEST[ $attribName] .")";
    	                    $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_SESSION['userID']. ", " . $attribNum . ","
	       			         . "'', ''," . $_REQUEST[ $attribName] . ", 0, " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID']. ")";
    	                }
    	                if( $update ){
    	                    if( $aArray[ $attribNum ]['OPT'] == "" ){
    	                        $lastVal = 0;
    	                    }else{
    	                        $lastVal = $aArray[ $attribNum ]['OPT'];
    	                    }
    	                    
    	                    $attribSql= "UPDATE HLP08 set OPID08 = '". $_REQUEST[ $attribName] . "' WHERE ATTR08 = $attribNum AND USER08 =" . $_SESSION['userID'];
    	                    $auditSql = "INSERT INTO HLP08A VALUES ( " . get_next_unique_id ( FACSLIB, "HLP08A", "ID08A", "" ) . ", " . $_SESSION['userID']. ", " . $attribNum . ","
	               . "'', ''," . $_REQUEST[ $attribName] . "," . $aArray[ $attribNum ]['OPT'] . ", " . date ( 'Ymd' ) . ", ".date ( 'his' ) . ", " . $_SESSION['userID']. ")";
    	                }
    	            }
    	        }
                
    	        if( $_SESSION['userID'] == 1021 ){
    	            echo $attribSql. "<hr>";
    	            echo $auditSql. "<hr>";
    	        }
    	        
    	        if( isset( $attribSql ) && $attribSql != "" ){
        	        $reAttribAction = odbc_prepare( $conn, $attribSql);
        	        odbc_execute( $reAttribAction);
    	        }
    	        
    	        if( isset( $auditSql ) && $auditSql != "" ){
        	        $reAttribAuditAction = odbc_prepare( $conn, $auditSql);
        	        odbc_execute( $reAttribAuditAction);
    	        }
    	        
    	        
    	    }
    	    
    	}
    	

        //reset session vars to ensure consistency of changes in profile

        $userInfo = user_info_by_id( $_SESSION['userID'] );
        $_SESSION['userID'] = $userInfo['ID05'];
        $_SESSION['name'] = $userInfo['NAME05'];
        $_SESSION['email'] = $userInfo['EMAIL05'];
        $_SESSION ['companyCode'] = $userInfo ['CODE05'];
        $_SESSION ['authority'] = $userInfo ['AUTH05'];

        //setcookie("mtp",$_SESSION['email'],time()+60*60*24*30);


        echo "<center>";
        echo "<br><br><b>Your profile has been updated</b>";
        echo "</center>";

        ?><meta http-equiv="refresh" content="2;url=index.php"/><?php

    }

?>
<?php 
/*
//LP0018 - User profile enhancement; user attributes maintenance and adding
$ResultHTML = "<tbody><tr><td>Attribute</td><td>Value</td><td style='text-align: center;' >Delete</td></tr>";
        $sqlAttrib = "SELECT * FROM HLP08 H8 ".
        "INNER JOIN HLP07 H7 ON H7.ID07 = H8.OPID08 ".
        "INNER JOIN HLP06 H6 ON H6.ID06 = H7.ATTR07 ".
        "WHERE H8.USER08 = ". $_SESSION['userID']; 
        $sqlCount = "SELECT COUNT(*) FROM HLP08 H8 ".
            "INNER JOIN HLP07 H7 ON H7.ID07 = H8.OPID08 ".
            "INNER JOIN HLP06 H6 ON H6.ID06 = H7.ATTR07 ".
            "WHERE H8.USER08 = " . $_SESSION['userID'];
        $resCount = odbc_prepare ( $conn, $sqlCount);
        odbc_execute ( $resCount );
        
        $count = odbc_fetch_array($resCount);
if($count[0] < 1){
    
    $ResultHTML .= '<tr><td style="text-align: center;height: 30px;" colspan="3"><a href="profile_add_attrib.php" class="button-type-link">ADD User Attribute</a></td></tr>';
    
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
<tr><td style="float: right; margin-right: 15px;"><a href="profile_add_attrib.php" class="button-type-link">ADD User Attribute</a></td></tr>
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
*/
?>
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