<?
/**
 * System Name:			    Logistics Process Support<br>
 * Program Name: 			editAM.php<br>
 * Development Reference:	DI868<br>
 * Description:				editAM.php allows system administrators to maintain account managers.<br>
 *
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY			    COMMENT<br>
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0539	  TS	  11/10/2011  Remove back-ups and director
 */
/**
 */
global $conn, $action;

include_once '../copysource/config.php';
include '../copysource/functions.php';
include '../../common/copysource/global_functions.php';
include '../copysource/notificationFunctions.php';

if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>LPS Contact Maintenance</title>

<style type="text/css">
<!--
@import url(../copysource/styles.css);
-->
</style>
<script language="JavaScript" type="text/javascript">
<!--
function closeW()
{
   window.opener='X';
   window.open('','_parent','');
   window.close();

}
// -->

</script>
</head>

<body onunload="opener.location=('../maintenanceNotification.php?maintenanceType=PAC')">
<?
include_once '../copysource/header.php';

if ($action == "") {

	$sql = "SELECT ID30, MRKT30, RESP30, PRMD15 FROM CIL30 T1"
	     . " INNER JOIN INP15 T2"
         . " ON T1.PGMJ30 = T2.PSAR15 AND PRMT15='PGMJ'"
         . " WHERE PGMJ30='" . $_REQUEST['itemClass'] . "'";

	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );
	?>
	<center>
<form method='post' action='paContactMaintenance.php'>
<table width=80% cellpadding=0 cellspacing=0>

    <tr><td>&nbsp;</td></tr>
	<tr><td colspan='3' class='titleBig'>P & A Pricing Contacts Maintenance</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	 <tr>
        <td colspan='2'>
            <a href='paContactMaintenance.php?action=add&itemClass=<?php echo $_REQUEST['itemClass'];?>'>Add Pricing Contact</a>
        </td>
        <td colspan='2' align='right'>
            <a href='javascript:void(0)' onclick='closeW()'>Done</a>
        </td>
    </tr>

	<tr class='header'>
        <td class='header'>Item Class</td>
        <td class='header'>Market Area</td>
        <td class='header' colspan='2'>Contact</td>
    </tr>
	<?php
	while ( ($row = odbc_fetch_array ( $res )) != false ) {

    $userInfo = user_info_by_id( $row ['RESP30'] );

    if( trim ($row ['MRKT30']) == 0 ){
		$contactDispay = "Defautl Contact";
	}else{
		$contactDispay = trim ( $row ['MRKT30'] );
	}
	?>
		<tr>
    		<td><?php echo trim ( $row ['PRMD15'] );?></td>
    		<td><?php echo $contactDispay;?></td>
    		<td><?php  echo trim($userInfo['NAME05']);?></td>
    		<td>
    		      <a href="paContactMaintenance.php?action=edit&id=<?php echo $row [ 'ID30' ];?>&itemClass=<?php echo $_REQUEST['itemClass'];?>" target='_new'><img src='<?php echo $IMG_DIR;?>/edit.gif' border=0 title='Edit'/></a>
                  <a href="paContactMaintenance.php?action=delete&id=<?php echo $row [ 'ID30' ];?>&itemClass=<?php echo $_REQUEST['itemClass'];?>" target='_new'><img src='<?php echo $IMG_DIR;?>/delete.gif' border=0 title='Delete'/></a>
            </td>
		</tr>
    <?php
	}
	?>
	<tr><td>&nbsp;</td></tr>
	</table>
</form>
</center>
	<?
}elseif( $action == "add" ){

$editClassSql = "SELECT * FROM CIL37 WHERE ID37 = " . $_REQUEST['classId'];

$editClassRes = odbc_prepare ( $conn, $editClassSql );
odbc_execute ( $editClassRes );

while( $editClassRow = odbc_fetch_array( $editClassRes) ){

	$desc = trim($editClassRow['DESC37']);

}

$userArray = get_user_list ();

?>
<center>
<form method='post' action='paContactMaintenance.php'>
<table width=40% cellpadding=0 cellspacing=0 border='0'>
    <tr><td>&nbsp;</td></tr>
	<tr><td colspan='2' class='titleBig'>Add <?php echo $desc;?> Major Class</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
    <tr>
        <td class='boldMedRight50'>Major Class:&nbsp;</td>
        <td>
            <select name='majorClass'>
                <?php list_major_classes(0, "no"); ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class='boldMedRight50'>Primary Contact:&nbsp;</td>
        <td>
            <select name='contact'>
            	<option value='0'>Default Contact</option>
                <?php show_user_list ( $userArray, 0 );?>
            </select>
        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td class='right'>
            <input type='submit' value='Save'/>
            <input type='hidden' name='action' value='save'/>
            <input type='hidden' name=classId value='<?php echo $_REQUEST['classId'];?>'/>
        </td>
        </form>
        <form method='post' action='<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC'>
        	<td class='left'>
	        	<input type='submit' value='Cancel'/>
        </td>
        </form>
    </tr>
</table>
</form>
</center>
<?php
}elseif ($action == "addClass") {

	$userArray = get_user_list ();

	?>
	<center>
	<form method='post' action='paContactMaintenance.php'>
	<table width=40% cellpadding=0 cellspacing=0 border='0'>
	    <tr><td>&nbsp;</td></tr>
		<tr><td colspan='2' class='titleBig'>Add Item Class</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
	    <tr>
	        <td class='boldMedRight50'>Item Class:</td>
	        <td align='left'><input name='itemClass' value='' maxlength='100'/></td>
	    </tr>
	    <tr>
	        <td class='boldMedRight50'>Default Contact:</td>
	        <td align='left'>
	        	<select name='responsible'>
                	<?php show_user_list ( $userArray, 0 ) ;?>
                </select>
            </td>
	    </tr>


	    <tr><td>&nbsp;</td></tr>
	    <tr>
	        <td class='right'>
	            <input type='submit' value='Save'/>
	            <input type='hidden' name='action' value='saveClass'/>
	        </td>
	        </form>
	        <form method='post' action='<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC'>
	        <td class='left'>
	            <input type='submit' value='Cancel'/>
	        </td>
	        </form>

	    </tr>
	</table>

	</center>
	<?php

}elseif ($action == "editClass") {
	$userArray = get_user_list ();


	$editClassSql = "SELECT * FROM CIL37 WHERE ID37 = " . $_REQUEST['classId'];

	$editClassRes = odbc_prepare ( $conn, $editClassSql );
	odbc_execute ( $editClassRes );

	while( $editClassRow = odbc_fetch_array( $editClassRes) ){

		$resId = $editClassRow['RESP37'];
		$desc = trim($editClassRow['DESC37']);

	}

	?>

	<center>
	<form method='post' action='paContactMaintenance.php'>
	<table width=40% cellpadding=0 cellspacing=0 border='0'>
	    <tr><td>&nbsp;</td></tr>
		<tr><td colspan='2' class='titleBig'>Edit Item Class</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
	    <tr>
	        <td class='boldMedRight50'>Item Class:</td>
	        <td align='left'><input name='itemClass' value='<?php echo $desc;?>' maxlength='100'/></td>
	    </tr>
	    <tr>
	        <td class='boldMedRight50'>Default Contact:</td>
	        <td align='left'>
	        	<select name='responsible'>
                	<?php show_user_list ( $userArray, $resId ) ;?>
                </select>
            </td>
	    </tr>


	    <tr><td>&nbsp;</td></tr>
	    <tr>
	        <td class='right'>
	            <input type='submit' value='Save'/>
	            <input type='hidden' name='classId' value='<?php echo $_REQUEST['classId'];?>'/>
	            <input type='hidden' name='action' value='saveEditClass'/>
	        </td>
	        </form>
	         <form method='post' action='<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC'>
	        <td class='left'>
	            <input type='submit' value='Cancel'/>
	        </td>
	        </form>
	    </tr>
	</table>
	</form>
	</center>
	<?php
}elseif ($action == "deleteClass") {

		?>
		<center>
	<form method='post' action='paContactMaintenance.php'>
	<table width=40% cellpadding=0 cellspacing=0 border='0'>

		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan='2' class='titleBig'>Delete Item Class</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
	    <?php
		$sql = "SELECT DESC37 FROM CIL37 WHERE ID37 = " . $_REQUEST['classId'];

		$res = odbc_prepare ( $conn, $sql );
		odbc_execute ( $res );
		while ( ($row = odbc_fetch_array ( $res )) != false ) {
	        ?>
			<tr><td class='boldMedRight50'>Delete: </b><?php echo $row ['DESC37'];?></td>
			<td class='left'>
			<select name='delSelection'>
			<option SELECTED value='N'>No</option>
			<option value='Y'>Yes</option>
			</select>
			</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td colspan='2' class='center'><input type='submit' value='Continue'/>
	    		<input type='hidden' name='classId' value='<?php echo $_REQUEST['classId'];?>'/>
	    		<input type='hidden' name='action' value='deleteClassContinue'/>
			</td></tr>
			<?php
		}
		?>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td class='center' colspan='2'>Note: Deletion of an Item Class will also delete all associated contacts.</td>
		</tr>
		</table>
		</form>
	<?

}elseif ($action == "delete") {

	?>
	<center>
<form method='post' action='paContactMaintenance.php'>
<table width=20% cellpadding=0 cellspacing=0>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan='2' class='titleBig'>Delete Major Class</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
    <?php
	$sql = "SELECT PGMJ30, PRMD15 FROM CIL30 T1"
	     . " INNER JOIN INP15 T2"
         . " ON T1.PGMJ30 = T2.PSAR15 AND PRMT15='PGMJ'"
         . " WHERE ID30='" . $_REQUEST['id'] . "'";


	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );
	while ( ($row = odbc_fetch_array ( $res )) != false ) {
        ?>
		<tr><td colspan='2'><b>Delete: </b><?php echo $row ['PGMJ30'] . " - " . $row ['PRMD15'];?></td></tr>
		<tr><td>
		<select name='delSelection'>
		<option SELECTED value='N'>No</option>
		<option value='Y'>Yes</option>
		</select>
		</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>
				<input type='submit' value='Continue'/>
	    		<input type='hidden' name='id' value='<?php echo $_REQUEST['id'];?>'/>
	    		<input type='hidden' name='action' value='deleteContinue'/>
	    		<input type='hidden' name='itemClass' value='<?php echo $_REQUEST['itemClass'];?>'/>
			</td>
			</form>
			<form method='post' action='<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC'>
			<td>
				<input type='submit' value='Cancel'/>

			</td>
			</form>
		</tr>
		<?php
	}
	?>
	</table>
	</form>
<?
} elseif ($action == "deleteClassContinue") {

	if ($_REQUEST['delSelection'] == "Y") {
		$delSql = "DELETE FROM CIL37 WHERE ID37=" . $_REQUEST['classId'];
		$delRes = odbc_prepare ( $conn, $delSql );
		odbc_execute ( $delRes );

		$delChildSql = "DELETE FROM CIL30 WHERE MSTR30=" . $_REQUEST['classId'];
		$delChildRes = odbc_prepare ( $conn, $delChildSql );
		odbc_execute ( $delChildRes );

		?>
		<center><br></br>
        <br></br>
        <b>Class has been deleted</b><br></br>
        <br></br>
        <meta http-equiv="refresh" content="2;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC" />
		<?php
	} else {
		?>
		<center><br></br>
        <br></br>
        <b>Class has <i>not</i> been deleted</b><br></br>
        <br></br>
        <meta http-equiv="refresh" content="2;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC" />
		<?php
	}


}elseif ($action == "deleteContinue") {

	if ($_REQUEST['delSelection'] == "Y") {
		$delSql = "DELETE FROM CIL30 WHERE ID30=" . $_REQUEST['id'];
		$delRes = odbc_prepare ( $conn, $delSql );
		odbc_execute ( $delRes );

		?>
		<center><br></br>
        <br></br>
        <b>Major Class has been deleted</b><br></br>
        <br></br>
        <meta http-equiv="refresh" content="2;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC" />
		<?php
	} else {
		?>
		<center><br></br>
        <br></br>
        <b>Major CLass has <i>not</i> been deleted</b><br></br>
        <br></br>
        <meta http-equiv="refresh" content="2;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC" />
		<?php
	}


} elseif( $action == "saveEditClass" ) {

	$updateClassSql = "UPDATE CIL37 SET DESC37 = '" . $_REQUEST['itemClass'] . "', RESP37=" . $_REQUEST['responsible'] . " WHERE ID37=" . $_REQUEST['classId'];
	$updateClassRes = odbc_prepare ( $conn, $updateClassSql );
	odbc_execute ( $updateClassRes );


	?><meta http-equiv="refresh" content="0;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC" /><?php


} elseif( $action == "saveClass" ) {

	$nextCIL37Record = get_next_unique_id ( FACSLIB, "CIL37", "ID37" );

	$insertClassSql = "INSERT INTO CIL37 VALUES( $nextCIL37Record, '" . $_REQUEST['itemClass'] . "', ". $_REQUEST['responsible'] . ")";


	$insertClassRes = odbc_prepare ( $conn, $insertClassSql );
	odbc_execute ( $insertClassRes );


	?><meta http-equiv="refresh" content="0;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC" /><?php

} elseif( $action == "save" ) {


	$checkDuplicates = 0;

	$dupSql = "SELECT DESC37 FROM CIL30 T1 "
 		    . " INNER JOIN CIL37 T2"
 		    . " ON T1.MSTR30 = T2.ID37"
 		    . " WHERE PGMJ30 = '" . $_REQUEST['majorClass'] . "'";

	$dupRes = odbc_prepare ( $conn, $dupSql );
	odbc_execute ( $dupRes );

	while( $dupRow = odbc_fetch_array( $dupRes ) ){

		$checkDuplicates++;
		$duplicateClass = $dupRow['DESC37'];
	}

	if( $checkDuplicates > 0 ){

		?>
		<center><br></br>
        <br></br>
        <b>Major Class already assigned to item class <?php echo $duplicateClass;?></b><br></br>
        <br></br>
        <a href='<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC'>Return to Item Class List</a>

		<?php
		die();
	}

    $nextCIL30Record = get_next_unique_id ( FACSLIB, "CIL30", "ID30" );

    $insertSql = "INSERT INTO CIL30 VALUES( $nextCIL30Record, 8, '', " . $_REQUEST['contact'] . ", 43, '', '" . $_REQUEST['majorClass'] . "'," . $_REQUEST['classId'] . ")";

    $insertRes = odbc_prepare ( $conn, $insertSql );
    odbc_execute ( $insertRes );

    ?><meta http-equiv="refresh" content="0;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC"/><?php


} elseif( $action == "saveEdit" )  {

    $updateSql = "UPDATE CIL30 SET RESP30=" . $_REQUEST['contact'] . " WHERE ID30=" . $_REQUEST['id'];
    $resUpdate = odbc_prepare ( $conn, $updateSql );
    odbc_execute ( $resUpdate );

	?>
	<center><br></br>
    <br></br>
    <b>Contact has been updated</b><br></br>
    <br></br>
   <meta http-equiv="refresh" content="0;url=<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC" />
    </center>
    </center>
    </body>
    </html>
    <?php

}elseif( $action == "edit" )  {

    $sql = "SELECT ID30, MSTR30, RESP30, PGMJ30 FROM CIL30"
         . " WHERE ID30=" . $_REQUEST['id'] . " AND PGMJ30 <> ''";

    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );

    $userArray = get_user_list ();
    ?>

     <center>
    <form method='post' action='paContactMaintenance.php'>
    <table width=40% cellpadding=0 cellspacing=0 border='0'>

    <?php
    while ( ($row = odbc_fetch_array ( $res )) != false ) {

    ?>
		<tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
    	<tr><td colspan='2' class='titleBig'>Edit Major Class</td></tr>
    	<tr><td>&nbsp;</td></tr>
    	<tr><td>&nbsp;</td></tr>
        <tr>
	        <td class='boldMedRight50'>Major Class:&nbsp;</td>
	        <td>
	            <select name='majorClass' disabled>
	                <?php list_major_classes( $row['PGMJ30'], 'yes'); ?>
	            </select>
	        </td>
	    </tr>
        <tr>
            <td class='boldMedRight50'>Primary Contact:&nbsp;</td>
            <td>
                <select name='contact'>
                    <?php show_user_list ( $userArray, trim($row['RESP30'])  );?>
                </select>
            </td>
        </tr>
    <?php
    }
    ?>
    <tr><td>&nbsp;</td></tr>
     <tr>
            <td class='right'>
                <input type='submit' value='Save'/>
                <input type='hidden' name='action' value='saveEdit'/>
                <input type='hidden' name='id' value='<?php echo $_REQUEST['id'];?>'/>
            </td>
            <form method='post' action='<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC'>
            <td class='left'>
                <input type='submit' value='Cancel'/>
            </td>
            </form>
        </tr>
    </table>
    </form>
    </center>

    <?php

    }elseif ($action == "listContacts") {


		?>
     	<center>
	    <form method='post' action='paContactMaintenance.php'>
	    <table width=40% cellpadding=0 cellspacing=0 border='0' class='hoverHighlightwht'>

	    <?php

		$sql = "SELECT * FROM CIL37 T1"
 			 . " LEFT JOIN CIL30 T2 "
 			 . " ON T1.ID37 = T2.MSTR30 AND PGMJ30 = ''"
 			 . " WHERE ID37 =" . $_REQUEST['classId'];

		$res = odbc_prepare ( $conn, $sql );
		odbc_execute ( $res );

		$resCounter = 0;
		while( $resRow = odbc_fetch_array( $res ) ){

		$resCounter++;
		$reponsibleInfo = "";
		$reponsibleInfo = user_info_by_id( $resRow['RESP30'] );

			if( $resCounter == 1){
			?>
		<tr><td class='noFormat'>&nbsp;</td></tr>
		<tr><td class='noFormat'>&nbsp;</td></tr>
		<tr><td colspan='2' class='titleBig'><?php echo $resRow['DESC37']?> Contacts</td></tr>
		<tr><td class='noFormat'>&nbsp;</td></tr>
		<tr><td class='noFormat'>&nbsp;</td></tr>
		<tr><td class='noFormat'>&nbsp;</td></tr>
		<tr>
	        <td class='left'>
            <a href='paContactMaintenance.php?action=addContact&classId=<?php echo $_REQUEST['classId'];?>'>Add Contact</a>
		    </td>
		    <td class='right'>
            <a href='<?php echo $mtpUrl;?>/maintenanceNotification.php?maintenanceType=PAC'>Back to List</a>
		    </td>
       </tr>
        		<tr><td class='noFormat'>&nbsp;</td></tr>
			<?php
		}
		if( $resRow['RESP30'] != "" && $resRow['RESP30'] != 0 ){
			?>
			<tr>
				<td><b><?php echo $reponsibleInfo['NAME05']?></b></td>
				<td class='right'><a href="paContactMaintenance.php?action=deleteContact&resId=<?php echo $resRow['RESP30'] ;?>&contId=<?php echo $resRow['ID30'];?>&classId=<?php echo $_REQUEST['classId'];?>"><img src='<?php echo $IMG_DIR;?>/delete.gif' border=0 title='Delete'/></a></td>
			</tr>
			<?php
		}


	}


}elseif ($action == "addContact") {

	$sql = "SELECT DESC37 FROM CIL37 WHERE ID37 =" . $_REQUEST['classId'];
	$res = odbc_prepare ( $conn, $sql );
	odbc_execute ( $res );

	while( $row = odbc_fetch_array( $res ) ){
		$ClassName = $row['DESC37'];

	}

     	$userArray = get_user_list ();

		?>
	<center>
	<form method='post' action='paContactMaintenance.php'>
	<table width=40% cellpadding=0 cellspacing=0 border='0'>
	    <tr><td>&nbsp;</td></tr>
	    <tr><td>&nbsp;</td></tr>
	    <tr><td>&nbsp;</td></tr>
		<tr><td colspan='2' class='titleBig'>Add <?php echo $ClassName;?>Contact</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
	    <tr>
	        <td class='boldMedRight50'>Contact:</td>
	        <td align='left'>
	        	<select name='contact'>
                	<?php show_user_list ( $userArray, 0 ) ;?>
                </select>
            </td>
	    </tr>
	    <tr><td>&nbsp;</td></tr>
	    <tr>
	        <td class='right'>
	            <input type='submit' value='Save'/>
	            <input type='hidden' name='action' value='saveContact'/>
	            <input type='hidden' name='classId' value='<?php echo $_REQUEST['classId'];?>'/>
	            <input type='hidden' name='className' value='<?php echo $ClassName;?>'/>
	       </td>
	       </form>
	       <form method='post' action='<?php echo $mtpUrl;?>/maintenance/paContactMaintenance.php?action=listContacts'>
	       <td class='left'>
	       		<input type='submit' value='Cancel'/>
	            <input type='hidden' name='classId' value='<?php echo $_REQUEST['classId'];?>'/>
	       </form>
	    </tr>
	</table>
	</form>
	</center>

<?php

}elseif( $action == "saveContact" ) {


	$checkDuplicates = 0;

	$dupSql = "SELECT RESP30 FROM CIL30 WHERE PGMJ30 = '' AND RESP30=" . $_REQUEST['contact'] . " AND MSTR30 = " . $_REQUEST['classId'];


	$dupRes = odbc_prepare ( $conn, $dupSql );
	odbc_execute ( $dupRes );

	while( $dupRow = odbc_fetch_array( $dupRes ) ){

		$checkDuplicates++;
	}

	if( $checkDuplicates > 0 ){

		?>
		<center><br></br>
        <br></br>
        <b>Resource already assigned to <?php echo $_REQUEST['className'];?></b><br></br>
        <br></br>
        <a href=<?php echo $mtpUrl;?>/maintenance/paContactMaintenance.php?action=listContacts&classId=<?php echo $_REQUEST['classId'];?>>Return to Item Class List</a>

		<?php
		die();
	}

	$nextCIL30Record = get_next_unique_id ( FACSLIB, "CIL30", "ID30" );

	$insertSql = "INSERT INTO CIL30 VALUES( $nextCIL30Record, 8, '', " . $_REQUEST['contact'] . ", 43, '', ''," . $_REQUEST['classId'] . ")";

	$insertRes = odbc_prepare ( $conn, $insertSql );
	odbc_execute ( $insertRes );

	?><meta http-equiv="refresh" content="0;url=<?php echo $mtpUrl;?>/maintenance/paContactMaintenance.php?action=listContacts&classId=<?php echo $_REQUEST['classId'];?>"/><?php

}elseif ($action == "deleteContact") {

		$reponsibleInfo = user_info_by_id( $_REQUEST['resId'] );

		?>
		<center>
	<form method='post' action='paContactMaintenance.php'>
	<table width=20% cellpadding=0 cellspacing=0>

		<tr><td colspan='2' class='titleBig'>Delete Contact</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
	    <?php
		$sql = "SELECT DESC37 FROM CIL37 WHERE ID37 = " . $_REQUEST['classId'];

		$res = odbc_prepare ( $conn, $sql );
		odbc_execute ( $res );
		while ( ($row = odbc_fetch_array ( $res )) != false ) {
	        ?>
			<tr><td><b>Delete: </b><?php echo $row ['DESC37'];?> - <?php echo $reponsibleInfo ['NAME05'];?></td></tr>
			<tr><td>
			<select name='delSelection'>
			<option SELECTED value='N'>No</option>
			<option value='Y'>Yes</option>
			</select>
			</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td><input type='submit' value='Continue'/>
	    		<input type='hidden' name='contId' value='<?php echo $_REQUEST['contId'];?>'/>
	    		<input type='hidden' name='classId' value='<?php echo $_REQUEST['classId'];?>'/>
	    		<input type='hidden' name='action' value='deleteContactContinue'/>
			</td></tr>
			<?php
		}
		?>
		</table>
		</form>
	<?

}elseif ($action == "deleteContactContinue") {

if ($_REQUEST['delSelection'] == "Y") {
	$delSql = "DELETE FROM CIL30 WHERE ID30=" . $_REQUEST['contId'];
	$delRes = odbc_prepare ( $conn, $delSql );
	odbc_execute ( $delRes );

	?>
		<center><br></br>
        <br></br>
        <b>Contact has been deleted</b><br></br>
        <br></br>
        <meta http-equiv="refresh" content="2;url=<?php echo $mtpUrl;?>/maintenance/paContactMaintenance.php?action=listContacts&classId=<?php echo $_REQUEST['classId'];?>" />
		<?php
	} else {
		?>
		<center><br></br>
        <br></br>
        <b>Contact has <i>not</i> been deleted</b><br></br>
        <br></br>
        <meta http-equiv="refresh" content="2;url=<?php echo $mtpUrl;?>/maintenance/paContactMaintenance.php?action=listContacts&classId=<?php echo $_REQUEST['classId'];?>" />
		<?php
	}

}
