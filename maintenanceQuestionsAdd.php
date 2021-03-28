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
 *  D0341     TS      25/03/2014   Re-write entire<br>
 */
/**
 */

if($_POST['frmCancel'] == "Cancel") {
	header("location: maintenanceQuestions.php");
}

if($_POST['frmReset'] == "Reset") {
	header("location: maintenanceQuestionsAdd.php");
}

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

if($_GET['step'] == 5) {
	$sqlMaxID = "SELECT MAX(ID34) AS MAXID FROM CIL34";
	$rsMaxID = odbc_prepare($conn, $sqlMaxID);
	odbc_execute($rsMaxID);
	$rowMaxID = odbc_fetch_array($rsMaxID);

	echo "maxid - '" . $rowMaxID['MAXID'] . "'<br>";
	$NextRow = $rowMaxID['MAXID'] + 1;

	if( !$_REQUEST['parentId'] ){
	   $parentId = 0;
	}else{
	    $parentId = $_REQUEST['parentId'];
	}

	if( !$_REQUEST['frmDependentAnswer'] ){
	    $dependent = 0;
	}else{
	    $dependent = $_REQUEST['frmDependentAnswer'];
	}


	$sqlInsert = "INSERT INTO cil34 (ID34, TEXT34, QTYP34, CLAS34, TYPE34, PRNT34, SECN34, DEPN34, ORDR34, REQD34 )
			      VALUES (" . $NextRow . ",'" . str_replace("'","''",$_POST['frmText']) . "','" . $_POST['frmAnswerMethod'] . "'," . $_POST['frmClass'] . "," . $_POST['frmType'] . "," . $parentId
	                     . "," . $_REQUEST['frmSection'] . "," . $dependent."," . $_POST['frmOrder'] . ", '" . $_POST['frmRequired'] . "')";

	$rsInsert = odbc_prepare($conn, $sqlInsert);
	odbc_execute($rsInsert);


    header("location:maintenanceQuestions.php");
}

if(isset($_GET['step'])) {
	$step = $_GET['step'];
} else {
	$step = 1;
}
$stepnext = $step + 1;


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript">

function validateForm(){
	var msg = "";


	if( document.getElementById('frmOrder').value == "" ){
	    msg = msg + "Display Order\n";
	}
	if( document.getElementById('frmText').value == "" ){
	    msg = msg + "Question\n";
	}
	if( document.getElementById('frmSection').value == "" ){
	    msg = msg + "Section\n";
	}

	if( msg != "" ){
	    alert( msg );
	    msg = "Please complete the following missing information:\n" + msg;
	    return false;
	}else{
	    return true;
	}

}
function validateAnswerMethod(){
    var answered = "";
	var frmAnswr = document.getElementsByName("frmAnswerMethod");

	for(var ans = 0; ans < frmAnswr.length; ans++) {
	   if(frmAnswr[ans].checked == true ) {
	       answered = true;
	   }
	}

	if( answered == false ){
		alert( "Please select an answer method" );
		return false;
	}else{
		return true;
	}


}

</script>
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
	<tr>
		<td>&nbsp</td>
	</tr>
	<tr>
		<td class='title'>Question/Suggestion Maintenance - Add</td>
	</tr>
</table>
<p></p>
<table width="700">
    <tr>
        <td><form method="post" action="maintenanceQuestionsAdd.php?parentId=<?php echo $_REQUEST['parentId'];?>&step=<?php echo $stepnext; ?>" style="display:inline;" onsubmit="return validateForm()">
         <?php
         if( $_REQUEST['child'] != true ){
            ?><strong>Section:</strong></td><?php

         ?>
         <td>

          <select name="frmSection" id="frmSection" maxlength="50" value="<?php echo $_REQUEST['frmText']; ?>" style="width:600px !important; height: 2.0em !important;"<?php if($step > 1) { echo " disabled"; } ?>>
                <option value=''>Select Section</option>
                <option value='1' <?php if($_REQUEST['frmSection'] == "1") { echo " SELECTED";} ?>>PFC Checklist</option>
                <option value='2' <?php if($_REQUEST['frmSection'] == "2") { echo " SELECTED";} ?>>Planner Checklist</option>
          </select>
          <?php
          }else{
              ?>&nbsp;</td><?php
          }
          ?>

    </tr>
   <tr>
      <td><strong>Question:</strong></td>
      <td>
          <input type="text" name="frmText" id="frmText" maxlength="250" value="<?php echo $_POST['frmText']; ?>" style="width:450px !important; height: 2.0em !important;"<?php if($step > 1) { echo " disabled"; } ?>/>
      </td>
   </tr>
          <?php
          if( $_REQUEST['child'] == true ){
          ?>
   <tr>
      <td>
            <input type="hidden" name="frmClass" value="<?php echo $_REQUEST['classId']; ?>">
            <input type="hidden" name="frmType" value="<?php echo $_REQUEST['typeId']; ?>">
            <input type="hidden" name="child" value="<?php echo $_REQUEST['child']; ?>">
            <input type="hidden" name=frmSection value="<?php echo $_REQUEST['section']; ?>">
            <input type="hidden" name=parent value="<?php echo $_REQUEST['parentId']; ?>">
            <input type="hidden" name=frmOrder value="<?php echo $_REQUEST['order']; ?>"/>
            <strong>Parent Dependent Value:</strong>
      </td>
      <td>
        <?php
        $sqlAnswers = 'SELECT * FROM CIL35 WHERE QID35 = ' . $_GET['parentId'] . ' ORDER BY ORDR35';
        $rsAnswers = odbc_prepare($conn, $sqlAnswers);
        odbc_execute($rsAnswers);

        ?>
        <select name='frmDependentAnswer' <?php if($step > 1) { echo " disabled"; }?>>
            <option value=''>Select Answer</option>
      <?php
          while( $aRows =  odbc_fetch_array($rsAnswers)){
            ?>
                <option value='<?php echo $aRows['ID35'];?>' <?php if($_REQUEST['frmDependentAnswer'] == $aRows['ID35']) { echo " SELECTED";} ?>><?php echo $aRows['OPTN35'];?></option>
            <?php

           }
      ?>
        </select>

      </td>
   </tr>

          <?php
          }else{
          ?>
          <tr>
            <td><strong>Display Order:</strong></td>
            <td>
                <select name='frmOrder' id='frmOrder' <?php if($step > 1) { echo " disabled"; } ?>>
                    <option value=''>Select Order</option>
                    <?php
                    for( $o=1;$o <= 25; $o++){
                        ?><option value='<?php echo $o;?>'<?php if($_REQUEST['frmOrder'] == $o ) { echo " SELECTED"; } ?>><?php echo $o?></option><?php
                    }
                    ?>

                  </select>
            </td>
          </tr>
               <?php
          }
          ?>
           <tr>
            <td><strong>Required:</strong></td>
            <td>
                <select name='frmRequired' id='frmRequired' <?php if($step > 1) { echo " disabled"; } ?>>
                    <option value='N'>No</option>
                    <option value='Y'>Yes</option>
                  </select>
            </td>
          </tr>
          <td>
            <?php
            if($step == 1) { ?><input type="submit" name="Submit1" value="Next" style="width:50px !important;"><?php }
            ?>
          </td>
          </form>


   <?php if($step > 1) { // begin step 2
   if( $_REQUEST['child'] == true ){
        $stepnext = 5;
   }
   ?>
   <tr>
      <td><strong>Answer Method:</strong></td>
      <td><form method="post" action="maintenanceQuestionsAdd.php?parentId=<?php echo $_REQUEST['parentId'];?>&step=<?php echo $stepnext; ?>" style="display:inline;" onsubmit="return validateAnswerMethod()">
          <input type="hidden" name="frmText" value="<?php echo $_POST['frmText']; ?>"/>
          <input type="hidden" name=frmSection value="<?php echo $_REQUEST['frmSection']; ?>"/>
          <input type="hidden" name=frmDependentAnswer value="<?php echo $_REQUEST['frmDependentAnswer']; ?>"/>
          <input type="hidden" name=frmOrder value="<?php echo $_REQUEST['frmOrder']; ?>"/>
          <input type="hidden" name=frmRequired value="<?php echo $_REQUEST['frmRequired']; ?>"/>
          <input type="radio" name="frmAnswerMethod" id="frmAnswerMethod" value="SEL"<?php if($_POST['frmAnswerMethod'] == "SEL") { echo " checked"; } ?><?php if($step > 2) { echo " disabled"; } ?> style="width:15px !important; border: none !important;"/> Dropdown Selection
          <input type="radio" name="frmAnswerMethod" id="frmAnswerMethod" value="RAD"<?php if($_POST['frmAnswerMethod'] == "RAD") { echo " checked"; } ?><?php if($step > 2) { echo " disabled"; } ?> style="width:15px !important; border: none !important;"/> Radio Buttons
          <input type="radio" name="frmAnswerMethod" id="frmAnswerMethod" value="TXT"<?php if($_POST['frmAnswerMethod'] == "TXT") { echo " checked"; } ?><?php if($step > 2) { echo " disabled"; } ?> style="width:15px !important; border: none !important;"/> Text Box - 50 Chars
          <input type="radio" name="frmAnswerMethod" id="frmAnswerMethod" value="SUG"<?php if($_POST['frmAnswerMethod'] == "SUG") { echo " checked"; } ?><?php if($step > 2) { echo " disabled"; } ?> style="width:15px !important; border: none !important;"/> Suggestion

          <?php if($step == 2) { ?><input type="submit" name="Submit2" value="Next" style="width:50px !important;"><?php } ?>
           <?php
          if( $_REQUEST['child'] == true ){
          ?>
                <input type="hidden" name="frmClass" value="<?php echo $_REQUEST['frmClass']; ?>">
                <input type="hidden" name="frmType" value="<?php echo $_REQUEST['frmType']; ?>">
                <input type="hidden" name="child" value="<?php echo $_REQUEST['child']; ?>">
                <input type="hidden" name="frmSection" value="<?php echo $_REQUEST['frmSection']; ?>">
                <input type="hidden" name=parent value="<?php echo $_REQUEST['parentId']; ?>">
                <input type="hidden" name=frmOrder value="<?php echo $_REQUEST['frmOrder']; ?>"/>
                <input type="hidden" name=frmRequired value="<?php echo $_REQUEST['frmRequired']; ?>"/>
          <?php
          }
          ?>
          </form>
      </td>
   </tr>
   <?php } // end checking if greater than step 1 ?>
   <?php if($step > 2) { // begin step 3?>

   <tr>
      <td><strong>Class:</strong></td>
      <td><form method="post" action="maintenanceQuestionsAdd.php?parentId=<?php echo $_REQUEST['parentId'];?>&step=<?php echo $stepnext; ?>" style="display:inline;">
          <input type="hidden" name="frmText" value="<?php echo $_POST['frmText']; ?>">
          <input type="hidden" name=frmSection value="<?php echo $_REQUEST['frmSection']; ?>">
          <input type="hidden" name="frmAnswerMethod" value="<?php echo $_POST['frmAnswerMethod']; ?>">
          <input type="hidden" name=parent value="<?php echo $_REQUEST['parentId']; ?>">
          <input type="hidden" name=frmOrder value="<?php echo $_REQUEST['frmOrder']; ?>"/>
          <input type="hidden" name=frmRequired value="<?php echo $_REQUEST['frmRequired']; ?>"/>
          <select name="frmClass" style="width:450px !important;"<?php if($step > 3) { echo " disabled"; } ?>>
          <?php
          $sqlClasses = 'SELECT *
 		                 FROM cil09';
          $rsClasses = odbc_prepare($conn, $sqlClasses);
          odbc_execute($rsClasses);

          while($rowClasses = odbc_fetch_array($rsClasses)) {
 	         ?><option <?php echo ( $_REQUEST['frmClass'] ==  $rowClasses['ID09']  ? "selected='selected'" : '');?> value='<?php echo $rowClasses['ID09'];?>'><?php echo $rowClasses['CLAS09'];?></option><?php
          }
          ?>
          </select>
          <?php if($step == 3) { ?><input type="submit" name="Submit3" value="Next" style="width:50px !important;"><?php } ?>
          </form>
      </td>
   </tr>
   <?php } //end checking if greater than step 2 ?>
   <?php if($step > 3) { // begin step 4?>
   <tr>
      <td><strong>Type:</strong></td>
      <td><form method="post" action="maintenanceQuestionsAdd.php?parentId=<?php echo $_REQUEST['parentId'];?>&step=<?php echo $stepnext; ?>" style="display:inline;">
          <input type="hidden" name="frmText" value="<?php echo $_POST['frmText']; ?>">
          <input type="hidden" name=frmSection value="<?php echo $_REQUEST['frmSection']; ?>">
          <input type="hidden" name="frmClass" value="<?php echo $_POST['frmClass']; ?>">
          <input type="hidden" name="frmAnswerMethod" value="<?php echo $_POST['frmAnswerMethod']; ?>">
          <input type="hidden" name=parent value="<?php echo $_REQUEST['parentId']; ?>">
          <input type="hidden" name=frmOrder value="<?php echo $_REQUEST['frmOrder']; ?>"/>
          <input type="hidden" name=frmRequired value="<?php echo $_REQUEST['frmRequired']; ?>"/>

          <select name="frmType" style="width:450px !important;">
          <?php
          $sqlTypes = 'SELECT *
 		                 FROM cil04
 		                 WHERE id04 in
                               (select type12 from cil12 where clas12 = ' . $_POST['frmClass'] . ')';
          $rsTypes = odbc_prepare($conn, $sqlTypes);
          odbc_execute($rsTypes);

           while($rowTypes = odbc_fetch_array($rsTypes)){
 	         ?><option <?php echo ( $_REQUEST['frmType'] ==  $rowTypes['ID04']  ? "selected='selected'" : '');?> value='<?php echo $rowTypes['ID04']?>'><?php echo $rowTypes['TYPE04'];?></option><?php
          }
          ?>
          </select>

          <?php if($step == 4) { ?><input type="submit" name="Submit4" value="Finish" style="width:50px !important;"><?php } ?>
          </form>
      </td>
   </tr>
   <?php } //end checking if greater than step 2 ?>
   <tr>
      <td colspan="2"><center><br><br><form method="post" action="maintenanceQuestionsAdd.php"><input type="submit" name="frmCancel" value="Cancel"> <input type="submit" name="frmReset" value="Reset"></form></center></td>
   </tr>
</table>
</center>
</body>
</html>
<?php
ob_flush();
?>