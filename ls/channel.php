<head>
<?php
$n=$_GET["n"];

$swfurl="live_watch.swf?n=".urlencode($n);
$bgcolor="#333333";
?><style type="text/css">
<!--
body {
	font-family: Arial, Helvetica, sans-serif;
	background-color: #000;
	font-size: 15px;
	color: #EEE;
}

a
{
	color: #FF6699;
	font-weight: normal;
	text-decoration: none;
}
-->
</style><title><?=$n?> Live Video Streaming</title>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
include("flash_detect.php");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center" bgcolor="#000000"><p><strong>You are watching:
        <?=strip_tags($n)?>
        </strong><br>
        Channel Demo Page - Edit channel.php to change this</p>
      <p><strong><br />
      </strong></p></td>
  </tr>
  <tr>
    <td height=400 bgcolor="#333333">
	
	<object width="100%" height="100%">
      <param name="movie" value="<?=$swfurl?>"></param><param bgcolor="<?=$bgcolor?>" /><param name="scale" value="noscale" /><param name="salign" value="lt"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed width="100%" height="100%" scale="noscale" salign="lt" src="<?=$swfurl?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" bgcolor="<?=$bgcolor?>></embed>
    </object>
	
	</td>
  </tr>
  <tr>
    <td align="center" bgcolor="#000000"><p>&nbsp;</p>
      <p><font color="#FFFFFF" face="Arial, Helvetica, sans-serif">The flash workspace above can have any size. Any of the panels can be disabled from vs_login.php .
	  <BR>You can also embed just <a href="video.php?n=<?=$n?>">plain video</a> or send mobile users without flash to a <a href="htmlchat.php?n=<?=$n?>">plain html external text chat interface</a>. </font></p>
      <p><a href="http://www.videowhisper.com/?p=Live+Streaming">Video Whisper Live Streaming</a></p></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
