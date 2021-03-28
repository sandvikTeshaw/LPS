<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<style type="text/css">
        div.a {
          text-indent: 20px;
        }

        div.b {
          text-indent: 40px;
        }

        div.c {
          text-indent: 60px;
        }
        div.d {
          text-indent: 80px;
        }
        div.e {
          text-indent: 100px;
        }
    </style>
</head>
<?php 
$path = "./";
$dir = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($dir,RecursiveIteratorIterator::SELF_FIRST);
while ($files->valid()) {
    $file = $files->current();
    $filename = $file->getFilename();
    $deep = $files->getDepth();
    $files->next();
    $valid = $files->valid();
    
    if( $dir != "stats" && $dir != "filterExports" && $dir != "sqlFailures" && $dir != "mailFailures" && $dir != "demo"  ){
    
        if( $deep == 0 ){
            ?><div class="a"><?php echo $filename;?></div><?php 
        }elseif( $deep == 1 ){
            ?><div class="b"><?php echo $filename;?></div><?php
        }elseif( $deep == 2 ){
            ?><div class="c"><?php echo $filename;?></div><?php
        }elseif( $deep == 3 ){
            ?><div class="d"><?php echo $filename;?></div><?php
        }elseif( $deep == 4 ){
            ?><div class="e"><?php echo $filename;?></div><?php
        }
    }
}
?>
</html>