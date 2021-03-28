<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            user_attributes_options.php<br>
 * Development Reference:   LP0018<br>
 * Description:             This is created for adding and updating new User Attribute options to the system and displaying
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
<title>Add - Edit User Attribute Options | <?php echo $SITE_TITLE;?></title>
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

	$( ".del-option-class" ).each(function(index) {
	    $(this).on("click", function(){
	    	var status = $(this).attr('status');
	    	var statusTxt = status == 1? "Deactivate" : "Activate";
	    	if(confirm("Do you want to " + statusTxt + " this User attribute option?")){
	    		$("#loader").addClass("show");
	    		var id = $(this).attr('id');

	    		var postString = "method=de_attrib_opt&ID=" + id +"&status=" + status;
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
	
	$("#form_attrib_options").submit(function(event) {
		
        $("#insert").prop("disabled", true);
        $("#insert").addClass("disable");
        $("#loader").addClass("show");
        var params = $("#form_attrib_options").serialize();
        var status = $("#status").val();
        var postString = "method=add_attrib_opt&" + params; 
        console.log(postString);

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
                    $("#form_attrib_options")[0].reset();
                    $("#options-table > tbody:last-child").append("<tr><td style='text-align: center;'>"+result.ID+"</td><td>"+result.STXT07+"</td><td>"+result.SORT07+"</td><td>"+ result.ACTIVE +"</td><td style='text-align: center;'><span class='del-option-class' status='0' id='"+result.ID+"'><img src='copysource/images/delete.png' alt='Activate/Deactivate' title='Activate/Deactivate'/></span></td></tr>");
                }
                else {
                    $(".error").show();
                    $(".error").fadeOut(5000, "swing");
                }
            }, error: function() {
            	$("#insert").prop("disabled", false);
                $("#insert").removeClass("disable");
                $("#loader").removeClass("show");
                $(".error").show();
                $(".error").fadeOut(5000, "swing");
            }
        });

        return false;
	});

	

	
    });
    
</script>
</head>
<body>

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

if(!isset($_REQUEST['query']) || $_REQUEST['query'] <= 0){
    header("Location: user_attributes_maintenance.php"); /* Redirect to user attribute maintenance*/
    exit();
}

$ID = "";
$title = "Add ";
$status = 0;
$disable = "";
$attrib_id = $_REQUEST['query'];
$opt_text = "";
$active_flag = "";
$sort = "";
$sqlAttrib = "SELECT * FROM HLP06 WHERE ID06 = " . $attrib_id;
$rsAttrib = odbc_prepare($conn, $sqlAttrib);
odbc_execute($rsAttrib);
$AttribData = odbc_fetch_array($rsAttrib);
$attribute = $AttribData['ATTR06'];

if(trim($AttribData['ATYP06']) != 'S'){
    header("Location: user_attributes_maintenance.php"); /* Redirect to user attribute maintenance*/
    exit();
}

if(isset($_REQUEST['noedit']) && $_REQUEST['noedit'] > 0)
{
    $ID = $_REQUEST['id'];
    $title = "Edit ";
    $status = 1;
    $disable = "disabled";
    $sqlEdit = "SELECT * FROM HLP07 WHERE ID07 = " . trim($_REQUEST['id']);
    $rsEdit = odbc_prepare($conn, $sqlEdit);
    odbc_execute($rsEdit);
    while($rowEdit = odbc_fetch_array($rsEdit)){
        $opt_text = trim($rowEdit['STXT07']);
        $active_flag = $rowEdit['ACTV07'];
        $sort = $rowEdit['SORT07'];
    }
    
    $active_flag == 1 ? $active_Y = "checked" : $active_N = "checked";
        
}
/*
$sqlHLP07 = "INSERT INTO HLP07 (ID07, ATTR07, STXT07, ACTV07, SORT07) "
            . "VALUES (1, 1, 'Austrailia', 1, 1)";
$rsHLP07 = odbc_prepare($conn, $sqlHLP07);
odbc_execute($rsHLP07);
$sqlHLP07 = "INSERT INTO HLP07 (ID07, ATTR07, STXT07, ACTV07, SORT07) "
    . "VALUES (2, 1, 'Canada', 1, 2)";
$rsHLP07 = odbc_prepare($conn, $sqlHLP07);
odbc_execute($rsHLP07);
$sqlHLP07 = "INSERT INTO HLP07 (ID07, ATTR07, STXT07, ACTV07, SORT07) "
    . "VALUES (3, 1, 'Pakistan', 1, 3)";
$rsHLP07 = odbc_prepare($conn, $sqlHLP07);
odbc_execute($rsHLP07);
$sqlHLP07 = "INSERT INTO HLP07 (ID07, ATTR07, STXT07, ACTV07, SORT07) "
    . "VALUES (4, 1, 'Swedan', 1, 4)";
$rsHLP07 = odbc_prepare($conn, $sqlHLP07);
odbc_execute($rsHLP07);

exit;
*/

$ResultHTML = "<tbody><tr><td>ID</td><td>Option</td><td>Sort Order</td><td>Active Flag</td><td>Action</td></tr>";
$sqlHLP07 = "SELECT * FROM HLP07 WHERE ATTR07 = " . $attrib_id . " ORDER BY SORT07 ASC";
$rsHLP07 = odbc_prepare($conn, $sqlHLP07);
odbc_execute($rsHLP07);
while($rowHLP07 = odbc_fetch_array($rsHLP07)){
    $active = $rowHLP07['ACTV07'] == 1 ? 'Yes' : 'No';
    $activeness = $rowHLP07['ACTV07'] == 0 ? "style='border: rgb(224, 108, 108) solid 1px;'" : '';
//<span class='icon-left'><a style='background:none;' href='user_attributes_options.php?query={$attrib_id}&id={$rowHLP07['ID07']}'><img src='copysource/images/edit.png' alt='Edit' title='Edit' /></a></span>
    $ResultHTML .= <<<HTML
			  <tr {$activeness}><td style="text-align: center;">{$rowHLP07['ID07']}</td><td>{$rowHLP07['STXT07']}</td><td>{$rowHLP07['SORT07']}</td><td>{$active}</td>
        <td style="text-align: center;"><span class="del-option-class" status='{$rowHLP07['ACTV07']}' id='{$rowHLP07['ID07']}'><img src='copysource/images/delete.png' alt='Activate/Deactivate' title='Activate/Deactivate'/></span></td></tr>
HTML;

}


?>
<!-- Primary Page Layout
================================================== -->
<div id="wrapper">

    <div class="container">
        <div class="panel panel-default" style="width:65%; margin-left: 18%;">
            <div class="panel-heading" style="text-align: center;">
                <h3 class="panel-title"><?php echo $title; ?>Option - <?php echo ucwords($attribute); ?></h3>
                <div style="float: right;height: 50px;margin-top: -47px;">
                        <a href="user_attributes_maintenance.php" class="button-type-link">BACK</a>
                    </div>
            </div><!--panel heading-->
            <div class="panel-body">
                <form class="" id="form_attrib_options" name="form_attrib_options" action="">
                    <div class="box-padding2">
                         <div class="form-row">
                            <label>Option Text</label>
                            <textarea name="opt-txt" class="form-control" style="width: 400px !important; height: auto !important;" placeholder="Option text"><?php echo $opt_text; ?></textarea>
                            <div class="clear"></div>
                        </div><!--form row-->
                        <div class="form-row">
                            <label class="label-right">Active Flag</label>
                            <label class="checkbox-inline" style="width:100px;">
                                <input name="active" type="radio" style="width: 20px;" id="inlineCheckbox1" <?php echo $active_Y; ?> value="1"  required/> YES
                            </label>
                            <label class="checkbox-inline" style="width:100px;">
                                <input name="active" type="radio" style="width: 20px;" id="inlineCheckbox2" <?php echo $active_N; ?> value="0" /> NO
                            </label>
                            <div class="clear"></div>
                        </div><!--form row-->

                        <div class="form-row">
                            <label class="label-right">Sort Order</label>
                            <input type="number" id="sort-order" name="sort-order" min="0" value="<?php echo $sort; ?>" class="form-control" style="width: 205px !important; height: 30px !important;" required placeholder="Sort Order value">
                            <input type="hidden" id="status" name="status" value="<?php echo $status; ?>">
                            <input type="hidden" id="ID" name="ID" value="<?php echo $ID; ?>">
                            <input type="hidden" id="attrib-id" name="attrib-id" value="<?php echo $attrib_id; ?>">
                            <!--<select name="sort-order" required class="form-control" style="width: 150px !important; height: 30px !important;">
                                <option value="0">Select sort order</option>
                                <option value="1">Top</option>
                                <option value="2">Middle</option>
                                <option value="3">Low</option>
                            </select> -->
                            <div class="clear"></div>
                        </div><!--form row-->
                        <div class="form-row">
                            <label class="hidden-xs"></label>
                            <input id="insert" name="insert" type="submit" class="login-btn next-btn" value="SUBMIT" style="margin-top: 10px;">
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
                <div class="loader" id="loader" style="display: none;"></div>
            </div>
        </div>
        
        <div class="clear"></div>
        <div class="panel panel-default">
            <div class="panel" style="">
                <div class="success" style="display: none;"><p>The task has been successfully completed.</p></div>
                <div class="error" style="display: none;"><p>Sorry! Some error occured. Try again!</p></div>
            </div>
        </div>
        <div class="clear"></div>
        
        <div class="panel panel-default">
                    <div class="panel-heading">
                    <h3 class="panel-title"><?php echo ucwords($attribute); ?> - User Attribute Options</h3>
                    </div><!--panel heading-->
                    <div class="panel-body">
                    <table id="options-table" class="data-table sla-data">
                        <?php echo $ResultHTML; ?>                        
                    </table>
            
            </div>
        </div>
        <div class="clear"></div>
        
    </div><!--container-->
</div><!--wrapper-->

<div style="height:250px;"></div>
<!-- End Document
================================================== -->
</body>

</html>