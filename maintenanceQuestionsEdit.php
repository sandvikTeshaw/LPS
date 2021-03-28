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
 *  D0341     TS      23/03/2014  Re-write<br>
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

if($_POST['submit'] == 'Done Editing') {
	header("location:maintenanceQuestions.php");
}

if($_POST['frmAddAnswerSubmit'] == 'Add') {
	$sqlNextAnswerID = 'SELECT MAX(ID35) AS MAXID FROM CIL35
                        UNION
                        SELECT MAX(ORDR35) AS MAXID FROM CIL35 WHERE QID35 = ' . $_GET['id'];
	$rsNextAnswerID = odbc_prepare($conn, $sqlNextAnswerID);
	odbc_execute($rsNextAnswerID);
	$rowNextAnswerID = odbc_fetch_array($rsNextAnswerID);

	$NextID35 = $rowNextAnswerID['MAXID'] + 1;
	$rowNextAnswerID = odbc_fetch_array($rsNextAnswerID);
	if($rowNextAnswerID['MAXID'] > 0) {
		$NextQID35 = $rowNextAnswerID['MAXID'] + 1;
	} else {
		$NextQID35 = 1;
	}

	$sqlInsertAnswer = "INSERT INTO cil35
 		                   (ID35, QID35, OPTN35, ORDR35)
			            VALUES (" . $NextID35 . "," . $_GET['id'] . ", '" . str_replace("'","''",$_POST['frmAddAnswerText']) . "'," . $NextQID35 . ")";
	$rsInsertAnswer = odbc_prepare($conn, $sqlInsertAnswer);
	odbc_execute($rsInsertAnswer);
}

if($_GET['update'] == 'answermethod') {
	$sqlUpdateAnswerMethod = "UPDATE cil34
 		                         SET QTYP34 = '" . $_POST['frmAnswerMethod'] . "'
 		                       WHERE ID34 = " . $_GET['id'];
    $rsUpdateAnswerMethod = odbc_prepare($conn, $sqlUpdateAnswerMethod);
    odbc_execute($rsUpdateAnswerMethod);
}

if($_GET['update'] == 'text') {
	$sqlUpdateText = "UPDATE cil34
 		                 SET TEXT34 = '" . $_POST['frmText'] . "'
 		               WHERE ID34 = " . $_GET['id'];
	$rsUpdateText = odbc_prepare($conn, $sqlUpdateText);
	odbc_execute($rsUpdateText);
}

if($_REQUEST['update'] == 'answerText') {
    $sqlUpdateAnswerText = "UPDATE CIL35
 		                 SET OPTN35 = '" . $_REQUEST['newAnswer'] . "'
 		               WHERE ID35 = " . $_REQUEST['aid'];
    $rsUpdateAnswerText = odbc_prepare($conn, $sqlUpdateAnswerText);
    odbc_execute($rsUpdateAnswerText);
}
if($_REQUEST['update'] == 'dependentAnswer') {
    $sqlUpdateDependent = "UPDATE CIL34
 		                 SET DEPN34 = '" . $_REQUEST['frmDependentAnswer'] . "'
 		               WHERE ID34 = " . $_REQUEST['id'];
    $rsUpdateDependent = odbc_prepare($conn, $sqlUpdateDependent);
    odbc_execute($rsUpdateDependent);
}
if($_REQUEST['update'] == 'order') {

    $sqlUpdateOrder = "UPDATE CIL34
 		                 SET ORDR34 = '" . $_REQUEST['frmOrder'] . "'
 		               WHERE ID34 = " . $_REQUEST['id'];
    $rsUpdateOrder = odbc_prepare($conn, $sqlUpdateOrder);
    odbc_execute($rsUpdateOrder);

    $sqlUpdateChildOrder = "UPDATE CIL34
 		                 SET ORDR34 = '" . $_REQUEST['frmOrder'] . "'
 		               WHERE PRNT34 = " . $_REQUEST['id'];
    $rsUpdateChildOrder = odbc_prepare($conn, $sqlUpdateChildOrder);
    odbc_execute($rsUpdateChildOrder);

}

if($_GET['action'] == 'delete') {
	$sqlDeleteAnswer = "DELETE FROM cil35 WHERE ID35 = " . $_GET['aid'];
	$rsDeleteAnswer = odbc_prepare($conn, $sqlDeleteAnswer);
	odbc_execute($rsDeleteAnswer);
	header("location: maintenanceQuestionsEdit.php?id=" . $_GET['id']);
}



if($_POST['frmSaveClassType'] == "Save") {
	$sqlUpdateClassType = "UPDATE cil34
 		                      SET CLAS34 = " . $_POST['frmClass'] . ",
 		                          TYPE34 = " . $_POST['frmType'] . "
 		                    WHERE ID34 = " . $_GET['id'];
	$rsUpdateClassType = odbc_prepare($conn, $sqlUpdateClassType);
	odbc_execute($rsUpdateClassType);
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

//ob_start ( 'compressBuffer' );
?>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
	<TR>
		<TD>&nbsp</TD>
	</TR>
	<TR>
		<TD class='title'>Question/Suggestion Maintenance - Edit</TD>
	</TR>
</table>

<?php
if($_REQUEST['action'] == 'editAnswer') {

    $sqlAnswersOption = "SELECT * FROM cil35 WHERE QID35 = {$_REQUEST['qid']} AND ID35 = {$_REQUEST['aid']}";
    $rsQuestionAnswer = odbc_prepare($conn, $sqlAnswersOption);
    odbc_execute($rsQuestionAnswer);

    ?>
    <p></p>
    </br></br></br>

    <?php
    while ( $rowAnswerEdit = odbc_fetch_array($rsQuestionAnswer ) ){
        $currentAnswer = $rowAnswerEdit['OPTN35'];
    }
    ?>
    <table width="500">
    <tr>
        <td><strong>Current Answer Text:</strong></td>
        <td><?php echo $currentAnswer;?></td>
    </tr>
    <form method="post" action="maintenanceQuestionsEdit.php?id=<?php echo $_REQUEST['qid']; ?>&update=answerText" style="display:inline;">
    <tr>
        <td><strong>New Answer Text:</strong></td>
        <td><input type='text' name='newAnswer' name='newAnswer' value=''/></td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td>

            <input type="submit" name="frmSaveAnswerText" id="frmSaveAnswerText" value="Save" style="width:50px !important;"/>
            <input type='hidden' name='aid' id='aid' value='<?php echo $_REQUEST['aid'];?>'/>
            </form>
            <form method="post" action="maintenanceQuestionsEdit.php?id=<?php echo $_REQUEST['qid']; ?>" style="display:inline;">
            <input type="submit" name="frmSaveAnswerCancel" id="frmSaveAnswerCancel" value="Cancel" style="width:50px !important;"/>
            </form>
        </td>
    </tr>
    </table>
    <?php
    die();

}

?>
<p></p>
<form method="post" action="maintenanceQuestionsEdit.php?id=<?php echo $_GET['id']; ?>">
</form>
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
<table width="700">
   <tr>
      <td width="150"><strong>Question:</strong></td>
      <td><form method="post" action="maintenanceQuestionsEdit.php?id=<?php echo $_GET['id']; ?>&update=text" style="display:inline;">
          <input type="text" id="frmText" name="frmText" maxlength="50" value="<?php echo trim($rowQuestion['TEXT34']); ?>" style="width:450px !important; height: 2.0em !important;" onKeyPress='document.getElementById("frmSaveText").style.visibility="visible";'>
          <input type='hidden' name='child' value='<?php echo $_REQUEST['child'];?>'/>
          <input type='hidden' name='parentId' value='<?php echo $_REQUEST['parentId'];?>'/>
          <input type="submit" name="frmSaveText" id="frmSaveText" value="Save" style="width:50px !important;">
          </form>
      </td>
   </tr>
   <tr>
      <td><strong>Answer Method:</strong></td>
      <td><form method="post"  action="maintenanceQuestionsEdit.php?id=<?php echo $_GET['id']; ?>&update=answermethod" style="display:inline;">
          <input type='hidden' name='child' value='<?php echo $_REQUEST['child'];?>'/>
          <input type='hidden' name='parentId' value='<?php echo $_REQUEST['parentId'];?>'/>
          <input type="radio" name="frmAnswerMethod" value="SEL"<?php if($rowQuestion['QTYP34'] == "SEL") { echo " checked"; } ?> style="width:15px !important; border: none !important;" onClick='this.form.submit();'> Dropdown Selection
          <input type="radio" name="frmAnswerMethod" value="RAD"<?php if($rowQuestion['QTYP34'] == "RAD") { echo " checked"; } ?> style="width:15px !important; border: none !important;" onClick='this.form.submit();'> Radio Buttons
          <input type="radio" name="frmAnswerMethod" value="TXT"<?php if($rowQuestion['QTYP34'] == "TXT") { echo " checked"; } ?> style="width:15px !important; border: none !important;" onClick='this.form.submit();'> Text Box - 50 Chars
          <input type="radio" name="frmAnswerMethod" value="SUG"<?php if($rowQuestion['QTYP34'] == "SUG") { echo " checked"; } ?> style="width:15px !important; border: none !important;" onClick='this.form.submit();'> Suggestion

          <!-- <input type="submit" name="SubmitAnswerMethod" value="Update" style="width:50px !important;"> -->
          </form>
      </td>
   </tr>

   <tr><td colspan=2><hr></hr></td></tr>

    <tr>
   <?php
   if( $_REQUEST['child'] == true ){
        ?>
        <td>
        <form method="post" action="maintenanceQuestionsEdit.php?id=<?php echo $_GET['id']; ?>&update=dependentAnswer" style="display:inline;">
        <strong>Parent Dependent Value:</strong>
        <input type='hidden' name='frmClass' value='<?php echo $rowQuestion['CLAS34'];?>'/>
        <input type='hidden' name='frmType' value='<?php echo $rowQuestion['TYPE34'];?>'/>
        <input type='hidden' name='child' value='<?php echo $_REQUEST['child'];?>'/>
         <input type='hidden' name='parentId' value='<?php echo $_REQUEST['parentId'];?>'/>
        </td>
        <td>
            <?php
            $sqlAnswers = 'SELECT * FROM CIL35 WHERE QID35 = ' . $_REQUEST['parentId'] . ' ORDER BY ORDR35';
            $rsAnswers = odbc_prepare($conn, $sqlAnswers);
            odbc_execute($rsAnswers);

            ?>
            <select name='frmDependentAnswer' <?php if($step > 1) { echo " disabled"; }?>>
                <option value=''>Select Answer</option>
          <?php
              while( $aRows =  odbc_fetch_array($rsAnswers)){
                ?>
                    <option value='<?php echo $aRows['ID35'];?>' <?php if($rowQuestion['DEPN34'] == $aRows['ID35']) { echo " SELECTED";} ?>><?php echo $aRows['OPTN35'];?></option>
                <?php

               }
          ?>
          </select>
          <input type="submit" name="frmSaveText" id="frmSaveText" value="Save" style="width:50px !important;">
          </form>
      </td>

        <?php
    }else{
   ?>

      <td>
      <form method="post" action="maintenanceQuestionsEdit.php?<?php echo $_SERVER['QUERY_STRING']; ?>">
      <strong>Class:</strong></td>

      <td><select name="frmClass" style="width:450px !important; height: 2.0em !important;" onChange='document.getElementById("frmSaveClassType").style.visibility="visible"; document.getElementById("frmType").selectedIndex=0;'>
            <option value="">-- Select One --</option>
            <?php
            $sqlClasses = 'SELECT * FROM cil09';
            $rsClasses = odbc_prepare($conn, $sqlClasses);
            odbc_execute($rsClasses);
            $rowClasses = odbc_fetch_array($rsClasses);

            do {
               echo '<option value="' . $rowClasses['ID09'] . '"';
               if($rowQuestion['CLAS34'] == $rowClasses['ID09']) {
                  echo " selected";
               }
               echo '>' . trim($rowClasses['CLAS09']) . '</option>';
            } while($rowClasses = odbc_fetch_array($rsClasses));
            ?>
          </select>
      </td>
   </tr>
   <tr>
      <td><strong>Type:</strong></td>
      <td><select name="frmType" style="width:450px !important; height: 2.0em !important;" id="frmType" onChange='document.getElementById("frmSaveClassType").style.visibility="visible"; document.getElementById("frmSaveClassType").disabled=false;'>
            <option value="">-- Select One --</option>
            <?php
            $sqlTypes = 'SELECT * FROM cil04';
            $rsTypes = odbc_prepare($conn, $sqlTypes);
            odbc_execute($rsTypes);
            $rowTypes = odbc_fetch_array($rsTypes);

            do {
               echo '<option value="' . $rowTypes['ID04'] . '"';
               if($rowQuestion['TYPE34'] == $rowTypes['ID04']) {
                  echo " selected";
               }
               echo '>' . trim($rowTypes['TYPE04']) . '</option>';
            } while($rowTypes = odbc_fetch_array($rsTypes));
            ?>
          </select> &nbsp;&nbsp;
          <input type="submit" name="frmSaveClassType" id="frmSaveClassType" value="Save" style="width:50px !important; visibility:hidden;" disabled/>
      </td>
   </tr>
    </form>
   <tr>
      <td><strong>Display Order:</strong></td>
      <td><form method="post" action="maintenanceQuestionsEdit.php?id=<?php echo $_GET['id']; ?>&update=order" style="display:inline;">

          <input type='hidden' name='child' value='<?php echo $_REQUEST['child'];?>'/>
          <input type='hidden' name='parentId' value='<?php echo $_REQUEST['parentId'];?>'/>
          <select name='frmOrder'>
            <option value=''>Select Order</option>
            <?php
            for( $o=1;$o <= 25; $o++){
                ?><option value='<?php echo $o;?>' <?php if($rowQuestion['ORDR34'] == $o ) { echo " SELECTED"; } ?>><?php echo $o?></option><?php
            }
            ?>

          </select>
          <input type="submit" name="SubmitOrder" value="Update" style="width:50px !important;"/>
          </form>
      </td>
   </tr>
   <?php
   }
   ?>

</table>
<br/><br/><br/><br/>
<table width="700" cellpadding="0" cellspacing="0">
   <tr>
      <td colspan="2" class="title">
         Available Answer Selections: <a href="#" onClick="document.getElementById('frmAddAnswers').style.display = 'inline';">[+Add New]</a><br>
         <form method="post" action="maintenanceQuestionsEdit.php?<?php echo $_SERVER['QUERY_STRING']; ?>" style="display:none;" id="frmAddAnswers">
            <input type="text" name="frmAddAnswerText" style="width:425px !important;" maxlength="50"> &nbsp;&nbsp; <input type="submit" name="frmAddAnswerSubmit" value="Add" style="width:50px !important;">
         </form>
      </td>
   </tr>
   <tr style="background:#CCC;">
      <td width="665"><strong>Answer</strong></td>
      <td width="35">&nbsp;</td>
   </tr>

<?php
$sqlAnswers = 'SELECT *
 		           FROM cil35
 		           WHERE QID35 = ' . $_GET['id'] . ' ORDER BY ORDR35';
$rsAnswers = odbc_prepare($conn, $sqlAnswers);
odbc_execute($rsAnswers);
$rowAnswers = odbc_fetch_array($rsAnswers);
$rowCount = 0;
do {
    if( $rowCount % 2 ){
	   echo "<tr class='alternate'>";
    }else{
	   echo "<tr class=''>";
    }
 	echo "   <td>" . $rowAnswers['OPTN35'] . "</td>";
 	echo "   <td>";
 	if(strlen($rowAnswers['OPTN35']) > 0) {
 	    echo "<a href='maintenanceQuestionsEdit.php?id=" . $_GET['id'] . "&action=editAnswer&qid=" . $rowAnswers['QID35'] . "&aid=" . $rowAnswers['ID35'] . "'><img src='" . $IMG_DIR . "/edit.gif' border='0'></a>
    		  <a href='maintenanceQuestionsEdit.php?id=" . $_GET['id'] . "&action=delete&aid=" . $rowAnswers['ID35'] . "' onclick=\"return confirm('Are you sure you want to delete this answer??');\"><img src='" . $IMG_DIR . "/delete.gif' border='0'></a>";
    } else {
        echo "&nbsp;";
    }
	echo "    </td>
 		  </tr>";
    $rowCount++;
} while($rowAnswers = odbc_fetch_array($rsAnswers));
?>

</table>
<br><br><br><br>
<form style="display:inline;">
<input type="button" name="submit" value="Done Editing" onClick="window.location = 'maintenanceQuestions.php';">
</form>
</center>
</body>
</html>
<?php ob_flush(); ?>