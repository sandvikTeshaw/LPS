<?php
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceQuestions.php<br>
 * Development Reference:	DI0341<br>
 * Description:				maintenanceQuestions.php listing of questions to be used for tickets<br>
 *
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0341     DAS     8/20/2013   Creation<br>
 *  D0341       TS    23/03/2014  Re-Write<br>
 */
/**
 */
global $conn;
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';


if (! $conn) {
// $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

if ($conn) {

} else {
	echo "Connection Failed";
}

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

if($_POST['submit'] == 'Cancel') {
	header("location:maintenanceQuestions.php");
}

if($_POST['submit'] == 'Confirm Deletion') {

    $sqlChildQuestions = 'SELECT ID34 FROM cil34 where prnt34 = ' . $_GET['id'];
    $rsChildQuestions = odbc_prepare($conn, $sqlChildQuestions);
    odbc_execute($rsChildQuestions);

    while($rowChildQuestions = odbc_fetch_array($rsChildQuestions)){

        //Delete the child options
        $sqlChildOptionsDelete = 'DELETE FROM cil35 WHERE QID35 = ' . $rowChildQuestions['ID34'];
        $rsChildOptionsDelete = odbc_prepare($conn, $sqlChildOptionsDelete);
        odbc_execute($rsChildOptionsDelete);

        //Delete the child
        $sqlChildDelete = 'DELETE FROM cil34 WHERE PRNT34 = ' . $_GET['id'];
        $rsChildDelete = odbc_prepare($conn, $sqlChildDelete);
        odbc_execute($rsChildDelete);

    }

    //Delete the parent options
    $sqlOptionsDelete = 'DELETE FROM cil35 WHERE QID35 = ' . $_GET['id'];
    $rsOptionsDelete = odbc_prepare($conn, $sqlOptionsDelete);
    odbc_execute($rsOptionsDelete);

    //Delete the parent
    $sqlDelete = 'DELETE FROM cil34 WHERE ID34 = ' . $_GET['id'];
    $rsDelete = odbc_prepare($conn, $sqlDelete);
    odbc_execute($rsDelete);


    header("location:maintenanceQuestions.php");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
</head>
<?php include_once 'copysource/header.php'; ?>

<body>

<?php
if ($_SESSION ['userID']) {

	if (! $_SESSION ['classArray'] && ! $_SESSION ['typeArray']) {
		$_SESSION ['classArray'] = get_classification_array ();
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
	//menuFrame ( "MTP" );
	include_once 'copysource/menu.php';
}

ob_start ( 'compressBuffer' );
?>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
	<TR>
		<TD>&nbsp</TD>
	</TR>
	<TR>
		<TD class='title'>Question Maintenance - Delete</TD>
	</TR>
</table>
<form method="post" action="maintenanceQuestionsDelete.php?id=<?php echo $_GET['id']; ?>">
<strong>Are you sure you want to delete the following question?</strong></br>
</br>
<?php
$sqlQuestion = 'SELECT *
 		           FROM cil34
 		                LEFT JOIN cil09
                          ON cil34.clas34 = cil09.id09
 		                LEFT JOIN cil04
 		                  ON cil34.type34 = cil04.id04
 		           WHERE ID34 = ' . $_GET['id'];
$rsQuestion = odbc_prepare($conn, $sqlQuestion);
odbc_execute($rsQuestion);
$rowQuestion = odbc_fetch_array($rsQuestion);
?>
<table width="550">
   <tr>
      <td><strong>Question:</strong></td>
      <td><?php echo $rowQuestion['TEXT34']; ?></td>
   </tr>
   <tr>
      <td><strong>Class:</strong></td>
      <td><?php echo $rowQuestion['CLAS09']; ?></td>
   </tr>
   <tr>
      <td><strong>Type:</strong></td>
      <td><?php echo $rowQuestion['TYPE04']; ?></td>
   </tr>
</table>
</br></br>
<input type="submit" name="submit" value="Cancel"> &nbsp;&nbsp;&nbsp; <input type="submit" name="submit" value="Confirm Deletion">
</form>

</center>
</body>
</html>
<?php ob_flush(); ?>