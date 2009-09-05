<?
if ($_GET["room"]) $room=$_GET["room"];
if ($_POST["room"]) $room=$_POST["room"];
?>
<?
$ext=strtolower(substr($_FILES['vw_file']['name'],-4));
$allowed=array(".swf",".zip",".rar",".jpg","jpeg",".png",".gif",".txt",".doc","docx",".htm","html",".pdf");

if (in_array($ext,$allowed)) move_uploaded_file($_FILES['vw_file']['tmp_name'], "uploads/".$room."/".$_FILES['vw_file']['name']);
?>
