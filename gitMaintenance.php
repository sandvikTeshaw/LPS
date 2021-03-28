<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            gitMaintenance.php<br>
 * Development Reference:   LP0052<br>
 * Description:             Global  Inventory Team - person list maintenance<br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *   LP0052     AD    26/10/2018  Create new LPS ticket type �Supersession�)*
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
    
    updateLinkCIL22($_REQUEST['linkID'], $_REQUEST['linkLink'], $_REQUEST['nameLink'], $_REQUEST['commentLink'], $_REQUEST['typeLink']);
    
    
}elseif($_REQUEST['action'] == "delete"){
    
    deleteLink($_REQUEST['linkID']);
    
}elseif(isset($_REQUEST['confirmDelete'])){
    
    deleteLinkCIL22($_REQUEST['linkID']);
    
    
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
    
    saveLink();
    
    echo "<br />";
    echo "<center> Global  Inventory Team Member List Maintenance </center>";
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
    $sql .= " from CIL22 JOIN HLP05 ON USER22=ID05";
    $sql .= " where ASCII(LEVL22)>64 ";//A
    $sql .= "   and ASCII(LEVL22)<97 ";//a
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    
    echo "<table class='data-table'>";
    
    echo "<tr>";
    echo "<th> Name </th>";
    echo "<th> Role </th>";
    echo "<th> Action </th>";
    echo "</tr>";
    
    while (($row = odbc_fetch_array($res)) <> false){
        
        echo "<tr>";
        
        echo "<td>";
        echo trim($row['NAME05']);
        echo "</td>";
        
        echo "<td>";
        echo trim($row['TITL22']);
        echo "</td>";
        
        
        echo "<td style='width: 60px; text-align: center;'>";
  //     echo " <a href='gitMaintenance.php?linkID=" . trim($row['LEVL22']) . "&action=edit' target='_new'>";
  //      echo "   <img src='" . $IMG_DIR . "/edit.gif' border=0 alt='Edit Role' />";
  //      echo " </a>";
  //      echo " &nbsp; ";
        echo " <a href='gitMaintenance.php?linkID=" . trim($row['LEVL22']) . "&action=delete' target='_new'>";
        echo "   <img src='" . $IMG_DIR . "/delete.gif' border=0 alt='Delete' />";
        echo " </a>";
        
        echo "</td>";
        
        echo "</tr>";
        
    }
    
    echo "</table>";
    
    
}


function addLink(){
    global $conn;
    
    echo "<form method='POST' enctype='multipart/form-data' action='gitMaintenance.php'>";
    echo "<table style='width: auto; margin-left: 1%;'>";
    
    echo "<tr>";
    echo "<td> </td>";
    echo "<td> New GIT Member </td>";
    echo "<td> Role  </td>";
    echo "<td> </td>";
    echo "</tr>";
    
    
    echo "<tr>";
    echo "<td>";
    echo "Add Member:";
    echo "</td>";
    //-------- user list selector
    $sqlU = "select ID05,NAME05 ";
    $sqlU .= " from  HLP05 ";
    $sqlU .= " where DEL05 <> 'Y' ";
    $sqlU .= " order by NAME05 ";
    //echo $sqlU;
    $resU = odbc_prepare($conn, $sqlU);
    odbc_execute($resU);
    ?>
 <td>
  <select name="comboUsers" id="comboUsers">
  <option value="none" >Select New Team Member</option>
 <?php 
     while($row = odbc_fetch_array($resU)){           
 ?>   
   <option value="<?php echo trim($row['ID05']); ?>" > <?php echo trim($row['NAME05']); ?></option>
 <?php } ?>
  </select>
  </td>
 <?php 
 
 //---------------------------   
    echo "<td>";
    echo "<input type='text' id='role' name='role' />";
    echo "</td>";
    
    echo "<td rowspan = '2'>";
    echo "<input type='submit' id='addNewLink' value='Confirm'>";
    echo "</td>";
    
    echo "</tr>";
    
    
    echo "</table>";
    echo "</form>";
    
    
}

function editLink($id){
    
    global $conn;
    
    $sql = "select * ";
    $sql .= " from CIL22 JOIN HLP05 ON USER22=ID05";
    $sql .= " where LEVL22 = '" . $id . "' ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    echo "<hr />";
    
    echo "<form method='POST' enctype='multipart/form-data' action='gitMaintenance.php'>";
    echo "<table align='center'>";
    
    echo "<tr><th colspan='2'>Edit Role</th></tr>";
    
    
    while (($row = odbc_fetch_array($res)) <> false){
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Name: ";
        echo "</td>";
        echo "<td>";
        echo "<input type='text' readonly id='nameLink' name='nameLink' value='" . trim($row['NAME05']) . "'/>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Comment: ";
        echo "</td>";
        echo "<td>";
        echo "<input type='text' id='commentLink' name='commentLink' value='" . trim($row['TITL22']) . "'/>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td colspan='2'>";
        echo " ";
        echo "<input type='hidden' id='linkID' name='linkID' value='" . trim($row['LEVL22']) . "'/>";
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
    $sql .= " from CIL22 JOIN HLP05 ON USER22=ID05 ";
    $sql .= " where LEVL22 = '" . $id . "' ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
    echo "<hr />";
    
    echo "<form method='POST' enctype='multipart/form-data' action='gitMaintenance.php'>";
    echo "<table align='center'>";
    
    echo "<tr><th colspan='2'>Delete Link</th></tr>";
    
    
    while (($row = odbc_fetch_array($res)) <> false){
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Name: ";
        echo "</td>";
        echo "<td>";
        echo trim($row['NAME05']);
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td class='titleBig' style='text-align:right;'>";
        echo " Role: ";
        echo "</td>";
        echo "<td>";
        echo trim($row['TITL22']);
        echo "</td>";
        echo "</tr>";
        

        
        echo "<td colspan='2'>";
        echo " ";
        echo "<input type='hidden' id='linkID' name='linkID' value='" . trim($row['LEVL22']) . "'/>";
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




function saveLink(){
    
    $link = $_REQUEST['comboUsers'];
    if ($link <> "none" && $link!=null){
        $role = $_REQUEST['role'];
        addLinkCIL22($link, $role);
    }
}


function addLinkCIL22($link, $role){
    
    global $conn, $CONO;
    
    $newID='A';
    $sql   = "select (max(ASCII(LEVL22)))as MAXID from CIL22 where ASCII(LEVL22)>64 and ASCII(LEVL22)<89";
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    while ($row = odbc_fetch_array($res)){
        //var_dump($row);
        if($row['MAXID']!=null)
            if($row['MAXID']<90)
            {
                $newID=chr(($row['MAXID'])+1);
            }
            else {
                echo "No more space for a new Team member!!!";
                return 1;
            }
    }
    $sql = "insert into CIL22 ";
    $sql .= " values('$newID','$role',$link) ";
   // echo $sql;
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    
}

function updateLinkCIL22($idLink, $link, $description, $comment, $isLink){
    
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
    echo "  window.opener.location = ('gitMaintenance.php');";
    echo "  window.open('', '_parent', '');";
    echo "  window.close();";
    echo "</script>";
    
}

function deleteLinkCIL22($idLink){
    
    global $conn, $attachementsFolder;
    

    //delete link:
    $sql = "delete from CIL22 ";
    $sql .= " where LEVL22 = '" . $idLink . "' ";
    
    $res = odbc_prepare($conn, $sql);
    odbc_execute($res);
    if ($idLink!='A') //defragmentation
        {
            $sqld   = "select (max(ASCII(LEVL22)))as MAXID from CIL22 where ASCII(LEVL22)>64 and ASCII(LEVL22)<89";
            $resd = odbc_prepare($conn, $sqld);
            odbc_execute($resd);
            while ($rowd = odbc_fetch_array($resd)){
                //var_dump($rowd);
            $maxID=$rowd['MAXID'];
            if($maxID>ord($idLink))
              {
                  $sqlu= "UPDATE CIL22 SET LEVL22='".$idLink."' WHERE LEVL22='".chr($maxID)."'"; 
                  $resu = odbc_prepare($conn, $sqlu);
                  odbc_execute($resu);
              }
            }
        }
    
    
    //close window:
    echo "<script>";
    echo "  window.opener.location = ('gitMaintenance.php');";
    echo "  window.open('', '_parent', '');";
    echo "  window.close();";
    echo "</script>";
    
}

?>