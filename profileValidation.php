<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            profileValidation.php<br>
 * Development Reference:   D0247<br>
 * Description:             LPS Profile Validation Page
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  D0247 	   TS	  25/02/2011  Link LPS User profile to Notification<br>
 * 
 */
/**
 */

        $userInfo = user_cookie_info( $_SESSION['email'] );
        $userArray = get_user_list();
        
        	
	        echo "<center>"; 
	        
	        ?><form method='post' name='profileForm' action='profileValidationResponse.php' onsubmit="javascript:return profileValidation()"><?
	        echo "<table width=50% cellpadding='0' cellspacing='0' border=0>";
	        echo "<TR><TD>&nbsp</TD></TR>";
	        echo "<TR><TD>&nbsp</TD></TR>";
	        echo "<tr>";
	            echo "<td class='titleBig' colspan='3'>Profile Validation</td>";
	        echo "</tr>";
	        echo "<TR>";
	            echo "<TD colspan='3'><hr></td>";
	        echo "</TR>";
	        echo "<TR><TD>&nbsp</TD></TR>";
	        echo "<TR><TD>&nbsp</TD></TR>";
	        echo "<TR>";
	        echo "<TD>&nbsp</TD>";
	        echo "<TD class='bold'><font color='red'>*</font>Name:</TD>";
	        echo "<TD><input type='text' name='name' id='name' value='" . trim($userInfo['NAME05']) . "' size='75'></TD>";
	        echo "</TR>";
	        echo "<TR>";
	        echo "<TD>&nbsp</TD>";
	        echo "<TD class='bold'><font color='red'>*</font>Company Code:</TD>";
	        echo "<TD>";
	            echo "<select name='code' class='long'>";
	                list_company_code( $userInfo['CODE05'] );
	            echo "</select>";
	        echo "</TD>";
	        echo "</TR>";
	        echo "<TR>";
	        echo "<TD>&nbsp</TD>";
	        echo "<TD class='bold'><font color='red'>*</font>Availability:</TD>";
	        echo "<TD>";
	            echo "<select name='availability'>";
	                list_availability( trim($userInfo['AVAL05']) );
	            echo "</select>";
	        echo "</TD>";
	        echo "</TR>";
	        echo "<TR>";
	        echo "<TD>&nbsp</TD>";
	        echo "<TD class='bold'><font color='red'>*</font>Back-up:</TD>";
	        echo "<TD>";
	            echo "<select name='backup' id='backup' class='long'>";
	                echo "<option ";
	        if (! trim($userInfo['BACK05'])) {
	            echo "SELECTED ";
	        }
	        echo "value='0'>Select name</option>";
	        foreach ( $userArray as $users ) {
	            if (trim ( $users ['AVAL05'] ) == "N") {
	                echo "<option class='red' ";
	                if ($userInfo ['BACK05'] == $users ['ID05']) {
	                    echo "SELECTED ";
	                }
	                echo "value='" . $users ['ID05'] . "'";
	                echo ">" . trim ( $users ['NAME05'] );
	                echo " - Out of Office";
	                
	            }else{
	                echo "<option ";
	                if ($userInfo ['BACK05'] == $users ['ID05']) {
	                    echo "SELECTED ";
	                }
	                echo "value='" . trim ( $users ['ID05'] ) . "'";
	                echo ">" . trim ( $users ['NAME05'] );
	            }
	            echo "</option>";
	        }
	        echo "</select>";
	        echo "</TD>";
	        echo "</TR>";
	        
	        $superId = get_supervisor_id( $_SESSION['userID']);	//D0247 - Added to populate current supervisor
	        ?>
	        <tr>
	        	<td>&nbsp;</td>
				<td class='bold'><font color='red'>*</font>Supervisor:</td>
				<td>
				<select name='super' id='super' class='long'>
					<?php show_user_list($userArray, $superId );?>
				</select>
				</td>
			</tr>
	        <TR><TD>&nbsp;</TD></TR>
	        <input type='hidden' name='action' value='saveProfile'>
	        <TR><TD>&nbsp;</TD><TD><input type='submit' value='Save Profile' onclick="return profileValidation()"></TD></TR>
	        
	        </table>
	        </form>
	        </center>

        