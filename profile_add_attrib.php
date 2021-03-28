<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            profile_add_attrib.php<br>
 * Development Reference:   LP0018<br>
 * Description:             This is developed to add user attributes to a user
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
<title>Add User Profile Attribute | <?php echo $SITE_TITLE;?></title>
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
	
	$("#user-attrib").change(function() {
        var dropdata = $(this).val();
        var data = dropdata.split('-');
        console.log(data);
        var AttribID = data[0];
        var AttribType = data[1];
        var postString = "method=get_options&ID=" + AttribID + "&type=" + AttribType;
        $.ajax({
            type: 'post',
            url: 'ajax_services.php',
            data: postString,
            success: function(result) {
            	var json = $.parseJSON(result);
                $("#div-options").html(json.DATA);
            }
        });
    });


	$("#attrib-add-form").submit(function(event) {
		
        $("#insert").prop("disabled", true);
        $("#insert").addClass("disable");
        $("#loader").addClass("show");
        var params = $("#attrib-add-form").serialize();
        var postString = "method=add_profile_attrib&" + params;
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
                    $("#attrib-add-form")[0].reset();
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
$UserID = $_SESSION['userID'];
if(isset($_REQUEST['userID']) && $_REQUEST['userID'] > 0)
    $UserID = $_REQUEST['userID'];
$sql = "SELECT NAME05 FROM HLP05 WHERE ID05 = " . $UserID;
$res = odbc_prepare($conn, $sql);
odbc_execute($res);
$UserData = odbc_fetch_array($res);
$Username = $UserData['NAME05'];

$listAttributes = "<option value=''>Select Attribute</option><br>";
$sqlAttrib = "SELECT * FROM HLP06 ORDER BY SORT06";
$resAttrib = odbc_prepare($conn, $sqlAttrib);
odbc_execute($resAttrib);
while ($rowAttrib = odbc_fetch_array($resAttrib)){
    $listAttributes .= "<option value='". trim($rowAttrib['ID06'])."-". trim($rowAttrib['ATYP06'])."'>" . trim($rowAttrib['ATTR06']) . "</option><br>";
}



?>
<!-- Primary Page Layout
================================================== -->
<div id="wrapper">

            <div class="container">

                <div class="col-md-8 col-sm-8 col-xs-8">

                    <div class="panel panel-default" style="width:65%; margin-left: 18%;">
                        <div class="panel-heading">
                            <h3 class="panel-title" style="text-align: center;">Add User Attributes to <?php echo $Username; ?></h3>
                        </div><!--panel heading-->
                        <div class="panel-body">
                            <form class="" id="attrib-add-form" name="attrib-add-form" action="">
                                <div class="box-padding2">
                                    <div class="form-row">
                                        <div class="form-row">
                                            <label>User Attributes</label>
                                            <select  id="user-attrib" name="user-attrib" class="form-control" style="width: 400px !important;" required>
                                                <?php echo $listAttributes; ?>
                                            </select>
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->
                                    <div class="form-row">
                                        <div class="form-row" id="div-options">
                                            
                                        </div>
                                        <div class="clear"></div>
                                    </div><!--form row-->
                                    <div class="form-row">
                                        <label class="hidden-xs"></label>
                                        <input type="hidden" name="UserID" id="UserID" value="<?php echo $UserID; ?>" />
                                        <input id="insert" name="insert" type="submit" class="login-btn next-btn" value="ADD ATTRIBUTE" style="margin-top: 10px;">
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
                            <div class="success" style="display: none;"><p>New User attribute and its value has been successfully added.</p></div>
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