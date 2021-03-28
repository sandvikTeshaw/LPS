<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            user_attribute.php<br>
 * Development Reference:   LP0018<br>
 * Description:             This is created for adding and updating new User Attribute matrix to the system
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
<title>Add - Edit User Attribute | <?php echo $SITE_TITLE;?></title>
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

	
	$("#form_user_attrib").submit(function(event) {
		
        $("#insert").prop("disabled", true);
        $("#insert").addClass("disable");
        $("#loader").addClass("show");
        var params = $("#form_user_attrib").serialize();
        var status = $("#status").val(); 
        if(status == 0)
            var postString = "method=add_attrib&" + params;
        else
        	var postString = "method=edit_attrib&" + params;
    	
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
                    if(status == 0)
                        $("#form_user_attrib")[0].reset();
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
$ID = "";
$title = "Add ";
$status = 0;
$disable = "";
$attribute = "";
$description = "";
$type = "";
$type_S = "";
$type_T = "";
$activeness = "";
$required = "";
$req_yes = "";
$req_no = "";
$sort = "";
if(isset($_REQUEST['query']) && $_REQUEST['query'] > 0)
{
    $ID = $_REQUEST['query'];
    $title = "Edit ";
    $status = 1;
    $disable = "disabled";
    $sqlEdit = "SELECT * FROM HLP06 WHERE ID06 = " . trim($_REQUEST['query']);
    $rsEdit = odbc_prepare($conn, $sqlEdit);
    odbc_execute($rsEdit);
    while($rowEdit = odbc_fetch_array($rsEdit)){
        $attribute = trim($rowEdit['ATTR06']);
        $description = trim($rowEdit['DESC06']);
        $type = trim($rowEdit['ATYP06']);
        $activeness = $rowEdit['ACTV06'];
        $required = $rowEdit['REQD06'];
        $sort = $rowEdit['SORT06'];
    }
    
    $type == 'S' ? $type_S = "checked" : $type_T = "checked";
    $required == 1 ? $req_yes = "checked" : $req_no = "checked";
    
}


?>
<!-- Primary Page Layout
================================================== -->
<div id="wrapper">

    <div class="container">
        <div class="panel panel-default" style="width:65%; margin-left: 18%;">
            <div class="panel-heading" style="text-align: center;">
                <h3 class="panel-title"><?php echo $title; ?>User Attribute Matrix</h3>
                <div style="float: right;height: 50px;margin-top: -47px;">
                        <a href="user_attributes_maintenance.php" class="button-type-link">BACK</a>
                    </div>
            </div><!--panel heading-->
            <div class="panel-body">
                <form class="" id="form_user_attrib" name="form_user_attrib" action="">
                    <div class="box-padding2">
                        <div class="form-row">
                            <label>User Attribute</label>
                            <input type="text" id="attrib" name="attrib" <?php echo $disable; ?> value="<?php echo $attribute; ?>" class="form-control" style="width: 414px !important; height: 30px !important;" required placeholder="Attribute Name">
                                <div class="clear"></div>
                        </div><!--form row-->

                        <div class="form-row">
                            <label>Description</label>
                            <textarea name="description" class="form-control" style="width: 400px !important; height: auto !important;" placeholder="Description"><?php echo $description; ?></textarea>
                            <div class="clear"></div>
                        </div><!--form row-->
                        <div class="form-row">
                            <label class="label-right">Attribute Type</label>
                            <label class="checkbox-inline" style="width:100px;">
                                <input name="attrib-type" type="radio" style="width: 20px;" id="inlineCheckbox1" <?php echo $type_T; ?> value="T"  required/> TEXT
                            </label>
                            <label class="checkbox-inline" style="width:100px;">
                                <input name="attrib-type" type="radio" style="width: 20px;" id="inlineCheckbox2" <?php echo $type_S; ?> value="S" /> SELECTION
                            </label>
                            <div class="clear"></div>
                        </div><!--form row-->

                        <div class="form-row">
                            <label class="label-right">Sort Order of Attributes</label>
                            <input type="number" id="sort-order" name="sort-order" min="0" value="<?php echo $sort; ?>" class="form-control" style="width: 205px !important; height: 30px !important;" required placeholder="Sort Order value">
                            <input type="hidden" id="status" name="status" value="<?php echo $status; ?>">
                            <input type="hidden" id="ID" name="ID" value="<?php echo $ID; ?>">
                            <!--<select name="sort-order" required class="form-control" style="width: 150px !important; height: 30px !important;">
                                <option value="0">Select sort order</option>
                                <option value="1">Top</option>
                                <option value="2">Middle</option>
                                <option value="3">Low</option>
                            </select> -->
                            <div class="clear"></div>
                        </div><!--form row-->

                        <div class="form-row">
                            <label class="label-right">Required Flag</label>
                            <label class="checkbox-inline" style="width:100px;">
                                <input name="required-flag" type="radio" style="width: 20px;" id="inlineCheckbox1" <?php echo $req_yes; ?> value="1"  required/> YES
                            </label>
                            <label class="checkbox-inline" style="width:100px;">
                                <input name="required-flag" type="radio" style="width: 20px;" id="inlineCheckbox2" <?php echo $req_no; ?> value="0" /> NO
                            </label>
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
                <div class="success" style="display: none;"><p>New User Attribute matrix has been successfully added.</p></div>
                <div class="error" style="display: none;"><p>Sorry! Some error occured. Try again!</p></div>
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