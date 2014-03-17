<head>
<?php
include("incsan.php");
sanV($n);

include_once("../../../../wp-config.php");

$stream=$_GET["n"];
$stream = sanitize_file_name($stream);

            $swfurl = plugin_dir_url(__FILE__) . "live_watch.swf?n=" . urlencode($stream);
            $swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
            $swfurl .= '&extension='.urlencode('_none_');
            $swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . '');



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
  <td height="400px" bgcolor="#333333">
	<object width="100%" height="100%">
      <param name="movie" value="<?=$swfurl?>"></param><param bgcolor="<?=$bgcolor?>" /><param name="scale" value="noscale" /><param name="salign" value="lt"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed width="100%" height="100%" scale="noscale" salign="lt" src="<?=$swfurl?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" bgcolor="<?=$bgcolor?>></embed>
    </object>
	</td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
