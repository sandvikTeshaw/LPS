<?php

/**
 * System Name:             Logistics Process Support
 * Program Name:            ajax_services.php<br>
 * Development Reference:   LP0019<br>
 * Description:             ajax_services.php is created for handling all the ajax calls
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP0018      AG    04/07/2017  Adding functions for the user profile enhancement 
 *  LP0029      TS    05/31/2018  Mass Upload Changes
 *  LP0055      AD    13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0068      AD    24/04/2019  GLBAU-16824_LPS Vendor Change
*/
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

set_time_limit ( 300 );

//D0301 - Added to compress output to remove all white space
ob_start("compressBuffer");

class AjaxServices {

    private $_RESULT = array('CODE' => 500, 'RESPONSE' => "Error has been occured, try again!");
    
    private $_CONN;
    
    function __construct() {
        // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
        // $this->_CONN = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
        $this->_CONN = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
    }
    
    function addSLA() {
        
         $class = trim($_REQUEST['classification']);
         $description = isset($_REQUEST['description']) && $_REQUEST['description'] != "" ? trim($_REQUEST['description']) : "";
         $type = trim($_REQUEST['type']);
         $priority = trim($_REQUEST['priority']);
         $escal_flag = trim($_REQUEST['escal-flag']);
         $busin_flag = trim($_REQUEST['business-flag']);
         $first_response = trim($_REQUEST['first-response']);
         $SLA_time = trim($_REQUEST['sla-time']);
         $escal_inc = ($escal_flag) == 1 && isset($_REQUEST['escalation-inc']) && $_REQUEST['escalation-inc'] != "" ? trim($_REQUEST['escalation-inc']) : 0;
         $ID = 0;
         $maxSql = "SELECT ID45 FROM CIL45 ORDER BY ID45 DESC LIMIT 1";
         $maxRes = odbc_prepare($this->_CONN, $maxSql);
         odbc_execute($maxRes);
         while( $maxRow = odbc_fetch_array($maxRes)){
             $ID = $maxRow['ID45'];
         
         }
         $ID++;
         
         $sql = "INSERT INTO CIL45 (ID45, DESC45, CLAS45, TYPE45, PRTY45, SLTM45, FRTM45, ESFL45, ETIN45, BDFL45, ACTV45) "
                . "VALUES (".$ID.", '". $description . "', " . $class . ", " . $type . ", " . $priority . ", " . $SLA_time . ", " . $first_response . ", " . $escal_flag . ", " . $escal_inc . ", " . $busin_flag . ", 1)";
         
         $res = odbc_prepare($this->_CONN, $sql);
            if (odbc_execute($res)) {
                $this->_RESULT['CODE'] = 200;
                $this->_RESULT['RESPONSE'] = "Your record has been successfully added.";
            }
        
        echo json_encode($this->_RESULT);
    }

    function editSLA() {
        
        $ID = trim($_REQUEST['ID']);
        $description = isset($_REQUEST['description']) && $_REQUEST['description'] != "" ? trim($_REQUEST['description']) : "";
        $priority = trim($_REQUEST['priority']);
        $escal_flag = trim($_REQUEST['escal-flag']);
        $busin_flag = trim($_REQUEST['business-flag']);
        $first_response = trim($_REQUEST['first-response']);
        $SLA_time = trim($_REQUEST['sla-time']);
        $escal_inc = ($escal_flag) == 1 && isset($_REQUEST['escalation-inc']) && $_REQUEST['escalation-inc'] != "" ? trim($_REQUEST['escalation-inc']) : 0;
        
        $sql = "UPDATE CIL45 SET DESC45 = '".$description."', PRTY45 = ".$priority.", SLTM45= ".$SLA_time.", FRTM45= ".$first_response.", ESFL45= ".$escal_flag.", ETIN45= ".$escal_inc.", BDFL45 = ".$busin_flag." WHERE ID45 = " . $ID;
        
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully added.";
        }
        
        echo json_encode($this->_RESULT);        
    }

    function deleteSLA() {
        $sql = "UPDATE CIL45 SET ACTV45 = 0 WHERE ID45 = " . trim($_REQUEST['param']);
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully deleted.";
        }
        echo json_encode($this->_RESULT);
    }

    function ticketTypesBasedOnClass() {
        $ticketTypes = "<option value=''>Select the Ticket Type</option>\n";
        if( $_REQUEST['param'] > 0 ){
            $sqlTypes = "SELECT ID04, TYPE04 FROM CIL04 C1 INNER JOIN CIL12 C2 ON C1.ID04 = C2.TYPE12 WHERE C2.CLAS12 = " . trim($_REQUEST['param']);

            $rsTypes = odbc_prepare($this->_CONN, $sqlTypes);
            odbc_execute($rsTypes);
            
            while($Type = odbc_fetch_array($rsTypes)){
                    $ticketTypes .= "<option value='" . trim($Type['ID04']) . "'";
                    if( trim($Type['ID04']) == $_REQUEST['typeId']) {
                        $ticketTypes .= " SELECTED='SELECTED' ";
                    }
                    $ticketTypes .= ">" . trim($Type['TYPE04']) . "</option>\n";
            }
            echo $ticketTypes;
        }else{
            
        }
    }
    
    //LP0029 - Function added for Mass Upload
    function ticketUploadTypesBasedOnClass( $id ){
        switch ($_REQUEST['id']) {
            case '3':
                $json = array("14"=>"Short Shipment",
                "19"=>"Over Shipment",
                "23"=>"Damaged Part"
                    );
                
                break;
            case '5':
                
                $json = array("60"=>"Price is Different on Similar Item",
                "61"=>"Competitor Feedback to GLP",
                "62"=>"Customer Feedback to GLP",
                "74"=>"Sandvik Feedback to GLP",
                "75"=>"Cost Check - GLP Team ONLY"
                    );
                break;
                
            case '7':
                
                $json = array("31"=>"Short Shipment",
                "32"=>"Over Shipment",
                "33"=>"Damaged Part"
                    );
                break;
                
            case '8':
                
                $json = array("43"=>"Price and Availability",
                            "55"=>"Country of Origin",
                            "56"=>"Long Term Declaration",
                            "57"=>"Material Data Safety Sheets",
                            "130"=>"Supplier Cost & Leadtime", //LP0055_AD
                            "133"=>"Vendor Change" //LP0055_AD
                );
                
                break;
        }
        
        echo json_encode($json);
    }
    function ticketStaticTypesBasedOnClass() {
        $ticketTypes = "";
        switch (trim($_REQUEST['param'])) {
            case '3':
                $ticketTypes .= "<option value='14'>Short Shipment</option>\n";
                $ticketTypes .= "<option value='19'>Over Shipment</option>\n";
                $ticketTypes .= "<option value='23'>Damaged Part</option>\n";
                break;
            case '5':
                $ticketTypes .= "<option value='60'>Price is Different on Similar Item</option>\n";
                $ticketTypes .= "<option value='61'>Competitor Feedback to GLP</option>\n";
                $ticketTypes .= "<option value='62'>Customer Feedback to GLP</option>\n";
                $ticketTypes .= "<option value='74'>Sandvik Feedback to GLP</option>\n";
                $ticketTypes .= "<option value='75'>Cost Check - GLP Team ONLY</option>\n";
                break;
                
            case '7':
                $ticketTypes .= "<option value='31'>Short Shipment</option>\n";
                $ticketTypes .= "<option value='32'>Over Shipment</option>\n";
                $ticketTypes .= "<option value='33'>Damaged Part</option>\n";
                break;
                
            case '8':
                $ticketTypes .= "<option value='43'>Price and Availability</option>\n";
                $ticketTypes .= "<option value='55'>Country of Origin</option>\n";
                $ticketTypes .= "<option value='56'>Long Term Declaration</option>\n";
                $ticketTypes .= "<option value='57'>Material Data Safety Sheets</option>\n";
                $ticketTypes .= "<option value='130'>Supplier Cost & Leadtime</option>\n";
                break;
        }
     
            echo $ticketTypes;
    }

    function NoMethod() {
        $Result['CODE'] = 404;
        $Result['RESPONSE'] = "No method defined";
        return $Result;
    }
    //LP0018 - Adding functions for the user profile enhancement
    function deUserAttrib(){
        $sql = "UPDATE HLP06 SET ACTV06 = 1 WHERE ID06 = " . trim($_REQUEST['ID']);
        if($_REQUEST['status'] == 1)
            $sql = "UPDATE HLP06 SET ACTV06 = 0 WHERE ID06 = " . trim($_REQUEST['ID']);
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully updated.";
        }
        echo json_encode($this->_RESULT);
    }
    
    function addUserAttrib(){
        $ID = 0;
        $maxSql = "SELECT ID06 FROM HLP06 ORDER BY ID06 DESC LIMIT 1";
        $maxRes = odbc_prepare($this->_CONN, $maxSql);
        odbc_execute($maxRes);
        while( $maxRow = odbc_fetch_array($maxRes)){
            $ID = $maxRow['ID06'];
             
        }
        $ID++;
        $attribute = trim($_REQUEST['attrib']);
        $description = isset($_REQUEST['description']) && $_REQUEST['description'] != "" ? trim($_REQUEST['description']) : "";
        $attrib_type = trim($_REQUEST['attrib-type']);
        $sort_order = trim($_REQUEST['sort-order']);
        $required_flag = trim($_REQUEST['required-flag']);
        
        $sql = "INSERT INTO HLP06 (ID06, ATTR06, DESC06, ATYP06, ACTV06, REQD06, SORT06) "
            . "VALUES (".$ID.", '" . $attribute . "', '". $description . "', '" . $attrib_type . "', 1, " . $required_flag . ", " . $sort_order . ")";
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            //Insert default entry into HLP07 iff the type is TEXT
            if($attrib_type == 'T'){
                $ID1 = 0;
                $maxSql1 = "SELECT ID07 FROM HLP07 ORDER BY ID07 DESC LIMIT 1";
                $maxRes1 = odbc_prepare($this->_CONN, $maxSql1);
                odbc_execute($maxRes1);
                while( $maxRow1 = odbc_fetch_array($maxRes1)){
                    $ID1 = $maxRow1['ID07'];
                     
                }
                $ID1++;
                $sql1 = "INSERT INTO HLP07 (ID07, ATTR07, STXT07, ACTV07, SORT07) "
                    . "VALUES (".$ID1.", " . $ID . ", '', 1,1)";
                $res1 = odbc_prepare($this->_CONN, $sql1);
                odbc_execute($res1);
            }
            
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully added.";
        }
        
        
        echo json_encode($this->_RESULT);
    }
    
    function editUserAttrib(){
        $ID = trim($_REQUEST['ID']);
        $description = isset($_REQUEST['description']) && $_REQUEST['description'] != "" ? trim($_REQUEST['description']) : "";
        $attrib_type = trim($_REQUEST['attrib-type']);
        $sort_order = trim($_REQUEST['sort-order']);
        $required_flag = trim($_REQUEST['required-flag']);
        
        $sql = "UPDATE HLP06 SET DESC06 = '".$description."', ATYP06 = '".$attrib_type."', REQD06= ".$required_flag.", SORT06= ".$sort_order." WHERE ID06 = " . $ID;
        
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully added.";
        }
        
        echo json_encode($this->_RESULT);
    }
    
    function deAttribOpt(){
        $sql = "UPDATE HLP07 SET ACTV07 = 1 WHERE ID07 = " . trim($_REQUEST['ID']);
        if($_REQUEST['status'] == 1)
            $sql = "UPDATE HLP07 SET ACTV07 = 0 WHERE ID07 = " . trim($_REQUEST['ID']);
        
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully updated.";
        }
        echo json_encode($this->_RESULT);
    }
    
    function addAttribOpt(){
               
        $maxSql = "SELECT ID07 FROM HLP07 ORDER BY ID07 DESC LIMIT 1";
        $maxRes = odbc_prepare($this->_CONN, $maxSql);
        odbc_execute($maxRes);
        $ID = 0;
        while( $maxRow = odbc_fetch_array($maxRes)){
            $ID = $maxRow['ID07'];
             
        }
        $ID++;
        $attrib_id = trim($_REQUEST['attrib-id']);
        $text = isset($_REQUEST['opt-txt']) && $_REQUEST['opt-txt'] != "" ? trim($_REQUEST['opt-txt']) : "";
        $active = trim($_REQUEST['active']);
        $sort_order = trim($_REQUEST['sort-order']);
        
        $sql = "INSERT INTO HLP07 (ID07, ATTR07, STXT07, ACTV07, SORT07) "
            . "VALUES (".$ID.", " . $attrib_id . ", '". $text . "', ". $active .", " . $sort_order . ")";
        
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $sql = "SELECT * FROM HLP07 ORDER BY ID07 DESC LIMIT 1";
            $res = odbc_prepare($this->_CONN, $sql);
            odbc_execute($res);
            $DATA = odbc_fetch_array($res);
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['ID'] = $DATA['ID07'];
            $this->_RESULT['STXT07'] = $DATA['STXT07'];
            $this->_RESULT['ACTIVE'] = $DATA['ACTV07'] == 1 ? "Yes" : "No";
            $this->_RESULT['SORT07'] = $DATA['SORT07'];
            $this->_RESULT['ATTR07'] = $DATA['ATTR07'];;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully added.";
        }
        
        echo json_encode($this->_RESULT);
    }
    
    function editAttribOpt(){
        $ID = trim($_REQUEST['ID']);
        $text = isset($_REQUEST['opt-txt']) && $_REQUEST['opt-txt'] != "" ? trim($_REQUEST['opt-txt']) : "";
        $sort_order = trim($_REQUEST['sort-order']);
        
        $sql = "UPDATE HLP07 SET STXT07 = '".$text."', SORT06= ".$sort_order." WHERE ID07 = " . $ID;
        
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully added.";
        }
        
        echo json_encode($this->_RESULT);
    }
    
    function delAttrib(){
        $sql = "DELETE FROM HLP08 WHERE ID08 = " . trim($_REQUEST['ID']);
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully updated.";
        }
        echo json_encode($this->_RESULT);
    }
    
    function addProfileAttrib(){
        $ID = 0;
        $maxSql = "SELECT ID08 FROM HLP08 ORDER BY ID08 DESC LIMIT 1";
        $maxRes = odbc_prepare($this->_CONN, $maxSql);
        odbc_execute($maxRes);
        while( $maxRow = odbc_fetch_array($maxRes)){
            $ID = $maxRow['ID08'];
             
        }
        $ID++;
        $exp = explode("-", trim($_REQUEST['user-attrib']));
        $attrib_id = $exp[0];
        $text = isset($_REQUEST['option-val']) && $_REQUEST['option-val'] != "" ? trim($_REQUEST['option-val']) : "";
        $option_id = isset($_REQUEST['option-sel']) && $_REQUEST['option-sel'] != "" ? trim($_REQUEST['option-sel']) : "";;
        $type = trim($_REQUEST['type']);
        $userID = trim($_REQUEST['UserID']);
        $OPID = 0;
        if($type == 'S')
            $OPID = $option_id;
        $sql = "INSERT INTO HLP08 (ID08, ATTR08, USER08, TEXT08, OPID08) "
            . "VALUES (".$ID.", " . $attrib_id . ", " . $userID . ",'". $text . "', ". $OPID .")";
        $res = odbc_prepare($this->_CONN, $sql);
        if (odbc_execute($res)) {
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully added.";
        }
        
        echo json_encode($this->_RESULT);
    }
    
    function updateProfileAttrib(){
        
        echo "<pre>";
        print_r($_REQUEST);
        echo "</pre>";
        exit;
        
        echo json_encode($this->_RESULT);
    }
    
    function GetAttributeOptions(){
        
        $fieldHTML = "";
        if (isset($_REQUEST['type']) && trim($_REQUEST['type']) == 'S'){
            $sql = "SELECT * FROM HLP07 INNER JOIN HLP06 ON HLP07.ATTR07 = HLP06.ID06 WHERE HLP07.ATTR07 = ". trim($_REQUEST['ID']);
            $res = odbc_prepare($this->_CONN, $sql);
            odbc_execute($res);
            $row1 = odbc_fetch_array($res);
            $fieldHTML .= "<input type='hidden' name='type' id='type' value='S'>";
            $fieldHTML .= "<label>Attribute Value</label><select name='option-sel' id='option-sel' class='form-control' style='width: 400px !important;' required><option value=''>Select the value</option>";
            while ($row = odbc_fetch_array($res)){
                $fieldHTML .= "<option value='". trim($row['ID07'])."'>" . trim($row['STXT07']) . "</option>";
            }
            $fieldHTML .= "</select>";
            $this->_RESULT['DATA'] = $fieldHTML;
            $this->_RESULT['CODE'] = 200;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully retrieved.";
        } else {
            $this->_RESULT['CODE'] = 200;
            $fieldHTML .= <<< HTML
            <input type='hidden' name='type' id='type' value='T'>
            <label>Attribute Value</label>
                <input type='text' id='option-val' name='option-val' value='' class='form-control' style='width: 414px !important; height: 30px !important;' required placeholder='Your value'>
HTML;
            $this->_RESULT['DATA'] = $fieldHTML;
            $this->_RESULT['RESPONSE'] = "Your record has been successfully retrieved.";
        }
               
        echo json_encode($this->_RESULT);
    }
    
}

$Method = isset($_REQUEST['method']) && $_REQUEST['method'] != "" ? trim($_REQUEST['method']) : "";

// echo "<pre>";
// print_r($_REQUEST);
// exit;

$Obj = new AjaxServices();
switch ($Method) {
    case 'add_sla':
        $Obj->addSLA();
        break;
    case 'edit_sla':
        $Obj->editSLA();
        break;
    case 'delete_sla':
        $Obj->deleteSLA();
        break;
    case 'de_attrib':
        $Obj->deUserAttrib();
        break;
    case 'add_attrib':
        $Obj->addUserAttrib();
        break;
    case 'edit_attrib':
        $Obj->editUserAttrib();
        break;
    case 'de_attrib_opt':
        $Obj->deAttribOpt();
        break;
    case 'add_attrib_opt':
        $Obj->addAttribOpt();
        break;
    case 'edit_attrib_opt':
        $Obj->editAttribOpt();
        break;
    case 'del_attrib':
        $Obj->delAttrib();
        break;
    case 'class_types':
        $Obj->ticketTypesBasedOnClass($_REQUEST['param']);
        break;
    case 'update_profile_attrib':
        $Obj->updateProfileAttrib();
        break;
    case 'add_profile_attrib':
        $Obj->addProfileAttrib();
        break;
    case 'get_options':
        $Obj->GetAttributeOptions();
        break;
    case 'class_static_types':
        $Obj->ticketStaticTypesBasedOnClass($_REQUEST['param']);
        break;
    case 'class_upload'://LP0029 - Mass Upload
        $Obj->ticketUploadTypesBasedOnClass($_REQUEST['id']);
        break;
        
    default:
        print_r($Obj->NoMethod());
        break;
}
?>