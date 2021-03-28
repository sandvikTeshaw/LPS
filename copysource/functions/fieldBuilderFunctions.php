<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            fieldBuilderFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions for building user interface fields
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  LP0050      KS    01/08/2018    Create new LPS ticket type �Inbound Parts Not Assembled�
 *  LP0076      AD    26/07/2019    Peter Hetterington mail "Spelling and Capitilisation mistakes within the LPS system
 *  
 **/

function classification_select_box($database, $table, $valueField, $displayField, $whereClause, $orderBy, $curValue) {
    global $conn;
    
    if ($valueField != $displayField) {
        $sql = "SELECT $valueField, $displayField FROM $table $whereClause $orderBy";
    } else {
        $sql = "SELECT $valueField FROM $table $whereClause";
    }
    
    if (! isset($_SESSION ['selectClassArray'])) {
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
        $selectArray = array ();
        while ( $row = odbc_fetch_array ( $res ) ) {
            array_push ( $selectArray, $row );
        }
        
        $_SESSION ['selectClassArray'] = $selectArray;
    }
    
    //Calls array to sort correctly
    $sortedSelectArray = sortArrayByField ( $_SESSION ['selectClassArray'], $orderBy, false );
    
    foreach ( $sortedSelectArray as $sArray ) {
        echo "<option ";
        if ($curValue == $sArray [$valueField]) {
            echo " SELECTED";
        }
        echo " value=" . trim ( $sArray [$valueField] ) . ">" . trim ( $sArray [$displayField] ) . "</option>";
    }
}

/**
 * Defines the priority dependant on priorityId and parm
 *
 * @param integer $prtyIn
 * @param string $parm
 * @return string Discription of Priority
 */
function get_priority($prtyIn, $parm) {
    
    if ($parm == "short") {
        switch ($prtyIn) {
            case 1 :
                return "A (Unit Down)";
            case 2 :
                return "B (Customer Order)";
            case 3 :
                return "C (Inventory)";
            case 4 :
                return "D (Scheduled)";
            case 5 :                                                        //**LP0050
                return "E (Project)";                                       //**LP0050
        }
    } else if ($parm == "letter") {
        switch ($prtyIn) {
            case 1 :
                return "A";
            case 2 :
                return "B";
            case 3 :
                return "C";
            case 4 :
                return "D";
            case 5 :                                                        //**LP0050
                return "E";                                                 //**LP0050
        }
    } else {
        switch ($prtyIn) {
            case 1 :
                return "A - Unit Down - Criticality 1.";
            case 2 :
                return "B - Customer Order - Criticality 2."; //lp0076_ad_x
//lp0076_ad_x   return "B - Customer Order - criticality 2.";
            case 3 :
                return "C - Inventory Request - Criticality 3.";//lp0076_ad_x
//lp0076_ad_x   return "C - Inventory Request - criticality 3.";
            case 4 :
                return "D - Scheduled Order - Criticality 4 / 5 / 6.";
            case 5 :                                                        //**LP0050
                return "E - Project based Ticket";                          //**LP0050
        }
    }
}

/**
 * Function creats and displays a list of priorities for display
 *
 * @param integer $priorityId
 */
function priority_select_box($priorityId) {
    global $type;
    
    echo "<option ";
    if ($priorityId == 1) {
        echo "SELECTED ";
    }
    echo "value=1>A - Unit Down - Criticality 1.</option>";
    echo "<option ";
    if ($priorityId == 2) {
        echo "SELECTED ";
    }
    echo "value=2>B - Customer Order - Criticality 2.</option>";//lp0076_ad_x
    //lp0076_ad_x    echo "value=2>B - Customer Order - criticality 2.</option>";
    echo "<option ";
    if ($priorityId == 3 || $priorityId == "") {
        echo "SELECTED ";
    }
    echo "value=3>C - Inventory Request - Criticality 3.</option>";//lp0076_ad_x
    //lp0076_ad_x    echo "value=3>C - Inventory Request - criticality 3.</option>";
    echo "<option ";
    if ($priorityId == 4) {
        echo "SELECTED ";
    }
    echo "value=4>D - Scheduled Order   - Criticality 4 / 5 / 6.</option>";
    
    if( isset($type) && $type == 118  ){                                        //**LP0050 - Add conditional statement to ensure only available for 118
        echo "<option ";                                                        //**LP0050

        if ($priorityId == 5){                                                  //**LP0050    
            echo "SELECTED ";                                                   //**LP0050
        }                                                                       //**LP0050
        echo "value=5>E - Project based Ticket</option>";                       //**LP0050
    }
    
}

/**
 * Function creats and displays a list of priorities for display
 *
 * @param integer $priorityId
 */
function priority_short_select_box($priorityId) {
    echo "<option ";
    if ($priorityId == 1) {
        echo "SELECTED ";
    }
    echo "value=1>A (Unit Down)";
    echo "<option ";
    if ($priorityId == 2) {
        echo "SELECTED ";
    }
    echo "value=2>B (Customer Order)";
    echo "<option ";
    if ($priorityId == 3 || $priorityId == "") {
        echo "SELECTED ";
    }
    echo "value=3>C (Inventory)";
    echo "<option ";
    if ($priorityId == 4) {
        echo "SELECTED ";
    }
    echo "value=4>D (Scheduled)";
    
    echo "<option ";                                                    //**LP0050
    if ($priorityId == 5){                                              //**LP0050
        echo "SELECTED ";                                               //**LP0050
    }                                                                   //**LP0050
    echo "value=5>E (Project)";                                         //**LP0050
    
}

/*
 * Sets up drop down boxes for attributes
 *
 * @parm integer $class Classification of ticket
 * @parm integer $type Type of ticket
 * @parm integer $prnt unique identifier of the parent type of the drop down list
 * @parm $currentValue current value of attribute set on ticket
 * @return $attributeArray an array of attributes from CIL07
 */
function show_attribute_drop_list($class, $type, $prnt, $currentValue) {
    global $CONO, $conn;
    
    $dropSql = "SELECT ATTR07, NAME07 FROM CIL07L02 WHERE CLAS07 = $class AND TYPE07=$type AND PRNT07=$prnt";
    
    $dropRes = odbc_prepare ( $conn, $dropSql );
    odbc_execute ( $dropRes );
    
    //echo $dropSql;
    
    
    $attribCount = 0;
    echo "<option value=''>Select Option</option>";
    while ( $dropRow = odbc_fetch_array ( $dropRes ) ) {
        echo "<option ";
        if ($currentValue == $dropRow ['ATTR07']) {
            echo "SELECTED ";
        }
        echo "value='" . trim ( $dropRow ['ATTR07'] ) . "'>" . trim ( $dropRow ['NAME07'] ) . "</option>";
    }
}

/*
 * select box list of years in JBA format
 *
 * @parm integer $yearIn current value of year for select
 *
 * @return none
 */
function list_jba_year($yearIn) {
    for($i = $yearIn - 5; $i <= $yearIn + 5; ++ $i) {
        $showYear = "2" . substr ( $i, 0, 1 ) - 1 . substr ( $i, 1 );
        echo "<option ";
        if ($yearIn == $i) {
            echo "SELECTED";
        }
        echo " value='" . $i . "'>" . trim ( $showYear ) . "</option>";
    }
}

/*
 * select box list of months
 * @parm integer $monthIn current value of month for select
 *
 * @return none
 */
function list_months($monthIn) {
    $monthShow = array ("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" );
    
    for($m = 1; $m <= 12; ++ $m) {
        echo "<option ";
        if ($m == $monthIn) {
            echo "SELECTED ";
        }
        echo "value='$m'>" . trim ( $monthShow [$m] ) . "</option>";
    }
    
}

/*
 * select box list of days
 * @parm integer $dayIn current value of day for select
 *
 * @return none
 */
function list_days($dayIn) {
    
    for($d = 1; $d <= 31; ++ $d) {
        echo "<option ";
        if ($d == $dayIn) {
            echo "SELECTED ";
        }
        echo "value='$d'>" . trim ( $d ) . "</option>";
    }
}

/**
 * Function creates and displays a list of contries for market area
 *
 * @param integer $country
 */
function get_country_listing($country) {
    global $conn, $CONO;
    
    $sql = "SELECT PSAR15, PRMD15 FROM DESC WHERE CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY' ORDER BY PRMD15 ASC";
    $countryRes = odbc_prepare ( $conn, $sql );
    odbc_execute ( $countryRes );
    
    while ( $countryRow = odbc_fetch_array ( $countryRes ) ) {
        echo "<option ";
        if ($country == trim ( $countryRow ['PSAR15'] )) {
            echo "SELECTED ";
        }
        echo "value='" . trim ( $countryRow ['PSAR15'] ) . "'>" . trim ( $countryRow ['PRMD15'] );
    }
}

/**
 * Function retrieves and displays list of company codes
 *
 * @param integer $code
 */
function list_company_code($code) {
    global $conn;
    
    $sql = "SELECT CODE27, CNAM27 FROM DSH27L00 ORDER BY CODE27, CNAM27";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        echo "<option ";
        if ($row ['CODE27'] == $code) {
            echo "SELECTED ";
        }
        echo "value='" . trim ( $row ['CODE27'] ) . "'>" . trim ( $row ['CODE27'] ) . "-" . trim ( $row ['CNAM27'] ) . "</option>";
        
    }
}

/**
 * Function displays user's availability defined by $avail id
 *
 * @param char $avail
 */
function list_availability($avail) {
    global $conn;
    
    echo "<option ";
    if ($avail == "Y") {
        echo "SELECTED ";
    }
    echo "value='Y'>Available</option>";
    echo "<option ";
    if ($avail == "N") {
        echo "SELECTED ";
    }
    echo "value='N'>Out of Office</option>";
    
}

/**
 * Function displays list of responsibilities defined by brand
 *
 * @param integer $brand
 */
FUNCTION list_bran_resp($brand) {
    
    global $conn, $CONO;
    $sql = "SELECT BRAN15, DESC15 FROM CIL15 WHERE CONO15 = '$CONO'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        echo "<option";
        if ($brand == $row ['BRAN15']) {
            echo " SELECTED";
        }
        echo " value='" . trim ( $row ['BRAN15'] ) . "'>" . trim ( $row ['DESC15'] ) . "</option>";
    }
}

/**
 * Functino displays list of model types
 *
 * @param integer $model
 */
function list_model_type($model) {
    global $conn, $CONO;
    
    $sql = "SELECT MODL27 FROM CIL27 WHERE CONO27 = '$CONO'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    while ( $row = odbc_fetch_array ( $res ) ) {
        echo "<option";
        if (trim ( $model ) == trim ( $row ['MODL27'] )) {
            echo " SELECTED";
        }
        echo " value='" . trim ( $row ['MODL27'] ) . "'>" . trim ( $row ['MODL27'] ) . "</option>";
    }
}

function list_returns_stockrooms($strc) {
    global $conn;
    $sql = "select T1.ATTR07, T2.NAME07 as NAME07 from CIL07 T1";
    $sql .= " inner join CIL07 T2";
    $sql .= " on T1.ATTR07 = T2.PRNT07";
    $sql .= " WHERE T1.clas07=9 and t1.NAME07='Stockroom returned to'";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    while ( $row = odbc_fetch_array ( $res ) ) {
        echo "<option value='" . substr ( trim ( $row ['NAME07'] ), 0, 2 ) . "'>" . trim ( $row ['NAME07'] ) . "</option>";
    }
}

/**
 * Function creates and displays a list of regions
 *
 * @param integer $country
 */
function get_region_listing($region) {
    global $conn, $CONO;
    
    
    //$sql = "SELECT cast( PSAR15 as CHAR(10) CCSID 285) AS PSAR15, cast( PRMD15 as CHAR(30) CCSID 285) AS PRMD15 FROM DESC WHERE CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY' ORDER BY PRMD15 ASC";
    //D0481 - Added CLAS32 = 9 to query
    $sql = "SELECT ID32, NAME32, NAME05 FROM CIL32 T1"
        . " INNER JOIN HLP05 T2"
            . " ON T1.RESP32 = T2.ID05"
                . " WHERE NAME32 <>'' AND ACTF32 = 'Y' AND CLAS32=9"
                    . " ORDER BY NAME32";
                    $regRes = odbc_prepare ( $conn, $sql );
                    odbc_execute ( $regRes );
                    
                    while ( $regRow = odbc_fetch_array ( $regRes ) ) {
                        echo "<option ";
                        if ($region == trim ( $regRow ['ID32'] )) {
                            echo "SELECTED ";
                        }
                        echo "value='" . trim ( $regRow ['ID32'] ) . "'>" . trim ( $regRow ['NAME32'] ) . " - " . trim ( $regRow ['NAME05'] ) . "</option>";
                    }
}

//D0129 - Function gets drop down values
function get_drop_down_value( $class, $type, $prnt, $attr ){
    global $CONO, $conn;
    
    $sql = "SELECT NAME07 FROM CIL07L02 WHERE CLAS07 = $class AND TYPE07=$type AND PRNT07=$prnt"
    . " AND ATTR07=$attr";
    
    $res = odbc_prepare( $conn, $sql );
    odbc_execute ( $res );
    
    while( $row = odbc_fetch_array( $res ) ){
        return $row['NAME07'];
    }
}


