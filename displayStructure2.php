<!doctype html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <title>Directory Contents</title>
  <link rel="stylesheet" href="./copysource/css/display.css">
  <script src="./copysource/js/sorttable.js"></script>
</head>

<body>
<?php 

if( !isset($_REQUEST['workingDirectory']) ){
    $workingDirectory =".";
}else{
    $workingDirectory = $_REQUEST['workingDirectory'];
}

if( !isset( $_REQUEST['directory'] ) || $_REQUEST['directory'] == "" || $_REQUEST['directory'] =="home"){
    $path = ".";
}else{
    
    $workingDirectory = $workingDirectory . "/" . $_REQUEST['directory'];
    chdir($workingDirectory);
    if( isset( $_REQUEST['directory']) ){
        $directory = $_REQUEST['directory'];
    }
    $path = ".";
    $showHome = true;
    
    
}


?>
  <div id="container">
  
    <h1>LPS Source Code</h1>
    <?php 
    if( isset( $showHome ) && $showHome == true ){
    ?>
    <h2 style="text-align:left;"><a href='./displayStructure2.php'><img src='../../../../images/home.png' width="45" height="45"></a></h2>
    <?php 
    }
    ?>
    <table class="sortable" border='1'>
      <thead>
        <tr>
          <th>Filename</th>
          <th>Size <small>(bytes)</small></th>
          <th>Date Modified</th>
        </tr>
      </thead>
      <tbody>
      <?php

   
        $myDirectory=opendir( $path );

        // Gets each entry
        while($entryName=readdir($myDirectory)) {
          $dirArray[]=$entryName;
        }

        // Finds extensions of files
        function findexts ($filename) {
          $filename=strtolower($filename);
      
          $exts=explode("[/\\.]", $filename);
          $n=count($exts)-1;
          $exts=$exts[$n];
          return $exts;
        }
        
        // Closes directory
        closedir($myDirectory);
        
        // Counts elements in array
        $indexCount=count($dirArray);
        
        // Sorts files
        sort($dirArray);

        // Loops through the array of files
        for($index=0; $index < $indexCount; $index++) {

          // Allows ./?hidden to show hidden files
          if($_SERVER['QUERY_STRING']=="hidden")
          {$hide="";
          $ahref="./";
          $atext="Hide";}
          else
          {$hide=".";
          $ahref="./?hidden";
          $atext="Show";}
              if(substr("$dirArray[$index]", 0, 1) != $hide) {
              
              // Gets File Names
              $name=$dirArray[$index];
              $namehref=$dirArray[$index];
              
              // Gets Extensions 
              $extn=findexts($dirArray[$index]); 
              
              // Gets file size 
              $size=number_format(filesize($dirArray[$index]));
              
              // Gets Date Modified Data
              $modtime=date("M j Y g:i A", filemtime($dirArray[$index]));
              $timekey=date("YmdHis", filemtime($dirArray[$index]));
              
              // Prettifies File Types, add more to suit your needs.
              switch ($extn){
                case "png": $extn="PNG Image"; break;
                case "jpg": $extn="JPEG Image"; break;
                case "svg": $extn="SVG Image"; break;
                case "gif": $extn="GIF Image"; break;
                case "ico": $extn="Windows Icon"; break;
                
                case "txt": $extn="Text File"; break;
                case "log": $extn="Log File"; break;
                case "htm": $extn="HTML File"; break;
                case "php": $extn="PHP Script"; break;
                case "js": $extn="Javascript"; break;
                case "css": $extn="Stylesheet"; break;
                case "pdf": $extn="PDF Document"; break;
                
                case "zip": $extn="ZIP Archive"; break;
                case "bak": $extn="Backup File"; break;
                
                default: $extn=strtoupper($extn)." File"; break;
              }

              // Separates directories
              if(is_dir($dirArray[$index])) {
                $extn="&lt;Directory&gt;"; 
                $size="&lt;Directory&gt;"; 
                $class="dir";
              } else {
                $class="file";
              }
              
              // Cleans up . and .. directories 
              if($name=="."){$name=". (Current Directory)"; $extn="&lt;System Dir&gt;";}
              if($name==".."){$name=".. (Parent Directory)"; $extn="&lt;System Dir&gt;";}
              
              $filePath =  "/" . $name;

              
              if( $class == "dir" ){
                  $imagePath = "../../../../images/folder.png";
                  $srcName = "./displayStructure2.php?directory=$name&workingDirectory=$workingDirectory";
                  
              }elseif( $class == "file" ){
                  $imagePath = "../../../../images/file.png";
                  $srcName = "./displayFile.php?directory=$workingDirectory&file=$filePath";
              }
              
              // Print 'em
              if( ( $name == "stats" || $name == "sqlFailures" || $name == "mailFailures" 
                    || $name == "demo" || $name == "filterExports" ) && $size == "&lt;Directory&gt;"){
                  
              }elseif( $name == "php_errors" ){
                  
              }else{
                print("
                  <tr class='$class'>
                    <td><a href='$srcName'><img src='$imagePath' width='40' height='40'>$name</a></td>
                    <td>$size</td>
                    <td sorttable_customkey='$timekey'>$modtime</td>
                  </tr>");
              }
          }
        }
      ?>
      </tbody>
    </table>
  </div>
  
</body>

</html>