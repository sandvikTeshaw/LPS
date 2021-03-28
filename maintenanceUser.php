<?
/**
 * System Name:			    Logistics Process Support
 * Program Name: 			maintenanceUsers.php<br>
 * Development Reference:	DI868<br>
 * Description:				maintenanceUsers.php listing of users with links to maintenance pages such as add, edit and delete<br>
 * 
 * 	MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 * 	--------  ------  ----------  ----------------------------------<br>
 *  D0097     TJS     12/04/2010  re-write userMaintenance for new escalation<br>
 *	LP00013		IS	  04/05/2016  
 *	LP0035      KS    31/01/2018  GLBAU-14397 LPS - Updated User Maintenance Screen View
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

if (isset($_SESSION ['email'])) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_SESSION ['email'] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_SESSION ['email'];
	
	if (!isset($_COOKIE ["mtp"])) {
		setcookie ( "mtp", $_SESSION ['email'], time () + 60 * 60 * 24 * 30 );
	}

} elseif( isset($_COOKIE ["mtp"] )) {
	
	$userInfo [] = "";
	$userInfo = user_cookie_info ( $_COOKIE ["mtp"] );
	$_SESSION ['userID'] = $userInfo ['ID05'];
	$_SESSION ['name'] = $userInfo ['NAME05'];
	$_SESSION ['companyCode'] = $userInfo ['CODE05'];
	$_SESSION ['email'] = $_COOKIE ["mtp"];
}

//set $productArea and $subProductArea                                                          //**LP0035
$sql2 = " select * ";                                                                           //**LP0035
$sql2 .= " from HLP06 ";                                                                        //**LP0035
$sql2 .= " where ATTR06 = 'Product Area' ";                                                     //**LP0035
$res2 = odbc_prepare($conn, $sql2);                                                              //**LP0035
odbc_execute($res2);                                                                             //**LP0035
$productArea = 0;                                                                               //**LP0035
while($row2 = odbc_fetch_array($res2)){                                                          //**LP0035
    $productArea = $row2['ID06'];                                                               //**LP0035
}                                                                                               //**LP0035
$sql2 = " select * ";                                                                           //**LP0035
$sql2 .= " from HLP06 ";                                                                        //**LP0035
$sql2 .= " where ATTR06 = 'Sub PA' ";                                                           //**LP0035
$sql2 .= "    or ATTR06 = 'Sub Product Area' ";                                                 //**LP0035
$res2 = odbc_prepare($conn, $sql2);                                                              //**LP0035
odbc_execute($res2);                                                                             //**LP0035
$subProductArea = 0;                                                                            //**LP0035
while($row2 = odbc_fetch_array($res2)){                                                          //**LP0035
    $subProductArea = $row2['ID06'];                                                            //**LP0035
}                                                                                               //**LP0035


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
<script type="text/javascript">
	function setEmail( tvalue ){
		
		var emailVar = tvalue.toLowerCase(); 
		emailVar = emailVar.replace(" ", ".");
		this.detailsForm.newEmail.value = emailVar + "@sandvik.com";
	}
	function validateEntry(){
		if( this.detailsForm.newEmail.value == "" ){
			alert( "You Must Enter An Email Address" );
			return false;
		}
		if( this.detailsForm.userName.value == "" ){
			alert( "You Must Enter A Name" );
			return false;
		}
		return true;

	}

	function deleteUser( employeeNumber, employeeName, requestType ){

			if( requestType == "delete" ){
				var answer = confirm( "Delete " + employeeName + "?" );
	
				if( answer )
					window.open ( "maintenanceUserDelete.php?action=delete&empId=" + employeeNumber, "" );
				else
					alert( employeeName + " has not been deleted" )
			}else{
				var answer = confirm( "Request Deletion of " + employeeName + "?" );
			
				if( answer )
					window.open ( "maintenanceUserDelete.php?action=request&empId=" + employeeNumber, "" );
				else
					alert( employeeName + " has not been deleted" )
			}

				
				
	}
	
	function removeUser( employeeNumber, employeeName ){
		var answer = confirm( "Remove " + employeeName + " from group?" );

		if( answer )
			window.open ( "maintenanceUserRemove.php?empId=" + employeeNumber, "" );
		else
			alert( employeeName + " has not been removed" )
			
	}

	function changeSuper( employeeNumber, superId ){

		
		window.open ( "maintenanceUserChangeSuper.php?empId=" + employeeNumber + "&superId=" + superId, "" );
	}

	function filterTable01() {																		//**LP0035
		var filterUser, filterSupervisor, filterAuthority, filterStatus, table, tr, td, i;			//**LP0035
		var filterPA, filterSubPA, filterLastLogon;													//**LP0035
		var tempValue;																				//**LP0035
		filterUser = document.getElementById("searchUserName").value;								//**LP0035
		filterSupervisor = document.getElementById("comboSupervisor").value;						//**LP0035
		filterAuthority = document.getElementById("comboAuthority").value;							//**LP0035
		filterStatus = document.getElementById("comboStatus").value;								//**LP0035
		filterPA = document.getElementById("comboPA").value;										//**LP0035
		filterSubPA = document.getElementById("comboSubPA").value;									//**LP0035
		filterLastLogon = document.getElementById("searchLastLogon").value;							//**LP0035
		table = document.getElementById("table01");													//**LP0035
		tr = table.getElementsByTagName("tr");														//**LP0035
		//** first data row is fourth (i=3)															//**LP0035
		for (i = 3; i < tr.length; i++){															//**LP0035
			tr[i].style.display = "";																//**LP0035
			td = tr[i].getElementsByTagName("td")[0]; //User										//**LP0035
			if (td){																				//**LP0035
				// after 4x "&nbsp;" -> from index 24												//**LP0035
        		if (td.innerHTML.toUpperCase().indexOf(filterUser.toUpperCase(), 24) <= -1){		//**LP0035
					tr[i].style.display = "none";													//**LP0035
				}																					//**LP0035
			} 																						//**LP0035
			td = tr[i].getElementsByTagName("td")[1]; //Supervisor									//**LP0035 
			if (td){																				//**LP0035
				if (filterSupervisor != '***all***'){												//**LP0035
        			if (td.getElementsByTagName("input")[0].value.trim() != filterSupervisor){		//**LP0035
						tr[i].style.display = "none";												//**LP0035
				  	}																				//**LP0035
				}																					//**LP0035
			} 																						//**LP0035
			td = tr[i].getElementsByTagName("td")[2]; //Authority									//**LP0035 
			if (td){																				//**LP0035
				if (filterAuthority != '***all***'){												//**LP0035
				    if (td.innerHTML != filterAuthority){											//**LP0035
						tr[i].style.display = "none";												//**LP0035
        		    }																				//**LP0035
				}																					//**LP0035
			} 																						//**LP0035
			td = tr[i].getElementsByTagName("td")[3]; //Status										//**LP0035
			if (td){																				//**LP0035
        		if (filterStatus != '***all***'){													//**LP0035
        			if (td.innerHTML != filterStatus){												//**LP0035
						tr[i].style.display = "none";												//**LP0035
        			}																				//**LP0035
				}																					//**LP0035
			} 																						//**LP0035

			td = tr[i].getElementsByTagName("td")[4]; //Last Logon									//**LP0035
			if (td){																				//**LP0035
        		if (td.innerHTML.toUpperCase().indexOf(filterLastLogon.toUpperCase()) <= -1){		//**LP0035
					tr[i].style.display = "none";													//**LP0035
				}																					//**LP0035
			} 																						//**LP0035
			td = tr[i].getElementsByTagName("td")[5]; //PA  										//**LP0035
			if (td){																				//**LP0035
        		if (filterPA != '***all***'){			 											//**LP0035
            		tempValue = td.innerHTML; 														//**LP0035
            		tempValue = tempValue.replace(/&amp;/g, "&"); 									//**LP0035
        			if (tempValue != filterPA){						 	        					//**LP0035
						tr[i].style.display = "none";												//**LP0035
        			}																				//**LP0035
				}																					//**LP0035
			} 																						//**LP0035
			td = tr[i].getElementsByTagName("td")[6]; //Sub PA										//**LP0035
			if (td){																				//**LP0035
        		if (filterSubPA != '***all***'){													//**LP0035
        			tempValue = td.innerHTML; 														//**LP0035
        			tempValue = tempValue.replace(/&amp;/g, "&"); 									//**LP0035
        			if (tempValue != filterSubPA){													//**LP0035
						tr[i].style.display = "none";												//**LP0035
        			}																				//**LP0035
				}																					//**LP0035
			} 																						//**LP0035

			
		} //for i																					//**LP0035
	}																								//**LP0035
	
	function sortTable01(column, ascend){																					//**LP0035
		var table, rows, i, arr;																							//**LP0035
		table = document.getElementById("table01");																			//**LP0035
		arr = new Array();																									//**LP0035
		rows = table.getElementsByTagName("TR");																			//**LP0035
																															//**LP0035
		for (i = 3; i < rows.length; i++) {																					//**LP0035
			arr[i-3] = new Array();																							//**LP0035
			if (column == 1){																								//**LP0035
				arr[i-3][0] = rows[i].getElementsByTagName("TD")[column].getElementsByTagName("input")[0].value.trim();		//**LP0035
			}else{																											//**LP0035
				arr[i-3][0] = rows[i].getElementsByTagName("TD")[column].innerHTML.toLowerCase();							//**LP0035
			}																												//**LP0035
			arr[i-3][1]  = rows[i].innerHTML;																				//**LP0035
		}																													//**LP0035
																															//**LP0035
		arr.sort(function(a, b){return (a[0] == b[0] ? 0: ((a[0] > b[0]) ? (1*ascend): (-1*ascend)))});						//**LP0035
																															//**LP0035
		for (i = 3; i < rows.length; i++) {																					//**LP0035
			rows[i].innerHTML = arr[i-3][1];																				//**LP0035
		}																													//**LP0035
																															//**LP0035
		filterTable01();																									//**LP0035
																															//**LP0035
	}																														//**LP0035
	
</script>
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

ob_start ( 'compressBuffer' );
?>
<center>
<table width=95% cellpadding='0' cellspacing='0'>
	<TR>
		<TD>&nbsp</TD>
	</TR>
<?
if( !isset( $action ) ){
    $action = "";
}
if(  $action != "password"){ ?>
	<TR>
		<TD class='title'>User Maintenance</TD>
	</TR>

<?
}else{
?>
	<TR>
		<TD class='title'>Lost Password</TD>
	</TR>
	
<?
}

?>
</table>
<?
if( !isset( $action ) || $action == "" || $action == "start" ){
	
	$sql = "SELECT * FROM CIL31J01 WHERE DEL05 <> 'Y'";
	
	if( $_SESSION['authority'] != "S" ){
		$sql .= " AND ( SUPR31 = {$_SESSION ['userID']} OR SUPR31 = 0 )";
	}
	$sql .= " ORDER by NAME05, ENAM05";
	
	$res = odbc_prepare( $conn, $sql );
	odbc_execute( $res );
	//LP00013
	?>
	<form method='post' action='maintenanceUser.php' name='frm'>
	<!-- //**LP0035  <table border='0' width='95%' cellpadding='0' cellspacing='0'>  -->
	<table id='table01' border='0' width='95%' cellpadding='0' cellspacing='0'> 	<!-- //**LP0035 -->
	<TR>
    <TD><a href='maintenanceUserAdd.php'><b>[Register User]</b></a> &nbsp;&nbsp;
    <a href='maintenanceGroup.php'><b>[Group Maintenance]</b></a></TD>
    </TR>
	<TR class='header'>
		<TD class='header'>User Name</TD>
		<TD class='header'>Supervisor</TD>
		<TD class='header'>Authority</TD>
		<TD class='header'>Status</TD>
		<TD class='header'>Last Log On</TD>   <!-- LP0035  -->
		<TD class='header'>PA</TD>            <!-- LP0035  -->
		<TD class='header'>Sub PA</TD>        <!-- LP0035  -->
		<TD class='header' width='5%'>Action</TD>
	</TR>
	
	<?
	
	$authArray = authority_array();

	echo "<TR class='header'>";                                                                                    //**LP0035
	echo "  <TD class='header'>";                                                                                  //**LP0035
	echo "    <INPUT type='text' id='searchUserName' onkeyup='filterTable01()' placeholder='Filter'></INPUT>";     //**LP0035
	echo "    <br />";                                                                                             //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(0, 1)'>Sort A-Z</A>";                              //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(0, -1)'>Sort Z-A</A>";                             //**LP0035
	echo "  </TD>";                                                                                                //**LP0035
	echo "  <TD class='header'>";                                                                                  //**LP0035
	          comboSupervisor();                                                                                   //**LP0035
	echo "    <br />";                                                                                             //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(1, 1)'>Sort A-Z</A>";                              //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(1, -1)'>Sort Z-A</A>";                             //**LP0035
	echo "  </TD>";                                                                                                //**LP0035
	echo "  <TD class='header'>";                                                                                  //**LP0035
	          comboAuthority($authArray);                                                                          //**LP0035
	echo "    <br />";                                                                                             //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(2, 1)'>Sort A-Z</A>";                              //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(2, -1)'>Sort Z-A</A>";                             //**LP0035
	echo "  </TD>";                                                                                                //**LP0035
	echo "  <TD class='header'>";                                                                                  //**LP0035
	          comboStatus();                                                                                       //**LP0035
    echo "    <br />";                                                                                             //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(3, 1)'>Sort A-Z</A>";                              //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(3, -1)'>Sort Z-A</A>";                             //**LP0035
	echo "  </TD>";                                                                                                //**LP0035
	
	echo "  <TD class='header'>";                                                                                  //**LP0035
	echo "    <INPUT type='text' id='searchLastLogon' onkeyup='filterTable01()' placeholder='Filter'></INPUT>";    //**LP0035
	echo "    <br />";                                                                                             //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(4, 1)'>Sort A-Z</A>";                              //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(4, -1)'>Sort Z-A</A>";                             //**LP0035
	echo "  </TD>";                                                                                                //**LP0035
	echo "  <TD class='header'>";                                                                                  //**LP0035
           	  comboPA();                                                                                           //**LP0035
	echo "    <br />";                                                                                             //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(5, 1)'>Sort A-Z</A>";                              //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(5, -1)'>Sort Z-A</A>";                             //**LP0035
	echo "  </TD>";                                                                                                //**LP0035
	echo "  <TD class='header'>";                                                                                  //**LP0035
              comboSubPA();                                                                                        //**LP0035
	echo "    <br />";                                                                                             //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(6, 1)'>Sort A-Z</A>";                              //**LP0035
	echo "    <A href='javascript:void(0)' onclick='sortTable01(6, -1)'>Sort Z-A</A>";                             //**LP0035
	echo "  </TD>";                                                                                                //**LP0035
	
	echo "  <TD class='header'></TD>";                                                                             //**LP0035
	echo "</TR>";                                                                                                  //**LP0035
	
	$rowCount = 0;
	while( $row = odbc_fetch_array( $res ) ){
		//**LP0035  if( $prev_super != $row['SUPR31'] ){
			?>
		<!-- //**LP0035		<tr><td>&nbsp;</td></tr>      //-->
		<!-- //**LP0035		<tr class='header'>           //-->
		<!-- //**LP0035			<td colspan='6' class='bold'><?php //**LP0035  echo $row['NAME05'];?>    //-->
		<!-- //**LP0035				<a href='changeSupervisor.php?superId=<?php //**LP0035  echo $row['SUPR31']?>' title="Change Supervisor for entire group">	 //-->
		<!-- //**LP0035					(Change Supervisor)	 //-->
		<!-- //**LP0035				</a>	 //-->
		<!-- //**LP0035			</td>	 //-->
		<!-- //**LP0035		</tr>	 //-->
			<?php
		//**LP0035	$prev_super = $row['SUPR31'];
		//**LP0035	$rowCount = 0;
		//**LP0035  }
	
		$rowCount++;
		//if( $rowCount % 10 == 0 ){
		    ob_end_flush();
		    ob_start();
		//}
		
    	if( $rowCount % 2 ){      
			echo "<TR class='alternate'>";
		}else{
			echo "<TR class=''>";
		}
		
		
		?>
			<td width=30%>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row['ENAM05'];?></td>
			<td><input type='text' name='supr_<?php echo $row['EMPL31'];?>' id='supr_<?php echo $row['EMPL31'];?>' value='<?php echo $row['NAME05'];?>' size='30' class='medium' onclick='changeSuper(<?php echo trim($row['EMPL31']);?>, <?php echo trim($row['SUPR31']);?>)' title='click to change supervisor'>
			</td>
			<td><?php 
				foreach ($authArray as $authorities) {
					if( $authorities['value'] == trim($row['AUTH05']) ){
						echo $authorities['description'];
						break;
					}
				}?>
			</td>
			<TD><?php 
				if( trim($row['AVAL05']) == "Y" ){
					echo "Available";
				}else{
					echo "Out";
				}?>
			</TD>
			
			
			<?php                                                                                           //**LP0035                        
		    //**Last Log On:                                                                                //**LP0035
			$sql2 = " select * ";                                                                           //**LP0035
			$sql2 .= " from HLP05 ";                                                                        //**LP0035
			$sql2 .= " where ID05 = " . $row['EMPL31'] . " ";                                               //**LP0035
                                                                                                         	//**LP0035
            $res2 = odbc_prepare($conn, $sql2);                                                              //**LP0035
            odbc_execute($res2);                                                                             //**LP0035
                                                                                                            //**LP0035
            $lastLogOn = "";                                                                                //**LP0035
            while($row2 = odbc_fetch_array($res2)){                                                          //**LP0035
                $lastLogOn = substr($row2['LOGT05'], 0, 10);                                                //**LP0035
                $lastLogOn .= " " . str_replace(".", ":", substr($row2['LOGT05'], 11, 8));                  //**LP0035
            }                                                                                               //**LP0035
                                                                                                            //**LP0035
            echo "<TD>";                                                                                    //**LP0035
            echo $lastLogOn;                                                                                //**LP0035
            echo "</TD>";                                                                                   //**LP0035
                                                                                                            //**LP0035
            //** PA:                                                                                        //**LP0035
            $sql2 = " select * ";                                                                           //**LP0035
            $sql2 .= " from HLP08 ";                                                                        //**LP0035
            $sql2 .= "  inner join HLP07 ";                                                                 //**LP0035
            $sql2 .= "     on OPID08 = ID07 ";                                                              //**LP0035
            $sql2 .= " where USER08 = " . $row['EMPL31'] . " ";                                             //**LP0035
            //**LP0035  $sql2 .= "   and ATTR08 = 1 ";                                                                  //**LP0035
            $sql2 .= "   and ATTR08 = " . $productArea . " ";                                               //**LP0035
                                                                                                            //**LP0035
			$res2 = odbc_prepare($conn, $sql2);                                                              //**LP0035
			odbc_execute($res2);                                                                             //**LP0035
                                                                                                			//**LP0035
			$PA = "";                                                                                       //**LP0035
			while($row2 = odbc_fetch_array($res2)){                                                          //**LP0035
			    $PA = trim($row2['STXT07']);                                                                //**LP0035
			}                                                                                               //**LP0035
                                                                                                			//**LP0035
			echo "<TD>";                                                                                    //**LP0035
			echo $PA;                                                                                       //**LP0035
			echo "</TD>";                                                                                   //**LP0035
                                                                                                			//**LP0035
			//** Sub PA:                                                                                    //**LP0035
			$sql2 = " select * ";                                                                           //**LP0035
			$sql2 .= " from HLP08 ";                                                                        //**LP0035
			$sql2 .= "  inner join HLP07 ";                                                                 //**LP0035
			$sql2 .= "     on OPID08 = ID07 ";                                                              //**LP0035
			$sql2 .= " where USER08 = " . $row['EMPL31'] . " ";                                             //**LP0035
			//**LP0035  $sql2 .= "   and ATTR08 = 2 ";                                                                  //**LP0035
			$sql2 .= "   and ATTR08 = " . $subProductArea;                                                  //**LP0035
                                                                                                			//**LP0035
			$res2 = odbc_prepare($conn, $sql2);                                                              //**LP0035
			odbc_execute($res2);                                                                             //**LP0035
                                                                                                        	//**LP0035
			$SubPA = "";                                                                                    //**LP0035
			while($row2 = odbc_fetch_array($res2)){                                                          //**LP0035
			    $SubPA = trim($row2['STXT07']);                                              			    //**LP0035
			}                                                                                               //**LP0035
                                                                                                			//**LP0035
			echo "<TD>";                                                                                    //**LP0035
			echo $SubPA;                                                                                    //**LP0035
			echo "</TD>";                                                                                   //**LP0035
                                                                                                			//**LP0035
			?>																								<!-- //**LP0035 -->	
			
			<td class='center'>
				<a href='maintenanceUserEdit.php?userId=<?php echo $row['EMPL31'];?>' title="Edit <?php echo $row['ENAM05'];?>">
				<img src='<?php echo $IMG_DIR;?>/edit.gif' border='0'>
				</a>
				<?php 
				if( $_SESSION['authority'] == "S" ){
				?>
					&nbsp;
					<a href='#' onclick='deleteUser(<?php echo trim($row['EMPL31']);?>,  "<?php echo trim($row['ENAM05']);?>", "delete")' title="Delete <?php echo $row['ENAM05'];?>">
					<img src='<?php echo $IMG_DIR;?>/delete.gif' border='0'>
					</a>
				<?php 
				}else{
					?>
					&nbsp;
					<a href='#' onclick='deleteUser(<?php echo trim($row['EMPL31']);?>,  "<?php echo trim($row['ENAM05']);?>", "request")' title="Request <?php echo $row['ENAM05'];?> be Removed">
					<img src='<?php echo $IMG_DIR;?>/delete.gif' border='0'>
					</a>
					<?php 

					
				}
				
				?>
			</td>
			</tr><?php 
	}
}
ob_end_flush();


function comboSupervisor(){                                                                     //**LP0035
    global $conn;                                                                               //**LP0035
    $sql = "SELECT DISTINCT SUPR31, NAME05 FROM CIL31J01 ";                                     //**LP0035
    $sql .= " ORDER by NAME05 ";                                                                //**LP0035
    $res = odbc_prepare($conn, $sql);                                                            //**LP0035
    odbc_execute($res);                                                                          //**LP0035
                                                                                                //**LP0035
    echo "<select id='comboSupervisor' name='comboSupervisor' onchange='filterTable01()'>";     //**LP0035
                                                                                                //**LP0035
    echo "<option value='***all***' selected>";                                                 //**LP0035
    echo "--- *all ---";                                                                        //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    while($row = odbc_fetch_array($res)){                                                        //**LP0035
                                                                                                //**LP0035
        echo "<option value='" . trim($row['NAME05']) . "'>";                                   //**LP0035
        echo   trim($row['NAME05']);                                                            //**LP0035
        echo "</option>";                                                                       //**LP0035
                                                                                                //**LP0035
    }                                                                                           //**LP0035
                                                                                                //**LP0035
    echo "</select>";                                                                           //**LP0035
    return;                                                                                     //**LP0035
}                                                                                               //**LP0035

function comboAuthority($authArray){                                                            //**LP0035
                                                                                                //**LP0035
    echo "<select id='comboAuthority' name='comboAuthority' onchange='filterTable01()'>";       //**LP0035
                                                                                                //**LP0035
    echo "<option value='***all***' selected>";                                                 //**LP0035
    echo "--- *all ---";                                                                        //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    foreach ($authArray as $auth) {                                                             //**LP0035
                                                                                                //**LP0035
        echo "<option value='" . trim($auth['description']) . "'>";                             //**LP0035
        echo   trim($auth['description']);                                                      //**LP0035
        echo "</option>";                                                                       //**LP0035
                                                                                                //**LP0035
    }                                                                                           //**LP0035
                                                                                                //**LP0035
    echo "</select>";                                                                           //**LP0035
    return;                                                                                     //**LP0035
}                                                                                               //**LP0035

function comboStatus(){                                                                         //**LP0035
                                                                                                //**LP0035
    echo "<select id='comboStatus' name='comboStatus' onchange='filterTable01()'>";             //**LP0035
                                                                                                //**LP0035
    echo "<option value='***all***' selected>";                                                 //**LP0035
    echo "--- *all ---";                                                                        //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    echo "<option value='Available'>";                                                          //**LP0035
    echo   "Available";                                                                         //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    echo "<option value='Out'>";                                                                //**LP0035
    echo   "Out";                                                                               //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    echo "</select>";                                                                           //**LP0035
    return;                                                                                     //**LP0035
}                                                                                               //**LP0035

function comboPA(){                                                                             //**LP0035
    global $conn, $productArea;                                                                 //**LP0035
    $sql = "select * ";                                                                         //**LP0035
    $sql .= " from HLP07 ";                                                                     //**LP0035
    $sql .= " where ATTR07 = " . $productArea . " ";                                            //**LP0035
    //**LP0035  $sql .= " where ATTR07 = 1 ";                                                   //**LP0035
    $sql .= " order by SORT07 ";                                                                //**LP0035
    $res = odbc_prepare($conn, $sql);                                                            //**LP0035
    odbc_execute($res);                                                                          //**LP0035
                                                                                                //**LP0035
    echo "<select id='comboPA' name='comboPA' onchange='filterTable01()'>";                     //**LP0035
                                                                                                //**LP0035
    echo "<option value='***all***' selected>";                                                 //**LP0035
    echo "--- *all ---";                                                                        //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    echo "<option value=''>";                                                                   //**LP0035
    echo "";                                                                                    //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    while($row = odbc_fetch_array($res)){                                                        //**LP0035
                                                                                                //**LP0035
        echo "<option value='" . htmlspecialchars(trim($row['STXT07'])) . "'>";                 //**LP0035
        echo   htmlspecialchars(trim($row['STXT07']));                                          //**LP0035
        echo "</option>";                                                                       //**LP0035
                                                                                                //**LP0035
    }                                                                                           //**LP0035
                                                                                                //**LP0035
    echo "</select>";                                                                           //**LP0035
    return;                                                                                     //**LP0035
}                                                                                               //**LP0035

function comboSubPA(){                                                                          //**LP0035
    global $conn, $subProductArea;                                                              //**LP0035
    $sql = "select * ";                                                                         //**LP0035
    $sql .= " from HLP07 ";                                                                     //**LP0035
    //**LP0035  $sql .= " where ATTR07 = 2 ";                                                   //**LP0035
    $sql .= " where ATTR07 = " . $subProductArea . " ";                                         //**LP0035
    $sql .= " order by SORT07 ";                                                                //**LP0035
    $res = odbc_prepare($conn, $sql);                                                            //**LP0035
    odbc_execute($res);                                                                          //**LP0035
                                                                                                //**LP0035
    echo "<select id='comboSubPA' name='comboSubPA' onchange='filterTable01()'>";               //**LP0035
                                                                                                //**LP0035
    echo "<option value='***all***' selected>";                                                 //**LP0035
    echo "--- *all ---";                                                                        //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    echo "<option value=''>";                                                                   //**LP0035
    echo "";                                                                                    //**LP0035
    echo "</option>";                                                                           //**LP0035
                                                                                                //**LP0035
    while($row = odbc_fetch_array($res)){                                                        //**LP0035
                                                                                                //**LP0035
        echo "<option value='" . htmlspecialchars(trim($row['STXT07'])) . "'>";                 //**LP0035
        echo   htmlspecialchars(trim($row['STXT07']));                                          //**LP0035
        echo "</option>";                                                                       //**LP0035
                                                                                                //**LP0035
    }                                                                                           //**LP0035
                                                                                                //**LP0035
    echo "</select>";                                                                           //**LP0035
    return;                                                                                     //**LP0035
}                                                                                               //**LP0035

?>






