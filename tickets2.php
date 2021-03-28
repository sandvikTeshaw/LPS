<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            tickets2.php<br>
 * Development Reference:   LP0025<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP00025      TS    20/08/2017 Uplift of ticket listing
 *  LP0025       KS    08/12/2017 <br>
 *  LP0034       KS    23/01/2018 Private Message Functionality
 *  LP0044       AD    28/08/2018 Add Buttons to Queue - Logistics Complete & Send to Pricing
 *  LP0064       TS    19/12/2018 Fix for IE CSS issues
 *  LP0054       AD    20/05/2019 Create "Assign to ____" Buttons
 *  LP0081       AD    25/07/2019 Queue Filter Corrections
 *  LP0084     AD     30/09/2019 LP0084 - LPS - Allow TSD's to be identified by Item Class and PGMJ Combination
 *  lp0087     AD     21/10/2019    Button assign to inventory Planner
 *
 */
/**
 */
include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
include 'copysource/multiFunctions.php';              

if( !isset( $_SESSION ['userID'] ) ){
    error_mssg ( "AUTHORITY" );
    die();
}
if( isset( $_SESSION ['userID'] ) && $_SESSION ['userID'] == 1021 ){
    ini_set('display_errors', 1); 
    ini_set('display_startup_errors', 1); 
    error_reporting(E_ALL); 
}
if( isset( $_REQUEST['pageCount']) ){    
    
    $pageCount = $_REQUEST['pageCount'];
}else{
    $pageCount = 0;
}


if( !isset($_SESSION['pageStartArray']) || $pageCount == 0 || !isset($pageCount) || $pageCount == "" ){
    
    $_SESSION['pageStartArray'] = array();
}

if( !isset($_REQUEST['from']) ){
    $_REQUEST['from'] = "";
}

if( !isset($_REQUEST['parentNumber']) ){
    $_REQUEST['parentNumber'] = "";
}

if( !isset( $_REQUEST['queryType'] )){
    $_REQUEST['queryType'] = "frontLine";
}

set_time_limit ( 300 );

if( !isset($_REQUEST['company']) && !isset($_SESSION['ticket2_filter_company']) ){
    $_REQUEST['company'] = "";
    $_SESSION['ticket2_filter_company'] = "";
}elseif( isset($_SESSION['ticket2_filter_company']) && !isset($_REQUEST['company']) ){
    $_REQUEST['company'] = $_SESSION['ticket2_filter_company'];
}



if( !isset($_REQUEST['supervisor']) && !isset($_SESSION['ticket2_filter_supervisor']) ){
    $_REQUEST['supervisor'] = "";
    $_SESSION['ticket2_filter_supervisor'] = "";
}elseif( isset($_SESSION['ticket2_filter_supervisor']) && !isset($_REQUEST['supervisor']) ){
    $_REQUEST['supervisor'] = $_SESSION['ticket2_filter_supervisor'];
}
if( !isset($_REQUEST['owner']) && !isset($_SESSION['ticket2_filter_owner']) ){
    $_REQUEST['owner'] = $_SESSION['userID']; 
    $_REQUEST['ticket2_filter_owner'] = $_SESSION['userID']; 
}elseif( isset($_SESSION['ticket2_filter_owner']) && !isset($_REQUEST['owner'] )){
    $_REQUEST['owner'] = $_SESSION['ticket2_filter_owner'];
}elseif( $_REQUEST['from']== "menu" ){
    $_REQUEST['owner'] = $_SESSION['userID'];  
}

if( !isset($_REQUEST['classification']) && !isset($_SESSION['ticket2_filter_classification']) ){
    $_REQUEST['classification'] = "";
    $_SESSION['ticket2_filter_classification'] = "";
}elseif( isset($_SESSION['ticket2_filter_classification']) && !isset($_REQUEST['classification'])){
    $_REQUEST['classification'] = $_SESSION['ticket2_filter_classification'];
}  

if( !isset($_REQUEST['requester']) && !isset($_SESSION['ticket2_filter_requester']) ){
 
    $_REQUEST['requester'] = $_SESSION['userID'];
    $_SESSION['ticket2_filter_requester'] = $_SESSION['userID'];
}elseif( isset($_SESSION['ticket2_filter_requester'])  && !isset($_REQUEST['requester']) ){

    $_REQUEST['requester'] = $_SESSION['ticket2_filter_requester'];
}elseif( $_REQUEST['from']== "menu" ){

    $_REQUEST['requester'] = $_SESSION['userID'];
}else{
 
    $_SESSION['ticket2_filter_requester'] = $_REQUEST['requester'];
}
if( !isset($_REQUEST['type']) && !isset($_SESSION['ticket2_filter_type']) ){
    $_REQUEST['type'] = "";
    $_SESSION['ticket2_filter_type'] = "";
    $selectedType = "";
}elseif( isset($_SESSION['ticket2_filter_type']) && !isset($_REQUEST['type'])){
    $_REQUEST['type'] = $_SESSION['ticket2_filter_type'];
    $selectedType = $_REQUEST['type'];
}else{
    $selectedType = $_REQUEST['type'];
}
if( !isset($_REQUEST['priority']) && !isset($_SESSION['ticket2_filter_priority']) ){
    $_REQUEST['priority'] = "";
    $_SESSION['ticket2_filter_priority'] = "";
}elseif( isset($_SESSION['ticket2_filter_priority']) && !isset($_REQUEST['priority'])  ){
    $_REQUEST['priority'] = $_SESSION['ticket2_filter_priority'];
}   
if( !isset($_REQUEST['partNumber']) && !isset($_SESSION['ticket2_filter_partNumber']) ){
    $_REQUEST['partNumber'] = "";
    $_SESSION['ticket2_filter_partNumber'] = "";
}elseif( isset($_SESSION['ticket2_filter_partNumber']) && !isset($_REQUEST['partNumber'])  ){
    $_REQUEST['partNumber'] = $_SESSION['ticket2_filter_partNumber'];
}  
if( !isset($_REQUEST['status']) && !isset($_SESSION['ticket2_filter_status']) ){
    $_REQUEST['status'] = "";
    $_SESSION['ticket2_filter_status'] = "";
}elseif( isset($_SESSION['ticket2_filter_status']) && !isset($_REQUEST['status']) ){
    $_REQUEST['status'] = $_SESSION['ticket2_filter_status'];
}  
if( !isset($_REQUEST['logic']) && !isset($_SESSION['ticket2_filter_logic']) ){
    $_REQUEST['logic'] = "OR";
    $_SESSION['ticket2_filter_logic'] = "OR";
}elseif( isset($_SESSION['ticket2_filter_logic']) && !isset($_REQUEST['logic'])){
    $_REQUEST['logic'] = $_SESSION['ticket2_filter_logic'];
}  
if( !isset($_REQUEST['planner']) && !isset($_SESSION['ticket2_filter_planner']) ){
    $_REQUEST['planner'] = "";
    $_SESSION['ticket2_filter_planner'] = "";
}elseif( isset($_SESSION['ticket2_filter_planner']) && !isset($_REQUEST['planner']) ){
    $_REQUEST['planner'] = $_SESSION['ticket2_filter_planner'];
} 
if( !isset($_REQUEST['parentNumber'] )){
    $_REQUEST['parentNumber'] = "";
}
?>
<!DOCTYPE html>     
<html>             

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge;" /> <!-- LP0064 - Fix IE CSS Issues -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>    <!-- //**LP0025_KS -->
<script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script> <!-- Add for URL API functionality to support URL in IE -->
<script type="text/javascript"> 

function callClass(){	
	
		var url_string = window.location.href
	    var url = new URL(url_string);
		var typeId = url.searchParams.get("type");
		var param = url.searchParams.get("classification");
        var postString = "method=class_types&param=" + param + "&typeId=" + typeId;
        
        $(document).ready(function(){
            $.ajax({ url: "ajax_services.php",
                    context: document.body,
                    data: postString,
                    success: function(){
                       
                    }});
            });
}
function selAllCheck(){

	var idCount = document.getElementById("idCounter").value; 
	document.getElementById('checkAll').style.display = 'none';
	document.getElementById('uncheckAll').style.display = '';

	  var i;
	  var checks = document.getElementsByName("ticketIds[]");
	  for (i = 0; i < idCount; i++) {
		if(checks[i].parentElement.parentElement.style.display != 'none')  //lp0081_ad
		  checks[i].checked = true;
	    //return true;
	  }
	

}
function uncheckAll(){

	var idCount = document.getElementById("idCounter").value; 
	document.getElementById('checkAll').style.display = '';
	document.getElementById('uncheckAll').style.display = 'none';

	var i;
	  var checks = document.getElementsByName("ticketIds[]");
	  for (i = 0; i < idCount; i++) {
		  checks[i].checked = false;
	    //return true;
	  }
}

function pseudoFuzzy(){																	//**LP0025_KS
																						//**LP0025_KS
	var idCount = document.getElementById("idCounter").value;							//**LP0025_KS
	var i;																				//**LP0025_KS
	var j;																				//**LP0025_KS
	var checks = document.getElementsByClassName("ticketRow");							//**LP0025_KS
	var cell;																			//**LP0025_KS
																						//**LP0025_KS
	for (i = 0; i < idCount; i++) {														//**LP0025_KS
		if ($('#fuzzy01').val() == ''){													//**LP0025_KS
			checks[i].style.display = '';												//**LP0025_KS
		}																				//**LP0025_KS
		else{																			//**LP0025_KS
			checks[i].style.display = 'none'; 											//**LP0025_KS
																						//**LP0025_KS
			for (j = 0; j < checks[i].getElementsByTagName("td").length; j++){			//**LP0025_KS
				cell = checks[i].getElementsByTagName("td")[j].innerHTML;				//**LP0025_KS
																						//**LP0025_KS
				if (cell.search($('#fuzzy01').val()) >= 0){								//**LP0025_KS
					checks[i].style.display = '';										//**LP0025_KS
					break;																//**LP0025_KS
				}																		//**LP0025_KS
			} //j																		//**LP0025_KS
		}																				//**LP0025_KS
	} //i																				//**LP0025_KS
	//return true;																		//**LP0025_KS
}																						//**LP0025_KS
																						//**LP0025_KS
function collapseChildren(idParent){													//**LP0025_KS
																						//**LP0025_KS
	var idCount = document.getElementById("idCounter").value;							//**LP0025_KS
	var i;																				//**LP0025_KS
	var j;																				//**LP0025_KS
	var checks = document.getElementsByClassName("ticketRow");							//**LP0025_KS
	var cell;																			//**LP0025_KS
																						//**LP0025_KS
	for (i = 0; i < idCount; i++) {														//**LP0025_KS
																						//**LP0025_KS
		cell = checks[i].getElementsByTagName("td")[2].innerHTML;						//**LP0025_KS
		if (cell == idParent){															//**LP0025_KS
			if (checks[i].style.display == 'none'){										//**LP0025_KS
				if ($('#fuzzy01').val() == ''){											//**LP0025_KS
					checks[i].style.display = '';										//**LP0025_KS
				}else{																	//**LP0025_KS
					for (j = 0; j < checks[i].getElementsByTagName("td").length; j++){	//**LP0025_KS
						cell = checks[i].getElementsByTagName("td")[j].innerHTML;		//**LP0025_KS
																						//**LP0025_KS
						if (cell.search($('#fuzzy01').val()) >= 0){						//**LP0025_KS
							checks[i].style.display = '';								//**LP0025_KS
							break;														//**LP0025_KS
						}																//**LP0025_KS
					} //j																//**LP0025_KS
				}																		//**LP0025_KS
			}else{																		//**LP0025_KS
				checks[i].style.display = 'none';										//**LP0025_KS
			}																			//**LP0025_KS
		}																				//**LP0025_KS
																						//**LP0025_KS
	} //i																				//**LP0025_KS
	//return true;																		//**LP0025_KS
}																						//**LP0025_KS
																						//**LP0025_KS
function hiddenFilter1(){																//**LP0025_KS
	var checks = document.getElementById("filter_visibility1");							//**LP0025_KS 
	var element = document.getElementsByClassName("table-container-body")[0]			//**LP0025_KS
				  .getElementsByTagName("tbody")[0];									//**LP0025_KS
																						//**LP0025_KS
	if (checks.style.display == ''){													//**LP0025_KS
		checks.style.display = 'none';													//**LP0025_KS
		document.getElementById("ExpandFilter1").style.display = '';					//**LP0025_KS
		document.getElementById("CollapseFilter1").style.display = 'none';				//**LP0025_KS
		element.style.height = '550px';													//**LP0025_KS
	}else{																				//**LP0025_KS
		checks.style.display = '';														//**LP0025_KS
		document.getElementById("ExpandFilter1").style.display = 'none';				//**LP0025_KS
		document.getElementById("CollapseFilter1").style.display = '';					//**LP0025_KS
		element.style.height = '350px';													//**LP0025_KS
	}																					//**LP0025_KS
}																						//**LP0025_KS
																						//**LP0025_KS
function targetNew(newWindow){															//**LP0025_KS															
	var x = document.getElementById("multiUpdate");										//**LP0025_KS
	if (newWindow == 1){																//**LP0025_KS
		x.target = '_blank';															//**LP0025_KS
	}else{																				//**LP0025_KS
		x.target = '';																	//**LP0025_KS
	}																					//**LP0025_KS
	return;																				//**LP0025_KS
}																						//**LP0025_KS
																						//**LP0025_KS
$(document).ready(function(){															//**LP0025_KS
    $("#fuzzy01").keyup(function(event){ 												//**LP0025_KS
    	pseudoFuzzy();																	//**LP0025_KS
    });																					//**LP0025_KS
});																						//**LP0025_KS
																						//**LP0025_KS

</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE_TITLE;?></title>
<meta charset="utf-8">


<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<!-- Web Font -->
<link href="http://fonts.googleapis.com/css?family=Ubuntu:300,400,500" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script type="text/javascript" src="copysource/jquery.js"></script> 
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script type="text/javascript" src="copysource/jquery.tablesorter.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>                


<style type="text/css">                                      
                                                             
.table-container-body table tbody, table thead{              
  /*display: block;                                           
}                                                            
                                                             
.table-container-body tbody{                                
	overflow-y: auto;                                       
	overflow-x: hidden;                                     
	height: 350px;                                          
                                            
}                                                            
</style> 													
		    												 


<script>
$(document).ready(function(){

	$(function () {
	    $("select#classification").change();

	    $( "#partNumber" ).autocomplete({
	        source: 'partSearch.php'
	    });
	  
	    
	});

	$("#classification").change(function() {
        var ClassID = $(this).val();
        var typeId = document.getElementById("selectedType").value;
		
        var postString = "method=class_types&param=" + ClassID + "&typeId=" + typeId;
        $.ajax({
            type: 'post',
            url: 'ajax_services.php',
            data: postString,
            success: function(result) {
                $("#ticket-types").html(result);
               
            }
        });
    });

   
	$("#outbound-table").tablesorter(); 
});
</script>
</head>
<body onload='callClass()'>

<?php 
//D0301 - Added to compress output to remove all white space
ob_start("compressBuffer");
?>
<!-- Primary Page Layout
================================================== -->
<?php

if (!isset( $conn )) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_connect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
    echo "Connection Failed";
}

include_once 'copysource/header.php';

if ($_SESSION ['userID']) {

    if (! $_SESSION ['classArray'] ) {
        $_SESSION ['classArray'] = get_classification_array ();
    }
    if( ! $_SESSION ['typeArray']){
        $_SESSION ['typeArray'] = get_typeName_array ();
    }

    //include_once 'copysource/menu_2.php';
    include_once 'copysource/menu.php';
    //menuFrame ( $SITENAME );
}


$userArray_delInc = get_user_list_del_included();   
$userArray = get_user_list();
$classArray = class_array();
$sClassArray = array( "9"=> "Returns", "3"=> "GOP", "5" => "Pricing", "7" => "Inbound", "8" => "GMM", "11" => "Regional", "17" => "Super" );
$typeArray = types_array();
$companyArray = company_array();

$stockroomArray = array();

    $sRooms = buildStockroomsArray();

$attrArray = array();
$attrSQL = "SELECT HTYP07, ATTR07, CLAS07, TYPE07 FROM CIL07 WHERE HTYP07='PART' OR HTYP07='SODP'";
$attrRes = odbc_prepare($conn, $attrSQL);
odbc_execute($attrRes);
while ($attrRow = odbc_fetch_array( $attrRes )){

    $attrArray[ trim($attrRow['HTYP07']) ][ trim($attrRow['CLAS07']) ][ trim($attrRow['TYPE07']) ] = trim($attrRow['ATTR07']);
}



if( !isset( $_REQUEST['priority']) || $_REQUEST['priority'] == 0 ){
    $selPriority = "N";
}else{
    $selPriority = $_REQUEST['priority'];
}

//$ResultHTML = "<td colspan='15' style='text-align: center;'>Please search something</td>";
//Search form submission
//if(isset($_REQUEST['search'])){
//     echo "<pre>";
//     print_r($_REQUEST);
//     echo "</pre>";
if( !isset( $_REQUEST['status']) ){
    $_REQUEST['status'] = 1;
}
if( $_REQUEST['status'] == '5') {
    $whereClause = "Where STAT01 = " . $_REQUEST['status'];
}else{
    $whereClause = "Where STAT01 <> 5";
}
//**LP0025_KS  if ( $_REQUEST['from']== "menu" ){
//**LP0025_KS      $whereClause .= " AND ( OWNR01 = " . $_SESSION['userID'] . " OR RQID01 = " . $_SESSION['userID'] . ")";
//**LP0025_KS  }else{

    if( isset( $_REQUEST['logic']) ){
        
    }else{
        $_REQUEST['logic'] = "OR" ;
    }
    
    if( $_REQUEST['logic'] == "AND" ){

        If (isset($_REQUEST['company']) && $_REQUEST['company'] != "") {
            $whereClause .= " AND CODE01 = " . $_REQUEST['company'];
        }
        If (isset($_REQUEST['owner']) && $_REQUEST['owner'] != "") {
            $whereClause .= " AND OWNR01 = " . $_REQUEST['owner'];
        }
        If (isset( $_REQUEST['classification']) && $_REQUEST['classification']!= "") {
            $whereClause .= " AND CLAS01 = " . $_REQUEST['classification'];
        }
        If ( $selectedType != 0) {
            $whereClause .= " AND TYPE01 = " . $selectedType;
        }
        
        If (isset($selPriority) && $selPriority != "" && $selPriority != "N" ) {
            $whereClause .= " AND PRTY01 = $selPriority";
        }
        If (isset($_REQUEST['requester']) && $_REQUEST['requester'] != "") {
            $whereClause .= " AND RQID01 = " . $_REQUEST['requester'];
        }
        if( isset($_REQUEST['partNumber']) && $_REQUEST['partNumber'] != "" ){
            $whereClause .= " AND ID01 in ( SELECT CAID10 FROM CIL10 WHERE TEXT10 = '" . $_REQUEST['partNumber']  . "')";
        }
        
        If (isset($_REQUEST['planner']) && $_REQUEST['planner'] != "") {                                                    //**LP0025_KS
            $whereClause .= " AND (POFF01 = " . $_REQUEST['planner'] . " and CLAS01 = 3) ";                                 //**LP0025_KS
        }                                                                                                                   //**LP0025_KS
        
        
        if( $_SESSION['userID'] == 1021 ){
            
            echo $whereClause . "<hr>";
            
      
        }
        
    }else{
        
        If (isset($_REQUEST['company']) && $_REQUEST['company'] != "") {

            $hasCriteria = true;
            $whereClause.= " AND CODE01 = " . $_REQUEST['company'];
        }
        If (isset( $_REQUEST['classification']) && $_REQUEST['classification']!= "") {
            $hasCriteria = true;
            $whereClause.= " AND CLAS01 = " . $_REQUEST['classification'];
        }
        If ( $selectedType != 0) {
            $hasCriteria = true;
            $whereClause.= " AND TYPE01 = " . $selectedType;
        }
        If (isset($selPriority) && $selPriority != "" && $selPriority != "N" ) {
            $hasCriteria = true;
            $whereClause.= " AND PRTY01 = $selPriority";
        }
        if( $_REQUEST['parentNumber'] != "" ){
           
                $hasCriteria = true;
                $whereClause.= " AND PRNT01 =" . $_REQUEST['parentNumber'];
            
        }
        if( isset($_REQUEST['partNumber']) && $_REQUEST['partNumber'] != "" ){
            $hasCriteria = true;
            $whereClause.= " AND ID01 in ( SELECT CAID10 FROM CIL10 WHERE TEXT10 = '" . $_REQUEST['partNumber']  . "')";
        }
        
        $orClause = " AND ( ";
        
        $hasCriteria = false;
        If (isset($_REQUEST['owner']) && $_REQUEST['owner'] != "") {
            $hasCriteria = true;
            $orClause.= " OWNR01 = " . $_REQUEST['owner'] . " OR";
        }
        
        If (isset($_REQUEST['planner']) && $_REQUEST['planner'] != "") {                                                    //**LP0025_KS
            $hasCriteria = true;                                                                                            //**LP0025_KS
            $orClause.= " (POFF01 = " . $_REQUEST['planner'] . " and CLAS01 = 3) OR";                                       //**LP0025_KS
        }                                                                                                                   //**LP0025_KS
        If (isset($_REQUEST['requester']) && $_REQUEST['requester'] != "") {
            $hasCriteria = true;
            $orClause .= " RQID01 = " . $_REQUEST['requester'] . " OR";
        }
        
        if( $hasCriteria ){
            $whereClause .= substr($orClause, 0, -2 ) . ")";
        }
        
    }
   
    
    
        $whereClause .= " FETCH FIRST 200 ROWS ONLY";
//**LP0025_KS  }

    $ResultHTML = "";
   // $ResultHTML = "<td colspan='8'>No Tickets Available for the searched criteria</td>";
   
    //**LP0025_KS  $resultSQL = "SELECT ID01, CLAS01, TYPE01, PRTY01, DATE01, UDAT01, OWNR01, RQID01, POFF01, CODE01, TIME01, UTIM01, UPID01, STRC01, BUYR01, CPDT01, CPTI01, PCPD01, PCPT01 FROM CIL01 " . $whereClause;
    $resultSQL = "SELECT ID01, CLAS01, TYPE01, PRTY01, DATE01, UDAT01, OWNR01, RQID01, POFF01, CODE01, TIME01, UTIM01, UPID01, STRC01, BUYR01, CPDT01, CPTI01, PCPD01, PCPT01, PRNT01, CHLF01, STAT01, STRC01 FROM CIL01 " . $whereClause; //**LP0025_KS

    $rsResource = odbc_prepare($conn, $resultSQL);
    odbc_execute($rsResource);

    if( $_SESSION['userID'] == 1021 ){
       
        echo $resultSQL . "<hr>";
    }
    
    //echo $resultSQL . "<hr>";
    
    $resCounter = 0;
    while ($row = odbc_fetch_array( $rsResource )){
        
        
        if( $resCounter == 0){
            $_SESSION['pageStartArray'][ $pageCount ] = $row['ID01'];
        }
    
        $lastRow = $row['ID01'];
        $resCounter++;
        
        $ticketNo = $row['ID01'];
        //$classificationVal = $classArray[ $row['CLAS01'] ];
        
        $classificationVal = $sClassArray[$row['CLAS01']];
        
        $id = trim($row['ID01']); 
        $criticality = trim($row['PRTY01']);
        //**LP0025_KS  $stockroom = trim($row['STRC01']);
        
        $stockroom = "";
        

           
        if( trim($row['STRC01']) != "" ){
            $stockroom = trim($row['STRC01']);
        }else{
            $stockroom = newStockRoom( $id, $sRooms );
        }

        
        $issueVal = $typeArray[ $row['TYPE01'] ];
        $updaterVal = "";
        $plannerVal = "";
        $ownerVal = "";
        $buyerVal = "";
        $buyerNumber = "";
        $requesterVal = "";
        $lastCommentVal = "";
        $requesterVal = trim(showUserFromArray ( $userArray_delInc, $row['RQID01'] ));
        
        if( strpos($requesterVal, ".") > 0 ){
            $requesterVal = str_replace(".", "<br/>.", $requesterVal);
        }
        $ownerVal = trim(showUserFromArray ( $userArray_delInc, $row['OWNR01'] ));
        if( strpos($ownerVal, ".") > 0 ){
            $ownerVal = str_replace(".", "<br/>.", $ownerVal);
        }
        
        $plannerVal = trim(showUserFromArray ( $userArray_delInc, $row['POFF01'] ));
        if( strpos($ownerVal, ".") > 0 ){
            $plannerVal = str_replace(".", "<br/>.", $plannerVal);
        }

        $updaterVal = trim(showUserFromArray ( $userArray_delInc, $row['UPID01'] ));
        if( strpos($updaterVal, ".") > 0 ){
            $updaterVal = str_replace(".", "<br/>.", $updaterVal);
        }
        
        $created = formatDate ( $row['DATE01'] );
        $lastUpdate = formatDate ( $row['UDAT01'] );
        
        //$resolved = 'Y';                                        //**LP0025_KS
        //if (checkTicketCompletion($row['ID01']) == false){      //**LP0025_KS
        //    $resolved = 'N';                                    //**LP0025_KS
        //}                                                       //**LP0025_KS
        
        if( $row['STAT01'] == 2){
            $resolved = "PFC";
        }elseif ( $row['STAT01'] >= 3 ){
            $resolved = "Comp";
        }else{
            $resolved = "Open";
        }
        
        if( $row['CPDT01'] > 0){
            $completeDate = formatDate ( $row['CPDT01'] );
            $compTimeVal = substr($row['CPTI01'], 0, 2) . ":" . substr($row['CPTI01'], 2, 2) . ":" . substr($row['CPTI01'], 4, 2);
            $completeOutput = $completeDate . "<br/>" . $compTimeVal;
        }else{
            $completeOutput = "-";
        }
        if( $row['PCPD01'] > 0){
            $pfcDate = formatDate ( $row['PCPD01'] );
            $pfcTimeVal = substr($row['PCPT01'], 0, 2) . ":" . substr($row['PCPT01'], 2, 2) . ":" . substr($row['PCPT01'], 4, 2);
            $pfcOutput = $pfcDate. "<br/>" . $pfcTimeVal;
        }else{
            $pfcOutput= "-";
        }
        
        if($lastUpdate == 0 ){
            $updaterVal = "No Update";
            $lastUpdate = "-";
        }
        
        
        if( $row['PRNT01'] > 0){                                                                                                                    //**LP0025_KS 
            $parent = $row['PRNT01'];                                                                                                               //**LP0025_KS
        }elseif ($row['CHLF01'] > 0){                                                                                                               //**LP0025_KS
            $parent = '<a href="javascript:void(0)" onclick="collapseChildren(' . trim($id) .  ');" style="text-decoration: none;" > + </a>';       //**LP0025_KS 
        }else{                                                                                                                                      //**LP0025_KS
            $parent = "  ";                                                                                                                         //**LP0025_KS
        }                                                                                                                                           //**LP0025_KS
        
        $companyVal = $companyArray[ $row['CODE01'] ];
        $createDate = substr($row['DATE01'], 0,4 ) . "-" . substr($row['DATE01'], 4,2 ) . "-" . substr($row['DATE01'], 6,2 );
        

        $start = strtotime( DATE( "Y-m-d" ) );
        $end = strtotime( $createDate );
        
        $daysOpenVal = ceil(abs($end - $start) / 86400);
       
        
        $partAttr = "";
        $orderAttr = "";
        
        if( isset( $attrArray['PART'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ]) ){
            $partAttr = $attrArray['PART'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ];
        }
        if( isset( $attrArray['SODP'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ] ) ){
            $orderAttr = $attrArray['SODP'][ trim($row['CLAS01']) ][ trim($row['TYPE01']) ];
        }
        
        $attrWhereClause = " WHERE CAID10 = " . $row['ID01'];
        
        if( $partAttr != "" &&  $orderAttr != "" ){
            $attrWhereClause .= " AND ( ATTR10 = $partAttr OR ATTR10 = $orderAttr )";
        }elseif( $partAttr != "" &&  $orderAttr == "" ){
            $attrWhereClause .= " AND ( ATTR10 = $partAttr  )";
        }elseif($partAttr == "" &&  $orderAttr != ""  ){
            $attrWhereClause .= " AND ( ATTR10 = $orderAttr  )";
        }
        
        $itemSql = "SELECT TEXT10, ATTR10 from CIL10 $attrWhereClause";
        
        $rsItem = odbc_prepare($conn, $itemSql);
        odbc_execute($rsItem);
        //$item = odbc_fetch_array($rsItem);
        $itemVal = "";
        $orderVal = "";
        while ($attrRowTxt = odbc_fetch_array( $rsItem )){
            if( $attrRowTxt['ATTR10'] == $partAttr ){
                $itemVal = trim($attrRowTxt['TEXT10']);
            }elseif ( $attrRowTxt['ATTR10'] == $orderAttr ){
                $orderVal = trim($attrRowTxt['TEXT10']);
            }
        }
        
        $supplier = "";                                                         //**LP0025_KS
        $supplierSql = "call SUPLP01('$itemVal')";                              //**LP0025_KS
        $supplierRes = odbc_prepare ( $conn, $supplierSql );                     //**LP0025_KS
        odbc_execute ( $supplierRes );                                           //**LP0025_KS
        while ( $supplierRow = odbc_fetch_array ( $supplierRes ) ) {             //**LP0025_KS
            $supplier = trim($supplierRow['DSSP35']) . " - " . trim($supplierRow['SNAM05']);  //**LP0025_KS
        }                                                                       //**LP0025_KS
        
        $buyrSql = "SELECT USER25 from CIL25 WHERE PLAN25 = " . $row['BUYR01'];
        
        $rsBuyr= odbc_prepare($conn, $buyrSql);
        odbc_execute($rsBuyr);
        while ($buyrRow = odbc_fetch_array( $rsBuyr)){
            $buyerNumber = $buyrRow['USER25'];
        }
        
        $buyerVal = trim(showUserFromArray ( $userArray_delInc, $buyerNumber));
        
        if( $row['UDAT01'] > 0  ){
            $stepSql = "SELECT STEP02 from CIL02 WHERE ID02=(SELECT MAX(ID02) FROM CIL02 WHERE CAID02 = " . $row['ID01'] . ")";
            if (isset( $_SESSION ['authority'] ) && ($_SESSION ['authority'] == "E") || ($_SESSION ['authority'] == "") ){            //**LP0034
                $stepSql .= " AND PRVT02 = 'N' ";               //**LP0034
            }                                                   //**LP0034
            
            //echo $stepSql;
            $rsStep= odbc_prepare($conn, $stepSql);
            odbc_execute($rsStep);
            while ($stepRow = odbc_fetch_array( $rsStep)){
                $lastCommentVal = trim($stepRow['STEP02']);
            }
        }
        
        $time = trim($row['TIME01']);
        $uTime = trim($row['UTIM01']);
        $timeVal = substr($time, 0, 2) . ":" . substr($time, 2, 2) . ":" . substr($time, 4, 2);
        if( $uTime > 0 ){
            $updateTimeVal = substr($uTime, 0, 2) . ":" . substr($uTime, 2, 2) . ":" . substr($uTime, 4, 2);
        }else{
            $updateTimeVal = "";
        }
        
        $createdOutput = $created . "<br/>" . $timeVal;
        $updatedOutput = $lastUpdate;
       
        
        if( $updateTimeVal ){
            $updatedOutput .= "<br/>" . $updateTimeVal;
        }
        
        //SLA Logic
        $slaTargetDate = "-";
        $ticketDateTime = trim($row['DATE01']) . " " . trim($row['TIME01']);
        $sqlSLA = "SELECT SLTM45, BDFL45 FROM CIL45 WHERE ACTV45 <> 0 " . " AND CLAS45 = ". trim($row['CLAS01']) ." AND TYPE45 = ". trim($row['TYPE01']). " AND PRTY45 = " . trim($row['PRTY01']);
        $rsSLA = odbc_prepare($conn, $sqlSLA);
        odbc_execute($rsSLA);
        $SLA = odbc_fetch_array($rsSLA);
        
        if(count($SLA) > 1)
        {
            $formatSLA = "+" . trim($SLA['SLTM45']) . " hours";
            //Only Business Days included
            if($SLA['BDFL45'])
            {
                $effectiveDate = strtotime($formatSLA, strtotime($ticketDateTime));
                $finalDate = date("Y/m/d H:i:s",$effectiveDate);
                $hours = hoursWithoutWeekend($ticketDateTime, $finalDate);
                $result = date("Y/m/d H:i:s", strtotime($finalDate)+$hours*3600);
                $weekDay = date("N", strtotime($result));
                $slaTargetDate = $weekDay >= 6 ? date("Y/m/d g:i A", strtotime($result)+48*3600) : date("Y/m/d g:i A", strtotime($result));
            }
            else        //Weekend are also included
            {
                $effectiveDate = strtotime($formatSLA, strtotime($ticketDateTime));
                $slaTargetDate = date("Y/m/d g:i A",$effectiveDate);
            }
        }
        
        if( $slaTargetDate ){
            $slaTargetDate1 = substr($slaTargetDate, 0, 10);
            $slaTargetDate2 = substr($slaTargetDate, 11 );
            
            $slaTargetDate = $slaTargetDate1 . "<br/>" . $slaTargetDate2;
        }

        if( isset( $ticketIds )){
            if( isset($_POST[$ticketIds] ) ){
                if( is_array($_POST[$ticketIds]) || is_object( $_POST[$ticketIds] ))
                {
                    foreach( $_POST[$ticketIds] as $k=>$v){ //**TESHAW - Fix for LP0054 go Live
                        unset($_POST[$k]);                  //**TESHAW - Fix for LP0054 go Live
                    }                                       //**TESHAW - Fix for LP0054 go Live
                }
            }
        }
        $ResultHTML .= <<<HTML
        <!-- //**LP0025_KS  <tr> --> 
        <tr class='ticketRow'>    <!-- //**LP0025_KS -->
            <td align='left'><input type='checkbox' name='ticketIds[]' name='ticketIds[]' value='{$id}' class='chkBoxQueue'></td>
            <td>{$parent}</td> <!-- LP0025_KS -->            
            <td width=3%><a target="_blank" href="showTicketDetails.php?ID01={$ticketNo}" style="font-weight: bold;letter-spacing: -0.5px;line-height: 100%;text-align: center;text-decoration: none;word-wrap: break-word !important; display:block; width:100%; height:100%">{$ticketNo}</a></td> <!-- //LP0025_KS -->
            
            <td><small>{$companyVal}<small></td>
            <td>{$classificationVal}</td>
            <td style="text-align:center">{$criticality}</td>
            <td>{$issueVal}</td>
            <td nowrap>{$itemVal}</td>
HTML;
//**LP0025_KS
        
        if( isset( $_REQUEST['queryType']) ){
        if($_REQUEST['queryType'] == "warehouse" || $_REQUEST['queryType'] == "planner"){
            $ResultHTML .= <<<HTML
            <td><small>{$supplier}</small></td>
HTML;
        } //**LP0025_KS
        }

$ResultHTML .= <<<HTML
<!-- //**LP0025_KS -->
            <td nowrap>{$orderVal}</td>
            <td>{$requesterVal}</td>
            <td nowrap><small>{$createdOutput}</small></td>
            <td nowrap><small>{$updatedOutput}</small></td>
            <!-- //**LP0025_KS  <td nowrap>{$updaterVal}</td> --> 
            <!-- //**LP0025_KS  <td nowrap>{$ownerVal}</td> -->
            <!-- //**LP0025_KS  <td nowrap>{$plannerVal}</td> -->
            <td>{$updaterVal}</td>  <!-- //**LP0025_KS -->
            <td>{$ownerVal}</td>    <!-- //**LP0025_KS -->
            <td>{$plannerVal}</td>  <!-- //**LP0025_KS -->
            <td>{$stockroom}</td>
            <!-- //**LP0025_KS <td nowrap>{$buyerVal}</td> -->
            <td>{$buyerVal}</td> <!-- //**LP0025_KS -->
            <td nowrap>{$daysOpenVal}</td>
            <td><small>{$completeOutput}</small></td>
            
HTML;
            if( isset( $_REQUEST['queryType']) ){
            if($_REQUEST['queryType'] != "frontLine"){
$ResultHTML .= <<<HTML
            <td><small>{$pfcOutput}</small></td>
            <td nowrap><small>{$slaTargetDate}</small></td>
HTML;
            }
            }
            //** "<td><small>{$slaTargetDate}</small></td>" moved after PFC to be according headers of columns        //**LP0025_KS
$ResultHTML .= <<<HTML
            
            <td>{$resolved}</td>  <!-- //**LP0025_KS -->
            <td><small>{$lastCommentVal}</small></td>
        </tr>
HTML;
                
    }

    if( $resCounter == 0 ){
        
        $ResultHTML = "<td colspan='20' style='text-align: center;'s>No Tickets Available for the searched criteria</td>";
        
    }
    
//}

//Array of Requesting Entity Company
$sqlCompanies = "SELECT CODE27, CNAM27 FROM DSH27 order by CNAM27";
$rsCompanies = odbc_prepare($conn, $sqlCompanies);
odbc_execute($rsCompanies);
$listCompanies = "<option value=''>Select the requesting company</option>\n";
if( is_array($companyArray) || is_object( $companyArray ))
{
    foreach($companyArray as $key => $value) {
        $listCompanies .= "<option ";
        if( $key == $_REQUEST['company'])
            $listCompanies .= "SELECTED ";
        $listCompanies .= "value='". $key."'>" . $value . "</option>\n";;
    }
}
//while($rowCompanies = odbc_fetch_array($rsCompanies)){
//    $listCompanies .= "<option ";
//    if($rowCompanies['CODE27'] == $_REQUEST['company'])
//        $listCompanies .= "SELECTED ";
//    $listCompanies .= "value='". trim($rowCompanies['CODE27'])."'>" . trim($rowCompanies['CNAM27']) . "</option>\n";;
//}

//Array of Supervisors
$sqlSupervisors = "SELECT DISTINCT SUPR31, NAME05 FROM CIL31 T1 INNER JOIN HLP05 T2 ON T1.SUPR31 = T2.ID05 ORDER BY NAME05";
$rsSupervisors = odbc_prepare($conn, $sqlSupervisors);
odbc_execute($rsSupervisors);
$listSupervisors = "<option value=''>Select Supervisor</option>\n";
while($rowSupervisors = odbc_fetch_array($rsSupervisors)){
    $listSupervisors .= "<option ";
    if($rowSupervisors['SUPR31'] == $_REQUEST['supervisor'])
        $listSupervisors .= "SELECTED ";
    $listSupervisors .= "value='" . trim($rowSupervisors['SUPR31']) . "'>" . trim($rowSupervisors['NAME05']) . "</option>\n";
}

//Array of Outbound Planners
//$sqlPlanners = "SELECT NAME05,PLAN25 FROM CIL25 T1 INNER JOIN HLP05 T2 ON T1.USER25 = T2.ID05 ORDER BY NAME05";
$sqlPlanners = "select ID05, NAME05, PLAN38 FROM CIL38 T1"
             . " Inner join HLP05 T2"
             . " On T1.PLAN38 = T2.ID05 GROUP BY ID05, NAME05, PLAN38 ORDER BY NAME05";
$rsPlanners = odbc_prepare($conn, $sqlPlanners);
odbc_execute($rsPlanners);
$listPlanners = "<option value=''>Select Outbound Planner</option>\n";
while($rowPlanners = odbc_fetch_array($rsPlanners)){
    $listPlanners .= "<option ";
    if(trim($rowPlanners['PLAN38']) == $_REQUEST['planner'])
        $listPlanners .="SELECTED ";
    $listPlanners .= "value='" . trim($rowPlanners['PLAN38']) . "'>" . trim($rowPlanners['NAME05']) . "</option>\n";
}
//Array of Owners

if( isset($_REQUEST['owner']) && $_REQUEST['owner'] != 0 && $_REQUEST['owner'] != "" ){
    $currentOwner =  $_REQUEST['owner'];
//**LP0025_KS  }elseif( $_REQUEST['from']== "menu" ){
//**LP0025_KS      $currentOwner = $_SESSION['userID'];
}

$listOwners = "<option value=''>Select Owner</option>";
if( is_array($userArray) || is_object( $userArray ))
{
    foreach ( $userArray as $users ) {
        $listOwners .= "<option ";
        if( isset( $currentOwner ) ){
            if (trim($users['ID05']) == trim($currentOwner)) {
                $listOwners .= "SELECTED ";
            }
        }
        $listOwners .= "value='" . trim ( $users ['ID05'] ) . "'>" . trim ( $users ['NAME05'] ) . "</option>";
    }
}

$listRequesters = "<option value=''>Select Requester</option>";
if( is_array($userArray) || is_object( $userArray ))
{
    foreach ( $userArray as $users ) {
        $listRequesters .= "<option ";
        //**LP0025_KS  if(  $_REQUEST['from']== "menu" ){ 
        //**LP0025_KS      if(trim($users['ID05']) == trim($_SESSION['userID'])){
        //**LP0025_KS          $listRequesters.= "SELECTED ";
        //**LP0025_KS      }
        //**LP0025_KS  }elseif (trim($users['ID05']) == trim($_REQUEST['requester'])) {
        
        if (trim($users['ID05']) == trim($_REQUEST['requester'])) { //**LP0025_KS
            $listRequesters.= "SELECTED ";
        }
        $listRequesters.= "value='" . trim ( $users ['ID05'] ) . "'>" . trim ( $users ['NAME05'] ) . "</option>";
    }
}

//List of undefined Classifications
$sqlClass = "SELECT ID09, CLAS09 FROM CIL09 WHERE CLAS09 <> ''"; // Not in CIL45 - CLAS45
$rsClass = odbc_prepare($conn, $sqlClass);
odbc_execute($rsClass);
$listClasses = "<option value=''>Select Classification</option>\n";
while($rowClass = odbc_fetch_array($rsClass)){
    $listClasses .= "<option ";
    if (trim($rowClass['ID09']) == trim($_REQUEST['classification'])) {
        $listClasses.= "SELECTED ";
    }
    
    $listClasses .= "value='" . trim($rowClass['ID09']) . "'>"  . trim($rowClass['CLAS09']) . "</option>\n";
}

//Ticket Status Selected
$openSelected = "";
$closeSelected = "";
if(isset($_REQUEST['status']) && $_REQUEST['status'] == 1){
    $openSelected = "checked";
    $closeSelected = "";
}
if(isset($_REQUEST['status']) && $_REQUEST['status'] == 5){
    $closeSelected = "checked";
    $openSelected = "";
}
if(!isset($_REQUEST['status'])){
    $openSelected = "checked";
    $closeSelected = "checked";
}

if(isset($_REQUEST['logic']) && $_REQUEST['logic'] == "OR" ){
    $orSelected = "checked";
    $andSelected = "";
}
if(isset($_REQUEST['logic']) && $_REQUEST['logic'] == "AND" ){
    $andSelected = "checked";
    $orSelected = "";
}
//lp0081_ad if(!isset($_REQUEST['logic'])) $orSelected = "checked";
if(!isset($_REQUEST['logic'])){
    $andSelected = ""; //lp0081_ad
    $orSelected = "checked";
}


if( isset( $_REQUEST['queryType'] ) ){
    if( $_REQUEST['queryType'] == "frontLine" || $_REQUEST['queryType'] == "" ){
        
        $listTitle = "Front Line View";
    
    }elseif( $_REQUEST['queryType'] == "planner" ){
        
        $listTitle = "Outbound Planner View";
        
    }elseif( $_REQUEST['queryType'] == "warehouse" ){
        
        $listTitle = "Warehouse View";
        
    }
}else{
    $listTitle = "Front Line View";
}

if( !isset($listTitle) ){
    $listTitle = "Front Line View";
}
?>

<div id="wrapper">
    	
        
          <div class="container">
        	
            <div class="col-md-8 col-sm-8 col-xs-8">
            	
                <div class="panel panel-default">
                    <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $listTitle;?>
                    	<?php 
                    	
                    	if( !isset($_REQUEST['queryType']) || !$_REQUEST['queryType'] ){
                    	    $_REQUEST['queryType'] = "frontLine";
                    	}
                    	
                    	if( $_SESSION ['authority'] != "E" ){
                    	    if( $_REQUEST['queryType'] == "frontLine" ){
                        	?>
                        		&nbsp;&nbsp;<font size='2'><a href='tickets2.php?queryType=planner'>Planner View</a></font>&nbsp;&nbsp;<font size='2'><a href='tickets2.php?queryType=warehouse'>Warehouse View</a></font>
                        	<?php     
                    	    }elseif ($_REQUEST['queryType']== "planner"){
                        	?>
                        		&nbsp;&nbsp;<font size='2'><a href='tickets2.php?queryType=frontLine'>Front Line View</a></font>&nbsp;&nbsp;<font size='2'><a href='tickets2.php?queryType=warehouse'>Warehouse View</a></font>
                        	<?php 
                        	    
                    	    }elseif ($_REQUEST['queryType']== "warehouse"){
                        	    ?>
                        		&nbsp;&nbsp;<font size='2'><a href='tickets2.php?queryType=frontLine'>Front Line View</a></font>&nbsp;&nbsp;<font size='2'><a href='tickets2.php?queryType=planner'>Planner View</a></font>
                        	<?php 
                        	    
                        	}
                    	}
                    	?>
                    </h3>
                    </div><!--panel heading-->
                    
                    <div id="filter_visibility1"> <!-- //**LP0025_KS  -->
                    
                    <div class="panel-body">
                    	<form class="" id="search_details" name="search_details" action="">
                        <div class="box-padding2">
                        <div class="form-row">
                            <div class="form-row-left">
                                <label>Requesting Entity Country</label>
                                <select  name="company" class="form-control" style="width: 400px !important;">
                                    <?php echo $listCompanies; ?>
                                </select>
                            </div>
                            <div class="form-row-right">
                                <label class='label-right'>Supervisor</label>
                                <select  name="supervisor" class="form-control" style="width: 400px !important;">
                                <?php echo $listSupervisors; ?>
                                </select>
                            </div>
                        <div class="clear"></div>
                        </div><!--form row-->
                        
						
						<div class="form-row">
						<div class="form-row-left">
                            <label>Owner</label>
                            <select  name="owner" class="form-control" style="width: 400px !important;">
                                <?php echo $listOwners; ?>
                            </select>
                            <!-- <input id="ownerName" name='ownerName' value='<?php //echo $_REQUEST['ownerName'];?>'><input id="owner" name='owner' value='<?php //echo $_REQUEST['owner'];?>'>-->
                        </div>
                        <div class="form-row-right">
                            <label class='label-right'>Classification</label>
                            <select  id="classification" name="classification" class="form-control" style="width: 400px !important;">
                            <?php echo $listClasses; ?>
                       	 	</select>
                         </div>
                     
                        <div class="clear"></div>
                        </div><!--form row-->
						
						<div class="form-row">
						  <div class="form-row-left">
                            <label>Requester</label>
                            <select  name="requester" class="form-control" style="width: 400px !important;">
                                <?php echo $listRequesters; ?>
                            </select>
                        </div>
                        <div class="form-row-right">
                            <label class='label-right'>Ticket Type</label>
                            <select id="ticket-types" name="type" class="form-control" style="width: 400px !important;">
                                <option value=''>Select the Ticket Type</option>
                            </select>
                            <input type='hidden' name='selectedType' id='selectedType' value='<?php echo $selectedType;?>'>
                        </div>
                        <div class="clear"></div>
                        </div><!--form row-->
                        
						<div class="form-row">
						  <div class="form-row-left">
                        <label>Priority</label>
                        <select  name="priority" class="form-control" style="width: 400px !important;">
                                <option SELECTED value='N'>Select Priority</option>
                                <?php echo priority_select_box( $selPriority ); ?>
                            </select>
                        </div>
                        <div class="form-row-right">
                            <label class='label-right'>Part (Starts With)</label>
                            <input id="partNumber" name='partNumber' value='<?php echo $_REQUEST['partNumber'];?>'>
                        </div>
                        <div class="clear"></div>
                        </div><!--form row--> <!-- //**LP0025_KS -->
                        
                        <div class="form-row">																	<!-- //**LP0025_KS -->
						  <div class="form-row-left">															<!-- //**LP0025_KS -->
                        <label>Outbound Planner</label>															<!-- //**LP0025_KS -->
                            <select  name="planner" class="form-control" style="width: 400px !important;">		<!-- //**LP0025_KS -->
                                <?php echo $listPlanners; ?>													<!-- //**LP0025_KS -->	
                            </select>																			<!-- //**LP0025_KS -->
                        </div>																					<!-- //**LP0025_KS -->
                        <div class="form-row-right">															<!-- //**LP0025_KS -->
                            																					<!-- //**LP0025_KS -->
                        </div>
                        <div class="form-row-right">
                            <label class='label-right'>Parent (Exact Match)</label>
                            <input id="parentNumber" name='parentNumber' value='<?php echo $_REQUEST['parentNumber'];?>'>
                        </div>
                        <div class="clear"></div>																<!-- //**LP0025_KS -->
                        </div><!--form row-->                                                                   <!-- //**LP0025_KS -->
                        
                        

						<div class="form-row">
							<div class="form-row-left">
                            <label>Status</label>
                            <label class="checkbox-inline" style="width:150px;">
                            <input name="status" type="radio" style="width: 20px;" id="inlineCheckbox1" value="1" <?php echo $openSelected; ?> /> OPEN
                            </label>
                            <label class="checkbox-inline" style="width:150px;">
                            <input name="status" type="radio" style="width: 20px;" id="inlineCheckbox2" value="5" <?php echo $closeSelected; ?> /> CLOSED
                            </label>
                        </div>
                        <div class="form-row-right">
                            <label class='label-right'>Logic</label>
                            <label class="checkbox-inline" style="width:150px;">
                            <input name="logic" type="radio" style="width: 20px;" id="inlineCheckbox3" value="OR" <?php echo $orSelected; ?> /> OR
                            </label>
                            <label class="checkbox-inline" style="width:150px;">
                            <input name="logic" type="radio" style="width: 20px;" id="inlineCheckbox4" value="AND" <?php echo $andSelected; ?> /> AND
                            </label>
                        </div>
                        <div class="clear"></div>
                        </div><!--form row-->
						
                        <div class="form-row">
                        <label class="hidden-xs"></label>
                        <input type="hidden" name="queryType" id="queryType" value="<?php echo $_REQUEST['queryType'];?>"/> 
                        <input name="search" type="submit" class="login-btn next-btn" value="SEARCH">
                        
                             
                        <div class="clear"></div>
                        </div><!--form row-->
                        
                        </div><!--box-padding-->
                        </form>
                    </div><!--panel body-->
                    
                    </div> <!-- filter_visibility1 -->      <!-- //**LP0025_KS  -->
                    
                </div><!--panel box-->
                <div class="clear"></div>
                <div class="panel panel-default">
                    <div class="panel-body">

                    <div align="left" style="font-size: 10px;">																				    <!-- //**LP0025_KS  -->
                       <a id= "ExpandFilter1" style="display: none" href="javascript:void(0)" onclick="hiddenFilter1();">Expand Filter</a>      <!-- //**LP0025_KS  -->
                       <a id= "CollapseFilter1" href="javascript:void(0)" onclick="hiddenFilter1();">Collapse Filter</a>                        <!-- //**LP0025_KS  -->
                    </div>																													    <!-- //**LP0025_KS  -->
                    
                    
                    
                    <div align="right">															  <!-- //**LP0025_KS  -->
                    <form action="javascript:return pseudoFuzzy();">            			      <!-- //**LP0025_KS  -->
                       	<label for="fuzzy01"> Filter Results: </label>						      <!-- //**LP0025_KS  -->
                     	<input type="text" id="fuzzy01" class="fuzzy01"></input>                  <!-- //**LP0025_KS  -->
                    </form>                                                                       <!-- //**LP0025_KS  -->
                    </div>																		  <!-- //**LP0025_KS  -->
                    
                    
                    <!--//**LP0025_KS <form method='post' action='multiUpdate.php'>       -->
                    <form method='post' id='multiUpdate' name='multiUpdate' action='multiUpdate.php'>					                      <!-- //**LP0025_KS -->
                    <input type='hidden' name='queryType' id='queryType' value="<?php echo $_REQUEST['queryType'];?>"/>                       <!-- //**LP0025_KS -->
                    
                     <div class="table-container-body"> <!-- //**LP0025  -->
                                        
                    <table id="outbound-table" class="data-table tablesorter">
                    <thead>
                    	                                                
                        <tr id='checkAll'><td colspan='2'><a href="javascript:void(0)" onclick="selAllCheck();">Check All</a></td></tr>
                        <tr id='uncheckAll' style="display:none"><td colspan='2'><a href="javascript:void(0)" onclick="uncheckAll();">Uncheck All</a></td></tr>
                        <tr>
                        	<th>&nbsp;</th>
                        	<th>Parent</th> <!-- LP0025_KS -->
                            <th>Ticket</th>
                            <th>Company</th>
                            <th>Class</th>
                            <th>Prty</th>
                            <th>Type</th>
                            <th>Item</th>
                            <?php                                            //**LP0025_KS 
                            if($_REQUEST['queryType'] == "warehouse" || $_REQUEST['queryType'] == "planner"){       //**LP0025_KS 
                          	     ?><th>Supplier</th><?php                    //**LP0025_KS
                          	}                                                //**LP0025_KS
                          	?>												 <!-- //**LP0025_KS -->
                            <th>Order</th>
                            <th>Requester</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th nowrap>Update By</th>
                            <th>Owner</th>
                            <th>Planner</th>
                            <th>StkRm</th>
                            <th>Buyer</th>
                            <th>Days Open</th>
                            <th>Complete</th>
                            <?php 
                          	if($_REQUEST['queryType'] != "frontLine"){
                          	     ?><th>PFC Date</th>
                          	       <th>SLA Target</th>
                          	     <?php 
                          	}
                          	?>
                          	
                          	<th>Stat</th title='Complete' alt='Complete'>								    <!-- //**LP0025_KS -->
                            <th>Last Comment</th>
                        </tr>
                    </thead>
                    
                   
                      
                    <tbody>
                        <?php echo $ResultHTML; ?>
                    </tbody>
                                     
                    </table>
                    
                    </div> <!-- //**LP0025  -->
                    
                                        
           			<br/>
<?php                                                                                     //**LP0025_KS 
           			      echo "<i>" . "Number of tickets: " . "</i>" . $resCounter;      //**LP0025_KS       
           			      echo "<br />";                                                  //**LP0025_KS

?>																				          <!-- //**LP0025_KS -->           			
           			<br/>
        <!-- //**LP0054_AD            <table class='data-table'>--> 
                    <table style="width: 100%"> <!-- //**LP0054_AD -->
<?php /*         //**LP0054_AD -->                                  
                    	<td><input id='submit' type='submit' class="login-btn next-btn" name = 'submit' value = 'Add Comments' onClick='targetNew(0)'></td>     <!-- //**LP0025_KS -->
                    	<td><input id='submit' type='submit' class="login-btn next-btn" name = 'submit' value = 'Resolve Tickets' onClick='targetNew(0)'></td>  <!-- //**LP0025_KS -->
                    	<td><input id='submit' type='submit' class="login-btn next-btn" name = 'submit' value = 'Re-Assign' onClick='targetNew(0)'></td>        <!-- //**LP0025_KS -->
                    	<td><input id='submit' type='submit' class="login-btn next-btn" name = 'submit' value = 'Export' onClick='targetNew(1)'></td>    	   <!-- //**LP0025_KS -->
                    	<td><input id='submit' type='submit' class="login-btn next-btn" name = 'submit' value = 'Logistics Complete' onClick='targetNew(0)'></td>        <!-- //**LP0044_AD -->
<?php if ($_SESSION ['authority'] == "L"  || $_SESSION ['authority'] == "S" ) {?> <!-- //**LP0044_AD -->
                    	<td><input id='submit' type='submit' class="login-btn next-btn" name = 'submit' value = 'Send to Pricing' onClick='targetNew(0)'></td>    	   <!-- //**LP0044_AD -->
                    	*/  //**LP0054_AD -->
?> <!-- //**LP0044_AD -->  
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Add Comments' onClick='targetNew(0)'></td>      <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Resolve Tickets' onClick='targetNew(0)'></td>   <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Re-Assign' onClick='targetNew(0)'></td>         <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Export' onClick='targetNew(1)'></td>    	    <!-- //**LP0054_AD -->
<?php if ($_SESSION ['authority'] != "E"  ) {?> <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Logistics Complete' onClick='targetNew(0)'></td>         <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to Pricing' onClick='targetNew(0)'></td>    	   <!-- //**TESHAW - Fix for LP0054 go Live                  	
              <!--       	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to TSD' onClick='targetNew(0)'></td>    	   <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to TSD' onClick='targetNew(0)'></td>    	   <!-- //**LP0084_AD -->
                    	<tr> <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to Buyer' onClick='targetNew(0)'></td>    	   <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to PFC' onClick='targetNew(0)'></td>    	   <!-- //**LP0054_AD -->
                     	<!-- <td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to Warehouse' onClick='targetNew(0)'></td>     <!-- //**LP0054_AD -->
                     	<!-- <td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to Sourcing' onClick='targetNew(0)'></td>     <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to Freight' onClick='targetNew(0)'></td>     <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to Requestor' onClick='targetNew(0)'></td>     <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to OBP' onClick='targetNew(0)'></td>    	   <!-- //**LP0054_AD -->
                    	<td><input id='submit' type='submit' class="login-btn " style="width:100%" name = 'submit' value = 'Assign to IP' onClick='targetNew(0)'></td>    	   <!-- //**LP0087_AD -->
                   	
<?php  };?> <!-- //**LP0054_AD -->                   	

                    	
                    	<!-- //**LP0025_KS <td><input id='submit' type='submit' name = 'submit' value = 'Add Comments'></td>       -->
                    	<!-- //**LP0025_KS <td><input id='submit' type='submit' name = 'submit' value = 'Resolve Tickets'></td>    -->
                    	<!-- //**LP0025_KS <td><input id='submit' type='submit' name = 'submit' value = 'Export'></td>             -->
                    	
                    	<td><input type='hidden' name='idCounter' id='idCounter' value='<?php echo $resCounter;?>'/>
                      	 
                    </table>
                    </form>
                    </div>
                </div>
                
            </div><!--col md,sm 8-->
            <div class="clear"></div>
        </div><!--container-->
</div><!--wrapper-->
<!-- //**LP0025_KS   <div style="height:250px;"></div>   -->



<!-- End Document
================================================== -->
</body>

</html>
<?php 
odbc_close( $conn );
?>
<?php 
function stockrooms($ticket){                                           //**LP0025_KS
    global $conn, $companyCode;
   
    //**LP0025_KS
    $sql = "select substr(c.NAME07, 1, 2) as STRC07 ";                  //**LP0025_KS
    $sql .= " from CIL07 a ";                                           //**LP0025_KS
    $sql .= " inner join CIL10 b ";                                    //**LP0025_KS
    $sql .= " on a.ATTR07 = b.ATTR10 ";                              //**LP0025_KS
    $sql .= " inner join CIL07 c ";                                    //**LP0025_KS
    $sql .= " on b.TEXT10 = CAST( c.ATTR07 as varchar(10)) ";        //**LP0025_KS
    $sql .= " where a.NAME07 like '%Stockroom%' ";                      //**LP0025_KS
    $sql .= " and b.CAID10 = " . $ticket . " ";                       //**LP0025_KS
    
    //**LP0025_KS
    $stmt = odbc_prepare($conn, $sql);                                   //**LP0025_KS
    $result = odbc_execute($stmt);                                       //**LP0025_KS
    //**LP0025_KS
    $returnStockrooms = "";                                                   //**LP0025_KS
    $idCounter = 0;                                                     //**LP0025_KS
    while ($row = odbc_fetch_array($stmt)){                              //**LP0025_KS
        if (++$idCounter > 1){                                          //**LP0025_KS
            $returnStockrooms .= ", ";                                        //**LP0025_KS
        }                                                               //**LP0025_KS
        $returnStockrooms .= $row['STRC07'];                                  //**LP0025_KS
    }                                                                   //**LP0025_KS
    return $returnStockrooms;                                                 //**LP0025_KS
}                                                                       //**LP0025_KS
//**LP0025_KS


function newStockRoom( $ticket, $sRoomArray ){
    global $conn, $companyCode;
    
    $stockRoomIds = "";
    foreach ($sRoomArray as $key => $sIDs ){
        
        $stockRoomIds .= $key . ",";
        
    }
    
    $stockRoomIds = substr($stockRoomIds, 0, -1 );
    $getStockRoomSql = "SELECT TEXT10 FROM CIL10 WHERE CAID10=$ticket AND ATTR10 in ( $stockRoomIds )";
    
    $res = odbc_prepare($conn, $getStockRoomSql);
    odbc_execute($res);
    
    while ($row = odbc_fetch_array($res)){
        
        return trim($row['TEXT10']);
    }
    
    return "";
    
    
    
}

function buildStockroomsArray(){
    global $conn, $companyCode;
    
    
    $sql = "select ATTR07, NAME07 from CIL07 WHERE NAME07 like '%Stockroom%' AND HTYP07='DROP'";
    
    $stmt = odbc_prepare($conn, $sql);                                   //**LP0025_KS
    $result = odbc_execute($stmt);
    
    $parentArray = array();
    while ($row = odbc_fetch_array($stmt)){                              //**LP0025_KS
        array_push( $parentArray, $row['ATTR07'] );
    }
    
    $parents = implode(",", $parentArray );
    
    $childSql = "select ATTR07, substr( NAME07, 1, 2) as STRC07 from CIL07 WHERE PRNT07 in (" . $parents . ")";
    $childRes = odbc_prepare($conn, $childSql);
    odbc_execute($childRes);
    
    
    
    $childArray = array();
    while ($childRow = odbc_fetch_array($childRes)){
        
        //$childArray = array_merge($childArray, array( $childRow['ATTR07'] => $childRow['STRC07'] ) );
        $childArray[ $childRow['ATTR07'] ] = $childRow['STRC07'];
        
    }
    
    return $childArray;
    
}

?>
