<?
if ($_POST["room"]) $room=$_POST["room"];
if ($_GET["room"]) $room=$_GET["room"];
unlink("uploads/".$room."/".$_GET["filename"]);
?>
