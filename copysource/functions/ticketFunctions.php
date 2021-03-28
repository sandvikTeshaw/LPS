<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            ticketFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions related core LPS ticket details
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification
 *  LP0050      KS    02/08/2018  Create new LPS ticket type �Inbound Parts Not Assembled�
 *  LP0055      AD    13/03/2019  GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0068      AD    29/04/2019  GLBAU-15650_LPS Vendor Change
 *  LP0054      AD    20/05/2019  LP0054 - LPS - Create "Assign to ____" Buttons
 *  LP0076      AD    28/06/2019  GLBAU-17554_Inbound Parts Not Marked with Sandvik Part Number
 *  LP0074      AD    18/09/2019  GLBAU-17040 LPS data fields connection with S21 - upper/lower case sensitivity
 *  LP0082      AD    18/09/2019  Amendment / enhancement to Vender change LPS ticket
 *  LP0084      AD    30/09/2019 LP0084 - LPS - Allow TSD's to be identified by Item Class and PGMJ Combination
 *  lp0087     AD     21/10/2019    Button assign to inventory Planner
 *  LP0086_2   AD    01/11/2019 GLBAU-17773  LPS - Add Buttons to Parent Tickets on Mass Upload(fix)
 *
 **/

/*
 * Displays the attributes types and values for tickets dependant on classification and type of ticket
 *
 * @parm integer $class Classification of ticket
 * @parm integer $type Type of ticket
 * @parm array $currentAttributeArray Array of current attribute information
 * @parm $orderArray defines the sort order of response
 * @return $attributeArray an array of attributes from CIL07
 */
function display_attributes($class, $type, $currentAttributeArray, $orderArray) {
    //lp0055_ad2 global $CONO, $conn;
    global $CONO, $conn,$supplierNumber130;//lp0055_ad2
    $supplierPartDefaultCurrencyCode="";
    $attributeSql = "SELECT * FROM CIL07L01 WHERE CLAS07 = $class AND TYPE07=$type order by PREC07";
    
    $attributeRes = odbc_prepare ( $conn, $attributeSql );
    odbc_execute ( $attributeRes );
    
 
    $attribCount = 0;
    $attributeArray [] = "";
    while ( $attributeRow = odbc_fetch_array ( $attributeRes ) ) {
     
        $attribCount ++;
        
        echo "<input type='hidden' name='attribId_" . $attribCount . "' value='" . trim ( $attributeRow ['ATTR07'] ) . "'>";
        echo "<input type='hidden' name='attribType_" . $attribCount . "' value='" . trim ( $attributeRow ['HTYP07'] ) . "'>";
        echo "<input type='hidden' name='attribName_" . $attribCount . "' value='" . trim ( $attributeRow ['NAME07'] ) . "'>";
        echo "<input type='hidden' name='attribRequired_" . $attribCount . "' value='" . trim ( $attributeRow ['REQD07'] ) . "'>";
        
        if ( isset($currentAttributeArray [$attributeRow ['ATTR07']])) {
            echo "<input type='hidden' name='attribExist_" . $attribCount . "' value='Y'>";
        } else {
            echo "<input type='hidden' name='attribExist_" . $attribCount . "' value='N'>";
        }
        
        ?><tr><?php

        //validation display, if invalid shows Invalid in red
        if (trim ( $attributeRow ['HTYP07'] ) == "SODP") {
            //DI Sales Order
            ?>

        <td style="display: block;"
            id="SODP_<?
            echo $attributeRow ['ATTR07'];
            ?>" class='bold'><?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            ?></td>

        <td style="display: none;"
            id="SODP_INVALID_<?
            echo $attributeRow ['ATTR07'];
            ?>"
            class='bold'><font color='red'><b>Invalid <?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            ?></b></font></td><?php
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "PART") {
          if($type==130){//LP0055_AD2
              ?>

        <td style="display: block;"
            id="PART_<?
          }else{ 
            ?>

        <td style="display: block;"
            id="PART_<?
          }
            echo $attributeRow ['ATTR07'];
            ?>" class='bold'><?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</td>";
            ?>



        <td style="display: none;"
            id="PART_INVALID_<?
            echo $attributeRow ['ATTR07'];
            ?>"
            class='bold'><font color='red'><b>Invalid <?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</b></font></td>";

        } elseif (trim ( $attributeRow ['HTYP07'] ) == "SPRT") { //LP0055_AD
            ?><!--//LP0055_AD -->

        <td style="display: block;"    
            id="SUPPLIER_PART_<?//LP0055_AD
            echo $attributeRow ['ATTR07'];//LP0055_AD
            ?>" class='bold'><?//LP0055_AD
            echo trim ( $attributeRow ['NAME07'] );//LP0055_AD
            if ($attributeRow ['REQD07'] == "Y") {//LP0055_AD
                echo "<font color='red'><b>*</b></font>";//LP0055_AD
            }//LP0055_AD
            echo "</td>";//LP0055_AD
            ?>



        <td style="display: none;" 
            id="SUPPLIER_PART_INVALID_<? //LP0055_AD -->
            echo $attributeRow ['ATTR07'];//LP0055_AD
            ?>" class='bold'><font color='red'><b>Invalid <?//LP0055_AD -->
            echo trim ( $attributeRow ['NAME07'] );//LP0055_AD
            if ($attributeRow ['REQD07'] == "Y") {//LP0055_AD
                echo "<font color='red'><b>*</b></font>";//LP0055_AD
            }
            echo "</b></font></td>";//LP0055_AD

        } elseif (trim ( $attributeRow ['HTYP07'] ) == "SUPP") { //LP0055_AD
            ?><!--//LP0055_AD -->

        <td style="display: block;"    
            id="SUPPLIER_<?//LP0055_AD
            echo $attributeRow ['ATTR07'];//LP0055_AD
            ?>" class='bold'><?//LP0055_AD
            echo trim ( $attributeRow ['NAME07'] );//LP0055_AD
            if ($attributeRow ['REQD07'] == "Y") {//LP0055_AD
                echo "<font color='red'><b>*</b></font>";//LP0055_AD
            }//LP0055_AD
            echo "</td>";//LP0055_AD
            ?>



        <td style="display: none;" 
            id="SUPPLIER_INVALID_<? //LP0055_AD -->
            echo $attributeRow ['ATTR07'];//LP0055_AD
            ?>" class='bold'><font color='red'><b>Invalid <?//LP0055_AD -->
            echo trim ( $attributeRow ['NAME07'] );//LP0055_AD
            if ($attributeRow ['REQD07'] == "Y") {//LP0055_AD
                echo "<font color='red'><b>*</b></font>";//LP0055_AD
            }
            echo "</b></font></td>";//LP0055_AD

            //DI932 - Added to support Returns functionality
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "DICU") {
            //DI Customer Number
            ?>

        <td style="display: block;"
            id="DICU_<?
            echo $attributeRow ['ATTR07'];
            ?>"
            class='bold'><?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</td>";
            ?>



        <td style="display: none;" id="DICU_INVALID_<? echo $attributeRow ['ATTR07'];?>" class='bold'><font color='red'><b>Invalid <? echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                ?><font color='red'><b>*</b></font><?php
            }
            ?></b></font></td><?php
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "PART") {
            ?>
        <td style="display: block;"
            id="PART_<?
            echo $attributeRow ['ATTR07'];
            ?>"
            class='bold'><?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</td>";
            ?>



        <td style="display: none;"
            id="PART_INVALID_<?
            echo $attributeRow ['ATTR07'];
            ?>"
            class='bold'><font color='red'><b>Invalid <?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</b></font></td>";
        }

        elseif ($type == 43 && trim ( $attributeRow ['NAME07'] ) == "Quantity Required") {
            ?>

        <td style="display: block;"
            id="QTY_<?
            echo $attributeRow ['ATTR07'];
            ?>" class='bold'><?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</td>";
            ?>



        <td style="display: none;"
            id="QTY_INVALID_<?
            echo $attributeRow ['ATTR07'];
            ?>"
            class='bold'><font color='red'><b>Invalid <?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</b></font></td>";
            //********************************* LP0076_AD START ****************************************
            //LP0077_AD  }elseif (($type == 134 )&& trim ( $attributeRow ['NAME07'] ) == "Logistics Purchase order") {   //LP0076_AD
        }elseif (($type == 134 || $type == 135)&& trim ( $attributeRow ['NAME07'] ) == "Logistics Purchase order") {  //LP0077_AD
            ?><!-- //LP0076_AD -->

        <td style="display: block;" id="PO_OK" class='bold'><?//LP0076_AD
                echo trim ( $attributeRow ['NAME07'] );//LP0076_AD
                if ($attributeRow ['REQD07'] == "Y") {//LP0076_AD
                    echo "<font color='red'><b>*</b></font>";//LP0076_AD
                }//LP0076_AD
                echo "</td>";//LP0076_AD
            ?><!-- //LP0076_AD -->
        <td style="display: none;"
            id="PO_INVALID" class='bold'><font color='red'><b>Invalid <?//LP0076_AD
            echo trim ( $attributeRow ['NAME07'] );//LP0076_AD
            if ($attributeRow ['REQD07'] == "Y") {//LP0076_AD
                echo "<font color='red'><b>*</b></font>";//LP0076_AD
            }//LP0076_AD
            echo "</b></font></td>";//LP0076_AD
            
            //********************************* LP0076_AD END ****************************************            
            //*****LP0021 - Start Addition on validation
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "INVN") {
            ?>

        <td style="display: block;"
            id="INVN_<?
            echo $attributeRow ['ATTR07'];
            ?>" class='bold'><?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</td>";
            ?>
        <td style="display: none;"
            id="INVN_INVALID_<?
            echo $attributeRow ['ATTR07'];
            ?>"
            class='bold'><font color='red'><b>Invalid <?
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</b></font></td>";
        //*****LP0021 - End Addition on validation
        //DI932 - Added to support Returns functionality
        }else {
            echo "<td class='bold'>";
            echo trim ( $attributeRow ['NAME07'] );
            if ($attributeRow ['REQD07'] == "Y") {
                echo "<font color='red'><b>*</b></font>";
            }
            echo "</td>";
        }
        echo "<td>";
        if( !isset( $attributeRow ['ATTR07'] ) ){
            $attributeRow ['ATTR07'] = "";
        }
        if( !isset( $currentAttributeArray [$attributeRow ['ATTR07']]) ){
            $currentAttributeArray [$attributeRow ['ATTR07']] = "";
        }
        if (trim ( $attributeRow ['HTYP07'] ) == "DROP") {
            echo "<select name='drop" . $attribCount . "' class='med'>";
            show_attribute_drop_list ( $class, $type, $attributeRow ['ATTR07'], $currentAttributeArray [$attributeRow ['ATTR07']] );
            echo "</select>";

        } elseif (trim ( $attributeRow ['HTYP07'] ) == "DATE") {
            if ($currentAttributeArray [$attributeRow ['ATTR07']]) {
                $yearIn = substr ( $currentAttributeArray [$attributeRow ['ATTR07']], 0, 3 );
                $monthIn = substr ( $currentAttributeArray [$attributeRow ['ATTR07']], 3, 2 );
                $dayIn = substr ( $currentAttributeArray [$attributeRow ['ATTR07']], 5, 2 );
            } else {
                $jbaDate = convert_to_jba_date ( date ( 'Ymd' ) );
                $yearIn = substr ( $jbaDate, 0, 3 );
                $monthIn = substr ( $jbaDate, 3, 2 );
                $dayIn = substr ( $jbaDate, 5, 2 );
            }
            echo "<select name='year" . $attribCount . "' class='small'>";
            list_jba_year ( $yearIn );
            echo "</select>";
            echo "<select name='month" . $attribCount . "' class='small'>";
            list_months ( $monthIn );
            echo "</select>";
            echo "<select name='day" . $attribCount . "' class='small'>";
            list_days ( $dayIn );
            echo "</select>";

 //lp0082_ad       } elseif (trim ( $attributeRow ['HTYP07'] ) == "PART" && $type != 133 ) {
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "PART"  ) {  //lp0082_ad 
            $attributeName = "part" . $attribCount;

            if ( isset($currentAttributeArray [$attributeRow ['ATTR07']]) ) {
   //LP0055_AD2             echo "<input type='text' name='part" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?><!--onfocus="selectPartInput()" onblur="checkPartNumber( this.value,LP0055AD2--> <?
        if($type!=130){  //LP0055_AD2
                echo "<input type='text' name='part" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkPartNumber( this.value, <? //LP0055_AD2
        }else{//LP0055_AD2
            echo "<input type='text' name='part" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkPartNumber( this.value, <? //LP0055_AD2
            
        }//LP0055_AD2
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?>, '<?
                echo $currentAttributeArray [$attributeRow ['ATTR07']];
                ?>' )" <?
       //LP0074_AD         echo " class='medium'>";
                echo " class='medium toCaps'>";//LP0074_AD
                $attributeArray ['PART'] = $currentAttributeArray [$attributeRow ['ATTR07']];

            } else {
 //LP0055_AD2   echo "<input type='text' name='part" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?><!--onfocus="selectPartInput()" onblur="checkPartNumber( this.value,LP0055AD2--> <?
        if($type!=130){  //LP0055_AD2
            
            if( isset( $currentAttributeArray [$attributeRow ['ATTR07']] ) ){
                $pval = trim ( $currentAttributeArray [$attributeRow ['ATTR07']] );
            }else{
                $pval = "";
            }
                echo "<input type='text' name='part" . $attribCount . "' value='$pval' "?>onfocus="selectPartInput()" onblur="checkPartNumber( this.value, <? //LP0055_AD2
        }else{//LP0055_AD2
            
            if( !isset ( $attributeRow ['ATTR07'] ) ){
                $attributeRow ['ATTR07'] = "";
            }
            echo "<input type='hidden' name='part" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkPartNumber( this.value, <? //LP0055_AD2
            
        }//LP0055_AD2
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?> )" <?
                //LP0074_AD         echo " class='medium'>";
                echo " class='medium toCaps'>";//LP0074_AD
                if( isset( $currentAttributeArray [$attributeRow ['ATTR07']] ) ){
                    $attributeArray ['PART'] = $currentAttributeArray [$attributeRow ['ATTR07']];
                }else{
                    $attributeArray ['PART'] = "";
                }
                
            }
   //*************************** LP0055_AD START ***************************************************
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "SPRT" || ( trim ( $attributeRow ['HTYP07'] ) == "PART" && $type = 133 )) {
            $attributeName = "part" . $attribCount;
            
            if ($currentAttributeArray [$attributeRow ['ATTR07']]) {
                echo "<input type='text' name='sprt" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkSuppPartNumber( this.value, <?
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?>, '<?
                echo $currentAttributeArray [$attributeRow ['ATTR07']];
                ?>' )" <?
                echo " class='medium'>";
                
                //***************************Start - LP0068 - Added functionality specialized for type = 130 and type = 133****************
                if( $type == 130 ){
                    /* //lp0082_ad                    
                    echo "<input type='text' name='sprt" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkSuppPartNumber( this.value, <?
                    echo $attributeRow ['ATTR07'];
                    ?>, <?
                    echo $type;
                    ?>, '<?
                    echo $currentAttributeArray [$attributeRow ['ATTR07']];
                    ?>' )" <?
                    echo " class='medium'>";
                    /*/ //lp0082_ad
                
                    $attributeArray ['SPRT'] = $currentAttributeArray [$attributeRow ['ATTR07']];
                    $partSql = "SELECT CURC01 FROM PMP01 WHERE CONO01='$CONO'";//LP0055_AD
                    $partSql .= " AND (VNDR01='" . $supplierNumber130 . "' AND ITEM01= '". trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) ."') ";//LP0055_AD
                    $partSql .= " ORDER BY MDTE01 DESC FETCH FIRST ROW ONLY ";//LP0055_AD
                    
                    $curField = "CURC01";
                    
                }elseif($type == 133 ){
                    echo "<input type='text' name='part" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkPartNumber( this.value, <? //LP0055_AD2
                    echo $attributeRow ['ATTR07'];
                    ?>, <?
                    echo $type;
                    ?>, '<?
                    echo $currentAttributeArray [$attributeRow ['ATTR07']];
                    ?>' )" <?
                    echo " class='medium'>";
                    $attributeArray ['PART'] = $currentAttributeArray [$attributeRow ['ATTR07']];
                   
                    $attributeArray ['PART'] = $currentAttributeArray [$attributeRow ['ATTR07']];
                    $partSql = "SELECT CURN05 FROM PLP05 WHERE CONO05='$CONO' AND DSEQ05='000'";//LP0068
                    $partSql .= " AND (SUPN05='" . trim($supplierNumber130) . "')";//LP0068
                    
                    
                    $curField = "CURN05";
                    
                }
        
                //***************************End - LP0068 ****************************************************************************
                
                            
                $partRes = odbc_prepare ( $conn, $partSql );//LP0055_AD
                odbc_execute ( $partRes );//LP0055_AD
                $row= odbc_fetch_array ( $partRes );
                $supplierPartDefaultCurrencyCode=$row[$curField];  //LP0068 - LP0055 - Added var for field name
                
            } else {
                if( $type == 130 ){

                    echo "<input type='text' name='sprt" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkSuppPartNumber( this.value, <?
                    echo $attributeRow ['ATTR07'];
                    ?>, <?
                    echo $type;
                    ?> )" <?
                    echo " class='medium'>";
                }else{
                    //LP0068 - remove part validation from type 133
                    echo "<input type='text' name='part" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' class='medium'>";
                }
                $attributeArray ['SPRT'] = $currentAttributeArray [$attributeRow ['ATTR07']];
            }
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "SUPP") {
            $attributeName = "part" . $attribCount;
            
            if ($currentAttributeArray [$attributeRow ['ATTR07']]) {
                echo "<input type='text' name='supp" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkSuppNumber( this.value, <?
                echo $attributeRow ['ATTR07'];
                $supplierNumber130=trim ( $currentAttributeArray [$attributeRow ['ATTR07']] );  //LP0055_AD2             
                ?>, <?
                echo $type;
                ?>, '<?
                echo $currentAttributeArray [$attributeRow ['ATTR07']];
                ?>' )" <?
                echo " class='medium'>";
                $attributeArray ['SUPP'] = $currentAttributeArray [$attributeRow ['ATTR07']];

            } else {
                echo "<input type='text' name='supp" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkSuppNumber( this.value, <?
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?> )" <?
                echo " class='medium'>";
                $attributeArray ['SUPP'] = $currentAttributeArray [$attributeRow ['ATTR07']];
            }

            //*************************** LP0055_AD END ***************************************************
            
        //*****LP0021 - Start Addition on validation
        }elseif (trim ( $attributeRow ['HTYP07'] ) == "INVN") {
            $attributeName = "invn" . $attribCount;

            if ($currentAttributeArray [$attributeRow ['ATTR07']]) {
                echo "<input type='text' name='invn" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkInvoice( this.value, <?
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?>, '<?
                echo $currentAttributeArray [$attributeRow ['ATTR07']];
                ?>' )" <?
                echo " class='medium'>";
                $attributeArray ['INVN'] = $currentAttributeArray [$attributeRow ['ATTR07']];
        //*****LP0021 - End Addition on validation
            } else {
                echo "<input type='text' name='invn" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' "?>onfocus="selectPartInput()" onblur="checkInvoice( this.value, <?
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?> )" <?
                echo " class='medium'>";
                $attributeArray ['INVN'] = $currentAttributeArray [$attributeRow ['ATTR07']];
            }


        }elseif (trim ( $attributeRow ['HTYP07'] ) == "SODP") {
            $attributeName = "sodp" . $attribCount;

            //Check to see if from add or edit
            //if from edit then send current value to javascript function
            if (isset($currentAttributeArray [$attributeRow ['ATTR07']])) {

                if (strpos ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), " " ) > 0) {
                    $sodpValue = substr ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), 0, strpos ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), " " ) );
                    $sodpBValue = substr ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), strpos ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), " " ) + 1 );
                } else {
                    $sodpValue = trim ( $currentAttributeArray [$attributeRow ['ATTR07']] );
                }
                echo "<input type='text' name='sodp" . $attribCount . "' class='small' value='$sodpValue'"?>onblur="checkOrderNumber( this.value, <?
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?>, '<?
                echo $currentAttributeArray [$attributeRow ['ATTR07']];
                ?>' )" <?
                echo ">";
                if ($class != 8 && $type !=42 ) {       //LP0024 - Added Exclusion of $type = 42
                    if( !isset( $sodpBValue ) ){
                        $sodpBValue = "";
                    }
                    echo " / " . "<input type='text' name='sodpb" . $attribCount . "' class='tiny' value='$sodpBValue' maxlength='3'"?>onblur="setDesnNumber( this.value )"<?
                    echo ">";
                }

                $attributeArray ['SODP'] = $currentAttributeArray [$attributeRow ['ATTR07']];
            } else {
                if( isset($currentAttributeArray [$attributeRow ['ATTR07']]  ) ){
                    $aValue = trim ( $currentAttributeArray [$attributeRow ['ATTR07']] );
                }else{
                    $aValue = "";
                }
                echo "<input type='text' name='sodp" . $attribCount . "' class='small' value='$aValue'"?>onblur="checkOrderNumber( this.value, <?
                echo $attributeRow ['ATTR07'];
                ?>, <?
                echo $type;
                ?>)" <?
                echo ">";
                if ($class != 8 && $type !=42) {    //LP0024 - Added Exclusion of $type = 42
                    if( isset( $currentAttributeArray [$attributeRow ['ATTR07']] ) ){
                        $bval = trim ( $currentAttributeArray [$attributeRow ['ATTR07']] );
                    }else{
                        $bval = "";
                    }
                    echo " / " . "<input type='text' name='sodpb" . $attribCount . "' class='tiny' value='$bval' maxlength='3'"?>onblur="setDesnNumber( this.value )"<?
                    echo ">";
                }
            }

        //DI932 - Added to support Returns functionality
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "DICU") {
            $attributeName = "dicu" . $attribCount;

            //Check to see if from add or edit
            //if from edit then send current value to javascript function
            if ($currentAttributeArray [$attributeRow ['ATTR07']]) {

                if (strpos ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), " " ) > 0) {

                    $dicuVal = substr ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), 0, strpos ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), " " ) );
                    $dicuBVal = substr ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), strpos ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ), " " ) + 1 );

                    echo "<input type='hidden' id='CUSTOMER_NUMBER' name='CUSTOMER_NUMBER' value='$dicuVal'>";

                } else {
                    $dicuVal = trim ( $currentAttributeArray [$attributeRow ['ATTR07']] );
                }
                echo "<input type='text' name='dicu" . $attribCount . "' class='small' value='$dicuVal'"?>onblur="setCustomer( this.value)" <?
                echo ">";
                echo " / " . "<input type='text' name='dicub" . $attribCount . "' class='tiny' value='$dicuBVal' maxlength='3'"?>onblur="checkCustomerAndSequence( this.value , <?
                echo $attributeRow ['ATTR07'];
                ?>)"<?
                echo ">";

                $attributeArray ['DICU'] = $currentAttributeArray [$attributeRow ['ATTR07']];
            } else {
                echo "<input type='text' name='dicu" . $attribCount . "' class='small' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'"?>onblur="setCustomer( this.value )"<?
                echo ">";
                echo " / " . "<input type='text' name='dicub" . $attribCount . "' class='tiny' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "' maxlength='3'"?>onblur="checkCustomerAndSequence( this.value, <?
                echo $attributeRow ['ATTR07'];
                ?>)" <?
            }

        } elseif (trim ( $attributeRow ['HTYP07'] ) == "COUN") {
            echo "<select name='coun" . $attribCount . "'>";
            echo "<option value=''>Select Market Area</option>";
            get_country_listing ( $currentAttributeArray [$attributeRow ['ATTR07']] );
            echo "</select>";

        } elseif (trim ( $attributeRow ['HTYP07'] ) == "BRAN") {
            echo "<select name='bran" . $attribCount . "'>";
            echo "<option value=''>Select Brand</option>";
            list_bran_resp ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) );
            echo "</select>";

        } elseif (trim ( $attributeRow ['HTYP07'] ) == "MODL") {
            echo "<select name='modl" . $attribCount . "'>";
            echo "<option value=''>Select Model</option>";
            list_model_type ( trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) );
            echo "</select>";

        //D0180 - Added for Returns Regional notification process
        } elseif (trim ( $attributeRow ['HTYP07'] ) == "REGN") {
            echo "<select name='regn" . $attribCount . "'>";
            echo "<option value=''>Select Region</option>";
            get_region_listing( $currentAttributeArray [$attributeRow ['ATTR07']] );
            echo "</select>";

        } else {
            if ($type == 42 && trim ( $attributeRow ['NAME07'] ) == "Quantity Required") {
                if ($currentAttributeArray [$attributeRow ['ATTR07']]) {
                    
                    echo "<input type='text' name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'"?>onblur="CheckPart( this.value )"<?
                    ">";
                } else {
                    echo "<input type='text' name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'"?>onblur="CheckPart( this.value )"<?
                    ">";
                }
   //***************************************** LP0055_AD START ******************************************             
                //LP0068_TS           } elseif ($type == 130 && trim ( $attributeRow ['HTYP07'] ) == "CURN") {
            } elseif (($type == 130||$type ==133) && trim ( $attributeRow ['HTYP07'] ) == "CURN") {//LP0068_TS
                echo "<input type='text' class='big' name='text" . $attribCount . "' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'>";
                if(trim($supplierPartDefaultCurrencyCode)!=trim( $currentAttributeArray [$attributeRow ['ATTR07']] ))
                    echo '<font color="red"> Vendor Default - '.$supplierPartDefaultCurrencyCode."</font>";
   //***************************************** LP0055_AD END ******************************************
   //***************************************** LP0076_AD START ****************************************
            } elseif (($type == 134)&&(trim ( $attributeRow ['NAME07'] ) == "Logistics Purchase order")){//LP0077_AD
                echo "<input type='text' id='PO' name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'"?>onblur="CheckPO( this.value )"<?//LP0076_AD
            //} elseif (($type == 134)&&(trim ( $attributeRow ['NAME07'] ) == "Supplier Name")){//LP0076_AD
            //    echo "<input type='text' id='suppName' name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'";//LP0076_AD
            //} elseif (($type == 134)&&(trim ( $attributeRow ['NAME07'] ) == "Supplier Number")){//LP0076_AD
            //    echo "<input type='text' id='suppNumber' name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'";//LP0076_AD
                
   //***************************************** LP0076_AD END ******************************************
   //***************************************** LP0077_AD START ****************************************
            } elseif (($type == 135 || $type == 134 )&&(trim ( $attributeRow ['NAME07'] ) == "Supplier Name")){//LP0077_AD
                ?>
				<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
				 <style>
                     .ui-autocomplete-loading {
                      background: white url("copysource/images/loader.gif") right center no-repeat;
                        }
                </style>
 				<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
 				<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
 				
                  <script>
              
                  $(function() {
                      $("#suppName").autocomplete({
                    	  source: function(request, response) {
                    	    $.getJSON("search_supplier.php", { pNumber: $('#sPnumber').val() }, 
                    	              response);
                    	  },
                    	  minLength: 2,
                    	  select: function(event, ui){
                    		  $( "#suppNumber" ).val(ui.item.id)// = // ;
                    	  }
                    	}).focus(function(){
                        	  <?php if( $type == 135 ){?>
                              $(this).autocomplete("search");
                              <?php }?>
                    	});
                  });
                	
                  </script>
                <div class="ui-widget">
                <input type='hidden' id='sPnumber' name='sPnumber' value='ab'>
                	<?php 
                	if( $type == 135 ){
                	   ?><input id="suppName" placeholder="search by name or number" style="height:16px; width:250px" <?="name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'" ?>>
                	   <?php 
                	}else{
                	    ?><input id="suppName" readonly style="height:16px; width:250px" <?="name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'" ?>>
                	   <?php
                	}
                	    
                	   ?>
					</div>
                 
                <?php 
                
            } elseif (($type == 135 || $type == 134 )&&(trim ( $attributeRow ['NAME07'] ) == "Supplier Number")){//LP0076_AD
                echo "<input type='text' readonly id='suppNumber' name='text" . $attribCount . "' class='big' value='" . trim ( $currentAttributeArray [$attributeRow ['ATTR07']] ) . "'";//LP0076_AD
                
   //***************************************** LP0077_AD END   ****************************************
                
            } else {
                   
                if( isset( $currentAttributeArray [$attributeRow ['ATTR07']] )){
                    $txtVal = trim ( $currentAttributeArray [$attributeRow ['ATTR07']] );
                }else{
                    $txtVal = "";
                }
                
                    echo "<input type='text' class='big' name='text" . $attribCount . "' value='$txtVal'>";
                }
        }

        if (trim ( $attributeRow ['NAME07'] ) == "Purchase Order Number" || trim ( $attributeRow ['NAME07'] ) == "Logistics Purchase Order" ) {
            $attributeArray ['poNumber'] = $currentAttributeArray [$attributeRow ['ATTR07']];
        }

        echo "</td>";
        echo "</tr>";
    }
    echo "<input type='hidden' name='attributeCount' value='" . $attribCount . "'>";
    
    if( !isset( $attributeArray['SODP'] ) || trim( $attributeArray['SODP'] ) == "" ){
        $attributeArray['SODP'] = " ";
    }
    if( !isset( $attributeArray['poNumber'] ) || trim( $attributeArray['poNumber'] ) == "" ){
        $attributeArray['poNumber'] = " ";
    }

    return $attributeArray;
}

/*
 * Returns an array of ticket attributes
 *
 * @parm integer $id Unique identifier of ticket
 * @return $attributeArray an array of attributes from CIL10
 */
function get_attribute_values($id) {
    global $CONO, $conn;
    
    $attributeValueSql = "SELECT ATTR10, TEXT10 FROM CIL10L01 WHERE CAID10 = $id order by attr10";
    $attributeValueRes = odbc_prepare ( $conn, $attributeValueSql );
    odbc_execute ( $attributeValueRes );
    
    $attribCount = 0;
    $attribArray = array();
    
    while ( $attributeValueRow = odbc_fetch_array ( $attributeValueRes ) ) {
        if( isset( $attributeValueRow ['TEXT10'] ) && $attributeValueRow ['TEXT10'] != "" && $attributeValueRow ['TEXT10'] != "0" ){

            $attribArray [trim ( $attributeValueRow ['ATTR10'] )] = trim ( $attributeValueRow ['TEXT10'] );
            
        }
    }

    return $attribArray;
}


/**
 * Function returns name of attribute
 *
 * @param integer $attributeType
 * @return string Attribute Name
 */
function get_attribute_text($attributeType) {
    
    if ($attributeType == "TEXT") {
        return "Text";
    } elseif ($attributeType == "DROP") {
        return "Drop Down";
    } elseif ($attributeType == "SODP") {
        return "DI Sales Order/Despatch Number";
    } elseif ($attributeType == "CURN") {
        return "Currency Codes";
    } elseif ($attributeType == "DATE") {
        return "Date";
    } elseif ($attributeType == "INVN") {
        return "Invoice Number";
    } elseif ($attributeType == "WYBL") {
        return "Carrier Waybill Number";
    } elseif ($attributeType == "PART") {
        return "Our Part Number";
    } elseif ($attributeType == "COUN") {
        return "Country Listing";
    } elseif ($attributeType == "BRAN") {
        return "Brand";
    } elseif ($attributeType == "MODL") {
        return "Model";
        
        //DI932 - Added to support Returns functionality
    } elseif ($attributeType == "DICU") {
        return "Customer Number/Sequence";
        
        //D0180 - Added to support Returns notifications
    } elseif ($attributeType == "REGN") {
        return "Region";    
        
    } elseif ($attributeType == "SPRT") { //LP0055_AD
        return "Supplier Part";//LP0055_AD
    }//LP0055_AD
     elseif ($attributeType == "SUPP") { //LP0055_AD
    return "Supplier ";//LP0055_AD
    }//LP0055_AD
}

/**
 * Function displays list of attribute types
 *
 * @param integer $attribute
 */
function list_attribute_types($attribute) {
    echo "<option ";
    if ($attribute == "TEXT") {
        echo "SELECTED ";
    }
    echo "value=TEXT>Free Text/Numeric</option>";
    echo "<option ";
    if ($attribute == "DATE") {
        echo "SELECTED ";
    }
    echo "value=DATE>Date</option>";
    echo "<option ";
    if ($attribute == "DROP") {
        echo "SELECTED ";
    }
    echo "value=DROP>Drop Down</option>";
    echo "<option ";
    if ($attribute == "CURN") {
        echo "SELECTED ";
    }
    echo "value=CURN>Currency Code</option>";
    echo "<option ";
    if ($attribute == "PART") {
        echo "SELECTED ";
    }
    echo "value=PART>Our Part Number</option>";
    echo "<option ";
    if ($attribute == "INVN") {
        echo "SELECTED ";
    }
    echo "value=INVN>Invoice Number</option>";
    echo "<option ";
    if ($attribute == "WYBL") {
        echo "SELECTED ";
    }
    echo "value=WYBL>Carrier Waybill Number</option>";
    echo "<option ";
    if ($attribute == "SODP") {
        echo "SELECTED ";
    }
    echo "value=SODP>Sales Order/Dispatch Number</option>";
    echo "<option ";
    if ($attribute == "COUN") {
        echo "SELECTED ";
    }
    echo "value=COUN>Country Listing</option>";
    echo "<option ";
    if ($attribute == "BRAN") {
        echo "SELECTED ";
    }
    echo "value=BRAN>Brand</option>";
    echo "<option ";
    if ($attribute == "MODL") {
        echo "SELECTED ";
    }
    echo "value=MODL>Model</option>";
    
    //DI932 - Added to support Returns functionality
    echo "<option ";
    if ($attribute == "DICU") {
        echo "SELECTED ";
    }
    echo "value=DICU>Customer Number/Sequence</option>";
    
    //D0180 - Added to support Returns notification process
    echo "<option ";
    if ($attribute == "REGN") {
        echo "SELECTED ";
    }
    echo "value=REGN>Region</option>";
    echo "<option ";//LP0055_AD
    if ($attribute == "SPRT") {//LP0055_AD
        echo "SELECTED ";//LP0055_AD
    }//LP0055_AD
    echo "value=SPRT>Supplier Part</option>";//LP0055_AD
    
    echo "<option ";//LP0055_AD
    if ($attribute == "SUPP") {//LP0055_AD
        echo "SELECTED ";//LP0055_AD
    }//LP0055_AD
    echo "value=SUPP>Supplier</option>";//LP0055_AD
    
}

/**
 * Function retrieves and displays ticket header information for DRP tickets
 *
 * @param string $ORDN70
 * @param string $DESN
 * @param string $CATN70
 */
function get_related_header_info_drp($ORDN70, $DESN, $CATN70) {
    global $CONO, $conn;
    
    $sql = "SELECT DSEQ55, CUSN55, ";
    $sql .= "DTCO40, CURN40";
    $sql .= " FROM OEP55J98 WHERE CONO55 = '$CONO' AND ORDN55 = '$ORDN70' AND CATN55 = '$CATN70' FETCH FIRST ROW ONLY optimize for 1 row";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        
        $DSEQ55 = $row ['DSEQ55'];
        
        if (! $DSEQ55) {
            $DSEQ55 = "000";
        }
        
        echo "<tr>";
        echo "<td class='bold' width=30%>&nbsp;&nbsp;&nbsp;***DRP ORDER***</td>";
        echo "</tr>";
        
        $cusSql = "SELECT CUSN05, DSEQ05,";
        $cusSql .= "CNAM05, CAD105, CAD205,";
        $cusSql .= " CAD305, CAD405, ";
        $cusSql .= " CAD505, PCD105, ";
        $cusSql .= " PCD205, PHON05 ";
        $cusSql .= "FROM CUSNAMES WHERE";
        $cusSql .= " CONO05 = '$CONO' AND CUSN05 = '" . $row ['CUSN55'] . "' AND DSEQ05 = '" . $row ['DSEQ55'] . "' AND CGP205 <> 'DSH' FETCH FIRST ROW ONLY optimize for 1 row";
        $cusRes = odbc_prepare ( $conn, $cusSql );
        odbc_execute ( $cusRes );
        
        while ( $cusRow = odbc_fetch_array ( $cusRes ) ) {
            
            $CNAM05 = $cusRow ['CNAM05'];
            $CAD105 = $cusRow ['CAD105'];
            $CAD205 = $cusRow ['CAD205'];
            $CAD305 = $cusRow ['CAD305'];
            $CAD405 = $cusRow ['CAD405'];
            $CAD505 = $cusRow ['CAD505'];
            $PCD105 = $cusRow ['PCD105'];
            $PCD205 = $cusRow ['PCD205'];
            $PHON05 = $cusRow ['PHON05'];
        }
        
        echo "<tr>";
        echo "<td class='bold' width=30%>&nbsp;&nbsp;&nbsp;Customer</td>";
        echo "<td>" . $row ['CUSN55'] . "-" . $row ['DSEQ55'] . "  $CNAM05</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td width=30% class='bold'>&nbsp;&nbsp;&nbsp;Customer Order Date</td>";
        $TXT_DATE = format_JBA_Date ( $row ['DTCO40'] );
        echo "<td align=left>$TXT_DATE</td>";
        echo "</tr>";
        
        $shipSql = "SELECT CONS68 FROM OEP68U WHERE CONO68 = '$CONO' AND";
        $shipSql .= " ORDN68 = '" . trim ( $ORDN70 ) . "' AND DESN68 = '" . trim ( $DESN ) . "' FETCH FIRST ROW ONLY optimize for 1 row";
        $shipRes = odbc_prepare ( $conn, $shipSql );
        odbc_execute ( $shipRes );
        
        while ( $shipRow = odbc_fetch_array ( $shipRes ) ) {
            
            $weightSql = "SELECT WGHT66 FROM OEP66U ";
            $weightSql .= "WHERE CONO66 = '$CONO' AND CONS66 = '" . trim ( $shipRow ['CONS68'] ) . "' AND PACK66 = 1 FETCH FIRST ROW ONLY optimize for 1 row";
            $weightRes = odbc_prepare ( $conn, $weightSql );
            odbc_execute ( $weightRes );
            
            while ( $weightRow = odbc_fetch_array ( $weightRes ) ) {
                if( isset($row ['WGHT66'])){
                    $TOT_WEIGHT = $row ['WGHT66'];
                }else{
                    $TOT_WEIGHT = 0;
                }
            }
        }
        
        echo "<tr>";
        echo "<td width=30% class='bold'>&nbsp;&nbsp;&nbsp;Total Weight</td>";
        echo "<td align=left>$TOT_WEIGHT KGS";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td width=30% class='bold'>&nbsp;&nbsp;&nbsp;Currency</td>";
        echo "<td align=left>" . $row ['CURN40'] . "</td>";
        echo "</tr>";
        
    }
}

/**
 * Function retrieves and displays related information for DRP tickets
 *
 * @param string $WHERE_CLAUSE
 * @param string $DESN
 */
function get_related_details_info_drp($WHERE_CLAUSE, $DESN) {
    global $CONO, $conn;
    
    
    $sql = "SELECT DSEQ55, CUSN55, DTCO40, CURN40, CATN55, LOCD55, ORDN55, QTOR55";
    $sql .= " FROM OEP55J98 WHERE CONO55 = '$CONO' $WHERE_CLAUSE ORDER BY ORDL55 ASC";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    echo "<tr>";
    echo "<td colspan=2>";
    
    echo "<table border=0 cellspacing=0 cellpadding=0 width=100%>";
    echo "<tr>";
    
    if (strpos ( $WHERE_CLAUSE, "CATN" ) == "0") {
        
        echo "<td bgcolor=#0060A0 NOWRAP class='bold'>Sales Order Number/<br>Customer PO</td>";
        echo "<td bgcolor=#0060A0 NOWRAP class='bold'>Part Number/<br>Description</td>";
    } else {
        echo "<td bgcolor=#0060A0 NOWRAP class='bold'>Item Number/<br>Description</td>";
    }
    echo "<td bgcolor=#0060A0 width=300 class='bold'>Shipping<br>Stockroom</td>";
    echo "<td bgcolor=#0060A0 class='title'>Qty&nbsp;</td>";
    echo "</tr>";
    $ROW_COLOR_FLAG = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        
        if (isset( $row ['DESN70'] ) && $row ['DESN70'] == $DESN) {
            
            echo "<tr bgcolor=red>";
        } else {
            
            
            if ($ROW_COLOR_FLAG == 0) {
                
                echo "<tr class='alternate'>";
                $ROW_COLOR_FLAG = 1;
            } else {
                
                echo "<tr>";
                $ROW_COLOR_FLAG = 0;
            }
        }
        
        $partDescription = get_part_description ( $row ['CATN55'] );
        
        if (strpos ( $WHERE_CLAUSE, "CATN" ) == "0") {
            
            echo "<td align=left>";
            if ($row ['DESN55'] == $DESN) {
                echo "<b>";
            }
            echo "<a name=PICK_ " . $row ['DESN55'] . ">" . $row ['ORDN55'] . "</a>/<br>" . $row ['CUSO40'];
            if ($row ['DESN55'] == $DESN) {
                echo "</b>";
            }
            echo "</td>";
            echo "<td align=left>";
            echo $row ['CATN55'] . "/<br>$partDescription";
            echo "</td>";
        } else {
            echo "<td align=left>" . $row ['CATN55'] . "/ <br>$partDescription</td>";
        }
        $stockRoomName = get_stockroom_desc ( $row ['LOCD55'] );
        echo "<td align=left>$stockRoomName</td>";
        $QTOR55 = str_replace ( ".", " ", $row ['QTOR55'] );
        echo "<td class='center'>";
        if (strpos ( $row ['QTOR55'], "." )) {
            echo substr ( $row ['QTOR55'], 0, strpos ( $row ['QTOR55'], "." ) );
        } else {
            echo $row ['QTOR55'];
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</td>";
    echo "</tr>";
}

/**
 *
 * Display retlated information for non drp tickets
 *
 * @parm string WHERE_CLAUSE    where clause to define SQL statement
 * @parm varchar DSEN
 *
 * @return none
 */
function get_related_details_info($WHERE_CLAUSE, $DESN) {
    global $conn, $CONO;
    
    $sql = "SELECT DESN70, CATN70, ORDN70, INVN70, LQTY70, UPRC70, BLIV70, LOCD70 ";
    $sql .= "FROM OEP70 WHERE CONO70 = '$CONO' $WHERE_CLAUSE";
    $sql .= "group by CATN70, ORDN70, INVN70, LQTY70, UPRC70, BLIV70, LOCD70, ORDL70, DESN70 order by ORDL70 ASC";
    
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    
    echo "<tr>";
    echo "<td colspan=2>";
    echo "<table border=0 cellspacing=0 cellpadding=0 width=100%>";
    echo "<tr class='header'>";
    echo "<td width=20% class='header'>Sales Order Number/<br>Customer PO</td>";
    echo "<td width=20% class='header'>Item Number/<br>Description</td>";
    
    echo "<td width=10% class='header'><b>INV#</td>";
    echo "<td width=10% class='header'>Shipping<br>Stockroom</td>";
    echo "<td class='header'>Qty&nbsp;</b></font></td>";
    echo "<td class='header'>Unit<br>Price</td>";
    echo "<td class='header'>Line<br>Value</td>";
    echo "</tr>";
    $ROW_COLOR_FLAG = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        
        if ($row ['DESN70'] == $DESN) {
            
            echo "<tr bgcolor=red>";
        } else {
            
            if ($ROW_COLOR_FLAG == 0) {
                
                echo "<tr class='alternate'>";
                $ROW_COLOR_FLAG = 1;
            } else {
                
                echo "<tr>";
                $ROW_COLOR_FLAG = 0;
            }
        }
        
        $partDescription = get_part_description ( $row ['CATN70'] );
     
        
        echo "<td align=left>";
        if ($row ['DESN70'] == $DESN) {
            echo "<b>";
        }
        $CUSO65b = get_customer_ordernumber ( "WHERE CONO40 = '$CONO' AND ORDN40 = '" . $row ['ORDN70'] . "'" );
        
        if( isset($CUSO65b) ) {
            echo $row ['ORDN70'] . " " . $DESN . "/<br>$CUSO65b";
        } elseif( isset( $row['CUSO65'] )) {
            echo $row ['ORDN70'] . " " . $DESN . "/<br>" . $row['CUSO65'];
        }else{
            echo $row ['ORDN70'] . " " . $DESN . "/<br>";
        }
        
        if ($row ['DESN70'] == $DESN) {
            echo "</b>";
        }
        echo "</td>";
        echo "<td align=left>";
        echo $row ['CATN70'] . "/<br>$partDescription";
        echo "</td>";
        
        echo "<td align=left>";
        echo $row ['INVN70'];
        echo "</td>";
        
        $stockRoomName = get_stockroom_desc ( $row ['LOCD70'] );
        
        echo "<td>$stockRoomName</td>";
        echo "<td class='center'>";
        echo number_format ( $row ['LQTY70'], 2 );
        echo "</td>";
        
        echo "<td class='center'>";
        echo number_format ( $row ['UPRC70'], 2 );
        echo "</td>";
        
        echo "<td class='center'>";
        echo number_format ( $row ['BLIV70'], 2 );
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</td>";
    echo "</tr>";
}
/**
 * Function displays Account Manager Suggestions
 *
 * @param integer $type
 * @param integer $id
 * @param integer $choice
 * @param string $impact
 */
function display_am_suggestions($type, $id, $choice, $impact) {
    global $conn, $CONO;
    
    $sql = "SELECT TEXT19 FROM CIL19L00 WHERE TYPE19 = $type ORDER BY PREC19 ASC";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    echo "<tr>";
    echo "<td>It is suggested that you:</td>";
    echo "</tr>";
    
    $numberDisplay = 0;
    while ( $row = odbc_fetch_array ( $res ) ) {
        $numberDisplay ++;
        echo "<tr>";
        echo "<td colspan='2'>&nbsp&nbsp&nbsp&nbsp$numberDisplay. " . $row ['TEXT19'] . "</td>";
        echo "</tr>";
    }
}

/**
 * Function returns short description of a ticket
 *
 * @param integer $ticketId
 * @return string ShortDescription
 */
function get_short_description($ticketId) {
    global $conn, $CONO;
    
    $sql = "select DESC01 FROM CIL01L00 WHERE ID01=$ticketId FETCH FIRST ROW ONLY";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    while ( $row = odbc_fetch_array ( $res ) ) {
        return $row ['DESC01'];
    }
}

function getReturnedStockroom($attributeId) {
    global $conn;
    $sql = "SELECT NAME07 FROM CIL07 WHERE ATTR07 = $attributeId";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        $stockRoomId = substr( $row['NAME07'], 0, 2 );
    }
    return $stockRoomId;
}
//****************************** LP0076_AD START *************************************
//Function return stockroom Code (like C1) for speciffied ticket
function getStockroomFromDropdown($ticketId,$type,$dropName) {//LP0076_AD
    global $conn;//LP0076_AD
    $sql = "SELECT ATTR07 FROM CIL07 WHERE NAME07 = '".$dropName."' AND TYPE07 = ".$type;//LP0076_AD
    $res = odbc_prepare ( $conn, $sql );//LP0076_AD
    odbc_execute ( $res );    //LP0076_AD
    while ( $row = odbc_fetch_array ( $res ) ) {//LP0076_AD
        $attrParent =  $row['ATTR07'];//LP0076_AD
    }//LP0076_AD
    $sql = "SELECT TEXT10 FROM cil10l01 WHERE ATTR10 = ".$attrParent." AND CAID10 = ".$ticketId;//LP0076_AD
    $res = odbc_prepare ( $conn, $sql );//LP0076_AD
    odbc_execute ( $res );//LP0076_AD
    while ( $row = odbc_fetch_array ( $res ) ) {//LP0076_AD
        $attrNr =  $row['TEXT10'];//LP0076_AD
    }//LP0076_AD
    $sql = "SELECT NAME07 FROM CIL07 WHERE ATTR07 = ".$attrNr;//LP0076_AD
    $res = odbc_prepare ( $conn, $sql );//LP0076_AD
    odbc_execute ( $res );//LP0076_AD
    while ( $row = odbc_fetch_array ( $res ) ) {//LP0076_AD
        $stockRoomId = substr( $row['NAME07'], 0, 2 );//LP0076_AD
    }//LP0076_AD
    
    
    return $stockRoomId;//LP0076_AD
}
//****************************** LP0076_AD END ***************************************
//D0129 - Function retrieves and returns supporting infomation based on ticket ID
function get_supporting_information( $id ){
    global $conn, $CONO;
    
    $ticketDetails = get_base_ticket_details( $id );
    
    $sql = "select NAME07, TEXT10, HTYP07, ATTR10 from CIL10 T1"
                    . " INNER JOIN CIL07 T2"
                    . " ON T1.ATTR10 = CAST( T2.ATTR07 as varchar( 10 ) )"
                    . " WHERE CAID10=$id AND HTYP07 <> 'PART' AND NAME07 <> 'Quantity Required'";
                    
                    
                    $res = odbc_prepare( $conn, $sql );
                    odbc_execute ( $res );
                    
                    $supportingArray = array();
                    while( $row = odbc_fetch_array( $res ) ){
                        
                        $rowVals[] = "";
                        
                        $rowVals['name'] = trim($row['NAME07']);
                        
                        switch ( trim($row['HTYP07']) ){
                            case "COUN":
                                $rowVals['value'] = get_market_area_name( trim($row['TEXT10']) );
                                break;
                                
                            case "DROP":
                                $rowVals['value'] = get_drop_down_value( $ticketDetails['CLAS01'], $ticketDetails['TYPE01'], trim($row['ATTR10']), trim($row['TEXT10']) );
                                break;
                                
                            case "DATE":
                                $rowVals['value'] = formatDate (trim($row['TEXT10']));
                                break;
                                
                            default:
                                $rowVals['value'] = trim($row['TEXT10']);
                                break;
                        }
                        
                        
                        array_push( $supportingArray, $rowVals );
                    }
                    
                    return $supportingArray;
}

//D0129 - Function to get base ticket details
function get_base_ticket_details( $ticketId ){
    global $conn;
    
    if( isset( $ticketId ) && is_numeric( $ticketId ) ){
        $sql = "SELECT * FROM CIL01 WHERE ID01=" . trim($ticketId);
        
        
        $res = odbc_prepare( $conn, $sql );
        if( odbc_execute ( $res ) ){
            
        }else{
            
            $handle = fopen("./sqlFailures/sqlFails.csv","a+");
            fwrite($handle, "1339 - get_base_ticket_details/ticketFunctions.php," . $sql . "\n" );
            fclose($handle);
        }
        
        while( $row = odbc_fetch_array( $res ) ){
            return $row;
        }
    }
    
}

//D0260 - Function to retrieve part snapshot information
function get_part_snapshot_info( $issue ){
    global $CONO, $conn;
    
    $sql = "SELECT * FROM CIL33 WHERE ISSU33=$issue";
    
    $res = odbc_prepare( $conn, $sql );
    odbc_execute ( $res );
    
    $row = odbc_fetch_array( $res );
    
    return $row;
}

//D0270 - Supervisor reports enhancement
//This function will return count of issues
function get_issue_count( $userId, $escalationLevel ){
    global $conn;
    
    if( $escalationLevel == 0 || $escalationLevel == "" ){
        $escalationClause = "=0";
    }elseif ( $escalationLevel == 1  ){
        $escalationClause = "=1";
    }else{
        $escalationClause = ">=2";
    }
    
    
    $sql = "SELECT count(ID01) as ISSUECOUNT FROM CIL01"
                    . " WHERE STAT01=1 AND OWNR01 = $userId AND ESLV01 $escalationClause";
                    
                    $res = odbc_prepare ( $conn, $sql );
                    odbc_execute ( $res );
                    
                    
                    while ( $row = odbc_fetch_array ( $res ) ) {
                        return $row['ISSUECOUNT'];
                        
                    }
                    
}

// imported from functions.php
function display_related_information($class, $type, $partNumber, $orderNumber, $from, $poNumber) {
//lp0055_AD2    global $conn, $CONO;
    global $conn, $CONO,$supplierNumber130;//lp0055_AD2 
    if ($type != 24) {
        echo "<tr><TD>&nbsp</td></tr>";
        echo "<tr>";
        echo "<TD colspan='2'>";
        echo "<hr>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<TD class='boldBig' colspan='2'>Related Information</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<TD colspan='2'>";
        echo "<hr>";
        echo "</td>";
        echo "</tr>";
        
        if( $orderNumber ){
            
            $customerInfoArray = get_customer_by_orderNumber( $orderNumber );
            
            if( isset($customerInfoArray['customerNumber']) ){

                echo "<tr>";
                echo "<TD colspan='2' class='bold'>";
                echo "Customer:   " . $customerInfoArray['customerNumber'];
                if( $customerInfoArray['site'] ){
                    echo " - " . $customerInfoArray['site'];
                }
                if( $customerInfoArray['customerName'] ){
                    echo " ( " . $customerInfoArray['customerName'] . " ) ";
                }
                echo "</td>";
                echo "</tr>";
            }
        }
        if ($class == 8 || ( $class == 3 && $type == 42 ) ) { //LP0022 - Add Expedite classification logic
            switch ($type) {
                case 42 :
                    
                    if ($from != "add") {
                        $relatedInfo [] = "";
                        $receivingInfo [] = "";
                        $relatedInfo = get_po_number ( $partNumber, $orderNumber );
                        
                        if ($relatedInfo ['PO']) {
                            $receivingInfo = get_receiving_info ( $partNumber, $relatedInfo ['PO'] );
                        } else {
                            $receivingInfo = get_receiving_info ( $partNumber, "" );
                        }
                        $showInputs = false;
                    } else {
                        $showInputs = true;
                    }
                    
                    //**** Start of Fix  - r-5097616 *****************//
                    //**LP0041  $itemInformationSql = "SELECT PLAN35, PDES35, PLNN06 FROM PARTS T1"
                    $itemInformationSql = "SELECT PLAN35, PDES35, PTYP35, PCLS35, PGMJ35, PLNN06, DIVN35 "         //**LP0041
                    . " FROM PARTS T1"                                                         //**LP0041
                    . " INNER JOIN PMP06 T2"
        . " ON T1.PLAN35 = T2.PLAN06"
        . " WHERE CONO35='DI' AND PNUM35='$partNumber'";
        
        
        $itemInformationRes= odbc_prepare ( $conn, $itemInformationSql);
        odbc_execute ( $itemInformationRes);
        
        while ( $itemInfoRow = odbc_fetch_array( $itemInformationRes) ) {
            $planNumber = $itemInfoRow['PLAN35'];
            $planName = $itemInfoRow['PLNN06'];
            $partDescription = $itemInfoRow['PDES35'];
            $itemType = $itemInfoRow['PTYP35'];                //**LP0041
            $itemClass = $itemInfoRow['PCLS35'];               //**LP0041
            $itemGroupMajor = $itemInfoRow['PGMJ35'];          //**LP0041
            $itemDivision = $itemInfoRow['DIVN35'];            //**LP0041
            $itemSubPA = substr($itemDivision, 0, 1);          //**LP0041
            $itemPA = getVATC15("DI", "_MIA", $itemSubPA);     //**LP0041
            
        }
        //**** End of Fix  - r-5097616 *****************//
        
        echo "<tr>";
        echo "<TD class='bold'>Description</td>";
        if ($showInputs) {
            echo "<TD><input type='text' name='relatedDescription' class='longNoBorder' value='' disabled></td>";
        } else {
            //echo "<TD>" . trim ( $receivingInfo ['PDES'] ) . "</td>";
            echo "<TD>" . trim ( $partDescription) . "</td>";          //r-5097616 - Change Datasource
        }
        
        echo "</tr>";                                                                                                          //**LP0041
        echo "<tr>";                                                                                                           //**LP0041
        echo "<TD class='bold'>Item Type</td>";                                                                                //**LP0041
        if ($showInputs) {                                                                                                     //**LP0041
            echo "<TD><input type='text' name='relatedItemType' value='' class='longNoBorder' disabled></td>";                 //**LP0041
        } else {                                                                                                               //**LP0041
            echo "<TD>" . trim($itemType) .  " - " . getDescriptionINP15("DI", "PTYP", trim($itemType)) . "</td>";             //**LP0041
        }                                                                                                                      //**LP0041
        echo "</tr>";                                                                                                          //**LP0041
        echo "<tr>";                                                                                                           //**LP0041
        echo "<TD class='bold'>Item Class</td>";                                                                               //**LP0041
        if ($showInputs) {                                                                                                     //**LP0041
            echo "<TD><input type='text' name='relatedItemClass' value='' class='longNoBorder' disabled></td>";                //**LP0041
        } else {                                                                                                               //**LP0041
            echo "<TD>" . trim($itemClass) . " - " . getDescriptionINP15("DI", "PCLS", trim($itemClass)) . "</td>";            //**LP0041
        }                                                                                                                      //**LP0041
        echo "</tr>";                                                                                                          //**LP0041
        echo "<tr>";                                                                                                           //**LP0041
        echo "<TD class='bold'>Item Group Major</td>";                                                                         //**LP0041
        if ($showInputs) {                                                                                                     //**LP0041
            echo "<TD><input type='text' name='relatedItemGroupMajor' value='' class='longNoBorder' disabled></td>";           //**LP0041
        } else {                                                                                                               //**LP0041
            echo "<TD>" . trim($itemGroupMajor) . " - " . getDescriptionINP15("DI", "PGMJ", trim($itemGroupMajor)) . "</td>";  //**LP0041
        }                                                                                                                      //**LP0041
        
        echo "<tr>";                                                                                                           //**LP0041
        echo "<TD class='bold'>Product Area</td>";                                                                             //**LP0041
        if ($showInputs) {                                                                                                     //**LP0041
            echo "<TD><input type='text' name='relatedProductArea' value='' class='longNoBorder' disabled></td>";              //**LP0041
        } else {                                                                                                               //**LP0041
            echo "<TD>" . trim($itemPA) . " - " . getDescriptionINP15("DI", "_MIC", trim($itemPA)) . "</td>";                  //**LP0041
        }                                                                                                                      //**LP0041
        echo "<tr>";                                                                                                           //**LP0041
        echo "<TD class='bold'>Sub Product Area</td>";                                                                         //**LP0041
        if ($showInputs) {                                                                                                     //**LP0041
            echo "<TD><input type='text' name='relatedSubProductArea' value='' class='longNoBorder' disabled></td>";           //**LP0041
        } else {                                                                                                               //**LP0041
            echo "<TD>" . trim($itemSubPA) . " - " . getDescriptionINP15("DI", "_MIA", trim($itemSubPA)) . "</td>";            //**LP0041
        }                                                                                                                      //**LP0041
        
        
        
        echo "</tr>";
        echo "<tr>";
        echo "<TD class='bold'>Supplier</td>";
        if ($showInputs) {
            echo "<TD><input type='text' name='relatedSupplier' value='' class='longNoBorder' disabled></td>";
        } else {
            echo "<TD>" . trim ( $receivingInfo ['SNAM'] ) . "</td>";
        }
        echo "</tr>";
        echo "<tr>";
        echo "<TD class='bold'>Supplier Part#</td>";
        if ($showInputs) {
            echo "<TD><input type='text' name='relatedPart' value='' class='longNoBorder' disabled></td>";
        } else {
            echo "<TD>" . trim ( $receivingInfo ['VCAT'] ) . "</td>";
        }
        echo "</tr>";
        echo "<tr>";
        echo "<TD class='bold'>Buyer</td>";
        if ($showInputs) {
            echo "<TD><input type='text' name='relatedBuyer' class='longNoBorder' value='' disabled></td>";
        } else {
            //echo "<TD>" . trim ( $receivingInfo ['PLAN'] ) . " - " . trim ( $receivingInfo ['PLNN'] ) . " ****** - Removed - r-5097616
            echo "<TD>" . trim ( $planNumber) . " - " . trim ( $planName) . "</td>";   //r-5097616 - Change Datasource
        }
        echo "</tr>";
        if ($relatedInfo ['PO'] || $showInputs) {
            echo "<tr>";
            echo "<TD class='bold'>PO #</td>";
            if ($showInputs) {
                echo "<TD><input type='text' name='relatedPo' class='longNoBorder' value='' disabled></td>";
            } else {
                echo "<TD>" . trim ( $relatedInfo ['PO'] ) . "</td>";
            }
            echo "</tr>";
        }
        
        if ($relatedInfo ['DRP'] || $showInputs) {
            echo "<tr>";
            
            if ($showInputs && $relatedInfo ['DRP']) {
                echo "<TD class='bold'>DRP #</td>";
                echo "<TD><input type='text' name='relatedDrp' class='longNoBorder' value='' disabled></td>";
            } else {
                echo "<TD class='bold'>DRP #</td>";
                echo "<TD>" . trim ( $relatedInfo ['DRP'] ) . "</td>";
            }
            echo "</tr>";
        }
        if ($relatedInfo ['PO'] || $showInputs) {
            echo "<tr>";
            //D109 - Change to get Receipt
            $rDateInfo = get_receipt_date_info ( $partNumber, $relatedInfo ['PO'] );
            
            if ($showInputs && $relatedInfo ['PO']) {
                echo "<TD class='bold'>Purchasing Flag</td>";
                echo "<TD><input type='text' name='relatedFlag' class='longNoBorder' value='' disabled></td>";
            } else {
                echo "<TD class='bold'>Purchasing Flag</td>";
                echo "<TD>" . trim ( $rDateInfo ['FLAG'] ) . "</td>";
            }
            echo "</tr>";
        }
        
        if ( isset( $rDateInfo ['RDAT'] ) && trim($rDateInfo ['RDAT']) != "" || $showInputs) {
            
            if ($showInputs && trim ( $rDateInfo ['RDAT'] ) != "") {
                echo "<TD class='bold'>Receipt Date</td>";
                echo "<TD><input type='text' name='relatedDue' class='longNoBorder' value='' disabled></td>";
            } elseif (trim ( $rDateInfo ['DDAT'] ) != "") {
                echo "<TD class='bold'>Receipt Date</td>";
                echo "<TD>" . format_JBA_Date ( trim ( $rDateInfo ['DDAT'] ) ) . "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<TD class='bold'>PO Date</td>";
                echo "<TD>" . format_JBA_Date ( trim ( $rDateInfo ['RDAT'] ) ) . "</td>";
            } elseif (trim ( $rDateInfo ['RDAT'] ) != "" && trim($rDateInfo['DDAT']) == "") {
                echo "<TD class='bold'>Receipt Date</td>";
                echo "<TD>" . format_JBA_Date ( trim ( $rDateInfo ['DDAT'] ) ) . "</td>";
            }
            echo "</tr>";
        }
        
        if ( isset($showInputs) || isset($followDate) || $followDate != "") {
            $followDate = get_follow_up_date ( $partNumber, $relatedInfo ['PO'] );
            if ($showInputs && $followDate != "") {
                echo "<TD class='bold'>Follow-Up Date</td>";
                echo "<TD><input type='text' name='relatedFollow' value='' class='longNoBorder' disabled></td>";
            } elseif ($followDate != "") {
                echo "<TD class='bold'>Follow-Up Date</td>";
                echo "<TD>" . format_JBA_Date ( trim ( $followDate ) ) . "</td>";
            }
            echo "</tr>";
        }
        
        break;
                case 43 :
                    
                    $supplierSql = "call SUPLP01( '$partNumber' )";
                    $supplierRes = odbc_prepare ( $conn, $supplierSql );
                    odbc_execute ( $supplierRes );
                    
                    
                    while ( $supplierRow = odbc_fetch_array ( $supplierRes ) ) {
                        echo "<tr>";
                        echo "<TD class='bold'>Supplier</td>";
                        echo "<TD>" . trim ( $supplierRow['DSSP35'] ) . " - " . trim ( $supplierRow['SNAM05'] ) . "</td>";
                        echo "</tr>";
                        $supplierNumber = trim ( $supplierRow['DSSP35'] );
                        echo "<tr>";
                        echo "<TD class='bold'>Part Description</td>";
                        echo "<TD>" . trim ( $supplierRow['PDES35'] ) . "</td>";
                        echo "</tr>";
                    }
                    if( !isset( $supplierNumber ) ){
                        $supplierNumber = "";
                    }
                    $supplierCostArray = get_supplier_cost ( $partNumber, $supplierNumber );
                    
                    if ($_SESSION ['authority'] != "E") {
                        echo "<tr>";
                        echo "<TD class='bold'>Supplier Cost</td>";
                        echo "<TD>" . trim ( $supplierCostArray ['price'] ) . " - " . trim ( $supplierCostArray ['currency'] ) . "</td>";
                        echo "</tr>";
                    }
                    break;
                case 44 :
                    
                    $partNumber = trim ( $partNumber );
                    $supplierSql = "call SUPLP01( '$partNumber' )";
                    $supplierRes = odbc_prepare ( $conn, $supplierSql );
                    odbc_execute ( $supplierRes );
                    
                    while ( $supplierRow = odbc_fetch_array ( $supplierRes ) ) {
                        echo "<tr>";
                        echo "<TD class='bold'>Supplier</td>";
                        echo "<TD>" . trim ( $supplierRow['DSSP35'] ) . " - " . trim ( $supplierRow['SNAM05'] ) . "</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<TD class='bold'>Part Description</td>";
                        echo "<TD>" . trim ( $supplierRow['PDES35'] ) . "</td>";
                        echo "</tr>";
                    }
                    break;
                    //******************************************** LP0055_AD START *********************************************************************
                case 130 :
                    $partNumber = trim ( $partNumber );
                    $supNameSql = "SELECT SNAM05 FROM PLP05 WHERE CONO05='DI' AND DSEQ05='000' AND SUPN05='".trim ( $supplierNumber130 )."'";
                    $supNameRes = odbc_prepare ( $conn, $supNameSql );
                    odbc_execute ( $supNameRes );
                    $supNameRow = odbc_fetch_array($supNameRes);
                    
                    $supplierSql = "call SUPLP01( '$partNumber' )";
                    $supplierRes = odbc_prepare ( $conn, $supplierSql );
                    odbc_execute ( $supplierRes );
                    
                    while ( $supplierRow = odbc_fetch_array ( $supplierRes ) ) {
                        echo "<tr>";
                        echo "<TD class='bold'>Supplier</td>";
                        echo "<TD>" . trim ( $supplierNumber130 ) . " - " . trim ( $supNameRow ['SNAM05'] ) . "</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<TD class='bold'>Part Description</td>";
                        echo "<TD>" . trim ( $supplierRow['PDES35'] ) . "</td>";
                        echo "</tr>";
                    }
                    $supplierNumber=$supplierNumber130;
                    $supplierCostArray = get_supplier_cost ( $partNumber, $supplierNumber );
                    
                    if ($_SESSION ['authority'] != "E") {
                        echo "<tr>";
                        echo "<TD class='bold'>Supplier Cost</td>";
                        echo "<TD>" . trim ( $supplierCostArray ['price'] ) . " - " . trim ( $supplierCostArray ['currency'] ) . "</td>";
                        echo "</tr>";
                    }
                    break;
                    //******************************************** LP0055_AD END ***********************************************************************
                    //******************************************** LP0068_AD START *********************************************************************
                case 133 : //LP0068_AD
                    $partNumber = trim ( $partNumber );//LP0068_AD
                    $supNameSql = "SELECT SNAM05 FROM PLP05 WHERE CONO05='DI' AND DSEQ05='000' AND SUPN05='".trim ( $supplierNumber130 )."'";//LP0068_AD
                    $supNameRes = odbc_prepare ( $conn, $supNameSql );//LP0068_AD
                    odbc_execute ( $supNameRes );//LP0068_AD
                    $supNameRow = odbc_fetch_array($supNameRes);//LP0068_AD
                    
                    $supplierSql = "call SUPLP01( '$partNumber' )";//LP0068_AD
                    $supplierRes = odbc_prepare ( $conn, $supplierSql );//LP0068_AD
                    odbc_execute ( $supplierRes );//LP0068_AD
                    
                    $supplierSRSql = "select STRCVR from ICPVRF where CONOVR = 'DI' and VNDRVR = '".$supplierNumber130."'";//LP0068_AD
                  // echo $supplierSRSql;
                    $supplierSRRes = odbc_prepare ( $conn, $supplierSRSql );//LP0068_AD
                    odbc_execute ( $supplierSRRes );//LP0068_AD
                    $supplierSR= odbc_fetch_array ( $supplierSRRes ); //LP0068_AD
                    
                    $stockSQL="select STRC60,SAVL60, SALC60, SOOR60, SITS60  from INP60 where  STRC60 IN ('C1','C2','C3','C4','63','67') AND CONO60 = 'DI' AND PNUM60 ='".$partNumber."'";//LP0068_AD
                    $stockRes=odbc_prepare ( $conn,$stockSQL);//LP0068_AD
                    odbc_execute ( $stockRes );//LP0068_AD
                    
                    while ( $supplierRow = odbc_fetch_array ( $supplierRes ) ) {//LP0068_AD
                        echo "<tr>";//LP0068_AD
                        echo "<TD class='bold'>Supplier</td>";//LP0068_AD
                        echo "<TD>" . trim ( $supplierNumber130 ) . " - " . trim ( $supNameRow ['SNAM05'] ) . "</td>";//LP0068_AD
                        echo "</tr>";//LP0068_AD
                        echo "<tr>";//LP0068_AD
                        echo "<TD class='bold'>Vendor Defined Stock Room </td>";//LP0068_AD
                        echo "<TD>" . trim ( $supplierSR ['STRCVR'] ) . "</td>";//LP0068_AD
                        echo "</tr>";//LP0068_AD
                        echo "<tr>";//LP0068_AD
                        echo "<TD class='bold'>Current Inventory Level </td>";//LP0068_AD
                        echo "<TD>";
                        //LP0084_TS?> <TABLE Border='1' style = "border-collapse: collapse; " width=90%> <?php ;//LP0068_AD
                        ?> <TABLE Border='1' style = "border-collapse: collapse; " width=90%> <?php ;//LP0084_TS
                        
                        echo "<tr>";//LP0068_AD
                        echo "<TD> StockRoom <TD> Available <TD>Allocated <TD>OnOrder<TD>InTransit" ;//LP0068_AD
                        echo "</tr>";//LP0068_AD
                        $sRowCounter = 0;//LP0084_TS
                        while ( $stockRow = odbc_fetch_array ( $stockRes ) ) {//LP0068_AD
                            $sRowCounter++;//LP0084_TS
                            
                            if( $sRowCounter == 1 ){//LP0084_TS
                                echo "<tr>";//LP0084_TS
                            }//LP0084_TS
                            
                          // var_dump($stockRow);
                            echo "<TD>",$stockRow['STRC60'];//LP0068_AD
                        //    for($i=1;$i<5;$i++)//LP0068_AD
                        //SAVL60, SALC60, SOOR60, SITS60  from INP60 wh
                                echo "<TD>",$stockRow['SAVL60']*1;//LP0068_AD
                                echo "<TD>",$stockRow['SALC60']*1;//LP0068_AD
                                echo "<TD>",$stockRow['SOOR60']*1;//LP0068_AD
                                echo "<TD>",$stockRow['SITS60']*1;//LP0068_AD
                                echo "<tr>";//LP0068_AD
                        }
                        echo "</TABLE>";//LP0068_AD
                        echo "</TD>";//LP0068_AD
                        echo "<tr>";//LP0068_AD
                        echo "<TD class='bold'>Part Description</td>";//LP0068_AD
                        echo "<TD>" . trim ( $supplierRow['PDES35'] ) . "</td>";//LP0068_AD
                        echo "</tr>";//LP0068_AD
                    }
                    $supplierNumber=$supplierNumber130;//LP0068_AD
                    $supplierCostArray = get_supplier_cost ( $partNumber, $supplierNumber );//LP0068_AD
                    
                    if ($_SESSION ['authority'] != "E") {//LP0068_AD
                        echo "<tr>";//LP0068_AD
                        echo "<TD class='bold'>Supplier Cost</td>";//LP0068_AD
                        echo "<TD>" . trim ( $supplierCostArray ['price'] ) . " - " . trim ( $supplierCostArray ['currency'] ) . "</td>";//LP0068_AD
                        echo "</tr>";//LP0068_AD
                    }//LP0068_AD
                    break;//LP0068_AD
                    //******************************************** //LP0068_AD END ***********************************************************************
                default :
                    ;
                    break;
            }
        } elseif ( $class == 3 && $type != 42 ) {//LP0022 - Add Expedite classification logic
            
            
            //include '../services/logistics/distribution/classes/ShipmentFunctions.php'; //LP0006 Include shipping information
            
            $orderNum = substr ( $orderNumber, 0, strpos ( $orderNumber, " " ) );
            $desnNumber = substr ( $orderNumber, strpos ( $orderNumber, " " ) + 1 );
            
            //DI868E - Added functionality to accept orderNumbers less than 7 characters long and left pad zeros until 8 characters in length
            while ( strlen ( $orderNum ) < 7 ) {
                $orderNum = "0" . $orderNum;
            }
            
            $WHERE_CLAUSE = "";
            echo "<center>";
            echo "<table border=0 width=90%>";
            if ($orderNum) {
                
                $WHERE_CLAUSE = " AND ORDN70 = '$orderNum' AND STAT70 <> 'X' $WHERE_CLAUSE";
                $count = count_records ( DATALIB, "OEP40", " WHERE CONO40 = '$CONO' AND ORDN40 = '$orderNum' AND OSRC40 = '3'" );
            }
            
            
            //If it's a DRP issue
            if ($count > 0) {
                
                get_related_header_info_drp ( $orderNum, $desnNumber, $partNumber );
                
                if ($partNumber != "") {
                    
                    get_related_details_info_drp ( " AND ORDN55 = '" . trim ( $orderNum ) . "' AND CATN55 = '" . trim ( $partNumber ) . "'", trim ( $desnNumber ) );
                } else {
                    
                    get_related_details_info_drp ( " AND ORDN55 = '" . trim ( $orderNum ) . "'", trim ( $desnNumber ) );
                }
                
            } else {
                
                //get_related_header_info( $orderNum, $desnNumber );
                
                if ($partNumber != "") {
                    
                    $WHERE_CLAUSE = " AND CATN70 = '" . trim($partNumber) . "'";
                }
                
                $count = count_records ( DATALIB, "OEP70", " WHERE CONO70 = '$CONO' $WHERE_CLAUSE" );
                
                //if ($count == 0) {
                $WHERE_CLAUSE .= " AND ORDN70 = '$orderNum'";
                //}
                get_related_details_info ( $WHERE_CLAUSE, $desnNumber );
            }
            
            //LP0006 Start ************************************
            ?>
			<tr>
			<td colspan=2>
			
			<?php 
			     display_ticket_ship_info( $orderNum, $desnNumber, $conn );
			?>
			
			</td>
			</tr>
			<?php 
			//LP0006 End ************************************
			

		} elseif ($class == 7) {
		   if ($type != 118){                                                                                     //**LP0050                  
			if ($poNumber) {
				$receivingInfo = get_receiving_info ( $partNumber, $poNumber );
				if (trim ( $receivingInfo ['PDES'] ) == "") {
					$relatedInfo = get_po_number ( $partNumber, $poNumber );
					$receivingInfo = get_receiving_info ( $partNumber, $relatedInfo ['PO'] );
				}
			} else {
				$receivingInfo = get_receiving_info ( $partNumber, "" );
			}

			echo "<tr>";
			echo "<TD class='bold'>Description</td>";
			echo "<TD>" . trim ( $receivingInfo ['PDES'] ) . "</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<TD class='bold'>Supplier</td>";
			echo "<TD>" . trim ( $receivingInfo ['SNAM'] ) . "</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<TD class='bold'>Supplier Part#</td>";
			echo "<TD>" . trim ( $receivingInfo ['VCAT'] ) . "</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<TD class='bold'>Buyer</td>";
			echo "<TD>" . trim ( $receivingInfo ['PLAN'] ) . " - " . trim ( $receivingInfo ['PLNN'] ) . "</td>";
			echo "</tr>";
			if( $receivingInfo ['ORDP'] ){
			    $recOrdp = number_format ( trim ( $receivingInfo ['ORDP'] ), 2 );
			}else{
			    $recOrdp = 0;
			}
			echo "<TD class='bold'>Order Price</td>";
			echo "<TD>" . $recOrdp. "</td>";
			echo "</tr>";
		  }                                                                                                      //**LP0050
			
			if ($type == 118){                                                                                   //**LP0050
			    $itemInformationSql = "select PDES35, PTYP35, PCLS35, PGMJ35 ";                                  //**LP0050
			    $itemInformationSql .= " from PARTS ";                                                           //**LP0050
			    $itemInformationSql .= " where CONO35='DI' and PNUM35='" . $partNumber . "' ";                   //**LP0050
			    $itemInformationRes= odbc_prepare ($conn, $itemInformationSql);                                   //**LP0050
                odbc_execute($itemInformationRes);                                                                //**LP0050
                $itemType = "";                                                                                  //**LP0050
                $itemClass = "";                                                                                 //**LP0050
                $itemGroupMajor = "";                                                                            //**LP0050
                while ($itemInfoRow = odbc_fetch_array($itemInformationRes)){                                     //**LP0050
                    $itemType = $itemInfoRow['PTYP35'];                                                          //**LP0050
                    $itemClass = $itemInfoRow['PCLS35'];                                                         //**LP0050
                    $itemGroupMajor = $itemInfoRow['PGMJ35'];                                                    //**LP0050
                }                                                                                                //**LP0050
                echo "<tr>";                                                                                     //**LP0050
                echo "<TD class='bold'>Description</td>";                                                        //**LP0050
                echo "<TD>" . get_part_description($partNumber) . "</td>";                                       //**LP0050
                echo "</tr>";                                                                                    //**LP0050
                echo "<tr>";                                                                                     //**LP0050
                echo "<TD class='bold'>Item Type</td>";                                                          //**LP0050
                echo "<TD>" . $itemType . "</td>";                                                               //**LP0050
                echo "</tr>";                                                                                    //**LP0050
                echo "<tr>";                                                                                     //**LP0050
                echo "<TD class='bold'>Item Class</td>";                                                         //**LP0050
                echo "<TD>" . $itemClass . "</td>";                                                              //**LP0050
                echo "</tr>";                                                                                    //**LP0050
                echo "<tr>";                                                                                     //**LP0050
                echo "<TD class='bold'>Item Group Major</td>";                                                   //**LP0050
                echo "<TD>" . $itemGroupMajor . "</td>";                                                         //**LP0050
                echo "</tr>";                                                                                    //**LP0050
			}                                                                                                    //**LP0050
			
			
		}
	}

}

function display_checklist( $section, $option, $drp, $classification ){
global $row, $conn;
    /************START - ADDED BY TED D0341******************/


            if( $option != "SUG" ){
                //SQL to get ticket quesiton answers all at once to limit DB calls
                $sqlAnswers = "SELECT QID36, AID36, TEXT36, TID36 FROM CIL36 WHERE TID36 = {$row ['ID01']} ORDER BY QID36";
                $rsAnswers = odbc_prepare($conn, $sqlAnswers);
                odbc_execute($rsAnswers);
                
                $answersArray = array();
                while($rowAnswers = odbc_fetch_array($rsAnswers)){

                    if( $rowAnswers['AID36'] != 0 ){
                        $answersArray[$rowAnswers['QID36']] = $rowAnswers['AID36'];
                    }else{
                        $answersArray[$rowAnswers['QID36']]= $rowAnswers['TEXT36'];
                    }
                }
            }


            //Sql retrieves questions and suggestions
            if( $option == "SUG" ){

                $optionClause = "AND QTYP34='SUG'";
            }else{

                $optionClause = "AND QTYP34 <> 'SUG'";

            }
            $sqlPFCQuestions = "SELECT TEXT34, ID34, PRNT34, DEPN34, QTYP34, REQD34 FROM CIL34 WHERE CLAS34 = {$row ['CLAS01']} AND TYPE34= {$row ['TYPE01']} AND SECN34 = $section $optionClause order by ORDR34";

            $rsPFC = odbc_prepare($conn, $sqlPFCQuestions);
            odbc_execute($rsPFC);

            //Read through questions
            $pfcRequiredArray = "";
            $plannerRequiredArray = "";

            while($rowPFCQestions = odbc_fetch_array($rsPFC)){
                $childArray = array();
                $childTypeArray = array();
                $childArrayCount = 0;


                if( trim($rowPFCQestions['REQD34']) == "Y"){
                    $required = "<font color='red'>*</font>&nbsp;";

                    if( $section == 1 ){

                        $pfcRequiredArray .= "," . trim($rowPFCQestions['ID34']);
                        //array_push($pfcRequiredArray, trim($rowPFCQestions['ID34']));
                    }elseif ( $section == 2 ){
                        $plannerRequiredArray .= "," . trim($rowPFCQestions['ID34']);
                        //array_push($plannerRequiredArray, trim($rowPFCQestions['ID34']));
                    }
                }



                if( $rowPFCQestions['PRNT34'] == 0 ){
                    if( isset( $answersArray[ $rowPFCQestions['ID34'] ] )){
                        $parentSelectedAnswer = trim($answersArray[ $rowPFCQestions['ID34'] ]);
                    }
                }else{
                    
                    if( isset( $answersArray[ $rowPFCQestions['PRNT34'] ] )){
                        $parentSelectedAnswer = trim($answersArray[ $rowPFCQestions['PRNT34'] ]);
                    }
                }

                if( $rowPFCQestions['PRNT34'] != 0 && $rowPFCQestions['QTYP34'] == "TXT" ){

                    if( isset( $parentSelectedAnswer ) && $rowPFCQestions['DEPN34'] == $parentSelectedAnswer ){
                        ?>
                        <tr style="display: table-row" id='quest_txt_tr<?php echo trim($rowPFCQestions['ID34']);?>'>
                        <?php
                     }else{
                        ?>
                        <tr style="display: none" id='quest_txt_tr<?php echo  trim($rowPFCQestions['ID34'])?>'>
                        <?php
                     }
                     ?>
                        <input type='hidden' name='quest_txtCount_<?php echo  trim($rowPFCQestions['ID34'])?>' id='quest_txtCount_<?php echo $rowPFCQestions['ID34']?>' value='1'/>
                <?php
                }else{
                ?>
                <input type='hidden' name='quest_txtCount_<?php echo trim($rowPFCQestions['ID34'])?>' id='quest_txtCount_<?php echo $rowPFCQestions['ID34']?>' value='0'/>
                <tr>
                <?php
                }
                    if( $rowPFCQestions['QTYP34'] != "SUG" && $rowPFCQestions['PRNT34'] == 0 ){
                        ?><td class='bold'><?php echo $required . $rowPFCQestions['TEXT34'];?></td><?php
                    }elseif( $rowPFCQestions['PRNT34'] > 0 && $rowPFCQestions['QTYP34'] != "SUG" ){
                        ?><td class='bold'>&emsp;<?php echo $required . $rowPFCQestions['TEXT34'];?></td><?php
                    }else{
                        ?><td colspan='2'>&emsp;<?php echo $rowPFCQestions['TEXT34'];?></td><?php

                    }
                	//Check to see if a child question
                	if( $rowPFCQestions['PRNT34'] != 0 && $rowPFCQestions['DEPN34'] != 0 ){

						$sqlPrntValue = "SELECT AID36 FROM CIL36 WHERE QID36 = {$rowPFCQestions['ID34']} AND TID36 = " . $row['ID01'];
						$rsPrntVal = odbc_prepare($conn, $sqlPrntValue);
						odbc_execute($rsPrntVal);

						while($rowPrntVal = odbc_fetch_array($rsPrntVal)){
							$parentValue = $rowPrntVal['AID36'];
						}

                 	}else{

						//If parent
						$sqlChildren = "SELECT ID34, QTYP34 FROM CIL34 WHERE PRNT34 = {$rowPFCQestions['ID34']}";
						$rsChildren = odbc_prepare($conn, $sqlChildren);
						odbc_execute($rsChildren);

						while($rowChildren = odbc_fetch_array($rsChildren)){
                            $childArrayCount++;
							array_push($childArray, $rowChildren['ID34']);	//Add children IDs to array for later user
							array_push($childTypeArray, $rowChildren['QTYP34']);	//Add children Types to array for later user
						}
					}


					if( $rowPFCQestions['QTYP34'] != "SUG" ){
                 	?>
                 	<td width='60%'>
                 	<?php

                 	//SQL Gets Answers
                 	$sqlSelOptions = "SELECT ID35, OPTN35 FROM CIL35 WHERE QID35 = {$rowPFCQestions['ID34']} ORDER BY ORDR35";
                 	$rsSelOptions = odbc_prepare($conn, $sqlSelOptions);
                 	odbc_execute($rsSelOptions);

                 	//If is parent and there are children, this will include javascript call onChange of value to enable or disable
                 	//Associated Children
                 	if( $rowPFCQestions['PRNT34'] == 0 && $childArrayCount > 0 ){
						$chArray = implode(",", $childArray);
						$chTypeArray = implode(",", $childTypeArray);
						$parentSelectedValue = "";

						if( $rowPFCQestions['QTYP34'] == "SEL" ){
						    
						    if( !isset( $answersArray[ $rowPFCQestions['ID34'] ] ) ){
						        $answersArray[ $rowPFCQestions['ID34'] ] = 0;
						    }
						    
						?><select name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>' onChange='enableDisableChildren( "<?php echo $chArray;?>", this, "<?php echo $chTypeArray;?>", "SEL" ); checkRequired( <?php echo $section?>, <?php echo $drp?>, <?php echo $classification?> );'>
						    <option value=''>Select Option</option><?php
							while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){

								?><option value="<?php echo $rowSelOptions['ID35'];?>" <?php if(trim($rowSelOptions['ID35']) == trim($answersArray[ $rowPFCQestions['ID34'] ])) { echo " SELECTED"; }?>><?php echo trim($rowSelOptions['OPTN35']);?></option><?php
							}
						?>
						</select>
						<?php
						}elseif( $rowPFCQestions['QTYP34'] == "RAD" ){
                            while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){
                                ?>
                                <input type="radio" name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>_rad_<?php echo $radioCounter;?>' value='<?php echo $rowSelOptions['ID35'];?>' class='radio' onClick='enableDisableChildren( "<?php echo $chArray;?>", this, "<?php echo $chTypeArray;?>", "RAD" ); checkRequired( <?php echo $section?>, <?php echo $drp?>, <?php echo $classification?> );' <?php if(trim($rowSelOptions['ID35']) == trim($answersArray[ $rowPFCQestions['ID34'] ])) { echo " checked"; }?>/><?php echo $rowSelOptions['OPTN35'];?>
                                <?php
							 }
							 ?>
 							    <input type='hidden' name='quest_<?php echo $rowPFCQestions['ID34']?>_radioCounter' id='quest_<?php echo $rowPFCQestions['ID34']?>_radioCounter' value='<?php echo $radioCounter?>'/>
 							<?php
                        }
					//If Parent but no children
					}elseif( $rowPFCQestions['PRNT34'] == 0 ){
					    
                        if( $rowPFCQestions['QTYP34'] == "SEL" ){
    						?><select name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>' onChange='checkRequired( <?php echo $section?>, <?php echo $drp?>, <?php echo $classification?> );'><option value=''>Select Option</option><?php
    							while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){
    							    ?><option value="<?php echo $rowSelOptions['ID35'];?>" <?php if(isset( $rowSelOptions['ID35'] ) && isset($answersArray[ $rowPFCQestions['ID34'] ]) && trim($rowSelOptions['ID35']) == trim($answersArray[ $rowPFCQestions['ID34'] ])) { echo " SELECTED"; }?>><?php echo trim($rowSelOptions['OPTN35']);?></option><?php
    							}
    						?>
    						</select>
						<?php
						}elseif( $rowPFCQestions['QTYP34'] == "TXT" ){
    						?>
                            <input type='text' name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>' value='<?php echo trim($answersArray[ $rowPFCQestions['ID34'] ])?>' maxlength='50' class='long'/>
                            <?php
                        }elseif ( $rowPFCQestions['QTYP34'] == "RAD" ){
                            $radioCounter = 0;
                            while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){
                                $radioCounter++;
                                if( trim( $rowPFCQestions['ID34'] ) == 78 ){
                                    if( !isset($answersArray[ $rowPFCQestions['ID34'] ]) || trim($answersArray[ $rowPFCQestions['ID34'] ] ==0 ) || trim($answersArray[ $rowPFCQestions['ID34'] ]) =="" ){
                                        $answersArray[ $rowPFCQestions['ID34'] ] = 1;
                                    }
                                }
                                ?>
                                <input type="radio" name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>_rad_<?php echo $radioCounter;?>' value='<?php echo $rowSelOptions['ID35'];?>' class='radio' <?php if( isset( $rowSelOptions['ID35']) && isset( $answersArray[ $rowPFCQestions['ID34']]) && trim($rowSelOptions['ID35']) == trim($answersArray[ $rowPFCQestions['ID34'] ])) { echo " checked"; }?> onClick='checkRequired( <?php echo $section?>, <?php echo $drp?>, <?php echo $classification?> );'><?php echo $rowSelOptions['OPTN35'];?>
                                <?php
							 }

                        }
					//If child but no DEPN requirement
					}elseif( $rowPFCQestions['DEPN34'] == 0 ){
                        if( $rowPFCQestions['QTYP34'] == "SEL" ){
						?><select name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>'><option value='' onChange='checkRequired( <?php echo $section?>, <?php echo $drp?>, <?php echo $classification?> );'>Select Option</option><?php
							while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){
								?><option value="<?php echo $rowSelOptions['ID35'];?>" <?php if(trim($rowSelOptions['ID35']) == trim($answersArray[ $rowPFCQestions['ID34'] ])) { echo " SELECTED"; }?>><?php echo trim($rowSelOptions['OPTN35']);?></option><?php
							}
						?>
						</select>
						<?php
						}elseif( $rowPFCQestions['QTYP34'] == "RAD" ){

						}

					}elseif( $rowPFCQestions['DEPN34'] == $parentSelectedAnswer ){


						//Child with disable / enable build on select but parent is currently selected to DEPN
						//Includes hidden var with required DEPN value

                        ?><input type='hidden' name='depn_<?php echo $rowPFCQestions['ID34']?>' id='depn_<?php echo $rowPFCQestions['ID34']?>' value='<?php echo $rowPFCQestions['DEPN34'];?>'></input><?php

						if( $rowPFCQestions['QTYP34'] == "SEL" ){

                        ?><select name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>' onChange='checkRequired( <?php echo $section?>, <?php echo $drp?>, <?php echo $classification?> );'><option value=''>Select Option</option><?php
							while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){
								?><option value="<?php echo $rowSelOptions['ID35'];?>" <?php if(trim($rowSelOptions['ID35']) == trim($answersArray[ $rowPFCQestions['ID34'] ])) { echo " SELECTED"; }?>><?php echo trim($rowSelOptions['OPTN35']);?></option><?php
							}
						?>
						</select>

						<?php
						}elseif( $rowPFCQestions['QTYP34'] == "RAD" ){
                            $radioCounter = 0;
                            while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){

                                $radioCounter++;
                                ?>
                                <input type="radio" name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>_rad_<?php echo $radioCounter;?>' value='<?php echo $rowSelOptions['ID35'];?>' class='radio' <?php if(trim($rowSelOptions['ID35']) == trim($answersArray[ $rowPFCQestions['ID34'] ])) { echo " checked"; }?> onClick='checkRequired( <?php echo $section?>, <?php echo $drp?>, <?php echo $classification?> );'><?php echo $rowSelOptions['OPTN35'];?>
                                <?php
						    }
							?>
							<input type='hidden' name='quest_<?php echo $rowPFCQestions['ID34']?>_radioCounter' id='quest_<?php echo $rowPFCQestions['ID34']?>_radioCounter' value='<?php echo $radioCounter?>'/>
							<?php

						}else{
       				    ?>

                                <input type='text' name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>' value='<?php echo trim($answersArray[ $rowPFCQestions['ID34'] ])?>' maxlength='50' class='long' />
                                <?php
                        }

					}else{


                     ?><input type='hidden' name='depn_<?php echo $rowPFCQestions['ID34']?>' id='depn_<?php echo $rowPFCQestions['ID34']?>' value='<?php echo $rowPFCQestions['DEPN34'];?>'></input><?php
					//Child with disable / enable build on select
					//Includes hidden var with required DEPN value
                        if( $rowPFCQestions['QTYP34'] == "SEL" ){
						?><select name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>' disabled='true' onChange='checkRequired( <?php echo $section?>,<?php echo $drp?>, <?php echo $classification?> );'><option value=''>Select Option</option><?php
							while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){
								?><option value="<?php echo $rowSelOptions['ID35'];?>"><?php echo trim($rowSelOptions['OPTN35']);?></option><?php
							}
						?>
						</select>

						<?php
						}elseif( $rowPFCQestions['QTYP34'] == "RAD" ){
                            $radioCounter = 0;
                            while ( $rowSelOptions= odbc_fetch_array($rsSelOptions) ){

                            $radioCounter++;
                                ?>
                                <input type="radio" name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>_rad_<?php echo $radioCounter;?>' class='radio' onClick='checkRequired( <?php echo $section?>,<?php echo $drp?>, <?php echo $classification?> );' disabled><?php echo $rowSelOptions['OPTN35'];?>
                                <?php
							}
							?>
							<input type='hidden' name='quest_<?php echo $rowPFCQestions['ID34']?>_radioCounter' id='quest_<?php echo $rowPFCQestions['ID34']?>_radioCounter' value='<?php echo $radioCounter?>'/>
							<?php

						}else{
						    
						    if( !isset( $answersArray[ $rowPFCQestions['ID34'] ] ) ){
						        $answersArray[ $rowPFCQestions['ID34'] ] = "";
						    }
                            ?>
                            <input type='text' name='quest_<?php echo $rowPFCQestions['ID34']?>' id='quest_<?php echo $rowPFCQestions['ID34']?>' value='<?php echo trim($answersArray[ $rowPFCQestions['ID34'] ])?>' maxlength='50' class='long' />
                            <?php
                        }
					}
					?>
					</td>
					<?php
					}
					?>
                </tr>
                <?php
                
                    if( isset( $parentValue )){
                        $prevParent = $parentValue;
                    }
                }

                if( $section == 1 ){
                    $pfcRequiredArray = substr($pfcRequiredArray, 1);
                    ?><input type='hidden' name='pfcArray' id='pfcArray' value='<?php echo $pfcRequiredArray;?>'/><?php
                }elseif( $section == 2 && $option != "SUG"){
                    $plannerRequiredArray = substr($plannerRequiredArray, 1);
                    ?><input type='hidden' name='plannerArray' id='plannerArray' value='<?php echo $plannerRequiredArray;?>'/><?php
                }
                ?>



                <?php

                /************END - ADDED BY TED D0341******************/

}

function show_checklist( $section, $option ){
global $row, $conn;

    //Sql retrieves questions and suggestions
    if( $option == "SUG" ){

        $optionClause = "AND QTYP34='SUG'";
        $bold = "";
    }else{
        $bold = "bold";
        $optionClause = "AND QTYP34 <> 'SUG'";

    }
    //$sqlAnswersQuestions = "SELECT TEXT34, ID34, PRNT34, DEPN34, QTYP34 FROM CIL34 WHERE CLAS34 = {$row ['CLAS01']} AND TYPE34= {$row ['TYPE01']} AND SECN34 = $section $optionClause order by ORDR34";

    //i-2779747 - Added type to the SQL where clause
    $sqlAnswersQuestions = "SELECT * FROM CIL34 T1"
                         . " LEFT JOIN CIL36 T2"
                         . " ON T1.ID34 = T2.QID36 AND T2.TID36 = {$row ['ID01']}"
                         . " LEFT JOIN CIL35 T3"
                         . " ON T1.ID34 = T3.QID35 AND T2.AID36 = T3.ID35"
                         . " WHERE TYPE34 = {$row ['TYPE01']} AND SECN34 = $section $optionClause order by ORDR34";


    $rsShowAns = odbc_prepare($conn, $sqlAnswersQuestions);
   odbc_execute($rsShowAns);

   while( $showAnsRow = odbc_fetch_array( $rsShowAns ) ){

        if( $showAnsRow['PRNT34'] > 0 || $option == "SUG" ){
            $spaces = "&emsp;";
        }else{
            $spaces = "";
        }
        ?><tr>
                <td class='<?php echo $bold;?>'><?php echo $spaces . $showAnsRow['TEXT34']?></td>
                <?php
                if( $showAnsRow['QTYP34'] == "SEL" || $showAnsRow['QTYP34'] == "RAD" ){
                    ?><td><?php echo $showAnsRow['OPTN35']?></td><?php
                }else{
                    ?><td><?php echo $showAnsRow['TEXT36']?></td><?php
                }?>
        </tr><?php
    }

}

function listChildTicket( $parent, $userArray ){
    global $CONO, $conn;
    
    $childSql = "SELECT ID01, STAT01, OWNR01, DESC01 FROM CIL01 WHERE PRNT01 = $parent";
    
    $childRes = odbc_prepare($conn, $childSql);
    odbc_execute($childRes);
    
    $childCounter = 1;
    while( $childRow = odbc_fetch_array( $childRes ) ){
        $childCounter++;
        
        //Get Ticket History
        if ($_SESSION ['authority'] != "E"){                                                                                                                                //**LP0034
      //lp0086      $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $childRow ['ID01'], " FETCH FIRST 1 ROW ONLY" );
            $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $childRow ['ID01'], " ORDER BY ID02 DESC FETCH FIRST 1 ROW ONLY" );  //lp0086
        }else{                                                                                                                                                              //**LP0034
    //lp0086        $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $childRow ['ID01'] . " AND PRVT02 = 'N' ", " FETCH FIRST 1 ROW ONLY" );   //**LP0034
            $historyArrayValues = get_array_values ( FACSLIB, "CIL02L02", "WHERE CAID02=" . $childRow ['ID01'] . " AND PRVT02 = 'N' ", "ORDER BY ID02 DESC FETCH FIRST 1 ROW ONLY" );   //**LP0086
        } 
        
        if ($childCounter % 2 == 0) {
            ?><tr><?php 
        } else {
            ?><tr class='alternate'><?php 
        }
            ?>
            <td width='5px'><input type='checkbox' name='ticketIds[]' value='<?php echo $childRow ['ID01'];?>' class='chkBox'>
        	<td><a href="showTicketDetails.php?ID01=<?php echo $childRow ['ID01'];?>" style="font-weight: bold;letter-spacing: -0.5px;line-height: 100%;text-align: center;text-decoration: none;word-wrap: break-word !important; display:block; width:100%; height:100%"><?php echo $childRow['ID01'];?></a></td>
        	<td><?php echo get_status_name ( $childRow['STAT01'] );?></td>
        	<td><small><?php echo trim( $childRow['DESC01'] );?></small></td>
        	
        	<td><?php echo showUserFromArray ( $userArray, trim( $childRow['OWNR01'] ) );?></td>
        	<?php
        	if ( (is_array( $historyArrayValues ) || is_object( $historyArrayValues )) && !empty( $historyArrayValues )){
            	if( !isset( $historyArrayValues[0]['DATE02'] ) &&   $historyArrayValues [0] ['DATE02']  != 0 &&  $historyArrayValues [0] ['DATE02'] != "" ){
            	       ?>
            	       <td> - </td>
            	       <?php 
            	}else{
            	   ?>
            	    	<td><?php echo formatDate ( $historyArrayValues [0] ['DATE02'] ) . " " . $historyArrayValues [0] ['TIME02']?></td>
            	    <?php 
            	}
            	if( !isset( $historyArrayValues [0] ['STEP02'] ) &&   $historyArrayValues [0] ['STEP02']  != 0 &&  $historyArrayValues [0] ['STEP02'] != "" ){
            	    ?>
            	       <td> - </td>
            	       <?php 
            	}else{
            	   ?>
            	    	<td><?php echo $historyArrayValues [0] ['STEP02'];?></td>
            	    <?php 
            	}
        	}else{
        	    ?>
        	    <td> - </td>
        	    <td> - </td>
        	    <?php 
        	}
        	?>
        </tr><?php 
        
    }
    
    return $childCounter -1;
    
}

function isParent( $id ){
    global $CONO, $conn;
    
    $parentSql = "SELECT CHLF01 FROM CIL01 WHERE ID01 = $id";
    
    $parentRes = odbc_prepare($conn, $parentSql);
    odbc_execute($parentRes);
    
    while( $parentRow = odbc_fetch_array( $parentRes ) ){
        return $parentRow['CHLF01'];
    }
    
}

function verifyClosedChildren( $parentId ){
    global $CONO, $conn;
    
    $parentSql = "SELECT STAT01 FROM CIL01 WHERE PRNT01 = $parentId";
    
    $parentRes = odbc_prepare($conn, $parentSql);
    odbc_execute($parentRes);
    
    $openTickets = 0;
    while( $parentRow = odbc_fetch_array( $parentRes ) ){
        
        if( $parentRow['STAT01'] != 5 ){
            $openTickets++;
        }
        
    }
    
    return $openTickets;
    
}
// *********************************** LP0054_AD ************************************
// Function use ticketID provided as parameter to find coresponding PFC as is defined
// in maintenance module using stockroom of order (return 0 if not suitable)

function findPFC($ticket){//** LP0054_AD
    global $CONO, $conn;//** LP0054_AD
    $sql = "SELECT PFID01,STRC01,TYPE01,ATTR07,HTYP07,TEXT10 ";//** LP0054_AD
    $sql .= "FROM CIL01 ";//** LP0054_AD
    $sql .= "LEFT JOIN CIL07  ON TYPE07=TYPE01 and HTYP07='SODP' "; //** LP0054_AD
    $sql .= "LEFT JOIN CIL10 ON ATTR10=ATTR07 AND CAID10=ID01 "; //** LP0054_AD
    $sql .= "WHERE ID01=".$ticket;//** LP0054_AD
    $res = odbc_prepare($conn, $sql);//** LP0054_AD
    odbc_execute($res);//** LP0054_AD
    $row=odbc_fetch_array($res);//** LP0054_AD
    //var_dump($row);
    if($row['PFID01']>0)return $row['PFID01'];//** LP0054_AD
    $stockroom=$row['STRC01'];//** LP0054_AD
    if(trim($row['STRC01'])=="") //** LP0054_AD
        if(is_null($row['TEXT10']))return 0; //not possible to return PFC for this ticket(no order);//** LP0054_AD
        $split=explode(' ',$row['TEXT10']);//** LP0054_AD
        if(count($split)==2){  // dispach nr is provided//** LP0054_AD
            $order=$split[0];//** LP0054_AD
            $dispatch=$split[1]*1;//** LP0054_AD
            $sql2 = "SELECT CAST(LOCD57 AS VARCHAR(5) CCSID 37 ) AS LOCD57 FROM INP57 ";//** LP0054_AD
            $sql2 .="WHERE CONO57='".$CONO."' AND ORDN57='".$order."' AND DESN57=".$dispatch;//** LP0054_AD
            $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
            odbc_execute($res2);//** LP0054_AD
            if($row2=odbc_fetch_array($res2))//** LP0054_AD
                $stockroom=$row2['LOCD57'];     //** LP0054_AD
        }else{ //** LP0054_AD
            $order=$split[0];//** LP0054_AD
            $sql2 = "SELECT CAST(LOCD40 AS VARCHAR(5) CCSID 37 ) AS LOCD40 FROM OEP40 ";//** LP0054_AD
            $sql2 .="WHERE CONO40='".$CONO."' AND ORDN40='".$order."'";//** LP0054_AD
            $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
            odbc_execute($res2);//** LP0054_AD
            if($row2=odbc_fetch_array($res2))//** LP0054_AD
                $stockroom=$row2['LOCD40'];  //** LP0054_AD
        }//** LP0054_AD
        if ($stockroom=="")return 0;//** LP0054_AD
        $sql3= "SELECT PFC2X FROM CIL20xj03 ";//** LP0054_AD
        $sql3.="WHERE STRC2X='".$stockroom."' AND TYPE2X=".$row['TYPE01']; //** LP0054_AD
        $res3 = odbc_prepare($conn, $sql3);//** LP0054_AD
        odbc_execute($res3);//** LP0054_AD
        if($row3=odbc_fetch_array($res3))return $row3['PFC2X'];//** LP0054_AD
        return 0;  //** LP0054_AD
        
}//** LP0054_AD
// Function use ticketID provided as parameter to find coresponding Freight Contact as is defined
// in maintenance module using stockroom of order (return 0 if not suitable)

function findFreightContact($ticket){//** LP0054_AD
    global $CONO, $conn;//** LP0054_AD
    
    $sql = "SELECT PFID01,STRC01,TYPE01,ATTR07,HTYP07,TEXT10 ";//** LP0054_AD
    $sql .= "FROM CIL01 ";//** LP0054_AD
    $sql .= "LEFT JOIN CIL07  ON TYPE07=TYPE01 and HTYP07='SODP' "; //** LP0054_AD
    $sql .= "LEFT JOIN CIL10 ON ATTR10=ATTR07 AND CAID10=ID01 "; //** LP0054_AD
    $sql .= "WHERE ID01=".$ticket;//** LP0054_AD
    $res = odbc_prepare($conn, $sql);//** LP0054_AD
    odbc_execute($res);//** LP0054_AD
    $row=odbc_fetch_array($res);//** LP0054_AD
    //var_dump($row);
    $stockroom=$row['STRC01'];//** LP0054_AD
    if(trim($row['STRC01'])=="") //** LP0054_AD
        if(is_null($row['TEXT10']))return 0; //not possible to return FC for this ticket(no order);//** LP0054_AD
        $split=explode(' ',trim($row['TEXT10']));//** LP0054_AD
        if(count($split)==2){  // dispach nr is provided//** LP0054_AD
            $order=$split[0];//** LP0054_AD
            $dispatch=$split[1]*1;//** LP0054_AD
            $sql2 = "SELECT CAST(LOCD57 AS VARCHAR(5) CCSID 37 ) AS LOCD57 FROM INP57 ";//** LP0054_AD
            $sql2 .="WHERE CONO57='".$CONO."' AND ORDN57='".$order."' AND DESN57=".$dispatch;//** LP0054_AD
            $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
            odbc_execute($res2);//** LP0054_AD
            if($row2=odbc_fetch_array($res2))//** LP0054_AD
                $stockroom=$row2['LOCD57'];     //** LP0054_AD
        }else{ //** LP0054_AD
            $order=$split[0];//** LP0054_AD
            $sql2 = "SELECT CAST(LOCD40 AS VARCHAR(5) CCSID 37 ) AS LOCD40 FROM OEP40 ";//** LP0054_AD
            $sql2 .="WHERE CONO40='".$CONO."' AND ORDN40='".$order."'";//** LP0054_AD
            $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
            odbc_execute($res2);//** LP0054_AD
            if($row2=odbc_fetch_array($res2))//** LP0054_AD
                $stockroom=$row2['LOCD40'];  //** LP0054_AD
        }//** LP0054_AD
        if ($stockroom=="")return 0;//** LP0054_AD
        $sql3= "SELECT USER49 FROM CIL49 ";//** LP0054_AD
        $sql3.="WHERE KEY149='FRE' AND KEY249='".$stockroom."'"; //** LP0054_AD
        $res3 = odbc_prepare($conn, $sql3);//** LP0054_AD
        odbc_execute($res3);//** LP0054_AD
        while( $row3=odbc_fetch_array($res3) ){
            if( $row3['USER49'] ){
                return $row3['USER49'];//** LP0054_AD
            }
        }
        
        return 0;  //** LP0054_AD
}//** LP0054_AD
        
function findWarehouseContact($ticket){//** LP0054_AD
            global $CONO, $conn;//** LP0054_AD
            
            $sql = "SELECT PFID01,STRC01,TYPE01,ATTR07,HTYP07,TEXT10 ";//** LP0054_AD
            $sql .= "FROM CIL01 ";//** LP0054_AD
            $sql .= "LEFT JOIN CIL07  ON TYPE07=TYPE01 and HTYP07='SODP' "; //** LP0054_AD
            $sql .= "LEFT JOIN CIL10 ON ATTR10=ATTR07 AND CAID10=ID01 "; //** LP0054_AD
            $sql .= "WHERE ID01=".$ticket;//** LP0054_AD
            $res = odbc_prepare($conn, $sql);//** LP0054_AD
            odbc_execute($res);//** LP0054_AD
            $row=odbc_fetch_array($res);//** LP0054_AD
            //var_dump($row);
            $stockroom=$row['STRC01'];//** LP0054_AD
            if(trim($row['STRC01'])=="") //** LP0054_AD
                if(is_null($row['TEXT10']))return 0; //not possible to return FC for this ticket(no order);//** LP0054_AD
                $split=explode(' ',$row['TEXT10']);//** LP0054_AD
                if(count($split)==2){  // dispach nr is provided//** LP0054_AD
                    $order=$split[0];//** LP0054_AD
                    $dispatch=$split[1]*1;//** LP0054_AD
                    $sql2 = "SELECT CAST(LOCD57 AS VARCHAR(5) CCSID 37 ) AS LOCD57 FROM INP57 ";//** LP0054_AD
                    $sql2 .="WHERE CONO57='".$CONO."' AND ORDN57='".$order."' AND DESN57=".$dispatch;//** LP0054_AD
                    $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
                    odbc_execute($res2);//** LP0054_AD
                    if($row2=odbc_fetch_array($res2))//** LP0054_AD
                        $stockroom=$row2['LOCD57'];     //** LP0054_AD
                }else{ //** LP0054_AD
                    $order=$split[0];//** LP0054_AD
                    $sql2 = "SELECT CAST(LOCD40 AS VARCHAR(5) CCSID 37 ) AS LOCD40 FROM OEP40 ";//** LP0054_AD
                    $sql2 .="WHERE CONO40='".$CONO."' AND ORDN40='".$order."'";//** LP0054_AD
                    $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
                    odbc_execute($res2);//** LP0054_AD
                    if($row2=odbc_fetch_array($res2))//** LP0054_AD
                        $stockroom=$row2['LOCD40'];  //** LP0054_AD
                }//** LP0054_AD
                if ($stockroom=="")return 0;//** LP0054_AD
                $sql3= "SELECT USER49 FROM CIL49 ";//** LP0054_AD
                $sql3.="WHERE KEY149='WAR' AND KEY249='".$stockroom."'"; //** LP0054_AD
                $res3 = odbc_prepare($conn, $sql3);//** LP0054_AD
                odbc_execute($res3);//** LP0054_AD
                while( $row3=odbc_fetch_array($res3) ){
                    if( $row3['USER49'] ){
                        return $row3['USER49'];//** LP0054_AD
                    }
                }
                
                return 0;  //** LP0054_AD
                
        }//** LP0054_AD
        function findTSD($ticket){//** LP0054_AD
            global $CONO, $conn;//** LP0054_AD
            $sql = "SELECT PFID01,STRC01,TYPE01,ATTR07,HTYP07,TEXT10 ";//** LP0054_AD
            $sql .= "FROM CIL01 ";//** LP0054_AD
            $sql .= "LEFT JOIN CIL07  ON TYPE07=TYPE01 and HTYP07='PART' "; //** LP0054_AD
            $sql .= "LEFT JOIN CIL10 ON ATTR10=ATTR07 AND CAID10=ID01 "; //** LP0054_AD
            $sql .= "WHERE ID01=".$ticket;//** LP0054_AD
            $res = odbc_prepare($conn, $sql);//** LP0054_AD
            odbc_execute($res);//** LP0054_AD
            $row=odbc_fetch_array($res);//** LP0054_AD
            //var_dump($row);
            if(is_null($row['TEXT10']))return 0; //not possible to return TSD for this ticket(no part_nr);//** LP0054_AD
            //LP0084_AD     $sql2 = "SELECT USER49  FROM CIL49 JOIN  INP35 ON KEY149='TSD' AND KEY249=PCLS35 ";//** LP0054_AD
            $sql2 = "SELECT USER49,KEY349,PGMJ35  FROM CIL49 JOIN  INP35 ON KEY149='TSD' AND KEY249=PCLS35 ";//** LP0084_AD
            $sql2 .="WHERE CONO35 = '".$CONO."' AND PNUM35= '".$row['TEXT10']."'";//** LP0054_AD
            $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
            odbc_execute($res2);//** LP0054_AD
            //  echo $sql2;
            /*LP0084_AD     if($row2=odbc_fetch_array($res2))//** LP0054_AD
             return $row2['USER49'];//** LP0054_AD  *///LP0084_AD
            $defaultTsd=0;//LP0084_AD  default tsd for that class;
            while($row2=odbc_fetch_array($res2)){//LP0084_AD
                //    var_dump($row2);
                if (trim($row2['KEY349'])=="ALL PGMJ")$defaultTsd=$row2['USER49'];//LP0084_AD
                if (trim($row2['KEY349'])==$row2['PGMJ35']) return $row2['USER49'];//LP0084_AD
            }//LP0084_AD
            return $defaultTsd;//LP0084_AD
            //lp0084_ad    return 0;  //** LP0054_AD //no tsd defined for this item class
            
        }//** LP0054_AD
        function findIPContact($ticket){//** LP0087_AD
            global $CONO, $conn;//** LP0087_AD
            $sql = "SELECT TYPE01,TEXT10,ATTR10 ";//** LP0087_AD
            $sql .= "FROM CIL01 ";//** LP0087_AD
            $sql .= "LEFT JOIN CIL10 ON CAID10=ID01 "; //** LP0087_AD
            $sql .= "WHERE ID01=".$ticket." ORDER BY ATTR10 DESC";//** LP0087_AD
            //echo $sql;
            $res = odbc_prepare($conn, $sql);//** LP0087_AD
            odbc_execute($res);//** LP0087_AD
            for($i=1;$i<4;$i++) $row=odbc_fetch_array($res);//** LP0087_AD  get the 3-th attribute from end
            //var_dump($row);
            if(is_null($row))return 0;//check if exist 3th attribute from end //** LP0087_AD 
            if($row['TYPE01']!=133)return 0; //not NOT VSD ticket type;//** LP0087_AD
            if( is_numeric( $row['TEXT10'] ) ){
                if( $row['ATTR10'] != $row['TEXT10']-1 )return 0;//not NOT VSD ticket type; (answer is not yes) //** LP0087_AD
                
                $sql2 = "SELECT USER49  FROM CIL49 WHERE KEY149='INP'  ";//there is a single person nominated as inventory planner//** LP0087_AD
                $res2=odbc_prepare($conn, $sql2);//** LP0087_AD
                odbc_execute($res2);//** LP0087_AD
                //  echo $sql2;
                while($row2=odbc_fetch_array($res2)){//LP0087_AD
                       // var_dump($row2);
                     return $row2['USER49'];//LP0087_AD
                }//LP0084_AD
                return 0;//no IP defined //LP0084_AD    
            }
        }//** LP0087_AD
        function findSrcContact($ticket){//** LP0054_AD
            global $CONO, $conn;//** LP0054_AD
            $supplierNumber="";
            $sql = "SELECT PFID01,STRC01,TYPE01,ATTR07,HTYP07,TEXT10 ";//** LP0054_AD
            $sql .= "FROM CIL01 ";//** LP0054_AD
            $sql .= "LEFT JOIN CIL07  ON TYPE07=TYPE01 and HTYP07='SUPP' "; //** LP0054_AD
            $sql .= "LEFT JOIN CIL10 ON ATTR10=ATTR07 AND CAID10=ID01 "; //** LP0054_AD
            $sql .= "WHERE ID01=".$ticket;//** LP0054_AD
            $res = odbc_prepare($conn, $sql);//** LP0054_AD
            odbc_execute($res);//** LP0054_AD
            $row=odbc_fetch_array($res);//** LP0054_AD
            //var_dump($row);  
            if(is_null($row['TEXT10'])){
            $sql = "SELECT PFID01,STRC01,TYPE01,ATTR07,HTYP07,TEXT10 ";//** LP0054_AD
            $sql .= "FROM CIL01 ";//** LP0054_AD
            $sql .= "LEFT JOIN CIL07  ON TYPE07=TYPE01 and HTYP07='PART' "; //** LP0054_AD
            $sql .= "LEFT JOIN CIL10 ON ATTR10=ATTR07 AND CAID10=ID01 "; //** LP0054_AD
            $sql .= "WHERE ID01=".$ticket;//** LP0054_AD
            $res = odbc_prepare($conn, $sql);//** LP0054_AD
            odbc_execute($res);//** LP0054_AD
            $row=odbc_fetch_array($res);//** LP0054_AD
            //var_dump($row);
            if(is_null($row['TEXT10']))return 0; //not possible to return SRC for this ticket(no part_nr);//** LP0054_AD
            $sql2 = "SELECT DSSP35 FROM INP35  ";//** LP0054_AD
            $sql2 .="WHERE CONO35 = '".$CONO."' AND PNUM35= '".$row['TEXT10']."'";//** LP0054_AD
            $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
            odbc_execute($res2);//** LP0054_AD
            $supplierNumber=0;
            if($row2=odbc_fetch_array($res2))//** LP0054_AD
                $supplierNumber= trim($row2['DSSP35']);//** LP0054_AD              
            } else return 0;  //** LP0054_AD //no supplier defined for this item class
            $sql2 = "SELECT USER49 FROM CIL49 WHERE KEY149='SRC' AND KEY249='".$supplierNumber."'";//** LP0054_AD
            $res2=odbc_prepare($conn, $sql2);//** LP0054_AD
            odbc_execute($res2);//** LP0054_AD
            if($row2=odbc_fetch_array($res2))//** LP0054_AD
                return $row2['USER49'];//** LP0054_AD
                return 0;  //** LP0054_AD //no SRC defined for this supplier
            
        }//** LP0054_AD
        
// *********************************** LP0054_AD END************************************

        
        function findWarehouseContactAttrib($ticket, $type){
            global $CONO, $conn;//** LP0054_AD
            
            $prntSql = "SELECT NAME07, ATTR07 FROM CIL07 WHERE TYPE07=$type AND NAME07='Receiving Stockroom'";
            $prntRes = odbc_prepare($conn, $prntSql);
            odbc_execute($prntRes);
            
            while ( $prntRow = odbc_fetch_array ( $prntRes ) ) {
                $parentID = $prntRow['ATTR07'];
            }
            
            $dropSql = "SELECT TEXT10, SUBSTRING( NAME07, 1, 2) as NAME07 FROM CIL10 T1 "
                . "INNER JOIN CIL07 T2 "
                . "ON T1.TEXT10 = T2.ATTR07 "
                . "WHERE CAID10=$ticket AND ATTR10=$parentID";
       if( $_SESSION['userID'] == 1021 ){
           echo $dropSql . "<hr>";
       }
                $dropRes = odbc_prepare($conn, $dropSql);
                odbc_execute($dropRes);
                
                while ( $dropRow = odbc_fetch_array ( $dropRes ) ) {
                    
                    //echo $dropRow['TEXT10'];
                    //echo $dropRow['NAME07'];
                    
                    $sql3= "SELECT USER49 FROM CIL49 ";//** LP0054_AD
                    $sql3.="WHERE KEY149='WAR' AND KEY249='". $dropRow['NAME07'] ."'"; //** LP0054_AD
                    $res3 = odbc_prepare($conn, $sql3);//** LP0054_AD
                    odbc_execute($res3);//** LP0054_AD
                    if($row3=odbc_fetch_array($res3))return $row3['USER49'];//** LP0054_AD
                    
                    
                }
                
        }
        function findFreightContactAttrib($ticket, $type){
            global $CONO, $conn;//** LP0054_AD
            
            $prntSql = "SELECT NAME07, ATTR07 FROM CIL07 WHERE TYPE07=$type AND NAME07='Receiving Stockroom'";
            $prntRes = odbc_prepare($conn, $prntSql);
            odbc_execute($prntRes);
            
            while ( $prntRow = odbc_fetch_array ( $prntRes ) ) {
                $parentID = $prntRow['ATTR07'];
            }
            
            $dropSql = "SELECT TEXT10, SUBSTRING( NAME07, 1, 2) as NAME07 FROM CIL10 T1 "
                    . "INNER JOIN CIL07 T2 "
                    . "ON T1.TEXT10 = T2.ATTR07 "
                    . "WHERE CAID10=$ticket AND ATTR10=$parentID";
                    
                    $dropRes = odbc_prepare($conn, $dropSql);
                    odbc_execute($dropRes);
                    
                    while ( $dropRow = odbc_fetch_array ( $dropRes ) ) {
                        
                        //echo $dropRow['TEXT10'];
                        //echo $dropRow['NAME07'];
                        
                        $sql3= "SELECT USER49 FROM CIL49 ";//** LP0054_AD
                        $sql3.="WHERE KEY149='FRE' AND KEY249='". $dropRow['NAME07'] ."'"; //** LP0054_AD
                        
                        $res3 = odbc_prepare($conn, $sql3);//** LP0054_AD
                        odbc_execute($res3);//** LP0054_AD
                        if($row3=odbc_fetch_array($res3))return $row3['USER49'];//** LP0054_AD
                        
                        
                        
                    }
                    
        }
        //function used to find id(hlp05) of person having BUYER role for specified ticket 
        function findBuyer($ticket){//** LP0086_AD
            global $CONO, $conn;//** LP0086_AD
            $sql="SELECT BUYR01 FROM CIL01 WHERE ID01=".$ticket;//** LP0086_AD
            $res = odbc_prepare($conn, $sql);//** LP0086_AD
            odbc_execute($res);//** LP0086_AD
            while ($row= odbc_fetch_array($res)){//** LP0086_AD
                $buyer=$row['BUYR01'];//** LP0086_AD
                if($buyer==0){
                $partSql = "SELECT PLAN35, TEXT10 FROM CIL10 T1 "//** LP0086_AD
                . "INNER JOIN CIL07 T2 "//** LP0086_AD
                . "ON T1.ATTR10 = t2.ATTR07 "//** LP0086_AD
                . "INNER JOIN PARTS T3 "//** LP0086_AD
                . "ON T1.TEXT10 = T3.PNUM35 AND CONO35='DI' "//** LP0086_AD
                . "WHERE CAID10=$ticket AND HTYP07='PART' ";//** LP0086_AD
                        
                $partRes = odbc_prepare($conn, $partSql);//** LP0086_AD
                odbc_execute($partRes);//** LP0086_AD
                 //  echo $partSql;     
                while ($partRow = odbc_fetch_array($partRes)){//** LP0086_AD                           
                    $buyer = trim($partRow['PLAN35']);//** LP0086_AD
                    }//LP0086_AD   
                }//LP0086_AD
                $psql="SELECT USER25 FROM CIL25 WHERE PLAN25=".trim($buyer);//LP0086_AD
                $pres= odbc_prepare($conn, $psql);   //LP0086_AD
                odbc_execute($pres);//LP0086_AD
                while ($prow = odbc_fetch_array($pres)){ //LP0086_AD
                    $RSID01 = $prow['USER25'];}//LP0086_AD
                    //var_dump($RSID01);
                    return $RSID01;//LP0086_AD

                        
            }//LP0086_AD
            
            return 0;//LP0086_AD(there is not possible to find buyer
        }
        
        