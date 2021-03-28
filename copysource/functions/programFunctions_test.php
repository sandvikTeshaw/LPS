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


function getReferenceNumbersFromFile ($referenceFile, $numberOfReferences = 1){

    echo "referenceFile: " . $referenceFile . "<br>";
    echo "numberOfReferences: " . $numberOfReferences . "<br>";

    
    global $i_conn;

    echo "Connecting...<br>";

//    $i_conn = i5_pconnect ( "localhost", "PHPSMCUSR", "PHPSMCUSR" );
//    $i_conn = i5_pconnect ( "SEDAS5", "PHPSMCUSR", "PHPSMCUSR" );

    $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    $i_conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );

    echo "SYSTEM: " . SYSTEM . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "DB_PASS: " . DB_PASS . "<br>";
    echo "Options: " . $Options . "<br>";


    echo "Connected.<br>";

    
    $description = array (
        array ("Name"=>"p_RefType", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"10"),
        array ("Name"=>"p_RefNr",   "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"10.0"),
        array ("Name"=>"p_Numbers", "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"10.0")
    );

    echo "description: " . $description . "<br>";
    
    $hdlPgm = i5_program_prepare("CIL001", $description);

    echo "hdlPgm: " . $hdlPgm . "<br>";

    if(!$hdlPgm){
        return array(0,0);
    }

    echo "$hdlPgm: " . $hdlPgm . "<br>";

    
    for ($i = 1; $i <= 10; $i++){
        $parameterIn = array(
            "p_RefType"=>$referenceFile,
            "p_RefNr"=>0,
            "p_Numbers"=>$numberOfReferences
        );
        
        $parameterOut = array(
            "p_RefType"=>"p_RefType",
            "p_RefNr"=>"p_RefNr",
            "p_Numbers"=>"p_Numbers"
        );

    echo "$hdlPgm: " . $hdlPgm . "<br>";

        
        $ret = i5_program_call($hdlPgm, $parameterIn, $parameterOut);
        if(!$ret){
            return array(0,0);
        }
        
        extract(I5_OUTPUT());
        
        if ($p_RefNr == -1){
            return array(0,0);
        }
        if ($p_RefNr <> 0){
            return array ($p_RefNr, $p_Numbers);
        }
    }
    return array(0,0);
    
}








?>
