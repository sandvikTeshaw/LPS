<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			advancedSearch.php<br>
 * Development Reference:	DI868<br>
 * Description:				Allows users to search for tickets with advance search criteria<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0128     TS      06/07/2010  Advanced Seach removal of elements
 *  D0301 		TS	   24/03/2011   Change Header and Menu calls
 */
/**
 */
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

if (!isset($conn)) {
	// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_connect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if (isset($conn)) {

} else {
	echo "Connection Failed";
}

if (isset($email)) {
	
	$userInfo [] = "";
	$userInfo = userInfo ( $email, $password );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	$_SESSION ['email'] = $email;
	$_SESSION ['password'] = $password;
	
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}
} elseif (isset($_SESSION ['email'])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	
	if (!isset($_COOKIE ["mtp"]) ) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif (isset($_COOKIE ["mtp"])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['authority'] = $userInfo ['AUTH05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
} else {

}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?echo $SITE_TITLE;?></title>
<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
<!-- D0128 Removed missign elements flag for classification -->
<script type="text/javascript">
function validateSubmit(){

	var missingInputs = "";
    if( this.detailsForm.source.value == "" || this.detailsForm.keywords.value == ""  ){
    
    	missingInputs = "Missing Required Fields\n";
    	
    	if( this.detailsForm.source.value == "" ){
    		missingInputs = "Source\n" + missingInputs;
    	}
    	if( this.detailsForm.keywords.value == "" ){
    		missingInputs = "Description / Part Number\n" + missingInputs;
    	}
    	
    	alert( missingInputs );
    	return false;
    }else{
    	return true;
    }   
}
</script>
</head>

<?

//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );

//D0301 - Change Header
include_once 'copysource/header.php';
echo "<body>";

if (isset($_SESSION['userID'] )) {
	
	if (!isset($_SESSION['classArray'])) {
		$_SESSION['classArray'] = get_classification_array ();
	}
	if( !isset( $_SESSION['typeArray'] ) ){
		$_SESSION['typeArray'] = get_typeName_array ();
	}
	
	//menuFrame ( "MTP" );
	include_once 'copysource/menu.php';
	
	$userArray = get_user_list ();
}
echo "<center>";
?><form method='post' name='detailsForm' action='tickets.php'
	onsubmit="javascript:return validateSubmit()"><?
	echo "<table width=80% cellpadding='0' cellspacing='0'>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<tr>";
	echo "<td class='titleBig' colspan='3'>Advanced Search</td>";
	echo "</tr>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR>";
	echo "<TD class='boldMed'><font color='red'>*</font>Description / Part Number / Return Number:</TD>";
	echo "<TD><input type='text' name='keywords' value='' size='75'>";
		echo "<select name='source'>";
			echo "<option value=''>Select Source</option>";
			echo "<option value='DESC'>Description</option>";
			echo "<option value='PART'>Exact Part Number</option>";
			echo "<option value='RETURN'>Exact Return Number</option>";
		echo "</select>";
	echo "</TR>";
	//D0128 - Removed required marker from classification
	echo "<TR>";
	echo "<TD class='boldMed'>Classification:</TD>";
	echo "<TD>";
	echo "<select name='classification' class='long'>";
	echo "<option value=''>Select Classification</option>";
	classification_select_box ( FACSLIB, "CIL09V01", "ID09", "CLAS09", "", "CLAS09", "" );
	echo "</select>";
	echo "</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD class='boldMed'>Company:</TD>";
	echo "<TD>";
	echo "<select name='companyCode' class='long'>";
	echo "<option value=''>Select Company</option>";
	if( !isset($code) ){
	    $code = 0;
	}
	list_company_code ( $code );
	echo "</select>";
	echo "</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD class='boldMed'>Requester</TD>";
	echo "<TD><select name='requester'>";
	echo "<option value=''>Select Requester</option>";
	foreach ( $userArray as $users ) {
		echo "<option value='" . trim ( $users ['ID05'] ) . "'>" . trim ( $users ['NAME05'] ) . "</option>";
	}
	echo "</select></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD class='boldMed'><font color='red'>*</font>Status:</TD>";
	echo "<TD>";
	echo "<select name='status'>";
	echo "<option value='1'>Open</option>";
	echo "<option value='5'>Resolved</option>";
	echo "</select>";
	echo "</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD class='boldMed'>&nbsp</TD>";
	echo "<TD>";
	if( !isset( $sYear ) ){
	    $sYear = 0;
	}
	if( !isset( $sMonth ) ){
	    $sMonth = 0;
	}
	if( !isset( $sDay ) ){
	    $sDay = 0;
	}
	select_date_listing ( "sYear", "sMonth", "sDay", $sYear, $sMonth, $sDay );
	echo "</TD>";
	echo "</TR>";
	//D0128 - Removed search method
	echo "<TR>";
	echo "<TD class='boldMed'><font color='red'>*</font>Date Range:</TD>";
	echo "<TD>to</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD class='boldMed'>&nbsp</TD>";
	echo "<TD>";
	if( !isset( $eYear ) ){
	    $eYear = 0;
	}
	if( !isset( $eMonth ) ){
	    $eMonth = 0;
	}
	if( !isset( $eDay ) ){
	    $eDay = 0;
	}
	
	select_date_listing ( "eYear", "eMonth", "eDay", $eYear, $eMonth, $eDay );
	echo "</TD>";
	echo "</TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<tr>";
	echo "<td colspan='2' align='left'><input type='submit' value='Continue'></td>";
	echo "</tr>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR><TD>&nbsp</TD></TR>";
	echo "<TR>";
	echo "<TD><font color='red'>*</font> Denotes required fields </TD>";
	echo "</TR>";
	echo "</table>";
	
	echo "<input type='hidden' name='from' value='advancedSearch'>";
	echo "</form>";
	
	page_footer( "advancedSearch");
	?>