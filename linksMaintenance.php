<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            linksMaintenance.php<br>
 * Development Reference:   LP0039<br>
 * Description:             Queue 2.0<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *   LP0039     KS    23/03/2018  In the LPS "Register for LPS account" page please add hyperlinks to instruction guidelines (SPIDER 2.0)
 *
 */
/**
 */


include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';



global $conn;

if (! $conn) {
    // $Options = array( 'i5_naming'=>DB2_I5_NAMING_ON);
	// $conn = db2_pconnect ( SYSTEM, DB_USER, DB_PASS, $Options );
	$conn = odbc_connect ( SYSTEM, DB_USER, DB_PASS );
}

$attachementsFolder = "../../attachments/documents/";


echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<link rel='stylesheet' type='text/css' href='copysource/custom.css'>";
echo "<style type='text/css'>";
echo "<!-- ";
echo "@import url(copysource/styles.css);";
echo "-->";
echo "</style>";
echo "</head>";
echo "<body>";


//header:
include_once 'copysource/header.php';



if($_REQUEST['action'] == "edit"){
    
    editLink($_REQUEST['linkID']);
    
}elseif(isset($_REQUEST['confirmEdit'])){
    
    updateLinkDSH07($_REQUEST['linkID'], $_REQUEST['linkLink'], $_REQUEST['nameLink'], $_REQUEST['commentLink'], $_REQUEST['typeLink']);
    
    
}elseif($_REQUEST['action'] == "delete"){
    
    deleteLink($_REQUEST['linkID']);
    
}elseif(isset($_REQUEST['confirmDelete'])){
    
    deleteLinkDSH07($_REQUEST['linkID']);
    
    
}else{
    
    //menu:
    if(!$_SESSION ['classArray']){
        $_SESSION ['classArray'] = get_classification_array();
    }
    if(!$_SESSION ['typeArray']){
        $_SESSION ['typeArray'] = get_typeName_array();
    }
    include_once 'copysource/menu.php';
    
    echo "<hr />";
    
    saveFileIFS();
    saveURL();
    
    echo "<br />";
    echo "<center> Links Maintenance </center>";
    echo "<br />";
    
    
    addLink();
    echo "<hr />";
    
    listLinks();
    echo "<hr />";
    
}



echo "</body>";
echo "</html>";


//********************************************************************************************************
//**  FUNCTIONS                                                                                         **
//********************************************************************************************************
function listLinks(){
    global $conn, $attachementsFolder, $IMG_DIR;
    
    $sql = "select * ";
    $sql .= " from DSH07 ";
    $sql .= " where WBID07 = 'DOC' ";
    $sql .= "   and PGID07 = 'CIL' ";
    $sql .= "   and KEY207 = 'USER' ";
    $sql .= " order by UFILE07 ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    
    echo "<table class='data-table'>";
    
    echo "<tr>";
    echo "<th> Name </th>";
    echo "<th> Link / File </th>";
    echo "<th> Comment </th>";
    echo "<th> Test link </th>";
    echo "<th> Action </th>";
    echo "</tr>";
    
    while (($row = odbc_fetch_array($res)) <> false){
        
        echo "<tr>";
        
        echo "<td>";
        echo trim($row['UFILE07']);
        echo "</td>";
        
        echo "<td>";
        echo trim($row['FILE07']);
        echo "</td>";
        
        echo "<td>";
        echo trim($row['COMM07']);
        echo "</td>";
        
        echo "<td>";
        if (trim($row['LINK07'] == 'Y')){
            echo "<a target='_blank' href='" . trim($row['FILE07']) . "'>" . trim($row['UFILE07']) . "</a>";
        }else{
            echo "<a target='_blank' href='" . $attachementsFolder . trim($row['FILE07']) . "'>" . trim($row['UFILE07']) . "</a>";
        }
        echo "</td>";
        
        
        echo "<td style='width: 60px; text-align: center;'>";
        echo " <a href='linksMaintenance.php?linkID=" . trim($row['ID07']) . "&action=edit' target='_new'>";
        echo "   <img src='" . $IMG_DIR . "/edit.gif' border=0 alt='Edit' />";
        echo " </a>";
        echo " &nbsp; ";
        echo " <a href='linksMaintenance.php?linkID=" . trim($row['ID07']) . "&action=delete' target='_new'>";
        echo "   <img src='" . $IMG_DIR . "/delete.gif' border=0 alt='Delete' />";
        echo " </a>";
        
        echo "</td>";
        
        echo "</tr>";
        
    }
    
    echo "</table>";
    
    
}


function addLink(){
    
    
    echo "<form method='POST' enctype='multipart/form-data' action='linksMaintenance.php'>";
    echo "<table style='width: auto; margin-left: 1%;'>";
    
    echo "<tr>";
    echo "<td> </td>";
    echo "<td> New link/file </td>";
    echo "<td> Name (if different) </td>";
    echo "<td> Comment </td>";
    echo "<td> </td>";
    echo "</tr>";
    
    
    echo "<tr>";
    echo "<td>";
    echo "Add link:";
    echo "</td>";
    echo "<td>";
    echo "<input type='url' id='linkURL' name='linkURL'/>";
    echo "</td>";
    echo "<td>";
    echo "<input type='text' id='textURL' name='textURL' />";
    echo "</td>";
    echo "<td>";
    echo "<input type='text' id='commentURL' name='commentURL' />";
    echo "</td>";
    
    echo "<td rowspan = '2'>";
    echo "<input type='submit' id='addNewLink' value='Confirm'>";
    echo "</td>";
    
    echo "</tr>";
    
    
    echo "<tr>";
    echo "<td>";
    echo "Add file:";
    echo "</td>";
    echo "<td>";
    echo "<input type='file' id='uploadFile' name='uploadFile' />";
    echo "</td>";
    echo "<td>";
    echo "<input type='text' id='textFile' name='textFile' />";
    echo "</td>";
    echo "<td>";
    echo "<input type='text' id='commentFile' name='commentFile' />";
    echo "</td>";
    
    echo "<td>";
    echo " ";
    echo "</td>";
    echo "</tr>";
    
    echo "</table>";
    echo "</form>";
    
    
}

function editLink($id){
    
    global $conn;
    
    $sql = "select * ";
    $sql .= " from DSH07 ";
    $sql .= " where ID07 = " . $id . " ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    echo "<hr />";
    
    echo "<form method='POST' enctype='multipart/form-data' action='linksMaintenance.php'>";
    echo "<table align='center'>";
    
    echo "<tr><th colspan='2'>Edit Link</th></tr>";
    
    
    while (($row = odbc_fetch_array($res)) <> false){
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Name: ";
        echo "</td>";
        echo "<td>";
        echo "<input type='text' id='nameLink' name='nameLink' value='" . trim($row['UFILE07']) . "'/>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Link: ";
        echo "</td>";
        echo "<td>";
        if (trim($row['LINK07']) == "Y"){
            echo "<input type='url' id='linkLink' name='linkLink' value='" . trim($row['FILE07']) . "'/>";
        }else{
            echo "<input type='hidden' id='linkLink' name='linkLink' value='" . trim($row['FILE07']) . "'/>";
            echo trim($row['FILE07']);
        }
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Comment: ";
        echo "</td>";
        echo "<td>";
        echo "<input type='text' id='commentLink' name='commentLink' value='" . trim($row['COMM07']) . "'/>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td colspan='2'>";
        echo " ";
        echo "<input type='hidden' id='linkID' name='linkID' value='" . trim($row['ID07']) . "'/>";
        echo "<input type='hidden' id='typeLink' name='typeLink' value='" . trim($row['LINK07']) . "'/>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td colspan='2'>";
        echo "<input type='submit' id='confirmEdit' name='confirmEdit' value='Confirm changes'>";
        echo "</td>";
        echo "</tr>";
        
    }
    
    echo "</table>";
    echo "</form>";
    
    
}


function deleteLink($id){
    
    global $conn;
    
    $sql = "select * ";
    $sql .= " from DSH07 ";
    $sql .= " where ID07 = " . $id . " ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    echo "<hr />";
    
    echo "<form method='POST' enctype='multipart/form-data' action='linksMaintenance.php'>";
    echo "<table align='center'>";
    
    echo "<tr><th colspan='2'>Delete Link</th></tr>";
    
    
    while (($row = odbc_fetch_array($res)) <> false){
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Name: ";
        echo "</td>";
        echo "<td>";
        echo trim($row['UFILE07']);
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Link: ";
        echo "</td>";
        echo "<td>";
        echo trim($row['FILE07']);
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Comment: ";
        echo "</td>";
        echo "<td>";
        echo trim($row['COMM07']);
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        
        echo "<td colspan='2'>";
        echo " ";
        echo "<input type='hidden' id='linkID' name='linkID' value='" . trim($row['ID07']) . "'/>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td colspan='2'>";
        echo "<input type='submit' id='confirmDelete' name='confirmDelete' value='Confirm delete'>";
        echo "</td>";
        echo "</tr>";
        
    }
    
    echo "</table>";
    echo "</form>";
    
}


function saveFileIFS(){
    
    global $attachementsFolder;
    
    $fileName = $_FILES['uploadFile']['name'];
    
    $uploadFile = $_SESSION['userID']  . "_" . date('Ymd') . "_" . date('him') . "_" . $fileName;
    $uploadFile = str_replace(" ", "_", $uploadFile);
    
    if ($TEST_SITE != "Y") {
        
        if(move_uploaded_file($_FILES['uploadFile']['tmp_name'], $attachementsFolder . $uploadFile)){
            $textFile = $_REQUEST['textFile'];
            if ($textFile == ""){
                $textFile = $fileName;
            }
            $commentFile = $_REQUEST['commentFile'];
            addLinkDSH07($uploadFile, $textFile, $commentFile, 'N');
            
        }
    }
    
}

function saveURL(){
    
    $linkURL = $_REQUEST['linkURL'];
    if ($linkURL <> ""){
        $textURL = $_REQUEST['textURL'];
        if ($textURL == ""){
            $textURL = $linkURL;
        }
        $commentURL = $_REQUEST['commentURL'];
        addLinkDSH07($linkURL, $textURL, $commentURL, 'Y');
    }
}


function addLinkDSH07($link, $description, $comment, $isLink){
    
    global $conn, $CONO;
    
    $ID07    = "(select (max(ID07) + 1) from DSH07)";
    $ATID07  = 0;
    $FILE07  = htmlspecialchars($link, ENT_QUOTES);
    $UFILE07 = htmlspecialchars($description, ENT_QUOTES);
    $DATE07  = date('Ymd');
    $TIME07  = date('His');
    $LINE07  = 1;
    $LINK07  = $isLink;
    $SMKT07  = "";
    $CONO07  = $CONO;
    $USER07  = $_SESSION['userID'];
    $COMM07  = htmlspecialchars($comment, ENT_QUOTES);
    $KEY107  = "";
    $KEY207  = "USER";
    $KEY307  = "";
    $PUBL07  = "Y";
    $PGID07  = "CIL";
    $WBID07  = "DOC";
    $KEY407  = "UPLOADED";
    
    $sql = "insert into DSH07 ";
    $sql .= " values($ID07, $ATID07, '$FILE07', '$UFILE07', $DATE07, ";
    $sql .= "        '$TIME07', $LINE07, '$LINK07', '$SMKT07', '$CONO07', ";
    $sql .= "        $USER07, '$COMM07', '$KEY107', '$KEY207', '$KEY307', ";
    $sql .= "        '$PUBL07', '$PGID07', '$WBID07', '$KEY407') ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
}

function updateLinkDSH07($idLink, $link, $description, $comment, $isLink){
    
    global $conn;
    
    $FILE07  = htmlspecialchars($link, ENT_QUOTES);
    $UFILE07 = htmlspecialchars($description, ENT_QUOTES);
    $DATE07  = date('Ymd');
    $TIME07  = date('His');
    $USER07  = $_SESSION['userID'];
    $COMM07  = htmlspecialchars($comment, ENT_QUOTES);
    
    $sql = "update DSH07 ";
    $sql .= " set USER07 = " . $USER07 . " ";
    $sql .= "   , DATE07 = " . $DATE07 . " ";
    $sql .= "   , TIME07 = '" . $TIME07 . "' ";
    if ($isLink == "Y"){
        $sql .= "   , FILE07 = '" . $FILE07 . "' ";
    }
    if ($UFILE07 <> ""){
        $sql .= "   , UFILE07 = '" . $UFILE07 . "' ";
    }
    $sql .= "   , COMM07 = '" . $COMM07 . "' ";
    $sql .= " where ID07 = " . $idLink . " ";
    
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    //close window:
    echo "<script>";
    echo "  window.opener.location = ('linksMaintenance.php');";
    echo "  window.open('', '_parent', '');";
    echo "  window.close();";
    echo "</script>";
    
}

function deleteLinkDSH07($idLink){
    
    global $conn, $attachementsFolder;
    
    //delete file:
    $sql = "select * ";
    $sql .= " from DSH07 ";
    $sql .= " where ID07 = " . $idLink . " ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    while (($row = odbc_fetch_array($res)) <> false){
        if (trim($row['LINK07']) <> "Y"){
            $filename = $attachementsFolder . trim($row['FILE07']);
            unlink($filename);
        }
    }
    
    //delete link:
    $sql = "delete DSH07 ";
    $sql .= " where ID07 = " . $idLink . " ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    //close window:
    echo "<script>";
    echo "  window.opener.location = ('linksMaintenance.php');";
    echo "  window.open('', '_parent', '');";
    echo "  window.close();";
    echo "</script>";
    
}

?>