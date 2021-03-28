<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            s21DescriptionsFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related System21 Descriptions File
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  LP0041      KS    11/05/2018    Add Item Type, Class and Group Major to Related Information section in LPS Expedite tickets.
 *  
 **/

//D0129 - Function retrieves and returns market area based on market area ID
function get_market_area_name( $marketId ){
    global $CONO, $conn;
    
    $sql = "SELECT PRMD15"
        . " FROM DESC WHERE CONO15 = '$CONO' AND PRMT15 = 'CTRY' AND PSAR15 <> 'CTRY'"
        . " AND PSAR15 = '$marketId'";
        
        $res = odbc_prepare( $conn, $sql );
        odbc_execute ( $res );
        
        while( $row = odbc_fetch_array( $res ) ){
            return $row['PRMD15'];
        }
        
}



function getDescriptionINP15($cono15, $prmt15, $psar15, $lang = 'EN'){	    //**LP0041
    global $conn; 											                //**LP0041
                                                                            //**LP0041
    //**Default return:                                                     //**LP0041
    $PRMD15 = "";											                //**LP0041
                                                                            //**LP0041
    //**Description from INP15:                                             //**LP0041
    $sqlINP15 = "SELECT PRMD15 "      						                //**LP0041
    . " FROM INP15 "      						                            //**LP0041
    . " WHERE CONO15='" . trim($cono15) . "' "	                            //**LP0041
    . "   AND PRMT15='" . trim($prmt15) . "' "	                            //**LP0041
    . "   AND PSAR15='" . trim($psar15) . "' "; 	                        //**LP0041
    $resINP15 = odbc_prepare ($conn, $sqlINP15);			                    //**LP0041
    odbc_execute($resINP15);									                //**LP0041
    while ($rowINP15 = odbc_fetch_array($resINP15)){ 		                //**LP0041
        $PRMD15 = trim($rowINP15['PRMD15']);				                //**LP0041
    }														                //**LP0041
                                                                            //**LP0041
    //**Overridden by description from INP49 (language file):               //**LP0041
    $sqlINP49 = "SELECT LGDS49 "      						                //**LP0041
    . " FROM INP49 "      						                            //**LP0041
    . " WHERE CONO49='" . trim($cono15) . "' "	                            //**LP0041
    . "   AND FILE49='INP15' "                    	                        //**LP0041
    . "   AND FLNM49='PRMD15' "                    	                        //**LP0041
    . "   AND LANG49='" . trim($lang) . "' "	                            //**LP0041
    . "   AND SRCA49='" . trim($prmt15) . trim($psar15) . "' "; 	        //**LP0041
    $resINP49 = odbc_prepare ($conn, $sqlINP49);			                    //**LP0041
    odbc_execute($resINP49);									                //**LP0041
    while ($rowINP49 = odbc_fetch_array($resINP49)){ 		                //**LP0041
        $PRMD15 = trim($rowINP49['LGDS49']);				                //**LP0041
    }														                //**LP0041
                                                                            //**LP0041
    return $PRMD15;											                //**LP0041
}															                //**LP0041


function getVATC15($cono15, $prmt15, $psar15){                      	    //**LP0041
    global $conn; 											                //**LP0041
                                                                            //**LP0041
    //**Default return:                                                     //**LP0041
    $VATC15 = "";											                //**LP0041
                                                                            //**LP0041
    $sqlINP15 = "SELECT VATC15 "      						                //**LP0041
    . " FROM INP15 "      						                            //**LP0041
    . " WHERE CONO15='" . trim($cono15) . "' "	                            //**LP0041
    . "   AND PRMT15='" . trim($prmt15) . "' "	                            //**LP0041
    . "   AND PSAR15='" . trim($psar15) . "' "; 	                        //**LP0041
    $resINP15 = odbc_prepare ($conn, $sqlINP15);			                    //**LP0041
    odbc_execute($resINP15);									                //**LP0041
    while ($rowINP15 = odbc_fetch_array($resINP15)){ 		                //**LP0041
        $VATC15 = trim($rowINP15['VATC15']);				                //**LP0041
    }														                //**LP0041
                                                                            //**LP0041
    return $VATC15;											                //**LP0041
}															                //**LP0041

