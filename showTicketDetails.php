<?
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
/**
 * System Name:             Logistics Process Support
 * Program Name:            showTicketDetails.php<br>
 * Development Reference:   DI868<br>
 * Description:             showTicketDetails.php retrieves ticket details, attributes, supporting information, related information and history
 *                          and displays.  This page also has validation built in to ensure user entries are valid<br>
 *                          <b>Note: This page has complex parts, before modification ensure that a firm understanding of the modification
 *                          is acquired</b><br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  DI868A      TS    29/05/2009    Backup enhancement<br>
 *  DI932       TS    30/07/2009    Global Retunrs enhancement<br>
 *  D0301		TS	  20/03/2011	LPS Performance issues
 *  D0455	    TS    16/11/2011    Modified JS to meet standards
 *  D0341		TS	  14/08/2013    Changes for PFC and Outbound checklists
 *  i-2779747 	TS    14/05/2014    Fix after D0341 deployment
 *  D0539 		TS 	  06/06/2014 	Pricing Contacts Workflow
 *  LP0002      IS    13/08/2015
 *  LP0004      IS    14/11/2015
 *	LP0013		IS	  21/05/2016
 *  LP0016      AG    10/12/2016    Outbound Planner to be added to all Global Process Support Ticket Types
 *  LP0006      TS    06/03/2017    Related information to be added to GOP tickets
 *  LP0018      AG    07/07/2017    User last login updation
 *  r-5097616   TS    07/12/2017    Fix for incorrect Buyer information in Related Information
 *  LP0034      KS    23/01/2018    Private Message Functionality
 *  LP0033      KS    05/04/2018    Change to LPS out of office functionality (SPIDER 2.0)
 *  LP0036      KS    16/04/2018    Add new button in LPS "Logistics Complete" to record action timestamp (SPIDER 2.0)
 *  LP0041      KS    14/05/2018    Add Item Type, Class and Group Major to Related Information section in LPS Expedite tickets.
 *  LP0029      TS    31/05/2018    MassUpload - Parent / Child Ticket relationships
 *  LP0044      AD    28/08/2018    Add Buttons to Queue - Logistics Complete & Send to Pricing
 *  LP0055      AD    13/03/2019    GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0068      AD    24/04/2019    GLBAU-16824_LPS Vendor Change
 *  LP0078      AD    31/05/2019   fix CIL01OA ticket transfer audit file is showing false transfers
 *  LP0054      AD    12/06/2019    LP0054 - LPS - Create "Assign to ____" Buttons
 *  LP0082    AD      18/09/2019  Amendment / enhancement to Vender change LPS ticket
 *  lp0087     AD     21/10/2019    Button assign to inventory Planner
 *  LP0089     AD     30/10/2019  Three bugs in relation to the authorities for Clas 5 Type 75 tickets [p-6385089]
 *
 */
/**
 */


global $conn, $email, $password, $SITE_TITLE, $mtpUrl, $IMG_DIR, $ALTERNATE_COLOR, $SITENAME, $class, $type, $prty, $stat;

include 'copysource/config.php';
include 'copysource/functions.php';
include 'copysource/superFunctions.php';
include 'copysource/shipmentFunctions.php'; //LP0006 - Added for shipment functions connection
include '../common/copysource/global_functions.php';


setcookie("mtp", "", time()-3600);

if (isset($conn)) {
    
}else{
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_connect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if ($conn) {
    
} else {
    echo "Connection Failed";
}

set_time_limit( 300 );

if ( isset( $epass ) ) {

    $password = trim(base64_decode( $epass ));
    $userInfo [] = "";
    $userInfo = userInfo( $email, $password );
    $_SESSION ['userID'] = trim($userInfo ['ID05']);
    $_SESSION ['name'] = trim($userInfo ['NAME05']);
    $_SESSION ['companyCode'] = trim($userInfo ['CODE05']);
    $_SESSION ['authority'] = trim($userInfo ['AUTH05']);
    $_SESSION ['email'] = trim($email);
    $_SESSION ['password'] = trim($password);
    
    setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
}elseif( isset($_SESSION ['email'])) {
    
    $userInfo [] = "";
    //D0281 - Changed function
 
    $userInfo = user_cookie_info_no_deletes( $_SESSION ['email'] );
    $_SESSION ['userID'] =      trim($userInfo ['ID05']);
    $_SESSION ['name'] =        trim($userInfo ['NAME05']);
    $_SESSION ['companyCode'] = trim($userInfo ['CODE05']);
    $_SESSION ['email'] =       trim($_SESSION ['email']);
    $_SESSION ['authority'] =   trim($userInfo ['AUTH05']);
    
    
    if (!isset($_COOKIE ["mtp"])) {
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }
    
}elseif( isset( $_REQUEST['email'])){
    
    if( $from == "sfRequest" ){
        $userInfo [] = "";
        //D0281 - Changed function
        
        $userInfo = user_cookie_info_no_deletes( $_REQUEST ['email'] );
        $_SESSION ['userID'] =      trim($userInfo ['ID05']);
        $_SESSION ['name'] =        trim($userInfo ['NAME05']);
        $_SESSION ['companyCode'] = trim($userInfo ['CODE05']);
        $_SESSION ['email'] =       trim($userInfo ['EMAIL05']);
        $_SESSION ['authority'] =   trim($userInfo ['AUTH05']);
        
        
        if (!isset($_COOKIE ["mtp"])) {
            setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
        }
    }
    
}


if( !isset($_SESSION['userID']) ){
    
    error_mssg( "NONE");
    die();
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<script type="text/javascript" src="copysource/js/ajax.js"></script>
<script src='./copysource/js/jquery-3.1.1.min.js' type='text/javascript'></script>
<link href='./copysource/css/jquery-ui.min.css' rel='stylesheet' type='text/css'>
<script src='./copysource/js/jquery-ui.min.js' type='text/javascript'></script>

<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(copysource/styles.css);
@import url(copysource/custom.css);
-->
</style>
<script type="text/javascript">

var ajax = new Array();

function checkOrderNumber( oNumber, attribNumber, type, currentVal ){
    if( currentVal != oNumber ){
        window.open( 'validateOrder.php?orderNumber=' + oNumber + '&attrib=' + attribNumber + '&type=' + type,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
    }else{

        eval( "this.SODP_" + attribNumber + ".style.display='block'" );
        eval( "this.SODP_INVALID_" + attribNumber + ".style.display='none'" );
    }
}
function checkPartNumber( pNumber, attribNumber, type, currentPart ){
    if( currentPart != pNumber ){
        window.open( 'validatePart.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
    }else{
        eval( "this.PART_" + attribNumber + ".style.display='block'" );
        eval( "this.PART_INVALID_" + attribNumber + ".style.display='none'" );
    }
}
function submitForm( parm ){


    if( parm == "resolve" && document.getElementById('actionResponse').value == "" ){
        alert( "You must provide details before resolving issue" )
        return false;
    }
    if( parm == "resolve" ){
    	document.getElementById('STAT01').value = "5";

    }
    if( parm == "post" ){

        var action = 'updateIssue.php';
        
    	document.getElementById('STAT01').value = "1";
    	document.getElementById("detailsForm").action = action;

    }
    if( parm == "assign" ){
        if( document.getElementById('RSID01').value == "" || document.getElementById('RSID01').value == 0 ){
            alert( "You must select a resource" )
            return false;
        }
        document.getElementById('STAT01').value = "assign";
    }

    if( parm == "pfcComplete" ){
    	document.getElementById('STAT01').value = "pfcComplete";
    }

    //D0539 - Added topricing for pricing flow change
    if( parm == "topricing" ){
    	document.getElementById('STAT01').value = "topricing";
    	document.detailsForm.action = 'updateIssue.php'; //**LP0044_AD
    }

    //LS0002 - DRP workflow issues
    if( parm == "drpComplete" ){
    	document.getElementById('STAT01').value = "drpComplete";
    }
    //LS0002 - DRP workflow issues
    if( parm == "obpComplete" ){
    	document.getElementById('STAT01').value = "obpComplete";
    }

    if( parm == "priComplete" ){									//**LP0036
    	document.getElementById('STAT01').value = "priComplete";	//**LP0036
    }				
    if( parm == "resChild" ){			
        
		var action = 'multiUpdate.php';
        
    	document.getElementById('STAT01').value = "1";
    	document.getElementById("detailsForm").action = action;
    }													//**LP0029
    //************************************** LP0054_AD START **************************************************************
    if( parm == "totsd" ){//LP0054_AD
    	document.getElementById('STAT01').value = "totsd";//LP0054_AD
    }//LP0054_AD
    if( parm == "topfc" ){//LP0054_AD
    	document.getElementById('STAT01').value = "topfc";//LP0054_AD
    }//LP0054_AD
    if( parm == "toobp" ){//LP0054_AD
    	document.getElementById('STAT01').value = "toobp";//LP0054_AD
    }//LP0054_AD
    if( parm == "tofreight" ){//LP0054_AD
    	document.getElementById('STAT01').value = "tofreight";//LP0054_AD
    }//LP0054_AD
    if( parm == "tobuyer" ){//LP0054_AD
    	document.getElementById('STAT01').value = "tobuyer";//LP0054_AD
    }//LP0054_AD
    if( parm == "torequester" ){//LP0054_AD
    	document.getElementById('STAT01').value = "torequester";//LP0054_AD
    }//LP0054_AD
    if( parm == "towar" ){//LP0054_AD
    	document.getElementById('STAT01').value = "towar";//LP0054_AD
    }//LP0054_AD
    if( parm == "tosrc" ){//LP0054_AD
    	document.getElementById('STAT01').value = "tosrc";//LP0054_AD
    }//LP0054_AD
    if( parm == "toip" ){//LP0087_AD
    	document.getElementById('STAT01').value = "toip";//LP0087_AD
    }//LP0087_AD
    
    //************************************** LP0054_AD END   **************************************************************
 
// PAKOZAK workaround due to i-6689752 START:

//    var xhttp = new XMLHttpRequest();//lp0078
//    xhttp.onreadystatechange = function() {//lp0078
//      if (this.readyState == 4 && this.status == 200) {//lp0078
//        if(this.responseText=="ok") 
            onAjaxAnswer();//lp0078
//           else {alert("Update Error!");//lp0078
//            document.getElementById("warningRow").style.display="table-row";//lp0078
//            }//lp0078
//      }//lp0078
//    };//lp0078
//    req="ajaxUpdateCheck.php?ticketnr="+ticketnr+"&timestamp="+timestamp;//lp0078
//    xhttp.open("GET", req, true);//lp0078
//    xhttp.send();//lp0078

// PAKOZAK workaround due to i-6689752 END.

}//lp0078
function onAjaxAnswer(){//lp0078 
    this.detailsForm.submit();
    return true;
}
function showImpact(){
    if( this.document.detailsForm.CHCE01.options[this.document.detailsForm.CHCE01.selectedIndex].value == "N" ){
        this.IMPACT.style.display='table-row';
    } else {
        this.IMPACT.style.display='none';
        this.document.detailsForm.IMPT01.value = "";
    }

}
function checkShowResolved(){

   
    var amChoice = this.document.detailsForm.KEY201.value;

    if(  amChoice == "N/A" || amChoice == "" ){
        this.RESOLVED.style.display='none';
    }else{
        this.RESOLVED.style.display='block';
    }
}
function checkShowPFCComplete(){

    var pfcChoice = this.document.detailsForm.KEY101.value;

    if( pfcChoice == "N/A" || pfcChoice == ""  ){
        this.PFCComplete.style.display='none';
    }else{
        this.PFCComplete.style.display='block';
    }
}
function vendorEmail(){
    if( this.VENDOREMAIL.style.display == "block" ){

      this.VENDOREMAIL.style.display='none';

      document.getElementById('VEND_EMAIL').value = "";
      document.getElementById('VEND_INFO').value = "";
      document.getElementById('VEND_CONT').value = "";

   }
   else{
      this.VENDOREMAIL.style.display='block';
   }
}

function copyToCabonCopy(){

    var addCC = document.getElementById('ccList').value;
    var currentCC = addCC + "," + this.detailsForm.ccSelected.value;

    this.detailsForm.ccSelected.value = currentCC;

    document.getElementById('ccList').value = "";
    document.getElementById('CC_FILTER').value = "";
    document.getElementById('CC_FILTER').focus();

}
function showCarbonCopy(){

    if( this.carbonCopy.style.display == "block" ){
        this.carbonCopy.style.display='none';
    } else {
        this.carbonCopy.style.display='block';
    }
}
function setDesnNumber( desn ){

    document.getElementById('DESN_NUMBER').value = desn;

}

function setCustomer( customerNumber ){

    document.getElementById('CUSTOMER_NUMBER').value = customerNumber;
}

function checkCustomerAndSequence( sequence, attribNumber ){
   var customer =  this.detailsForm.CUSTOMER_NUMBER.value;
   window.open( 'validateCustomer.php?customerNumber=' + customer + '&sequence=' + sequence+'&attrib=' + attribNumber,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
}

function createUserList( index ){
	var obj = document.getElementById('RSID01');
	eval(ajax[index].response); // Executing the response from Ajax as Javascript code
	obj = 0;
}
function ccListFocus(){
	if( document.getElementById('CC_FILTER').value == "4 Charaters of Resource Name"){
		document.getElementById('CC_FILTER').value = "";
	}
}

function ccListKeyUp(){

			var resourceInput = document.getElementById('CC_FILTER').value;


		if( document.getElementById('CC_FILTER').value.length == 4 ){

			var fourCharName = document.getElementById('CC_FILTER').value;

			document.getElementById('ccList').options.length = 0;


	        var index = ajax.length;
	        ajax[index] = new sack();

	        ajax[index].requestFile = 'createUserList.php?idvalue=email&resourceInput=' + fourCharName; // Specifying which file to get

	        ajax[index].onCompletion = function(){
	        	ccUserList(index);
	         };   // Specify function that will be executed after file has been found

	        ajax[index].runAJAX();      // Execute AJAX function

	        document.getElementById('CC_FILTER').value = "";
		}

}

function ccUserList( index ){
	var obj = document.getElementById('ccList');
	eval(ajax[index].response); // Executing the response from Ajax as Javascript code
	obj = 0;
}


//D0341***************
//This function enables and disables children question selections
//Receives 2 Parms, Array of children and this
function enableDisableChildren( children, parentSelValue, childType, callParentType ){

	var ch = children.split( ',' );		                                        //split up array of children sent based on parent
	var chType = childType.split( ',' );	 										//split up array of children Types sent based on parent
	var chDesn;
	var radioOptionName = "";
	var prntVal = "";

	if( callParentType == "SEL" ){

		prntVal = parentSelValue.options[parentSelValue.selectedIndex].value;	//Get value selected from parent

	}else if( callParentType == "RAD" ){

		prntVal = parentSelValue.value;

	}



	 //Read through array anc compare chDesn and parentValue Selected
	 for (i=0; i < ch.length; i++){
		var childDepnName = 'depn_' + ch[i].toString();							//Build names from dynamic content and convert to string
	 	var childQuestName = 'quest_' + ch[i].toString();						//Build names from dynamic content and convert to string
	 	chDesn = document.getElementById(childDepnName).value;					//Get value of required Child Desn


		if( chType[i] == "SEL"){
    		  if( prntVal.toString() == chDesn.toString() ){
    			  document.getElementById(childQuestName).disabled = false;			//enables box

    		  }else{
    			  document.getElementById(childQuestName).disabled = true;			//disables box
    			  document.getElementById(childQuestName).value = "";
    		  }

		}else if( chType[i] == "RAD" ){

			var radioCounterName = 'quest_' + ch[i] + '_radioCounter';
			var radioCounter = document.getElementById(radioCounterName).value;

			for( rc=1; rc <= radioCounter; rc++ ){
				radioOptionName = 'quest_' + ch[i] + '_rad_' + rc;

				if( prntVal.toString() == chDesn.toString() ){
    			    document.getElementById( radioOptionName ).disabled = false;
				}else{
					document.getElementById( radioOptionName ).disabled = true;
					document.getElementById( radioOptionName ).checked = false;
				}
			}
		}else if( chType[i] == "TXT" ){

			var childQuestTxtCounterName = 'quest_txtCount_' + ch[i].toString();	//Build names from dynamic content and convert to string
			var childQuestTxtTrName = 'quest_txt_tr' + ch[i].toString();				//Build names from dynamic content and convert to string
			if( document.getElementById(childQuestTxtCounterName).value = "1" ){

				if( prntVal.toString() == chDesn.toString() ){
				    eval( "this." + childQuestTxtTrName + ".style.display='table-row'" );
				}else{
					document.getElementById(childQuestName).value = "";
					eval( "this." + childQuestTxtTrName + ".style.display='none'" );
				}
			}


		}

	}

}

function checkRequired( section, drp, clss ){

	var sec = section.toString();
	var elementName = "";
	var missing;
	var sessionauthority = <?php echo '"' . $_SESSION['authority'] . '"'; ?> ;		//**LP0036
	
	if( sec == "1" ){
		   var array = document.getElementById('pfcArray').value
	}else if( sec == "2" ){
		   var array = document.getElementById('plannerArray').value
	}

	var arr = array.split( ',' );

	
	for (a=0; a < arr.length; a++){
		elementName = "quest_" + arr[a];

	    if( document.getElementById( elementName ).value == "" && document.getElementById( elementName ).disabled != true ){
	    	   missing = true;
	    }
	}

	//**LP0036  if( !drp && clss != 11 ){

    //**LP0036  	if( missing ){
    //**LP0036  		this.PFCComplete.style.display='none';
    //**LP0036  	}else{
    //**LP0036  		this.PFCComplete.style.display='block';
    //**LP0036  	}
	//**LP0036  }else if( clss == 11 ){
	//**LP0036  	if( missing ){
    //**LP0036  		this.RESOLVED.style.display='none';
    //**LP0036  	}else{
    //**LP0036  		this.RESOLVED.style.display='block';
    //**LP0036  	}
		
	//**LP0036  }else{

	//**LP0036  	if( missing ){
    //**LP0036  		this.DRPComplete.style.display='none';
    //**LP0036  	}else{
    //**LP0036  		this.DRPComplete.style.display='block';
    //**LP0036  	}

	//**LP0036  }

	
	if (sessionauthority != 'S'){										//**LP0036
		if (clss == 11){												//**LP0036
			if( missing ){												//**LP0036
	    		this.RESOLVED.style.display='none';						//**LP0036
	    	}else{														//**LP0036
	    		this.RESOLVED.style.display='block';					//**LP0036
	    	}															//**LP0036
		}																//**LP0036
    }																	//**LP0036

	if (((clss == 3) || (clss == 8)) && (sec == "2")){				//**LP0036
		if( missing ){												//**LP0036
    		this.DRPComplete.style.display='none';					//**LP0036
    	}else{														//**LP0036
    		this.DRPComplete.style.display='block';					//**LP0036
    	}															//**LP0036
	}																//**LP0036
	
	if (clss == 5){													//**LP0036
		if( missing ){												//**LP0036
			this.PriComplete.style.display='none';					//**LP0036
		}else{														//**LP0036
			this.PriComplete.style.display='block';					//**LP0036
		}															//**LP0036
	}																//**LP0036

	if ((clss == 3) && (sec == "1")){								//**LP0036
		if( missing ){												//**LP0036
    		this.PFCComplete.style.display='none';					//**LP0036	
    	}else{														//**LP0036
    		this.PFCComplete.style.display='block';					//**LP0036
    	}															//**LP0036
	}																//**LP0036
        
}

function selAllCheck(){

	var chk_arr =  document.getElementsByName("ticketIds[]");
	var chklength = chk_arr.length;   

	for(k=0;k< chklength;k++)
	{
	    chk_arr[k].checked = true;
	}

	this.checkAll.style.display='none';	
	this.unChkAll.style.display='block';	
	
}
function uncheckAll(){

	var chk_arr =  document.getElementsByName("ticketIds[]");
	var chklength = chk_arr.length;   

	for(k=0;k< chklength;k++)
	{
	    chk_arr[k].checked = false;
	}

	this.checkAll.style.display='block';	
	this.unChkAll.style.display='none';	
	
}
function onLoad(){//lp0082_ad
	<?php 
	if ( !isset($_REQUEST['TYPE01'])){//lp0082_ad
	    
    	if( isset( $_REQUEST['ID01'] ) ){
    	    if( $_REQUEST['ID01'] != "" ){
        	   $temp=(get_base_ticket_details($_REQUEST['ID01']));//lp0082_ad
        	   $_REQUEST['TYPE01']=$temp['TYPE01'];//lp0082_ad
    	    }
    	}
    }//lp0082_ad
	
    if(isset( $_REQUEST['TYPE01'] ) && $_REQUEST['TYPE01'] ==133){?> //lp0082_ad

		this.detailsForm.text11.parentElement.parentElement.style.display = 'none';//lp0082_ad
		//this.detailsForm.sprt1.style.display = 'none';//lp0082_ad		
		if(this.detailsForm.drop13.selectedIndex!=2){//lp0082_ad
			this.detailsForm.drop14.parentElement.parentElement.style.display = 'none';//lp0082_ad
			this.detailsForm.drop15.parentElement.parentElement.style.display = 'none';//lp0082_ad

			}	//lp0082_ad
	<?php }?>	//lp0082_ad
	}//lp0082_ad
//-->
</script>
</head>
<body onload='onLoad()'><!--//lp0082_ad-->
<?
 //D0301 - Removed calls to global_functions and replaced with includes to pages with cache enabled
//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );

//D0301 - Added to compress output to remove all white space

include_once 'copysource/header.php';
if( !isset($CLAS01) ){
    $CLAS01 = isset($_REQUEST['CLAS01']) ? $_REQUEST['CLAS01'] : ""; //Changed by AG, it was giving error as there are no params
}

if( isset($_SESSION['userID']) ){

	if( !isset($_SESSION['classArray']) ){
    	$_SESSION['classArray'] = get_classification_array();
	}
	if( !isset($_SESSION['typeArray']) ){
    	$_SESSION['typeArray'] = get_typeName_array();
	}

    //D0301 - Removed calls to global_functions and replaced with includes to pages with cache enabled
    //menuFrame( "MTP" );
    
	//LP0018 - Update Last login
	update_last_login();
    include_once 'copysource/menu.php';
}else{
    error_mssg( "NONE");
}

if( isset( $ID01 ) && $ID01 != ""){
    
$parentFlag = isParent( $ID01 );

if( $parentFlag == 0 ){
    ?><form method='post' name='detailsForm' id='detailsForm' action='updateIssue.php'><?php 
}else{
    ?><form method='post' name='detailsForm' id='detailsForm' action='multiUpdate.php'><?php 
}
?>
<!-- input type for validation on PartNumber and OrderNumber -->
<input type='hidden' id='PART_NUMBER' name='PART_NUMBER' value=''/>
<input type='hidden' id='ORDER_NUMBER' name='ORDER_NUMBER' value=''/>
<input type='hidden' id='DESN_NUMBER' name='DESN_NUMBER' value=''/>
<input type='hidden' name='ticketIds[]' id='ticketIds[]' value='<?php echo $ID01;?>'/>  <!-- //**LP0044_AD Added to provide parameters for calling saveMultiUpdate.php  -->
<input type='hidden' name='saveAction' id='saveAction' value='Send to Pricing'/> <!-- //**LP0044_AD  -->

<?php //D0455 - Added hidden parm to send from to return back to myTickets 
if( !isset($from) ){
    $from = "";   
}
?>

<input type='hidden' id='from' name='from' value='<?php echo $from; ?>'/>

<?php


    $id = $ID01;
    $class = $CLAS01;
    
        global $conn, $mtpUrl;
    
      //LP0078_AD   $sql = "SELECT ID01, RQID01, DESC01, LDES01, DATE01, STAT01, PRTY01, RSID01, TYPE01, CLAS01, BUYR01, OWNR01, POFF01,"
        $sql = "SELECT ID01, RQID01, DESC01, LDES01, DATE01, STAT01, PRTY01, RSID01, TYPE01, CLAS01, BUYR01, OWNR01, POFF01,UDAT01,UTIM01, "//LP0078_AD 
         . " KEY101, KEY201, KEY301, KEY401, CHCE01, IMPT01, EMDA01, EDAT01, ESTI01, PRNT01, CHLF01 FROM CIL01L00 WHERE ID01 = " . trim($id);
    
        $res = odbc_prepare ( $conn, $sql );
        if( odbc_execute ( $res ) ){
            
        }else{
            $handle = fopen("./sqlFailures/sqlFails.csv","a+");
            fwrite($handle, "683 - showTicketDetails.php," . $sql . "\n" );
            fclose($handle);
        }
    
        echo "<center>";
        echo "<table width='75%' cellpadding=0 cellspacing=0 border=0>";
    
        $userArray = get_user_list ();
    
        $ticketCounter = 0;
        if ($_SESSION ['userID'] == "1021") {
            echo "After User Arrays:" . date ( 'H:i:s' );
        }
    
        while ( $row = odbc_fetch_array ( $res ) ) {
            //LP0078 ?>
            <script> var ticketnr= <?= $row['ID01']; ?>; var timestamp= <?= $row['UDAT01'].$row['UTIM01'];?>; </script>  <?php //LP0078
            //LP0004 - Start Change ************************************
            $employeeArray = array();
            array_push($employeeArray, $_SESSION ['userID'] );
            $superAuthority[] = "";
            $superAuthArray = get_super_reports_authority( $_SESSION ['userID'], $employeeArray, $conn, $superAuthority, $row['RQID01'],1);
            
            ////LP0004 - End Change ************************************
            
    	//LP0013 check group authority
    	// if no EDIT and READ authority then stop listing
    	//LP0013
    	/* below is to check Cost Check - GLP Team only group authority for logged in user (edit, read and close )*/
    	if($row['STAT01']==1){
    		$readAuth = getUserGroupAuth($_SESSION ['userID'],2,"READ");	// 2 Is the group id in CIL39
    		$editAuth = getUserGroupAuth($_SESSION ['userID'],2,"EDIT");	// 2 Is the group id in CIL39
    		
    		if ($editAuth){
    			$readAuth = true;
    		}
    		if(!$readAuth && !$editAuth && $row['CLAS01']==5 && $row['TYPE01']==75){
    			echo '<TR><TD class="title">You do not have access to this ticket, please contact your administrator if you require access</TD></TR>';
    			die();
    		}
    	}else if($row['STAT01']==5){
    		$closAuth = getUserGroupAuth($_SESSION ['userID'],2,"CLOS");	// 2 Is the group id in CIL39
    		if(!$closAuth&& $row['CLAS01']==5 && $row['TYPE01']==75){
    			echo '<TR><TD class="title">You do not have access to this ticket, please contact your administrator if you require access</TD></TR>';
    			die();
    		}
    	}
    	//*********************************** LP0054_AD START *******************************************/
    ?><!--   //LP0054_AD-->
    <input type='hidden' id='BUYR01' name='BUYR01' value='<?=$row['BUYR01']; ?> '/><!--   //LP0054_AD-->
    <?php //LP0054_AD
    	//*********************************** LP0054_AD END *******************************************/
    	
    	//*********************************** LP0055_AD START ********************************************
    	    /* below is to check Price Change only group authority for logged in user (edit, read and close )*/
    if($row['TYPE01']==130 || $row['TYPE01']==133){  //lp0089
    	    if($row['STAT01']==1){//LP0055_AD
    	        $readAuth = getUserGroupAuth($_SESSION ['userID'],3,"READ");	// 3 Is the group id in CIL39//LP0055_AD
    	        $editAuth = getUserGroupAuth($_SESSION ['userID'],3,"EDIT");	// 3 Is the group id in CIL39//LP0055_AD
    	        
    	        if ($editAuth){//LP0055_AD
    	            $readAuth = true;//LP0055_AD
    	        }//LP0055_AD
    	       //lp0068_ad if(!$readAuth && !$editAuth && $row['CLAS01']==8 && $row['TYPE01']==130){//LP0055_AD
    	        if(!$readAuth && !$editAuth && $row['CLAS01']==8 &&( $row['TYPE01']==130 || $row['TYPE01']==133)){//LP0068_AD
    	                echo '<TR><TD class="title">You do not have access to this ticket, please contact your administrator if you require access</TD></TR>';//LP0055_AD
    	            die();//LP0055_AD
    	        }//LP0055_AD
    	    }else if($row['STAT01']==5){//LP0055_AD
    	        $closAuth = getUserGroupAuth($_SESSION ['userID'],3,"CLOS");	// 3 Is the group id in CIL39//LP0055_AD
    	      //lp0068_ad  if(!$closAuth&& $row['CLAS01']==8 && $row['TYPE01']==130){//LP0055_AD
    	        if(!$closAuth&& $row['CLAS01']==8 && ($row['TYPE01']==130 || $row['TYPE01']==133)){//LP0068_AD
    	                echo '<TR><TD class="title">You do not have access to this ticket, please contact your administrator if you require access</TD></TR>';//LP0055_AD
    	            die();//LP0055_AD
    	        }//LP0055_AD
    	    }//LP0055_AD
    }//lp0089
    	    //*********************************** LP0055_AD END **********************************************
    	    
    	/*Authority check ended*/
            /******************************Moved by Ted***************/
            //LP0002 changes started      
            $drp = 0;
            if( $row ['CLAS01'] == 3 ){      //Check to see if Classification is Global Order Process Support 
                $getCustomers=get_customers_showTicket($id);
                $arrStockRoom = get_stockroom_array();
                
                if( isset( $getCustomers[0] ) ){
                    if( (array_search ( trim ( $getCustomers[0] ), $arrStockRoom ) !== false) ){ //Check to see if a DRP order
                        if( validate_drp_manager( $_SESSION ['userID'] )){//Check to see if the current user is an active DRP manager
                            $drp = 1;
        
                        }
                    }
                }
            
            }
            
            //LP0002 - Change var name and added section
            $plannerHasAnswers = check_ticket_answers(  $row ['CLAS01'] , $row ['TYPE01'], $row ['ID01'], "2" );
            
            //LP0002 - added call to check PFC section
            $pfcHasAnswers = check_ticket_answers(  $row ['CLAS01'] , $row ['TYPE01'], $row ['ID01'], "1" );
            
      
            //LP0002 changes ended
            /******************************Moved by Ted***************/

    
            if ($class != 6) {
                echo "<input type='hidden' name='currentOwnerId' value='" . $row ['OWNR01'] . "'>";
            } else {
                echo "<input type='hidden' name='currentOwnerId' value='" . $row ['POFF01'] . "'>";
            }
            $ticketCounter ++;
            ?>
            <input type='hidden' name='POFF01' value='<?php echo $row ['POFF01'];?>'>
            <tr><TD>&nbsp;</td></tr>
            <tr><TD>&nbsp;</td></tr>
            <tr>
               <TD class='titleBig' colspan='3'>Ticket - <?php echo $row['ID01']; ?></td>
            </tr>
            <tr id='warningRow' style='display: none' ><!--  //lp0078-->
               <TD class='titleBig' colspan='3' style="color: red;">WARNING: This ticket has been already changed by somebody else !!! <br></br><!--  //lp0078-->
               <input type="button" onclick="document.location.reload()" value="Refresh!"><!--  //lp0078-->
               <input type="button" onclick="onAjaxAnswer()" value="Force Update!"><!--  //lp0078-->
                
                </td><!--  //lp0078-->
            </tr><!--  //lp0078-->
            
            <tr><TD>&nbsp;</td></tr>
            <?php
            //Check for vendor email
            $vendorEmailDetails = get_vendor_email_details ( $row ['ID01'] );
            if ($vendorEmailDetails [0]) {
                echo "<TR bgcolor='red'>";
                $vendSendDate = formatDate ( $vendorEmailDetails [2] );
                echo "<TD class='bold' colspan='3'>Vendor email has been sent to " . $vendorEmailDetails [0] . " on $vendSendDate</td>";
                echo "</tr>";
            }
            echo "<tr>";
            echo "<TD colspan='3'>";
            echo "<hr>";
            echo "</td>";
            echo "</tr>";
            
            
            if( trim($row['CHLF01']) == 0 ){
                if( trim($row['PRNT01']) != trim($row['ID01']) && trim($row['PRNT01']) != 0){
                    $parentDisplay = " - Parent: <a href='showTicketDetails.php?ID01=${row['PRNT01']}'>${row['PRNT01']}</a>";   
                }else{
                    $parentDisplay = "";
                }
                $ticketDetailsTitle = "General Ticket Details" . $parentDisplay;
            }else{
                $ticketDetailsTitle = "Parent Ticket Details";
            }
            
     ?>
            <tr>
            	<td class='boldBig' colspan='3'><?php echo $ticketDetailsTitle;?></td>
            </tr>
            <tr>
            <td colspan='3'><hr></td>
            </tr>
            <tr>
            <td class='bold' width=20%>Requester</td>
            <td>
            <?php
            //DI868F - Removed ability to change requester on update
            foreach ( $userArray as $users ) {
                if ($row ['RQID01'] == $users ['ID05']) {
                    echo trim ( $users ['NAME05'] );
                }
            }
            //DI868F - End
            echo "<input type='hidden' name='RQID01' value='" . $row ['RQID01'] . "'>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Status</td>";
            echo "<TD>" . get_status_name ( $row ['STAT01'] ) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Classificaton</td>";
            echo "<TD>";
            //echo "<select disabled name='CLAS01' class='long'>";
            //classification_select_box ( FACSLIB, "CIL09V01", "ID09", "CLAS09", "", "CLAS09", $row ['CLAS01'] );
            
            if( !isset($_SESSION['classArray']) ){
                $_SESSION['classArray'] = get_classification_array();
            }
            if( !isset($_SESSION['typeArray']) ){
                $_SESSION['typeArray'] = get_typeName_array();
            }
            if (is_array($_SESSION['classArray']) || is_object($_SESSION['classArray'])){
                foreach ($_SESSION['classArray'] as $classList ) {
                		if( $classList['ID09'] == $row ['CLAS01'] ){
                		    echo trim($classList['CLAS09']);
               			}
                }
            }
            ?>
            </td>
            </tr>
            <tr>
            <td class='bold'>Type</td>
            <td><?php
        	$x = 0;
        	if (is_array($_SESSION ['typeArray']) || is_object($_SESSION ['typeArray'])){
                foreach ( $_SESSION ['typeArray'] as $typeList ) {
                    if( isset($typeList[$row ['CLAS01']]['CLASS'][$x]) && $typeList[$row ['CLAS01']]['CLASS'][$x] == $row ['CLAS01'] ){
                	?>
                		<?php
                		if( $row ['TYPE01'] == $typeList[$row ['CLAS01']]['ID'][$x] ){
                		    echo $typeList[$row ['CLAS01']]['NAME'][$x];
                		}
                	}
                    $x ++;
                }
        	}
            ?>
            
            </td>
            <input type='hidden' name='CLAS01' value='<?php echo trim($row ['CLAS01']);?>'>
            <input type='hidden' name='TYPE01' value='<?php echo trim($row ['TYPE01']);?>'>
            </tr>
            <tr>
            <?php 
            if ($class == 7) {
                echo "<TD class='bold'>Owner</td>";
    
                echo "<TD>" . showUserFromArray ( $userArray, $row['POFF01'] )  . "</td>";
            }else{
            	echo "<TD class='bold'>Owner</td>";
    
                echo "<TD>" . showUserFromArray ( $userArray, $row['OWNR01'] ) . "</td>";
    
            }
            echo "</tr>";
            //LP0016 - Outbound Planner to be added to all Global Process Support Ticket Types
            if ($row ['CLAS01'] == 3) {
                echo "<TD class='bold'>Outbound Planner</td>";
                $plannerInfo = user_info_by_id($row ['POFF01'] );
                //**LP0033  echo "<TD>" . trim ( $plannerInfo ['NAME05'] )  . "</td>";
                echo "<td>";                                                                                //**LP0033
                echo  trim($plannerInfo['NAME05']);                                                         //**LP0033
                if (($_SESSION ['authority'] == "S") || ($_SESSION ['authority'] == "L")){                  //**LP0033
                    echo " &nbsp; ";                                                                        //**LP0033
                    comboNewOBP();                                                                          //**LP0033
                }                                                                                           //**LP0033
                echo "</td>";                                                                               //**LP0033
            }
            echo "</tr>";
            //LP0016 changes ended.
            echo "<tr>";
            echo "<TD class='bold'>Short Description</td>";
            echo "<TD>" . trim ( $row ['DESC01'] ) . "</td>";
            echo "<input type='hidden' name='DESC01' value='" . trim ( $row ['DESC01'] ) . "'>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Long Description</td>";
            echo "<TD>" . trim ( mb_convert_encoding($row ['LDES01'], 'UTF-8' )) ."</td>";
            echo "</tr>";
    
    
            //D0217 - Removed ability for non-requester from changeing the priority of the issue.
            if( $_SESSION ['userID'] <> trim($row['RQID01']) && $_SESSION ['authority'] <> "S" ){
    
            	 	echo "<tr>";
    	            echo "<TD class='bold'>Priority</td>";
    	            echo "<TD>";
    	            echo "<SELECT name='PRTY01' class='long' disabled=true>";
    	            priority_select_box ( trim ( $row ['PRTY01'] ) );
    	            echo "</select>";
    	            echo "</td>";
    	            echo "</tr>";
    
            	echo "<input type='hidden' name='PRTY01' value='" . trim ( $row ['PRTY01'] ) . "'>";
            	echo "<input type='hidden' name='currentPriority' value='" . trim ( $row ['PRTY01'] ) . "'>";
    
            }else{
    
    	        //DI868H - Removed Priority selection capabilities for classification 5 (Pricing Administration)
    	        if ($class != 5 && trim ( $row ['TYPE01'] ) != 51 && $class != 9 ) {
    	            echo "<tr>";
    	            echo "<TD class='bold'>Priority</td>";
    	            echo "<TD>";
    	            echo "<SELECT name='PRTY01' class='long'>";
    	            priority_select_box ( trim ( $row ['PRTY01'] ) );
    	            echo "</select>";
    	            echo "<input type='hidden' name='currentPriority' value='" . trim ( $row ['PRTY01'] ) . "'>";
    	            echo "</td>";
    	            echo "</tr>";
    	        } elseif (trim ( $row ['TYPE01'] ) == 51) {
    	            echo "<tr>";
    	            echo "<TD class='bold'>Priority</td>";
    	            echo "<TD>";
    	            echo "<SELECT name='PRTY01' class='long' disabled=true>";
    	            priority_select_box ( trim ( $row ['PRTY01'] ) );
    	            echo "</select>";
    	            echo "</td>";
    	            echo "</tr>";
    
    	            //D0215 - Changed value from '1' to '2'
    	            echo "<input type='hidden' name='PRTY01' value='2'>";
    	        } else {
    	            echo "<input type='hidden' name='PRTY01' value='3'>";
    	        }
    	        //DI868H - End
    	        echo "<tr>";
            }
    
            echo "<input type='hidden' id='CURRENT_RSID01' name='CURRENT_RSID01' value='" . $row['RSID01'] . "'>";
            //echo "<TD class='bold'>Resource</td>";
            //echo "<td><input type='text' name='RSID_FILTER' id='RSID_FILTER' value='4 Charaters of Resource Name' onFocus='userListFocus()' onkeyup='userListKeyUp()'>";
            //echo "</td>";
            //echo "</tr>";
            ?>
            
            <tr>
            <td class='bold'>Resource</td>
            <td><input type='text' id='autocomplete' value='Start Typing' ></td>
            </tr>
            <tr>
            <!-- <td>Selected User id</td>-->
            <td><input type='hidden' id='RSID01' name='RSID01'/></td>
            </tr>
            <?php
            echo "<tr>";
            echo "<TD class='bold'>Created</td>";
            echo "<TD>" . formatDate ( trim ( $row ['DATE01'] ) ) . "</td>";
            echo "</tr>";
            ?>
    <tr>
        <TD>&nbsp;</td>
    </tr>
            
    
    <tr>
        <TD class='bold'><a href="javascript:void(0)" onClick='showCarbonCopy()'>Carbon Copy</a></td>
    </tr>
    <?php 
            //Move Child ticket details to childTicket.php
            
            if( trim($row['CHLF01']) == 0 ){
                include_once 'childTicket.php';
            }else{
                include_once 'parentTicket.php';   
            }
           
    
            echo "<TD width=50%>&nbsp</td>";
            echo "</tr>";
           
            echo "</td>";
            echo "</tr>";
            echo "<tr><TD>&nbsp</td></tr>";
            echo "<tr>";
            echo "<td colspan='3'>";
            echo "<hr>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td class='boldBig' colspan='3'>History Log</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD colspan='3'>";
            echo "<hr>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD colspan='3'>";
            echo "<table width=100% cellpadding='0' cellspacing='0'>";
            echo "<TR class='headerBlue'>";
            echo "<TD class='headerWhitePadded'>Date</td>";
            echo "<TD class='headerWhitePadded'>Entered By</td>";
            echo "<TD class='headerWhitePadded'>Action/Response</td>";
            echo "</tr>";
            $historyCounter = 0;
            foreach ( $historyArrayValues as $history ) {
                if ($historyCounter % 2 == 0) {
                    echo "<tr>";
                } else {
                    echo "<TR class='alternate'>";
                }
                
                if ($history ['PRVT02'] != 'N'){                    //**LP0034
                    $stylePrivateBegin = "<font color='blue'>";     //**LP0034
                    $stylePrivateEnd = "</font>";                   //**LP0034
                }else{                                              //**LP0034
                    $stylePrivateBegin = "";                        //**LP0034
                    $stylePrivateEnd = "";                          //**LP0034
                }                                                   //**LP0034
                
                //**LP0034  echo "<TD width=20%>" . formatDate ( $history ['DATE02'] ) . " " . $history ['TIME02'] . "</td>";
                echo "<TD width=20%>" . $stylePrivateBegin . formatDate ( $history ['DATE02'] ) . " " . $history ['TIME02'] . $stylePrivateEnd . "</td>";  //**LP0034
                //$userName = showUserFromArray( $userArray, $history['RSID02'] );
                //**LP0034  echo "<TD width=30%>" . showUserFromArray ( $userArray, $history ['RSID02'] ) . "</td>";
                echo "<TD width=30%>" . $stylePrivateBegin . showUserFromArray ( $userArray, $history ['RSID02'] ) . $stylePrivateEnd . "</td>";    //**LP0034
                echo "<TD>" . $history ['STEP02'] . "</td>";
                echo "</tr>";
    
                if ($vendorEmailDetails [5] == $history ['ID02']) {
                    echo "<tr>";
                    echo "<TD colspan='3'>";
                    echo "<center>";
                    echo "<table width=90% cellpadding=0 cellspacing=0>";
                    echo "<TR class='header'>";
                    echo "<TD colspan='3' class='bold'>Vendor email information</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<TD>";
                    echo $vendorEmailDetails [1];
                    echo "</td>";
                    echo "<TD>";
                    echo $vendorEmailDetails [0];
                    echo "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<TD colspan='3'>";
                    echo $vendorEmailDetails [4];
                    echo "</td>";
                    echo "</tr>";
                    echo "</table>";
                    echo "</center>";
                    echo "</td>";
                    echo "</tr>";
                }
                $historyCounter ++;
            }
            echo "</table>";
            echo "</td>";
            echo "</tr>";
            echo "<tr><TD>&nbsp</td></tr>";
            echo "<tr>";
            echo "<TD colspan='3'>";
            echo "<hr>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='boldBig' colspan='3'>Attachments</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD colspan='3'>";
            echo "<hr>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            $tempId = $_SESSION ['userID'] . "_" . date ( 'Ymd' ) . "_" . date ( 'his' );
            echo "<TD colspan='3'>";
            echo "<iframe src='$mtpUrl/attachments.php?userID=" . $_SESSION ['userID'] . "&attachmentId=" . $row ['ID01'] . "' name='attachments' FRAMEBORDER='0' width=100% height='150'></iframe>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
    
        	if ($_SESSION ['userID'] == "1021") {
                echo "After History:" . date ( 'H:i:s' );
            }
    
        }
}else{
    $ticketCounter = 0;
    ?><center><?php 
}
    if ($ticketCounter == 0) {
        ?>
        <tr><TD>&nbsp;</td></tr>
        <tr><TD class='title' colspan='6'>No Ticket Available</td></tr>
        <?php
    }
// } End display_ticket_details function no longer needed
?>
<input type='hidden' name='ID01' value='<?php echo $ID01; ?>'/>
<input type='hidden' name='STAT01' id='STAT01' value=''/>
</form>
<?php
page_footer( "login" );

odbc_close( $conn );

//D0301 - Added to output buffer

?>

<!-- Script -->
    <script type='text/javascript' >
    $( function() {
  
  		$( "#autocomplete" ).focus(function() {
              this.value = '';
            });

        $( "#autocomplete" ).autocomplete({
            source: function( request, response ) {
                
                $.ajax({
                    url: "fetchData.php",
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function( data ) {
                        response( data );
                    }
                });
            },
            select: function (event, ui) {
                $('#autocomplete').val(ui.item.label); // display the selected text
                $('#RSID01').val(ui.item.value); // save selected id to input
                return false;
            }
        });

        // Multiple select
        $( "#multi_autocomplete" ).autocomplete({
            source: function( request, response ) {
                
                var searchText = extractLast(request.term);
                $.ajax({
                    url: "fetchData.php",
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: searchText
                    },
                    success: function( data ) {
                        response( data );
                    }
                });
            },
            select: function( event, ui ) {
                var terms = split( $('#multi_autocomplete').val() );
                
                terms.pop();
                
                terms.push( ui.item.label );
                
                terms.push( "" );
                $('#multi_autocomplete').val(terms.join( ", " ));

                // Id
                var terms = split( $('#selectuser_ids').val() );
                
                terms.pop();
                
                terms.push( ui.item.value );
                
                terms.push( "" );
                $('#selectuser_ids').val(terms.join( ", " ));

                return false;
            }
           
        });
    });

    function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }

    </script>

</html>


<?php                                                                                                           //**LP0033
function comboNewOBP(){                                                                                         //**LP0033
    global $conn;                                                                                               //**LP0033
                                                                                                                //**LP0033
    $sql= "select distinct(PLAN38), NAME05 from osldipdatl.CIL38 T1 "
         . "inner join osldipdatl.hlp05 T2 "
         . "on T1.PLAN38 = T2.ID05 and T2.DEL05='N' and AUTH05='L'";
                                                                                                                //**LP0033
    $res = odbc_prepare($conn, $sql);                                                                            //**LP0033
    odbc_execute($res);                                                                                          //**LP0033
                                                                                                                //**LP0033
    echo "<select name='newOBP'>";                                                                              //**LP0033
    echo "<option value='0' selected> *** Change OBP *** </option>";                                            //**LP0033
    while($row = odbc_fetch_array($res)){                                                                        //**LP0033
        echo "<option value='" . trim($row['PLAN38']) . "'>" . trim($row['NAME05']) . "</option>";              //**LP0033
    }                                                                                                           //**LP0033
    echo "</select>";                                                                                           //**LP0033
                                                                                                                //**LP0033
}                                                                                                               //**LP0033
?>

