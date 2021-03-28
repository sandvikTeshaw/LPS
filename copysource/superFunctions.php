<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            functions.php<br>
 * Development Reference:   LP0004br>
 * Description:             This is the LPS Supervisor function file
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *	LP0004	   IS	  21/05/2016	New Functions for supervisor
 */

function get_super_reports_authority( $superId, $employeeArray, $conn, $superAuthority, $requester, $supCounter ){
    
    if( isset($superId) && $superId != "" && $superId != 0 ){
        if( !isset($supCounter) ){
            $supCounter = 1;
        }
        if( !isset($employeeArray) ){
            $employeeArray = array();
        }
        
        $supCounter++;
        
        if( $supCounter >= 100){
            return $superAuthority;
        }
    
        $sql = "SELECT ID05, AUTH05 FROM CIL31 T1"
             . " INNER JOIN HLP05 T2"
             . " ON T1.EMPL31 = T2.ID05"
             . " WHERE SUPR31 = $superId AND T2.DEL05 <> 'Y' AND EMPL31 <> $superId GROUP by ID05, AUTH05";
    
    
        //echo $sql;
    
        $res = odbc_prepare ( $conn, $sql );
        odbc_execute ( $res );
    

        while ( $row = odbc_fetch_array ( $res ) ) {
            
            //echo trim($row['ID05']) . "==".  trim($requester) . "<hr>";
            if( trim($row['ID05']) == trim($requester) ){
               $superAuthority['requester'] = true;
                }
            
            if( trim($row['AUTH05']) == 'P' ){
                $superAuthority['pfc'] = true;
            }else if( trim($row['AUTH05']) == 'L' ){
                $superAuthority['planner'] = true;
            }
            
            $drpSql ="";
            $drpSql = "select ACTM20 from CIL20 where ACTM20=" . $row['ID05'];
            
            //echo $drpSql;
            
            $drpRes = odbc_prepare ( $conn, $drpSql );
            odbc_execute ( $drpRes );
      
            
            while ( $drpRow = odbc_fetch_array ( $drpRes ) ) {
                
                $superAuthority['drp'] = true;
                
            }
            
    	
    		foreach ( $employeeArray as $checkEmp ){
               
    		    if( $checkEmp == trim($row['ID05']) ){
    		          $found = true;   
    		    }
    		}
    		
    		
    		array_push($employeeArray, trim($row['ID05']) );
    		
    		if( isset($found) && $found ){
        	
        		   
        		    
    		}else if( is_supervisor( $row['ID05'] ) ){
    
    			$superAuthority = get_super_reports_authority( $superId, $employeeArray, $conn, $superAuthority, $requester, $supCounter  );
    
    		}
    		
    		
    		
    		$found = false;
    	}
    }
	

	if( !isset($superAuthority['pfc'] ) || trim($superAuthority['pfc']) == "" ){
	    $superAuthority['pfc'] = false;
	}
	if( !isset($superAuthority['drp'] ) || trim($superAuthority['drp']) == "" ){
	    $superAuthority['drp'] = false;
	}
	if( !isset($superAuthority['planner'] ) || trim($superAuthority['planner']) == "" ){
	    $superAuthority['planner'] = false;
	}
	if( !isset($superAuthority['requester'] ) || trim($superAuthority['requester']) == "" ){
	    $superAuthority['requester'] = false;
	}

	return $superAuthority;

}
?>