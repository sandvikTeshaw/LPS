<?php 
/**
 * System Name:             Logistics Process Support
 * Program Name:            supervisorFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing supervisor related functions
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification 
 *  
 **/

function getSupervisorAuthority($reportId, $currentUserID ){
    global $CONO, $conn, $IMG_DIR;
    $reportInfo = user_info_by_id($reportId);
    $superPlanner = false;
    $superPfc = false;
    $superDrp = false;
    
    $sql ="";
    $sql = "select ID38 from CIL38 where ID38='".$reportId."'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $row = odbc_fetch_array ( $res );
    if(length($row)>0){
        $superPlanner = true;
    }else{
        $superPlanner = false;
    }
    
    $sql ="";
    $sql = "select ACTM13 from CIL13 where ACTM13='".$reportId."'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $row = odbc_fetch_array ( $res );
    if(length($row)>0){
        $superPlanner = true;
    }else{
        $superPlanner = false;
    }
    
    $sql ="";
    $sql = "select PFC2X from CIL20x where PFC2X='".$reportId."'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $row = odbc_fetch_array ( $res );
    if(length($row)>0){
        $superPfc = true;
    }else{
        $superPfc = false;
    }
    
    $sql ="";
    $sql = "select ACTM20 from CIL20 where ACTM20='".$reportId."'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    $row = odbc_fetch_array ( $res );
    if(length($row)>0){
        $superDrp = true;
    }else{
        $superDrp = false;
    }
    
}

//D0270 - Supervisor reports enhancement
//This function will create an array of employees in linked to a supervisor
function get_super_reports( $superId, $rowFlag, $employeeArray ){
    global $conn;
    
    $sql = "SELECT EMPL31, T2.NAME05 as NAME_1, T3.NAME05 as NAME_2 FROM CIL31 T1"
        . " INNER JOIN HLP05 T2"
            . " ON T1.EMPL31 = T2.ID05"
                . " INNER JOIN HLP05 T3"
                    . " ON T1.SUPR31 = T3.ID05"
                        . " WHERE SUPR31 = $superId AND T2.DEL05 <> 'Y' AND EMPL31 <> $superId GROUP by EMPL31, T2.NAME05, T3.NAME05";
                        
                        
                        $res = odbc_prepare ( $conn, $sql );
                        odbc_execute ( $res );
                        
                        //$employeeArray = "";
                        //$employeeListArray = array();
                        
                        while ( $row = odbc_fetch_array ( $res ) ) {

                            $rowFlag ++;
                            if ($rowFlag % 2) {
                                ?><tr><?php
		} else {
			?><tr class='alternate'><?php
		}

		$countOpen = get_issue_count( $row['EMPL31'], 0 );
		$countReminders = get_issue_count( $row['EMPL31'], 1 );
		$countEscalation = get_issue_count( $row['EMPL31'], 2 );
		$totalCount = $countOpen + $countReminders + $countEscalation;
		?>
			<td><?php echo $row['NAME_1'];?></td>
			<td><?php echo $row['NAME_2'];?></td>
			<td class='center'><a href='tickets.php?listUser=<?php echo $row['EMPL31'];?>&from=superreports&stat=1&eslv=0'><?php echo $countOpen;?></a></td>
			<td class='center'><a href='tickets.php?listUser=<?php echo $row['EMPL31'];?>&from=superreports&stat=1&eslv=1'><?php echo $countReminders;?></a></td>
			<td class='center'><a href='tickets.php?listUser=<?php echo $row['EMPL31'];?>&from=superreports&stat=1&eslv=2'><?php echo $countEscalation;?></a></td>
			<td class='center'><a href='tickets.php?listUser=<?php echo $row['EMPL31'];?>&from=superreports&stat=1&eslv=all'><?php echo $totalCount;?></a></td>
		</tr>
		<?php
		
	
		foreach ( $employeeArray as $checkEmp ){
           
		    if( $checkEmp == trim($row['EMPL31']) ){
		          $found = true;   
		    }
		}
		
		
		if( isset( $found ) &&  $found  == true ){

		}else if( is_supervisor( $row['EMPL31'] ) ){
		    //echo $row[0] . "<hr>";
			//$littleListArray = array();
		    $rowFlag = get_super_reports( $row['EMPL31'], $rowFlag, $employeeArray  );

		}
		array_push($employeeArray, trim($row['EMPL31']) );
		
		$found = false;
	}
	

	return $rowFlag;

}

