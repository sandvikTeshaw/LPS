<?
/**
 * System Name:             Logistics Process Support
 * Program Name:            help.php<br>
 * Development Reference:   DI868<br>
 * Description:             LPS Application demonstrations page
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
include 'copysource/functions.php';
include '../common/copysource/global_functions.php';
//Testing the commit

headerFrame ( $_SESSION ['name'] );

?>
<head>
<title><?
echo $SITE_TITLE . " "?>User Demonstrations</title>
</head>
<body vlink='blue'>
<center>
<table width=30% border=0>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th colspan=3>LPS Demonstrations</th>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <?
    if ($handle = opendir ( './demo' )) {
        while ( false !== ($file = readdir ( $handle )) ) {
            if ($file != "." && $file != "..") {
                
                $fileName = str_replace( ".htm", "", $file );
                if( $fileName != "swf" ){
                ?>
                <tr>
                    <td width=20%>&nbsp</td>
                    <td colspan='2'><u><b><?echo $fileName;?></b></u></td>
                </tr>
                
                <?
                $handleDir = "./demo/$fileName";
                $newHandle = opendir( $handleDir );
                
                while ( false !== ($urlfile = readdir ( $newHandle )) ) {
                    if ($urlfile != "." && $urlfile != "..") {
                        $newFileName = str_replace( ".htm", "", $urlfile );
                        $urlfile = str_replace( ".htm", ".swf", $urlfile );
                        
                        ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td width=10%>&nbsp;</td>
                            <td><a href='./demo/swf/<?echo $urlfile;?>'><?echo $newFileName;?></a></td>
                        </tr>
                        
                        <?
                    }
                    
                }
             }
          }
          ?><tr><td>&nbsp;</td></tr><?
        }
    }
    ?>

</table>
</center>
</body>


