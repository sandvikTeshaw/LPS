<?php
/**
 * System Name:             Common
 * Program Name:            global_functions.php<br>
 * Development Reference:   <br>
 * Description:             Global Functions page<br>
 * 							Function that can be used cross application
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    	DD/MM/YYYY  COMMENT<br>
 *  --------  ------  	----------  ----------------------------------<br/>
 *  D0270      	TS    	01/05/2012  Supervior reports logic<br/>
 *  D0555	   	TS	 	08/05/2012  Re-Assigne LPS issues to other users<br/>
 *  D0455 	   	TS 		05/06/2013 	Re-Design to entire menu for browser compatability and Look aand feel
 *  D0341       TS      24/03/2014  Added Question Maintenance to menu
 *  GLBAU11283  IS      17/09/2015  Menu option changed.
 *  LP0004      IS      13/11/2015
 *	LP0013		IS		05/05/2016	change menu option for Cost Check � GLP Team ONLY
 *  LP0016      AG      11/12/2016  Outbound Planner to be added to all Global Process Support Ticket Types
 *  LP0017      TS      05/02/2017  Survey Functionality Addition
 *  LP0021      TS      02/03/2017  Enable New Key Spider in the Web Ticket Types
 *  LP0019      AG      06/03/2017  SLA maintenance link added
 *  LP0022      TS      03/05/2017  Expedite Classification Change
 *  LP0018      AG      05/07/2017  User attributes link added
 *  LP00025     TS      20/08/2017 Uplift of ticket listing
 *  LP0039      KS      28/03/2018  In the LPS "Register for LPS account" page please add hyperlinks to instruction guidelines (SPIDER 2.0)
 *  LP0029      TS      05/31/2018  Mass Upload Changes
 *  LP0050      KS      02/08/2018  Create new LPS ticket type �Inbound Parts Not Assembled�
 *  LP0052      AD      18/09/2018  Create new LPS ticket type �Supersession�
 *  LP0056      AD      21/11/2018   - Removal of List Ticket Menu option and view from LPS
 *  LP0062      AD      19/12/2018  ticket type Documentation Request for Trade Compliance
 *  LP0053      AD      25/01/2019  Postpone Functionality - demerge and fix
 *  LP0066      AD      27/02/2019  New LPS GOPS ticket type: Freight Quotation Request
 *  LP0055      AD      13/03/2019    GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0055      KS      29/03/2019  fix i-6126975
 *  LP0068      AD      23/04/2019    GLBAU-16824_LPS Vendor Change
 *  LP0076      AD      28/06/2019    GLBAU-17554_Inbound Parts Not Marked with Sandvik Part Number
 *  LP0077      AD      01/07/2019    GLBAU-17554_LPS Inbound PO not mentioned
 *  LP0085      AD      08/10/2019    GLBAU-18097_4 LPS Tickets Aurora under regional order process support 
 *  
 */

$userArray = get_user_list ();

$authRead = 0;
$authCreate = 0;


if( isset($_SESSION['userID']) && $_SESSION['userID'] != "" && $_SESSION['userID'] != 0 ){
    
    $superExists = is_supervisor( $_SESSION['userID'] );
    // LP0013
    $UserInfo = user_info_by_id($_SESSION['userID']);
    $authority =   trim($UserInfo ['AUTH05']);
    
    
    //Get User group create / read authority from CIL40
    $userAuthsql = "Select * from CIL40 where USER40=". $_SESSION['userID']." AND GRUP40=2";
    $res = odbc_prepare( $conn, $userAuthsql );
    odbc_execute( $res );
    
    while( $row = odbc_fetch_array( $res ) ){
        $authRead	= trim($row['READ40']);
        $authCreate = trim($row['CRTE40']);
    }
}


?>
<ul id="menu">
     <li><a href="<?php echo $mtpUrl;?>" class="nodrop">Home</a></li>
    <li><a href="<?php echo $mtpUrl;?>/tickets2.php?from=menu&queryType=frontLine&status=1&type=0" class="nodrop">View Tickets</a></li><!-- LP0025 - Ticket Uplift -->
	<li><a href="javascript:void(0)" class="drop">List Tickets</a><!-- Begin 4 columns Item -->
  <!--  LP0052_AD    <div class="dropdown_4columns"><!-- Begin 4 columns container -->
        <div class="dropdown_4columns_left"><!-- Begin 4 columns container -->  <!--  LP0052_AD -->
            <div class="col_5">
                <h2>Ticket Listings - Select ticket type listing below</h2>
            </div>
            <?php
	        //D0270 - Super menu item added.
	        if( is_supervisor( $_SESSION['userID'] ) ){?>
	        <div class="col_2">
                <ul>
                    <li><a href="<?php echo $mtpUrl;?>/superreports.php">My Reports</a></li>
                </ul>
            </div>
            <?php }?>
            <!-- Added for LP0016, When adding more menu items, the whole menu disturbs so following div will clear it -->
            <div style="clear:both;"></div>
            <!-- Ended here -->
            <div class="col_1">
                <h3 class='nopadding'><br/>Returns</h3>
                <ul>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=9&type=53">Global Returns</a></li>
                </ul>
            </div>
            <div class="col_1">

                <h3 class='nopadding'>Global Order Processing Support</h3>
                <ul>
                	<!-- LP0022 Start-->
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=42">Expediting</a>
                    <!-- LP0022 End-->
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=14">Short Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=19">Over Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=23">Damaged Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=18">Misdirected Part/Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=22">Wrong Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=24">Miscellaneous</a></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=76">Shipment Packaging Issue</a><br/></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=47">Loading/Shipping Error<br/>(Control Tower use only)</a><br/></li>
                    <!-- LP0021 - Start Added -->
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=102">Track and Trace</a></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=103">Invoiced item error<br/>(Wrong price on item)</a><br/></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=107">Full Package Short Shipped</a></li>
                    <!-- LP0021 - End Added -->
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=126">Documentation request</a></li> <!-- LP0062_AD --> 
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=3&type=129">Freight Quotation Request</a></li> <!-- LP0066_AD --> 
                    
                    
                </ul>
            </div>

            <div class="col_1">
<!-- GLBAU11283 Menu Change -->
                <h3 class='nopadding'>Global Pricing & Administration</h3>
                <ul>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=5&type=60">Price is Different<br/>On Similar item</a><br/></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=5&type=61">Competitor Feedback<br/>To GLP</a><br/></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=5&type=62">Customer Feedback to GLP</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=5&type=74">Sandvik Feedback to GLP</a></li>
                    	<?php
							// LP0013 check if user has authority to read from CIL40
                    	if( isset( $authority ) && $authority == "S" || $authRead == '1' ){
					 	?>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=5&type=75">Cost Check<br/>GLP Team ONLY</a><br/></li>
                    	<?php
							}
						?>
                    </ul>
            </div>
            <div class="col_1">

                <h3 class='nopadding'><br/>Inbound  - Warehouse</h3>
                <ul>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=31">Short Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=32">Over Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=33">Damaged Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=34">Wrong Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=48">Delivery Not Due Yet</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=49">No Standard Cost</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=51">Pack List Issues</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=52">MSHS/ATEX Inspection Failure</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=59">Incomplete Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=71">Pre-Pack Issues</a></li>
                    <li class='nospace'><small></small><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=108">Overdue/Parts not Goods Receipted</a></small></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=50">Miscellaneous</a></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=118">Inbound Parts <br />Not Assembled</a></li>     <!-- //**LP0050 -->
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=70">Missing Sandvik <br />Part No</a></li>  
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=134">Inbound Parts Not Marked <br />with Sandvik Part Number</a></li>     <!-- //**LP0076 -->
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=135">Inbound Parts PO not <br />Mentioned</a></li>     <!-- //**LP0077 -->
                
                </ul>
            </div>
            <div class="col_1">

                <h3 class='nopadding'>Global Material Management</h3>
                <ul>
                    <!-- LP0022 <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=42">Expediting</a></li>-->
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=43">Pricing and Availability</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=44">Availability Only</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=54">Weight  kg</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=55">Country of Origin</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=56">Long Term Declaration</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=57">Material Safety Data Sheet</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=58">Weight kg and Dimension</a></li>
					<!-- LP0021 - Start Added -->
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=93">Declartion of Conformity</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=94">Mill Test Certifcate</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=91">Export Control Classification Number</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=92">CE Marking</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=95">CBCA Programme Route B</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=96">3C Marking</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=97">SONCAP Nigeria</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=105">Item Test Certificate</a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=104">Supplier Declartion of Origin </a></li>
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=106">Dual Use Certificate</a></li>
					<!-- LP0021 - End Added -->
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=130">Supplier Cost & Leadtime Update</a></li><!-- LP0055_AD -->
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=8&type=133">Supplier/VDSR Update</a></li><!-- LP0068_AD -->
					
                 </ul>
            </div>
             <div class="col_1">

                <h3 class='nopadding'>Regional Order Process Support</h3>
                <ul>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=66">Short Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=65">Over Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=63">Damaged Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=64">Misdirected Part/Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=67">Wrong Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=110">Packaging Issues</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=68">Miscellaneous</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=136">Overdue purchase order</a></li><!-- LP0085_AD -->
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=137">Expedite</a></li><!-- LP0085_AD -->
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=138">Price and Availability</a></li><!-- LP0085_AD -->
                    <li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=11&type=139">Availability Only</a></li><!-- LP0085_AD -->
                </ul>
            </div>    
            <div class="col_1">
                <h3 class='nopadding'><br/>Supersessions</h3><!-- LP0052_AD -->
                <ul><!-- LP0052_AD -->
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=17&type=121">New Supersession</a></li><!-- LP0052_AD -->					
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=17&type=122">Superseded item with a strategic stock setting</a></li><!-- LP0052_AD -->					
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=17&type=123">Investigation in a Supersession</a></li><!-- LP0052_AD -->					
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=17&type=124">Temporary Removal of a Supersession</a></li><!-- LP0052_AD -->
					<li><a href="<?php echo $mtpUrl;?>/tickets.php?status=1&CLAS09=17&type=125">Change or Removal of a replacement part or the end date entered</a></li><!-- LP0052_AD -->					
										

                </ul><!-- LP0052_AD -->
            </div><!-- LP0052_AD -->

        </div><!-- End 4 columns container -->

    </li><!-- End 4 columns Item -->
	<li><a href="javascript:void(0)" class="drop">Add Tickets</a><!-- Begin 4 columns Item -->
        <div class=dropdown_4columns_left><!-- Begin 4 columns container -->

            <div class="col_5">
                <h2>Add Listings - Select type of new ticket below</h2>
            </div>
            <div class="col_1">
                <h3 class='nopadding'><br/>Returns</h3>
                <ul>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=9&type=53">Global Returns</a></li>
                </ul>
            </div>
            <div class="col_1">

                <h3 class='nopadding'>Global Order Processing Support</h3>
                <ul>
                	<!-- LP0022 -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=42">Expediting</a>
                    <!-- LP0022 -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=14">Short Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=19">Over Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=23">Damaged Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=18">Misdirected Part/Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=22">Wrong Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=24">Miscellaneous</a></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=76">Shipment Packaging Issue</a><br/></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=47">Loading/Shipping Error<br/>(Control Tower use only)</a></li>
                    <!-- LP0021 - Start Added -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=102">Track and Trace</a></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=103">Invoiced item error<br/>(Wrong price on item)</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=107">Full Package Short Shipped</a></li>
                    <!-- LP0021 - End Added -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=126">Documentation request</a></li><!-- LP0062_AD -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=3&type=129">Freight Quotation Request</a></li><!-- LP0066_AD -->
                    
                </ul>
            </div>

            <div class="col_1">
<!-- GLBAU11283 Menu Change -->
                <h3 class='nopadding'>Global Pricing & Administration</h3>
                <ul>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=5&type=60">Price is Different<br/>On Similar item</a><br/></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=5&type=61">Competitor Feedback<br/>To GLP</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=5&type=62">Customer Feedback to GLP</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=5&type=74">Sandvik Feedback to GLP</a></li>
                    <?php
							// LP0013 check if user has authority to create from CIL40
                    if( isset( $authority ) && $authority == "S" || $authCreate == '1' ){
					 	?>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=5&type=75">Cost Check<br/>GLP Team ONLY</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col_1">

                <h3 class='nopadding'><br/>Inbound  - Warehouse</h3>
                <ul>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=31">Short Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=32">Over Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=33">Damaged Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=34">Wrong Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=48">Delivery Not Due Yet</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=49">No Standard Cost</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=51">Pack List Issues</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=52">MSHS/ATEX Inspection Failure</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=59">Incomplete Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=71">Pre-Pack Issues</a></li>
                    <li class='nospace'><small><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=108">Overdue/Parts not Goods Receipted</a></small></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=50">Miscellaneous</a></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=118">Inbound Parts <br /> Not Assembled</a></li> 		<!-- //**LP0050 -->
					<!-- //**LP0055_KS  <li class='nospace'><a href="<?php //**LP0055_KS  echo $mtpUrl;?>/tickets.php?status=1&CLAS09=7&type=70">Missing Sandvik <br />Part No</a></li>  -->  
					<li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=70">Missing Sandvik <br />Part No</a></li>
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=134">Inbound Parts Not Marked <br />with Sandvik Part Number</a></li>     <!-- //**LP0076 -->
                    <li class='nospace'><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=7&type=135">Inbound Parts PO not <br />Mentioned</a></li>     <!-- //**LP0077 -->
					                    
                </ul>
            </div>
            <div class="col_1">

                <h3 class='nopadding'>Global Material Management</h3>
                <ul>
                    <!-- LP0022<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=42">Expediting</a></li>-->
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=43">Pricing and Availability</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=44">Availability Only</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=54">Weight  kg</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=55">Country of Origin</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=56">Long Term Declaration</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=57">Material Safety Data Sheet</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=58">Weight kg and Dimension</a></li>
					<!-- LP0021 - Start Added -->
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=93">Declartion of Conformity</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=94">Mill Test Certifcate</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=91">Export Control Classification Number</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=92">CE Marking</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=95">CBCA Programme Route B</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=96">3C Marking</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=97">SONCAP Nigeria</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=105">Item Test Certificate</a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=104">Supplier Declartion of Origin </a></li>
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=106">Dual Use Certificate</a></li>
					<!-- LP0021 - End Added -->
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=130">Supplier Cost & Leadtime Update</a></li><!-- LP0055_AD -->
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=8&type=133">Supplier/VDSR Update</a></li><!-- LP0068_AD -->
					
                </ul>
            </div>
             <div class="col_1">

                <h3 class='nopadding'>Regional Order Process Support</h3>
                <ul>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=66">Short Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=65">Over Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=63">Damaged Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=64">Misdirected Part/Shipment</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=67">Wrong Part</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=110">Packaging Issues</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=68">Miscellaneous</a></li>
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=136">Overdue Purchase Order</a></li><!-- LP0085_AD -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=137">Expedite</a></li><!-- LP0085_AD -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=138">Price and Availability</a></li><!-- LP0085_AD -->
                    <li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=11&type=139">Availability Only</a></li><!-- LP0085_AD -->
                    
                </ul>
            </div>

             <div class="col_1"><!-- LP0052_AD -->

                <h3 class='nopadding'><br/>Supersessions</h3><!-- LP0052_AD -->
                <ul><!-- LP0052_AD -->
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=17&type=121">NEW Supersession</a></li><!-- LP0052_AD -->					
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=17&type=122">Superseded item with a strategic stock setting</a></li><!-- LP0052_AD -->					
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=17&type=123">Investigation in a Supersession</a></li><!-- LP0052_AD -->					
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=17&type=124">Temporary Removal of a Supersession</a></li><!-- LP0052_AD -->
					<li><a href="<?php echo $mtpUrl;?>/addTicket.php?status=1&class=17&type=125">Change or Removal of a replacement part or the end date entered</a></li><!-- LP0052_AD -->					
										

                </ul><!-- LP0052_AD -->
            </div><!-- LP0052_AD -->

        </div><!-- End 4 columns container -->


    </li><!-- End 4 columns Item -->
	<li><a href="<?php echo $mtpUrl;?>/advancedSearch.php" class="nodrop">Adv. Search</a>
	<li><a href="javascript:void(0)" onclick="window.open('<?php echo $mtpUrl;?>/stats/index.php')" class="nodrop">Stats</a></li>
	<li><a href="<?php echo $mtpUrl;?>/profile.php" class="nodrop">My Profile</a></li>
	<?php
	if (isset($_SESSION ['authority']) && $_SESSION ['authority'] == "E") {

    		$superExists = is_supervisor( $_SESSION['userID'] );
        	if( $superExists ){
				?>
				<li class="drop"><a href="#" class="drop">Maintenance</a>
					<div class="dropdown_3columns">
						<div class="col_2">
	                		<h2>LPS Maintenance</h2>
	            		</div>
	            		<div class="col_1">
			                <ul class="greybox">
			                    <li><a href="<?php echo $mtpUrl;?>/maintenanceUser.php">User Maintenance</a></li>
			                    <li><a href="<?php echo $mtpUrl;?>/listAssignedTickets.php?from=maint">Re-Assign Open Tickets</a></li><!-- LP0004 Fix Feb 01/2017 -->
			                </ul>
		                </div>
					</div>
				</li>
			<?php    //LP0004
			}
	}elseif( isset($_SESSION ['authority']) == "S"){
	    
	    //if(  ($_SESSION ['authority'] == "L" ||  $_SESSION ['authority'] == "P" ||  $_SESSION['authority'] == "R" ) && !$superExists ){
	?>
			<li class="drop"><a href="#" class="drop">Maintenance</a>
				<div class="dropdown_3columns" style="width: 340px; padding: 5px;"><!-- Begin 3 columns container -->
					<div class="col_2">
	                	<h2>LPS Maintenance</h2>
	            	</div>
	            	<div class="col_1">
		                <ul class="greybox">
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceUser.php">User Maintenance</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/user_attributes_maintenance.php">User Attributes</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceGroup.php">Group Maintenance</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceNotification.php">Notifications</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceCompany.php">Company</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/listAssignedTickets.php?from=maint">Re-Assign Open Tickets</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/sla_maintenance.php">SLA Maintenance</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceEscalationReasons.php">Hold Escalation</a></li><!-- LP0053 - Added to Menu -->
		                    
		                </ul>
            		</div>
            		<div class="col_1">
		                <ul class="greybox">
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceClassification.php">Classification</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceMarket.php">Market Area</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceMessage.php">Message</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceQuestions.php">Question Maintenance</a></li><!-- D0341 - Added to Menu -->
		                    <li><a href="<?php echo $mtpUrl;?>/maintenance/survey/surveyMaintenance.php">Survey Maintenance</a></li><!-- LP0017 - Added to Menu -->
		                    <li><a href="<?php echo $mtpUrl;?>/linksMaintenance.php">Links Maintenance</a></li><!-- LP0039 - Added to Menu -->
		                    <li><a href="<?php echo $mtpUrl;?>/gitMaintenance.php">Global  Inventory Team</a></li><!-- LP0052 - Added to Menu -->
		                    <li><a href="<?php echo $mtpUrl;?>/sptMaintenance.php">Strategic Planning Team</a></li><!-- LP0052 - Added to Menu -->
		                    
		                </ul>
            		</div>
            	</div>
            </li>

		<?php
	}elseif(  isset( $_SESSION ['authority'])&& ($_SESSION ['authority'] == "L" ||  $_SESSION ['authority'] == "P" ||  $_SESSION['authority'] == "R" ) && ( !isset($superExists) || !$superExists) ){
	    ?>
			<li class="drop"><a href="#" class="drop">Maintenance</a>
				<div class="dropdown_3columns"><!-- Begin 3 columns container -->
					<div class="col_2">
	                	<h2>LPS Maintenance</h2>
	            	</div>
	            	<div class="col_1">
		                <ul class="greybox">
		                    <li><a href="<?php echo $mtpUrl;?>/listAssignedTickets.php?from=maint">Re-Assign Open Tickets</a></li>
		                </ul>
            		</div>
            	</div>
            </li>
	    
	<?php 
	}elseif(  ( isset( $_SESSION['authority'] ) && $_SESSION['authority'] == "R" ) && isset( $superExists ) && $superExists == true ){
	    ?>
			<li class="drop"><a href="#" class="drop">Maintenance</a>
				<div class="dropdown_3columns"><!-- Begin 3 columns container -->
					<div class="col_2">
	                	<h2>LPS Maintenance</h2>
	            	</div>
	            	<div class="col_1">
		                <ul class="greybox">
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceUser.php">User Maintenance</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/listAssignedTickets.php?from=maint">Re-Assign Open Tickets</a></li>
		                </ul>
            		</div>
            	</div>
            </li>
	    	
	<?php 
	}elseif(  isset( $_SESSION ['authority'] ) && ($_SESSION ['authority'] == "L" ||  $_SESSION ['authority'] == "P" ) && isset( $superExists ) && $superExists == true ){
	    ?>
			<li class="drop"><a href="#" class="drop">Maintenance</a>
				<div class="dropdown_3columns"><!-- Begin 3 columns container -->
					<div class="col_2">
	                	<h2>LPS Maintenance</h2>
	            	</div>
	            	<div class="col_1">
		                <ul class="greybox">
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceUser.php">User Maintenance</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/maintenanceNotification.php">Notifications</a></li>
		                    <li><a href="<?php echo $mtpUrl;?>/listAssignedTickets.php?from=maint">Re-Assign Open Tickets</a></li>
		                </ul>
            		</div>
            	</div>
            </li>
	    
	<?php 
	   }      

	?>
	<li><a href="<?php echo $mtpUrl;?>/massUpload.php" target='_new'>Mass Upload</a></li><!-- LP0029 - Mass Upload -->
    <li><a href="<?php echo $mtpUrl;?>/help.php" target='_new'>Help</a></li>
	<li class="menu_right"><a href="<?php echo $mtpUrl;?>/logout.php">Logout</a></li>
</ul>