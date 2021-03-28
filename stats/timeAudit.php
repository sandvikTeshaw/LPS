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

?>
<body>
<?php


$sql = "SELECT ID01,PCPD01, PCPT01, DCPD01, DCPT01, DPFL01, CPDT01, CPTI01 FROM CIL01 WHERE ( PCPD01 <> 0 OR DCPD01 <> 0 OR CPDT01 <> 0 ) ORDER BY ID01";

$res = odbc_prepare( $conn, $sql );
odbc_execute( $res );


?>
<table width=100% class='list'>
<tr>
</tr>
<tr><td>&nbsp;</td></tr>
<tr class='headerBlue'>
<td class='headerWhite'>ID</td>
<td class='headerWhite'>PFC Complete Date</td>
<td class='headerWhite'>PFC Complete Time</td>
<td class='headerWhite'>DRP Complete Date</td>
<td class='headerWhite'>DRP Complete Time</td>
<td class='headerWhite'>DRP</td>
<td class='headerWhite'>Complete Date</td>
<td class='headerWhite'>Complete Time</td>
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
			<td><?php echo trim($row['ID01']);?></td>
			<td><?php echo trim($row['PCPD01']);?></td>
			<td><?php echo trim($row['PCPT01']);?></td>
			<td><?php echo trim($row['DCPD01']);?></td>
			<td><?php echo trim($row['DCPT01']);?></td>
			<td><?php echo trim($row['DPFL01']);?></td>
			<td><?php echo trim($row['CPDT01']);?></td>
			<td><?php echo trim($row['CPTI01']);?></td>
			<td>
			
		</tr>
		
	<?php 
}
	
	?>

	</table>
