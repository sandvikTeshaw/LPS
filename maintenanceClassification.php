<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            maintenanceClassification.php<br>
 * Development Reference:   DI868<br>
 * Description:             maintenanceClassification.php allows system addministrators the ability to add, edit and delete classifications<br>
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  DI932       TS    30/07/2009    Add new functionality for Returns<br>
 *  LP0069      AD    11/03/2019    AutoResolve Tickets
 */
/**
 */
include_once 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

//Added to remove warnings
global $conn, $SITE_TITLE, $action, $type, $attribute, $attributeName, $required, $className, $CHCK09, $optionName, $class, $confirmation, $optionAttribute, $IMG_DIR,
       $attributeType, $precedence, $classification, $responsible, $typeName;

if (!$conn) {
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
<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--
@import url(copysource/styles.css);
-->
</style>
</head>
<?
//headerFrame ( $_SESSION ['name'] );
include_once 'copysource/header.php';
echo "<body>";

if ($_SESSION ['userID']) {
    
    if (! $_SESSION ['classArray'] && ! $_SESSION ['typeArray']) {
        $_SESSION ['classArray'] = get_classification_array ();
        $_SESSION ['typeArray'] = get_typeName_array ();
    }
    //menuFrame ( "MTP" );
    include_once 'copysource/menu.php';
}

$userArray = get_user_list();
?>


<center>
<table width=95% cellpadding='0' cellspacing='0'>
    <TR><TD>&nbsp</TD></TR>
    <TR><TD>&nbsp</TD></TR>
    <?if( $action != "saveType" && $action != "editType" && $action != "editAttributes" && $action != "listAttributes" 
        && $action != "editAttribute" && $action != "saveAttribute" && $action != "addAttribute" 
        && $action!= "updateAttribute" && $action != "deleteAttribute" && $action != "editOption" && $action != "deleteOption" 
        && $action != "addOption" && $action != "updateOption" && $action != "attributeSelection" && $action != "addType"
        && $action != "saveNewType"  && $action != "deleteType" && $action != "continueDeleteType" ){ ?>
    <TR>
        <TD class='title'>Classification Maintenance</TD>
    </TR>
    <?}else{ ?>
    <TR>
        <TD class='title'>Type Maintenance</TD>
    </TR>
    <?}?>
    
</table>
<?if( $action == "" ){?>

    <form method='post' action='maintenanceClassification.php' name='frm'>
<table border='0' width='95%' cellpadding='0' cellspacing='0'>
<tr>
<td colspan='2'><a href="maintenanceClassification.php?action=addClass">[Add Classification]</a>
</td>
</tr>
<?
    $sql = "SELECT CLAS09, ID09, TYPE04, ID04, NAME05 FROM CIL09J02 ORDER BY ID09";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    $rowCount = 0;
    $prev_clas = "";
    while( $row = odbc_fetch_array( $res ) ){
    
        if( $row['ID09'] != $prev_clas ){
            $prev_clas = trim($row['ID09']);
            
        ?>
        <tr><td>&nbsp</td></tr>
        <tr><td>&nbsp</td></tr>
        <TR class='header'>
            <TD class='boldBig' colspan='4'><?echo trim($row['CLAS09']);?></TD>
            <TD width=5% class='right'>
                <a href='maintenanceClassification.php?action=editClassification&classification=<?echo trim($row['ID09']);?>'><img src=<?echo $IMG_DIR;?>/edit.gif border='0'></a>
                <a href='maintenanceClassification.php?action=deleteClassification&classification=<?echo trim($row['ID09']);?>'><img src=<?echo $IMG_DIR;?>/delete.gif border='0'></a>
            </TD>
        </TR>
        <TR>
            <TR class='header'>
            <TD class='header' width=5%>&nbsp</TD>
            <TD class='header'>Type</TD>
          <!-- LP0069   <TD class='header' colspan='2'>Responsible</TD>-->
            <TD class='header' colspan='1'>Responsible</TD><!-- LP0069 -->
            <TD class='header' colspan='1'>AutoResolve</TD><!-- LP0069 -->
            <TD class='header' colspan='1'></TD><!-- LP0069 -->
            
          
        </TR>
        <tr><td colspan='2'><a href='maintenanceClassification.php?action=addType&class=<?echo trim($row['ID09']);?>'>[Add Type]</a></td></tr>
        <?
        }
        if( trim($row['TYPE04']) != "" ){
        $rowCount++;
        if( $rowCount % 2 ){      
            echo "<TR class='alternate'>";
        }else{
            echo "<TR class=''>";
        }  
            echo "<TD>&nbsp</TD>";
            echo "<TD class='bold'>" . trim($row['TYPE04']) . "</TD>"; 
            echo "<TD class='bold'>" . trim($row['NAME05']) . "</TD>"; 
            $autoSql="SELECT COUNT(*) FROM CIL48 WHERE TYPE48=".$row['ID04'];//LP0069
            $autoRes=odbc_prepare( $conn, $autoSql );//LP0069
            odbc_execute( $autoRes );//LP0069
            $autoCount=odbc_fetch_array($autoRes);//LP0069
            if($autoCount['00001']==0)//LP0069
                echo "<TD>&nbsp</TD>";//LP0069
                else echo "<TD class='bold'>AutoResolve</TD>";//LP0069
            ?><TD width=5% class='right'>
                <a href='maintenanceClassification.php?action=listAttributes&type=<?echo trim($row['ID04']);?>'><img src=<?echo $IMG_DIR;?>/post.gif border='0' alt='Edit Attributes'></a>
                <a href='maintenanceClassification.php?action=editType&type=<?echo trim($row['ID04']);?>'><img src=<?echo $IMG_DIR;?>/edit.gif border='0' alt='Edit Type'></a>
                <a href='maintenanceClassification.php?action=deleteType&type=<?echo trim($row['ID04']);?>'><img src=<?echo $IMG_DIR;?>/delete.gif border='0' alt='Delete Type'></a>
            </TD>
            <?
        }
        
    }
 ?>
 <tr><td>&nbsp;</td></tr>
 </table>
 </form>
 </center>
 <?
}else if( $action == "editClassification" ){
    $sql = "SELECT CLAS09 FROM CIL09L02 WHERE ID09=$classification";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='40%' cellpadding='0' cellspacing='0'>";

    while( $row = odbc_fetch_array( $res ) ){
        echo "<TR><TD class='bold'>Classification:</TD></TR>";
        echo "<TR><TD><input type='text' name='className' value='" . trim($row['CLAS09']) . "' size='100'></TD></TR>";
        echo "<input type='hidden' name='action' value='saveClassification'>";
        echo "<input type='hidden' name='classification' value='$classification'>";
    }
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
}elseif( $action == "saveClassification" ){
    $updateClassSql = "UPDATE CIL09 SET CLAS09='" . $className . "' WHERE ID09=$classification";
    $updateClassRes = odbc_prepare( $conn, $updateClassSql );
    odbc_execute( $updateClassRes );
    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Classification has been updated</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='action' value=''>";
    echo "</form>";
}elseif( $action == "editType" ){
    
    $sql = "SELECT TYPE04, RESP04 FROM CIL04L00 WHERE ID04=$type";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    //echo $sql;
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='60%' cellpadding='0' cellspacing='0'>";

    while( $row = odbc_fetch_array( $res ) ){
        echo "<TR><TD class='bold'>Type: </TD><TD><input type='text' name='typeName' value='" . trim($row['TYPE04']) . "' size='75'></TD></TR>";
        echo "<TR><TD class='bold'>Responsible: </TD><TD>";
        echo "<select name='responsible'>";
            show_user_list( $userArray, $row['RESP04'] );
        echo "</select>";
        echo "</TD></TR>";
 //******************** LP0069_AD START ****************************
        ?>
        <tr><td>&nbsp<td><td width = "240px"><td width = "80px"></td></tr>
        <?php
        $sql = "SELECT * FROM CIL48 WHERE TYPE48=$type";
        $res = odbc_prepare( $conn, $sql );
        odbc_execute( $res );
        $autoResArray=[];
        while( $row = odbc_fetch_array( $res ) ){
            $autoResArray["{$row['PRIO48']}"]=$row['HOUR48']; // prio->hours//
        }
        for($i=1;$i<5;$i++){
            $checked="";$disabled="none";$hours=30;
            if(isset($autoResArray["$i"])){
                $hours=$autoResArray["$i"]/24;
                $checked="checked";$disabled="table-cell";
            }
            ?>
            <tr>
            <td><b></b></>Autoresolve Priority <?= $i; ?> </td>
            <td>   <input type="checkbox" onchange="js_updateRange()" id ="ck_prio<?=$i;?>" name ="ck_prio<?=$i;?>" <?=$checked;?> /></td>
            <td class="hiddable_<?=$i;?>" style= "text-align:right; display:<?=$disabled;?>">  <input  type="range" name ="rg_prio<?=$i;?>" id ="rg_prio<?=$i;?>" onchange="js_updateHours()" min="1" max="30" step="1" value="<?=$hours;?>"/></td>          
            <td class="hiddable_<?=$i;?>" style= "text-align:right; display:<?=$disabled;?>"><span id="hours<?=$i;?>"> <?= $hours;?></span> days</td> </tr>
            <?php //
        }//
        ?>
    <script>
		function js_updateRange() {
			for(i=1;i<5;i++){
				var ck=document.getElementById("ck_prio"+i);
				var tdArray=document.getElementsByClassName("hiddable_"+i);				
				 if(ck.checked==false){				 
			     		tdArray[0].style.display="none"; 
			    		tdArray[1].style.display="none"; 
				 	}
				 else {
						tdArray[0].style.display="table-cell"; 
			    		tdArray[1].style.display="table-cell"; 
				 	} 					  
				};
				
		}
	       function js_updateHours() {
	    	   for(i=1;i<5;i++){
					var rg=document.getElementById("rg_prio"+i);
					var h=document.getElementById("hours"+i);
					h.innerHTML=rg.value;
	    	   }
	    	         
	       }
	       
    </script>
    <?php
        
        
 //******************** LP0069_AD END ******************************       
    }
    echo "<input type='hidden' name='action' value='saveType'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
    
}elseif( $action == "saveType" ){
    $updateTypeSql = "UPDATE CIL04 SET TYPE04='" . $typeName . "', RESP04=$responsible WHERE ID04=$type";
    $updateTypeRes = odbc_prepare( $conn, $updateTypeSql );
    odbc_execute( $updateTypeRes );
    //******************** LP0069_AD START ****************************
    $sql="DELETE FROM CIL48 WHERE TYPE48=".$type;//<!-- LP0069_AD -->
    $res=odbc_prepare( $conn, $sql );//<!-- LP0069_AD -->
    odbc_execute( $res);//<!-- LP0069_AD -->
    for ($i = 1; $i < 5; $i++) {//<!-- LP0069_AD -->
        if(${'ck_prio'.$i}=='on'){//<!-- LP0069_AD -->
            $hours=${"rg_prio".$i}*24;//<!-- LP0069_AD -->
            $insertAttributeSql = "INSERT INTO CIL48 VALUES(";//<!-- LP0069_AD -->
            $insertAttributeSql .= get_next_unique_id( FACSLIB, "CIL48", "ID48", "" ) . ", $type,$i ,$hours,0)";//<!-- LP0069_AD -->
            $attribRes = odbc_prepare( $conn, $insertAttributeSql );//<!-- LP0069_AD -->
            odbc_execute( $attribRes );   //<!-- LP0069_AD -->
            //<!-- LP0069_AD -->
        }    //<!-- LP0069_AD -->
    }//<!-- LP0069_AD -->
    //******************** LP0069_AD END ******************************
    
    //echo $updateTypeSql;
    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Type has been updated</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='action' value=''>";
    echo "</form>";
}else if( $action == "listAttributes"){
    $sql = "SELECT TYPE04, NAME07, HTYP07, ATTR07, PREC07, REQD07 FROM CIL04J07 WHERE ID04=$type AND HTYP07<>'' order by PREC07";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    echo "<center>";
    echo "<table border='0' width='70%' cellpadding='0' cellspacing='0'>";
    echo "<TR><TD>&nbsp</TD></TR>";

    echo "<TR><TD class='boldBig' colspan='3'>" . trim($row[0]) . "</TD>";
                echo "<TD class='right' colspan='2'><a href='maintenanceClassification.php?action=addAttribute&type=$type'>Add Attribute</a></TD>";
            echo "</TR>";
    $altCounter=0;
    while( $row = odbc_fetch_array( $res ) ){
        $altCounter++;
        if( $altCounter == 1 ){
            
            echo "<TR class='header'>";
                echo "<TD class='header'>Attribute</TD>";
                echo "<TD class='header'>Attribute Type</TD>";
                echo "<TD class='header'>Required</TD>";
                echo "<TD class='header' colspan='2'>Precision</TD>";
            echo "</TR>";
        }
        
        if( $altCounter %2 ){
            echo "<TR>";
        }else{
            echo "<TR class='alternate'>";
        }

            if( trim($row['HTYP07']) != "DROP"){
                echo "<TD>" . trim($row['NAME07']) . "</TD>";
            }else{
                echo "<TD><a href='maintenanceClassification.php?action=attributeSelection&type=$type&attribute=" . $row['ATTR07'] . "'>" . trim($row['NAME07']) . "</a></TD>";
            }
            echo "<TD>";
                $aType = get_attribute_text( trim($row['HTYP07']));
                echo $aType;
            echo "</TD>";
            if( trim($row['REQD07']) == "Y" ){
                $req = "Yes";
            }else{
                $req = "No";
            }
            echo "<TD>" . trim($req) . "</TD>";
            echo "<TD>" . trim($row['PREC07']) . "</TD>";
            echo "<TD class='right'>";
            ?>
                <a href='maintenanceClassification.php?type=<?echo $type;?>&action=editAttribute&attribute=<?echo trim($row['ATTR07']);?>'><img src=<?echo $IMG_DIR;?>/edit.gif border='0' alt='Edit Attribute'></a>
                <a href='maintenanceClassification.php?type=<?echo $type;?>&action=deleteAttribute&attribute=<?echo trim($row['ATTR07']);?>'><img src=<?echo $IMG_DIR;?>/delete.gif border='0' alt='Delete Attribute'></a>
            <?
            echo "</TD>";
            
        echo "</TR>";
        
    }
    echo "</table>";
    echo "</center>";
}elseif( $action == "editAttribute"){
    $sql = "SELECT NAME07, REQD07, PREC07, HTYP07 FROM CIL07L03 WHERE ATTR07=$attribute";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    //echo $sql;
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='60%' cellpadding='0' cellspacing='0'>";

    while( $row = odbc_fetch_array( $res ) ){
        echo "<TR><TD class='bold'>Attribute:</TD>";
            echo "<TD><input type='text' name='attributeName' value='" . trim($row['NAME07']) . "' size='75'></TD>";
        echo "</TR>";
        echo "<TR>";
            echo "<TD class='bold'>Required:</TD>";
            echo "<TD>";
                echo "<select name='required'>";
                    list_yesNo( trim($row['REQD07']) ); 
                echo "</select>";
            echo "</TD>";
        echo "</TR>";
        echo "<TR>";
            echo "<TD class='bold'>Precedence:</TD>";
            echo "<TD>";
                echo "<select name='precedence'>";
                    select_box_numeric( 1, 30, $row['PREC07'] );
                echo "</select>";
            echo "</TD>";
        echo "</TR>";
        echo "<TR>";
            echo "<TD class='bold'>Attribute Type:</TD>";
            echo "<TD>";
                echo "<select name='attributeType'>";
                    list_attribute_types( trim($row['HTYP07']) );
                echo "</select>";
            echo "</TD>";
        echo "</TR>";
        
        echo "<input type='hidden' name='action' value='updateAttribute'>";
        echo "<input type='hidden' name='type' value='$type'>";
        echo "<input type='hidden' name='attribute' value='$attribute'>";
    }
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
    
}elseif( $action == "updateAttribute"){
    $updateAttributeSql = "UPDATE CIL07 SET NAME07='" . $attributeName . "', REQD07='$required', PREC07=$precedence";
    $updateAttributeSql .= ", HTYP07='$attributeType' WHERE ATTR07=$attribute";
    $updateAttributeRes = odbc_prepare( $conn, $updateAttributeSql );
    odbc_execute( $updateAttributeRes );
    
    //echo $updateAttributeSql;
    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Attribute has been updated</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='action' value='listAttributes'>";
    echo "</form>";
    
}elseif ( $action == "addAttribute" ){
    
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='60%' cellpadding='0' cellspacing='0'>";

        echo "<TR><TD class='bold'>Attribute:</TD>";
            echo "<TD><input type='text' name='attributeName' value='' size='75'></TD>";
        echo "</TR>";
        echo "<TR>";
            echo "<TD class='bold'>Required:</TD>";
            echo "<TD>";
                echo "<select name='required'>";
                    list_yesNo( "N" ); 
                echo "</select>";
            echo "</TD>";
        echo "</TR>";
        echo "<TR>";
            echo "<TD class='bold'>Precedence:</TD>";
            echo "<TD>";
                echo "<select name='precedence'>";
                    select_box_numeric( 1, 10, 1);
                echo "</select>";
            echo "</TD>";
        echo "</TR>";
        echo "<TR>";
            echo "<TD class='bold'>Attribute Type:</TD>";
            echo "<TD>";
                echo "<select name='attributeType'>";
                    list_attribute_types( "" );
                echo "</select>";
            echo "</TD>";
        echo "</TR>";
        
        echo "<input type='hidden' name='action' value='saveAttribute'>";
        echo "<input type='hidden' name='type' value='$type'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
    
}elseif ( $action == "saveAttribute" ){
    
    $classSql = "SELECT CLAS12 FROM CIL12 WHERE TYPE12=$type";
    $classRes = odbc_prepare( $conn, $classSql );
    odbc_execute( $classRes );
    
    while( $classRow = odbc_fetch_array( $classRes ) ){
        $classification = $classRow['CLAS12'];
    }
    
    
    $insertAttributeSql = "INSERT INTO CIL07 VALUES(";
    $insertAttributeSql .= get_next_unique_id( FACSLIB, "CIL07", "ATTR07", "" ) . ", $classification, '$attributeName', '', 'N', '$required'";
    $insertAttributeSql .= ", '$attributeType', $type, $precedence, 0)" ;
    $attribRes = odbc_prepare( $conn, $insertAttributeSql );
    odbc_execute( $attribRes );
    
    //echo $insertAttributeSql;
    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Attribute has been added</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='action' value='listAttributes'>";
    echo "</form>";

}elseif ( $action == "deleteAttribute" ){

    $deleteAttribute = "DELETE FROM CIL07 WHERE ATTR07=$attribute OR PRNT07=$attribute";
    $delRes = odbc_prepare( $conn, $deleteAttribute );
    odbc_execute( $delRes );
    
    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Attribute has been deleted</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='action' value='listAttributes'>";
    echo "</form>";
}elseif ( $action == "attributeSelection" ){
    
    $sql = "SELECT NAME07, HTYP07, ATTR07, PREC07, REQD07 FROM CIL04J07 WHERE ID04=$type AND (ATTR07=$attribute OR PRNT07=$attribute) order by ATTR07";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );

    //echo $sql;
    echo "<center>";
    echo "<table border='0' width='70%' cellpadding='0' cellspacing='0'>";
    echo "<TR><TD>&nbsp</TD></TR>";

    $altCounter=0;
    while( $row = odbc_fetch_array( $res ) ){
        $altCounter++;
        if( $altCounter == 1 ){
            echo "<TR><TD class='boldBig'>" . trim($row['NAME07']) . "</TD>";
                echo "<TD class='right'><a href='maintenanceClassification.php?action=addOption&type=$type&attribute=$attribute'>Add Option</a></TD>";
            echo "</TR>";
            echo "<TR class='header'>";
                echo "<TD class='header' colspan='2'>Option</TD>";
            echo "</TR>";
        }else{
            if( $altCounter %2 ){
                echo "<TR>";
            }else{
                echo "<TR class='alternate'>";
            }
                echo "<TD>" . trim($row['NAME07']) . "</TD>";
                echo "<TD class='right'>";
                ?>
                    <a href='maintenanceClassification.php?attribute=<?echo $attribute;?>&type=<?echo $type;?>&action=editOption&optionAttribute=<?echo trim($row['ATTR07']);?>'><img src=<?echo $IMG_DIR;?>/edit.gif border='0' alt='Edit Attribute'></a>
                    <a href='maintenanceClassification.php?attribute=<?echo $attribute;?>&type=<?echo $type;?>&action=deleteOption&optionAttribute=<?echo trim($row['ATTR07']);?>'><img src=<?echo $IMG_DIR;?>/delete.gif border='0' alt='Delete Attribute'></a>
                <?
                echo "</TD>";
                
            echo "</TR>";
        }
        
    }
    echo "</table>";
    echo "</center>";
}elseif ( $action == "editOption" ){
    $sql = "SELECT NAME07, ATTR07 FROM CIL07L03 WHERE ATTR07=$optionAttribute";
    $res = odbc_prepare( $conn, $sql );
    odbc_execute( $res );
    
    
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='60%' cellpadding='0' cellspacing='0'>";

    while( $row = odbc_fetch_array( $res ) ){
        echo "<TR><TD class='bold'>Option:</TD>";
            echo "<TD><input type='text' name='optionName' value='" . trim($row['NAME07']) . "' size='75'></TD>";
        echo "</TR>";
        
    }
    echo "<input type='hidden' name='optionAttribute' value='$optionAttribute'>";
    echo "<input type='hidden' name='action' value='updateOption'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='attribute' value='$attribute'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    
    
}elseif ( $action == "deleteOption" ){
    
    $deleteAttribute = "DELETE FROM CIL07 WHERE ATTR07=$optionAttribute";
    $delRes = odbc_prepare( $conn, $deleteAttribute );
    odbc_execute( $delRes );
    
    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Option has been deleted</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='attribute' value='$attribute'>";
    echo "<input type='hidden' name='action' value='attributeSelection'>";
    echo "</form>";
    
    

}elseif ( $action == "addOption" ){
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='60%' cellpadding='0' cellspacing='0'>";
        echo "<TR><TD class='bold'>Option:</TD>";
            echo "<TD><input type='text' name='optionName' value='' size='75'></TD>";
        echo "</TR>";
    echo "<input type='hidden' name='action' value='saveOption'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='attribute' value='$attribute'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    
}elseif ( $action == "updateOption" ){
    $updateSql = "UPDATE CIL07 SET NAME07='" . $optionName . "' WHERE ATTR07=$optionAttribute";
    $res = odbc_prepare( $conn, $updateSql );
    odbc_execute( $res );

    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Option has been updated</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='attribute' value='$attribute'>";
    echo "<input type='hidden' name='action' value='attributeSelection'>";
    echo "</form>";
}elseif ( $action == "saveOption" ){
    
    $classSql = "SELECT CLAS12 FROM CIL12 WHERE TYPE12=$type";
    $classRes = odbc_prepare( $conn, $classSql );
    odbc_execute( $classRes );
    
    while( $classRow = odbc_fetch_array( $classRes ) ){
        $classification = $classRow['CLAS12'];
    }
    
    
    $insertAttributeSql = "INSERT INTO CIL07 VALUES(";
    $insertAttributeSql .= get_next_unique_id( FACSLIB, "CIL07", "ATTR07", "" ) . ", $classification, '$optionName', '', '', ''";
    $insertAttributeSql .= ", '', $type, 0, $attribute )" ;
    $attribRes = odbc_prepare( $conn, $insertAttributeSql );
    odbc_execute( $attribRes );

    echo "<center>";
    echo "<form method='get' action='maintenanceClassification.php'>";
    echo "<br><br><b>Option has been added</b>";
    echo "<br><input type='submit' value='Continue'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<input type='hidden' name='attribute' value='$attribute'>";
    echo "<input type='hidden' name='action' value='attributeSelection'>";
    echo "</form>";

}elseif( $action == "addClass" ){
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='30%' cellpadding='0' cellspacing='0'>";
    echo "<TR><TD class='bold'>Classification:</TD></TR>";
    echo "<TR><TD><input type='text' name='className' value='' size='100'></TD></TR>";
    echo "<TR><TD class='bold'>Has Checklist:</TD></TR>";
    echo "<TR><TD>";
        echo "<select name=CHCK09>";
            echo "<option value='' SELECTED>Select Option</option>";
            list_yesNo( "" );
        echo "</select>";
    echo "</TD></TR>";
    echo "<input type='hidden' name='action' value='saveNewClassification'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
    
}elseif ( $action == "saveNewClassification" ){
    
    $cil09InsertSql = "INSERT INTO CIL09 values(";
    $cil09InsertSql .= get_next_unique_id( FACSLIB, "CIL09", "ID09", "" ) . ", '$className', 0, '$CHCK09')";

    $res = odbc_prepare( $conn, $cil09InsertSql );
    odbc_execute( $res );
    
    echo "<center>";
        echo "<br><br>Classification has been added<br><br>";
        ?><a href="javascript:history.go(-2)">Continue</a><?
    echo "</center>";
    
}elseif ($action == "addType"){
    
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='60%' cellpadding='0' cellspacing='0'>";
    echo "<TR><TD class='bold'>Type: </TD><TD><input type='text' name='typeName' value='' size='100'></TD></TR>";
    echo "<TR><TD class='bold'>Responsible: </TD><TD>";
    echo "<select name='responsible'>";
        show_user_list( $userArray, $row[1] );
    echo "</select>";
    echo "</TD></TR>";
    echo "<input type='hidden' name='action' value='saveNewType'>";
    echo "<input type='hidden' name='class' value='$class'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
    
}elseif ( $action == "saveNewType" ){
    
    $ID04 = get_next_unique_id( FACSLIB, "CIL04", "ID04", "" );
    $cil04InsertSql = "INSERT INTO CIL04 values(";
    $cil04InsertSql .=  $ID04 . ", 0, '$typeName', $responsible)";
    
    $res = odbc_prepare( $conn, $cil04InsertSql );
    odbc_execute( $res );
    
    $cil12InsertSql = "INSERT INTO CIL12 VALUES(";
    $cil12InsertSql .= $class . ", $ID04)";
    
    $res12 = odbc_prepare( $conn, $cil12InsertSql );
    odbc_execute( $res12 );
    
    
    echo "<center>";
        echo "<br><br>Type has been added<br><br>";
        ?><a href="javascript:history.go(-2)">Continue</a><?
    echo "</center>";
    
}elseif ( $action == "deleteType" ){
    
    $typeName = get_type_name( $type );
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='30%' cellpadding='0' cellspacing='0'>";
    echo "<tr><td class='bold'>Deletion Confirmation</td></tr>";
    echo "<TR><TD class='bold'>Type: $typeName</TD></TR>";
    echo "<TR><TD>";
        echo "<select name=confirmation>";
            list_yesNo( "N" );
        echo "</select>";
    echo "</TD></TR>";
    
    echo "<input type='hidden' name='action' value='continueDeleteType'>";
    echo "<input type='hidden' name='type' value='$type'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
    
}elseif ($action == "continueDeleteType"){

    if( trim($confirmation) == "Y"){
        $deleteSql = "DELETE FROM CIL04 WHERE ID04=" . trim($type);
        $resDel = odbc_prepare( $conn, $deleteSql );
        
        $deleteSql12 = "DELETE FROM CIL12 WHERE TYPE12=" . trim($type);
        $res12 = odbc_prepare( $conn, $deleteSql12 );

        odbc_execute( $resDel );
        odbc_execute( $res12 );

        
        echo "<center>";
        echo "<br><br>Type has been deleted<br><br>";
        ?><a href="javascript:history.go(-2)">Continue</a><?
        echo "</center>";
        
    }else{
        echo "<center>";
        echo "<br><br>Type has <i>Not</i> been deleted<br><br>";
        ?><a href="javascript:history.go(-2)">Continue</a><?
        echo "</center>";   
    }
}elseif ( $action == "deleteClassification" ){
    $className = get_class_name( $classification );
    echo "<center>";
    echo "<form method='post' action='maintenanceClassification.php' name='frm'>";
    echo "<table border='0' width='30%' cellpadding='0' cellspacing='0'>";
    echo "<tr><td class='bold'>Deletion Confirmation</td></tr>";
    echo "<TR><TD class='bold'>Classification: $className</TD></TR>";
    echo "<TR><TD>";
        echo "<select name=confirmation>";
            list_yesNo( "N" );
        echo "</select>";
    echo "</TD></TR>";
    
    echo "<input type='hidden' name='action' value='continueDeleteClass'>";
    echo "<input type='hidden' name='class' value='$classification'>";
    echo "<TR><TD>&nbsp</TD></TR>";
    echo "<tr><TD><input type='submit' value='Continue'>";
    echo "</table>";
    echo "</form>";
    echo "</center>";
}elseif ($action == "continueDeleteClass"){
    if( trim($confirmation) == "Y"){
        $deleteSql = "DELETE FROM CIL09 WHERE ID09=" . trim($class);
        $resDel = odbc_prepare( $conn, $deleteSql );

        odbc_execute( $resDel );
        
        echo "<center>";
        echo "<br><br>Classification has been deleted<br><br>";
        ?><a href="javascript:history.go(-2)">Continue</a><?
        echo "</center>";
        
    }else{
        echo "<center>";
        echo "<br><br>Classification has <i>Not</i> been deleted<br><br>";
        ?><a href="javascript:history.go(-2)">Continue</a><?
        echo "</center>";   
    }
}


?>
    