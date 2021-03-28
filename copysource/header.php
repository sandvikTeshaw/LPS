<?php

// Set Cache file and days
//$cachefile = 'cache/lpsheadercache.txt';
//$cachetime = 4320 * 60; // 3 Days

//Start Buffer
ob_start();

// Serve from the cache if it is younger than $cachetime
//if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile)) ) {
//
//		include($cachefile);
//		echo "<!-- Cached ".date('jS F Y H:i', filemtime($cachefile))." -->";
//

//}else{
if( !isset( $_SESSION['userID']) ){
    
    if( !isset($_REQUEST['action']) || ( $_REQUEST['action'] != ""  && $_REQUEST['action'] != "register" ) ){
        error_mssg( "NONE");
        
        die();
    }
}else{
    

	$name = user_name_by_id( $_SESSION['userID'] );
}



?>

<table width=100% cellpadding="0" cellspacing="0" topmargin="0"
	leftmargin="0" class='header' border="0">
	<TR>
		<TD class='bold'><img src="<?
	echo $baseUrl;
	?>/images/logo.gif"><?
	if( isset( $title ) ){
	   echo trim ( $title );
	}
	if ( isset( $name) ) {
		echo " - " . trim ( $name );
	}
	
	if( isset($parm) ){
	    
	}else{
	    $parm = "";
	}
	?>
   </TD>

   <td width=65%>&nbsp;</td>
   <form method='get' action='showTicketDetails.php'>
   <td class='bold'>
   	Quick Search<br>
   	<input type='text' name='ID01' value='<?echo $parm;?>' class='small'>
   	<input type='submit' value='GO' class='go'>
   </td>
   </form>

	</TR>
</table>

<?php

		//Open Cache File as writeable
		//$fp = fopen($cachefile, 'w+'); // open the cache file for writing


		//Write to cache file
		//fwrite($fp, ob_get_contents()); // save the contents of output buffer to the file

		//fwrite($fp, "abc123" );

		//Close connection to cache file
		//fclose($fp); // close the file



//}

//Output buffer - Display to page
ob_end_flush(); // Send the output to the browser

?>
