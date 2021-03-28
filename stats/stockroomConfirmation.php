<?php

include_once '../copysource/config.php';
include '../copysource/functions.php';
include '../../common/copysource/global_functions.php';

global $conn;

if (! $conn) {
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
    echo "Connection Failed";
}

if ($_SESSION ['email']) {
    $userInfo [] = "";
    $userInfo = user_cookie_info ( $_SESSION ['email'] );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['email'] = $_SESSION ['email'];
    
    if (! $_COOKIE ["mtp"]) {
        setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
    }

} elseif ($_COOKIE ["mtp"]) {
    $userInfo [] = "";
    $userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
    $_SESSION ['userID'] = $userInfo ['ID05'];
    $_SESSION ['name'] = $userInfo ['NAME05'];
    $_SESSION ['email'] = $_COOKIE ["mtp"];
    
} else {
    
    error_mssg ( "NONE" );
}

?>
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
<?php
$skipHeader = "skipHeader";
include_once '../copysource/header.php';
echo "<body>";

if( $_POST['action'] == "" ){
	?>
	<body vlink='blue'>
	<center>
	<form method='post' action='stockroomConfirmation.php'>
	<table width=60% border=0>
		<tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
	   <tr>
            <td colspan='2' class='center'><b>
            Stockroom Confirmation</b></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
	   <tr>
	       <td>Start Date:
	       <td><?select_date_listing("syear", "smonth", "sday", $syear, $smonth, $sday)?></td>
	   </tr>
	   <tr>
           <td>End Date:
           <td><?select_date_listing("eyear", "emonth", "eday", $eyear, $emonth, $eday)?></td>
       </tr>
       <tr>
            <td>
            <input type='hidden' name='action' value='continue'>
            <input type='submit' value='continue'>
            </td>
       </tr>
	</table>   
	</form>
	</center>
    <? 
	
	
}else{

	$startDate = $syear;
	if( strlen( $smonth ) == 1  ){
		$startDate .= "0".$smonth;
	}else{
		$startDate .= $smonth;
	}
	if( strlen( $sday ) == 1  ){
		$startDate .= "0".$sday;
	}else{
		$startDate .= $sday;
	}
	
	$endDate = $eyear;
	if( strlen( $emonth ) == 1  ){
		$endDate .= "0".$emonth;
	}else{
		$endDate .= $emonth;
	}
	if( strlen( $eday ) == 1  ){
		$endDate .= "0".$eday;
	}else{
		$endDate .= $eday;
	}
	

	//echo $startDate. "<hr>";
	//echo $endDate. "<hr>";
	
	
	$sql = "SELECT STRC01,CODE01, NAME05, ID01, DATE01, TYPE04, TEXT10, KEY101, KEY201
		   FROM CIL01 T1
		   INNER JOIN CIL04 T2
		   ON T1.TYPE01 = T2.ID04
		   INNER JOIN HLP05 T3
		   ON T1.RQID01 = T3.ID05
		   INNER JOIN CIL10 T4
		   ON T1.ID01 = T4.CAID10
		   WHERE date01>=$startDate and date01 <= $endDate and clas01=3 and ( attr10=21 or attr10=50 or attr10=55 or attr10=74)
		   ORDER BY ID01";
	 
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
	
	ob_start("compressBuffer");
	?>
	<br/><br/>
	
	<?php $date = DATE('Ymdhmi');
	$fname = './confReports/StockroomConfirmation_' . $date . '.csv';
	$fp = fopen( $fname, 'a+');
	$headerlineInsert = "Stockroom,Company,Requester,Ticket,Date,Type,Item Number,PFC Confirmation, Planner Confirmation" .  "\r\n";
		fwrite($fp, $headerlineInsert );
	
	?>
	
	<table width=100% class='list'>
		<tr>
			<td><a href='<?php echo $fname;?>'>[Download Report]</a></td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr class='headerBlue'>
			<td class='headerWhite'>Stockroom</td>
			<td class='headerWhite'>Company</td>
			<td class='headerWhite'>Requester</td>
			<td class='headerWhite'>Ticket</td>
			<td class='headerWhite'>Date</td>
			<td class='headerWhite'>Type</td>
			<td class='headerWhite'>Item<br>Number</br></td>
			<td class='headerWhite'>PFC<br>Confirmation</br></td>
			<td class='headerWhite'>Planner<br>Confirmation</br></td>
		</tr>
	<?php 
	$rowFlag = 0;
	while( $row = odbc_fetch_array( $res ) ){
		$rowFlag ++;
		
		if ($rowFlag % 2) {
			echo "<TR>";
		} else {
			echo "<TR class='alternate'>";
		}
		?>
			<td><?php echo trim($row['STRC01']);?></td>
			<td><?php echo trim($row['CODE01']);?></td>
			<td><?php echo trim($row['NAME05']);?></td>
			<td><?php echo trim($row['ID01']);?></td>
			<td><?php echo trim($row['DATE01']);?></td>
			<td><?php echo trim($row['TYPE04']);?></td>
			<td><?php echo trim($row['TEXT10']);?></td>
			<td>
			<?php 
			if( trim($row['KEY101']) == "Y" ){
				?>Yes<?php 
			}elseif( trim($row['KEY101']) == "N" ){
				?>No<?php
			}elseif( trim($row['KEY101']) == "N/A" ){
				?>N/A<?php
			}else{
				?>-<?php 
			}
			?>
			</td>
			<td>
			<?php 
			if( trim($row['KEY201']) == "Y" ){
				?>Yes<?php 
			}elseif( trim($row['KEY201']) == "N" ){
				?>No<?php
			}elseif( trim($row['KEY201']) == "N/A" ){
				?>N/A<?php
			}else{
				?>-<?php 
			}
			?>
			</td>
		</tr>
		
		<?php 
		
		$lineInsert = trim($row['STRC01']) . "," .  trim($row['CODE01']) . "," .  trim($row['NAME05']) . "," .  trim($row['ID01']) . 
					"," .  trim($row['DATE01']) . "," .  trim($row['TYPE04']) . "," .  trim($row['TEXT10']) . "," .  trim($row['KEY101']) .
					"," .  trim($row['KEY201']) . "\r\n";
		fwrite($fp, $lineInsert);
	}
	
	?>

	</table>
	<?php 
	fclose( $fp );
	page_footer ( "main" );
	ob_flush();
	

}