<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            staticDefineFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to static values for LPS System
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *	LP0035      KS    31/01/2018    GLBAU-14397 LPS - Updated User Maintenance Screen View
 *  LP0050      KS    01/08/2018    Create new LPS ticket type “Inbound Parts Not Assembled”
 *  
 **/

/**
 * Function returns name of status
 *
 * @param integer $statID
 * @return sting of Status name
 */
function get_status_name($statID) {
    
    switch ($statID) {
        case 1 :
            return "Open";
            //LP0020 - Start Change
        case 2 :
            return "PFC Complete";
        case 3 :
            return "DRP Complete";
        case 4 :
            return "Complete";
            //LP0020 - End Change
        case 5 :
            return "Resolved";
    }
    return "None";
}

/**
 *
 * Sets array of system authority levels
 *
 * Receives no parametes
 * Returns multi dimensional array of authority levels, code and description
 */
function authority_array() {
    
    //**LP0035 $authorityArray [] = "";
    $authorityArray = [];   //**LP0035
    
    $authorityArray [0] ['value'] = "E";
    $authorityArray [0] ['description'] = "Everyone/Requester";
    $authorityArray [1] ['value'] = "L";
    $authorityArray [1] ['description'] = "Logistics";
    $authorityArray [2] ['value'] = "P";
    $authorityArray [2] ['description'] = "PFC";
    $authorityArray [3] ['value'] = "R";
    $authorityArray [3] ['description'] = "Regional";
    $authorityArray [4] ['value'] = "S";
    $authorityArray [4] ['description'] = "System Administrator";
    
    return $authorityArray;
    
}


/* LP0019 - Function for returning priority list is added
 * @param $id       id = 0 will return array of priorities list else the specific priority
 *
 */
function priority_short_list($id){
    $priorities_list = array("A (Unit Down)", "B (Customer Order)", "C (Inventory)", "D (Scheduled)");
    array_push($priorities_list,"E (Project)");                                                     //**LP0050
    if($id == 0) return $priorities_list;
    else if ($id == 1) return "A (Unit Down)";
    else if ($id == 2) return "B (Customer Order)";
    else if ($id == 3) return "C (Inventory)";
    else if ($id == 4) return "D (Scheduled)";
    else if ($id == 5) return "E (Project)";                                                        //**LP0050
    else return "No Priority";
}

