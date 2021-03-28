<?php
//Start session, set here so that all pages include
session_start ();

//Allowing application to get variables, set here so that all pages will include
extract ( $_REQUEST );
extract ( $_POST );
extract ( $_GET );

//SYSTEM Constants
define ( 'FACSLIB', 'OSLDITDATL' );
define ( 'DATALIB', 'OSLDITDATL' );
define ( 'YSLIB', 'OSLDIPDATL' );
define ( 'SYSTEM', 'SEDAS24_DIT' );
define ( 'DB_USER', 'PHPDITUSR' );
define ( 'DB_PASS', 'PHPDITUSR' );
define ( 'FINANCIAL_LIB', 'OSLDIPDATL' );
define ( 'MODS_LIB', 'OSLDIPDATL' );
define ( 'PROGRAM_LIB', 'OSLDIPGMP3' );

$IMG_DIR = "../../../../../images";	//Path to the image directory

$CONFIG_PATH = "copysource/config.php";	//Path to the config file

$baseUrl = "http://nlmnt448.win.dom.sandvik.com/";
$mtpUrl = "http://nlmnt448.win.dom.sandvik.com/development/smc/global/lps";	//Site url
$attachmentsUrl = "http://nlmnt448.win.dom.sandvik.com/development/smc/attachments";

$CONO = "DI";	//Default company code of application

$SITENAME = "LPS";	//Site name of application

$FROM_MAIL = "lps.test@sandvik.com";	//Email address mail is sent from <br>
$FROM_USER = "Logistics Process Support"; //This is the from user for mail


//Comment out section to change when away Changed while away
$LPS_DEFAULT_ADMIN = 10414; //This is set to ensure that empty buyer numbers are picked up

$LPS_RESOURCE_NAME = "Niklas Massinen"; //default name
$LPS_RESOURCE_EMAIL = "niklas.massinen@sandvik.com"; //default email

//Comment out section to change when away Changed while away
$MM_DEFAULT_CONTACT = 10414; //This is set to ensure that empty buyer numbers are picked up
$MM_DEFAULT_CONTACT_NAME = "Niklas Massinen"; //default name
$MM_DEFAULT_CONTACT_EMAIL = "niklas.massinen@sandvik.com"; //default email

/* Add back when deafult user is away
$MM_DEFAULT_CONTACT = 10983; //This is set to ensure that empty buyer numbers are picked up
$MM_DEFAULT_CONTACT_NAME = "Laura Brennan"; //default name
$MM_DEFAULT_CONTACT_EMAIL = "laura.brennan@sandvik.com"; //default email
*/

//Comment out section to change when away Changed while away
$GOP_DEFAULT_CONTACT = 10414; //This is set to ensure that empty buyer numbers are picked up
$GOP_DEFAULT_CONTACT_NAME = "Niklas Massinen"; //default name
$GOP_DEFAULT_CONTACT_EMAIL = "niklas.massinen@sandvik.com"; //default email

/* Add back when deafult user is away
$GOP_DEFAULT_CONTACT = 10983; //This is set to ensure that empty buyer numbers are picked up
$GOP_DEFAULT_CONTACT_NAME = "Laura Brennan"; //default name
$GOP_DEFAULT_CONTACT_EMAIL = "laura.brennan@sandvik.com"; //default email
*/

//Comment out section to change when away Changed while away
$C2_DEFAULT_CONTACT = 10414; //This is set to ensure that empty C2 default is correct
$C2_DEFAULT_CONTACT_NAME = "Niklas Massinen"; //default name
$C2_DEFAULT_CONTACT_EMAIL = "niklas.massinen@sandvik.com"; //default email

/* Add back when deafult user is away
$C2_DEFAULT_CONTACT = 10983; //This is set to ensure that empty C2 default is correct
$C2_DEFAULT_CONTACT_NAME = "Laura Brennan"; //default name
$C2_DEFAULT_CONTACT_EMAIL = "laura.brennan@sandvik.com"; //default email
*/

$C1_DEFAULT_CONTACT = 11367; //This is set to ensure that C1 default is correct
$C1_DEFAULT_CONTACT_NAME = "David Vilaplana"; //default name
$C1_DEFAULT_CONTACT_EMAIL = "david.vilaplana@sandvik.com"; //default email
$TEST_SITE = "N";	//This flag turns off sql inserts and echos them instead

//Comment out section to change when away Changed while away
$GPA_DEFAULT_CONTACT = 10414; //This is set to ensure that empty buyer numbers are picked up
$GPA_DEFAULT_CONTACT_NAME = "Niklas Massinen"; //default name
$GPA_DEFAULT_CONTACT_EMAIL = "niklas.massinen@sandvik.com"; //default email

//Comment out section to change when away Changed while away
$GLP_DEFAULT_CONTACT = 10414; //This is set to ensure that empty buyer numbers are picked up
$GLP_DEFAULT_CONTACT_NAME = "Niklas Massinen"; //default name
$GLP_DEFAULT_CONTACT_EMAIL = "niklas.massinen@sandvik.com"; //default email

$SHOW_NOTIFICATIONS = false; //This is to show who the notifications will be sent to, this will also turn off sending of mail

$COMPANY_NAME = "Sandvik Mining and Construction";

$SITE_TITLE = "Logistics Process Support - Test (DIT) Environment";

//Set alternate color - Light blue
$ALTERNATE_COLOR = "#CCDDDD";

$USER_GUIDE_LINK = "https://sandvik.sharepoint.com/teams/SMRTKnowledgeBank/_layouts/15/search.aspx/siteall?q=LPS";

$GLOBALS['normalizeSaveChars'] = array( '$'=>'�', '@'=>'@', '/' => '-' );

$SURVEY_HEADING = "Help us improve our service by answering the following optional questions.";
$SURVEY_ADDITIONAL_INFO_LABEL = "What can we do to improve our service?";

$TEST_SITE = 'N'; //Used for Test site so no emails are sent while testing --AG

?>
