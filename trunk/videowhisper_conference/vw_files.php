<files>
<?
$dir="uploads";
$dir.="/$room";
if (!file_exists($dir)) mkdir($dir);
$handle=opendir($dir);
while 
(($file = readdir($handle))!==false) 
{
if (($file != ".") && ($file != "..") && (!is_dir("$dir/".$file))) echo "<file file_name=\"".$file."\" file_size=\"".filesize("$dir/".$file)."\" />";
}
closedir($handle); 
?>
</files>