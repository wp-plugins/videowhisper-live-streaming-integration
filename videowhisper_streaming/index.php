<?
if (!$_POST["username"]||$_POST["username"]=="Studio") $username="Studio".rand(100,999);
else $username=$_POST["username"];
setcookie("username",urlencode($username),time()+3600);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Video Whisper Live Broadcast</title>
<script language="javascript">AC_FL_RunContent = 0;</script>
<script src="AC_RunActiveContent.js" language="javascript"></script>
</head>
<body bgcolor="#333333">
<script language="javascript">
	if (AC_FL_RunContent == 0) {
		alert("This page requires AC_RunActiveContent.js.");
	} else {
		AC_FL_RunContent(
			'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0',
			'width', '100%',
			'height', '100%',
			'src', 'live_broadcast',
			'quality', 'high',
			'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
			'align', 'middle',
			'play', 'true',
			'loop', 'true',
			'scale', 'noscale',
			'wmode', 'window',
			'devicefont', 'false',
			'id', 'live_broadcast',
			'bgcolor', '#333333',
			'name', 'live_broadcast',
			'menu', 'true',
			'allowFullScreen', 'true',
			'allowScriptAccess','sameDomain',
			'movie', 'live_broadcast',
			'salign', 'lt'
			); //end AC code
	}
</script>
<noscript>
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="800" height="420" id="live_broadcast" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="true" />
	<param name="movie" value="live_broadcast.swf" /><param name="quality" value="high" /><param name="scale" value="noscale" /><param name="salign" value="lt" /><param name="bgcolor" value="#333333" />	<embed src="live_broadcast.swf" quality="high" scale="noscale" salign="lt" bgcolor="#333333" width="100%" height="100%" name="live_broadcast" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>
</noscript>
</body>
</html>
