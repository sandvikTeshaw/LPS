<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            tickets.php<br>
 * Development Reference:   DI868<br>
 * Description:             tickets.php displays ticket listing defined by the from paramter<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *   DI868H   TS      06/11/2008  Pricing Admin classification<br>
 *   D0301	  TS	  18/03/2011  Performance Issues
 *   D0455    TS      24/11/2011  Browser compatibility issue 
 *   D0270    TS	  02/05/2012  Supervisor enhancement
 *  LPS0004   IS      20/03/2016  
 * 	 LP0013	  IS	  21/05/2016	Cost Check - GLP Team Only authorization check
 *   LP0016   AG      11/12/2016    Outbound Planner to be added to all Global Process Support Ticket Types
 *   LP0050   KS      07/08/2018  Create new LPS ticket type �Inbound Parts Not Assembled�
 */
/**
 */

include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

set_time_limit ( 300 );

//D0301 - Added to compress output to remove all white space
ob_start("compressBuffer");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?
echo $SITE_TITLE;
?></title>
<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
</head>
<?

if (!isset($conn)) {
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}



include_once 'copysource/header.php';

if( !isset( $_SESSION['userID'] ) ){
    
    ?>
    <br><br><br><center>
    We seem to have encountered problem with your authority, please logout and try again.
    <?php   
    
    die();
    
}

$PHP_SELF = $_SERVER["PHP_SELF"];
//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );


	if( !$_SESSION ['classArray'] ){
	 	$_SESSION ['classArray'] = get_classification_array ();
	}
	if( !$_SESSION ['typeArray'] ){
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
//menuFrame ( $SITENAME );
include_once 'copysource/menu.php';

if (!isset($PRTY01) || $PRTY01 == "") {
    $PRTY01 = 1;
}

if( !isset( $_SESSION['userID'] ) ){
    
    ?>
    <br><br><br><center>
    We seem to have encountered problem with your authority, please logout and try again.
    <?php   
    
    die();
    
}

?>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
    <tr>
        <td>&nbsp;</td>
    </tr>
<?
//LP0013
if( !isset( $_REQUEST['CLAS09'] ) ){
    $_REQUEST['CLAS09'] = "";
}
$readAuth = getUserGroupAuth($_SESSION ['userID'],2,"READ");	// 2 Is the group id in CIL39
if(!$readAuth && $_REQUEST['CLAS09']==5 && $_REQUEST['type']==75){
	echo '<tr><TD class="title">You do not have access to this ticket listing, please contact your administrator if you require access</td></tr>';
	die();
}

if( !isset( $from ) ){
    $from = "";
}

if( !isset( $from ) && isset( $_REQUEST['from'])){
    $from = $_REQUEST['from'];
}

if( $from == "advancedSearch" ){
    if( isset($_REQUEST['classification'])){
        $CLAS09 = $_REQUEST['classification'];
    }
}
//D0310 - Removed extra calls not needs, can be done with Session arrays
//$classArray = get_array_values ( FACSLIB, "CIL09", "WHERE ID09=$CLAS09", "" );
//$typeArray = get_array_values ( FACSLIB, "CIL04", "WHERE ID04=$type", "" );

//D0301 - End Added to get names to eliminate above calls
if (is_array($_SESSION ['classArray']) || is_object($_SESSION ['classArray']))
{
    foreach ( $_SESSION ['classArray'] as $class ){
        if( isset($class['ID09']) && isset($CLAS09) && $class['ID09'] == $CLAS09 ){
    		$className = $class['CLAS09'];
    	}
    }
}

if( $from != "advancedSearch"  && isset( $CLAS09 ) && $CLAS09 != "" && $CLAS09 != 0 ){
    $x = 0;
    if (is_array($_SESSION ['typeArray']) || is_object($_SESSION ['typeArray']))
    {
        foreach ( $_SESSION ['typeArray'] as $types ){
          
            if( isset( $types[$CLAS09]['ID'][$x] ) && $types[$CLAS09]['ID'][$x] == $type ){
        		$typeName = $types[$CLAS09]['NAME'][$x];
        	}
        	$x++;
        }
    }
}
//LPS0004
echo '<tr><TD class="title"></td></tr>';

if ($from != "myTickets" && $from != "advancedSearch" && $from != "userTickets" && $from != "superreports") {
    echo "<tr><TD class='title'>" . $className. " - " . $typeName . "</td></tr>";
} elseif ($from == "advancedSearch") {
    
    $startDate = format_working_date ( $sYear, $sMonth, $sDay );
    $endDate = format_working_date ( $eYear, $eMonth, $eDay );
    
    echo "<tr><TD class='title'>Search Results</td></tr>";
} else {
	
    if ($from == "myTickets") {
        if (!isset($reassign) || $reassign != true) {
            echo "<tr><TD class='title'>My Tickets</td></tr>";
        } else {
            echo "<tr><TD class='title'>Re-Assign My Tickets</td></tr>";
        }
    } elseif ($from == "userTickets") {
        if ($stat == 1) {
            $chosenStatus = "Open";
        } else {
            $chosenStatus = "Resolved";
        }
        $listUserName = user_name_by_id ( $listUser );
        echo "<tr><TD class='title'>$listUserName $chosenStatus Tickets</td></tr>";
    }elseif ($from == "superreports") {
    	
        if ($eslv == 0) {
            $chosenStatus = "Open Tickets ( Not Escalated )";
        }elseif( $eslv == 1) {
            $chosenStatus = "Open Tickets ( Reminder Sent )";
        }elseif( $eslv == 2) {
            $chosenStatus = "Open Tickets ( Escalated )";
        }else{
        	$chosenStatus = "Open Tickets ( All )";
        }
        
        $listUserName = user_name_by_id ( $listUser );
        echo "<tr><TD class='title'>$listUserName - $chosenStatus</td></tr>";
    }
}

?>
</table>

<table width=95% cellpadding='0' cellspacing='0'>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <form action='<?
    echo $PHP_SELF;
    ?>' method='get'>
<?
//D0270 - Added superreports logic
if ($from != "advancedSearch" && $from != "myTickets" && $from != "userTickets" && $from != "superreports") {
    ?>
    <tr>
        <TD width=5%>Status:</td>
        <td><select name='stat'>
            <option
                <?
    if (isset($stat) && $stat == 1) {
        echo " SELECTED";
    }
    ?>
                value='1'>Open</option>
            <option
                <?
    if (isset($stat) == 5) {
        echo " SELECTED";
    }
    ?>
                value='5'>Resolved</option></td>
    </tr>
     
    <?
    //DI868H    
    if ($CLAS09 != 5) {
        ?>
    <tr>
        <TD width=5%>Priority:</td>
        <td><select name='PRTY01'>
            <!-- 
            <option 
            <?
        if ($PRTY01 == 5) {
            echo " SELECTED";
        }
        ?>
            value='5'>All Priorities</option>
    -->
            <?
        priority_short_select_box ( $PRTY01 );
        ?>
    </select> <input type='image'
            src='<?
        echo $IMG_DIR;
        ?>/go.gif' class='go'></td>
    </tr>
    
    <?
    } else {
        $PRTY01 = 3;
        
        ?>
<tr>
        <td><input type='hidden' name='PRTY01' value='3'> <input type='image'
            src='<?
        echo $IMG_DIR;
        ?>/go.gif' class='go'></td>
    </tr>
<?
    }

}
if( !isset($CLAS09) ){
    $CLAS09 = "";
}
if( !isset($type) ){
    $type = "";
}
if( !isset($from) ){
    $from = "";
}
if( !isset($orderBy) ){
    $orderBy = "";
}
if( !isset($whereClause) ){
    $whereClause = "";
}
if( !isset($listUser) ){
    $listUser = "";
}

?>

<input type='hidden' name=CLAS09 value='<?php echo trim( $CLAS09 );?>'>
    <input type='hidden' name=type value='<?php echo trim( $type );?>'>
    <input type='hidden' name=from value='<?php echo trim( $from );?>'>
    <input type='hidden' name=orderBy value='<?php echo trim( $orderBy );?>'>
    <input type='hidden' name=whereClause
        value=<?
        echo trim ( $whereClause );
        ?>>
    <input type='hidden' name=listUser value=<?
    echo trim ( $listUser );
    ?>>
    </form>
</table>
</center>

<?
//D0270 - Added superreports logic
if ($CLAS09 == "" && $from != "myTickets" && $from != "advancedSearch" && $from != "userTickets" && $from != "superreports") {
    die ();
} else {
}
//Add logic to check if the group is CostCheck-GLP Team only and user has authentication to see the ticket

//$sortedArray = sortArrayByField( $_SESSION['ticketArray'], "ID01", true );
if( isset( $_SESSION ['ticketArray'] )){
    $sortedArray = $_SESSION ['ticketArray'];
}
if( !isset( $startValue) ){
    $startValue = false;
}
echo "<center>";
echo "<table width=95% class='list'>";
echo $startValue;
echo "<form method='post' action='$PHP_SELF'>";

if ($startValue) {
    $whereClause .= " WHERE ID01 < $startValue";
}

if( !isset( $stat ) ){
    $stat = 1;
}

$userList = get_user_list ();
$ticketArray = list_tickets ( $stat, $whereClause, $CLAS09, $type, $PRTY01, $orderBy, $from, $listUser );
//LP0016 - Outbound Planner to be added to all Global Process Support Ticket Types
if ($from == "myTickets") {
    $resourcedTicketArray = list_tickets ( $stat, $whereClause, $CLAS09, $type, $PRTY01, $orderBy, "myResources", $listUser );
    $PlannerTicketArray = list_tickets ( $stat, $whereClause, 3, $type, $PRTY01, $orderBy, "myPlannerTickets", $listUser );

}
//LP0016 changes end here.
?>
<tr>
    <td>&nbsp;</td>
</tr>
<tr>
    <td><a href="listAssignedTickets.php?from=<?php echo $from;?>&userSelect=<?php echo $listUser;?>&stat=<?php echo $stat;?>&eslv=<?php echo $eslv;?>">Re-assign Tickets</a></td>
</tr>
<tr>
    <td>&nbsp;</td>
</tr>
<tr class='headerBlue'>
<td class='headerWhite'>ID</td>
<td class='headerWhite' width=50%>Short Description</td>
<td class='headerWhite'>Created</td>
<td class='headerWhite'>Updated</td>
<td class='headerWhite'>Priority</td>
<td class='headerWhite'>Requester</td>
<td class='headerWhite'>Last User</td>
<?php 

if ( isset($_REQUEST['type']) && $_REQUEST['type']==118){                                                                //**LP0050
    echo "<td class='headerWhite'>Supplier</td>";                                           //**LP0050
}                                                                                           //**LP0050

if( !isset($reassign) ){
    $reassign = false;
}
if ($reassign == true) {
    echo "<TD class='headerWhite'>Resource</td>";
    echo "<form method='post' action='reassignIssues.php'>";
}
echo "</tr>";

if ($reassign == true) {
    echo "</form>";
    echo "<form method='post' action='reassign.php'>";
}
//Calls array to sort correctly
$rowFlag = 0;

if( is_array($ticketArray) || is_object( $ticketArray ))
{
    foreach ( $ticketArray as $sArray ) {
        $rowFlag ++;
        $makeBold = "";
        $endBold = "";
        if ($_SESSION ['userID'] == $sArray ['UPID01'] || ($from == "myTickets" && $stat == 5) || $from != "myTickets") {
            if ($rowFlag % 2) {
                echo "<tr>";
            } else {
                echo "<TR class='alternate'>";
            }
        } else {
            echo "<TR class='red'>";
            $makeBold = "<b>";
            $endBold = "</b>";
        }
        //D0455 - Added from to the parameters of the href
        echo "<TD class='paddedBorder'>$makeBold<a href='showTicketDetails.php?EMAIL05=" . $_SESSION ['email'];
        echo "&ID01=" . $sArray['ID01'] . "&CLAS01=" . $CLAS09 . "&TYPE01=" . $type . "&from=" . $from . "'>" . $sArray['ID01'] . "</a>$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . trim ( $sArray['DESC01'] ) . "$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . trim ( formatDate ( $sArray['DATE01'] ) ) . "$endBold</td>";
        if ($sArray['UDAT01'] != 0) {
            echo "<TD class='paddedBorder'>$makeBold" . trim ( formatDate ( $sArray['UDAT01'] ) ) . "$endBold</td>";
        } else {
            echo "<TD class='paddedBorder'>&nbsp</td>";
        }
        echo "<TD class='centerBorder'>$makeBold" . trim ( get_priority ( $sArray['PRTY01'], "letter" ) ) . "$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . showUserFromArray ( $userList, $sArray['RQID01'] ). "$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . showUserFromArray ( $userList, $sArray['UPID01'] ) . "$endBold</td>";
        
        if ( isset($_REQUEST['type'])==118){                                                                                                //**LP0050
            $supplier = "";                                                                                                         //**LP0050  
            $sqlSupplier = " select TEXT10 ";                                                                                       //**LP0050
            $sqlSupplier .= " from CIL10J01 ";                                                                                      //**LP0050
            $sqlSupplier .= " where CAID10 = " . $sArray['ID01'] . " ";                                                             //**LP0050
            $sqlSupplier .= "   and NAME07 = 'Supplier Name' ";                                                                     //**LP0050
            $resSupplier = odbc_prepare($conn, $sqlSupplier);                                                                        //**LP0050
            odbc_execute($resSupplier);                                                                                              //**LP0050
            while($rowSupplier = odbc_fetch_array($resSupplier)){                                                                    //**LP0050
                $supplier = $rowSupplier['TEXT10'];                                                                                 //**LP0050
            }                                                                                                                       //**LP0050
            echo "<TD class='paddedBorder'>" . $makeBold . $supplier . $endBold . "</td>";                                          //**LP0050
        }                                                                                                                           //**LP0050
        
        if ($reassign == true) {
            echo "<TD class='paddedBorder'><select name='resource_" . $sArray['ID01'] . "'>";
            if (is_array($userList) || is_object($userList)){
                foreach ( $userList as $listUsers ) {
                    echo "<option ";
                    if ($_SESSION ['userID'] == $listUsers ['ID05']) {
                        echo "SELECTED ";
                    }
                    echo "value='" . trim ( $listUsers ['ID05'] ) . "'>" . trim ( $listUsers ['NAME05'] ) . "</option>";
                }
            }
            echo "</select></td>";
        }
        echo "</tr>";
        $startValue = $sArray['ID01'];
    }
}

if ($rowFlag == 0) {
    echo "<tr><td>&nbsp</td></tr>";
    echo "<tr><TD class='title' colspan='6'>No Tickets Available</td></tr>";
} else {
    if ($reassign == true) {
        echo "<tr><td>&nbsp</td></tr>";
        $_SESSION ['ticketArray'] = $ticketArray;
        echo "<tr><TD colspan='3'><input type='submit' value='Re Assign'></td></tr>";
    }
}

//Calls array to sort correctly
$rowFlag = 0;
echo "<tr><td colspan='7'>";
echo "<table width=100% class='list'>";

if ( isset( $resourcedTicketArray )&& (is_array($resourcedTicketArray) || is_object($resourcedTicketArray))){
    foreach ( $resourcedTicketArray as $sArray ) {
        
        if ($rowFlag == 0) {
            echo "<tr><td>&nbsp;</td></tr>";
            echo "<tr>";
            echo "</tr>";
            echo "<TR class='headerBlue'>";
            echo "<TD class='headerWhite' colspan='8'>My Resourced Tickets</td>";
            echo "</tr>";
            echo "<tr>";
            echo "</tr>";
            echo "<TR class='headerBlue'>";
            echo "<TD class='headerWhite'>ID</td>";
            echo "<TD class='headerWhite' width=50%>Short Description</td>";
            echo "<TD class='headerWhite'>Created</td>";
            echo "<TD class='headerWhite'>Updated</td>";
            echo "<TD class='headerWhite'>Priority</td>";
            echo "<TD class='headerWhite'>Requester</td>";
            echo "<TD class='headerWhite'>Last User</td>";
            echo "<TD class='headerWhite'>Owner</td>";
            echo "</tr>";
        }
        $rowFlag ++;
        $makeBold = "";
        $endBold = "";
        
        if ($rowFlag % 2) {
            echo "<tr>";
        } else {
            echo "<TR class='alternate'>";
        }
        
        echo "<TD class='paddedBorder'>$makeBold<a href='showTicketDetails.php?EMAIL05=" . $_SESSION ['email'];
        echo "&ID01=" . $sArray['ID01'] . "&CLAS01=" . $CLAS09 . "&TYPE01=" . $type . "'>" . $sArray['ID01'] . "</a>$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . trim ( $sArray['DESC01'] ) . "$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . trim ( formatDate ( $sArray['DATE01'] ) ) . "$endBold</td>";
        if ($sArray['UDAT01'] != 0) {
            echo "<TD class='paddedBorder'>$makeBold" . trim ( formatDate ( $sArray['UDAT01'] ) ) . "$endBold</td>";
        } else {
            echo "<TD class='paddedBorder'>&nbsp</td>";
        }
        echo "<TD class='centerBorder'>$makeBold" . trim ( get_priority ( $sArray['PRTY01'], "letter" ) ) . "$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . showUserFromArray ( $userList, $sArray['RQID01'] ) . "$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . showUserFromArray ( $userList, $sArray['UPID01'] ). "$endBold</td>";
        echo "<TD class='paddedBorder'>$makeBold" . showUserFromArray ( $userList, $sArray['OWNR01'] ) . "$endBold</td>";
        echo "</tr>";
        $startValue = $sArray['ID01'];
    
    }
}
echo "</table>";
echo "</td></tr>";


if ($reassign == true) {
    echo "</form>";
}

echo "</table>";
if( !isset( $prev_class )){
    $prev_class = "";
}
echo "<input type='hidden' name='prev_class' value='" . trim ( $prev_class ) . "'>";
echo "<input type='hidden' name='startValue' value='" . trim ( $startValue ) . "'>";
echo "</form>";
page_footer ( "main" );

//D0301 - Added to output buffer
ob_flush();
?>