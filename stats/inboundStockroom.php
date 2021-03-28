<?php
include '../../common/copysource/global_functions.php';
include '../copysource/config.php';
if( $_POST['action'] == "" ){
	?>
	<body vlink='blue'>
	<center>
	<form method='post' action='inboundStockroom.php'>
	<table width=60%>
	   <tr>
            <td align='center'><b>
            Inbound Issues</b></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
	   <tr>
	       <td>Start Date:
	       <td><?select_date_listing("syear", "smonth", "sday", $syear, $smonth, $sday)?></td>
	   </tr>
	   <tr>
           <td>End Date:
           <td><?select_date_listing("eyear", "emonth", "eday", $eyear, $emonth, $eday)?></td>
       </tr>
       <tr>
            <td>
            <input type='hidden' name='action' value='continue'>
            <input type='submit' value='continue'>
            </td>
       </tr>
	</table>   
	</form>
	</center>
    <? 
	
	
}else{
	
	$startDate = $syear;
    if( strlen( $smonth ) == 1  ){
    	$startDate .= "0".$smonth;
    }else{
    	$startDate .= $smonth;
    }
    if( strlen( $sday ) == 1  ){
        $startDate .= "0".$sday;
    }else{
        $startDate .= $sday;
    }
 
    $endDate = $eyear;
    if( strlen( $emonth ) == 1  ){
        $endDate .= "0".$emonth;
    }else{
        $endDate .= $emonth;
    }
    if( strlen( $eday ) == 1  ){
        $endDate .= "0".$eday;
    }else{
        $endDate .= $eday;
    }

    if( !$conn ){
        $conn = odbc_connect( SYSTEM, DB_USER, DB_PASS );
    }
    
    if( !$conn ){
    	echo "Connection Failed";
    }
    
    //initialize arrays for storing attribute IDs
    $attrArray = array();
    $prntAttrArray = array();
    
    $attrSql = "SELECT ATTR07 FROM " . FACSLIB . ".CIL07 WHERE NAME07 = 'Receiving Stockroom'";
    $resAttr = odbc_prepare( $conn, $attrSql );
    odbc_execute( $resAttr );
   
    $attrClause = "WHERE ";
    $rCount = 0;
    while( $attrRow = odbc_fetch_array( $resAttr ) ){
    	
    	if( $rCount != 0 ){
    		$attrClause .= " OR ";
   	 	}
   	 	$attrClause .= "PRNT07=${attrRow['ATTR07']}"; 
    	$rCount++;
    }
    
	$attrPrntSql = "SELECT ATTR07, NAME07, PRNT07 FROM " . FACSLIB . ".CIL07 $attrClause ORDER BY NAME07, ATTR07";
	
   	$resPrntAttr = odbc_prepare( $conn, $attrPrntSql );
   	odbc_execute( $resPrntAttr );
   	
   	$orClauseArray = array();
   	$stNameArray = array();
   	$prevStName = "";
   	$first = true;
   	while( $attrPrntRow = odbc_fetch_array( $resPrntAttr ) ){
   		
   		if( $prevStName != $attrPrntRow['NAME07'] ){
   			array_push( $stNameArray, $attrPrntRow['NAME07'] );
   			
   			if( !$first ){
   				$currentOrClause .= ")";
   				array_push( $orClauseArray, $currentOrClause );	
   			}else{
   				$first = false;	
   			}
   			
   			$prevStName = $attrPrntRow['NAME07'];
   			$currentOrClause = " AND ( ";
   			$orClauseCount = 0;
   		}
   		
   		if( $orClauseCount != 0 ){
   			$currentOrClause .= " OR ";
   		}
   		
   		$currentOrClause .= "(ATTR10 = " . $attrPrntRow['PRNT07'] . " AND TEXT10 = '${attrPrntRow['ATTR07']}')";
   		$orClauseCount++;
   	}
   	
   	
   	?>
   	
   	<center>
    <table width=60%>
        <tr>
            <td colspan='2' align='center'><b>
            Inbound Issues <?echo get_long_display_date( $syear, $smonth, $sday );?> - <?echo get_long_display_date( $eyear, $emonth, $eday );?>
            </b></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
	    <tr>
	        <th><u>Stockroom</u></th>
	        <th><u># of Issues Opened</u></th>
	    </tr>
   
   	<?php 	
   	$currentOrClause .= ")";
   	array_push( $orClauseArray, $currentOrClause );	
   	
   	$nameCount = 0;
   	foreach ($orClauseArray as $oClause) {
   		//echo $stNameArray[$nameCount] . "--->" . $oClause . "<hr>";
   		
   		
   		$sql = "SELECT count(LINE10) FROM " . FACSLIB . ".CIL10 T1"
   			 . " INNER JOIN " . FACSLIB . ".CIL01 T2"
   			 . " ON T1.CAID10  = T2.ID01"
   			 . " WHERE T2.DATE01 >= $startDate AND T2.DATE01 <= $endDate"
   			 . $oClause;
   			
   			 
   			$res = odbc_prepare( $conn, $sql );
   			odbc_execute( $res );
   			while( $row = odbc_fetch_array( $res ) ){
	   			echo "<tr>";
	    	  		echo "<td align='center'>" . $stNameArray[$nameCount] . "</td>";
	    	   		echo "<td align='center'>" . $row['00001'] . "</td>";
	    		echo "</tr>";
   			}
   			$nameCount++;
   	}
   	
    ?>
	</table>
	</center>
	<?
}


