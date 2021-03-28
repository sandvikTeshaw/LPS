<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            addTicket.php<br>
 * Development Reference:   DI868<br>
 * Description:             Save new LPS ticket and attributes
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  DI868C    TS      23/08/2008  Disable submit button<br>
 *  DI868G    TS      06/11/2008  Short description<br>
 *  DI868H    TS      06/11/2008  Pricing Admin classification<br>
 *  DI932     TS      30/07/2009  Retunrs Classification<br>
 *  D0107     TS      14/01/2010  Remove Requester selection<br>
 *  D0215	  TS	  04/11/2010  Paperwork not recieved priority error<br>
 *  D0301 	  TS	  24/03/2011  Change Header and Menu calls
 *  i-2312795 TS	  23/04/2013  Disable Textboxed	
 *  LP0021    TS      11/04/2017  Added Invoice validation logic
 *  LP0028    TS      06/12/2017  Remove auto setting for priotiry on type = 51
 *  LP0027    TS      12/07/2017  CIL01 Fix
 *  LP0046    KS      21/06/2018  Auto Create Cost Check ticket
 *  LP0052    AD      29/10/2017  Create new LPS ticket type �Supersession�)*
 *  LP0046    KS      06/11/2018  merging with LP0052
 *  LP0055    AD      13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0055    KS      29/03/2019  fix
 *  LP0068    AD      24/04/2019  GLBAU-16824_LPS Vendor Change
 *  LP0076    AD      28/06/2019    GLBAU-17554_Inbound Parts Not Marked with Sandvik Part Number
 *  LP0082    AD      18/09/2019  Amendment / enhancement to Vender change LPS ticket 
 *  LP0075    TS      24/10/2019  Supersession fix 
 *  LP0090    TS      12/06/2019  Lower Case Part Number entry failure fix
 *   
/**
 * 
 */


include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
include 'copysource/functions/programFunctions.php';                        //**LP0027
// require_once 'CW/cw.php';   //Already added on programFunctions.php       //**LP0027

global $conn;

if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if (isset($conn)) {

} else {
    echo "Connection Failed";
}

if (isset($_SESSION ['email'])) {
    $userInfo [] = "";
    $userInfo = user_cookie_info ( $_SESSION ['email'] );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['email'] = $_SESSION ['email'];
    
    if (!isset($_COOKIE ["mtp"])) {
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }

} elseif(isset( $_COOKIE ["mtp"] )) {
    $userInfo [] = "";
    $userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['email'] = $_COOKIE ["mtp"];
    
} else {
    
    error_mssg ( "NONE" );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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

function checkOrderNumber( oNumber, attribNumber, type ){

    quantity = this.detailsForm.QUANTITY.value;
    if( oNumber != "" && (type != "42" || quantity != "") ){
        window.open( 'validateOrder.php?orderNumber=' + oNumber + '&attrib=' + attribNumber + '&type=' + type,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
        oNumber = oNumber.toString();
       
        this.detailsForm.ORDER_NUMBER.value = oNumber;
        this.detailsForm.ORDER_ATTRIBUTE.value = attribNumber;
        
    }else if( oNumber != "" && type == "42" ) {
    
        oNumber = oNumber.toString();

        this.detailsForm.ORDER_NUMBER.value = oNumber;
        this.detailsForm.ORDER_ATTRIBUTE.value = attribNumber;
    }
}
function checkPartNumber( pNumber, attribNumber, type ){
    var oNumber = "";
    var dNumber = "";
    var orderAttrib = "";

    if( this.detailsForm.ORDER_NUMBER.value != ""  ){
        oNumber = this.detailsForm.ORDER_NUMBER.value.toString();
    }
    if( this.detailsForm.DESN_NUMBER.value != ""  ){
        dNumber = this.detailsForm.DESN_NUMBER.value;
    }
    if( this.detailsForm.ORDER_ATTRIBUTE.value != ""  ){
        orderAttrib = this.detailsForm.ORDER_ATTRIBUTE.value;
    }

    pNumber = pNumber.toUpperCase();	<!-- LP0090 -->
    quantity = this.detailsForm.QUANTITY.value;

    if( pNumber != "" && (type != "42" || quantity != "")){
        window.open( 'validatePart.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type + '&orderNumber=' + oNumber + '&desnNumber=' + dNumber + '&orderAttrib=' + orderAttrib,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
    
    }else if( pNumber != "" && type == "42" ) {
        window.open( 'validatePart.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type + '&orderNumber=' + oNumber + '&desnNumber=' + dNumber + '&orderAttrib=' + orderAttrib,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
        this.detailsForm.PART_NUMBER.value = pNumber;
        this.detailsForm.PART_ATTRIBUTE.value = attribNumber;
  
    }
    else if ( pNumber == "" && type == "135" ) {
        $(":disabled").prop("disabled", false);//lp0077_AD
    }
    if( type == "135" ){
   
    	document.getElementById("sPnumber").value=pNumber;
    	document.getElementById("suppName").value="  ";
    	document.getElementById("suppName").focus();
    }
    
   
}
//***************************************************** LP0055_AD START ************************************************************************************
function checkSuppPartNumber( pNumber, attribNumber, type ){
    
    //**LP0055_KS  var oNumber = "";
    //**LP0055_KS  var dNumber = "";
    //**LP0055_KS  var orderAttrib = "";
    var sNumber = "";										//**LP0055_KS
    
    //**LP0055_KS  if( this.detailsForm.ORDER_NUMBER.value != ""  ){
    //**LP0055_KS      oNumber = this.detailsForm.ORDER_NUMBER.value.toString();
    //**LP0055_KS  }
    //**LP0055_KS  if( this.detailsForm.DESN_NUMBER.value != ""  ){
    //**LP0055_KS      dNumber = this.detailsForm.DESN_NUMBER.value;
    //**LP0055_KS  }
    //**LP0055_KS  if( this.detailsForm.ORDER_ATTRIBUTE.value != ""  ){
    //**LP0055_KS      orderAttrib = this.detailsForm.ORDER_ATTRIBUTE.value;
    //**LP0055_KS  }
    
    //**LP0055_KS  quantity = this.detailsForm.QUANTITY.value;

    if( this.detailsForm.SUPPLIER_NUMBER.value != ""  ){	//**LP0055_KS
        sNumber = this.detailsForm.SUPPLIER_NUMBER.value;	//**LP0055_KS
    }														//**LP0055_KS

    window.open( 'validateSupplierPart.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type + '&supplierNumber=' + sNumber,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no') //**LP0055_KS
    
    //**LP0055_KS  if( pNumber != "" && (type != "42" || quantity != "")){
    //**LP0055_KS      window.open( 'validateSupplierPart.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type + '&orderNumber=' + oNumber + '&desnNumber=' + dNumber + '&orderAttrib=' + orderAttrib,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
    //**LP0055_KS  }else if( pNumber != "" && type == "42" ) {
    //**LP0055_KS      window.open( 'validateSupplierPart.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type + '&orderNumber=' + oNumber + '&desnNumber=' + dNumber + '&orderAttrib=' + orderAttrib,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
    //**LP0055_KS      this.detailsForm.SUPPLIER_PART_NUMBER.value = pNumber;
    //**LP0055_KS      this.detailsForm.SUPPLIER_PART_ATTRIBUTE.value = attribNumber;
    //**LP0055_KS  }
}
function checkSuppNumber( pNumber, attribNumber, type ){
    
    var oNumber = "";
    var dNumber = "";
    var orderAttrib = "";
    
    if( this.detailsForm.ORDER_NUMBER.value != ""  ){
        oNumber = this.detailsForm.ORDER_NUMBER.value.toString();
    }
    if( this.detailsForm.DESN_NUMBER.value != ""  ){
        dNumber = this.detailsForm.DESN_NUMBER.value;
    }
    if( this.detailsForm.ORDER_ATTRIBUTE.value != ""  ){
        orderAttrib = this.detailsForm.ORDER_ATTRIBUTE.value;
    }
    
    quantity = this.detailsForm.QUANTITY.value;
    
    if( pNumber != "" && (type != "42" || quantity != "")){
        window.open( 'validateSupplier.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type + '&orderNumber=' + oNumber + '&desnNumber=' + dNumber + '&orderAttrib=' + orderAttrib,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
    }else if( pNumber != "" && type == "42" ) {
        window.open( 'validateSupplier.php?partNumber=' + pNumber + '&attrib=' + attribNumber + '&type=' + type + '&orderNumber=' + oNumber + '&desnNumber=' + dNumber + '&orderAttrib=' + orderAttrib,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
        this.detailsForm.SUPPLIER_NUMBER.value = pNumber;
        this.detailsForm.SUPPLIER_ATTRIBUTE.value = attribNumber;
    }
}

//***************************************************** LP0055_AD END **************************************************************************************
<!--LP0021 - Start of add of invoice addition-->
function checkInvoice( iNumber, attribNumber, type ){

	if( iNumber != "" ){
       window.open( 'validateInvoice.php?invoiceNumber=' + iNumber + '&attrib=' + attribNumber + '&type=' + type,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
	
	}
}
<!--LP0021 - End of add of invoice addition-->

function checkQuantity ( quantity, attribNumber, type ){
    if( quantity != "" ){
        pNumber = this.detailsForm.PART_NUMBER.value;
        oNumber = this.detailsForm.ORDER_NUMBER.value;
        
        partAttribNumber = this.detailsForm.PART_ATTRIBUTE.value;
        orderAttribNumber = this.detailsForm.ORDER_ATTRIBUTE.value;
        
        this.detailsForm.QUANTITY.value = quantity;

        window.open( 'expiditeVerification.php?quantity=' + quantity + '&orderNumber=' + oNumber + '&partNumber=' + pNumber + '&orderAttrib=' + orderAttribNumber + '&partAttrib=' + partAttribNumber + '&type=' + type,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
    }
}
function checkRequiredInputs(){
	var liability=false;//lp0082_ad
	var vdsr=false;//lp0082_ad
    var z = 1;
    var attributeCounter = this.detailsForm.attributeCount.value;
    var missingAttributesArray = "";
    for( z=1; z<= attributeCounter; z++ ){
    
        var required = eval( "this.detailsForm.attribRequired_" + z + ".value" ).toLowerCase();
        var type = eval( "this.detailsForm.attribType_" + z + ".value").toLowerCase();
        <?php  //lp0082_ad
        if ($_REQUEST['type']==133){//lp0082_ad
                    ?>//lp0082_ad
                    //lp0082_ad if(this.detailsForm.drop10.selectedIndex==2)liability=true;
                    if(this.detailsForm.drop13.selectedIndex==2)vdsr=true;//lp0082_ad
                    if(z==11 && liability)required = "y";//lp0082_ad
                    if(z==14 && vdsr)required = "y";//lp0082_ad
                    if(z==15 && vdsr)required = "y";//lp0082_ad
                    
        <?php } ?>	     //lp0082_ad
        if( type != "date" && (required == "y" )){           
            var type_1=type;//lp0082_ad
            if(type_1=="curn")type_1="text";//lp0082_ad
        	//lp0082_ad if( eval( "this.detailsForm." + type + z + ".value" ) == "" ){
        	if( eval( "this.detailsForm." + type_1 + z + ".value" ) == "" ){//lp0082_ad
                        missingAttributesArray = missingAttributesArray + "\n" + eval("this.detailsForm.attribName_" + z + ".value");
            }
            
        }
            
    }
    if( missingAttributesArray != "" ){
        missingAttributesArray = "Missing Required Supporting Information" + missingAttributesArray;
        alert( missingAttributesArray );
        return false;
    }
    
    return true;
    
}
function openCenteredWindow( url, height, width, name, parms ){

   var left = Math.floor( ( screen.width - width ) / 2 );
   var top = Math.floor( ( screen.height - height) / 2 );
   var winParms = "top=" + top + ",left=" + left + ",height=" + height + ",width=" + width;
   if(parms){ 
      winParms += "," + parms; 
   }

   var win = window.open( url, name, winParms );
   win.focus();
   return win;
}

function CheckPart( BoxNumber ){

   if( this.detailsForm.CLAS01.value == 8 ){
   
    var url = this.detailsForm.mtpUrl.value 

      orderNumber = this.detailsForm.ORDER_NUMBER.value;
      ItemNumber = this.detailsForm.PART_NUMBER.value;
      Quantity = BoxNumber
      openCenteredWindow( url + '/item_check.php?ITEM='+ItemNumber+'&DINUM='+orderNumber+'&QTY='+Quantity, 600, 500, 'prechecklist', 'scrollbars=1' );
   }
}
//******************************* LP0076_AD START ****************************************************
function CheckPO(){//LP0076_AD

	var url = this.detailsForm.mtpUrl.value //LP0076_AD
	var po=document.getElementById("PO").value;//LP0076_AD
	var xhttp = new XMLHttpRequest();//LP0076_AD
	xhttp.onreadystatechange = function() {//LP0076_AD
	    if (this.readyState == 4 && this.status == 200) {//LP0076_AD
		  var result=this.responseText;  //LP0076_AD
	      if(result=='Wrong Purchase Order'&& po!=''){//LP0076_AD
	    	  document.getElementById("PO_INVALID").style.display='block';//LP0076_AD
	    	  document.getElementById("PO_OK").style.display='none';//LP0076_AD
	    	  document.getElementsByName("submitButton")[0].disabled = true;//LP0076_AD
		      }//LP0076_AD
		      else{//LP0076_AD
		    	  document.getElementById("PO_INVALID").style.display='none';//LP0076_AD
		    	  document.getElementById("PO_OK").style.display='block';//LP0076_AD
		    	  document.getElementsByName("submitButton")[0].disabled = false;//LP0076_AD
		    	  pos=result.search(" ");//LP0076_AD
		    	  snum=result.slice(0,pos);//LP0076_AD
		    	  snam=result.slice(pos+1);//LP0076_AD
		    	  document.getElementById("suppName").value=snam;//LP0076_AD
		    	  document.getElementById("suppNumber").value=snum;	    //LP0076_AD	  
			      };//LP0076_AD
			       
	    }//LP0076_AD
	  };//LP0076_AD
	xhttp.open("GET", "ajaxcheck_po.php?PO="+po.trim(), true);//LP0076_AD
	xhttp.send();//LP0076_AD
	}//LP0076_AD
//******************************** LP0076_AD END *****************************************************
function copyToCabonCopy(){
    var addCC = this.document.detailsForm.ccList.options[this.document.detailsForm.ccList.selectedIndex].value;
    var currentCC = addCC + "," + this.detailsForm.ccSelected.value;
    
    this.detailsForm.ccSelected.value = currentCC;

}
function showCarbonCopy(){
    if( this.carbonCopy.style.display == "block" ){
        this.carbonCopy.style.display='none';
    } else {
        this.carbonCopy.style.display='block';
    }

}
function setDesnNumber( desn ){
    this.detailsForm.DESN_NUMBER.value = desn;
}
function setCustomer( customerNumber ){
    this.detailsForm.CUSTOMER_NUMBER.value = customerNumber;
}

function checkCustomerAndSequence( sequence, attribNumber ){
   var customer =  this.detailsForm.CUSTOMER_NUMBER.value;
   window.open( 'validateCustomer.php?customerNumber=' + customer + '&sequence=' + sequence+'&attrib=' + attribNumber,'', 'width=1,height=1,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')
   
    
}

function selectPartInput(){

	this.detailsForm.submitButton.disabled = true;
	
}

function onLoad(){//lp0082_ad
<?php if($_REQUEST['type']==133){?>//lp0082_ad
	this.detailsForm.text11.parentElement.parentElement.style.display = 'none';//lp0082_ad
<?php }?>	//lp0082_ad
}//lp0082_ad


function showSubmitRow(){ <!--  //** LP0052_AD-->

	e=document.getElementById("assignedTo"); <!--  //** LP0052_AD-->
	//alert(e.options[e.selectedIndex].value); <!--  //** LP0052_AD-->
	if(e.options[e.selectedIndex].value != "none"){ <!--  //** LP0052_AD-->
    	document.getElementById("rowSubmit").style.display="table-row"; <!--  //** LP0052_AD-->
		
    	} <!--  //** LP0052_AD-->
	else { <!--  //** LP0052_AD-->
    	document.getElementById("rowSubmit").style.display="none"; <!--  //** LP0052_AD-->
	 	} <!--  //** LP0052_AD-->
}	 <!--  //** LP0052_AD-->

</script>
</head>

<?



//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );

//D0301 - Change Header
include_once 'copysource/header.php';
//lp0082_ad echo "<body>";
if( $_REQUEST['class']== 17 ){

    echo "<body onload='onLoad();showSubmitRow();'>";//lp0082_ad
}else{

    echo "<body onload='onLoad()'>";//lp0082_ad
}

if (!isset($_SESSION ['classArray']) && !isset($_SESSION ['typeArray'])) {
    $_SESSION ['classArray'] = get_classification_array ();
    $_SESSION ['typeArray'] = get_typeName_array ();
}

if( !$_SESSION ['classArray'] ){
	 	$_SESSION ['classArray'] = get_classification_array ();
	}
	if( !$_SESSION ['typeArray'] ){
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
//menuFrame ( "MTP" );
include_once 'copysource/menu.php';

$userArray = get_user_list ();
//*********************************** LP0055_AD START ********************************************
/* below is to check Price Change only group authority for logged in user (CREATE )*/
$readAuth = getUserGroupAuth($_SESSION ['userID'],3,"CRTE");	// 3 Is the group id in CIL39//LP0055_AD
//LP0068_AD if(!$readAuth && $_REQUEST['type']==130){//LP0055_AD
if(!$readAuth &&( $_REQUEST['type']==130 || $_REQUEST['type']==133)){//LP0068_AD
        
    echo '<TR><TD class="title">You do not have access to this ticket, please contact your administrator if you require access</TD></TR>';//LP0055_AD
    die();//LP0055_AD
}//LP0055_AD

//*********************************** LP0055_AD END **********************************************


//Prime CIL01 so there is no delay on program
$tempStart = 0;                

list ($tempStart, $lastID) = getReferenceNumbersFromFile("");                   

echo "<center>";
?>
    <form method='post' name='detailsForm' action='saveIssue.php'
    onsubmit="javascript:return checkRequiredInputs()">
    <?
    //input type for validation on PartNumber and OrderNumber
    

    echo "<input type='hidden' name='mtpUrl' value='$mtpUrl'>";
    echo "<input type='hidden' name='PART_NUMBER' value=''>";
    echo "<input type='hidden' name='SUPPLIER_PART_NUMBER' value=''>";  //LP0055_AD
    echo "<input type='hidden' name='SUPPLIER_NUMBER' value=''>";  //LP0055_AD
    echo "<input type='hidden' name='DESN_NUMBER' value=''>";
    echo "<input type='hidden' name='CUSTOMER_NUMBER' value=''>";
    echo "<input type='hidden' name='ORDER_NUMBER' value=''>";
    echo "<input type='hidden' name='PART_ATTRIBUTE' value=''>";
    echo "<input type='hidden' name='SUPPLIER_PART_ATTRIBUTE' value=''>";//LP0055_AD
    echo "<input type='hidden' name='SUPPLIER_ATTRIBUTE' value=''>";//LP0055_AD
    echo "<input type='hidden' name='ORDER_ATTRIBUTE' value=''>";
    echo "<input type='hidden' name='QUANTITY' value=''>";
    
    echo "<table width='90%' cellpadding=0 cellspacing=0 border=0>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<TR><TD class='titleBig' colspan='3'>New Ticket</TD></TR>";
    echo "<TR>";
    echo "<TD colspan='2'>";
    echo "<hr>";
    echo "</TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD class='boldBig' colspan='2'>General Ticket Details</TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD colspan='2'>";
    echo "<hr>";
    echo "</TD>";
    echo "</TR>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<TR>";
    echo "<TD width='20%' class='bold'>Requester</TD>";
   
    
        echo "<TD><select name='RQID01'>";
        foreach ( $userArray as $users ) {
            echo "<option ";
            if ($users ['ID05'] == $_SESSION ['userID']) {
                echo "SELECTED ";
            }
            echo "value='" . trim ( $users ['ID05'] ) . "'>" . trim ( $users ['NAME05'] ) . "</option>";
        }
        echo "</select></TD>";
    
    echo "</TR>";
    echo "<TR>";
    echo "<TD class='bold'>Classification</TD>";
    foreach ( $_SESSION ['classArray'] as $classList ) {
        if ($classList ['ID09'] == $_REQUEST['class']) {
            echo "<TD>" . trim ( $classList ['CLAS09'] ) . "</TD>";
            echo "<input type='hidden' name='CLAS01' value='" . trim ( $classList ['ID09'] ) . "'>";
        }
    }
    echo "</TR>";
    echo "<TR>";
    echo "<TD class='bold'>Type</TD>";
    $z = 0;
    foreach ( $_SESSION ['typeArray'] as $typeList ) {
        if ( isset( $typeList [$_REQUEST['class']] ['ID'] [$z] ) && $typeList [$_REQUEST['class']] ['ID'] [$z] == $type) {
            echo "<TD>" . trim ( $typeList [$_REQUEST['class']] ['NAME'] [$z] ) . "</TD>";
            echo "<input type='hidden' name='TYPE01' value='" . trim ( $typeList [$_REQUEST['class']] ['ID'] [$z] ) . "'>";
        }
        $z ++;
    }
    echo "</TR>";
    //DI868H - Removed Priority selection capabilities for classification 5 (Pricing Administration)
    //LP0028 - Remove && $type != 51 from if statement
    if( $_REQUEST['class'] != 5 && $_REQUEST['class'] != 9 ){
        echo "<TR>";
        echo "<TD class='bold'>Priority</TD>";
            echo "<TD><select name='PRTY01' class='long'>";
            priority_select_box ( "" );
            echo "</select></TD>";
        echo "</TR>";
    }else{
        echo "<input type='hidden' name='PRTY01' value='3'>";
    }
    //DI868H - End
    //DI868G - Added text field to add short description
    echo "<TR>";
    echo "<TD class='bold'>Short Description</TD>";
    echo "<TD><input type='text' name='shortDescription' value=''></TD>";
    echo "</TR>";
    //DI868G - End
    echo "<TR>";
    echo "<TD class='boldTop'>Long Description</TD>";
    echo "<TD><textarea name='LDES01' cols='75' rows='8'></textarea></TD>";
    echo "</TR>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<TR>";
    echo "<TD colspan='2'>";
    echo "<hr>";
    echo "</TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD class='boldBig' colspan='2'>Supporting Information</TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD colspan='2'>";
    echo "<hr>";
    echo "</TD>";
    echo "</TR>";
    $attributeValues = array();
    if ($type == 75){                                                                   //**LP0046
        if ($_REQUEST['basedOnTicket'] != ""){                                          //**LP0046
            $attributeValues[1387] = $_REQUEST['basedOnTicket']; //Orig. ticket         //**LP0046
            $sqlItem = " select * ";                                                    //**LP0046
            $sqlItem .= " from CIL10 ";                                                 //**LP0046
            $sqlItem .= "  inner join CIL07 ";                                       //**LP0046
            $sqlItem .= "    on ATTR10 = ATTR07 ";                                      //**LP0046
            $sqlItem .= " where CAID10 = " . $_REQUEST['basedOnTicket'] . " ";          //**LP0046
            $sqlItem .= "   and HTYP07 = 'PART' ";                                      //**LP0046
            $sqlItem .= " order by ATTR10 ";                                            //**LP0046
            $resItem = odbc_prepare($conn, $sqlItem);                                    //**LP0046
            odbc_execute($resItem);                                                      //**LP0046
            if($rowItem = odbc_fetch_array($resItem)){                                   //**LP0046
                $attributeValues[1384] = trim($rowItem['TEXT10']); //Item               //**LP0046
                $itemAttr = get_part_info(trim($rowItem['TEXT10']));                    //**LP0046
                $attributeValues[1385] = $itemAttr['PLSC35']; //Family code             //**LP0046
                $attributeValues[1386] = $itemAttr['MAGC35']; //MAPL Group code         //**LP0046
                $attributeValues[1388] = $itemAttr['DSSP35']; //Vendor                  //**LP0046
                $attributeValues[1390] = $itemAttr['PLAN35']; //Purch. officer          //**LP0046
            }                                                                           //**LP0046
        }                                                                               //**LP0046
    }                                                                                   //**LP0046 
    
   $orderArray = array();
    $attributeReturnArray = display_attributes ( $_REQUEST['class'], $type, $attributeValues, $orderArray );
    
    if ($_REQUEST['class'] == 8) {
        //display_related_information( $class, $type, "", "", "add"  );
    //echo "<TR><TD colspan='3'>";
    //echo "<iframe name='iFrame1' src='relatedInformation.php?class=$class&type=$type' width=100% frameborder=0></iframe>";
    //echo "</TD></TR>";
    }
    ?>
    <TR><TD>&nbsp</TD></TR>
    <TR>
        <TD class='bold'><a href='#' onClick="showCarbonCopy()">Carbon Copy</a></TD>
    </TR>
    <TR>
        <td colspan='2'>
        <table width=100% cellpadding=0 cellspacin=0  style="display:none;" id='carbonCopy'>
        <TR>
            <TD width=10%>&nbsp</TD>
            <td class='top'>
            <?php 
            if( !isset( $fieldValue ) ){
                $fieldValue = 0;
            }
            ?>
            <select name='ccList'>
                <?show_user_list_email($userArray, $fieldValue);?>
            </select><br><br>
            <input type='button' value='Add To CC' onClick="copyToCabonCopy()">
            </td>
            <TD>
                <textarea rows="3" cols="50" name='ccSelected'></textarea>
            </TD>
        </TR>
        </table>
        </td>
    </TR>
    <?
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<TR>";
    echo "<TD colspan='2'>";
    echo "<hr>";
    echo "</TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD class='boldBig' colspan='2'>Attachments</TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD colspan='2'>";
    echo "<hr>";
    echo "</TD>";
    echo "</TR>";
    
    echo "<TR>";
    $tempId = $_SESSION ['userID'] . "_" . date ( 'Ymd' ) . "_" . date ( 'his' );
    echo "<TD colspan='2'>";
    echo "<iframe src='attachments.php?attachmentId=$tempId&userID=" . $_SESSION ['userID'] . "' name='attachments' FRAMEBORDER='0' width=100% height='90'></iframe>";
    echo "</TD>";
    echo "</TR>";
    //***************************************************** LP0052_AD *********************************************************
    $teamSSID=0;//for disabling submit button //** LP0052_AD 
    if ($_REQUEST['class'] == 17) //Superrsesion //** LP0052_AD 
        //if($_REQUEST['type'] != 121) //not basic type //** LP0052_AD
        { //** LP0052_AD
            $teamSSID=65;//A-Z - id //** LP0052_AD
            if($_REQUEST['type']==122)$teamSSID=97; //Strategic Stock a-z ID //** LP0052_AD
            $sqlt  = "SELECT * FROM CIL22 JOIN HLP05 ON USER22=ID05"; //** LP0052_AD
            $sqlt .= " where ASCII(LEVL22)>= ".$teamSSID ;//A //** LP0052_AD
            $sqlt .= "   and ASCII(LEVL22)<= ".($teamSSID+90-65);//Z-A; //** LP0052_AD
            $rest = odbc_prepare($conn, $sqlt); //** LP0052_AD
            odbc_execute($rest); //** LP0052_AD
            
            ?> <!--  //** LP0052_AD-->
                <tr> <!--  //** LP0052_AD-->
                	<td> <!--  //** LP0052_AD-->
                	<!--  add logic to display CC when type = 121 -->
                	<?php if( $_REQUEST['type'] != 121 ){?>
                		Assigned To: <span style="color: red; ">*</span>  <!--  //** LP0052_AD-->
                	<?php }else{?>
                		CC To: <span style="color: red; ">*</span>  <!--  //** LP0052_AD-->
                	<?php }?>
                	</td>          <!--  //** LP0052_AD-->
                	<td> <!--  //** LP0052_AD-->
                		<select id="assignedTo" name="assignedTo" onChange="showSubmitRow()"> <!--  //** LP0052_AD-->
                			<option value="none" selected > Chose person </option> <!--  //** LP0052_AD-->
               	<?php 
               	$pCounter = 0;
               	while (($rowt = odbc_fetch_array($rest)) <> false){
               	    $pCounter++;
               	    if( $pCounter == 1 ){
               	        $sOption = " selected ";   
               	    }else{
               	        $sOption = "";   
               	    }
               	?>
               				<option value="<?php echo trim($rowt['ID05']); ?>" <?php echo $sOption;?>> <?php echo trim($rowt['TITL22'])." ". trim($rowt['NAME05']);?></option> <!--  //** LP0052_AD-->
               	<?php } ?>			
                		</select> <!--  //** LP0052_AD-->
                	</td> <!--  //** LP0052_AD-->
                	
                </tr> <!--  //** LP0052_AD-->
                <tr id ="rowSubmit" style="display:none"> <!--  //** LP0052_AD-->
                <script> <!--  //** LP0052_AD-->
                
                </script> <!--  //** LP0052_AD-->
                 
               <?php  //** LP0052_AD-->
               
        }  //** LP0052_AD-->
        if ($teamSSID==0)echo "<TR>";     //** LP0052_AD-->
   //**************************************************** LP0052_AD END ********************************************************
    
    //DI868C added name to submit button so that it can be disabled by validateOrder.php
    echo "<TD><input type='submit' value='Save Ticket' name='submitButton'></TD>";
    echo "</TR>";
    
    echo "</table>";
    
    echo "<input type='hidden' name='tempId' value='$tempId'>";
    echo "</form>";
    echo "</center>";
    echo "</body>";
    page_footer ( "login" );