<?
//echo "INSERT INTO `#__vw_rooms` (`name` ,`details` ,`type` ,`owner` ,`capacity` ,`lobby` ,`rank` ,`expires`) VALUES ('".JRequest::getVar( 'rname' )."', '".JRequest::getVar( 'rdescription' )."', '1', '$username', '".JRequest::getVar( 'rcapacity' )."', '', '0', '".(JRequest::getVar( 'rexpires' )*3600*24)."')"; 
?>