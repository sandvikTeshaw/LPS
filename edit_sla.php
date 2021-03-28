<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            edit_sla.php<br>
 * Development Reference:   LP0019<br>
 * Description:             edit_sla.php is created for updating previously added SLA matrix in the system
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
<title>Edit SLA | <?php echo $SITE_TITLE;?></title>
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
	
		$("#form_edit_sla").submit(function(event) {
		
        $("#update").prop("disabled", true);
        $("#update").addClass("disable");
        $("#loader").addClass("show");
        var params = $("#form_edit_sla").serialize();
        var postString = "method=edit_sla&" + params;
        $.ajax({
            type: 'post',
            url: 'ajax_services.php',
            data: postString,
            dataType: 'json',
            success: function(result) {
                $("#update").prop("disabled", false);
                $("#update").removeClass("disable");
                $("#loader").removeClass("show");
                console.log(result);
                if (result.CODE == 200) {
                    $(".success").show();
                    $(".success").fadeOut(5000, "swing");
                    window.location = "sla_maintenance.php";
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

$ID = isset($_REQUEST['query']) && $_REQUEST['query'] != "" ? trim($_REQUEST['query']) : 0;
if($ID == 0){
    header ("Location: sla_maintenance.php");
    exit;
}

$sql = "SELECT C1.*, C2.CLAS09, C3.TYPE04 FROM CIL45 C1 INNER JOIN CIL09 C2 ON C2.ID09 = C1.CLAS45 INNER JOIN CIL04 C3 ON C3.ID04 = C1.TYPE45 WHERE C1.ID45 = " . $ID;
$res = odbc_prepare($conn, $sql);
odbc_execute($res);
$matrixData = odbc_fetch_array($res);

$prio = $matrixData['PRTY45'];
$class = $matrixData['CLAS09'];
$type = $matrixData['TYPE04'];
$description = $matrixData['DESC45'];
$SLA_time = $matrixData['SLTM45'];
$first_response = $matrixData['FRTM45'];
$escalation_inc = $matrixData['ETIN45'];
$escal_flag = $matrixData['ESFL45'];
$business_flag = $matrixData['BDFL45'];
$esflag_on = $esflag_off = $busflag_yes = $busflag_no = "";
$escal_flag == 1 ? $esflag_on = "checked" : $esflag_off = "checked";
$business_flag == 1 ? $busflag_yes = "checked" : $busflag_no = "checked";

$priorities = priority_short_list(0);
//List of priorities
$listPriorities = "<option value=''>Select the Priority</option>\n";
foreach ($priorities as $key => $priority){
    $listPriorities .= "<option ";
    if(($key+1) == $prio)
        $listPriorities .= "SELECTED ";
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
                            <form class="" id="form_edit_sla" name="form_edit_sla" action="">
                                <div class="box-padding2">
                                    <div class="form-row">
                                        <div class="form-row-left">
                                            <label>Classification</label>
                                            <input id="classification" disabled="true" name="classification" value="<?php echo $class;?>" class="form-control" style="width: 400px !important;height: 30px !important;">
                                        </div>
                                        <div class="form-row-right">
                                            <label class='label-right'>Ticket Type</label>
                                            <input id="ticket-types" disabled="true" name="type" value="<?php echo $type;?>" class="form-control" style="width: 400px !important;height: 30px !important;">
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->

                                    <div class="form-row">
                                        <div class="form-row-left">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" style="width: 400px !important; height: auto !important;" placeholder="Optional Description"><?php echo $description;?></textarea>
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
                                                <input name="escal-flag" type="radio" style="width: 20px;" id="inlineCheckbox1" value="1"  <?php echo $esflag_on; ?> required/> ON
                                            </label>
                                            <label class="checkbox-inline" style="width:100px;">
                                                <input name="escal-flag" type="radio" style="width: 20px;" id="inlineCheckbox2" value="0" <?php echo $esflag_off; ?> /> OFF
                                            </label>
                                        </div>
                                        <div class="form-row-right">
                                            <label class="label-right">Include Only Business Days</label>
                                            <label class="checkbox-inline" style="width:100px;">
                                                <input name="business-flag" type="radio" style="width: 20px;" id="inlineCheckbox1" value="1"  <?php echo $busflag_yes; ?> required/> YES
                                            </label>
                                            <label class="checkbox-inline" style="width:100px;">
                                                <input name="business-flag" type="radio" style="width: 20px;" id="inlineCheckbox2" value="0" <?php echo $busflag_no; ?> /> NO
                                            </label>
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->

                                    <div class="form-row">
                                        <div class="form-row-left" id="esclation-div" style="">
                                            <label>Escalation Increments</label>
                                            <input type="number" id="esclation-inc" name="escalation-inc" class="form-control" min="0" style="width: 150px !important; height: 30px !important;" placeholder="Escalation Increments" value="<?php echo $escalation_inc; ?>">
                                        </div>
                                        <div class="form-row-right" style="float:right;">
                                            <label class="label-right">SLA Time to Complete</label>
                                            <input type="number" name="sla-time" required class="form-control" min="0" style="width: 150px !important; height: 30px !important;" placeholder="SLA Time in Hrs" value="<?php echo $SLA_time; ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->
                                    
                                    <div class="form-row">
                                        <div class="form-row-right" style="float:right;">
                                            <label class="label-right">Time to First Response</label>
                                            <input type="number" name="first-response" required class="form-control" min="0" style="width: 150px !important; height: 30px !important;" placeholder="Firs Response in Hrs" value="<?php echo $first_response; ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->

                                    <div class="form-row">
                                        <label class="hidden-xs"></label>
                                        <input type="hidden" name="ID" required value="<?php echo $ID; ?>">
                                        <input id="update" name="update" type="submit" class="login-btn next-btn" value="UPDATE SLA" style="margin-top: 10px;">
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
                            <div class="success" style="display: none;"><p>New SLA matrix has been successfully updated.</p></div>
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