<?php 

include 'copysource/config.php';
include 'copysource/define.php';
include 'copysource/functions.php';
include 'copysource/functions/massUploadFunctions.php';
include 'copysource/validationFunctions.php';
include 'copysource/functions/programFunctions.php';
include '../common/copysource/global_functions.php';
// require_once 'CW/cw.php';    
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE_TITLE;?></title>
<meta charset="utf-8">

<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>

<!-- Web Font -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    
    <script>
    
$(document).ready(function(){

    $( "select[name='classification']" ).change(function () {

        var classID = $(this).val();
        var postString = "method=class_upload&id=" + classID;
        if(classID) {
            $.ajax({
                url: 'ajax_services.php',
                dataType: 'Json',
                data: postString, 
                success: function(data) {
                    $('select[name="type"]').empty();
                    $.each(data, function(key, value) {
                        $('select[name="type"]').append('<option value="'+ key +'">'+ value +'</option>');
                    });
                }
            });
    
    
        }else{
            $('select[name="type"]').empty();
        }
    });
});
</script>
</head>

<?php 
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

?>
<body>
<div id="wrapper">
	<div class="container">
		<div class="col-md-8 col-sm-8 col-xs-8">
			<div class="panel panel-default">
            	<div class="panel-heading">
                <h3 class="panel-title">Mass Upload Field Options<font size="2">
                	</font></h3>

                </div>
			<div class="panel-body">

<?php 

global $conn;

if (! $conn) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {
    
} else {
    echo "Connection Failed";
    die();
}


if( $action == "" ){
    
   
    ?>
    <form class="" id="continue_options" name="continue_options" action="uploadOptions.php" method="post">
    		<div class="box-padding2">
        	<div class="form-row">
    <div class="form-row-left">
    <label>Select Class:</label>
    <select name="classification" class="form-control">
    <option value="">--- Select Classification ---</option>
    <?php
    foreach ($uploadClasses as $key => $class) {
        ?><option <?php if($_REQUEST['classification'] == $key ) echo"selected"; ?> value='<?php echo $key;?>'>
                                 <?php echo $class;?></option>\n<?php 
                            }
                        ?>
    
    
                    </select>
                </div>
       			<div class="clear"></div>
    			<div class="form-row-left">
                      <label>Select Type:</label>
                <select name="type" id="type" class="form-control" style="width:350px">
                </select>
            	</div>
            	<div class="clear"></div>
            	<div class="clear"><br/></div>
            	<div class="clear"></div>
                        <div class="form-row">
                        <label class="hidden-xs"></label>
                        <input id="continue" name="continue" type="submit" class="login-btn next-btn" value="Continue"/>
                        <input id="action" name="action" type="hidden" value="continue"/>
                        </div>
                        <div class="clear"></div>
      			</div>
      			
    </div>
</div>
</div>
            	
<?php 
    die();
}


$type = $_REQUEST['type'];

$typeName = get_type_name($type);

$attrSql = "SELECT ATTR07, NAME07, HTYP07 FROM CIL07 WHERE TYPE07=$type AND ( HTYP07='DROP' OR HTYP07='COUN'"
         . " OR HTYP07='BRAN' OR HTYP07='MODL' OR HTYP07='REGN')";
$attrRes = odbc_prepare($conn, $attrSql);
odbc_execute($attrRes);  

$optionCount = 0;
?><table cellpadding='5px'>
	<tr><td colspan=3><b><u><font size='6'><?php echo $typeName;?>&nbsp;Options</font></u></b></td></tr>
	<tr valign='top'><td>
<?php 

while ($attRow = odbc_fetch_array($attrRes)){ 
    $optionCount++;
    
    if( trim($attRow['HTYP07']) == 'DROP' ){
        $attrOptionSql = "SELECT NAME07 FROM CIL07 WHERE PRNT07={$attRow['ATTR07']}";
        $titleSpan = 1;
    }elseif ( trim($attRow['HTYP07']) == 'COUN' ){
        $attrOptionSql = "SELECT PSAR15, PRMD15 FROM DESC WHERE CONO15 = 'DI' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY' ORDER BY PRMD15 ASC";
        $titleSpan = 2;
    }elseif ( trim($attRow['HTYP07']) == 'BRAN' ){
        $attrOptionSql = "SELECT BRAN15, DESC15 FROM CIL15 WHERE CONO15 = 'DI' ORDER BY BRAN15";
        $titleSpan = 2;
    }elseif ( trim($attRow['HTYP07']) == 'MODL' ){
        $attrOptionSql = "SELECT MODL27 FROM CIL27 WHERE CONO27 = 'DI' ORDER BY MODL27";
        $titleSpan = 1;
    }elseif ( trim($attRow['HTYP07']) == 'REGN' ){
        $attrOptionSql = "SELECT NAME32 FROM CIL32 NAME32 <>'' AND ACTF32 = 'Y' ORDER BY NAME32";
        $titleSpan = 1;
    }

  
    $attrOptionRes = odbc_prepare($conn, $attrOptionSql);
    odbc_execute($attrOptionRes);  
    
    if( $optionCount == 1 ){
        ?><table border='1' cellpadding='3px'><?php 
    }else{
        ?></table></td><td><table border='1' cellpadding='3px'><?php 
    }
    
    ?><tr><td bgcolor='#0060A0' align='center' colspan=<?php echo $titleSpan;?>><font color='#FFFFFF'><b><?php echo $attRow['NAME07'];?></b></td></tr><?php 
    if( trim($attRow['HTYP07']) == 'COUN' ||  trim($attRow['HTYP07']) == 'BRAN'  ){
        ?><tr><td bgcolor='#0060A0' colspan=<?php echo $titleSpan;?>><font color='#FFFFFF'><small>Use Code in Upload</small></td></tr><?php
        ?><tr><td bgcolor='#0060A0'><font color='#FFFFFF'><b>Code</b></td><td bgcolor='#0060A0'><font color='#FFFFFF'><b>Description</b></td></tr><?php 
    }
        
    while ($optionsRow = odbc_fetch_array($attrOptionRes)){  
        
        if( trim($attRow['HTYP07']) == 'DROP' ){
            ?><tr><td><?php echo $optionsRow['NAME07'];?></td></tr><?php 
        }elseif ( trim($attRow['HTYP07']) == 'COUN' ){
            ?><tr><td><?php echo trim($optionsRow['PSAR15']);?></td><td><?php echo trim($optionsRow['PRMD15']);?></td></tr><?php 
        }elseif ( trim($attRow['HTYP07']) == 'BRAN' ){
            ?><tr><td><?php echo trim($optionsRow['BRAN15']);?></td><td><?php echo trim($optionsRow['DESC15']);?></td></tr><?php 
        }elseif ( trim($attRow['HTYP07']) == 'MODL' ){
            ?><tr><td><?php echo $optionsRow['MODL27'];?></td></tr><?php 
        }elseif ( trim($attRow['HTYP07']) == 'REGN' ){
            ?><tr><td><?php echo $optionsRow['NAME32'];?></td></tr><?php
        }
        
    }
    
}
?></td></tr></table>