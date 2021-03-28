<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            add_sla.php<br>
 * Development Reference:   LP0019<br>
 * Description:             add_sla.php is created for adding new SLA matrix to the system
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
<title>Add SLA | <?php echo $SITE_TITLE;?></title>
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
<script type="text/javascript">
$(document).ready(function() {
	
	$("#classification").change(function() {
        var ClassID = $(this).val();
        var postString = "method=class_types&param=" + ClassID;
        $.ajax({
            type: 'post',
            url: 'ajax_services.php',
            data: postString,
            success: function(result) {
                $("#ticket-types").html(result);
               
            }
        });
    });


	$("#form_add_sla").submit(function(event) {
		
        $("#insert").prop("disabled", true);
        $("#insert").addClass("disable");
        $("#loader").addClass("show");
        var params = $("#form_add_sla").serialize();
        var postString = "method=add_sla&" + params;
        $.ajax({
            type: 'post',
            url: 'ajax_services.php',
            data: postString,
            dataType: 'json',
            success: function(result) {
                $("#insert").prop("disabled", false);
                $("#insert").removeClass("disable");
                $("#loader").removeClass("show");
                console.log(result);
                if (result.CODE == 200) {
                    $(".success").show();
                    $(".success").fadeOut(5000, "swing");
                    $("#form_add_sla")[0].reset();
                }
                else{
                    $(".error").show();
                    $(".error").fadeOut(5000, "swing");
                }
            }, error: function() {
                $(".error").show();
                $(".error").fadeOut(5000, "swing");
            }
        });

        return false;
	});
	
    $("input[name$='escal-flag']").change(function() {
        if ($(this).val() == 1){
            $("#esclation-div").show();
            $("#esclation-inc").prop('required',true);
        }
        else {
            $("#esclation-div").hide();
            $("#esclation-inc").prop('required',false);
        }
    });
    
    
    
});
</script>
</head>
<body>

<?php

if (! $conn) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
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

if(!isset($_SESSION['authority'])||$_SESSION['authority'] != 'S'){
    header("Location: index.php"); /* Redirect to home page*/
    exit();
}

//List of undefined Classifications
$sqlClass = "SELECT ID09, CLAS09 FROM CIL09"; // Not in CIL45 - CLAS45
$rsClass = odbc_prepare($conn, $sqlClass);
odbc_execute($rsClass);
$listClasses = "<option value=''>Select Classification</option>\n";
while($rowClass = odbc_fetch_array($rsClass)){
    $listClasses .= "<option ";
    $listClasses .= "value='" . trim($rowClass['ID09']) . "'>" . trim($rowClass['CLAS09']) . "</option>\n";
}


$priorities = priority_short_list(0);
//List of priorities
$listPriorities = "<option value=''>Select the Priority</option>\n";
foreach ($priorities as $key => $priority){
    $listPriorities .= "<option ";
    $listPriorities .= "value='". trim($key+1)."'>" . trim($priority) . "</option>\n";

}

?>
<!-- Primary Page Layout
================================================== -->
<div id="wrapper">

            <div class="container">

                <div class="col-md-8 col-sm-8 col-xs-8">

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Add SLA Matrix</h3>
                        </div><!--panel heading-->
                        <div class="panel-body">
                            <form class="" id="form_add_sla" name="form_add_sla" action="">
                                <div class="box-padding2">
                                    <div class="form-row">
                                        <div class="form-row-left">
                                            <label>Classification</label>
                                            <select  id="classification" name="classification" class="form-control" style="width: 400px !important;" required>
                                                <?php echo $listClasses; ?>
                                            </select>
                                        </div>
                                        <div class="form-row-right">
                                            <label class='label-right'>Ticket Type</label>
                                            <select id="ticket-types" name="type" class="form-control" style="width: 400px !important;" required>
                                                <option value=''>Select the Ticket Type</option>
                                            </select>
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->

                                    <div class="form-row">
                                        <div class="form-row-left">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" style="width: 400px !important; height: auto !important;" placeholder="Optional Description"></textarea>
                                        </div>
                                        <div class="form-row-right">
                                            <label class="label-right">Priority</label>
                                            <select  name="priority" class="form-control" style="width: 400px !important;" required>
                                                <?php echo $listPriorities; ?>
                                            </select>
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->

                                    <div class="form-row">
                                        <div class="form-row-left">
                                            <label>Escalation Flag</label>
                                            <label class="checkbox-inline" style="width:100px;">
                                                <input name="escal-flag" type="radio" style="width: 20px;" id="inlineCheckbox1" value="1"  required/> ON
                                            </label>
                                            <label class="checkbox-inline" style="width:100px;">
                                                <input name="escal-flag" type="radio" style="width: 20px;" id="inlineCheckbox2" value="0" /> OFF
                                            </label>
                                        </div>
                                        <div class="form-row-right">
                                            <label class="label-right">Include Only Business Days</label>
                                            <label class="checkbox-inline" style="width:100px;">
                                                <input name="business-flag" type="radio" style="width: 20px;" id="inlineCheckbox1" value="1"  required/> YES
                                            </label>
                                            <label class="checkbox-inline" style="width:100px;">
                                                <input name="business-flag" type="radio" style="width: 20px;" id="inlineCheckbox2" value="0" /> NO
                                            </label>
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->

                                    <div class="form-row">
                                        <div class="form-row-left" id="esclation-div" style="display:none;">
                                            <label>Escalation Increments</label>
                                            <input type="number" id="esclation-inc" name="escalation-inc" class="form-control" min="0" style="width: 150px !important; height: 30px !important;" placeholder="Escalation Increments">
                                        </div>
                                        <div class="form-row-right" style="float:right;">
                                            <label class="label-right">SLA Time to Complete</label>
                                            <input type="number" name="sla-time" required class="form-control" min="0" style="width: 150px !important; height: 30px !important;" placeholder="SLA Time in Hrs">
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->
                                    
                                    <div class="form-row">
                                        <div class="form-row-right" style="float:right;">
                                            <label class="label-right">Time to First Response</label>
                                            <input type="number" name="first-response" required class="form-control" min="0" style="width: 150px !important; height: 30px !important;" placeholder="Firs Response in Hrs">
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->

                                    <div class="form-row">
                                        <label class="hidden-xs"></label>
                                        <input id="insert" name="insert" type="submit" class="login-btn next-btn" value="ADD SLA" style="margin-top: 10px;">
                                            <div id="loader" style=""></div>
                                            <div class="clear"></div>
                                    </div><!--form row-->

                                </div><!--box-padding-->
                            </form>
                        </div><!--panel body-->
                    </div><!--panel box-->
                    <div class="clear"></div>
                    <div class="panel panel-default">
                        <div class="panel" style="">
                            <div class="success" style="display: none;"><p>New SLA matrix has been successfully added.</p></div>
                            <div class="error" style="display: none;"><p>Sorry! Some error occured. Try again!</p></div>
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