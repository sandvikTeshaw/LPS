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
 *  D0341     TS      25/03/2014  Re-write entire <br/>
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


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript"> </script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE_TITLE; ?></title>

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
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class='title'>Question/Suggestion Maintenance</td>
	</tr>
	<tr>
	   <td class='title'><p>[<a href='maintenanceQuestionsAdd.php?parentId=0'>+Add Question/Suggestion</a>]</p></td>
	</tr>
</table>
<table width="90%" cellpadding="0" cellspacing="0">
   <tr style="background:#CCC;">
      <td width='30%'><strong>Text</strong></td>
      <td width='30%'><strong>Class</strong></td>
      <td width='20%'><strong>Type</strong></td>
      <td width='10%'><strong>Order</strong></td>
      <td width='5%'><strong>Section</strong></td>
      <td width='5%'>&nbsp;</td>
   </tr>

<?php

for( $qtype = 1; $qtype <= 2; $qtype++ ){

    if( $qtype == 1 ){
        $sqlQuestions = "SELECT * FROM cil34 LEFT JOIN cil09 ON cil34.clas34 = cil09.id09 LEFT JOIN cil04 ON cil34.type34 = cil04.id04 where prnt34 = 0 AND QTYP34 <> 'SUG' ORDER BY SECN34, ID09, ID04, ORDR34";

    ?>
        <tr>
            <td class='boldBig' colspan='2'>Questions</td>
        </tr>
        <tr>
            <td colspan='6'><hr/></td>
        </tr>
    <?php
    }else{
        $sqlQuestions = "SELECT * FROM cil34 LEFT JOIN cil09 ON cil34.clas34 = cil09.id09 LEFT JOIN cil04 ON cil34.type34 = cil04.id04 where prnt34 = 0 AND QTYP34 = 'SUG' ORDER BY SECN34, ID09, ID04, ORDR34";

        ?>
                <tr>
                    <td colspan='6'><hr/></td>
                </tr>
                <tr>
                    <td class='boldBig' colspan='2'>Suggestions</td>
                </tr>
                <tr>
                    <td colspan='6'><hr/></td>
                </tr>
            <?php
    }

    $rsQuestions = odbc_prepare($conn, $sqlQuestions);
    odbc_execute($rsQuestions);
    $rowCount = 0;

    while($rowQuestions = odbc_fetch_array($rsQuestions)){

        $sectionName = "";
        switch ($rowQuestions['SECN34']) {
    	case 1:
    	    $sectionName = "PFC";
    	    break;
    	case 2:
    	    $sectionName = "Planner";
    	    break;
    	default:
    	    $sectionName = "Error in Section";
    	    break;
        }


        if( $rowCount % 2 ){
    	   echo "<tr class='alternate'>";
        }else{
    	   echo "<tr class=''>";
        }
     	echo "   <td>" . $rowQuestions['TEXT34'] . "</td>
     		     <td>" . $rowQuestions['CLAS09'] . "</td>
     		     <td>" . $rowQuestions['TYPE04'] . "</td>
                 <td>" . $rowQuestions['ORDR34'] . "</td>
                 <td>" . $sectionName . "</td>
    		     <td>";
    	       if( $rowQuestions['QTYP34'] != "TXT" && $rowQuestions['QTYP34'] != "SUG"){
                     echo "<a href='maintenanceQuestionsAdd.php?child=true&parentId=" . $rowQuestions['ID34'] . "&classId=" . $rowQuestions['ID09'] . "&typeId=" . $rowQuestions['ID04'] . "&section=" . $rowQuestions['SECN34'] . "&order=" . $rowQuestions['ORDR34'] . "'><img src='" . $IMG_DIR . "/post.gif' border='0' title='Add Child'></a>";
               }
    		   echo "<a href='maintenanceQuestionsEdit.php?id=" . $rowQuestions['ID34'] . "'><img src='" . $IMG_DIR . "/edit.gif' border='0' title='Edit'></a>
        		    <a href='maintenanceQuestionsDelete.php?id=" . $rowQuestions['ID34'] . "'><img src='" . $IMG_DIR . "/delete.gif' border='0' title='Delete'></a></td>
     		  </tr>";

     	$sqlChildQuestions = 'SELECT * FROM cil34 LEFT JOIN cil09 ON cil34.clas34 = cil09.id09 LEFT JOIN cil04 ON cil34.type34 = cil04.id04 where prnt34 = ' . $rowQuestions['ID34'];
     	$rsChildQuestions = odbc_prepare($conn, $sqlChildQuestions);
     	odbc_execute($rsChildQuestions);

     	$rowChildCount = 0;
     	while($rowChildQuestions = odbc_fetch_array($rsChildQuestions)){

        $questionType = "";
        switch ($rowChildQuestions['QTYP34']) {
        	case "TXT":
        	    $questionType = "Text";
        	    break;
        	case "SEL":
        	    $questionType = "Drop Down";
        	    break;
        	case "RAD":
        	    $questionType = "Options";
        	    break;
        	case "SUG":
        	    $questionType = "Suggestion";
        	    break;
        	default:
        	    $questionType = "Error in Section";
        	    break;
        }

            if( $rowChildCount == 0 ){
            ?>
            <tr>
            <td colspan='6'>
            <center><table width=95% cellpadding='0' cellspacing='0' class='hoverHighlight' border='0'><?php
            }
            $rowChildCount++;
            ?>
              <tr>
                <td width='80%'><?php echo $rowChildQuestions['TEXT34'];?></td>
                <td width='15%'><?php echo $questionType;?></td>
    		     <td width='5%'><a href='maintenanceQuestionsEdit.php?id=<?php echo $rowChildQuestions['ID34'];?>&child=true&parentId=<?php echo $rowQuestions['ID34'];?>'><img src='<?php echo $IMG_DIR;?>/edit.gif' border='0'></a>
        		     <a href='maintenanceQuestionsDelete.php?id=<?php echo $rowChildQuestions['ID34'];?>'><img src='<?php echo $IMG_DIR;?>/delete.gif' border='0'></a></td>
     		  </tr>
            <?php
     	}
     	if( $rowChildCount > 0 ){
     	    ?></td></tr></table></center></td></tr><tr><td>&nbsp;</td></tr><?php
     	}

        $rowCount++;
    }
}
?>

</table>
</center>
</body>
</html>

<?php ob_flush(); ?>