<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            user_attributes_maintenance.php<br>
 * Development Reference:   LP0018<br>
 * Description:             This lists all the user attributes of the user.<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 */
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

set_time_limit ( 300 );

//D0301 - Added to compress output to remove all white space
ob_start("compressBuffer");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--[if IE 8]>    <html class="no-js ie8 ie" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js ie9 ie" lang="en"> <![endif]-->
<!--[if gt IE 9]><![endif]-->
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User Attributes Maintenance | <?php echo $SITE_TITLE;?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<!-- Web Font -->
<link href="http://fonts.googleapis.com/css?family=Ubuntu:300,400,500" rel="stylesheet" type="text/css">
<script type="text/javascript" src="copysource/jquery.js"></script>
<script>
$(document).ready(function(){
	$( ".icon-right" ).each(function(index) {
	    $(this).on("click", function(){
	    	var status = $(this).attr('status');
	    	var statusTxt = status == 1? "Deactivate" : "Activate";
	    	if(confirm("Do you want to " + statusTxt + " this User attribute?")){
	    		$("#loader").addClass("show");
	    		var id = $(this).attr('id');

	    		var postString = "method=de_attrib&ID=" + id +"&status=" + status;
	            $.ajax({
	                type: 'post',
	                url: 'ajax_services.php',
	                data: postString,
	                dataType: 'json',
	                success: function(result) {
	                    $("#loader").html("");
	                    console.log(result);
	                    if (result.CODE == 200) {
		                    if(status == 1)
		                    	  $("#"+id).closest('tr').css("border", "rgb(224, 108, 108) solid 1px");
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
</head>
<body>
<!-- Primary Page Layout
================================================== -->
<?php

if (! $conn) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
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

if(!isset($_SESSION['authority'])||$_SESSION['authority'] != 'S'){    
    header("Location: index.php"); /* Redirect to home page*/
    exit();
}

$ResultHTML = "<tbody><tr><td>ID</td><td>Attribute</td><td>Attribute Type</td><td>Required Flag</td><td>Sort Order</td><td>Active Flag</td><td>Add Options</td><td>Action</td></tr>";
$sqlHLP06 = "SELECT * FROM HLP06 ORDER BY ID06";
$rsHLP06 = odbc_prepare($conn, $sqlHLP06);
odbc_execute($rsHLP06);
//while($rowCIL45 = db2_fetch_assoc($rsCIL45)){
while($rowHLP06 = odbc_fetch_array($rsHLP06)){
    $attr_type = trim($rowHLP06['ATYP06']) == 'S' ? 'Selection' : 'Text';
    $req_flag = $rowHLP06['REQD06'] == 1 ? 'Yes' : 'No';
    $active = $rowHLP06['ACTV06'] == 1 ? 'Yes' : 'No';
    $activeness = $rowHLP06['ACTV06'] == 0 ? "style='border: rgb(224, 108, 108) solid 1px;'" : '';
    $add_options = trim($rowHLP06['ATYP06']) == 'S' ? "<td style='text-align: center;'><span><a style='background:none;' href='user_attributes_options.php?query=" . $rowHLP06['ID06'] . "'><img src='copysource/images/add.png' alt='Add Options' title='Add Options' /></a></span></td>" : "<td style='text-align: center;'>No Options</td>";
    $ResultHTML .= <<<HTML
			  <tr {$activeness}><td style="text-align: center;">{$rowHLP06['ID06']}</td><td>{$rowHLP06['ATTR06']}</td><td>{$attr_type}</td><td>{$req_flag}</td><td>{$rowHLP06['SORT06']}</td><td>{$active}</td>
        {$add_options}
        <td><span class='icon-left'><a style='background:none;' href='user_attribute.php?query={$rowHLP06['ID06']}'><img src='copysource/images/edit.png' alt='Edit' title='Edit' /></a></span>
        <span class='icon-right' status='{$rowHLP06['ACTV06']}' id='{$rowHLP06['ID06']}'><img src='copysource/images/delete.png' alt='Activate/Deactivate' title='Activate/Deactivate'/></span></td></tr>
HTML;
    
}


?>

<div id="wrapper">
    	
        <div class="container">
        	
            <div class="col-md-8 col-sm-8 col-xs-8">
                            <div class="panel panel-default">
                    <div class="panel-heading">
                    <h3 class="panel-title">User Attributes Maintenance</h3>
                    <div style="float: right;height: 50px;margin-top: -47px;">
                        <a href="user_attribute.php" class="button-type-link">ADD User Attribute</a>
                    </div>
                    </div><!--panel heading-->
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
                            <div class="success" id="success" style="display: none;"><p>The Action has been successfully completed.</p></div>
                            <div class="error" id="error" style="display: none;"><p>Sorry! Some error occured. Try again!</p></div>
                        </div>
                    </div>
            </div><!--col md,sm 8-->
            <div class="clear"></div>
        </div><!--container-->
</div><!--wrapper-->
<div style="height:250px;"></div>
<!-- End Document
================================================== -->
</body>

</html>