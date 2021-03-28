<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            uploadTemplates.php<br>
 * Development Reference:   LP0026<br>
 * Description:             LPS Mass Upload
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 *  Initial    TS      16/10/2017 INITIAL Creation
 *  LP0055     AD      08/04/2019 GLBAU-15650_LPS Vendor Price Update_CR
 *  LP0068    AD      24/04/2019  GLBAU-16824_LPS Vendor Change
 * 
 */
/**
 */
include 'copysource/config.php';
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title><?echo $SITE_TITLE;?></title>

<style type="text/css">
<!--

@import url(copysource/styles.css);
-->
</style>
<link rel="stylesheet" type="text/css" href="copysource/custom.css">    
<script type="text/javascript">

</script>

</head>
<?php 

include_once 'copysource/header.php';
$listTitle = "Upload Templates";

?>
<div id="wrapper">
        <div class="container">
            <div class="col-md-8 col-sm-8 col-xs-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $listTitle;?>
                    </h3>
                    </div>
               </div>
 
				<table id="outbound-table" class="info-table">
                    <thead>		
                    	 <tr>
                        	<th colspan='2'>Global Order Processing</th>        
                        </tr>
                         <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GOPShortOverDamaged.xlsx'>Short Shipment</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GOPShortOverDamaged.xlsx'>Over Shipment</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GOPShortOverDamaged.xlsx'>Damaged Part</a></td>
            			</tr>

            			<tr>
                        	<th colspan='2'>Global Price & Administration</th>        
                        </tr>
                        <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GPASimilarItem.xlsx'>Price is Different on Similar Item</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GPACompetitorFeedback.xlsx'>Competitor Feedback to GLP</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GPACustomerFeedback.xlsx'>Customer Feedback to GLP</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GPASandvikFeedback.xlsx'>Sandvik Feedback to GLP</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GPACostCheck.xlsx'>Cost Check - GLP Team ONLY</a></td>
            			</tr>
            			<tr>
                        	<th colspan='2'>Inbound Warehouse</th>        
                        </tr>
                         <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/InboundOverShort.xlsx'>Short Shipment</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/InboundOverShort.xlsx'>Over Shipment</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/InboundDamage.xlsx'>Damaged Part</a></td>
            			</tr>
            			<tr>
                        	<th colspan='2'>Global Material Management</th>        
                        </tr>
                         <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GMMPriceAvailability.xlsx'>Price and Availability</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GMMOrigin.xlsx'>Country of Origin</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GMMLongTerm.xlsx'>Long Term Delivery</a></td>
            			</tr>
            			 <tr>
                         	<td width='5%'>&nbsp;</td>
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GMM_MDSS.xlsx'>Material Data Safety Sheet</a></td>
            			</tr>
            			 <tr><!-- LP0055 -->
                         	<td width='5%'>&nbsp;</td><!-- LP0055 -->
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GMM_VPLU.xlsx'>Vendor Price and Leadtime Update</a></td><!-- LP0055 -->
            			</tr><!-- LP0055 -->
            			 <tr><!-- LP0068 -->
                         	<td width='5%'>&nbsp;</td><!-- LP0068 -->
            				<td><a href='<?php echo $mtpUrl;?>/uploadTemplates/GMM_VC.xlsx'>Supplier/VDSR Update</a></td><!-- LP0068 -->
            			</tr><!-- LP0068 -->
                    </thead>
               </table>
            </div>
      </div>
</div><!--panel heading-->