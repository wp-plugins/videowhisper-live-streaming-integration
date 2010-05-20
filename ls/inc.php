<?php
include("../../../../wp-config.php");
$options = get_option('VWliveStreamingOptions');
$rtmp_server = $options['rtmp_server'];
$rtmp_amf = 'AMF3';

?>