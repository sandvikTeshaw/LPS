<?php 

include_once '../copysource/config.php';
include '../copysource/functions.php';
include '../../common/copysource/global_functions.php';

//error_reporting(E_ALL); 
//ini_set('display_errors', 1);

if (! $conn) {
    	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if( isset( $_REQUEST['syear']) ){
    $syear = $_REQUEST['syear'];
}else{
    $syear = "2017";
}
if( isset( $_REQUEST['smonth']) ){
    $smonth = $_REQUEST['smonth'];
}else{
    $smonth = "02";
}
if( isset( $_REQUEST['sday']) ){
    $sday = $_REQUEST['sday'];
}else{
    $sday = "20";
}

if( isset( $_REQUEST['eyear']) ){
    $eyear = $_REQUEST['eyear'];
}else{
    $eyear = DATE( 'Y' );
}
if( isset( $_REQUEST['emonth']) ){
    $emonth = $_REQUEST['emonth'];
}else{
    $emonth = DATE( 'm' );
}
if( isset( $_REQUEST['eday']) ){
    $eday = $_REQUEST['eday'];
}else{
    $eday = DATE( 'd' );
}
?>

<head>
<title>Survey Results</title>
</head>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(../copysource/styles.css);
-->
</style>
<script type="text/javascript">

function buildDetails( fileName ){

	var syear = this.frmSurveyResults.syear.value;
	var smonth = this.frmSurveyResults.smonth.value;
	var sday = this.frmSurveyResults.sday.value;
	var eyear = this.frmSurveyResults.eyear.value;
	var emonth = this.frmSurveyResults.emonth.value;
	var eday = this.frmSurveyResults.eday.value;

	window.open( 'surveyDetailResults.php?fileName=' + fileName + '&syear=' + syear +'&smonth=' + smonth +'&sday=' + sday + '&eyear=' + eyear +'&emonth=' + emonth +'&eday=' + eday,'', 'width=500,height=500,menubar=no,status=no,location=no,toolbar=no,scrollbars=no')

	return true;
}

</script>
<?php
$skipHeader = "skipHeader";
include_once '../copysource/header.php';


//Fix Data 
$questSql = "SELECT ID41, QTXT41 from CIL41";
$questRes = odbc_prepare ( $conn, $questSql );
odbc_execute ( $questRes );

$questionArray = array();
$answerArray = array();
while ( $row = odbc_fetch_array ( $questRes ) ) {
    
    $questionArray[ $row['ID41'] ] = $row['QTXT41'];
    
    $ansSql = "SELECT ID42, ATXT42 from CIL42 WHERE QID42 = " . $row['ID41'];
    $ansRes = odbc_prepare ( $conn, $ansSql );
    odbc_execute ( $ansRes );
    
    $ansClause = "";

    while ( $rowAns = odbc_fetch_array ( $ansRes ) ) {
        
        $answerArray[ $rowAns['ID42'] ] = $rowAns['ATXT42'];
        $ansClause .= $rowAns['ID42'] . ",";
    }
    
    $ansClause = substr($ansClause, 0, -1);
    
    $fixSql = "DELETE FROM CIL43 WHERE QID43=" . $row['ID41'] . " AND AID43 NOT IN (" . $ansClause . ")";
    $fixRes = odbc_prepare ( $conn, $fixSql );
    odbc_execute ( $fixRes );

}

$date = DATE('Ymdhmi');
$fileName = "SurveyResults_" . $date;


?>
<body>
<center>
<br/><br/>
<form method='post' action='surveyStats.php' name='frmSurveyResults' onsubmit="javascript:return buildDetails( '<?php echo $fileName;?>' )">
<table width=80% border=0 cellpadding=0 cellspacing=0>
   <tr><td class='center' colspan='2'><b>LPS Survey Results</b></td></tr>
   <tr><td>&nbsp;&nbsp;</td></tr>
   <tr>
        <td width='5%'>Start Date:</td>
        <td><?php select_date_listing("syear", "smonth", "sday", $syear, $smonth, $sday);?>&nbsp;&nbsp;***Note: Date of Survey Release is 2017/02/20</td>
   </tr>
   <tr>
        <td>End Date:</td>
        <td><?php select_date_listing("eyear", "emonth", "eday", $eyear, $emonth, $eday);?></td>
   </tr>
   <tr>
        <td colspan='2'>
        <input type='submit' name='submit' value='Submit'/>
        <input type='hidden' name='action' value='continue'/>
        <input type='hidden' name='fileName' value='<?php echo $fileName;?>'/>
        </td>
   </tr>
</table>
</form>

<?php 

if( $_REQUEST['action'] != "continue" ){
    die();
}


$startDate = $syear . str_pad($smonth, 2, '0', STR_PAD_LEFT) . str_pad($sday, 2, '0', STR_PAD_LEFT);
$endDate = $eyear . str_pad($emonth, 2, '0', STR_PAD_LEFT) . str_pad($eday, 2, '0', STR_PAD_LEFT);



$resultsSql = "SELECT count(ID43) as count, QID43, AID43 FROM CIL43 WHERE DATE43 >= " . $startDate . " AND DATE43 <= " . $endDate
            . " GROUP BY QID43, AID43 ORDER BY QID43, AID43";

$resultRes = odbc_prepare ( $conn, $resultsSql );
odbc_execute ( $resultRes );

$downloadURL = "./surveyDetails/" .  $_REQUEST['fileName'] . ".csv";

?>
<table width=80% border=0 cellpadding='0' cellspacing='0'>
    <tr><td colspan='3'><a href='<?php echo $downloadURL;?>'>Download Detailed Results</a></td></tr>
    <?php 
    $prevId = 0;
    while ( $rowResults = odbc_fetch_array ( $resultRes ) ) {

        if( $rowResults['QID43'] != $prevId ){
            
            $altCounter=0;
            if( $prevId > 0 ){
            ?>
            <tr><td>&nbsp;&nbsp;</td></tr>
            <?php 
            }
            ?>
            
            <?php $prevId = $rowResults['QID43'];?>
            <tr class='header'>
                <td class='boldBig' colspan='2'><b><?php echo $questionArray[ $rowResults['QID43'] ];?></b></td>
                <td class='boldBig'><b>Count</b></td>
              </tr>
            <?php 
        }
        if( $altCounter % 2 ){
        ?>
            <tr>
        <?php
        }else{
        ?>
            <tr class='alternate'>
        <?php 
        }
        ?>
            <td width=5%>&nbsp;</td>
            <td width=50% class='regBig'><?php echo $answerArray[ $rowResults['AID43'] ];?></td>
            <td class='regBig'><?php echo $rowResults['COUNT'];?></td>
          </tr>
 

   <?php 
        $altCounter++;
    }
   ?>
</table>
</center>