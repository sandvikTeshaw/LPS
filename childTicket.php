<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            childTicket.php<br>
 * Development Reference:   LP0036<br>
 * Description:             childTicket.php</br>
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  LP0036      TS    29/05/2009   Initial seperation of child / parent tickets<br>
 *  LP0050      KS    15/08/2018   new ticket type -�Inbound Parts Not Assembled�
 *  LP0046      KS    22/08/2018   Auto Create Cost Check ticket
 *  LP0053      AD    19/11/2018   Auto Postpone Functionality
 *  LP0055      AD    13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0068      AD    24/04/2019  GLBAU-16824_LPS Vendor Change
 *  LP0054      AD    20/05/2019  LP0054 - LPS - Create "Assign to ____" Buttons
 *  LP0079      AD    27/06/2019  Resolve button displayed twice
 *  LP0084     AD     30/09/2019 LP0084 - LPS - Allow TSD's to be identified by Item Class and PGMJ Combination
 *  lp0087     AD     21/10/2019    Button assign to inventory Planner
 *  LP0089     AD     30/10/2019  Three bugs in relation to the authorities for Clas 5 Type 75 tickets [p-6385089]
 *  LP0086       AD    15/11/2019 GLBAU-17773  LPS - Add Buttons to Parent Tickets on Mass Upload
 *  LP0091      KS    24/01/2020  GLBAU-18828 Class 5 tickets missing Assign Resource button [p-6489690]
 *
 */
/**
 */
?>
<tr>
    <td colspan='2'>
    <table width=90% cellpadding=0 cellspacin=0 style="display: none;" id='carbonCopy'>
        <tr>
            <td class='top'>
            <td><input type='text' name='CC_FILTER' id='CC_FILTER' value='4 Charaters of Resource Name' onFocus='ccListFocus()' onKeyUp='ccListKeyUp()' maxlength='4'>
             <select name='ccList' id='ccList' class='med'>
                        <?
        //show_user_list_email ( $userArray, $fieldValue );
        ?>
                    </select><br>
            <br>
            <input type='button' value='Add To CC' onClick="copyToCabonCopy()"></td>
            <TD><textarea rows="3" cols="50" id='ccSelected' name='ccSelected'></textarea></td>
        </tr>
    </table>
    </td>
</tr>

<?
        $canBeResolved=false; //LP0079_AD
        $resolveHidden="";//LP0079_AD
        echo "<tr><TD>&nbsp</td></tr>";
        echo "<tr>";
        echo "<TD colspan='2'>";
        echo "<hr>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<TD class='boldBig' colspan='2'>Supporting Information</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<TD colspan='2'>";
        echo "<hr>";
        echo "</td>";
        echo "</tr>";
        if ($_SESSION ['userID'] == "1021") {
            echo "Before Attributes:" . date ( 'H:i:s' );
        }

        $attributeReturnArray = array();
        $attributeValues[] = "";
        $attributeValues = get_attribute_values ( $row ['ID01'] );

        //Display the attributes and attribute information
        if( !isset( $orderArray) ){
            $orderArray = array( "" );
        }
        $attributeReturnArray = display_attributes ( $row ['CLAS01'], $row ['TYPE01'], $attributeValues, $orderArray );

        //capture the attribute value to send for saving
        //echo "<input type='hidden' name='attributeCount' value='" . $i . "'>";
        if ($_SESSION ['userID'] == "1021") {
            echo "After Attributes:" . date ( 'H:i:s' );
        }


        if( trim ( $row ['CLAS01']) != 9 ){

/**
 * Display Related information for issue
 *
 * @param Issue Classification $class
 * @param Issue Type $type
 * @param Part Number $partNumber
 * @param Order Number $orderNumber
 */

            if( !isset($attributeReturnArray['PART']) ){
                $attributeReturnArray['PART'] = "";
            }
        //Display the related information of the ticket
        
          if( trim ( $row['CLAS01'] ) != 11 ){
              $partNr=   trim ( $attributeReturnArray['PART'] );
              
               display_related_information ( trim ( $row ['CLAS01'] ), trim ( $row ['TYPE01'] ), trim ( $attributeReturnArray['PART'] ), trim ( $attributeReturnArray['SODP'] ), "edit", trim ( $attributeReturnArray ['poNumber'] ) );
      //LP0055_AD2        display_related_information ( trim ( $row ['CLAS01'] ), trim ( $row ['TYPE01'] ),$partNr, trim ( $attributeReturnArray['SODP'] ), "edit", trim ( $attributeReturnArray ['poNumber'] ) );
          }
          
        }
        if ($_SESSION ['userID'] == "1021") {
            echo "After Related Information:" . date ( 'H:i:s' );

        }
        if ($row ['CLAS01'] == 3 || ( $row ['CLAS01']  == 11 && $_SESSION ['authority'] != "E" ) ) {
            if ($row ['TYPE01'] != 24) {
                ?><tr><TD>&nbsp</td></tr>
                      <tr><TD colspan='2'><hr></td></tr>
                      <tr><TD class='boldBig' colspan='2'>Point of First Contact, Warehouse Checklist</td></tr>
                      <tr><TD colspan='2'><hr></td></tr>
				<?php 
                if( $_SESSION ['authority'] == "P" || $_SESSION ['authority'] == "S" || $_SESSION ['authority'] == "L" || $superAuthArray['drp'] || $superAuthArray['pfc'] ){ //LS0002 Added drp to enable fields for DRP manager.
                    //display_checklist( 1, "", $drp );
                
                    display_checklist( 1, "", $drp, trim ( $row ['CLAS01'] ) );
                }else{
                    show_checklist( 1, "" );
                }
                if( trim ( $row ['CLAS01'] ) != 11 ){
                    ?><tr><TD>&nbsp</td></tr>
                    <tr><TD colspan='2'><hr></td></tr>
                    <?php 
                    if($drp){?>
                    <tr><TD class='boldBig' colspan='2'>DRP Manager Checklist</td></tr>
                    <?php }else{?>
                    <tr><TD class='boldBig' colspan='2'>Outbound Planner Checklist</td></tr>
                    <?php }
                    //LP0002 code ended?>
                    <tr><TD colspan='2'><hr></td></tr>
                    <tr><td>It is suggested that you:</td></tr>
                    <?php
                   
                if( $_SESSION ['authority'] == "L" || $_SESSION ['authority'] == "S" || $superAuthArray['drp'] || $superAuthArray['planner']){    
                    display_checklist( 2, "SUG", $drp,trim ( $row ['CLAS01'] ));
                    display_checklist( 2, "", $drp, trim ( $row ['CLAS01'] )  );
                }else{
                    show_checklist( 2, "SUG" );
                    show_checklist( 2, "" );
                }

                    ?><tr><TD>&nbsp;</td></tr><?php 
                    if (($_SESSION ['authority'] == "L" || $_SESSION ['authority'] == "S" || $superAuthArray['drp'] || $superAuthArray['planner']) && $row ['TYPE01'] != "42" ) {  //LS0004 
                        ?><tr><TD class='bold'>Do you wish to follow the suggested course of action?</td>
                        <TD><select id='CHCE01' name='CHCE01' onChange='showImpact()'>
                        <option value=''>Select Option</option>
                        <?php 
                        list_yesNo ( trim ( $row ['CHCE01'] ) );
                        ?>
                        </select></td></tr>
    					<?php 
                        if ($row ['CHCE01'] == "N" ) {
                            ?><tr style="display:table-row;" id="IMPACT"><?
                        } else {
                            ?>
                            <tr style="display:none;" id="IMPACT"><?
                        }
                        ?>
                        <TD class='bold'>If no, explain your course of action:</td>
                        <TD><input type='text' id='IMPT01' name='IMPT01' value='<?php echo trim ( $row ['IMPT01'] );?>'>
                        </tr>
    					<tr>
                        <TD class='bold'>Planner error confirmed?</td>
                        <TD>
                        <select id='KEY201' name='KEY201'>
                        <option <?php 
                        if ($row ['KEY201'] == "N/A" || ! $row ['KEY201']) {
                            echo "SELECTED ";
                        }
                        ?>value='N/A'>Select Option</option>
                        <?php 
                        list_yesNo ( trim ( $row ['KEY201'] ) );
                        ?>
                        </select>
                        <input type='hidden' id='OLD_KEY201' name='OLD_KEY201' value='<?php echo trim ( $row ['KEY201'] );?>'>
                        <?php 
                        if ($row ['KEY201']) {
                            foreach ( $userArray as $users ) {
                                if ($row ['KEY401'] == $users ['ID05']) {
                                    echo "&nbsp&nbspConfimed by: " . $users ['NAME05'];
                                }
                            }
                        }
                        ?>
                        </td></tr>
                        <?php     
                    } else if( $row ['TYPE01'] != "42" ) {
                        ?>
                        <tr>
                        <TD class='bold'>Planners answer to suggested course of action?</td>
                        <TD>
                        <select name='CHCE01' id='CHCE01' disabled>
                        <option value=''>Select Option</option>
                        <?php list_yesNo ( trim ( $row ['CHCE01'] ) );?>
                        </select></td><tr>
                        <TD class='bold'>Planner error confirmed?</td>
                        <TD><select id='KEY201' name='KEY201' onChange='checkShowResolved()' disabled>
                        	<option <?php 
                        if ($row ['KEY201'] == "N/A" || ! $row ['KEY201']) {
                            echo " SELECTED ";
                        }
                        echo "value='N/A'>Select Option</option>";
                        list_yesNo ( trim ( $row ['KEY201'] ) );
                        echo "</select>";
                        echo "</td>";
                        if ($row ['KEY201']) {
                            foreach ( $userArray as $users ) {
                                if ($row ['KEY401'] == $users ['ID05']) {
                                    echo "&nbsp&nbspConfimed by: " . $users ['NAME05'];
                                }
                            }
                        }
                        echo "</tr>";
    
                    }
                }
            }
        }
   
        if (($row ['CLAS01'] == 8 && ($row ['TYPE01'] == "43" || $row ['TYPE01'] == "42" || $row ['TYPE01'] == "44" || $row ['TYPE01'] == "130"|| $row ['TYPE01'] == "133")) ||  //**LP0068
        ($row ['CLAS01'] == 7 && $row ['TYPE01'] != "118")) {                                                                   //**LP0050
            echo "<tr>";
            echo "<p>";
            echo "<TD class='bold'>Email Vendor<input type='checkbox' class='chkBox' name='VEND_CHK'"?> onClick="vendorEmail();"<?
            echo "></td>";
            echo "</p>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD colspan='2'>";
            ?><table width=90% cellpadding=0 cellspacing=0
        style="display: none;" id="VENDOREMAIL"><?
            echo "<tr><TD>&nbsp</td></tr>";
            echo "<tr>";
            echo "<TD colspan='2'>";
            echo "<hr>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='boldBig' colspan='2'>Vendor Email Information</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD colspan='2'>";
            echo "<hr>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Vendor Email Address:</td>";
            echo "<TD><input type=text id='VEND_EMAIL' name='VEND_EMAIL' value=''></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Vendor Contact Name:</td>";
            echo "<TD><input type=text id='VEND_CONT' name='VEND_CONT' value=''></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td class='bold'>Other Info:</td>";
            echo "<TD><textarea id='VEND_INFO' name='VEND_INFO' cols='50' rows='3'></textarea></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Include Attachments:</td>";
            echo "<TD><input type='checkbox' class='chkBox' name='EMAIL_ATTACH'></td>";
            echo "</tr>";

            echo "</table>";
            echo "</td>";
            echo "</tr>";
        }


        //D0260 - Start of LPS Pricing Snapshot*******************************
        if( ( $row ['CLAS01'] == 5 || $row ['CLAS01'] == 8 ) &&  count_records ( FACSLIB, "CIL33", " WHERE ISSU33 = $id" ) > 0 ){
        	 echo "<tr><TD>&nbsp</td></tr>";
	        echo "<tr>";
	        echo "<TD colspan='2'>";
	        echo "<hr>";
	        echo "</td>";
	        echo "</tr>";
	        echo "<tr>";
	        echo "<TD class='boldBig' colspan='2'>Part Information (Snapshot at time of ticket entry)</td>";
	        echo "</tr>";
	        echo "<tr>";
	        echo "<TD colspan='2'>";
	        echo "<hr>";
	        echo "</td>";
	        $partInfoArray = get_part_snapshot_info( $id );
	        echo "<tr>";
            echo "<TD class='bold'>Item Class:</td>";
            echo "<TD>" . $partInfoArray['PCLS33']. "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Item Group Major:</td>";
            echo "<TD>" . $partInfoArray['PGMJ33']. "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Commodity Code:</td>";
            echo "<TD>" . $partInfoArray['PLSC33']. "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Prefered Vendor:</td>";
            echo "<TD>" . $partInfoArray['DSSP33']. "</td>";
            echo "</tr>";
             echo "<tr>";
            echo "<TD class='bold'>Price Rule:</td>";
            echo "<TD>" . $partInfoArray['PREG33']. "</td>";
            echo "</tr>";


        }
        //D0260 - End of LPS Pricing Snapshot*******************************

        echo "<tr><TD>&nbsp</td></tr>";
        echo "<tr>";
        echo "<TD colspan='2'>";
        echo "<hr>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<TD class='boldBig' colspan='2'>Ticket Operations</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<TD colspan='2'>";
        echo "<hr>";
        echo "</td>";
        echo "</tr>";


    	if ($_SESSION ['userID'] == "1021") {
            echo "Before History:" . date ( 'H:i:s' );
        }
        //Get Ticket History
        if ($_SESSION ['authority'] != "E"){                                                                                                                                //**LP0034
            $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $row ['ID01'], " ORDER BY DATE02 DESC, TIME02 DESC" );
        }else{                                                                                                                                                              //**LP0034
            $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $row ['ID01'] . " AND PRVT02 = 'N' ", " ORDER BY DATE02 DESC, TIME02 DESC" );   //**LP0034
        }                                                                                                                                                                   //**LP0034

        if ( isset( $historyArrayValues[0]['DATE02'] ) &&  $historyArrayValues[0]['DATE02'] != "") {
            echo "<tr>";
            echo "<TD class='bold'>Last Updated</td>";
            echo "<TD>" . formatDate ( $historyArrayValues [0] ['DATE02'] ) . " " . $historyArrayValues [0] ['TIME02'] . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<TD class='bold'>Update</td>";
            echo "<TD>" . $historyArrayValues [0] ['STEP02'] . "&nbsp&nbsp&nbsp&nbsp<b>Updated By:</b>" . showUserFromArray ( $userArray, $historyArrayValues [0] ['RSID02'] ) . "</td>";
            echo "</tr>";
        }
        echo "<tr>";
        echo "<TD class='boldTop'>Action / Response</td>";
        //  echo "<TD><textarea name='actionResponse' id='actionResponse' cols='75' rows='8'></textarea>"; //**LP0053_AD
        if ($row ['PRTY01'] != 1) // LP0054_AD 
             echo "<TD><textarea name='actionResponse' id='actionResponse' cols='75' rows='8' onkeyup='blockbuttons()' change='blockbuttons()'></textarea>"; //**LP0053_AD
        else echo "<TD><textarea name='actionResponse' id='actionResponse' cols='75' rows='8' ></textarea>"; //**LP0054_AD
        if (($_SESSION ['authority'] != "E") and                        //**LP0034
            ($_SESSION ['authority'] != "")    ){                       //**LP0034
            echo "<select name='visible'>";                             //**LP0034
            echo "  <option value='N'>Public</option>";                 //**LP0034
            echo "  <option value='Y'>Backline</option>";                //**LP0034
            echo "</select>";                                           //**LP0034
        }else{                                                          //**LP0034
            echo "<input type='hidden' name='visible' value='N'>";      //**LP0034
        }                                                               //**LP0034
        
        echo "</tr>";

        echo "<tr>";

        if ($row ['PRTY01'] != 1) {
            
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
        echo "<tr><TD>&nbsp</td></tr>";
        //**LP0034  echo "<input type='hidden' name='visible' value='N'>";
        echo "<tr><TD>&nbsp</td></tr>";
        echo "<tr>";
        echo "<td colspan='2'>";
        echo "<table width=90% cellpadding=0 cellspacing=0>";
		
        //**LP0034  echo "<input type='hidden' name='visible' value='N'>";
        echo "<tr><TD>&nbsp</td></tr>";
        //       echo "<tr>"; //**LP0053_AD
        ?><!-- //**LP0053_AD -->
         <tr id='buttonsRow'> <!-- //**LP0053_AD -->
        <?php   
        if( !isset($readAuth) ){
            $readAuth = 0;
        }
        if( !isset($editAuth) ){
            $editAuth = 0;
        }
    
        if($readAuth && $editAuth && $row['CLAS01']==5 && $row['TYPE01']==75){  //lp0089
            echo "<TD class='bold'><a href='#' onClick="?>"return submitForm('post')"<?
			echo ">Post Action/Response</a></td>";
			echo "<TD class='bold'><a href='#' onClick="?>"return submitForm('assign')"<?
			echo ">Assign Resource</a></td>";
        }else if($readAuth && !$editAuth && $row['CLAS01']==5 && $row['TYPE01']==75){//lp0089
			echo "<TD class='bold'>Post Action/Response</td>";
			echo "<TD class='bold'>Assign Resource</td>";
        //**LP0091  }else if($row['CLAS01']!=5 && $row['TYPE01']!=75){//lp0089
        }else if(($row['CLAS01'] != 5) || ($row['TYPE01'] !=75)){                                                  //LP0091
			echo "<TD class='bold'><a href='#' onClick="?>"return submitForm('post')"<?
			echo ">Post Action/Response</a></td>";
			echo "<TD class='bold'><a href='#' onClick="?>"return submitForm('assign')"<?
			echo ">Assign Resource</a></td>";
		}
      
		if ( $_SESSION ['authority'] == "S") {//LP0079_AD
		    $canBeResolved=true; //LP0079_AD
		    
		}elseif( isset( $superAuthArray['requester'] ) && $superAuthArray['requester'] == true ){//LP0079_AD
		    $canBeResolved=true;//LP0079_AD
		    
		}elseif ($row ['CLAS01'] == 3 && ( ( trim($row ['KEY201']) == "" && trim($row ['KEY201']) != "N/A") || ($_SESSION ['authority'] == "P" ) || //LP0079_AD
		    ($_SESSION ['authority'] == "E" ) )&& $row ['TYPE01'] != 24 && $_SESSION ['userID'] != $row ['RQID01'] || //LP0079_AD
		    ($_SESSION ['authority'] == "L" && $_SESSION ['userID'] != $row ['RQID01'] ) ) {//LP0079_AD
    
		        $canBeResolved=true;//LP0079_AD
		        $resolveHidden = " style=\"display: none;\" "; //LP0079_AD

        //  DI868I Added functionality so that only requesters can resolve C1 & C2 issues//
		} elseif (($row ['CLAS01'] == 7 && $_SESSION ['userID'] != $row ['RQID01'] && $_SESSION ['authority'] != "S")) {//LP0079_AD
		    $canBeResolved=true;//LP0079_AD
		    $resolveHidden = " style=\"display: none;\" "; //LP0079_AD
		}elseif (($row ['CLAS01'] == 11 || ( $row ['CLAS01'] == 3 && $row ['TYPE01'] !=24)) && !$pfcHasAnswers ) {//LP0079_AD
		    $canBeResolved=true;//LP0079_AD
		    $resolveHidden = " style=\"display: none;\" ";//LP0079_AD
		}elseif ( $row ['CLAS01'] == 3 && $row ['TYPE01'] == 24 && $_SESSION['userID'] != $row ['RQID01'] && $_SESSION ['authority'] != "S") {//LP0079_AD
		    $canBeResolved=true;//LP0079_AD
		    $resolveHidden = " style=\"display: none;\" ";//LP0079_AD
		}elseif ( ( ($_SESSION['userID'] != $row ['RQID01']) && $_SESSION ['authority'] != "S"  &&  $superAuthArray['requester'] != true ) ) {//LP0079_AD
     
		    $canBeResolved=true;//LP0079_AD
		    $resolveHidden = " style=\"display: none;\" ";//LP0079_AD
		}else {//LP0079_AD
		    $canBeResolved=true;//LP0079_AD
		    $resolveHidden = "  ";//LP0079_AD
            
        }
      
        if($readAuth && $editAuth && $row['CLAS01']==5 && $row['TYPE01']==75){//LP0089_AD
            $canBeResolved=true;$resolveHidden = "  ";//LP0089_AD
        }else if($readAuth && !$editAuth && $row['CLAS01']==5 && $row['TYPE01']==75){//LP0089_AD
            $canBeResolved=false;//LP0089_AD
        }//LP0089_AD
		//************************************LP0079_AD END ********************************************************
		
        //LP0004
        //if( ($_SESSION ['authority'] == "P" || $_SESSION ['authority'] == "S") && $row ['CLAS01'] == 3 && $row ['TYPE01'] != 24 ){
        if( ($_SESSION ['authority'] == "P" || $superAuthArray['pfc'] || $_SESSION ['authority'] == "S") && $row ['CLAS01'] == 3 && $row ['TYPE01'] != 24 ){
            //LP0002 - Changed if Statement to check if pfc questions answered.
            if ( !$pfcHasAnswers )  {
                ?><td style="display: none;" id="PFCComplete" class='bold'><?
            } else {
                ?><td style="display: block;" id="PFCComplete" class='bold'><?
            }
            echo "<a href='#' onClick="?>"return submitForm('pfcComplete')"<?
            echo ">PFC Complete</a></td>";
        }else{                                                                                      //**LP0036
            echo "<td style='display: none;' id='PFCComplete' class='bold'></td>";                  //**LP0036
        }

        // LP0002 start showing DRP Complete
       // if( ($drp)|| (($_SESSION ['authority'] == "L" || $_SESSION ['authority'] == "S") && $row ['CLAS01'] == 3 && $row ['TYPE01'] != 24)){
       //LP0004
        //**LP0036  if( ($superAuthArray['drp'])|| (($_SESSION ['authority'] == "L" || $_SESSION ['authority'] == "S") && $row ['CLAS01'] == 3 && $row ['TYPE01'] != 24)){     
        if( ($superAuthArray['drp'])|| (($_SESSION['authority'] == "L" || $_SESSION['authority'] == "S") && ($row['CLAS01'] == 3  || $row['CLAS01'] == 8 ))){      //**LP0036
            $oNum = trim ( $attributeReturnArray['SODP'] );
            $orderNumB = substr ( $oNum, 0, strpos ( $oNum, " " ) );
           
            if( count_records ( DATALIB, "OEP40", " WHERE CONO40 = '$CONO' AND ORDN40 = '$orderNumB' AND OSRC40 = '3'" ) > 0 ){
                //**LP0036  $drpSubmitLabel = "DRP Complete";
                $drpCount = 1;                          //LP0020
            }else{
                //**LP0036  $drpSubmitLabel = "OBP Complete";
                $drpCount = 0;                          //LP0020
            }
            
            $drpSubmitLabel = "Logistics Complete";     //**LP0036
            
            if( $plannerHasAnswers && $drpCount == 1 ){
           ?><td style="display: block;" id="DRPComplete" class='bold'><?
            echo "<a href='#' onClick="?>"return submitForm('drpComplete')"<?
            echo ">" . $drpSubmitLabel . "</a></td>";
            }elseif ( $plannerHasAnswers && $drpCount == 0 ){
                ?><td style="display: block;" id="DRPComplete" class='bold'><?
            echo "<a href='#' onClick="?>"return submitForm('obpComplete')"<?
            echo ">" . $drpSubmitLabel . "</a></td>";
            }else{
            ?><td style="display: none;" id="DRPComplete" class='bold'><?
              echo "<a href='#' onClick="?>"return submitForm('obpComplete')"<?
              echo ">" . $drpSubmitLabel . "</a></td>";
            }
        }else{                                                                                      //**LP0036
            echo "<td style='display: none;' id='DRPComplete' class='bold'></td>";                  //**LP0036
        }

                      
        if(($superAuthArray['drp']) || (($_SESSION['authority'] == "L" || $_SESSION['authority'] == "S") && ($row['CLAS01'] == 5 || $row['CLAS01'] == 7) && $row ['TYPE01'] != 75)){     //**LP0036
            $drpSubmitLabel = "Logistics Complete";                                                                                                               //**LP0036
            if($plannerHasAnswers){                                                                                                                             //**LP0036
                echo "<td style='display: block;' id='PriComplete' class='bold'>";                                                                              //**LP0036
                echo "<a href='#' onClick="?>"return submitForm('priComplete')"<?                                                                               //**LP0036
                echo ">" . $drpSubmitLabel . "</a></td>";                                                                                                       //**LP0036
            }else{                                                                                                                                              //**LP0036
                echo "<td style='display: none;' id='PriComplete' class='bold'>";                                                                               //**LP0036
                echo "<a href='#' onClick="?>"return submitForm('priComplete')"<?                                                                               //**LP0036
                echo ">" . $drpSubmitLabel . "</a></td>";                                                                                                       //**LP0036
            }                                                                                                                                                   //**LP0036
        }else{                                                                                                                                                  //**LP0036
            echo "<td style='display: none;' id='PriComplete' class='bold'></td>";                                                                              //**LP0036
        }                                                                                                                                                       //**LP0036

        if( ($_SESSION ['authority'] == "L" || $_SESSION ['authority'] == "S") && ( $row ['TYPE01'] == 130 || $row ['TYPE01'] == 133 || $row ['TYPE01'] == 103 || $row ['TYPE01'] == 43 || $row ['TYPE01'] == 60 || $row ['TYPE01'] == 61 || $row ['TYPE01'] == 62 || $row ['TYPE01'] == 74 || $row ['TYPE01'] == 75)){//lp0086
        
        	?><td  id="topricing" class='bold'>
              	<a href='#' onClick="return submitForm('topricing')">Assign to Pricing</a>
              </td>
            <?php
        }elseif( ($_SESSION ['authority'] == "L" || $_SESSION ['authority'] == "S") && $row ['CLAS01'] == 8 && $row ['TYPE01'] == 43 ){
            $canBeResolved=true;$resolveHidden = "  ";//LP0079_AD
        }    //LP0079_AD
        if($canBeResolved){//LP0079_AD
			?>
			<td  id="RESOLVED" class='bold' <?php echo $resolveHidden; //LP0079AD ?> >
			<a href='#' onClick="return submitForm('resolve')">Resolve Ticket</a></td>
			<?php
          
		}
		
		if (($row ['CLAS01'] == 5) && ($row ['TYPE01'] != 75)){                                                                                                                   //**LP0046
		    $userAuthsql = "select * ";                                                                                                                                           //**LP0046
		    $userAuthsql .= " from CIL40 ";                                                                                                                                       //**LP0046
		    $userAuthsql .= " where USER40='" . $_SESSION['userID'] . "' ";                                                                                                       //**LP0046
		    $userAuthsql .= "   and GRUP40='2' ";                                                                                                                                 //**LP0046
		    $userAuthres = odbc_prepare($conn, $userAuthsql);                                                                                                                      //**LP0046
		    odbc_execute($userAuthres);                                                                                                                                            //**LP0046
		    while($userAuthrow = odbc_fetch_array($userAuthres)){                                                                                                                  //**LP0046
		        $authCreate = trim($userAuthrow['CRTE40']);                                                                                                                       //**LP0046
		    }                                                                                                                                                                     //**LP0046
		    if (($_SESSION ['authority'] == "S") || ($authCreate == '1')){                                                                                                        //**LP0046
		        echo "<td class='bold'>";                                                                                                                                         //**LP0046
		        echo "<a target='_blank' href='" .  $mtpUrl . "/addTicket.php?status=1&class=5&type=75&basedOnTicket=" . $row ['ID01'] . "'>Create Cost Check Ticket</a>";        //**LP0046
		        echo "</td>";                                                                                                                                                     //**LP0046
		    }                                                                                                                                                                     //**LP0046
		}                                                                                                                                                                         //**LP0046
		
		
		//D0539 - End Added for Pricing Team function ******************
		//********************* LP0054 START ****************************
		if ($_SESSION ['authority'] != "E" && $row['TYPE01'] != 24 && $row['TYPE01'] != 50 && $row['TYPE01'] != 76 && $row['TYPE01'] != 47 && $row['TYPE01'] != 53  ){ //LP0054_AD
		    if (findTSD($row['ID01'])>0){//LP0084_AD              *******************BUTTON enABLED*****************

		     //LP0084_AD   if (false&&findTSD($row['ID01'])>0){//LP0054_AD              *******************BUTTON DISABLED*****************
		            ?><td  id="totsd" class='bold'> <?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('totsd')">Assign to TSD</a><?php //LP0054_AD ?>
              	</td><?php //LP0054_AD ?>
            	<?php //LP0054_AD
		    }//LP0054_AD
		    if (findPFC($row['ID01'])>0){//LP0054_AD
		        ?><td  id="topfc" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('topfc')">Assign to PFC</a><?php //LP0054_AD ?>
              	</td><?php //LP0054_AD ?>
            	<?php //LP0054_AD
		    }//LP0054_AD
		    if (findFreightContact($row['ID01'])>0 && ($row ['CLAS01'] == 3 && $row['TYPE01'] != 103 ) ){//LP0054_AD
		        
		        ?><td  id="tofreight" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('tofreight')">Assign to Freight</a><?php //LP0054_AD ?>
            	<?php //LP0054_AD
		    }elseif(  $row['TYPE01'] == 31 || $row['TYPE01'] == 32|| $row['TYPE01'] == 33 || $row['TYPE01'] == 34){
		        ?><td  id="tofreight" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('tofreight')">Assign to Freight</a><?php //LP0054_AD ?>
            	<?php //LP0054_AD
		    }//LP0054_AD	
		    if (findWarehouseContact($row['ID01'])>0  ){//LP0054_AD
		        
		        if( $row ['CLAS01'] != 5 && $row['TYPE01'] != 42 ){
    		        if( $row ['CLAS01'] != 8 || ($row['TYPE01'] == 55 || $row['TYPE01'] == 57 ) ){
        		        ?><td  id="tofreight" class='bold'><?php //LP0054_AD ?>
                      	<!-- <a href='#' onClick="return submitForm('towar')">Assign to Warehouse</a>--><?php //LP0054_AD ?>
                    	<?php //LP0054_AD
    		        }
		        }
		    }elseif( $row ['CLAS01'] == 7 && $row['TYPE01'] != 50 ){
		        
		        ?><td  id="tofreight" class='bold'><?php //LP0054_AD ?>
                  	<!-- <a href='#' onClick="return submitForm('towar')">Assign to Warehouse</a>--><?php //LP0054_AD ?>
                	<?php //LP0054_AD
		        
		        
		    }//LP0054_AD
		    if (findSrcContact($row['ID01'])>0 && ( $row['TYPE01'] == 42 || $row ['CLAS01'] == 5 || ( $row ['CLAS01'] == 8 && $row['TYPE01'] != 44 && $row['TYPE01'] != 54 && $row['TYPE01'] != 58 ))) {//LP0054_AD
		    ?><td  id="tofreight" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('tosrc')">Assign to Sourcing</a><?php //LP0054_AD ?>
            	<?php //LP0054_AD
		    } //LP0054_AD
		    
		    if ($row ['CLAS01'] != 11 && $row ['CLAS01'] != 17 && $row['TYPE01'] != 14 && $row['TYPE01'] != 18 && $row['TYPE01'] != 19 && $row['TYPE01'] != 102 && $row['TYPE01'] != 103 && $row['TYPE01'] != 107 ){ //LP0054_AD
		        ?><td id="tobuyer" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('tobuyer')">Assign to Buyer</a><?php //LP0054_AD ?>
              	</td><?php //LP0054_AD ?>
            	<?php //LP0054_AD
		    } //LP0054_AD
		    if (($row['RQID01'])>0){ //LP0054_AD
		        ?><td id="torequester" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('torequester')">Assign to Requester</a><?php //LP0054_AD ?>
              	</td><?php //LP0054_AD ?>
            	<?php
		    } //LP0054_AD
		    if (($row['TYPE01'])==133 && findIPContact($row['ID01'])>0){ //LP0087_AD
		        ?><td id="toIP" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('toip')">Assign to Inventory Planner</a><?php //LP0087_AD ?>
              	</td><?php //LP0087_AD ?>
            	<?php
		    } //LP0087_AD
		    
		} //LP0054_AD
		if (($row['POFF01'])>0 && ( $row['TYPE01'] == 42 || $row['TYPE01'] == 19 || $row['TYPE01'] == 14 || $row['TYPE01'] == 22 || $row['TYPE01'] == 23 || $row['TYPE01'] == 18 || $row['TYPE01'] == 102  || $row['TYPE01'] == 103 || $row['TYPE01'] == 107 )) { //LP0054_AD
		    ?><td  id="toobp" class='bold'><?php //LP0054_AD ?>
              	<a href='#' onClick="return submitForm('toobp')">Assign to OBP</a><?php //LP0054_AD ?>
              	</td><?php //LP0054_AD ?>
            	<?php
		    } //LP0054_AD
		    
		//*********************  LP0054 END  ****************************
		
		?>
		</table>
		</td>
		</tr>
