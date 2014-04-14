<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>VideoWhisper Live Broadcast</title>
</head>

<body>
<?php

include_once("../../../../wp-config.php");

$stream = $_GET['n'];
include("incsan.php");
sanV($stream);

          	$swfurl = plugin_dir_url(__FILE__) . "live_broadcast.swf?room=" . urlencode($stream);
            $swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
            $swfurl .= '&extension='.urlencode('_none_');
            $swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . '');
            
$bgcolor="#333333";
include("flash_detect.php");
?>
<object width="100%" height="100%">
<param name="movie" value="<?=$swfurl?>"></param><param bgcolor="<?=$bgcolor?>"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed width="100%" height="100%" scale="noscale" salign="lt" src="<?=$swfurl?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" bgcolor="<?=$bgcolor?>"></embed>
</object>
<style type="text/css">
<!--
BODY
{
	margin:0px;
	background: <?=$bgcolor?>;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #EEE;
}

#info
{
	float: right;
	width: 25%;
	position: absolute;
	bottom: 10px;
	right: 10px;
	text-align:left;
	padding: 10px;
	margin: 10px;
	background-color: #666;
	border: 1px dotted #AAA;
	z-index: 1;
	
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#999', endColorstr='#666'); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#999), to(#666)); /* for webkit browsers */
	background: -moz-linear-gradient(top,  #999,  #666); /* for firefox 3.6+ */
	
	box-shadow: 2px 2px 2px #333;


	-moz-border-radius: 9px;
	border-radius: 9px;
}

a {
	color: #F77;
	text-decoration: none;
}

.button {
	-moz-box-shadow:inset 0px 1px 0px 0px #f5978e;
	-webkit-box-shadow:inset 0px 1px 0px 0px #f5978e;
	box-shadow:inset 0px 1px 0px 0px #f5978e;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #db4f48), color-stop(1, #944038) );
	background:-moz-linear-gradient( center top, #db4f48 5%, #944038 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#db4f48', endColorstr='#944038');
	background-color:#db4f48;
	border:1px solid #d02718;
	display:inline-block;
	color:#ffffff;
	font-family:Verdana;
	font-size:12px;
	font-weight:normal;
	font-style:normal;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #810e05;
	padding: 5px;
	margin: 2px;
}
.button:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #944038), color-stop(1, #db4f48) );
	background:-moz-linear-gradient( center top, #944038 5%, #db4f48 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#944038', endColorstr='#db4f48');
	background-color:#944038;
}
	
-->
</style>

<?php
if ($stream)
{
$options = get_option('VWliveStreamingOptions');

$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

//if any key matches any listing
function inList($keys, $data)
{
	if (!$keys) return 0;

	$list=explode(",", strtolower(trim($data)));

	foreach ($keys as $key)
		foreach ($list as $listing)
			if ( strtolower(trim($key)) == trim($listing) ) return 1;

			return 0;
}

//username
if ($current_user->$userName) $username=urlencode($current_user->$userName);
sanV($username);

//access keys
if ($current_user)
{
	$userkeys = $current_user->roles;
	$userkeys[] = $current_user->user_login;
	$userkeys[] = $current_user->ID;
	$userkeys[] = $current_user->user_email;
	$userkeys[] = $current_user->display_name;
}

if (inList($userkeys, $options['premiumList'])) //premium broadcasters can transcode
if ($options['transcoding'])
{
?>

<div id="info">
iOS Transcoding (iPhone/iPad)<BR>
<a href='#' class="button" id="transcoderon">ON</a>
<a href='#' class="button" id="transcoderoff">OFF</a>
<div id="result">A stream must be broadcast for transcoder to start.</div>
<p align="right">(<a href="javascript:void(0)" onClick="info.style.display='none';">hide</a>)</p>
</div>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
	$.ajaxSetup ({
		cache: false
	});
	var ajax_load = "Loading...";
	
	$("#transcoderon").click(function(){
		$("#result").html(ajax_load).load("../../../../wp-admin/admin-ajax.php?action=vwls_trans&task=mp4&stream=<?php echo $stream; ?>");
	});
	
	$("#transcoderoff").click(function(){
	$("#result").html(ajax_load).load("../../../../wp-admin/admin-ajax.php?action=vwls_trans&task=close&stream=<?php echo $stream; ?>");
	});
</script>
<?php
}
}
?>
</body>
</html>
