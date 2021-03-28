<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            sla_maintenance.php<br>
 * Development Reference:   LP0019<br>
 * Description:             sla_maintenance.php List all current SLA entries.<br>
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
<title>SLA Maintenance | <?php echo $SITE_TITLE;?></title>
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
		    if(confirm("Do you want to delete this SLA maintenance?")){
		    	    $("#loader").addClass("show");
		    	    var id = $(this).attr('id');
		    	    var postString = "method=delete_sla&param=" + id;
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
		    	    
			        
			        console.log(id);
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

$ResultHTML = "";
$sqlCIL45 = "SELECT C45.*, C9.CLAS09, C4.TYPE04 FROM CIL45 C45 INNER JOIN CIL09 C9 ON C45.CLAS45 = C9.ID09 INNER JOIN CIL04 C4 ON C4.ID04 = C45.TYPE45 WHERE ACTV45 = 1 order by CLAS45, TYPE45";
$rsCIL45 = odbc_prepare($conn, $sqlCIL45);
odbc_execute($rsCIL45);

$prevType = 0;
while($rowCIL45 = odbc_fetch_array($rsCIL45)){
    $priority = priority_short_list($rowCIL45['PRTY45']);
    
    if( $prevType== 0 || $prevType!= trim($rowCIL45['TYPE45']) ){
        $prevType= trim($rowCIL45['TYPE45']);
        $ResultHTML .= <<<HTML
		<thead><tr><th colspan='7'>{$rowCIL45['CLAS09']} - {$rowCIL45['TYPE04']}</th></tr></thead>
        <tbody><tr><td>Priority</td><td>SLA Time to Complete</td><td>Time to First Response</td><td>Escalation Increments</td><td>Business Days Only</td><td>Description</td><td>Action</td></tr>
HTML;

        }

        $ResultHTML .= <<<HTML
        
			  <tr><td>{$priority}</td><td>{$rowCIL45['SLTM45']}</td><td>{$rowCIL45['FRTM45']}</td><td>{$rowCIL45['ETIN45']}</td><td>{$rowCIL45['BDFL45']}</td><td>{$rowCIL45['DESC45']}</td>
        <td><span class='icon-left'><a style='background:none;' href='edit_sla.php?query={$rowCIL45['ID45']}'><img src='copysource/images/edit.png' alt='Edit' title='Edit' /></a></span>
        <span class='icon-right' id='{$rowCIL45['ID45']}'><img src='copysource/images/delete.png' alt='Delete' title='Delete'/></span></td></tr>
HTML;
    
}


?>

<div id="wrapper">
    	
        <div class="container">
        	
            <div class="col-md-8 col-sm-8 col-xs-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                    <h3 class="panel-title">SLA Maintenance</h3>
                    <div style="float: right;height: 50px;margin-top: -47px;">
                        <a href="add_sla.php" class="button-type-link">ADD SLA</a>
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
                            <div class="success" style="display: none;"><p>The SLA matrix has been successfully deleted.</p></div>
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