<?php 

/**
 * System Name:             Logistics Process Support
 * Program Name:            view_CIL01OA.php<br>
 * Development Reference:   LP0042<br>
 * Description:             Show records CIL01OA<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *   LP0042     KS     14/06/2018   creation
 *
 */
/**
 */

include_once '../copysource/config.php';

global $conn;


echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title> CIL01OA </title>';
echo '</head>';
echo '<body>';


if (! $conn) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {
    
} else {
    echo "Connection Failed";
    die();
}





$ticketsSQL =  "select * ";                                                                                         
$ticketsSQL .= " from CIL01OA ";                                                                                      
$ticketsSQL .= " order by ID01, DATE01, TIME01 ";                                                                                 
 
$ticketsRes = odbc_prepare($conn, $ticketsSQL);                                                                      
odbc_execute($ticketsRes);                                                                                           

echo "<table border='1px' cellspacing='1px' cellpaddind='2'>";
echo "<tbody>";

echo "<tr>";
echo " <th>Ticket</th>";
echo " <th>Date</th>";
echo " <th>Time</th>";
echo " <th>From User</th>";
echo " <th>To User</th>";
echo " <th>Change Type</th>";
echo " <th>Audit User</th>";
echo "</tr>";

while ($ticketsRow = odbc_fetch_array($ticketsRes)){                                                                 
    echo "<tr>";                                                                                                
    echo " <td>" . $ticketsRow['ID01']   . " </td>";                                                                                                  
    echo " <td>" . $ticketsRow['DATE01'] . " </td>";
    echo " <td>" . $ticketsRow['TIME01'] . " </td>";
    echo " <td>" . $ticketsRow['FUSR01'] . " </td>";
    echo " <td>" . $ticketsRow['TUSR01'] . " </td>";
    echo " <td>" . $ticketsRow['CTYP01'] . " </td>";
    echo " <td>" . $ticketsRow['AUSR01'] . " </td>";
    echo "</tr>";                                                                                                   
}   


echo "</tbody>";
echo "</table>";  

echo '</body>';
echo '</html>';

?>