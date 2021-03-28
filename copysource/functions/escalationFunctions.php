<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            escalationFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related to LPS Ticket Escalations
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  
 **/

/**
 * Function calculates and returns an escalation date based on the number of hours ($subtractHours) retrieved in the call
 *
 * @param ineteger $subtractHours
 * @return integer $ago
 */
function get_escalation_date_time($subtractHours) {
    
    $weekday = "";
    while ( ($weekday == "Saturday" || $weekday == "Sunday") || $weekday == "" ) {
        $hoursAgo = mktime ( date ( "H" ) - $subtractHours, 0, 0, date ( "m" ), date ( "d" ), date ( "Y" ) );
        $ago ['date'] = strftime ( "%Y", $hoursAgo ) . strftime ( "%m", $hoursAgo ) . strftime ( "%d", $hoursAgo );
        $ago ['time'] = strftime ( "%H", $hoursAgo ) . date ( "i" ) . date ( "s" );
        
        $weekday = date ( 'l', strtotime ( $ago ['date'] ) );
        
        //echo $ago['date'] . "<hr>";
        //echo $ago['time'] . "<hr>";
        //echo $weekday . "<hr>";
        
        
        $subtractHours += 24;
    }
    
    //echo "<font color='red'>" . $weekday . "</font></hr>";
    
    
    return $ago;
    
}

/**
 * get_postpone_date_time
 * Adds hour to current date and time
 *
 * @param addhours int
 * @return array of future
 */
function get_postpone_date_time($addHours) {
    
    $weekday = "";
    while ( ($weekday == "Saturday" || $weekday == "Sunday") || $weekday == "" ) {
        $futureHours = mktime ( date ( "H" ) + $addHours, 0, 0, date ( "m" ), date ( "d" ), date ( "Y" ) );
        $future ['date'] = strftime ( "%Y", $futureHours ) . strftime ( "%m", $futureHours ) . strftime ( "%d", $futureHours );
        $future ['time'] = strftime ( "%H", $futureHours ) . date ( "i" ) . date ( "s" );
        
        $weekday = date ( 'l', strtotime ( $future ['date'] ) );
        $addHours += 24;
    }
    
    //echo "<font color='red'>" . $weekday . "</font></hr>";
    
    
    return $future;
    
}