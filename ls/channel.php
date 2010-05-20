<?php
$n=$_GET["n"];

$swfurl="live_watch.swf?n=".urlencode($n);
?><style type="text/css">
<!--
body {
	background-color: #000;
}
-->
</style><title><?=$n?> Live Video Streaming</title><body bgcolor="#C0C0C0" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"><table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center" bgcolor="#000000"><p><font color="#FFFFFF" face="Arial, Helvetica, sans-serif"><strong>You are watching:
        <?=strip_tags($n)?>
        </strong></font><br>
        <font color="#FFFFFF" face="Arial, Helvetica, sans-serif">Channel Demo Page - Edit channel.php to change this</font></p>
      <p><strong><br />
      </strong></p></td>
  </tr>
  <tr>
    <td height=400 bgcolor="#333333"><object width="100%" height="100%">
      <param name="movie" value="<?=$swfurl?>"></param><param name="scale" value="noscale" /><param name="salign" value="lt"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed width="100%" height="100%" scale="noscale" salign="lt" src="<?=$swfurl?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed>
    </object></td>
  </tr>
  <tr>
    <td align="center" bgcolor="#000000"><p>&nbsp;</p>
      <p><font color="#FFFFFF" face="Arial, Helvetica, sans-serif">The flash workspace above can have any size. Any of the panels can be disabled from vs_login.php .</font>      </p>
      <p><a href="http://www.videowhisper.com/?p=Live+Streaming"><font color="#AA2255" face="Arial, Helvetica, sans-serif">Video Whisper Live Streaming</font></a></p></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
