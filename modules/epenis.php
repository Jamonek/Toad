<?php

class Net_SmartIRC_module_epenis {

var $name = array(name => 'epenis', access => '0', from => 'channel');
var $version = '$Revision$';
var $description = 'Says your epenis size.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '0';
var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%epenis/', $this, 'epenis');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

function epenis(&$irc, &$data) {
	global $toad;
	
	if($toad->is_disabled($irc->_network[1][9], $data->channel, 'epenis') == true)
	{
		$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}epenis\x02: This command is disabled for \x02{$data->channel}\x02.");
		return;
	}
	
	$x = rand(1, 15);
	
	$toad->privmsg($data->channel, "\x02{$data->nick}\x02: Your epenis is this big, 8".str_repeat('=', $x)."D");

}
}
?>