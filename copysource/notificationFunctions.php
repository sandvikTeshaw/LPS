<?php
/**
 * System Name:             Logistics Process Support
* Program Name:            notificationsFunctions.php<br>
* Development Reference:   D0539<br>
* Description:             This is the LPS notifications functions file
*
*  MODIFICATION CONTROL<br>
*  ====================<br>
*    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
*  --------  ------  ----------  ----------------------------------<br>
*  D0539      TS     24/03/2014   P&A Pricing Contacts
*  GLBAU8595  IS     04/10/2015   added list_planner_table function
*
*/
/**
 */

//D0539 - Price And Availability Pricing Contacts
function list_papricing_contacts(){
    global $CONO, $conn, $IMG_DIR;
    ?>
      <table border=0 cellspacing=0 cellpadding=2 width=80% class='hoverHighlightwht'>
        <tr><td class='noFormat'>&nbsp;</td></tr>
        <tr><td class='noFormat'>&nbsp;</td></tr>
		<tr>
        <td colspan='2' class='noFormat'><a href='maintenance/paContactMaintenance.php?action=addClass'>Add Item Class</a></td></tr>
		<tr><td class='noFormat'>&nbsp;</td></tr>
    <?
    $sql = "SELECT * FROM CIL37";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
		$classReponsibleInfo = user_info_by_id( $row['RESP37'] );
    	?>
    	<tr class='header'>
    	<td class='headerColored'><?php echo $row['DESC37'];?></td>
    	<td class='headerRightColored'colspan='2'>
    		<?php echo $classReponsibleInfo['NAME05'];?>
    		<a href='maintenance/paContactMaintenance.php?action=listContacts&classId=<?php echo $row ['ID37'];?>'><img src='<?php echo $IMG_DIR;?>/resource_new.gif' border=0 title='Maintain Contacts'></a>
    		<a href='maintenance/paContactMaintenance.php?action=add&classId=<?php echo $row ['ID37'];?>'><img src='<?php echo $IMG_DIR;?>/post.gif' border=0 title='Add Major Class'></a>
    		<a href='maintenance/paContactMaintenance.php?action=editClass&classId=<?php echo $row ['ID37'];?>'><img src='<?php echo $IMG_DIR;?>/edit.gif' border=0 title='Edit'></a>
            <a href='maintenance/paContactMaintenance.php?action=deleteClass&classId=<?php echo $row ['ID37'];?>'><img src='<?php echo $IMG_DIR;?>/delete.gif' border=0 title='Delete'></a>

    	</tr>
    	<?php


    	$itemClassSql = "SELECT * FROM CIL30 WHERE MSTR30 = " . $row['ID37'] . " AND PGMJ30 <> '' ORDER BY PGMJ30";

    	$itemClassRes = odbc_prepare ( $conn, $itemClassSql );
	    odbc_execute ( $itemClassRes );

	    $rowLineCounter = 0;
	    while ( $itemClassRow = odbc_fetch_array ( $itemClassRes ) ) {
			$rowLineCounter++;

			if( $rowLineCounter == 1 ){
				?>
				<tr>
					<td class='alternateBack' width='50%'>SAB /PGMJ</td>
					<td colspan='2' class='alternateBack'>Default Contact</td>
				</tr>
				<?php

			}
			$reponsibleInfo = user_info_by_id( $itemClassRow['RESP30'] );

			?>
			<tr>
				<td ><?php echo $itemClassRow['PGMJ30']?></td>
				<td><?php echo $reponsibleInfo['NAME05'];?></td>
				<td class='right'>
    				<a href='maintenance/paContactMaintenance.php?action=edit&id=<?php echo $itemClassRow ['ID30'];?>'><img src='<?php echo $IMG_DIR;?>/edit.gif' border=0 title='Edit'></a>
            		<a href='maintenance/paContactMaintenance.php?action=delete&id=<?php echo $itemClassRow ['ID30'];?>'><img src='<?php echo $IMG_DIR;?>/delete.gif' border=0 title='Delete'></a>
            	</td>

			</tr>
			<?php

	    }
	    ?><tr><td>&nbsp;</td></tr><?php

	}

	    ?></table><?php


}

function list_regional_contacts(){
	global $CONO, $conn, $IMG_DIR;
	?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%>
                                        <tr>
                                            <td>&nbsp</td>
                                        </tr>
                                        <tr>
                                            <td colspan='2'><a
                                                href='maintenance/regionalMaintenance.php?action=add'
                                                target='_new'>Add Regional Contact</a></Td>
                                        </tr>
                                        <tr class='header'>
                                            <td class='header'>Country</td>
                                            <td class='header' colspan='2'>Responsible</td>
                                        </tr>
<?
	//D0481 - Added CLAS32 = 9 to query
    $sql = "SELECT ID32, NAME32, RESP32, NAME05  FROM CIL32L01 T1 "
    	 . " INNER JOIN HLP05 T2"
     	 . " ON T1.RESP32 = T2.ID05"
     	 . " WHERE NAME32 <> '' AND ACTF32 <> 'N' AND CLAS32=11"
    	 . " ORDER BY NAME32";


    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCounter ++;

        if ($rowCounter % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "</tr>";
        }
        echo "<td class=boldTop>" . $row ['NAME32'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href=maintenance/regionalMaintenance.php?action=edit&regionId=" . $row ['ID32'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";
            echo "<a href=maintenance/regionalMaintenance.php?action=delete&regionId=" . $row ['ID32'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";
        } else {
            echo "&nbsp";
        }

        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";

}

//D0185 - Added for inbound default resources
function list_inbound_default_contacts(){
	global $CONO, $conn, $IMG_DIR;
	?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%>
        <tr>
            <td>&nbsp</td>
        </tr>
        <tr>
            <td colspan='2'><a
                href='maintenance/inboundMaintenance.php?action=add'
                target='_new'>Add Default Stockroom Responsible</a></Td>
        </tr>
        <tr class='header'>
            <td class='header'>Stockroom</td>
            <td class='header' colspan='2'>Responsible</td>
        </tr>
<?
    $sql = "SELECT ID29, NAME07, NAME05  FROM CIL29 T1 "
    	 . " INNER JOIN HLP05 T2"
     	 . " ON T1.RESP29 = T2.ID05"
     	 . " INNER JOIN CIL07 T3"
     	 . " ON T1.BACK29 = T3.ATTR07"
    	 . " ORDER BY NAME07";


    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCounter ++;
        if ($rowCounter % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "</tr>";
        }
        echo "<td class=boldTop>" . $row ['NAME07'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href=maintenance/inboundMaintenance.php?action=edit&strc=" . $row ['ID29'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";
            echo "<a href=maintenance/inboundMaintenance.php?action=delete&strc=" . $row ['ID29'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";
        } else {
            echo "&nbsp";
        }

        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}


/*
 * D0180 - 	Changed entire function to use CIL32 instead of CIL29
* 			Changed all required fields
* 			Changed SQL statements
* 			Changed Href references
*/
function list_global_returns_contacts() {
	global $CONO, $conn, $IMG_DIR;
	?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%>
                                        <tr>
                                            <td>&nbsp</td>
                                        </tr>
                                        <tr>
                                            <td colspan='2'><a
                                                href='maintenance/returnsMaintenance.php?action=add'
                                                target='_new'>Add Global Return Resource</a></Td>
                                        </tr>
                                        <tr class='header'>
                                            <td class='header'>Region</td>
                                            <td class='header' colspan='2'>Responsible</td>
                                        </tr>
<?
	//D0481 - Added CLAS32 = 9 to query
    $sql = "SELECT ID32, NAME32, RESP32, NAME05  FROM CIL32L01 T1 "
    	 . " INNER JOIN HLP05 T2"
     	 . " ON T1.RESP32 = T2.ID05"
     	 . " WHERE NAME32 <> '' AND ACTF32 <> 'N' AND CLAS32=9"
    	 . " ORDER BY NAME32";


    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCounter ++;
        if ($rowCounter % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "</tr>";
        }
        echo "<td class=boldTop>" . $row ['NAME32'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href=maintenance/returnsMaintenance.php?action=edit&regionId=" . $row ['ID32'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";
            echo "<a href=maintenance/returnsMaintenance.php?action=delete&regionId=" . $row ['ID32'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";
        } else {
            echo "&nbsp";
        }

        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}


/**
 * Function displays list of pricing contacts
 *
 */
//D0359 - Removed back-up
function list_pricing_contacts() {
	global $conn, $CONO, $IMG_DIR;

	$sql = "SELECT BRAN16, DESC16, NAME05 FROM CIL16J02 WHERE CONO16 = '$CONO' ORDER BY DESC16";
	?>
    <table border=0 cellspacing=0 cellpadding=2 width=80%>
                            <tr>
                                <td>&nbsp</td>
                            </tr>
                            <tr>
                                <td colspan='3'><a
                                    href='maintenance/pricingContactMaintenance.php?action=addPricingContact'
                                    target='_new'>Add Pricing Contact</a></Td>
                            </tr>
                            <tr class='header'>
                            	<td class='header'>Brand</td>
                                <td class='header'>Description</td>
                                <td class='header' colspan='2'>Responsible</td>
                            </tr>
<?
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCounter = 0;
    while ( $row = db2_fetch_array ( $res ) ) {
        $rowCount ++;

        if ($rowCount % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "<tr class=''>";
        }
        echo "<td class='boldTop'>" . $row ['BRAN16'] . "</td>";
        echo "<td class='boldTop'>" . $row ['DESC16'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</b></td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href='maintenance/pricingContactMaintenance.php?brand=" . $row ['BRAN16'] . "&action=editPricingContact' target='_new'><img src=$IMG_DIR/edit.gif border=0 alt='Edit'></a>";
            echo "<a href='maintenance/pricingContactMaintenance.php?brand=" . $row ['BRAN16'] . "&action=deletePricingContact' target='_new'><img src=$IMG_DIR/delete.gif border=0 alt='Edit'></a>";
        } else {
            echo "&nbsp";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

/**
 * Functino displays list of Operations Managers and calls list of PFCs dependant on the record information of the OPS manager
 *
 */
//D0359 - Removed back-up and director
function list_opmg_table() {
	global $CONO, $conn, $IMG_DIR;

	$sql = "SELECT STRC20, STRN20, NAME05, NAMER5 FROM CIL20J01 WHERE CONO20 = '$CONO' ORDER BY STRC20 ASC";
	?>
<table border=0 cellspacing=0 cellpadding=2 width=80%>
                        <tr>
                            <td>&nbsp</td>
                        </tr>
                        <tr>
                            <td colspan='2'><a
                                href='maintenance/stockroomMaintenance.php?action=addStockroom'
                                target='_new'>Add Stockroom</a></Td>
                        </tr>
                        <tr class='header'>
                            <td class='header'>Stockroom</td>
                            <td class='header'>DRP Order Manager</td>
                            <td class='header' colspan='2'>Operations Manager</td>
                        </tr>
<?
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );

    while ( $row = db2_fetch_array ( $res ) ) {
        echo "<tr class='alternate'>";
        echo "<td class='boldMedTop'>" . $row ['STRC20'] . "-" . $row ['STRN20'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</b></td>";
        echo "<td><b>" . $row ['NAMER5'] . "</b></td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href='maintenance/stockroomMaintenance.php?stockroom=" . $row ['STRC20'] . "&action=addPfc' target='_new'><img src=$IMG_DIR/post.gif border=0 alt='Add PFC'></a>";
            echo "<a href='maintenance/stockroomMaintenance.php?stockroom=" . $row ['STRC20'] . "&action=editStockroom' target='_new'><img src=$IMG_DIR/edit.gif border=0 alt='Edit'></a>";
            echo "<a href='maintenance/stockroomMaintenance.php?stockroom=" . $row ['STRC20'] . "&action=deleteStockroom' target='_new'><img src=$IMG_DIR/delete.gif border=0 alt='Delete Stockroom'></a>";
        } else {
            echo "&nbsp";
        }
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td colspan=4 class='center'>";
        list_pfc_table ( $row ['STRC20'] );
        echo "</td>";
        echo "</tr>";
        echo "<tr><td>&nbsp</td></tr>";
        echo "<tr><td>&nbsp</td></tr>";

    }
}


/**
 * Function displays list of TODs
 *
 */
//D0359 - Removed back-up display
function list_tod_table() {
	global $CONO, $conn, $IMG_DIR;
	?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%>
                <tr>
                    <td>&nbsp</td>
                </tr>
                <tr>
                    <td colspan='2'><a
                        href='maintenance/todMaintenance.php?action=addTod' target='_new'>Add
                    TOD</a></Td>
                </tr>
                <tr class='header'>
                    <td class='header'>Brand</td>
                    <td class='header' colspan='2'>Responsible</td>
                </tr>
<?
    $sql = "SELECT DESC15, NAME05, BRAN15  FROM CIL15J01 ORDER BY DESC15";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCounter = 0;
    while ( $row = db2_fetch_array ( $res ) ) {
        $rowCounter ++;
        if ($rowCounter % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "</tr>";
        }
        echo "<td class=boldTop>" . $row ['DESC15'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href=maintenance/todMaintenance.php?action=editTod&brand=" . $row ['BRAN15'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit TOD'></a>";
            echo "<a href=maintenance/todMaintenance.php?action=deleteTod&brand=" . $row ['BRAN15'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete TOD'></a>";
        } else {
            echo "&nbsp";
        }

        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}


/**
 * Function displays list of account managers
 *
 */
//D0359 - Removed back-up display
function list_am_table() {
	global $CONO, $conn, $IMG_DIR;
	?>
      <table border=0 cellspacing=0 cellpadding=2 width=95%>
            <tr>
                <td colspan='2'><a href='maintenance/addCountry.php' target='_new'>Add
                Country</a></Td>
            </tr>
            <tr class='header'>
                <td class='header' width=4%>Code</td>
                <td class='header'>Country</td>
                <td class='header'>Customer</td>
                <td class='header'>Lotus Notes</td>
                <td class='header'>Outbound Planners</td>
                <td class='header' colspan='2'>Logistics</td>
            </tr>
<?
    $sql = "SELECT COUN13, CUSN13, LOTS13, NAME05, NAMER5, DESC13 FROM CIL13J01 ORDER BY DESC13 ASC";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );

    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCounter ++;
        if ($rowCounter % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "</tr>";
        }
        echo "<td class='top'>" . trim ( $row ['COUN13'] ) . "</a></td>";
        echo "<td class='top'>" . trim ( $row ['DESC13'] ) . "</a></td>";
        echo "<td class='boldTop'>";
        if ($row ['CUSN13'] == "0") {
            echo "&nbsp";
        } else {
            echo "<a href='maintenance/editAmDelivery.php?customerNumber=" . trim ( $row ['CUSN13'] ) . "&userId=" . $_SESSION ['userID'] . "&country=" . trim ( $row ['COUN13'] ) . "' target='_new'>" . trim ( $row ['CUSN13'] ) . "</a></td>";
        }
        echo "<td class='top'>";
        if ($row ['LOTS13'] == "0") {
            echo "&nbsp";
        } elseif (strlen ( $row ['LOTS13'] ) == "3") {
            $LOTS13 = "0" . $row ['LOTS13'];
            echo $LOTS13;
        } else {
            echo $row ['LOTS13'];
        }
        echo "</td>";
        echo "<td class='top'>" . $row ['NAME05'] . "</td>";
        echo "<td class='top'>" . $row ['NAMER5'] . "</td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href='maintenance/editAm.php?userId=" . $_SESSION ['userID'] . "&country=" . trim ( $row ['COUN13'] ) . "' target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";
            echo "<a href='maintenance/editAm.php?action=delete&userId=" . $_SESSION ['userID'] . "&country=" . trim ( $row ['COUN13'] ) . "' target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";
        } else {
            echo "&nbsp";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "<tr><td colspan='2'><a href='maintenance/addCountry.php' target='_new'>Add Country</a></Td></tr>";
    echo "</table>";
}

function list_major_classes( $selected, $duplicates ){
	global $conn, $CONO;

	if( $duplicates == 'no' ){
		$sql = "SELECT PSAR15, PRMD15 FROM DESC WHERE CONO15 = 'DI' AND PRMT15 = 'PGMJ' AND PRMD15 <> '' "
	     . " AND PSAR15 not in ( SELECT PGMJ30 FROM CIL30 )"
		 . "ORDER BY PSAR15 ASC";
	}else{
		$sql = "SELECT PSAR15, PRMD15 FROM DESC WHERE CONO15 = 'DI' AND PRMT15 = 'PGMJ' AND PRMD15 <> '' "
		. " ORDER BY PSAR15 ASC";

	}


	$classRes = odbc_prepare ( $conn, $sql );
	odbc_execute ( $classRes );

	while ( $classRow = odbc_fetch_array ( $classRes ) ) {
		echo "<option ";
		if ($selected == trim ( $classRow ['PSAR15'] )) {
			echo "SELECTED ";
		}
		echo "value='" . trim ( $classRow ['PSAR15'] ) . "'>" . trim ( $classRow ['PSAR15'] ) . " - " . trim ( $classRow ['PRMD15'] );
	}


}

function send_pricing_notification_email( $userFullName, $userEmail, $userPassword, $itemClassName, $SHOW_NOTIFICATIONS, $SITENAME, $ID01, $DESC01, $className, $typeName, $FROM_USER ){

	$encryptedPassword = base64_encode ( $userPassword );

	$message = "\n\n<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>";
	$message .= "Dear $userFullName,<br><br><br>";

	//EC-D0249 - Added complete logic and changed messages
		$message .= "An " . $SITENAME . " ticket has been sent to the $itemClassName pricing team in which you are a defined as a contact<br><br>";
		$subject = "$SITENAME $itemClassName Pricing Team Ticket - #$ID01 - $DESC01";

	$message .= "Ticket#: " . $ID01 . "<br>";
	$message .= "Classification: " . $className . "<br>";
	$message .= "Type: " . $typeName . "<br><br><br>";
	$message .= "To directly reference the ticket click the link below:<br><br>";
	$message .= "<a href='$mtpUrl/showTicketDetails.php?ID01=$ID01&email=$userEmail&epass=$encryptedPassword'>View Ticket</a><br><br>";
	$message .= "Thank You<br>";
	$message .= $FROM_USER;


	//Sets up mail to use HTML formatting
	$strHeaders = "MIME-Version: 1.0\r\n";
	$strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$strHeaders .= "From: " . $FROM_MAIL;

	if( $SHOW_NOTIFICATIONS == true ){
		echo $message . "<hr>";
	}else{
		mail ( $userEmail, $subject, $message, $strHeaders );
	}

}

?>