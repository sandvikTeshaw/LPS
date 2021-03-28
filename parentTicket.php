<?php
/*  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *   LP0086     AD    16/10/2019      GLBAU-17773  LPS - Add Buttons to Parent Tickets on Mass Upload
 *   lp0087     AD    21/10/2019    Button assign to inventory Planner
 */
?>
	<tr><TD>&nbsp;</td></tr>
        <tr>
        	<td colspan='3'>
        		<hr/>
        	</td>
        </tr>
        <tr>
			<TD class='boldBig' colspan='3'>Child Tickets</td>
		</tr>
		<tr>
        	<td colspan='3'>
        		<hr/>
        	</td>
        </tr>
        <tr>
    		<td colspan='3'>
    			<center>
    			<table width='95%' cellpadding='0' cellspacing='0' class='data-table'>
        			<tr>
            			<td colspan='2'>Ticket</td>
            			<td>Status</td>
            			<td>Description</td>
            			<td>Owner</td>
            			<td>Last Update</td>
        				<td>Last Comment</td>
       				</tr>
       				<tr id='checkAll'><td colspan='2'><a href="javascript:void(0)" onclick="selAllCheck();">Check</a></td></tr>
                    <tr id='unChkAll' style="display:none"><td colspan='2'><a href="javascript:void(0)" onclick="uncheckAll();">Uncheck</a></td></tr>
       					
               
            		<?php $numChildCount = listChildTicket( $row ['ID01'], $userArray );?>
            		<tr>
            			  <td colspan='7'> 
            			  <table style="width:100%"> <?php //LP0086_AD ?>
            			  <tr><?php //LP0086_AD ?>   
							<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Resolve Tickets' class="login-btn " style="width:100%"/>
							<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Re-Assign' class="login-btn " style="width:100%"/>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to TSD' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to Buyer' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to PFC' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to Warehouse' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				</tr><?php //LP0086_AD ?>            				            				
            				<tr><?php //LP0086_AD ?>            				
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to Sourcing' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to Freight' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to OBP' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to Requestor' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				<input type='submit' name='submit' value='Assign to Pricing' class="login-btn " style="width:100%"/> <?php //LP0086_AD ?>
            				<td style="width:17%"><?php //LP0086_AD ?>
            				
            				<?php 
            				if( $_SESSION['authority'] == "L" || $_SESSION['authority'] == "S" ){
            				    ?><input type='submit' name='submit' value='Logistics Complete' class="login-btn " style="width:100%"/><?php //LP0086_AD
            				}
            				?>
            				</table><?php //LP0086_AD ?>
            			</td>
            		</tr>

            	</table>
            	</center>
            </td>
        </tr>
        <tr><TD>&nbsp;
        <?php if($row['TYPE01']==133){  //LP0087_AD?>
        <input type='submit' name='submit' value='Assign to IP' class="login-btn " style="width:53%"/><?php //LP0087_AD?>
        <?php }//LP0087_AD?>
        </td></tr>
        <tr><TD>&nbsp;</td></tr>
        <tr>
        	<TD colspan='3'>
        		<hr>
        	</td>
        </tr>
        <tr>
        	<TD class='boldBig' colspan='3'>Ticket Operations</td>
        </tr>
        <tr>
        	<TD colspan='3'>
        		<hr>
        	</td>
        </tr>

<?php 
    	if ($_SESSION ['userID'] == "1021") {
            echo "Before History:" . date ( 'H:i:s' );
        }
        //Get Ticket History
        if ($_SESSION ['authority'] != "E"){                                                                                                                                //**LP0034
            $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $row ['ID01'], " ORDER BY DATE02 DESC, TIME02 DESC" );
        }else{                                                                                                                                                              //**LP0034
            $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $row ['ID01'] . " AND PRVT02 = 'N' ", " ORDER BY DATE02 DESC, TIME02 DESC" );   //**LP0034
        }                                                                                                                                                                   //**LP0034
        

        if ($historyArrayValues [0] ['DATE02'] != "") {
            echo "<tr>";
            echo "<TD class='bold'>Last Updated</td>";
            echo "<TD>" . formatDate ( $historyArrayValues [0] ['DATE02'] ) . " " . $historyArrayValues [0] ['TIME02'] . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Update</td>";
            echo "<TD>" . $historyArrayValues [0] ['STEP02'] . "&nbsp;&nbsp;&nbsp;&nbsp;<b>Updated By:</b>" . showUserFromArray ( $userArray, $historyArrayValues [0] ['RSID02'] ) . "</td>";
            echo "</tr>";
        }
        echo "<tr>";
        echo "<TD class='boldTop'>Action / Response</td>";
        echo "<TD><textarea name='actionResponse' id='actionResponse' cols='75' rows='8'></textarea>";
        
        if (($_SESSION ['authority'] != "E") and                        //**LP0034
            ($_SESSION ['authority'] != "")    ){                       //**LP0034
            echo "<select name='visible'>";                             //**LP0034
            echo "  <option value='N'>Public</option>";                 //**LP0034
            echo "  <option value='Y'>Backline</option>";                //**LP0034
            echo "</select>";                                           //**LP0034
        }else{                                                          //**LP0034
            echo "<input type='hidden' name='visible' value='N'>";      //**LP0034
        }                                       if ($row ['PRTY01'] != 1) {
            /*  //**LP0053_AD  //old temporary postpone system removed
             echo "<TD class='boldTop'>Postpone Escalation</td>";
             //DI868K - Allow users to postone the escalation up to 3 days, this functionality is only available once though out the life of the ticket
             
             if ($row ['EMDA01'] == 0) {
             echo "<TD><select name='postpone'>";
             echo "<option Selected value='0'>0 Hours</option>";
             echo "<option value='1'>24 Hours</option>";
             echo "<option value='2'>48 Hours</option>";
             echo "<option value='3'>72 Hours</option>";
             echo "</select></td>";
             echo "</tr>";
             } else {
             
             echo "<input type='hidden' name='postpone' value='0'>";
             //EMDA01 is the days postponed, so this checks to see how many days has been postponed and shows the date and time the escalation will occurr
             switch ($row ['EMDA01']) {
             case 1 :
             echo "<TD>24 Hours ( " . formatDate ( $row ['EDAT01'] ) . " - " . substr ( $row ['ESTI01'], 0, 2 ) . ":" . substr ( $row ['ESTI01'], 2, 2 ) . ":" . substr ( $row ['ESTI01'], 0, 2 ) . " )</td>";
             break;
             case 2 :
             echo "<TD>48 Hours ( " . formatDate ( $row ['EDAT01'] ) . " - " . substr ( $row ['ESTI01'], 0, 2 ) . ":" . substr ( $row ['ESTI01'], 2, 2 ) . ":" . substr ( $row ['ESTI01'], 0, 2 ) . " )</td>";
             break;
             case 3 :
             echo "<TD>72 Hours ( " . formatDate ( $row ['EDAT01'] ) . " - " . substr ( $row ['ESTI01'], 0, 2 ) . ":" . substr ( $row ['ESTI01'], 2, 2 ) . ":" . substr ( $row ['ESTI01'], 0, 2 ) . " )</td>";
             break;
             }
             }
             */ //**LP0053_AD
            //**************************************************************** START  //**LP0053_AD  ************************************************************************
            $holdEscalation="";//LP0053_AD
            $displayReasonRow="none";//LP0053_AD
            $unPostponed="Selected";//LP0053_AD
            if ($row ['EMDA01'] >= 9){//LP0053_AD
                $holdEscalation="checked";//LP0053_AD
                $displayReasonRow="table-row";//LP0053_AD
                $unPostponed="";//LP0053_AD
                echo "<input type='hidden' name='postponedBefore' value='1'>";//LP0053_AD
            }//LP0053_AD
            ?><!--  //LP0053_AD-->
           <tr><TD class='boldTop'>Hold Escalation<td><!--  //LP0053_AD-->
            <input type="checkbox" style="width: 22px" id="ck_holdEscalation" name="ck_holdEscalation" onclick='holdEscalationOnclick()' value="onHold" <?php echo $holdEscalation; ?> ></td></tr><!--  //LP0053_AD-->
           <tr id= "escalationReasonRow" style="display:<?php echo $displayReasonRow; ?>;"><!--  //LP0053_AD-->
           	<td class='boldTop'>Reason for Holding Escalation <span style="color: red;">*</span></td><!--  //LP0053_AD-->
                <TD><select name='postponeReason' id='postponeReason' onclick='blockbuttons()'><!--  //LP0053_AD-->
				<option value='0' <?php echo $unPostponed;?>>*Please Select Reason </option><!--  //LP0053_AD-->
<?php 
$sqlPostponeReasons = "SELECT * FROM CIL47 "; //LP0053_AD
$resPostponeReasons  = odbc_prepare( $conn, $sqlPostponeReasons );//LP0053_AD
odbc_execute( $resPostponeReasons );//LP0053_AD
while( $rowPostponeReasons = odbc_fetch_array( $resPostponeReasons ) ){//LP0053_AD
    $selectedMark="";//LP0053_AD
    if($rowPostponeReasons['ID47']==$row['EMDA01']-8)$selectedMark="Selected";//LP0053_AD
    if($rowPostponeReasons['ACTV47']==0 ||$selectedMark=="Selected")//LP0053_AD
        echo "<option ".$selectedMark." value='".($rowPostponeReasons['ID47']+8)."'>".trim($rowPostponeReasons['DESC47']) ." </option>";//LP0053_AD
}//LP0053_AD
?>                
                </select></td><!--  //LP0053_AD-->
           </tr><!--  //LP0053_AD-->
           
           <script type="text/javascript"><!--  //LP0053_AD-->
			function holdEscalationOnclick(){//LP0053_AD
				if (document.getElementById("ck_holdEscalation").checked){ //LP0053_AD
					document.getElementById("escalationReasonRow").style.display="table-row";//LP0053_AD
					}//LP0053_AD
				else document.getElementById("escalationReasonRow").style.display="none";//LP0053_AD
				blockbuttons();//LP0053_AD
				}//LP0053_AD
			///*
			function blockbuttons(){//LP0053_AD
				if (document.getElementById("ck_holdEscalation").checked &&//LP0053_AD
						(document.getElementById("postponeReason").value=='0') //LP0053_AD
					 ){//LP0053_AD
					document.getElementById("buttonsRow").style.display="none";//LP0053_AD
					}//LP0053_AD
					else document.getElementById("buttonsRow").style.display="table-row";	//LP0053_AD
			}//LP0053_AD
			//*/	//LP0053_AD
			</script><!--  //LP0053_AD-->
            <?php 
            
  //***************************************************************** END  //**LP0053_AD ***************************************************************************
            
        } else {
            echo "<input type='hidden' name='postpone' value='0'>";
        }

        ?>
        <tr><TD>&nbsp;</td></tr>
        	<input type='hidden' name='visible' value='N'><!-- //**LP0034  echo " -->
        <tr><TD>&nbsp;</td></tr>
        <!--<tr> //**LP0053_AD -->
         <tr id='buttonsRow'> <!-- //**LP0053_AD -->
        <TD colspan='1'>
        <table width=100% cellpadding=0 cellspacing=0 class='data-table'>
        	<tr>
        		<TD class='bold'>
        			<input type='submit' name='submitPost' value='Post Action' onclick="return submitForm('post');" class="login-btn next-btn"/>
        		<?php 
        
        		if( verifyClosedChildren( $row['ID01'] ) == 0 ){
        		    $showResolved = true;
        		}elseif( $superAuthArray['requester'] == true ){
        		    $showResolved = true;
        		}elseif( $_SESSION['authority'] == "S" ){
        		    $showResolved = true;
        		}elseif ( $_SESSION['userID'] == $row ['RQID01'] ) {
        		    $showResolved = true;
        		}
        		
        		if( $showResolved ){
        		?>
        		<input type='submit' name='submitResolve' value='Resolve' onclick="return submitForm('resolveParent');" class="login-btn next-btn"/></td>
        		<?php 
        		}
        		?>
        	</tr>
        </table>
