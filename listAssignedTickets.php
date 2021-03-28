<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			superreports.php<br>
 * Development Reference:	D0270<br>
 * Description:				list tickets of a supervisors reports<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  	LP0004	IS		30/3/2016	
 */
/**
 */

include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

if (! $conn) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS);
}

if ($conn) {

} else {
	echo "Connection Failed";
}
/*
 * TODO fix this later
 * 
 */
if ($_SESSION ['email']) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	
	if (! $_COOKIE ["mtp"]) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif ($_COOKIE ["mtp"]) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
</head>

<?php 
include_once 'copysource/header.php';

$useragent = $_SERVER['HTTP_USER_AGENT'];

if ($_SESSION ['userID']) {
    
    if (! $_SESSION ['classArray'] ) {
        $_SESSION ['classArray'] = get_classification_array ();
    }
    if( ! $_SESSION ['typeArray']){
        $_SESSION ['typeArray'] = get_typeName_array ();
    }
    
    include_once 'copysource/menu.php';
}

if( $from != "maint" && $from != "superreports"){
	$titleMy = "My";
	$title = "";
	
	if( $_REQUEST['action'] == "owned" || !$_REQUEST['action'] ){
	    $titleMy .= " Owned";
	}else{
	    $titleMy .= " Request";
	}
	
}elseif ( $_REQUEST['userSelect'] != 0 && $_REQUEST['userSelect']!= "" ){

	$assignee = trim(user_name_by_id($_REQUEST['userSelect']));

	$title = "For " . $assignee;
	$titleMy = "";
}


?>


<center>
<table width=90% cellpadding=0 cellspacing=0>
<?php // LP0004 added two links for Request Tickets and Owned Tickets ?>
<form method='post' action='reassign.php'>
<?php
if( $_REQUEST['action'] == "owned" || !$_REQUEST['action'] ){
	echo '<input type="hidden" name="selection" value="owned"/>';
	   
	}else{    	
    	
	echo '<input type="hidden" name="selection" value="requests"/>';		
    }
    
    
if( $_REQUEST['userSelect'] ){
		
	$ticketUser = $_REQUEST['userSelect'];
	
}else{
	
	$ticketUser = $_SESSION ['userID'];
	
}
?>
	<tr>
	<td>&nbsp;</td>
	</tr>
	
	<tr>
	    <td>
            <table width="50%" border="0">
              <tr>
                <td><a href="listAssignedTickets.php?action=request&userSelect=<?php echo $ticketUser?>&from=<?php echo $from;?>">Request Tickets</a></td>
                <td><a href="listAssignedTickets.php?action=owned&userSelect=<?php echo $ticketUser?>&from=<?php echo $from;?>">Owned Tickets</a></td>
              </tr>
            </table>
		</td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	</tr>
	<tr>
            <td class='title' colspan='6'>Re-Assign <?php echo $titleMy;?> Tickets <?php echo $title;?></td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	</tr>
	<?php 
	if($_SESSION ['authority'] == "S" ){
	   $userArray = get_user_list ();
	}else{
	    $employeeArray = array();
	    $userArray = array();
	    array_push($employeeArray, $_SESSION ['userID'] );
	    
	    $userArray = get_super_user_list ( $_SESSION ['userID'], $employeeArray, $userArray );
	}
	if( $from == "maint" ){
		
	?>
	<tr>
		<td><b>Select User:</b>
			<select name='userSelect' onchange="document.location.href = 'listAssignedTickets.php?from=maint&userSelect=' + this.value">
			<option value=''>Select User</option>
			
		<?php
		if( $_SESSION ['authority'] != "S" ){
		    ?><option value='<?php echo $_SESSION ['userID'];?>'><?php echo trim($_SESSION ['name'])?></option><?php 
		}
		foreach ( $userArray as $users ) {
            echo "<option ";
            if ($users ['ID05'] == $_REQUEST['userSelect']) {
                echo "SELECTED ";
            }
            echo "value='" . trim ( $users ['ID05'] ) . "'>" . trim ( $users ['NAME05'] ) . "</option>";
        }
        ?>
        </select></td>
    </tr>
     <?php 
    if( !$_REQUEST['userSelect'] ){
    	die();	
    }
    ?>
    <tr><td>&nbsp;</td></tr>
    
    <?php
	
	}
	
	//LP0004 change new resource to new owner/new requester
	if( $_REQUEST['action'] == "owned" || !$_REQUEST['action']){
		
		$titleUser = "New Owner";
		
	}else{
		
		$titleUser = "New Requester";
		
	}
	?>
	<tr>
		<td><b><?php echo $titleUser; ?></b>
			<select name='newResource'>
			<option value=''>Select User</option>
		<?php 
		if( $_SESSION ['authority'] != "S" ){
		    ?><option value='<?php echo $_SESSION ['userID'];?>'><?php echo trim($_SESSION ['name'])?></option><?php
		}
		foreach ( $userArray as $users ) {
            echo "<option ";
            if ($users ['ID05'] == $_REQUEST['newResource']) {
                echo "SELECTED ";
            }
            echo "value='" . trim ( $users ['ID05'] ) . "'>" . trim ( $users ['NAME05'] ) . "</option>";
        }
        ?>
        </select></td>
    </tr>
	<tr><td>&nbsp;</td></tr>
    <tr class='headerBlue'>
        <td class='headerWhite'>ID</td>
        <td class='headerWhite' nowrap>Short Description</td>
        <td class='headerWhite'>Assign to <br/>Resource</td>
    </tr>
	<?php 
	
	
	
	//LP0004
	if( $_REQUEST['action'] == "owned" || !$_REQUEST['action']){
	    
	   $whereClause = " WHERE STAT01 = 1 AND OWNR01 = $ticketUser";
	   
	}else{


    	$whereClause = " WHERE STAT01 = 1 AND RQID01 = $ticketUser";
    	
    }
	
	
	$ticketArray = basic_ticket_list( $whereClause );
	
	$ticksList = "";
	foreach ( $ticketArray as $ticks ){
		
		?><tr>
			<td class='paddedBorder'><a href='showTicketDetails.php?EMAIL05=<?php echo $_SESSION ['email'];?>&ID01=<?php echo $ticks['ID01'];?>'><?php echo $ticks['ID01'];?></a></td>
			<td class='paddedBorder'><?php echo $ticks['DESC01'];?></td>
			<td class='paddedBorder'><input type='checkbox' name='chk_<?php echo $ticks['ID01']?>' class='small'/></td>
		  </tr>
		<?php 
		$ticksList .= $ticks['ID01'] . ",";
	}	
	?>
	<tr>
		<td>
			<input type='hidden' name='ticketList' value='<?php echo substr( $ticksList, 0, -1 );?>'/>
			<input type='hidden' name='ticketUser' value='<?php echo $ticketUser;?>'/>
			<input type='submit' name='frm_submit' value='Continue'/>
		</td>
	</tr>
	</form>
	</table>
	</center>
	</html>