<?php 

include_once '../copysource/config.php';
include '../copysource/functions.php';
include '../../common/copysource/global_functions.php';

global $conn;

if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
    echo "Connection Failed";
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LPS Statistics</title>

<style type="text/css">
<!--
@import url(../copysource/styles.css);
-->
</style>
<?php
$skipHeader = "skipHeader";
include_once '../copysource/header.php';

?>
<body vlink='blue'>
<center>
<br/><br/>
<table width=60% border=0>
   <tr><td class='center'><b>LPS Statistics</b></td></tr>
   
   <tr><td>&nbsp;&nbsp;</td></tr>
   <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class='center'><a href='inboundStockroom.php'>Inbound Receiving Stockroom</a></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
   <tr>
    <td class='center'><a href='stockroomConfirmation.php'>Stockroom Confirmation Report</a></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <?php 
  if(  $_SESSION['authority'] == "S" ){
      ?>
      <tr>
      <td class='center'><a href='surveyStats.php'>Survey Results</a></td>
      </tr>
      <?php 
  }
  
  ?>
  <tr><td>&nbsp;&nbsp;</td></tr>
   <tr>
    <td class='center'><a href='timeAudit.php'>Time Audit</a></td>
  </tr>
   <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class='center'><a href='view_CIL01EA.php' target='_blank'>Escalation Audit</a></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
   <tr>
    <td class='center'><a href='view_CIL01OA.php' target='_blank'>Owner Change Audit</a></td>
  </tr>
</table>
</center>



