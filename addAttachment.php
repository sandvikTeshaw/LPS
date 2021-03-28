<?php
/**
 *System Name:			   Logistics Process Support
 *Program Name:            addAttachment.php<br>
 *Development Reference:   DI868<br>
 *Description:             Adds attachment to ticket and uploads attachment to file system
 * 
 *MODIFICATION CONTROL<br>
 *====================<br>
 *FIXNO     BY      DD/MM/YYYY  COMMENT<br>                           
 *--------  ------  ----------  ----------------------------------<br>
 * 
 */
/**
 */



include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

global $conn, $action, $userID, $TEST_SITE, $attachmentId, $SMKT07, $comment, $CONO;

if (!isset($conn)) {
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if (isset($conn)) {
} else {
	echo "Connection Failed";
}
if (isset($_SESSION ['email'])) {
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}
} elseif( isset($_COOKIE ["mtp"]) ) {
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} else {
	
	error_mssg ( "NONE" );
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Logistics Process Support</title>

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
<script type="text/javascript">
function refresh_attachments( targ ){
	opener.location.href=opener.location.href + "&LINK21=Z";
	window.close();
}
</script>
</head>
<body>

<?

include_once 'copysource/header.php';

if ($action == "") {
	echo "<form method='POST' enctype='multipart/form-data' action='addAttachment.php'>";
	echo "<center>";
	echo "<table width=50% cellpadding='0' cellspacing='0'>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td class='titleBig' colspan='2'>Attach File</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<TR class='big'>";
	echo "<td class='bold'>Attachment:</td>";
	echo "<td class='uploadButton'><input class='big' name='uploadFile' type='file'></td>";
	echo "</tr>";
	echo "<TR class='big'>";
	echo "<td class='bold'>Comment:</td>";
	echo "<td><input type='text' name='comment' value='' class='longer'></td>";
	echo "</tr>";
	echo "<input type='hidden' name='action' value='addAttachment'>";
	echo "<input type='hidden' name='userID' value='$userID'/>";
	echo "<input type='hidden' name='attachmentId' value='$attachmentId'/>";
	echo "<tr>";
	echo "<td>";
	echo "<input type='submit' value='Save Attachment'>";
	echo "</td>";
	echo "</tr>";
	echo "<TR><TD>&nbsp;</TD></TR>";
	echo "<TR><TD>&nbsp;</TD></TR>";
	echo "<TR>";
		echo "<td colspan='2'>Please ensure that attachments are no larger than 2MB in size</td>";
	echo "</tr>";
	echo "<TR>";
		echo "<td colspan='2'><a href='copysource/resize.pdf'>Instructions for resizing images</a></td>";
	echo "</tr>";
	
	echo "</table>";
	echo "</form>";
	echo "</center>";
} else {
	
	$uploadFile = $userID . "_" . date ( 'Ymd' ) . "_" . date ( 'him' ) . "_" . $_FILES ['uploadFile'] ['name'];
	$uploadFile = str_replace ( " ", "_", $uploadFile );
	
	if ($TEST_SITE != "Y") {
		
		if( move_uploaded_file ( $_FILES ['uploadFile'] ['tmp_name'], "../../attachments/tickets/$uploadFile" )){
		
			$nextId = get_next_unique_id ( FACSLIB, "DSH07L02", "ID07", "" );
			
			$insertSql = "INSERT INTO DSH07 VALUES(";
			$insertSql .= " $nextId, 0, '" . addslashes ( $uploadFile ) . "', '" . addslashes ( $_FILES ['uploadFile'] ['name'] ) . "', ";
			$insertSql .= date ( 'Ymd' ) . ", " . date ( 'His' ) . ", 1, 'N', '$SMKT07', '$CONO', $userID, '" . addslashes ( $comment ) . "', ";
			$insertSql .= "'$attachmentId', '', '', 'Y', 'CIL', 'CIA', 'UPLOADED')";
			
			
			//Execute insert sql   
			if( $insertAttachRes = odbc_prepare ( $conn, $insertSql )){
	
			}else{
				echo "Prep Fail<hr>";
				echo $insertSql . "<hr>";
				die();
			}
			odbc_execute ( $insertAttachRes );
			
		}else{
			?>
				<center>
			<table width=50% cellpadding='0' cellspacing='0'>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class='titleBig' colspan='2'><font color='red'>*****Upload Failed*****</font></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class='title'><font color='red'>Please Contact Your Local Service Desk</font></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class='title'><a href="javascript:refresh_attachments();">Close</a>
					</td>
				</tr>
			</table>
			</center>
			<?

			die();
		}
	
		
	} else {

		$nextId = get_next_unique_id ( FACSLIB, "DSH07L00", "ID07", "" );
		
		$insertSql = "INSERT INTO DSH07 VALUES(";
		$insertSql .= " $nextId, 0, '" . addslashes ( $uploadFile ) . "', '" . addslashes ( $_FILES ['uploadFile'] ['name'] ) . "', ";
		$insertSql .= date ( 'Ymd' ) . ", " . date ( 'His' ) . ", 1, 'N', '$SMKT07', '$CONO', $userID, '" . addslashes ( $comment ) . "', ";
		$insertSql .= "'$attachmentId', '', '', 'Y', 'CIL', 'CIA', 'UPLOADED')";
		
		echo $insertSql;
		die();
	}
	?>
	<center>
<table width=50% cellpadding='0' cellspacing='0'>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class='titleBig' colspan='2'>Attach File</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class='title'>Attachment has been uploaded</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class='title'><a href="javascript:refresh_attachments();">Close</a>
		</td>
	</tr>
</table>
</center>
<?
}
?>
</body>
</html>
