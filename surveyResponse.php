<?php 

include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';


setcookie("mtp", "", time()-3600);

if (isset($conn)) {

}else{
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
    // $conn = db2_connect ( SYSTEM, DB_USER, DB_PASS, $Options );
    $conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}
if ($conn) {

} else {
    echo "Connection Failed";
}

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
<body>

<?php
include_once 'copysource/header.php';

//headerFrame ( $_SESSION ['name'], $SITENAME, $ID01 );


	if( !$_SESSION ['classArray'] ){
	 	$_SESSION ['classArray'] = get_classification_array ();
	}
	if( !$_SESSION ['typeArray'] ){
		$_SESSION ['typeArray'] = get_typeName_array ();
	}
//menuFrame ( $SITENAME );
include_once 'copysource/menu.php';


$surveySql = "SELECT * FROM CIL41 WHERE ACTF41 = 1";
$surveyRes = odbc_prepare ( $conn, $surveySql );
odbc_execute ( $surveyRes );


$ansCounter = 0;
while( $surveyRow = odbc_fetch_array( $surveyRes )){
    
    $ansCounter++;
    
    $sVariable = "q_" . $surveyRow['ID41'];
    
    if( isset( $_REQUEST[ $sVariable ] ) ){
        foreach ( $_REQUEST[ $sVariable ]  as $answer ){
            $userResponse = $answer;
        }
    }
    
    if( isset($userResponse) ){
        $nextID = get_next_unique_id( FACSLIB, "CIL43", "ID43", "" );
        $resultInsertSql = "INSERT INTO CIL43 VALUES( $nextID, " . $surveyRow['ID41'] . "," . $userResponse . ", " . $_REQUEST['ID01'] . ", " . $_SESSION ['userID'] . ", " . DATE( 'Ymd' ) . ")";
        
        $res = odbc_prepare ( $conn, $resultInsertSql );
        odbc_execute ( $res );
    }
    

}

$nextCommentID = get_next_unique_id( FACSLIB, "CIL44", "ID44", "" );
$sqlCommentInsert = "INSERT INTO CIL44 VALUES( $nextCommentID, " . $_REQUEST['ID01'] . ", " . $_SESSION ['userID'] . ",'" . $_REQUEST['addInfo'] . "', ". DATE( 'Ymd' ) . ")";
$resComment = odbc_prepare ( $conn, $sqlCommentInsert );
odbc_execute ( $resComment );

?>
<center><br><br>Thank you for your feedback<br><br>

    <form method='get' action='tickets.php?stat=1&CLAS09=<?php echo $CLAS09;?>&type=<?php echo $type;?>&PRTY01=<?php echo $PRTY01;?>' class='buttonForm'>
	<input type='hidden' name='stat' value='1'>
	<input type='hidden' name='CLAS09' value='<?php echo $CLAS09;?>' />
    <input type='hidden' name='type' value='<?php echo $type;?>' />
    <input type='hidden' name='PRTY01' value='<?php echo $PRTY01;?>' />
	<input type='hidden' name='from' value='<?php echo $from;?>' />
	<input type='submit' name='' value='Continue' class='buttonForm'>
	</form>
    </form>
</center>



