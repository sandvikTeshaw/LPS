<?php
/**
 * System Name:             Logistics Process Support
 * Program Name:            functions.php<br>
 * Development Reference:   DI868<br>
 * Description:             This is the LPS function file
 *
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>
 *  --------  ------  ----------  ----------------------------------<br>
 *  DI868A    TS      29/05/2009   Added backup functionality<br>
 *  DI868B     TS     16/06/2009   Change functionality to maintain original owner of ticket<br>
 *  DI932      TS     30/07/2009   Global Retunrs Functionality<br>
 *  D0108      TS     15/01/2010   Notification and workflow modifcation<br>
 *  D0109      TS     02/02/2010   Change Due Date to Receipt Date<br>
 *  D0114      TS     03/02/2010   Remove Feedback functionality <br>
 *  D0097      TS     04/01/2010   Changes for new escalation process<br>
 *  D0097b	   TS	  03/06/2010   Added function for lost password<br>
 *  D0171 	   TS     08/06/2010   Incorrect Receipt date<br>
 *  D0128      TS     06/07/2010   Advanced search removal of elements<br>
 *  D0180	   TS 	  09/07/2010   Mdification of Returns Notifications<br>
 * 	D0185      TS     27/07/2010   Modifications for missing owners<br>
 *  D0215	   TS	  04/11/2010   Paperwork not recieved priority error<br>
 *  D0217	   TS	  22/11/2010   Prevent Owners From Changing Priority<br>
 *  D0301	   TS	  18/03/2011   Performance issues for LPS application<br>
 *  D0246 	   TS	  11/05/2011   Return Search Results<br>
 *  D0260	   TS	  21/07/2011   LPS Part snapshot information addition<br>
 *  D0359 	   TS	  10/08/2011   Notification restructure for new tickets<br>
 *  D0481	   TS	  17/01/2012   LPS Regional Notifications and Contacts<br>
 *  D0270	   TS     05/02/2012   Supervisor reports enhancement
 *  i-2312795  TS	  23/04/2013   Disable Textboxed
 *  i-2294568  TS	  25/04/2013   Remove Casting
 *  D0341      TS     09/01/2014   PFC Changes
 *  i-2987839  TS     12/11/2014   Bug With C1 DRP issues
 *  LP0002     TS     18/08/2015   LPS DRP manager workflow issue
*   GLBAU8595  IS     04/10/2015   added list_planner_table function
*   LPS0003    IS     31/10/2015   Change Notification flow to use new Outbound planner set-up
*   LPS0004    IS     13/11/2015
*	LP0013	   IS	  21/05/2016	added new function to check user authentication from CIL40
*   LP0016     AG     12/12/2016    Outbound Planner to be added to all Global Process Support Ticket Types - Changes in list_tickets()
*   LP0019     AG     06/03/2017    Function for returning priority list and hoursWithOutWeekend function is added
*   LP0021     TS     11/04/2014    Valiation addition
*   LP0022      TS      03/05/2017  Expedite Classification Change
*   LP0020     TS     09/06/2017    Completion enhancement
*   LP0018     AG     07/07/2017    Functions of last login for user profile enhacement 
*   LP0024      TS      07/10/2017  Fix sourcing for Expedite tickets.
*   LP0029      TS    15/12/2017    Functions Separation, structure modification 
 */
/**
 */
//LP0013

include_once 'functions/userFunctions.php';
include_once 'functions/userGroupFunctions.php';
include_once 'functions/supervisorFunctions.php';
include_once 'functions/notificationMaintenanceFunctions.php';
include_once 'functions/fieldBuilderFunctions.php';
include_once 'functions/ticketListFunctions.php';
include_once 'functions/staticDefineFunctions.php';
include_once 'functions/s21OrderFunctions.php';
include_once 'functions/ticketFunctions.php';
include_once 'functions/commonFunctions.php';
include_once 'functions/validationFunctions.php';
include_once 'functions/ticketSourcingFunctions.php';
include_once 'functions/s21PartsFunctions.php';
include_once 'functions/s21StockroomFunctions.php';
include_once 'functions/s21CustomerFunctions.php';
include_once 'functions/s21SupplierFunctions.php';
include_once 'functions/escalationFunctions.php';
include_once 'functions/s21DescriptionsFunctions.php';
include_once 'functions/slaFunctions.php';




?>