<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			attachments.php<br>
 * Development Reference:	DI868<br>
 * Description:				attachments.php displays the attachments related to a specific ticket, this page is set up and called from an iFrame.<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 * 
 */
/**
 */

include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

if (!isset($conn)) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options);
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
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
<title><?echo $SITE_TITLE;?></title>

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

<?

if (!isset($action) || $action != "delete") {
	?>
<table width=100% cellpadding='0' cellspacing='0'>
	<tr>
		<TD><img src="<?
	echo $IMG_DIR;
	?>/new_attach.gif" alt="Attach File"><a
			href='addAttachment.php?attachmentId=<?
	echo $attachmentId;
	?>&userID=<?
	echo $userID;
	?>'
			target='new'>Attach File</a>
	
	</tr>
</table>

<?
	show_attachments ( $attachmentId );
	?>
<?
} else {
	
	$deleteSql = "delete from DSH07 WHERE ID07=$deleteId";
	$delRes = odbc_prepare ( $conn, $deleteSql );
	odbc_execute ( $delRes );
	
	echo "<center>";
	echo "<table width=50% cellpadding='0' cellspacing='0'>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD class='titleBig' colspan='2'>Delete Attachment</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD class='title'>Attachment has been deleted</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD class='title'>";
	?>
	<a href="javascript:refresh_attachments();">Close</a>
	<?
	echo "</TD></TR>";
	echo "</center>";
}
