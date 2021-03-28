<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            ticketSourcingFunctions.php<br>
 * Development Reference:   LP0029<br>
 * Description:             Functions file containing functions used sourcing tickets and sending emails to resources.
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *
 *  LP0029      TS    15/12/2017    Functions Separation, structure modification
 *  LP0033      KS    11/01/2018    Change to LPS out of office functionality
 *  p-5613373   TS    20/03/2018    Fix misc planner missing
 *  LP0045      KS    19/06/2018    LPS - Auto Assign "Cost Check" ticket to buyer
 *  LP0052      AD    09/10/2018    LPS - Auto Assign "Superssesion" ticket to buyer
 *  LP0063		AD    07/12/2018    Supersession fix for LP0052 development
 *  LP0066		AD    28/02/2019    Global Order Processing Support - Freight Quotation request
 *  LP0085     AD     08/10/2019    GLBAU-18097_4 LPS Tickets Aurora under regional order process support
 *  LP0075     TS     12/09/2019    Change for suppression fix
 *  LP0088     AD     24/10/2019    GLBAU-18442  Duplicated notifications due to bug in ticketSourcingFunction.php [p-6378198]
 *  i-6306156  TS     12/12/2019    i-6306156 Regional sourcing Fix
 *
 **/

/**
 * Function sets up notifications and notification flow for ticket actions also creates the email thread and sends to appropriate contacts
 *
 * @param integer $id
 * @param integer $classification
 * @param integer $type
 * @param array $emailArray
 * @param string $partNumber
 * @param string $orderNumber
 * @param integer $marketArea
 * @param string $shortDescription
 * @param integer $priority
 * @param string $desnNumber
 * @param string $receiveStockroom
 * @param string $returnedToStockroom
 */

//TRICKY - This has alot of functionality built in to it, before modifying ensure that the concepts are well understood
//DI868J - Added desnNumber parameter
//D0180 - Added $region parameter
function notifications($id, $classification, $type, $emailArray, $partNumber, $orderNumber, $marketArea, $shortDescription, $priority, $desnNumber, $receiveStockroom, $returnedToStockroom, $region ) {
    global $conn, $CONO, $SHOW_NOTIFICATIONS, $FROM_MAIL, $FROM_USER, $SITENAME, $mtpUrl, $MM_DEFAULT_CONTACT_NAME, $MM_DEFAULT_CONTACT, $MM_DEFAULT_CONTACT_EMAIL, $C1_DEFAULT_CONTACT, $C2_DEFAULT_CONTACT, $GPA_DEFAULT_CONTACT,$GPA_DEFAULT_CONTACT_NAME, $GPA_DEFAULT_CONTACT_EMAIL, $TEST_SITE;
    
    //DI868E - Added functionality to accept orderNumbers less than 7 characters long and left pad zeros until 8 characters in length
    while ( strlen ( $orderNumber ) < 7 ) {
        $orderNumber = "0" . $orderNumber;
    }
    if (! $emailArray) {
        $emailArray = array ();
    }
    //Email setup for Technical Support Classification Class = 6
    if ($classification == 6) {
        //Get email and address of user responsible for brand
        $brandEmail = get_brand_email ( $id );
        
        //**LP0033  if (! array_search ( $brandEmail ['email'], $emailArray )) {
        if (array_search($brandEmail, $emailArray) === false){                          //**LP0033
            array_push ( $emailArray, $brandEmail );
        }
    } elseif ($classification == 8 ) {
        
        //Determine the plannerID of the part
        $primaryBuyerSql = "SELECT PLAN35  FROM PARTS ";
        $primaryBuyerSql .= "WHERE CONO35 = '$CONO' AND PNUM35 ='" . trim ( $partNumber ) . "'";
        $primaryBuyerRes = odbc_prepare ( $conn, $primaryBuyerSql );
        odbc_execute ( $primaryBuyerRes );
        
        while ( $primaryBuyerRow = odbc_fetch_array ( $primaryBuyerRes ) ) {
            $PLAN35 = $primaryBuyerRow ['PLAN35'];
        }
        
        $whileCounter = 0;
        if( isset( $PLAN35 ) && $PLAN35 != 0 && $PLAN35 != "" ){

            //Determine correct contact for plannerID
            //D0108 - Added Fields 20 - 25
            //                   0       1        2       3        4      5       6      7       8
            $buyerSql = "SELECT EXID25, PAID25, NAMEE5, MAILE5, AVALE5, NAMEP5, MAILP5, AVALP5, BENAM5,";
            //                9       10      11      12      13       14     15      16      17     18      19       20     21      22      23      24       25
            $buyerSql .= " MAIBE5, BPNAM5, MAIBP5, BEXP25, BPRI25, PASS05, PASSE5, PASSP5, PASBE5, PASBP5, USER25, NAME05, BBUP25, NAMER5, AVAL05, EMAIL05, PASS05";
            $buyerSql .= " FROM CIL25J02 WHERE PLAN25 = $PLAN35";
            
            
            $buyerRes = odbc_prepare ( $conn, $buyerSql );
            odbc_execute ( $buyerRes );
            
            
            
            if ($PLAN35 == 0 || $PLAN35 == "") {
                
                $BUYER = 0;
            } else {
                $BUYER = $PLAN35;
            }
    
            
            while ( $buyerRow = odbc_fetch_array ( $buyerRes ) ) {
                $whileCounter ++;
                
                $updateBuyerSql = "";
                
                
                if ($type == 42) {
                    
                    //D0359 - Start - Get Backup Info ***********************************
                    $backId = trim(get_back_up_id( $buyerRow['EXID25'] ));	// Get Expedite BackupId
                    $backInfo = user_info_by_id( $backId );
                    $back['name'] = trim($backInfo['NAME05']);
                    $back['email'] = trim($backInfo['EMAIL05']);
                    $back['pass'] = trim($backInfo['PASS05']);
                    $back['availability'] = trim($backInfo['AVAL05']);
                    //D0359 - End - Get Backup Info ***********************************
                    
                    if (trim ( $buyerRow['AVALE5'] ) == "Y" || !$backId || trim($back['availability']) == "N") {
                        $expediteNotify ['name'] = trim($buyerRow['NAMEE5']);
                        $expediteNotify ['email'] = trim($buyerRow['MAILE5']);
                        $expediteNotify ['pass'] = trim($buyerRow['PASSE5']);
                        $ownerId = trim($buyerRow['EXID25']);
                        
                        //Check to see if user is already in notification list to eliminate duplicates
                        //**LP0033  if (! array_search ( $expediteNotify ['email'], $emailArray )) {
                        if (array_search($expediteNotify, $emailArray) === false){                          //**LP0033
                            array_push ( $emailArray, $expediteNotify );
                        }
                        //DI868B  - Added RESP01 to query to maintain first responsible owner
                        $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= " . $buyerRow['EXID25'] . ",RESP01= " . $buyerRow['EXID25'] . " WHERE ID01 = $id";
                    } else {
                        
                        //D0359 - Start - Change Backup Info ***********************************
                        //$expediteNotify ['name'] = $buyerRow['BENAM5'];
                        //$expediteNotify ['email'] = $buyerRow['MAIBE5'];
                        //$expediteNotify ['pass'] = $buyerRow['PASBE5'];
                        //$ownerId = $buyerRow['BEXP25'];
                        
                        //** email should be send to planner as well:                       //**LP0033
                        $expediteNotify ['name'] = trim($buyerRow['NAMEE5']);                     //**LP0033
                        $expediteNotify ['email'] = trim($buyerRow['MAILE5']);                    //**LP0033
                        $expediteNotify ['pass'] = trim($buyerRow['PASSE5']);                    //**LP0033
                        if (array_search($expediteNotify, $emailArray) === false){          //**LP0033
                            array_push($emailArray, $expediteNotify);                       //**LP0033
                        }                                                                   //**LP0033
                        
                        
                        $expediteNotify ['name'] = trim($back['name']);
                        $expediteNotify ['email'] = trim($back['email']);
                        $expediteNotify ['pass'] = trim($back['pass']);
                        $ownerId = $backId;
                        //D0359 - End - Change Backup Info ***********************************
                        
                        //Check to see if user is already in notification list to eliminate duplicates
                        //**LP0033  if (! array_search ( $expediteNotify ['email'], $emailArray )) {
                        if (array_search($expediteNotify, $emailArray) === false){                          //**LP0033
                            array_push ( $emailArray, $expediteNotify );
                        }
                        //DI868B  - Added RESP01 to query to maintain first responsible owner
                        $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= " . $ownerId . ", RESP01= " . $ownerId. " WHERE ID01 = $id";
                    }
                } elseif ($type == 43 || $type == 44 || $type == 45) {
                    
                    //D0359 - Start - Get Backup Info ***********************************
                    $backId = trim(get_back_up_id( $buyerRow['PAID25'] )); //Get PA Back-Up ID
                    $backInfo = user_info_by_id( $backId );
                    $back['name'] = trim($backInfo['NAME05']);
                    $back['email'] = trim($backInfo['EMAIL05']);
                    $back['pass'] = trim($backInfo['PASS05']);
                    $back['availability'] = trim($backInfo['AVAL05']);
                    //D0359 - End - Get Backup Info ***********************************
                    
                    
                    if ( trim($buyerRow['AVALP5']) == "Y"  || !$backId || trim($back['availability']) == "N") {
                        
                        $pA ['name'] = trim($buyerRow['NAMEP5']);
                        $pA ['email'] = trim($buyerRow['MAILP5']);
                        $pA ['pass'] = trim($buyerRow['PASSP5']);
                        $ownerId = trim($buyerRow['PAID25']);
                        
                        //Check to see if user is already in notification list to eliminate duplicates
                        
                        
                        //**LP0033  if (! array_search ( $expediteNotify ['email'], $emailArray )) {
                        if (array_search($pA, $emailArray) === false){                                   //**LP0033
                            array_push ( $emailArray, $pA );
                        }
                        
                        
                        //DI868B  - Added RESP01 to query to maintain first responsible owner
                        $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= $ownerId, RESP01=$ownerId WHERE ID01 = $id";
                    } else {
                        
                        //D0359 - Start - Change Backup Info ***********************************
                        //$pA ['name'] = $buyerRow['BPNAM5'];
                        //$pA ['email'] = $buyerRow['MAIBP5'];
                        //$pA ['pass'] = $buyerRow['PASBP5'];
                        //$ownerId = $buyerRow['BPRI25'];
                        
                        
                        //** email should be send to planner as well:                       //**LP0033
                        $pA ['name'] = trim($buyerRow['NAMEP5']);                                 //**LP0033
                        $pA ['email'] = trim($buyerRow['MAILP5']);                                //**LP0033
                        $pA ['pass'] = trim($buyerRow['PASSP5']);                                //**LP0033
                        if (array_search($pA, $emailArray) === false){                      //**LP0033
                            array_push ($emailArray, $pA);                                  //**LP0033
                        }                                                                   //**LP0033
                        
                        
                        $pA ['name'] = trim($back['name']);
                        $pA ['email'] = trim($back['email']);
                        $pA ['pass'] = trim($back['pass']);
                        $ownerId = trim($backId);
                        
                        
                        //D0359 - End - Change Backup Info ***********************************
                        
                        //Check to see if user is already in notification list to eliminate duplicates
                        //**LP0033 if (! array_search ( $expediteNotify ['email'], $emailArray )) {
                        if (array_search ($pA, $emailArray) === false){                     //**LP0033
                            array_push ( $emailArray, $pA );
                        }
                        //DI868B  - Added RESP01 to query to maintain first responsible owner
                        $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= $ownerId, RESP01= $ownerId WHERE ID01 = $id";
                        
                        
                    }
                    //D0108 - Added else for all new Material Management Classification Types to go to the coordinator
                }else{
                    
                    //D0359 - Start - Get Backup Info ***********************************
                    $backId = trim(get_back_up_id( $buyerRow['USER25'] )); //Get Coordinator Back-Up ID
                    $backInfo = user_info_by_id( $backId );
                    $back['name'] = trim($backInfo['NAME05']);
                    $back['email'] = trim($backInfo['EMAIL05']);
                    $back['pass'] = trim($backInfo['PASS05']);
                    $back['availability'] = trim($backInfo['AVAL05']);
                    //D0359 - End - Get Backup Info ***********************************
                    
                    if (trim ( $buyerRow['AVAL05'] ) == "Y" || ! trim($buyerRow['BBUP25'])) {
                        $cordEmail['name'] = trim($buyerRow['NAME05']);
                        $cordEmail ['email'] = trim($buyerRow['EMAIL05']);
                        $cordEmail ['pass'] = trim($buyerRow['PASS05']);
                        $ownerId = trim($buyerRow['USER25']);
                        
                        //Check to see if user is already in notification list to eliminate duplicates
                        //**LP0033  if (! array_search ( $cordEmail ['email'], $emailArray )) {
                        if (array_search($cordEmail, $emailArray) === false){                                   //**LP0033
                            array_push ( $emailArray, $cordEmail );
                        }
                        //DI868B  - Added RESP01 to query to maintain first responsible owner
                        $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= $ownerId,RESP01=$ownerId WHERE ID01 = $id";
                    } else {
                        
                        //D0359 - Start - Change Backup Info ***********************************
                        //$cordEmail['name'] = $buyerRow['NAMER5'];
                        //$cordEmail['email'] = $buyerRow['EMAIL05'];
                        //$cordEmail['pass'] = $buyerRow['PASS05'];
                        //$ownerId = $buyerRow['BBUP25'];
                        
                        //** email should be send to planner as well:                       //**LP0033
                        $cordEmail['name'] = trim($buyerRow['NAME05']);                          //**LP0033
                        $cordEmail ['email'] = trim($buyerRow['EMAIL05']);                        //**LP0033
                        $cordEmail ['pass'] = trim($buyerRow['PASS05']);                         //**LP0033
                        if (array_search($cordEmail, $emailArray) === false){               //**LP0033
                            array_push ( $emailArray, $cordEmail );                         //**LP0033
                        }                                                                   //**LP0033
                        
                        
                        $cordEmail ['name'] = trim($back['name']);
                        $cordEmail ['email'] = trim($back['email']);
                        $cordEmail ['pass'] = trim($back['pass']);
                        $ownerId = trim($backId);
                        //D0359 - End - Change Backup Info ***********************************
                        
                        //Check to see if user is already in notification list to eliminate duplicates
                        //**LP0033  if (! array_search ( $cordEmail ['email'], $emailArray )) {
                        if (array_search($cordEmail, $emailArray) === false){                                   //**LP0033
                            array_push ( $emailArray, $cordEmail );
                        }
                        //DI868B  - Added RESP01 to query to maintain first responsible owner
                        $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01=$ownerId, RESP01=$ownerId WHERE ID01 = $id";
                    }
                }
                
                //D0185 - ensure that the ownerId is populated, if not process the following
                if( $ownerId == "" || $ownerId == 0 ){
                    
                    $cordEmail['name'] = trim($buyerRow['NAME05']);
                    $cordEmail ['email'] = trim($buyerRow['EMAIL05']);
                    $cordEmail ['pass'] = trim($buyerRow['PASS05']);
                    $ownerId = trim($buyerRow['USER25']);
                    
                    //D0359 - Start - Get Backup Info ***********************************
                    $backId = trim(get_back_up_id( $buyerRow['USER25'] )); //Get PA Back-Up ID
                    $backInfo = user_info_by_id( $backId );
                    $back['name'] = trim($backInfo['NAME05']);
                    $back['email'] = trim($backInfo['EMAIL05']);
                    $back['pass'] = trim($backInfo['PASS05']);
                    $back['availability'] = trim($backInfo['AVAL05']);
                    
                    
                    if( $ownerId == "" || $ownerId == 0 ){
                        //$cordEmail['name'] = $buyerRow['NAMER5'];
                        //$cordEmail['email'] = $buyerRow['EMAIL05'];
                        //$cordEmail['pass'] = $buyerRow['PASS05'];
                        //$ownerId = $buyerRow['BBUP25'];
                        
                        //D0359 - End - Get Backup Info ***********************************
                        $cordEmail ['name'] = trim($back['name']);
                        $cordEmail ['email'] = trim($back['email']);
                        $cordEmail ['pass'] = trim($back['pass']);
                        $ownerId = $backId;
                        //D0359 - End - Change Backup Info ***********************************
                        
                    }
                    if( $ownerId == "" || $ownerId == 0 ){
                        //lp0088_ad         $cordEmail['name'] = $MM_DEFAULT_CONTACT_NAME;
                        $cordEmail['name'] = trim($MM_DEFAULT_CONTACT_NAME);//lp0088_ad
                        //lp0088_ad                  $cordEmail['email'] = $MM_DEFAULT_CONTACT_EMAIL;
                        $cordEmail['email'] = trim($MM_DEFAULT_CONTACT_EMAIL);//lp0088_ad
                        $ownerId = $MM_DEFAULT_CONTACT;
                    }
                    
                    //Check to see if user is already in notification list to eliminate duplicates
                    //**LP0033  if (! array_search ( $cordEmail ['email'], $emailArray )) {
                    if (array_search($cordEmail, $emailArray) === false){                        //**LP0033
                        array_push ( $emailArray, $cordEmail );
                    }
                    //DI868B  - Added RESP01 to query to maintain first responsible owner
                    $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01=$ownerId, RESP01=$ownerId, POFF01=$ownerId WHERE ID01 = $id";
                    
                }
            }
            
        }else{
            $whileCounter == 0;
        }
        
            if ($whileCounter == 0) {
                
                $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= " . $MM_DEFAULT_CONTACT . " WHERE ID01 = $id";
                
                //lp0088_ad   $notifyDefault ['name'] = $MM_DEFAULT_CONTACT_NAME;
                $notifyDefault ['name'] = trim($MM_DEFAULT_CONTACT_NAME);//lp0088_ad
                //lp0088_ad    $notifyDefault ['email'] = $MM_DEFAULT_CONTACT_EMAIL;
                $notifyDefault ['email'] = trim($MM_DEFAULT_CONTACT_EMAIL);//lp0088_ad
                
                //Need to add get password function
                //**LP0033  if (! array_search ( $notifyDefault ['email'], $emailArray )) {
                if (array_search($notifyDefault, $emailArray) === false){                                   //**LP0033
                    array_push ( $emailArray, $notifyDefault );
                }
            }
            //echo $updateBuyerSql;
            if ($updateBuyerSql != "") {
                //Execute attribute SQL
                if ($TEST_SITE != "Y") {
                    $buyerUpdateRes = odbc_prepare ( $conn, $updateBuyerSql );
                    odbc_execute ( $buyerUpdateRes );
                    
                } else {
                    echo $updateBuyerSql . "<hr>";
                }
            }
            
            if( !isset( $BUYER ) || $BUYER == "" ){
                $BUYER = 0;
            }
        //********************************************************************** **LP0052_AD START ***********************************************************
    } elseif ($classification == 17 ) {
        //LP0063_AD      /*  if($type==121){  //SUPERSSESION ticket type //LP0052_AD //Based on mail request from MaryMcGrath 8nov2018 ******************************************************************
        
        //LP0075 - Change Conditional statement to force to skip to else always
        if( $type == 121) { //Supersession fix for LP0052 development//** LP0063_AD //SUPERSSESION ticket type //LP0052_AD //Based on mail request from MaryMcGrath 8nov2018 ******************************************************************
            $selectFirstPartSql="SELECT TEXT10 FROM cil10j05 where caid10=".$id." AND PREC07=1";//Select FIRST part atrribute //LP0052_AD
            $selectFirstPartRes=odbc_prepare ( $conn, $selectFirstPartSql );//LP0052_AD
            odbc_execute($selectFirstPartRes);//LP0052_AD
            $partNumberRow=odbc_fetch_array($selectFirstPartRes);//LP0052_AD
            $partNumber=$partNumberRow['TEXT10'];  //** LP0052_AD-->
            
            //Determine the plannerID of the part  //** LP0052_AD-->
            $primaryBuyerSql = "SELECT PLAN35  FROM PARTS ";  //** LP0052_AD-->
            $primaryBuyerSql .= "WHERE CONO35 = '$CONO' AND PNUM35 ='" . trim ( $partNumber ) . "'";  //** LP0052_AD-->
            $primaryBuyerRes = odbc_prepare ( $conn, $primaryBuyerSql );  //** LP0052_AD-->
            odbc_execute ( $primaryBuyerRes );  //** LP0052_AD-->
            
            while ( $primaryBuyerRow = odbc_fetch_array ( $primaryBuyerRes ) ) {  //** LP0052_AD-->
                $PLAN35 = $primaryBuyerRow ['PLAN35'];  //** LP0052_AD-->
            }  //** LP0052_AD-->
            
            //Determine correct contact for plannerID
            //D0108 - Added Fields 20 - 25
            //                   0       1        2       3        4      5       6      7       8
            $buyerSql = "SELECT EXID25, PAID25, NAMEE5, MAILE5, AVALE5, NAMEP5, MAILP5, AVALP5, BENAM5,";  //** LP0052_AD-->
            //                9       10      11      12      13       14     15      16      17     18      19       20     21      22      23      24       25
            $buyerSql .= " MAIBE5, BPNAM5, MAIBP5, BEXP25, BPRI25, PASS05, PASSE5, PASSP5, PASBE5, PASBP5, USER25, NAME05, BBUP25, NAMER5, AVAL05, EMAIL05, PASS05";  //** LP0052_AD-->
            $buyerSql .= " FROM CIL25J02 WHERE PLAN25 = $PLAN35";  //** LP0052_AD-->
            
            
            $buyerRes = odbc_prepare ( $conn, $buyerSql );  //** LP0052_AD-->
            odbc_execute ( $buyerRes );  //** LP0052_AD-->
            
            $whileCounter = 0;  //** LP0052_AD-->
            
            if ($PLAN35 == 0 || $PLAN35 == "") {  //** LP0052_AD-->
                
                $BUYER = 0;  //** LP0052_AD-->
            } else {  //** LP0052_AD-->
                $BUYER = $PLAN35;  //** LP0052_AD-->
            }  //** LP0052_AD-->
            
            
            
            while ( $buyerRow = odbc_fetch_array ( $buyerRes ) ) {  //** LP0052_AD-->
                $whileCounter ++;  //** LP0052_AD-->
                
                $updateBuyerSql = "";  //** LP0052_AD-->
                
                {
                    
                    //D0359 - Start - Get Backup Info ***********************************  //** LP0052_AD-->
                    $backId = trim(get_back_up_id( $buyerRow['USER25'] )); //Get Coordinator Back-Up ID  //** LP0052_AD-->
                    $backInfo = user_info_by_id( $backId );  //** LP0052_AD-->
                    $back['name'] = trim($backInfo['NAME05']);  //** LP0052_AD-->
                    $back['email'] = trim($backInfo['EMAIL05']);  //** LP0052_AD-->
                    $back['pass'] = trim($backInfo['PASS05']);  //** LP0052_AD-->
                    $back['availability'] = trim($backInfo['AVAL05']);  //** LP0052_AD-->
                    //D0359 - End - Get Backup Info ***********************************  //** LP0052_AD-->
                    
                    if (trim ( $buyerRow['AVAL05'] ) == "Y" || ! trim($buyerRow['BBUP25'])) {  //** LP0052_AD-->
                        $cordEmail['name'] = trim($buyerRow['NAME05']);  //** LP0052_AD-->
                        $cordEmail ['email'] = trim($buyerRow['EMAIL05']);  //** LP0052_AD-->
                        $cordEmail ['pass'] = trim($buyerRow['PASS05']);  //** LP0052_AD-->
                        $ownerId = trim($buyerRow['USER25']);  //** LP0052_AD-->
                        
                        //Check to see if user is already in notification list to eliminate duplicates  //** LP0052_AD-->
                        //**LP0033  if (! array_search ( $cordEmail ['email'], $emailArray )) {  //** LP0052_AD-->
                        if (array_search($cordEmail, $emailArray) === false){                                   //**LP0033  //** LP0052_AD-->
                            array_push ( $emailArray, $cordEmail );  //** LP0052_AD-->
                        }
                        //DI868B  - Added RESP01 to query to maintain first responsible owner  //** LP0052_AD-->
                        $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= $ownerId,RESP01=$ownerId WHERE ID01 = $id";  //** LP0052_AD-->
                    } else {  //** LP0052_AD-->
                        
                        /* - LP0075 - Remove CC functionality
                         //** email should be send to planner as well:                        //** LP0052_AD-->
                         $cordEmail['name'] = trim($buyerRow['NAME05']);                           //** LP0052_AD-->
                         $cordEmail ['email'] = trim($buyerRow['EMAIL05']);                        //** LP0052_AD-->
                         $cordEmail ['pass'] = trim($buyerRow['PASS05']);                          //** LP0052_AD-->
                         if (array_search($cordEmail, $emailArray) === false){                //** LP0052_AD-->
                         array_push ( $emailArray, $cordEmail );                         //** LP0052_AD-->
                         }                                                                   //** LP0052_AD-->
                         
                         
                         $cordEmail ['name'] = trim($back['name']);  //** LP0052_AD-->
                         $cordEmail ['email'] = trim($back['email']);  //** LP0052_AD-->
                         $cordEmail ['pass'] = trim($back['pass']);  //** LP0052_AD-->
                         $ownerId = trim($backId);  //** LP0052_AD-->
                         if (array_search($cordEmail, $emailArray) === false){          //** LP0052_AD-->
                         array_push ( $emailArray, $cordEmail );  //** LP0052_AD-->
                         }
                         //DI868B  - Added RESP01 to query to maintain first responsible owner
                         $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01=$ownerId, RESP01=$ownerId WHERE ID01 = $id";  //** LP0052_AD-->
                         */
                    }
                }
                
                //D0185 - ensure that the ownerId is populated, if not process the following
                if( $ownerId == "" || $ownerId == 0 ){  //** LP0052_AD-->
                    
                    $cordEmail['name'] = trim($buyerRow['NAME05']);  //** LP0052_AD-->
                    $cordEmail ['email'] = trim($buyerRow['EMAIL05']);  //** LP0052_AD-->
                    $cordEmail ['pass'] = trim($buyerRow['PASS05']);  //** LP0052_AD-->
                    $ownerId = trim($buyerRow['USER25']);  //** LP0052_AD-->
                    
                    //D0359 - Start - Get Backup Info ***********************************  //** LP0052_AD-->
                    $backId = trim(get_back_up_id( $buyerRow['USER25'] )); //Get PA Back-Up ID  //** LP0052_AD-->
                    $backInfo = user_info_by_id( $backId );  //** LP0052_AD-->
                    $back['name'] = trim($backInfo['NAME05']);  //** LP0052_AD-->
                    $back['email'] = trim($backInfo['EMAIL05']);  //** LP0052_AD-->
                    $back['pass'] = trim($backInfo['PASS05']);  //** LP0052_AD-->
                    $back['availability'] = trim($backInfo['AVAL05']);  //** LP0052_AD-->
                    
                    
                    if( $ownerId == "" || $ownerId == 0 ){  //** LP0052_AD-->
                        $cordEmail ['name'] = trim($back['name']);  //** LP0052_AD-->
                        $cordEmail ['email'] = trim($back['email']);  //** LP0052_AD-->
                        $cordEmail ['pass'] = trim($back['pass']);  //** LP0052_AD-->
                        $ownerId = $backId;  //** LP0052_AD-->
                        //D0359 - End - Change Backup Info ***********************************
                        
                    }
                    if( $ownerId == "" || $ownerId == 0 ){  //** LP0052_AD-->
                        //lp0088_ad    $cordEmail['name'] = $MM_DEFAULT_CONTACT_NAME;  //** LP0052_AD-->
                        $cordEmail['name'] = trim($MM_DEFAULT_CONTACT_NAME);  //** LP0088_AD-->
                        //lp0088_ad    $cordEmail['email'] = $MM_DEFAULT_CONTACT_EMAIL;  //** LP0052_AD-->
                        $cordEmail['email'] = trim($MM_DEFAULT_CONTACT_EMAIL);  //** LP0088_AD-->
                        $ownerId = $MM_DEFAULT_CONTACT;  //** LP0052_AD-->
                    }
                    
                    //Check to see if user is already in notification list to eliminate duplicates
                    //**LP0033  if (! array_search ( $cordEmail ['email'], $emailArray )) {
                    if (array_search($cordEmail, $emailArray) === false){         //** LP0052_AD-->
                        array_push ( $emailArray, $cordEmail );  //** LP0052_AD-->
                    }
                    //DI868B  - Added RESP01 to query to maintain first responsible owner
                    $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01=$ownerId, RESP01=$ownerId, POFF01=$ownerId WHERE ID01 = $id";  //** LP0052_AD-->
                    
                }  //** LP0052_AD-->
            }  //** LP0052_AD-->
            if ($whileCounter == 0) {  //** LP0052_AD-->
                
                $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= " . $MM_DEFAULT_CONTACT . " WHERE ID01 = $id";  //** LP0052_AD-->
                
                //** LP0088_AD-->       $notifyDefault ['name'] = $MM_DEFAULT_CONTACT_NAME;  //** LP0052_AD-->
                $notifyDefault ['name'] = trim($MM_DEFAULT_CONTACT_NAME);  //** LP0088_AD-->
                //** LP0088_AD-->       $notifyDefault ['email'] = $MM_DEFAULT_CONTACT_EMAIL;  //** LP0052_AD-->
                $notifyDefault ['email'] = trim($MM_DEFAULT_CONTACT_EMAIL);  //** LP0088_AD-->
                
                //Need to add get password function
                //**LP0033  if (! array_search ( $notifyDefault ['email'], $emailArray )) {
                if (array_search($notifyDefault, $emailArray) === false){                                     //** LP0052_AD-->
                    array_push ( $emailArray, $notifyDefault );  //** LP0052_AD-->
                }
            }
            //echo $updateBuyerSql;
            if ($updateBuyerSql != "") {  //** LP0052_AD-->
                //Execute attribute SQL
                if ($TEST_SITE != "Y") {  //** LP0052_AD-->
                    $buyerUpdateRes = odbc_prepare ( $conn, $updateBuyerSql );  //** LP0052_AD-->
                    odbc_execute ( $buyerUpdateRes );  //** LP0052_AD-->
                    
                } else {  //** LP0052_AD-->
                    echo $updateBuyerSql . "<hr>";  //** LP0052_AD-->
                }  //** LP0052_AD-->
            }  //** LP0052_AD-->
            $ccownerId=$_REQUEST['assignedTo'];  //** LP0063_AD-->
            $ccbackInfo = user_info_by_id( $ccownerId );  //** LP0063_AD-->
            $back['name'] = trim($ccbackInfo['NAME05']);  //** LP0063_AD-->
            $back['email'] = trim($ccbackInfo['EMAIL05']);  //** LP0063_AD-->
            $back['pass'] = trim($ccbackInfo['PASS05']);  //** GLBAU-16624_AD-->
            
            array_push ( $emailArray, $back );  //** LP0063_AD-->
            
        }//End exception for type 122 //LP0063_AD
        else { //types 122- assignee taken from request
            //Based on mail request from MaryMcGrath ******************************************************************
            $ownerId=$_REQUEST['assignedTo'];  //** LP0052_AD-->
            
                $ownerInfo = user_info_by_id_with_backup( $ownerId );  //** LP0052_AD-->
                $owner['name'] = trim($ownerInfo['NAME']);  //** LP0052_AD-->
                $owner['email'] = trim($ownerInfo['EMAIL']);  //** LP0052_AD-->
                $owner['pass'] = trim($ownerInfo['PASS']);  //** LP0052_AD-->
                
                if( $ownerInfo['AVAL'] == "N" && isset( $ownerInfo['BACK'] ) && $ownerInfo['BACK'] <> 0 
                    &&  $ownerInfo['BACK_AVAL'] == "Y" ){
                    
                        $back['name'] = trim($ownerInfo['BACK_NAME']);  //** LP0052_AD-->
                        $back['email'] = trim($ownerInfo['BACK_MAIL']);  //** LP0052_AD-->
                        $back['pass'] = trim($ownerInfo['BACK_PASS']);  //** LP0052_AD-->
                        
                        $ownerId = $ownerInfo['BACK'];
                        
                        array_push ( $emailArray, $back );  //** LP0052_AD-->
                    

                }else{
                    
                   
                    array_push ( $emailArray, $owner );  //** LP0052_AD-->
                    
                }

            $updateOwnerSql = "UPDATE CIL01 SET  OWNR01=$ownerId, RESP01=$ownerId, POFF01=$ownerId WHERE ID01 = $id";  //** LP0052_AD-->
            $buyerOwnerRes = odbc_prepare ( $conn, $updateOwnerSql );  //** LP0052_AD-->
            odbc_execute ( $buyerOwnerRes );  //** LP0052_AD-->
        }
        
        //********************************************************************** **LP0052_AD END ***********************************************************
    } elseif ($classification == 3) {
        
        if ($type != 24) {
            //DI868J - Added $desnNumber parameter
            $emailArray = get_pfc_am_email_by_order ( $orderNumber, $partNumber, $type, $emailArray, $id, $desnNumber, $priority );     //LP0022 - Added Priority
        } else {
            
            $amArray = get_am_mail_by_market ( $marketArea, $emailArray, $id );
            
            $am ['name'] = trim($amArray ['name']);     //**LP0033
            $am ['email'] = trim($amArray ['email']);   //**LP0033
            $am ['pass'] = trim($amArray ['pass']);     //**LP0033
            
            //**LP0033  if (! array_search ( $amArray ['email'], $emailArray )) {
            if (array_search($am, $emailArray) === false){                                   //**LP0033
                $ownerId = trim($amArray ['owner']);
                array_push ( $emailArray, $am );                                             //**LP0033
                //**LP0033  $am ['name'] = trim($amArray ['name']);
                //**LP0033  $am ['email'] = trim($amArray ['email']);
                //**LP0033  $am ['pass'] = trim($amArray ['pass']);
                //**LP0033  array_push ( trim($emailArray, $am ));
                //DI868B  - Added RESP01 to query to maintain first responsible owner
                //$updateBuyerSql = "UPDATE CIL01 SET OWNR01= " . $ownerId . ", RESP01= " . $ownerId . " WHERE ID01 = $id";
                
                if( $ownerId != 0 && $ownerId != "" ){
                    //p-5613373 Fix misc planner missing, added POFF01 entry in sql
                    $updateBuyerSql = "UPDATE CIL01 SET OWNR01= $ownerId, RESP01=$ownerId, POFF01=$ownerId WHERE ID01 = $id";
                    
                    $buyerUpdateRes = odbc_prepare ( $conn, $updateBuyerSql );
                    odbc_execute ( $buyerUpdateRes );
                }
            }
        }
        
    } elseif ($classification == 7) {
        
        $receiveStockroomSql = "SELECT NAME07 FROM CIL07 WHERE ATTR07=$receiveStockroom";
        $receiveStockroomRes = odbc_prepare ( $conn, $receiveStockroomSql );
        odbc_execute ( $receiveStockroomRes );
        
        while ( $recRow = odbc_fetch_array ( $receiveStockroomRes ) ) {
            $receiveStockroomName = substr ( $recRow ['NAME07'], 0, 2 );
        }
        
        //D0185 - LPS owner assignement Issue
        $defaultResId = get_default_responsible( $classification, $receiveStockroom );
        
        
        //$backInfo = user_info_by_id( $returnsRow ['BACK05'] );
        
        //Determine the plannerID of the part
        $primaryBuyerSql = "SELECT PLAN35  FROM PARTS ";
        $primaryBuyerSql .= "WHERE CONO35 = '$CONO' AND PNUM35 ='" . trim ( $partNumber ) . "'";
        $primaryBuyerRes = odbc_prepare ( $conn, $primaryBuyerSql );
        odbc_execute ( $primaryBuyerRes );
        
        while ( $primaryBuyerRow = odbc_fetch_array ( $primaryBuyerRes ) ) {
            $PLAN35 = $primaryBuyerRow ['PLAN35'];
        }
        
        if( isset( $PLAN35 ) ){
        //Determine correct contact for plannerID
        $buyerSql = "SELECT NAME05, EMAIL05, AVAL05, NAMER5, BMAIL05, ID05, BID05, PASS05, BPASS05";
        $buyerSql .= " FROM CIL25J01 WHERE PLAN25 = $PLAN35";
        $buyerRes = odbc_prepare ( $conn, $buyerSql );
        odbc_execute ( $buyerRes );
        
       
        while ( $buyerRow = odbc_fetch_array ( $buyerRes ) ) {
            
            if ($PLAN35 == 0 || $PLAN35 == "") {
                $BUYER = $MM_DEFAULT_CONTACT;
            } else {
                $BUYER = $PLAN35;
            }
            
            //D0359 - Start - Get Backup Info ***********************************
            $backId = get_back_up_id(  $buyerRow['NAMER5'] ); //Get PA Back-Up ID
            $backInfo = user_info_by_id( $backId );
            $back['name'] = trim($backInfo['NAME05']);
            $back['email'] = trim($backInfo['EMAIL05']);
            $back['pass'] = trim($backInfo['PASS05']);
            $back['availability'] = trim($backInfo['AVAL05']);
            //D0359 - End - Get Backup Info ***********************************
            
                if (trim ( $buyerRow['AVAL05'] ) == "Y" || ! $buyerRow['BMAIL05'] || $back['availability'] == "N") {
                    
                    if( isset( $buyerRow['EXID25'] )){
                        $buyer ['name'] = trim($buyerRow['EXID25']);
                    }else{
                        $buyer ['name'] = "";
                    }
                    if( isset( $buyerRow['PAID25'] )){
                        $buyer ['email'] = trim($buyerRow['PAID25']);
                    }else{
                        $buyer ['email'] = "";
                    }
                    if( isset( $buyerRow['AVALP5'] )){
                        $buyer ['pass'] = trim($buyerRow['AVALP5']);
                    }else{
                        $buyer ['pass'] = "";
                    }
                    
                    //Check to see if user is already in notification list to eliminate duplicates
                    //**LP0033  if (! array_search ( $buyer ['email'], $emailArray )) {
                    if (array_search($buyer, $emailArray) === false){                                   //**LP0033
                        array_push ( $emailArray, $buyer );
                    }
    
                    if ($buyerRow['ID05'] == "" || $buyerRow['ID05'] == 0) {
                        //$ownerId = ${$receiveStockroomName . "_DEFAULT_CONTACT"};
                        $ownerInfo = user_info_by_id( $defaultResId );
                        
                        $buyer ['name'] = trim($ownerInfo ['NAME05']);
                        $buyer ['email'] = trim($ownerInfo ['EMAIL05']);
                        $buyer ['pass'] = trim($ownerInfo ['PASS05']);
                        $ownerId = $defaultResId;
                        
                    } else {
                        $ownerId = $buyerRow['ID05'];
                    }
                    
    
                    //echo $ownerId . "<hr>";
                    //DI868B  - Added RESP01 to query to maintain first responsible owner
                    $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= " . $ownerId . ", RESP01= " . $ownerId . ", ";
                    $updateBuyerSql .= "POFF01=" . $ownerId . " WHERE ID01 = $id";
                } else {
                    
                    //D0359 - Start - Change Backup info ***********************************
                    //$buyer ['name'] = $buyerRow['MAILE5'];
                    //$buyer ['email'] = $buyerRow['AVALE5'];
                    //$buyer ['pass'] = $buyerRow['BENAM5'];
                    
                    //** email should be send to planner as well:                       //**LP0033
                    $buyer ['name'] = trim($buyerRow['NAME05']);                              //**LP0033
                    $buyer ['email'] = trim($buyerRow['EMAIL05']);                             //**LP0033
                    $buyer ['pass'] = trim($buyerRow['PASS05']);                              //**LP0033
                    if (array_search($buyer, $emailArray ) === false) {                 //**LP0033
                        array_push ( $emailArray, $buyer );                             //**LP0033
                    }                                                                   //**LP0033
                    
                    $buyer ['name'] = trim($back['name']);
                    $buyer ['email'] = trim($back['email']);
                    $buyer ['pass'] = trim($back['pass']);
                    //D0359 - End - Change Backup info ***********************************
                    
                    //Check to see if user is already in notification list to eliminate duplicates
                    //**LP0033  if (! array_search ( $buyer ['email'], $emailArray )) {
                    if (array_search($buyer, $emailArray) === false){                                   //**LP0033
                        array_push ( $emailArray, $buyer );
                    }
                    
                    if ( !$backId ) {
                        //$ownerId = ${$receiveStockroomName . "_DEFAULT_CONTACT"};
                        $ownerInfo = user_info_by_id( $defaultResId );
                        $buyer ['name'] = trim($ownerInfo ['NAME05']);
                        $buyer ['email'] = trim($ownerInfo ['EMAIL05']);
                        $buyer ['pass'] = trim($ownerInfo ['PASS05']);
                        $ownerId = trim($defaultResId);
                        
                    } else {
                        $ownerId = $backId; 	//D0359
                    }
                    //echo $ownerId . "<hr>";
                    //DI868B  - Added RESP01 to query to maintain first responsible owner
                    
                    $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= " . $ownerId . ", RESP01= " . $ownerId . ", ";
                    //**LP0033  $updateBuyerSql .= "POFF01=" . $ownerId . " WHERE ID01 = $id";
                    $updateBuyerSql .= "POFF01=" . $buyerRow['ID05'] . " WHERE ID01 = $id";      //**LP0033
                }
            }
        }
        
        if( $BUYER == "" ){
            $BUYER = 0;
        }
        
        
        
        //D0185 - Added to check to ensure that the ownerId is populated, if not process the following
        if ($ownerId == "" || $ownerId == 0) {
            //$ownerId = ${$receiveStockroomName . "_DEFAULT_CONTACT"};
            $ownerInfo = user_info_by_id( $defaultResId );
            
            $buyer ['name'] = trim($ownerInfo ['NAME05']);
            $buyer ['email'] = trim($ownerInfo ['EMAIL05']);
            $buyer ['pass'] = trim($ownerInfo ['PASS05']);
            $ownerId = trim($defaultResId);
            
            if ($ownerId == "" || $ownerId == 0) {
                $ownerInfo = user_info_by_id( $MM_DEFAULT_CONTACT );
                $buyer ['name'] = trim($ownerInfo ['NAME05']);
                $buyer ['email'] = trim($ownerInfo ['EMAIL05']);
                $buyer ['pass'] = trim($ownerInfo ['PASS05']);
                $ownerId = $MM_DEFAULT_CONTACT;
            }
            
            //DI868B  - Added RESP01 to query to maintain first responsible owner
            $updateBuyerSql = "UPDATE CIL01 SET BUYR01 = $BUYER, OWNR01= " . $ownerId . ", RESP01= " . $ownerId . ", ";
            $updateBuyerSql .= "POFF01=$ownerId WHERE ID01 = $id";
        }
        
        
        //Check to see if user is already in notification list to eliminate duplicates
        //**LP0033  if (! array_search ( $buyer ['email'], $emailArray )) {
        if (array_search($buyer, $emailArray) === false) {               //**LP0033
            array_push ( $emailArray, $buyer );
        }
        
        
        //echo $updateBuyerSql;
        if ($updateBuyerSql != "") {
            //Execute attribute SQL
            if ($TEST_SITE != "Y") {
                
                $buyerUpdateRes = odbc_prepare ( $conn, $updateBuyerSql );
                odbc_execute ( $buyerUpdateRes );
                //echo $updateBuyerSql;
            } else {
                echo $updateBuyerSql . "<hr>";
            }
        }
        
    } elseif ($classification == 5) {
        //LP0013
        if($type == 75){
            $newAssocUser = 0;                                                                                                  //**LP0045
            //Determine the plannerID of the part
            $primaryBuyerSql = "SELECT PLAN35  FROM PARTS ";
            $primaryBuyerSql .= "WHERE CONO35 = '$CONO' AND PNUM35 ='" . trim ( $partNumber ) . "'";
            //echo $primaryBuyerSql;
            $primaryBuyerRes = odbc_prepare ( $conn, $primaryBuyerSql );
            odbc_execute ( $primaryBuyerRes );
            
            while ( $primaryBuyerRow = odbc_fetch_array ( $primaryBuyerRes ) ) {
                $PLAN35 = $primaryBuyerRow ['PLAN35'];
            }
            
            //**LP0045  $paidSql = "SELECT PAID25 FROM CIL25 WHERE PLAN25 = '".$PLAN35."'";
            $paidSql = "SELECT PAID25 FROM CIL25 WHERE PLAN25 = " . $PLAN35. " ";                                               //**LP0045
            //echo "\n". $paidSql;
            //**LP0045  $paidRes = odbc_prepare($conn,$paidSQL);
            $paidRes = odbc_prepare($conn, $paidSql);                                                                            //**LP0045
            odbc_execute($paidRes);
            while($paidRow = odbc_fetch_array($paidRes)){
                $newAssocUser = $paidRow['PAID25'];
            }
            
            if($newAssocUser != 0){                                                                                             //**LP0045
                $newAssocUserInfo = user_info_by_id($newAssocUser);                                                             //**LP0045
                $newAssocUserDetail['email'] = trim($newAssocUserInfo['EMAIL05']);                                              //**LP0045
                $newAssocUserDetail['name'] = trim($newAssocUserInfo['NAME05']);                                                //**LP0045
                $newAssocUserDetail['pass'] = trim($newAssocUserInfo['PASS05']);                                                //**LP0045
                if ($newAssocUserDetail['email'] != ""){                                                                        //**LP0045
                    $dulplicates = check_duplicate_array_vals(trim($newAssocUserDetail['email']), $emailArray, 'email' );       //**LP0045
                    if (!$dulplicates){                                                                                         //**LP0045
                        array_push($emailArray, $newAssocUserDetail);                                                           //**LP0045
                    }                                                                                                           //**LP0045
                }                                                                                                               //**LP0045
                //**LP0045
                if($newAssocUserInfo['AVAL05'] == "N"){                                                                         //**LP0045
                    $backId = trim(get_back_up_id($newAssocUser));                                                              //**LP0045
                    $backInfo = user_info_by_id($backId);                                                                       //**LP0045
                    if(($backInfo['EMAIL05'] != "") && ($backInfo['AVAL05'] == "Y")){                                           //**LP0045
                        $back['name'] = trim($backInfo['NAME05']);                                                              //**LP0045
                        $back['email'] = trim($backInfo['EMAIL05']);                                                            //**LP0045
                        $back['pass'] = trim($backInfo['PASS05']);                                                              //**LP0045
                        $dulplicates = check_duplicate_array_vals(trim($back['email']), $emailArray, 'email' );                 //**LP0045
                        if (!$dulplicates){                                                                                     //**LP0045
                            array_push($emailArray, $back);                                                                     //**LP0045
                        }                                                                                                       //**LP0045
                        $newAssocUser = $backId;                                                                                //**LP0045
                    }                                                                                                           //**LP0045
                }                                                                                                               //**LP0045
            }                                                                                                                   //**LP0045
            if($newAssocUser == 0){                                                                                             //**LP0045
                $newAssocUser = $GPA_DEFAULT_CONTACT;                                                                           //**LP0045
                $own['name'] = $GPA_DEFAULT_CONTACT_NAME;                                                                       //**LP0045
                //lp0088_ad         $own['email'] = $GPA_DEFAULT_CONTACT_EMAIL;                                                                     //**LP0045
                $own['email'] =trim( $GPA_DEFAULT_CONTACT_EMAIL);       //lp0088_ad                                                               //**LP0045
                $dulplicates = check_duplicate_array_vals(trim($own['email']), $emailArray, 'email');                           //**LP0045
                if (!$dulplicates) {                                                                                            //**LP0045
                    array_push($emailArray, $own);                                                                              //**LP0045
                }                                                                                                               //**LP0045
            }                                                                                                                   //**LP0045
            
            //Assign ticket to associated user LP0013
            $updateSql = "UPDATE CIL01 SET OWNR01='".$newAssocUser."', POFF01='".$newAssocUser."', RSID01='".$newAssocUser."' WHERE ID01='".$id. "'";
            //echo "\n". $updateSql;
            $res = odbc_prepare( $conn, $updateSql );
            odbc_execute( $res );
            
            // here to assign ticket to the user associated with PAID25
            //**LP0045  }
        }else{                                                                                                                  //**LP0045
            
            $partBrand = get_part_brand ( $partNumber );
            
            $brandSql = "SELECT NAME05, EMAIL05, PASS05, BNAM05, BMAIL05, BPASS05, AVAL05, ID05, BID05 FROM CIL16J02 WHERE BRAN16='$partBrand'";
            $brandRes = odbc_prepare ( $conn, $brandSql );
            odbc_execute ( $brandRes );
            while ( $brandRow = odbc_fetch_array ( $brandRes ) ) {
                
                //D0359 - Start - Get Backup Info ***********************************
                $backId = trim(get_back_up_id(  $brandRow ['ID05'] )); //Get PA Back-Up ID
                $backInfo = user_info_by_id( $backId );
                $back['name'] = trim($backInfo['NAME05']);
                $back['email'] = trim($backInfo['EMAIL05']);
                $back['pass'] = trim($backInfo['PASS05']);
                $back['availability'] = trim($backInfo['AVAL05']);
                //D0359 - End - Get Backup Info ***********************************
                
                if (trim ( $brandRow ['AVAL05'] ) == "Y" || !$brandRow ['BMAIL05'] || $back['availability'] == "N" ) {
                    $brand ['name'] = trim($brandRow ['NAME05']);
                    $brand ['email'] = trim($brandRow ['EMAIL05']);
                    $brand ['pass'] = trim($brandRow ['PASS05']);
                    $ownerId = trim($brandRow ['ID05']);
                } else {
                    //$brand ['name'] = $brandRow [3];
                    //$brand ['email'] = $brandRow [4];
                    //$brand ['pass'] = $brandRow [5];
                    //$ownerId = $brandRow [8];
                    
                    $brand ['name'] = trim($back['name']);
                    $brand ['email'] = trim($back['email']);
                    $brand ['pass'] = trim($back['pass']);
                    $ownerId = trim($backId);
                }
                
            }
            
            
            //D0341 - Created to ensure no duplicates.
            if( $ownerId != 0 && $ownerId != "" ){
                
                $dulplicates = check_duplicate_array_vals( trim ( $brand ['email'] ), $emailArray, 'email' );
                
                if ( !$dulplicates ) {
                    array_push ( $emailArray, $brand );
                }
            }else{
                
                $ownerId = $GPA_DEFAULT_CONTACT;
                $own ['name'] = $GPA_DEFAULT_CONTACT_NAME;
                //lp0088_ad       $own ['email'] = $GPA_DEFAULT_CONTACT_EMAIL;
                $own ['email'] = trim($GPA_DEFAULT_CONTACT_EMAIL);//lp0088_ad
                
                
                $dulplicatesOwn = check_duplicate_array_vals( trim ( $own ['email'] ), $emailArray, 'email' );
                
                if ( !$dulplicatesOwn ) {
                    array_push ( $emailArray, $own );
                }
                
            }
            
            
            //DI868B  - Added RESP01 to query to maintain first responsible owner
            $updateSql = "UPDATE CIL01 SET OWNR01= " . $ownerId . ", RESP01= " . $ownerId . ", ";
            $updateSql .= "POFF01=" . $ownerId . " WHERE ID01 = $id";
            
            if ($updateSql != "") {
                //Execute attribute SQL
                if ($TEST_SITE != "Y") {
                    $updateRes = odbc_prepare ( $conn, $updateSql );
                    odbc_execute ( $updateRes );
                    //echo $updateSql;
                } else {
                    echo $updateSql . "<hr>";
                }
            }
            
        }                                                                                                                       //**LP0045
        //DI932 - Add for Returns classification
        //D0180 - Changed function to user CIL32 info instead of CIL29
    } elseif ($classification == 9) {
        
        if ($region != "") {
            //$globalReturnsSql = "SELECT RESP29, BACK29, T2.NAME05 as NAME, T2.AVAL05 as AVAL, T2.EMAIL05 as EMAIL, T2.PASS05 as PASS,";
            //$globalReturnsSql .= " T3.NAME05 as BACK_NAME, T3.AVAL05 as BACK_AVAL, T3.EMAIL05 as BACK_EMAIL, T3.PASS05 as BACK_PASS";
            //$globalReturnsSql .= " FROM CIL29 T1";
            //$globalReturnsSql .= " LEFT JOIN HLP05 T2";
            //$globalReturnsSql .= " ON T1.RESP29 = T2.ID05";
            //$globalReturnsSql .= " LEFT JOIN HLP05 T3";
            //$globalReturnsSql .= " ON T1.BACK29 = T3.ID05";
            
            //D0481 - Added CLAS32 = 9 to query
            $globalReturnsSql = "SELECT RESP32, NAME05 as NAME, AVAL05 as AVAL, EMAIL05 as EMAIL, PASS05 as PASS, BACK05"
                . " FROM CIL32 T1"
                    . " INNER JOIN HLP05 T2"
                        . " ON T1.RESP32 = T2.ID05"
                            . " WHERE ID32 = $region AND CLAS32=9";
                            
                            
                            $res = odbc_prepare ( $conn, $globalReturnsSql );
                            odbc_execute ( $res );
                            
                            while ( $returnsRow = odbc_fetch_array ( $res ) ) {
                                if (trim ( $returnsRow ['AVAL'] ) == "Y" ||  $returnsRow ['BACK05'] == 0 ) {
                                    $return ['name'] = trim($returnsRow ['NAME']);
                                    $return ['email'] = trim($returnsRow ['EMAIL']);
                                    $return ['pass'] = trim($returnsRow ['PASS']);
                                    $ownerId = trim($returnsRow ['RESP32']);
                                    
                                } else {
                                    
                                    $backInfo = user_info_by_id( $returnsRow ['BACK05'] );
                                    $return ['name'] = trim($backInfo ['NAME05']);
                                    $return ['email'] = trim($backInfo ['EMAIL05']);
                                    $return ['pass'] = trim($returnsRow ['PASS05']);
                                    $ownerId = trim($returnsRow ['BACK05']);
                                }
                            }
                            
                            //DI868B  - Added RESP01 to query to maintain first responsible owner
                            $updateSql = "UPDATE CIL01 SET OWNR01= " . $ownerId . ", RESP01= " . $ownerId . ", ";
                            $updateSql .= "POFF01=$ownerId WHERE ID01 = $id";
                            
                            
                            
                            if ($updateSql != "") {
                                //Execute attribute SQL
                                if ($TEST_SITE != "Y") {
                                    $updateRes = odbc_prepare ( $conn, $updateSql );
                                    odbc_execute ( $updateRes );
                                    //echo $updateSql;
                                } else {
                                    echo $updateSql . "<hr>";
                                }
                            }
                            
                            //**LP0033  if (! array_search ( $return ['email'], $emailArray )) {
                            if (array_search($return, $emailArray) === false){                                   //**LP0033
                                array_push ( $emailArray, $return );
                            }
        }
        //D0481 - Add Regional Notifications and assignment
    }elseif ( $classification == 11 ){
        //var_dump($type);
        if ($type=='136' || $type=='137' || $type=='138' || $type=='139'){//LP0085_AD
            $sql="SELECT TEXT10 FROM CIL10 WHERE CAID10=".$id." ORDER BY LINE10 ";//LP0085_AD
            //echo $sql;
            $res = odbc_prepare ( $conn, $sql );//LP0085_AD
            odbc_execute ( $res );//LP0085_AD
            $row=odbc_fetch_array ( $res );//LP0085_AD
            $row=odbc_fetch_array ( $res );//LP0085_AD
            // echo $row['TEXT10'];
            $sql = "SELECT USER49 FROM CIL49 WHERE KEY149='LPC' AND KEY249='".$row['TEXT10']."'";//LP0085_AD
            $res = odbc_prepare ( $conn, $sql );//LP0085_AD
            odbc_execute ( $res );//LP0085_AD
            $row=odbc_fetch_array ( $res );//LP0085_AD
            //var_dump($row['USER49']);
            $ownerId=intval($row['USER49']);  //** LP0085_AD
            $backInfo = user_info_by_id( $ownerId );  //** LP0085_AD-->
            //var_dump($backInfo);
            $back['name'] = trim($backInfo['NAME05']);  //** LP0085_AD-->
            $back['email'] = trim($backInfo['EMAIL05']);  //** LP0085_AD-->
            $back['pass'] = trim($backInfo['PASS05']);  //** LP0085_AD-->
            if(trim($backInfo['AVAL05'])!="Y"){//** LP0085_AD-2-->
                $ownerId=$backInfo['BACK05'];//** LP0085_AD-->
                $backInfo = user_info_by_id($ownerId );  //** LP0085_AD-->
                $back['name'] = trim($backInfo['NAME05']);  //** LP0085_AD-->
                $back['email'] = trim($backInfo['EMAIL05']);  //** LP0085_AD-->
                $back['pass'] = trim($backInfo['PASS05']);  //** LP0085_AD-->
            }
            array_push ( $emailArray, $back );  //** LP0085_AD-->
            $updateOwnerSql = "UPDATE CIL01 SET  OWNR01=$ownerId, RESP01=$ownerId, POFF01=$ownerId WHERE ID01 = $id";  //** LP0085_AD-->
            $buyerOwnerRes = odbc_prepare ( $conn, $updateOwnerSql );  //** LP0085_AD-->
           
                if( odbc_execute ( $buyerOwnerRes ) ){
                    
                }else{
                    $handle = fopen("./sqlFailures/sqlFails.csv","a+");
                    fwrite($handle, "1046 - updateIssues.php," . $updateOwnerSql . "\n" );
                    fclose($handle);
                }
            
        }else{//LP0085_AD
            
            //i-6306156 - Added Active check to SQL
            $globalReturnsSql = "SELECT RESP32, NAME05 as NAME, AVAL05 as AVAL, EMAIL05 as EMAIL, PASS05 as PASS, BACK05"
                . " FROM CIL32 T1"
                    . " INNER JOIN HLP05 T2"
                        . " ON T1.RESP32 = T2.ID05"
                            . " WHERE NAME32 = '$marketArea' AND CLAS32=11 AND ACTF32 <> 'N'";
                            //echo $globalReturnsSql . "<hr>";
                            
                            $res = odbc_prepare ( $conn, $globalReturnsSql );
                            odbc_execute ( $res );
                            
                            while ( $returnsRow = odbc_fetch_array ( $res ) ) {
                                if (trim ( $returnsRow ['AVAL'] ) == "Y" ||  $returnsRow ['BACK05'] == 0 ) {
                                    $return ['name'] = trim($returnsRow ['NAME']);
                                    $return ['email'] = trim($returnsRow ['EMAIL']);
                                    $return ['pass'] = trim($returnsRow ['PASS']);
                                    $ownerId = trim($returnsRow ['RESP32']);
                                    
                                } else {
                                    
                                    $backInfo = user_info_by_id( $returnsRow ['BACK05'] );
                                    $return ['name'] = trim($backInfo ['NAME05']);
                                    $return ['email'] = trim($backInfo ['EMAIL05']);
                                    $return ['pass'] = trim($returnsRow ['PASS05']);
                                    $ownerId = trim($returnsRow ['BACK05']);
                                }
                            }
                            
                            if( $ownerId > 0 ){
                                //DI868B  - Added RESP01 to query to maintain first responsible owner
                                $updateSql = "UPDATE CIL01 SET OWNR01= " . $ownerId . ", RESP01= " . $ownerId . ", ";
                                $updateSql .= "POFF01=$ownerId WHERE ID01 = $id";
                                
                                
                                
                                if ($updateSql != "") {
                                    //Execute attribute SQL
                                    if ($TEST_SITE != "Y") {
                                        $updateRes = odbc_prepare ( $conn, $updateSql );
                                        odbc_execute ( $updateRes );
                                        //echo $updateSql;
                                    } else {
                                        echo $updateSql . "<hr>";
                                    }
                                }
                            
                            
                                //**LP0033  if (! array_search ( $return ['email'], $emailArray )) {
                                if (array_search($return, $emailArray) === false){                                   //**LP0033
                                    array_push ( $emailArray, $return );
                                }
                            }
        }//LP0085_AD
    }
    $toEmail = "";
    if ($SHOW_NOTIFICATIONS) {
        foreach ( $emailArray as $emailSend ) {
            echo $emailSend ['name'] . "<hr>";
            echo $emailSend ['email'] . "<hr>";
            echo $emailSend ['pass'] . "<hr>";
            
        }
    } else {
        foreach ( $emailArray as $emailSend ) {
            
            if( isset($emailSend ['email']) ){
                $className = get_class_name ( $classification );
                $typeName = get_type_name ( $type );
                
                $encryptedPassword = base64_encode ( $emailSend ['pass'] );
                
                $toEmail = $emailSend ['email'];
                
                //Start of Temp Change for Ceva and DHL for Server Change ******************************************
                $tmpUser = strtoupper(  $toEmail );
                if ( ( strpos( $tmpUser, 'DHL' ) !== false ) ) {
                    $tmpUrl = $mtpUrl;
                    $mtpUrl = "http://sedas5.is.sandvik.com:89/production/smc/global/lps";
                }
                //End of Temp Change for Ceva and DHL for Server Change ******************************************
                
                $message = "\n\n<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>";
                $message .= "Dear " . trim ( $emailSend ['name'] ) . ",<br><br><br>";
                $message .= "A new " . trim ( $SITENAME ) . " $className- $typeName ticket has been entered.<br><br>";
                $message .= "<b>Short Description:</b> " . $shortDescription . "<br><br><br>";
                $message .= "Please reference ticket #$id<br><br><br>";
                $message .= "To directly reference the ticket follow the link below:<br><br>";
                $message .= "<a href='$mtpUrl/showTicketDetails.php?ID01=$id&email=$toEmail&epass=$encryptedPassword'>View Ticket</a><br><br>";
                $message .= "Thank You,<br>$FROM_USER<br>";
                
                $subject = "#$id - $priority - $className - $typeName";
                
                //Sets up mail to use HTML formatting
                $strHeaders = "MIME-Version: 1.0\r\n";
                $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
                $strHeaders .= "From: " . $FROM_MAIL;
                
                if( isset($toEmail) && $toEmail != "" ){
                    if( mail( $toEmail, $subject, $message, $strHeaders ) ){
                        
                    }else{
                        $handle = fopen("./mailFailures/mailErrors.csv","a+");
                        fwrite($handle, "1146 - Update - TicketSourcing - Not Sendmail," . $toEmail . "," . $subject . "," . substr($message, 0, 100 ) . "\n" );
                        fclose($handle);
                    }
                }
                
                if( isset( $tmpUrl ) ){
                    $mtpUrl = $tmpUrl;
                }
            }
            
        }
    }
}

/**
 * Function retrieves and returns the correct brand contact dependant on the $id
 *
 * @param integer $id
 * @return string $brand_email
 */
function get_brand_email($id) {
    global $conn;
    
    $sql = "SELECT  EMAIL05, BMAIL05, NAME05, BNAM05, AVAL05, PASS05, BPASS05 FROM CIL10J04 WHERE CAID10=$id AND HTYP07='BRAN'";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    //echo $sql;
    
    
    while ( $row = odbc_fetch_array ( $res ) ) {
        //Check to see if responsible user is available or out of office
        if ($row ['AVAL05'] == "Y") {
            $brand_email ['name'] = $row ['NAME05'];
            $brand_email ['email'] = $row ['EMAIL05'];
            $brand_email ['pass'] = $row ['PASS05'];
        } else {
            $brand_email ['name'] = $row ['BNAM05'];
            $brand_email ['email'] = $row ['BMAIL05'];
            $brand_email ['pass'] = $row ['BPASS05'];
        }
    }
    
    return $brand_email;
}

/**
 * Function retunrs array of Point of First Contact email information based on the $orderNumber, $partNumber and Ticket $type
 *
 * @param string $orderNumber
 * @param string $partNumber
 * @param integer $type
 * @param array $emailArray
 * @param integer $ticketId
 * @param string $desnNumber
 * @return array of PFC information
 */
//DI868J - Added $desnNumber parameter
function get_pfc_am_email_by_order($orderNumber, $partNumber, $type, $emailArray, $ticketId, $desnNumber, $priority) { //LP0022 - Added Priority
    global $conn, $CONO, $GOP_DEFAULT_CONTACT, $GOP_DEFAULT_CONTACT_NAME, $GOP_DEFAULT_CONTACT_EMAIL;
    
    
    $customerInfo = get_order_customer_site_number ( trim ( $orderNumber ), trim ( $desnNumber ) );
    //DI868J - Added $desnNumber parameter
    
    $count = count_records ( DATALIB, "OEP40", " WHERE CONO40 ='$CONO' AND ORDN40 = '$orderNumber' AND OSRC40 = '3'");
   
    If( $count > 0 ){
        $drpOrder = true;
    }
    
    
    
            //LP0016 ************Start - Added functionality to stored SLMN as the POFF01 (Purchase Officer )
            $slmSql = "select SLMN41, ID38, PLAN38 from OEP41 T1 Inner join CIL38 T2 on T1.SLMN41 = T2.SLMN38";
            $slmSql .= " where CONO41='DI' and DCRE41 = '$orderNumber'";
            $slmRes = odbc_prepare($conn, $slmSql);
            odbc_execute ( $slmRes );

            
            while ( $slmnRow = odbc_fetch_array( $slmRes ) ) {
                
                $slmnInfo = user_info_by_id($slmnRow ['PLAN38'] );
                
                $backId = trim(get_back_up_id( $slmnRow ['PLAN38'] ));
                
                $backInfo = user_info_by_id( $backId );
                
                $back['name'] = trim($backInfo['NAME05']);
                $back['email'] = trim($backInfo['EMAIL05']);
                $back['pass'] = trim($backInfo['PASS05']);
                $back['availability'] = trim($backInfo['AVAL05']);
                
                if (trim($slmnInfo['AVAL05']) == "N"){                                  //**LP0033
        
                    $outboundPlanner['name']  = trim($slmnInfo['NAME05']);                    //**LP0088
                    $outboundPlanner['email'] = trim($slmnInfo['EMAIL05']);                   //**LP0088
                    $outboundPlanner['pass']  = trim($slmnInfo['PASS05']);                    //**LP0088
                    if (array_search($outboundPlanner, $emailArray) === false){         //**LP0033
                        array_push ( $emailArray, $outboundPlanner );                   //**LP0033
                    }                                                                   //**LP0033
                    //**LP0033
                    //Supervisor should receive an email:                               //**LP0033
                    $supervisorSQL = "select SUPR31 ";                                  //**LP0033
                    $supervisorSQL .= " from CIL31 ";                                   //**LP0033
                    $supervisorSQL .= " where EMPL31 = " . $slmnRow ['PLAN38']. " ";    //**LP0033
                    $supervisorRes = odbc_prepare($conn, $supervisorSQL);                //**LP0033
                    odbc_execute($supervisorRes);                                        //**LP0033
                    while ($supervisorRow = odbc_fetch_array($supervisorRes)){           //**LP0033
                        $supervisorInfo = user_info_by_id($supervisorRow['SUPR31']);    //**LP0033
                        $outboundPlanner['name']  = trim($supervisorInfo['NAME05']);    //**LP0033
                        $outboundPlanner['email'] = trim($supervisorInfo['EMAIL05']);   //**LP0033
                        $outboundPlanner['pass']  = trim($supervisorInfo['PASS05']);    //**LP0033
                        //**LP0033
                        if (array_search($outboundPlanner, $emailArray) === false){     //**LP0033
                            array_push ( $emailArray, $outboundPlanner );               //**LP0033
                        }                                                               //**LP0033
                    }                                                                   //**LP0033
                }                                                                       //**LP0033
                
                if (trim($slmnInfo ['AVAL05']) == "Y" || $back['email'] == ""  || $back['availability'] == "N" ) {
                    
                  
                    $outboundPlanner['name']  = trim($slmnInfo ['NAME05']);//**LP0088
                    $outboundPlanner['email'] = trim($slmnInfo ['EMAIL05']);//**LP0088
                    $outboundPlanner['pass']  = trim($slmnInfo ['PASS05']);//**LP0088
                    
                    $ownerId = $slmnRow ['PLAN38'];                                     //**LP0033
                    $poff01 = $slmnRow ['PLAN38'];
                    
                    
                } else {
                    
                    $outboundPlanner['name'] = trim($back['name']);
                    $outboundPlanner['email'] = trim($back['email']);
                    $outboundPlanner ['pass'] = trim($back['pass']);
                    //**LP0033  $poff01 = trim($backId);
                    $ownerId = trim($backId);                                           //**LP0033
                    $poff01 = $slmnRow['PLAN38'];                                       //**LP0033
                }
                
                if (array_search($outboundPlanner, $emailArray) === false){                          
                    array_push ( $emailArray, $outboundPlanner );
                }
                
                
            }
        if( $type <> 102 ){
            //Sql to get correct stockroom order sent from
            $sql = "SELECT LOCD70 FROM OEP70LU3 WHERE CONO70 = '$CONO'";
            $sql .= " AND ORDN70='$orderNumber' AND CATN70='$partNumber' fetch first row only";
            $res = odbc_prepare ( $conn, $sql );
            odbc_execute ( $res );
            
            while ( $row = odbc_fetch_array ( $res ) ) {
                
                
                $pfcSql = "SELECT NAME05, EMAIL05, AVAL05, BNAM05, BMAIL5, ID05, BID05, PASS05, BPAS05 FROM CIL20XJ01 WHERE STRC2X='" . $row ['LOCD70'] . "'";
                $pfcSql .= " AND TYPE2X=$type fetch first row only";
                
                $pfcRes = odbc_prepare ( $conn, $pfcSql );
                odbc_execute ( $pfcRes );
                while ( $pfcRow = odbc_fetch_array ( $pfcRes ) ) {
                    
                    //D0359 - Start - Get Backup Info ***********************************
                    $backId = get_back_up_id( $pfcRow ['ID05'] ); //Get AM back-up by market
                    $backInfo = user_info_by_id( $backId );
                    $back['name'] = trim($backInfo['NAME05']);
                    $back['email'] = trim($backInfo['EMAIL05']);
                    $back['pass'] = trim($backInfo['PASS05']);
                    $back['availability'] = trim($backInfo['AVAL05']);
                    //D0359 - End - Get Backup Info ***********************************
                    
                    if (trim($pfcRow ['AVAL05']) == "Y" || trim($pfcRow ['BMAIL5']) == "" || trim($back['availability']) == "N") {
                        $pfc ['name'] = trim($pfcRow ['NAME05']);
                        $pfc ['email'] = trim($pfcRow ['EMAIL05']);
                        $pfc ['pass'] = trim($pfcRow ['PASS05']);
                        $pfcId = $pfcRow ['ID05'];
                    } else {
                        
                        $pfc ['name'] = trim($back['name']);
                        $pfc ['email'] = trim($back['email']);
                        $pfc ['pass'] = trim($back['pass']);
                        $pfcId = $backId;
                        //D0359 - End - Back-up Change***********************************
                        
                    }
                    
                    //**LP0033  if (! array_search ( trim ( $pfc ['email'] ), $emailArray )) {
                    if (array_search($pfc, $emailArray) === false){                             //**LP0033
                        array_push ( $emailArray, $pfc );
                    }
                }
            }
        
            if (!isset($am ['email'])) {
                $amSql = "SELECT NAME05, EMAIL05, AVAL05, NAMBA5, MAIBA5, ID05, IDBA05, PASS05, PASBA5 FROM CIL13J01 WHERE ";
                $amSql .= "CUSN13='" . trim ( $customerInfo [0] ) . "' FETCH FIRST ROW ONLY";
                $amRes = odbc_prepare ( $conn, $amSql );
                odbc_execute ( $amRes );
                
                while ( $amRow = odbc_fetch_array ( $amRes ) ) {
                    
                    //D0359 - Start - Get Backup Info ***********************************
                    $backId = trim(get_back_up_id( $amRow ['ID05'] )); //Get AM back-up by market
                    $backInfo = user_info_by_id( $backId );
                    $back['name'] = trim($backInfo['NAME05']);
                    $back['email'] = trim($backInfo['EMAIL05']);
                    $back['pass'] = trim($backInfo['PASS05']);
                    $back['availability'] = trim($backInfo['AVAL05']);
                    //D0359 - End - Get Backup Info ***********************************
                    
                    if (trim($amRow ['AVAL05']) == "Y" || trim($amRow ['MAIBA5']) == "" || trim($back['availability']) == "N" ) {
                        $am ['name'] = trim($amRow ['NAME05']);
                        $am ['email'] = trim($amRow ['EMAIL05']);
                        $am ['pass'] = trim($amRow ['PASS05']);
                        //$ownerId = trim($amRow [5]);      //LP0024 - Removed Owner assignment
                        //$poff01 = trim($amSiteRow [5]);
                    } else {
                        
                        //D0359 - Start - Back-up change***********************************
                        //$am ['name'] = $amRow [3];
                        //$am ['email'] = $amRow [4];
                        //$am ['pass'] = $amRow [8];
                        
                        $am ['name'] = trim($back['name']);
                        $am ['email'] = trim($back['email']);
                        $am ['pass'] = trim($back['pass']);
                        //$backUpOwnerInfo = user_cookie_info($amRow [6]);
                        //$ownerId = $backUpOwnerInfo['ID05'];
                        //$ownerId = trim($backId);                           //LP0024 - Removed Owner assignment
                        //$poff01 = trim($backId);
                        //D0359 - End - Back-up change ***********************************
                        
                    }
                }
                
            }
        
            if ( isset($drpOrder) ) {//This means it is a DRP order and we need to get the DPR Manager
                
                //D0341 - Fix bug change to array element 2 $customerInfo [2] from 0 $customerInfo [0]
                $drpSql = "SELECT NAME05, EMAIL05, AVAL05, NAMAM5, BAMAI05, ID05, BAID05, PASS05, BAPAS05 FROM CIL20J01 WHERE STRC20='" . $customerInfo [0] . "'";
          
                
                $drpRes = odbc_prepare ( $conn, $drpSql );
                odbc_execute ( $drpRes );
                while ( $drpRow = odbc_fetch_array ( $drpRes ) ) {
                    
                    //D0359 - Start - Get Backup Info ***********************************
                    $backId = trim(get_back_up_id( $drpRow ['ID05'] )); //Get AM back-up by market
                    $backInfo = user_info_by_id( $backId );
                    $back['name'] = trim($backInfo['NAME05']);
                    $back['email'] = trim($backInfo['EMAIL05']);
                    $back['pass'] = trim($backInfo['PASS05']);
                    $back['availability'] = trim($backInfo['AVAL05']);
                    //D0359 - End - Get Backup Info ***********************************
                    
                    //D0341 - fix bracket issue, from trim($drpRow [2] == "Y" ) to trim($drpRow [2] ) == "Y"
                    if (trim($drpRow ['AVAL05'] ) == "Y" || trim($drpRow ['BAMAI05']) == "" || trim($back['availability']) == "N" ) {
                        $drp ['name'] = trim($drpRow ['NAME05']);
                        $drp ['email'] = trim($drpRow ['EMAIL05']);
                        $drp ['pass'] = trim($drpRow ['PASS05']);
                        // $ownerId = trim($drpRow [5]);                     //LP0024 - Removed Owner assignment
                        //$poff01 = trim($drpRow [5]);
                    } else {
                        $drp ['name'] = trim($back['name']);
                        $drp ['email'] = trim($back['email']);
                        $drp ['pass'] = trim($back['pass']);
        
                    }
                    
                    $pfcSql = "SELECT NAME05, EMAIL05, AVAL05, BNAM05, BMAIL5, ID05, BID05, PASS05, BPAS05 FROM CIL20XJ01 WHERE STRC2X='" . $customerInfo [2] . "'";
                    $pfcSql .= " AND TYPE2X=$type fetch first row only";
                    $pfcRes = odbc_prepare ( $conn, $pfcSql );
                    odbc_execute ( $pfcRes );
                    
                    while ( $pfcRow = odbc_fetch_array ( $pfcRes ) ) {
                        
                        //D0359 - Start - Get Backup Info ***********************************
                        $backId = trim(get_back_up_id( $drpRow ['ID05'] )); //Get AM back-up by market
                        $backInfo = user_info_by_id( $backId );
                        $back['name'] = trim($backInfo['NAME05']);
                        $back['email'] = trim($backInfo['EMAIL05']);
                        $back['pass'] = trim($backInfo['PASS05']);
                        $back['availability'] = trim($backInfo['AVAL05']);
                        //D0359 - End - Get Backup Info ***********************************
                        
                        if ( trim($pfcRow ['AVAL05']) == "Y" || trim($pfcRow ['BMAIL5']) == "" || trim($back['availability']) == "N" ) {
                            $pfc ['name'] = trim($pfcRow ['NAME05']);
                            $pfc ['email'] = trim($pfcRow ['EMAIL05']);
                            $pfc ['pass'] = trim($pfcRow ['PASS05']);
                            $pfcId = trim($pfcRow ['ID05']);
                        } else {
                            
                            //D0359 - Start - Back-up change***********************************
                            //$pfc ['name'] = $pfcRow [3];
                            //$pfc ['email'] = $pfcRow [4];
                            //$pfc ['pass'] = $pfcRow [8];
                            //$pfcId = $pfcRow [6];
                            
                            $pfc ['name'] = trim($back['name']);
                            $pfc ['email'] = trim($back['email']);
                            $pfc ['pass'] = trim($back['pass']);
                            $pfcId = trim($backId);
                            //D0359 - End - Back-up change***********************************
                            
                        }
                        
                        //D0341 - Created to ensure no duplicates.
                        $dulplicates = check_duplicate_array_vals( trim ( $pfc ['email'] ), $emailArray, 'email' );
                        
                        if ( !$dulplicates ) {
                            array_push ( $emailArray, $pfc );
                        }
                    }
                    
                }
                //**LP0033  if (! array_search ( trim ( $drp ['email'] ), $emailArray )) {
                if (array_search($drp, $emailArray) === false){                             //**LP0033
                    
                    array_push ( $emailArray, $drp );
                }
                
        
            }
    }else{
        
        $pfcSql = "SELECT NAME05, EMAIL05, AVAL05, BNAM05, BMAIL5, ID05, BID05, PASS05, BPAS05 FROM CIL20XJ01 WHERE STRC2X='" . $customerInfo [2] . "'";
        $pfcSql .= " AND TYPE2X=$type fetch first row only";
        $pfcRes = odbc_prepare ( $conn, $pfcSql );
        odbc_execute ( $pfcRes );
        
        while ( $pfcRow = odbc_fetch_array ( $pfcRes ) ) {
            
            //D0359 - Start - Get Backup Info ***********************************
            $backId = trim(get_back_up_id( $pfcRow ['ID05'] )); //Get AM back-up by market
            $backInfo = user_info_by_id( $backId );
            $back['name'] = trim($backInfo['NAME05']);
            $back['email'] = trim($backInfo['EMAIL05']);
            $back['pass'] = trim($backInfo['PASS05']);
            $back['availability'] = trim($backInfo['AVAL05']);
            //D0359 - End - Get Backup Info ***********************************
            
            if ( trim($pfcRow ['AVAL05']) == "Y" || trim($pfcRow ['BMAIL5']) == "" || trim($back['availability']) == "N" ) {
                $pfc ['name'] = trim($pfcRow ['NAME05']);
                $pfc ['email'] = trim($pfcRow ['EMAIL05']);
                $pfc ['pass'] = trim($pfcRow ['PASS05']);
                $pfcId = trim($pfcRow ['ID05']);
                
                
            } else {
   
                $pfc ['name'] = trim($back['name']);
                $pfc ['email'] = trim($back['email']);
                $pfc ['pass'] = trim($back['pass']);
                $pfcId = trim($backId);
                
                //D0359 - End - Back-up change***********************************
                
            }
            
            $ownerId = $pfcId;
            
            
            
            //D0341 - Created to ensure no duplicates.
            $dulplicates = check_duplicate_array_vals( trim ( $pfc ['email'] ), $emailArray, 'email' );
            
            if ( !$dulplicates ) {
                array_push ( $emailArray, $pfc );
            }
        }
        
        
        
    }
    if (!isset($ownerId) || $ownerId == 0) {
        $ownerId = $GOP_DEFAULT_CONTACT;
        $own ['name'] = $GOP_DEFAULT_CONTACT_NAME;
        //lp0088   $own ['email'] = $GOP_DEFAULT_CONTACT_EMAIL;
        $own ['email'] = trim($GOP_DEFAULT_CONTACT_EMAIL);   //lp0088
        //lp0088 $own ['pass'] = 'dftContact';                                                //**LP0033
        $gopinfo=user_info_by_id($GOP_DEFAULT_CONTACT);   //lp0088
        $own ['pass'] =trim($gopinfo['PASS05']);   //lp0088
        //Need to add get password function
        
        
        //**LP0033  if (! array_search ( trim ( $own ['email'] ), $emailArray )) {
        if (array_search($own, $emailArray) === false){                             //**LP0033
            array_push ( $emailArray, $own );
        }
    }
    
    if( trim($customerInfo [2]) != "" ){
        
      
        //DI868B  - Added RESP01 to query to maintain first responsible owner
        //D0341 - Removed , OWNR01=$pfcId, RESP01=$pfcId";
        $updateOwnerSql = "UPDATE CIL01 SET STRC01='" . $customerInfo [2] . "'";
        
        
        //D0341 - Start Add to get POFF*************************************************
        //$poffSql = "SELECT ACTM13 FROM CIL13 WHERE ";
        //$poffSql .= "CUSN13='" . trim ( $customerInfo [0] ) . "'";
        //$poffRes = odbc_prepare ( $conn, $poffSql );
        //odbc_execute ( $poffRes );
        
        //while ( $poffRow = odbc_fetch_array ( $poffRes ) ) {
        if( !isset( $poff01 ) ){
            $poff01 = 0;
        }else{
            $updateOwnerSql .= ", POFF01=$poff01";
        }
        
        //}
        
        
        //D0341 - End Add to get POFF*************************************************

        
        if ($customerInfo [0]) {
            $updateOwnerSql .= ", CUSN01='" . $customerInfo [0] . "'";
        }
        if ($customerInfo [1]) {
            $updateOwnerSql .= ", DSEQ01='" . $customerInfo [1] . "'";
        }
        if ( isset( $pfcId ) && ( ( $type == 42 && $priority != "A (Unit Down)" ) || ( $type != 42 && isset( $pfcId )) ) ) {  //LP0022 - Expidite Logic
            
            //DO341 - Changed added , OWNR01=$pfcId, RESP01=$pfcId";
            $updateOwnerSql .= ", PFID01=$pfcId, OWNR01=$pfcId, RESP01=$pfcId";
            
        }elseif ( isset($pfcId) && $type == 42 && $priority == "A (Unit Down)" ) { //LP0022 - Expidite Pritority 1 Logic

            $updateOwnerSql .= ", PFID01=$pfcId, OWNR01=$poff01, RESP01=$pfcId";
            
        }else{ //DO341 - Added if statement in case PFC does not exist

            $updateOwnerSql .= ", OWNR01=$ownerId, RESP01=$ownerId";
        }
        
        
        
        $updateOwnerSql .= " WHERE ID01=$ticketId";
        
        
        //echo $updateOwnerSql . "<hr>";
        $ownerUpdateRes = odbc_prepare ( $conn, $updateOwnerSql );
        odbc_execute ( $ownerUpdateRes );
        //echo $updateOwnerSql;
    }
    
    
    //**LP0033  if (! array_search ( trim ( $am ['email'] ), $emailArray )) {
    if( isset( $am ) ){
        if (array_search($am, $emailArray) === false){                             //**LP0033
            array_push ( $emailArray, $am );
        }
    }
    
    
    return $emailArray;
}

/**
 * Function returns an array of Purchase office contact information
 *
 * @return array of Purchase office contact information
 */
function get_purchase_officer_contact_array() {
    global $conn, $CONO;
    
    $sql = "select PLAN25, SUPR25, BSUP25, MANG25, BMAN25, DIRC25, BDIR25 FROM CIL25L02 ORDER BY PLAN25";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    //echo $sql;
    
    
    $poContactsArray = array ();
    while ( $row = odbc_fetch_array ( $res ) ) {
        $poContactsArray [trim ( $row ['PLAN25'] )] ['planner'] = $row ['PLAN25'];
        $poContactsArray [trim ( $row ['PLAN25'] )] ['supervisor'] = $row ['SUPR25'];
        $poContactsArray [trim ( $row ['PLAN25'] )] ['backUpSuper'] = $row ['BSUP25'];
        $poContactsArray [trim ( $row ['PLAN25'] )] ['manager'] = $row ['MANG25'];
        $poContactsArray [trim ( $row ['PLAN25'] )] ['backUpManger'] = $row ['BMAN25'];
        $poContactsArray [trim ( $row ['PLAN25'] )] ['director'] = $row ['DIRC25'];
        $poContactsArray [trim ( $row ['PLAN25'] )] ['backUpDirector'] = $row ['BDIR25'];
    }
    
    return $poContactsArray;
}

/**
 * Function structures all outgoing mail to the same format
 *
 * @param string $toName
 * @param string $toEmail
 * @param integer $ticketId
 * @param integer $class
 * @param integer $type
 * @param string $shortDescription
 * @param string $cc
 * @param string $sendMailFlag
 * @param integer $priority
 * @param string $subject
 */
function send_mail($toName, $toEmail, $ticketId, $class, $type, $shortDescription, $cc, $sendMailFlag, $priority, $subject) {
    
    global $conn, $CONO, $SITENAME, $mtpUrl, $FROM_MAIL;
    
    //Start of Temp Change for Ceva and DHL for Server Change ******************************************
    $tmpUser = strtoupper(  $toEmail );
    if ( ( strpos( $tmpUser, 'DHL' ) !== false ) ) {
        $tmpUrl = $mtpUrl;
        $mtpUrl = "http://sedas5.is.sandvik.com:89/production/smc/global/lps";
    }
    //End of Temp Change for Ceva and DHL for Server Change ******************************************
    
    $message = "\n\n<b>********** DO NOT REPLY TO THIS MESSAGE **********</b><br><br>";
    $message .= "Dear " . trim ( $toName ) . ",<br><br><br>";
    $message .= "<b>" . $priority . "</b> " . trim ( $SITENAME ) . " " . trim ( $class ) . " - " . trim ( $type ) . " ticket requires your attention.<br><br>";
    $message .= "<b>Short Description:</b> " . $shortDescription . "<br><br><br>";
    $message .= "Please reference ticket #" . $ticketId . "<br><br><br>";
    $message .= "To directly reference the ticket follow the link below:<br><br>";
    $message .= "<a href='$mtpUrl/showTicketDetails.php?ID01=" . $ticketId . "'>View Ticket</a><br><br>";
    
    //Sets up mail to use HTML formatting
    $strHeaders = "MIME-Version: 1.0\r\n";
    $strHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $strHeaders .= "From: " . $FROM_MAIL . "\r\n";
    if ($cc) {
        $strHeaders .= "cc: " . $cc . "\r\n";
    }
    //echo $ticketId . "<hr>";
    //echo trim($subject) . "<hr>";
    //echo trim($message) . "<hr>";
    //echo trim($toEmail) . "<hr>";
    //echo $strHeaders . "<hr>";
    
    
    if ($sendMailFlag == true) {
        if( $toEmail ){
            if( mail( $toEmail, $subject, $message, $strHeaders ) ){
                
            }else{
                $handle = fopen("./mailFailures/mailErrors.csv","a+");
                fwrite($handle, "send_mail Function - ," . $toEmail . "," . $subject . "," . substr($message, 0, 100 ) . "\n" );
                fclose($handle);
            }
        }
    }
    
    if( isset( $tmpUrl ) ){
        $mtpUrl = $tmpUrl;
    }
}

/*
 * Return planner contacts base on customer and delivery sequence
 *
 * @parm none
 *
 * @return array of Planner Contacts
 */
function get_dseq_planner_contact_array() {
    global $conn, $CONO;
    
    $sql = "select CUSN1X, DSEQ1X, OPMG1X, BOPM1X, DIRC1X, BDIR1X FROM CIL13XL00 ORDER BY CUSN1X, DSEQ1X";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    //echo $sql;
    
    
    $plannerContactsArray = array ();
    while ( $row = odbc_fetch_array ( $res ) ) {
        $plannerContactsArray [trim ( $row ['CUSN1X'] )] [trim ( $row ['DSEQ1X'] )] ['logistics'] = $row ['OPMG1X'];
        $plannerContactsArray [trim ( $row ['CUSN1X'] )] [trim ( $row ['DSEQ1X'] )] ['backUpLogistics'] = $row ['BOPM1X'];
        $plannerContactsArray [trim ( $row ['CUSN1X'] )] [trim ( $row ['DSEQ1X'] )] ['director'] = $row ['DIRC1X'];
        $plannerContactsArray [trim ( $row ['CUSN1X'] )] [trim ( $row ['DSEQ1X'] )] ['backUpDirector'] = $row ['DIRC1X'];
    }
    
    return $plannerContactsArray;
}

/*
 * Return planner contacts base on customer
 *
 * @parm none
 *
 * @return array of Planner Contacts
 */
function get_planner_contact_array() {
    global $conn, $CONO;
    
    $sql = "select CUSN13, OPMG13, BOPM13, DIRC13, BDIR13 FROM CIL13L04 ORDER BY CUSN13";
    $res = odbc_prepare ( $conn, $sql );
    odbc_execute ( $res );
    //echo $sql;
    
    
    $plannerContactsArray = array ();
    while ( $row = odbc_fetch_array ( $res ) ) {
        $plannerContactsArray [trim ( $row ['CUSN13'] )] ['logistics'] = $row ['CUSN13'];
        $plannerContactsArray [trim ( $row ['CUSN13'] )] ['backUpLogistics'] = $row ['OPMG13'];
        $plannerContactsArray [trim ( $row ['CUSN13']  )] ['director'] = $row ['DIRC13'];
        $plannerContactsArray [trim ( $row ['CUSN13']  )] ['backUpDirector'] = $row ['BDIR13'];
    }
    
    return $plannerContactsArray;
}

//D0185 - LPS owner assignment, retrieve the default responsible user for a selected stockroom
function get_default_responsible( $classification, $receiveStockroom ){
    global $CONO, $conn;
    
    $sql = "select RESP29 from cil07 t1"
        . " inner join cil29 t2"
            . " on t1.attr07 = t2.back29"
                . " where name07=(select NAME07 from cil07"
                    . " where attr07=$receiveStockroom) and attr07 in (select back29 from cil29) AND CLAS29=$classification";
                    
                    
                    $res = odbc_prepare( $conn, $sql );
                    odbc_execute ( $res );
                    
                    while( $row = odbc_fetch_array( $res ) ){
                        return $row['RESP29'];
                    }
                    
}

