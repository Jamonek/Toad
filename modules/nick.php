<?php
class Net_SmartIRC_module_nick
{

var $name = array(name =>'nick', access => '1', from => 'channel');
var $version = '$Revision$';
var $description = 'Gets the weather.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '1';

var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%nick', $this, 'nick');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}


function nick(&$irc, &$data) {
	global $toad;

if(($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER)) {
if(!$data->messageex[1]) {
$irc->changeNick('Toad');
$irc->message( SMARTIRC_TYPE_QUERY, 'nickserv', 'identify '.$toad->_NickServPass.'' );
}
if(strtolower($irc->_nick) == strtolower($data->messageex[1])) {
$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, '\x02Error\x02: That already is my nick you tit.' );
return;
}
$irc->changeNick($data->messageex[1]);
} else {
$irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You don't have access to this command, {$data->nick}." );
return;
}
}

}

?>