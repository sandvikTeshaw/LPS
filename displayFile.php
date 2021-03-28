<!doctype html>
<html lang="en" dir="ltr">

<head>
<meta charset="UTF-8">
<title>Directory Contents</title>
<link rel="stylesheet" href="./copysource/css/display.css">
<script src="./copysource/js/sorttable.js"></script>
<?php header('Content-type: text/plain php'); ?>
</head>

<?php 


if( isset($_REQUEST['directory']) &&  $_REQUEST['directory'] != "" ){
    
    
    if( $_REQUEST['directory'] != 'apacheRoot'){
        chdir("./" . $_REQUEST['directory']);
    }else{
        chdir("../../../../../logs/");
        $file = "error.log";
    }
    

    
}
if( $_REQUEST['directory'] != 'apacheRoot' ){
    $file = "." . $_REQUEST['file'];
}

$myfile = fopen( $file, "r") or die("Unable to open file!");
echo fread($myfile,filesize( $file ));
fclose($myfile);


?>