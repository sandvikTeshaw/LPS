<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            notificatoinMaintenanceFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to notifications Maintenance Area
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification
 *  LP0054     AD     20/05/2019 LP0054 - LPS - Create "Assign to ____" Buttons
 *  LP0084     AD     30/09/2019 LP0084 - LPS - Allow TSD's to be identified by Item Class and PGMJ Combination
 *  LP0085     AD     08/10/2019    GLBAU-18097_4 LPS Tickets Aurora under regional order process support
 *  lp0087     AD     21/10/2019    Button assign to inventory Planner
 *
 **/

//GLBAU8595 - Outbound Planner
function list_planner_table(){
    global $CONO, $conn, $IMG_DIR;
    ?>
          <table border=0 cellspacing=0 cellpadding=2 width=95%>
                <tr class='header'>
                    <td class='header' width=4%>Planner Code</td>
                    <td class='header'>DI Name</td>
                    <td class='header'>Planner</td>
                    <td class='header'>OPS Manager</td>
                    <td class='header'>Active</td>
                    <td class='header' colspan='2'>Action</td>
                </tr>
    <?
        $sql = "select PSAR15, PRMD15 from DESC where CONO15='DI' 
            and PRMT15='TN' and PSAR15 
            NOT IN ( select SLMN38 from CIL38)";
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
    
        $rowCounter = 0;
        while ( $row = odbc_fetch_array ( $res ) ) {
            $rowCounter ++;
            $PSAR15 = $row ['PSAR15'];
            $buyerFirst = substr ( $row ['PRMD15'], 0, 2 );
            $buyerLast = substr ( $row ['PRMD15'], strpos ( $row ['PRMD15'], " " ), 4 );
           // echo trim($buyerFirst) . " " . trim($buyerLast);
            $hlp05Sql = "SELECT ID05, NAME05 FROM HLP05L03 WHERE lcase(NAME05) LIKE '" .
                strtolower ( trim ( $buyerFirst ) );
            $hlp05Sql .= "%' AND lcase(NAME05) LIKE '%" . strtolower ( trim ( $buyerLast ) ) . "%' FETCH FIRST ROW ONLY";
            $nameRes = odbc_prepare ( $conn, $hlp05Sql );
            odbc_execute ( $nameRes );
            
            while ( $nameRow = odbc_fetch_array ( $nameRes ) ) {
                $newUserId = $nameRow ['ID05'];
            }
            if (! $newUserId) {
                $newUserId = 0;
            }else{
                $newUserId = $newUserId;
            }

           // echo $newUserId . "<BR />";
            // Insert in CIL38 table 
            $nextCIL38 = get_next_unique_id ( FACSLIB, "CIL38", "ID38", "" );
            $class = 3; //only for global order process support tickets
            //$newUserId = $newUserID; // from function above
            $SLMN38 = $PSAR15;
            $active = "Y";
            $insertSql = "insert into CIL38 values(". $nextCIL38 . "," .  $class . "," . $newUserId .  "," . $newUserId .  ",'" . $SLMN38 . "','" . $active . "')";
             // Execute insert into CIL38
            
            $insertRes = odbc_prepare ( $conn, $insertSql);
            $result = odbc_execute( $insertRes );
            //Fetch data to display in matrix.
                        
        }
        $sqlCIL38 = "select ID38, SLMN38, ACTV38, T2.NAME05 AS T2NAME05, T3.NAME05 AS T3NAME05, PRMD15 from CIL38 T1
            Inner join HLP05 T2
            On T1.PLAN38 = T2.ID05
            Inner join HLP05 T3
            On T1.OPMG38 = T3.ID05
            Inner join DESC T4
            ON T4.CONO15='DI' and T1.SLMN38 = T4.PSAR15 and T4.PRMT15='TN'
            Where CLAS38='3'";
       // echo $sqlCIL38 . '<br />';
        $sqlRes = odbc_prepare ( $conn, $sqlCIL38 );
        odbc_execute ( $sqlRes );
        
        $planCounter = 0;
        while ( $row = odbc_fetch_array ( $sqlRes ) ) {
            $planCounter++;
            //echo  $planCounter . " " . $row[1];
            if ($planCounter % 2) {
                echo "<tr class='alternate'>";
            } else {
                echo "<tr>";
            }
            echo "<td class='top'>" . trim ( $row ['SLMN38'] ) . "</a></td>";
            echo "<td class='top'>" . trim ( $row ['PRMD15'] ) . "</a></td>";
            echo "<td class='top'>" . trim ( $row ['T2NAME05'] ) . "</a></td>";
            echo "<td class='top'>" . trim ( $row ['T3NAME05'] ) . "</a></td>";
            echo "<td class='top'>" . trim ( $row ['ACTV38'] ) . "</a></td>";
            echo "<td class='right'>";
//            if ($_SESSION ['authority'] == "S") {
                echo "<a href='maintenance/editPlanner.php?id=" . trim ( $row ['SLMN38'] ) . "&action=editPlanner' target='_new'><img src=$IMG_DIR/edit.gif border=0 alt='Edit'></a>";
                echo "<a href='maintenance/deletePlanner.php?id=" . trim ( $row ['SLMN38'] ) . "&action=deletePlanner' target='_new'><img src=$IMG_DIR/delete.gif border=0 alt='Edit'></a>";
        
//            } else {
//                echo "&nbsp";
//            }
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
        if ( isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {
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
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCounter ++;
        if ($rowCounter % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "</tr>";
        }
        echo "<td class=boldTop>" . $row ['DESC15'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</td>";
        echo "<td class='right'>";
        if (isset($_SESSION ['authority']) && $_SESSION ['authority']  == "S") {
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
 * Function displays a list of Point of First Contacts
 *
 * @param string $strc
 */
//D0359 - Removed Back-up
function list_pfc_table($strc) {
    global $conn, $CONO, $IMG_DIR;

    $sql = "SELECT TYPE04, NAME05, TYPE2X  FROM CIL20XJ01 WHERE STRC2X = '$strc' ORDER BY TYPE2X ASC";
    ?>

<table border=0 cellspacing=0 cellpadding=2 width=80% border=0>
                    <tr class='header'>
                        <td class='boldMed'>Type</td>
                        <td class='boldMed' colspan='2'>PFC</td>
                    </tr>
<?
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCount = 0;
    //echo $sql;s
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCount ++;

        if ($rowCount % 2) {
            echo "<tr class=''>";
        } else {
            echo "<tr class='alternate'>";
        }

        echo "<td class='boldTop' width=50%>" . $row ['TYPE04'] . "</td>";
        echo "<td width=45%><b>" . $row ['NAME05'] . "</b></td>";
        echo "<td class='right' width=5%>";
        if (isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {
            echo "<a href='maintenance/stockroomMaintenance.php?stockroom=$strc&type=" . $row ['TYPE2X'] . "&action=editPfc' target='_new'><img src=$IMG_DIR/edit.gif border=0 alt='Edit PFC'></a>";
            echo "<a href='maintenance/stockroomMaintenance.php?stockroom=$strc&type=" . $row ['TYPE2X'] . "&action=deletePfc' target='_new'><img src=$IMG_DIR/delete.gif border=0 alt='Delete PFC'></a>";
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

 //lp00_54_AD   $sql = "SELECT STRC20, STRN20, NAME05, NAMER5 FROM CIL20J01 WHERE CONO20 = '$CONO' ORDER BY STRC20 ASC";
    $sql = "SELECT STRC20, STRN20, C.NAME05 AS CNAME05, NAMER5, FN.NAME05 AS FNNAME05, WN.NAME05 AS WNNAME05 ".  //lp00_54_AD
        " FROM CIL20J01 C ". //lp00_54_AD
        " LEFT JOIN CIL49 FC ON STRC20=FC.KEY249 AND FC.KEY149='FRE' ".//lp00_54_AD
        "  LEFT JOIN HLP05 FN ON FN.ID05=FC.USER49  ".//lp00_54_AD
        " LEFT JOIN CIL49 WC ON STRC20=WC.KEY249 AND WC.KEY149='WAR' ".//lp00_54_AD
        "  LEFT JOIN HLP05 WN ON WN.ID05=WC.USER49 ".//lp00_54_AD
        " WHERE CONO20 = '$CONO' ORDER BY STRC20 ASC";//lp00_54_AD
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
                            <td class='header' colspan='1'>Operations Manager</td>
                            <td class='header' colspan='1'>Freight Contact</td> <!-- LP0054_AD -->
                            <td class='header' colspan='1'>Warehouse Contact</td> <!-- LP0054_AD -->
                            
                        </tr>
<?
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );

    while ( $row = odbc_fetch_array ( $res ) ) {
        echo "<tr class='alternate'>";
        echo "<td class='boldMedTop'>" . $row ['STRC20'] . "-" . $row ['STRN20'] . "</td>";
        echo "<td><b>" . $row ['CNAME05'] . "</b></td>";
        echo "<td><b>" . $row ['NAMER5'] . "</b></td>";
        echo "<td><b>" . $row ['FNNAME05'] . "</b></td>";// <!-- LP0054_AD -->
        echo "<td><b>" . $row ['WNNAME05'] . "</b></td>";//<!-- LP0054_AD -->
        echo "<td class='right'>";
        if (isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {
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
                                <td colspan='2'><a
                                    href='maintenance/pricingContactMaintenance.php?action=addPricingContact'
                                    target='_new'>Add Pricing Contact</a></Td>
                            </tr>
                            <tr class='header'>
                                <td class='header'>Brand</td>
                                <td class='header' colspan='2'>Responsible</td>
                            </tr>
<?
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCount = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCount ++;

        if ($rowCount % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "<tr class=''>";
        }
        echo "<td class='boldTop'>" . $row ['DESC16'] . "</td>";
        echo "<td><b>" . $row ['NAME05'] . "</b></td>";
        echo "<td class='right'>";
        if (isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {
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
 * Function displays list of Buyers
 *
 */
//D0359 - Modified function to show only correct information based on user profile.
//        Removed Back-up, Supervisor, Manager and Director from CIL25
function list_buyers() {
    global $conn, $CONO, $IMG_DIR;

    $sql = "SELECT  cast( PLNN06 as CHAR(25) CCSID 285) AS PLNN06, PLAN06 FROM PMP06 WHERE CONO06 = '$CONO' ORDER BY PLAN06";

    ?>
    <table border=0 cellspacing=0 cellpadding=2 width=100%>
                                <tr>
                                    <td>&nbsp</td>
                                </tr>
                                <tr>
                                    <td><a href='maintenanceNotification.php?maintenanceType=Buyer'>Update
                                    New Buyers</a></td>
                                </tr>
                                <tr class='header'>
                                    <td class='header'>Buyer#</td>
                                    <td class='header'>DI Name</td>
                                    <td class='header'>Coordinator</td>
                                    <td class='header'>Expedite</td>
                                    <td class='header'>P&A</td>
                                    <td class='header' colspan='2'>Active</td>
                                </tr>

<?
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $rowCount = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCount ++;

        if ($rowCount % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "<tr class=''>";
        }
        echo "<td class='boldTop'>" . trim ( $row ['PLAN06'] ) . "</td>";
        echo "<td class='boldTop'>" . trim ( $row ['PLNN06'] ) . "</td>";

        $plannerSql = "SELECT  NAME05, NAMEE5, NAMEP5, ACTV25, NAMEM5, NAMES5, NAMED5, BMNAM5, BSNAM5, BDNAM5 FROM CIL25J01 WHERE PLAN25=" . trim ( $row ['PLAN06'] );
        $planRes = odbc_prepare ( $conn, $plannerSql );
        odbc_execute ( $planRes );
       
        $plannerCount = 0;
        while ( $planRow = odbc_fetch_array ( $planRes ) ) {
            $plannerCount ++;

            echo "<td><b>" . trim ( $planRow ['NAME05'] ) . "</b></td>";
            echo "<td><b>" . trim ( $planRow ['NAMEE5'] ) . "</b></td>";
            echo "<td><b>" . trim ( $planRow ['NAMEP5'] ) . "</b></td>";
            echo "<td><b>" . trim ( $planRow ['ACTV25'] ) . "</b></td>";

            echo "<td class='right'>";
            if( isset($_SESSION ['authority'] ) == "S") {
                echo "<a href='maintenance/buyerMaintenance.php?plan=" . trim ( $row ['PLAN06'] ) . "&action=editBuyer' target='_new'><img src=$IMG_DIR/edit.gif border=0 alt='Edit'></a>";
                echo "<a href='maintenance/buyerMaintenance.php?plan=" . trim ( $row ['PLAN06'] ) . "&action=deleteBuyer' target='_new'><img src=$IMG_DIR/delete.gif border=0 alt='Edit'></a>";

            } else {
                echo "&nbsp";
            }
            echo "</td>";
        }

        echo "</tr>";

        if ($plannerCount == 0) {

            $buyerFirst = substr ( $row ['PLNN06'], 0, 2 );
            $buyerLast = substr ( $row ['PLNN06'], strpos ( $row ['PLNN06'], " " ), 4 );
            //echo trim($buyerFirst) . " " . trim($buyerLast);


            $hlp05Sql = "SELECT ID05, NAME05 FROM HLP05L03 WHERE lcase(NAME05) LIKE '" . strtolower ( trim ( $buyerFirst ) );
            $hlp05Sql .= "%' AND lcase(NAME05) LIKE '%" . strtolower ( trim ( $buyerLast ) ) . "%' FETCH FIRST ROW ONLY";
            
            $nameRes = odbc_prepare ( $conn, $hlp05Sql );
            odbc_execute ( $nameRes );

            while ( $nameRow = odbc_fetch_array ( $nameRes ) ) {
                $newUserId = $nameRow ['ID05'];
            }
            if (! $newUserId) {
                $newUserId = 0;
            }

            $nextCIL25Record = get_next_unique_id ( FACSLIB, "CIL25L00", "ID25", "" );
            
     
            $insertPlanSql = "INSERT INTO CIL25 VALUES(";
            $insertPlanSql .= $nextCIL25Record . "," . $row ['PLAN06'] . ", $newUserId, 0, 'Y', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";

            //echo $insertPlanSql . "<hr>";
            $planRes = odbc_prepare ( $conn, $insertPlanSql );
            odbc_execute ( $planRes );
        }
    }
}

/**
 * Function displays list of Account managers defined by $country
 *
 * @param integer $country
 */
function list_am_site_table($country) {
    global $CONO, $conn, $IMG_DIR;
    ?>
    <center>
                                <table border=0 cellspacing=0 cellpadding=2 width=95%>
                                    <tr class='header'>
                                        <td class='header'>Site</td>
                                        <td class='header'>Lotus Notes</td>
                                        <td class='header'>Outbound Planner</td>
                                        <td class='header' colspan='2'>Logistics</td>
                                    </tr>
<?
    //(2)Accnt Manager, (3)Ops Manager, (5)BackUp Accnt Manager, (6)BackUp Ops Manager, (7)Director, BackUp Director
    $sql = "SELECT DSEQ1X, LOTS1X, NAME05, NAMER5, COUN1X, NAMBA5, NAMBO5, NAMED5, NAMBD5  FROM CIL13XJ1 WHERE COUN1X='$country' ORDER BY DSEQ1X ASC";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );

    //echo $sql;
    $rowCounter = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $rowCounter ++;
        if ($rowCounter % 2) {
            echo "<tr class='alternate'>";
        } else {
            echo "</tr>";
        }

        echo "<td class='boldTop'>" . trim ( $row ['DSEQ1X'] ) . "</a></td>";
        echo "<td class='top'>";
        if ($row ['LOTS1X'] == "0") {
            echo "&nbsp";
        } elseif (strlen ( $row ['LOTS1X'] ) == "3") {
            $LOTS13 = "0" . $row ['LOTS1X'];
            echo $LOTS13;
        } else {
            echo $row ['LOTS1X'];
        }
        echo "</td>";
        echo "<td class='top'>" . $row ['NAME05'] . "</td>";
        echo "<td class='top'>" . $row ['NAMER5'] . "</td>";
        echo "<td class='right'>";
        if ($_SESSION ['authority'] == "S") {
            echo "<a href='$PHP_SELF?action=edit&deliverySequence=" . $row ['DSEQ1X'] . "&userId=" . $_SESSION ['userID'] . "&country=" . trim ( $row ['COUN1X'] ) . "'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";
            echo "<a href='$PHP_SELF?action=deleteSite&deliverySequence=" . $row ['DSEQ1X'] . "&userId=" . $_SESSION ['userID'] . "&country=" . trim ( $row ['COUN1X'] ) . "'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";
        } else {
            echo "&nbsp";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</center>";
}

/**
 * Function defines the Account manager's email information defined by the market area
 *
 * @param ineteger $marketArea
 * @param array $emailArray
 * @param ineteger $id
 * @return array of Account Manager Email information
 */
function get_am_mail_by_market($marketArea, $emailArray, $id) {
    global $conn, $CONO;
    
    $sql = "SELECT NAME05, EMAIL05, AVAL05, NAMBA5, MAIBA5, ID05, IDBA05, PASS05, PASBA5 FROM CIL13J01 WHERE COUN13='" . $marketArea . "'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        
        //D0359 - Start - Get Backup Info ***********************************
        $backId = trim(get_back_up_id( $row ['ID05'] )); //Get AM back-up by market
        $backInfo = user_info_by_id( $backId );
        $back['name'] = trim($backInfo['NAME05']);
        $back['email'] = trim($backInfo['EMAIL05']);
        $back['pass'] = trim($backInfo['PASS05']);
        $back['availability'] = trim($backInfo['AVAL05']);
        //D0359 - End - Get Backup Info ***********************************
        
        if ($row ['AVAL05'] == "Y" || $row ['MAIBA5'] == "" || $back['availability'] == "N" ) {
            
            $am ['name'] = trim($row ['NAME05']);
            $am ['email'] = trim($row ['EMAIL05']);
            $am ['pass'] = trim($row ['PASS05']);
            $am ['owner'] = trim($row ['ID05']);
        } else {
            
            //D0359 - Start - Backup Change ***********************************
            //$am ['name'] = $row [3];
            //$am ['email'] = $row [4];
            //$am ['pass'] = $row [8];
            //$am ['owner'] = $row [6];
            
            $am ['name'] = trim($back['name']);
            $am ['email'] = trim($back['email']);
            $am ['pass'] = trim($back['pass']);
            $am ['owner'] = trim($backId);
            
            //$backUpOwnerInfo = user_cookie_info($row [6]);
            //$am ['owner'] = $backUpOwnerInfo['ID05'];
            //D0359 - End - Backup Change  ***********************************
        }
    }
    return $am;
    
}

/* Function displays list of Global Returns Resources
 *
 */

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
        if ( isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {
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
        if (isset( $_SESSION ['authority'] ) && $_SESSION ['authority'] == "S") {
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
        if ( isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {
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
//************************************************* LP054_ad start **************************************************
/* Function displays list of Technical Support Desk (TSD)Contacts
 *
 */

/*
 
 */
function list_tsd_table() { //LP054_ad
    global $CONO, $conn, $IMG_DIR;//LP054_ad
    ?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%><!-- //LP054_ad -->
                                        <tr><!-- //LP054_ad -->
                                            <td>&nbsp</td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
                                        <tr><!-- //LP054_ad -->
                                            <td colspan='2'><a
                                                href='maintenance/tsdMaintenance.php?action=add'
                                                target='_new'>Add TSD Resource</a></Td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
                                        <tr class='header'><!-- //LP054_ad -->
                                            <td class='header'>Item Class</td><!-- //LP054_ad -->
                                            <td class='header'>Item PGMJ</td><!-- //LP084_ad -->
                                            
                                            <td class='header' colspan='2'>Responsible</td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
<?//LP054_ad
//lp0084_ad    $sql = "SELECT ID49,   USER49,    KEY249,   NAME05  FROM CIL49 T1 "//LP054_ad
//lp0054_Ad---------ID---ContactID--partClass--PGMJ---  Name -----------------
    $sql = "SELECT ID49,   USER49,    KEY249,  KEY349  , NAME05  FROM CIL49 T1 "//lp0084_ad
    . " INNER JOIN HLP05 T2"//LP054_ad
    . " ON T1.USER49 = T2.ID05"//LP054_ad     
    . " WHERE KEY149 = 'TSD' "//LP054_ad
 //lp0084_ad   . " ORDER BY KEY249 ";//LP054_ad
    . " ORDER BY KEY249,USER49,KEY349 ";//LP084_ad
    

    $res = odbc_prepare ( $conn, $sql );//LP054_ad
    odbc_execute ( $res );//LP054_ad
    $rowCounter = 0;//LP054_ad
    while ( $row = odbc_fetch_array ( $res ) ) {//LP054_ad
        $rowCounter ++;//LP054_ad
        if ($rowCounter % 2) {//LP054_ad
            echo "<tr class='alternate'>";//LP054_ad
        } else {//LP054_ad
            echo "</tr>";//LP054_ad
        }//LP054_ad
 //lp0084_ad       echo "<td class=boldTop>" . $row ['KEY249'] . "</td>";//LP054_ad
        echo "<td class=boldTop>" .get_description_brand_name( $row ['KEY249']) . "</td>";//LP054_ad
        if (trim($row ['KEY349'])=='ALL PGMJ' ) echo "<td class=boldTop> * </td>";//LP084_ad
        else  echo "<td class=boldTop>" .get_description_PGMJ( $row ['KEY349'] ). "</td>";//LP084_ad
        echo "<td><b>" . $row ['NAME05'] . "</td>";//LP054_ad
        echo "<td class='right'>";//LP054_ad
        if (isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {//LP054_ad
            echo "<a href=maintenance/tsdMaintenance.php?action=edit&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";//LP054_ad
            echo "<a href=maintenance/tsdMaintenance.php?action=delete&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";//LP054_ad
        } else {//LP054_ad
            echo "&nbsp";//LP054_ad
        }//LP054_ad

        echo "</td>";//LP054_ad
        echo "</tr>";//LP054_ad
    }//LP054_ad
    echo "</table>";//LP054_ad
    echo " <br>* - This person is the default Technical Support for all PGMJ groups inside this class if it is not specified otherwise";//LP084_ad
    
}//LP054_ad

/* Function displays list of Freight Contacts
 *
 */

function list_freight_table() { //LP054_ad
    global $CONO, $conn, $IMG_DIR;//LP054_ad
    ?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%><!-- //LP054_ad -->
                                        <tr><!-- //LP054_ad -->
                                            <td>&nbsp</td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
                                        <tr><!-- //LP054_ad -->
                                            <td colspan='2'><a
                                                href='maintenance/freightMaintenance.php?action=add'
                                                target='_new'>Add Freight Contact</a></Td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
                                        <tr class='header'><!-- //LP054_ad -->
                                            <td class='header'>Stockroom</td><!-- //LP054_ad -->
                                            <td class='header' colspan='2'>Responsible</td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
<?//LP054_ad
//lp0054_Ad---------ID---ContactID--partClass-- Name -----------------
    $sql = "SELECT ID49,   USER49,    KEY249,   NAME05  FROM CIL49 T1 "//LP054_ad
    . " INNER JOIN HLP05 T2"//LP054_ad
    . " ON T1.USER49 = T2.ID05"//LP054_ad
    . " WHERE KEY149 = 'FRE' "//LP054_ad
    . " ORDER BY KEY249 ";//LP054_ad


    $res = odbc_prepare ( $conn, $sql );//LP054_ad
    odbc_execute ( $res );//LP054_ad
    $rowCounter = 0;//LP054_ad
    while ( $row = odbc_fetch_array ( $res ) ) {//LP054_ad
        $rowCounter ++;//LP054_ad
        if ($rowCounter % 2) {//LP054_ad
            echo "<tr class='alternate'>";//LP054_ad
        } else {//LP054_ad
            echo "</tr>";//LP054_ad
        }//LP054_ad
        echo "<td class=boldTop>" . $row ['KEY249'] . "</td>";//LP054_ad
        echo "<td><b>" . $row ['NAME05'] . "</td>";//LP054_ad
        echo "<td class='right'>";//LP054_ad
        if ( isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {//LP054_ad
            echo "<a href=maintenance/freightMaintenance.php?action=edit&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";//LP054_ad
            echo "<a href=maintenance/freightMaintenance.php?action=delete&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";//LP054_ad
        } else {//LP054_ad
            echo "&nbsp";//LP054_ad
        }//LP054_ad

        echo "</td>";//LP054_ad
        echo "</tr>";//LP054_ad
    }//LP054_ad
    echo "</table>";//LP054_ad
}//LP054_ad


function list_sourcing_table() { //LP054_ad
    global $CONO, $conn, $IMG_DIR;//LP054_ad
    ?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%><!-- //LP054_ad -->
                                        <tr><!-- //LP054_ad -->
                                            <td>&nbsp</td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
                                        <tr><!-- //LP054_ad -->
                                            <td colspan='2'><a
                                                href='maintenance/srcMaintenance.php?action=add'
                                                target='_new'>Add Sourcing Contact</a></Td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
                                        <tr class='header'><!-- //LP054_ad -->
                                            <td class='header'>Supplier Number</td><!-- //LP054_ad -->
                                            <td class='header' colspan='3'>Supplier Name</td><!-- //LP054_ad -->
                                            <td class='header' colspan='3'>Responsible</td><!-- //LP054_ad -->
                                        </tr><!-- //LP054_ad -->
<?//LP054_ad
//lp0054_Ad---------ID---ContactID--partClass-- Name -----------------
    $sql = "SELECT DISTINCT ID49,   USER49,    KEY249,   NAME05, T3.SNAM05 AS SNAME  FROM CIL49 T1 "//LP054_ad
    . " INNER JOIN HLP05 T2"//LP054_ad
    . " ON T1.USER49 = T2.ID05"//LP054_ad
    . " LEFT JOIN PLP05 T3"//LP054_ad
    . " ON T1.KEY249 = T3.SUPN05 AND T3.CONO05='DI' AND DSEQ05='000' "//LP054_ad
    . " WHERE KEY149 = 'SRC' "//LP054_ad
    . " ORDER BY KEY249 ";//LP054_ad


    $res = odbc_prepare ( $conn, $sql );//LP054_ad
    odbc_execute ( $res );//LP054_ad
    $rowCounter = 0;//LP054_ad
    while ( $row = odbc_fetch_array ( $res ) ) {//LP054_ad
        $rowCounter ++;//LP054_ad
        if ($rowCounter % 2) {//LP054_ad
            echo "<tr class='alternate'>";//LP054_ad
        } else {//LP054_ad
            echo "</tr>";//LP054_ad
        }//LP054_ad
        echo "<td class=boldTop>" . $row ['KEY249'] . "</td>";//LP054_ad
        echo "<td class=boldTop>" . $row ['SNAME'] . "</td>";//LP054_ad
        echo "<td><td><td><b>" . $row ['NAME05'] . "</td>";//LP054_ad
        echo "<td class='right'>";//LP054_ad
        if (isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S" ) {//LP054_ad
            echo "<a href=maintenance/srcMaintenance.php?action=edit&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";//LP054_ad
            echo "<a href=maintenance/srcMaintenance.php?action=delete&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";//LP054_ad
        } else {//LP054_ad
            echo "&nbsp";//LP054_ad
        }//LP054_ad

        echo "</td>";//LP054_ad
        echo "</tr>";//LP054_ad
    }//LP054_ad
    echo "</table>";//LP054_ad
}//LP054_ad


//************************************************* LP054_ad end ****************************************************
function list_localpurchasing_table() { //LP085_ad
    global $CONO, $conn, $IMG_DIR;//LP085_ad
    ?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%><!-- //LP085_ad -->
                                        <tr><!-- //LP085_ad -->
                                            <td>&nbsp</td><!-- //LP085_ad -->
                                        </tr><!-- //LP085_ad -->
                                        <tr><!-- //LP085_ad -->
                                            <td colspan='2'><a
                                                href='maintenance/lpcMaintenance.php?action=add'
                                                target='_new'>Add Local Purchasing Contact</a></Td><!-- //LP085_ad -->
                                        </tr><!-- //LP085_ad -->
                                        <tr class='header'><!-- //LP085_ad -->
                                            <td class='header'>Country</td><!-- //LP085_ad -->
                                            <td class='header' colspan='3'>Responsible</td><!-- //LP085_ad -->
                                            <td class='header'></td><!-- //LP085_ad -->
                                        </tr><!-- //LP085_ad -->
<?//LP085_ad
//lp0054_Ad---------ID---ContactID--partClass-- Name -----------------
    $sql = "SELECT DISTINCT ID49,   USER49,    KEY249,   NAME05  FROM CIL49 T1 "//LP085_ad
    . " INNER JOIN HLP05 T2"//LP085_ad
    . " ON T1.USER49 = T2.ID05"//LP085_ad
    . " WHERE KEY149 = 'LPC' "//LP085_ad
    . " ORDER BY KEY249 ";//LP085_ad


    $res = odbc_prepare ( $conn, $sql );//LP085_ad
    odbc_execute ( $res );//LP085_ad
    $rowCounter = 0;//LP085_ad
    while ( $row = odbc_fetch_array ( $res ) ) {//LP085_ad
        $rowCounter ++;//LP085_ad
        if ($rowCounter % 2) {//LP085_ad
            echo "<tr class='alternate'>";//LP085_ad
        } else {//LP085_ad
            echo "</tr>";//LP085_ad
        }//LP054_ad
        echo "<td class=boldTop>" . $row ['KEY249'] . "</td>";//LP085_ad
        echo "<td><td><td><b>" . $row ['NAME05'] . "</td>";//LP085_ad
        echo "<td class='right'>";//LP085_ad
        if (isset( $_SESSION ['authority']) && $_SESSION ['authority'] == "S") {//LP085_ad
            echo "<a href=maintenance/lpcMaintenance.php?action=edit&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";//LP085_ad
            echo "<a href=maintenance/lpcMaintenance.php?action=delete&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";//LP085_ad
        } else {//LP085_ad
            echo "&nbsp";//LP085_ad
        }//LP054_ad

        echo "</td>";//LP085_ad
        echo "</tr>";//LP085_ad
    }//LP085_ad
    echo "</table>";//LP085_ad
}//LP085_ad

function list_inventoryplanner_table() { //LP0087_ad
    global $CONO, $conn, $IMG_DIR;//LP0087_ad
    ?>
      <table border=0 cellspacing=0 cellpadding=2 width=80%><!-- //LP0087_ad -->
                                        <tr><!-- //LP0087_ad -->
                                            <td>&nbsp</td><!-- //LP0087_ad -->
                                        </tr><!-- //LP0087_ad -->
                                        <tr><!-- //LP0087_ad -->
                                            <td colspan='1'><a
                                                href='maintenance/inpMaintenance.php?action=add'
                                                target='_new'>Add Inventory Planner Contact</a></Td><!-- //LP0087_ad -->
                                        </tr><!-- //LP0087_ad -->
                                        <tr class='header'><!-- //LP0087_ad -->
                                            <td class='header' colspan='3'>Responsible</td><!-- //LP0087_ad -->
                                            <td class='header'></td><!-- //LP0087_ad -->
                                        </tr><!-- //LP0087_ad -->
<?//LP0087_ad
//lp0054_Ad---------ID---ContactID--partClass-- Name -----------------
    $sql = "SELECT DISTINCT ID49,   USER49,    KEY249,   NAME05  FROM CIL49 T1 "//LP0087_ad
    . " INNER JOIN HLP05 T2"//LP0087_ad
    . " ON T1.USER49 = T2.ID05"//LP0087_ad
    . " WHERE KEY149 = 'INP' "//LP0087_ad
    . " ORDER BY KEY249 ";//LP0087_ad


    $res = odbc_prepare ( $conn, $sql );//LP0087_ad
    odbc_execute ( $res );//LP0087_ad
    $rowCounter = 0;//LP0087_ad
    while ( $row = odbc_fetch_array ( $res ) ) {//LP0087_ad
        $rowCounter ++;//LP0087_ad
        if ($rowCounter % 2) {//LP0087_ad
            echo "<tr class='alternate'>";//LP0087_ad
        } else {//LP0087_ad
            echo "</tr>";//LP0087_ad
        }//LP054_ad
        //echo "<td class=boldTop>" . "</td>";//LP0087_ad //clas selector- not needed here
        echo "<td><b>" . $row ['NAME05'] . "</td>";//LP0087_ad
        echo "<td class='right'>";//LP0087_ad
        if ( isset($_SESSION ['authority']) && $_SESSION ['authority'] == "S") {//LP0087_ad
          //  echo "<a href=maintenance/inpMaintenance.php?action=edit&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/edit.gif' border=0 alt='Edit'></a>";//LP0087_ad
            echo "<a href=maintenance/inpMaintenance.php?action=delete&regionId=" . $row ['ID49'] . " target='_new'><img src='$IMG_DIR/delete.gif' border=0 alt='Delete'></a>";//LP0087_ad
        } else {//LP0087_ad
            echo "&nbsp";//LP0087_ad
        }//LP054_ad

        echo "</td>";//LP0087_ad
        echo "</tr>";//LP0087_ad
    }//LP0087_ad
    echo "</table>";//LP0087_ad
}//LP0087_ad


