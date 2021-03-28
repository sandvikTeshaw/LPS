<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            multiSurveyResponse.php<br>
 * Development Reference:   LP0025<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP00025      TS    20/08/2017 Uplift of ticket listing
 */
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--[if IE 8]>    <html class="no-js ie8 ie" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js ie9 ie" lang="en"> <![endif]-->
<!--[if gt IE 9]><![endif]-->
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE_TITLE;?></title>
<meta charset="utf-8">

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
.survey{
	list-style-type: none;
	padding: 0;
}
.surveyTitle{
	list-style-type: none;
	font-weight: bold;
	font-size: 18px;
	padding-bottom: 10px;
}
ul p{
	color: red;
	width: 20%;
	float: left;
}
.surveyList{
	width:60%;
	float: auto;
	text-align: left;
	font-size: 12px;
	font-weight: bold;

}
.radioLabel{
    width:100%;
	padding-left: 0;
	overflow: hidden;
}
input .radio{
	margin-right: 0;
	padding-right: 0;
	text-align: left;
	display: inline; 
	overflow: hidden;
}
.radioInput{
	width:30%;
	padding-left: 0;
	float: left;
    overflow: hidden;
}
.buttonClass{
	display:block;
	width:205px;
	overflow: hidden; 
	padding-left: 30px;
}
.buttonForm{
	width:100px;
	overflow: hidden;
	float: left;
	
}
.addInfoStyle {
	width: 57%;
	font-weight: bold;
	font-size: 15px;
	text-align: left;
	padding-left: 30px;

}
</style>
<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<!-- Web Font -->


<!-- Primary Page Layout
================================================== -->
<?php

if (!isset($conn)) {
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
?>
<form method='post' action='saveMultiSurveyResponse.php'>
<?php 
$surveyCounter = 0;
$surveySql = "SELECT * FROM CIL41 WHERE ACTF41 = 1";
$surveyRes = odbc_prepare ( $conn, $surveySql );
odbc_execute ( $surveyRes );

while( $surveyRow = odbc_fetch_array( $surveyRes )){
    $surveyCounter++;
    
}

if( $surveyCounter > 0 ){
    
    ?><ul>
        <li class='surveyTitle'>
        <?php echo $SURVEY_HEADING;?>
        </li>
    </ul>
    <?php 
    $surveySql2 = "SELECT * FROM CIL41 WHERE ACTF41 = 1 ORDER BY QSRT41";
    $surveyRes2 = odbc_prepare ( $conn, $surveySql2 );
    odbc_execute ( $surveyRes2 );
    while( $surveyRow2 = odbc_fetch_array( $surveyRes2 )){
    
        ?>
        <ul class='surveyList'>
            <li class='survey'>
            <?php echo $surveyRow2['QTXT41'];?>
            </li>
            <div class='radioLabel'>
            <?php 
            $answerSql = "SELECT * FROM CIL42 WHERE ACTF42 = 1 and QID42 = " . $surveyRow2['ID41'] . " ORDER BY ASRT42";
            $answerRes = odbc_prepare ( $conn, $answerSql );
            odbc_execute ( $answerRes );
            
            while( $answerRow = odbc_fetch_array( $answerRes )){
                ?>
                <div class='radioInput'>
                <input type='radio' name='q_<?php echo $surveyRow2['ID41']?>[]' value='<?php echo $answerRow['ID42'];?>' class='radio'/>
                
                <?php echo $answerRow['ATXT42'];?>
                </div>
                <?php 
            }
            ?>
        </div>
        </ul>
        <br/>   
        <?php 
    }
    ?>
   
    <div class='addInfoStyle'>
        <?php echo trim($SURVEY_ADDITIONAL_INFO_LABEL);?>
    </div>
    <div class='addInfoStyle'><textarea rows="4" cols="75" name='addInfo' id='addInfo'></textarea></div>
    
    <?php 
    }
?>

    <div class='buttonClass'>
    <br>
    <?php 
    foreach ( $ticketIds as $selId ) {
         ?><input type='hidden' name='ticketIds[]' id='ticketIds[]' value='<?php echo $selId;?>'/><?php
     }
     ?>
	<input type='submit' name='' value='Submit' class='buttonForm'>
	</form>
	<form method='post' action='tickets2.php?status=1&from=menu' class='buttonForm'>
	<input type='submit' name='' value='Continue' class='buttonForm'>
	</form>
	</div>
	 </center>