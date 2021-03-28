<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			lostPassword.php<br>
 * Development Reference:	DI868<br>
 * Description:				lostPassword.php application page for sending account information to users<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0097     TJS     12/04/2010  re-write userMaintenance for new escalation<br>  
 */
/**
 */
global $conn;
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';


if (!isset($conn) ) {
	//$Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if (isset($conn) ) {

} else {
	echo "Connection Failed";
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
@import url(copysource/styles.css);
-->
</style>
</head>
<?
//include_once 'copysource/header.php';

?>
<body>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td class='title'>Lost Password</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
</table>
<?php 
if( isset($_REQUEST['action']) && $_REQUEST['action'] == "password" && ( !isset($_REQUEST['continue']) || $_REQUEST['continue'] == "") ){
?>
<form method='post' action='lostPassword.php'>
<table>
	<tr>
		<td class='bold'>Email:</td>
		<td>
			<input type='text' name='email' value='@sandvik.com'/>
			<input type='hidden' name='continue' value='Y'/>
		</td>
	</tr>
	<tr>
		<td><input type='submit' value='Continue'/></td>
</table>
</form>
	<?php 
}else{
		//D0097b - Added replace to deal with CCSID issue
    if( isset($_REQUEST['email'])){
		$emailSent = trim(strtolower($_REQUEST['email']));
		$emailSent = strtr($emailSent, $GLOBALS['normalizeSaveChars']);
   
		
		$recordCount = count_records( FACSLIB, "HLP05", " WHERE LCASE(EMAIL05)='$emailSent'", "" ); 
		
		if( $recordCount == 0 ){
			?>
			<center>
			<br/><br/><b>Sorry, that email address does not exist in LPS<br/>
			Please contact your system administrator</b><br/><br/>
			
			<a href='index.php'>Back</a>
			
			</center>
			<?php 
		}else{
			
			send_account_info_mail( $_REQUEST['email'] ); 
			
			echo "<center>";
			echo "<br><br><b>Your LPS Account Information has been emailed to you</b>";
		?>
			<meta http-equiv="refresh" content="3;url=index.php"/>
		<?php
		}
    }
		
}
	?>

</center>
</body>
</html>

