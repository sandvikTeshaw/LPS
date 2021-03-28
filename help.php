<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            help.php<br>
 * Development Reference:   DI868<br>
 * Description:             LPS Application help page
 * 
 *  MODIFICATION CONTROL<br>
 *  ====================<br>
 *    FIXNO     BY    DD/MM/YYYY  COMMENT<br>                           
 *  --------  ------  ----------  ----------------------------------<br>
 * 
 */
/**
 */

include_once 'copysource/config.php';
//include 'copysource/functions.php';
//include '../common/copysource/global_functions.php';

//include_once 'copysource/header.php';

?>
<head>
<title><?echo $SITE_TITLE . " "?>Help</title>
</head>
<body vlink='blue'>
<center>
<table width=30%>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <th>LPS Help</th>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align='center'><a href='<?echo $USER_GUIDE_LINK;?>'>User Guides</a></td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align='center'><a href='demos.php'>Demonstrations</a></td>
    </tr>
</table>
</center>
</body>


