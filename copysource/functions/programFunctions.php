<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            programFunctions.php<br>
 * Development Reference:   LP0027<br>
 * Description:             This is the LPS function file
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *   LP0027     KS    01/12/2017  LPS Unique ID creation
 */
/**
 */
require_once('../../../../IbmiToolkit/ToolkitApi/ToolkitService.php');
require_once('../../../../IbmiToolkit/ToolkitApi/CW/cw.php');

function getReferenceNumbersFromFile ($referenceFile, $numberOfReferences = 1){

    
    $db = SYSTEM;
    $user = DB_USER;
    $pass = DB_PASS;
    $extension = 'odbc';

    $ToolkitServiceObj = ToolkitService::getInstance( $db, $user, $pass, $extension );
    $ToolkitServiceObj->setToolkitServiceParams(array('stateless' => true));

    //AddParameterChar('in/out/both', size, 'comment', 'name', value);
    $param[] = $ToolkitServiceObj->AddParameterChar('both', 10, 'Reference File', 'p_RefType', $referenceFile);    
    $param[] = $ToolkitServiceObj->AddParameterPackDec('both', 10,0, 'Reference Number', 'p_RefNr', 0);
    $param[] = $ToolkitServiceObj->AddParameterPackDec('both', 10, 0, 'Number of Refrences', 'p_Numbers', $numberOfReferences);


    $result = $ToolkitServiceObj->PgmCall("CIL001", "", $param, null, null);

    // echo "<pre>";
    // print_r($result['io_param']);
    // echo "</pre>";

    if(!isset($result['io_param']))
        return array(0,0);
    else if($result['io_param']['p_RefNr'] == -1)
        return array(0,0);
    else if($result['io_param']['p_RefNr'] <> 0)
        return array($result['io_param']['p_RefNr'], $result['io_param']['p_Numbers']);
    else return array(0,0);
    
    
}

?>