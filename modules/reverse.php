<?php
class Net_SmartIRC_module_reverse
{
var $name = array(name => 'reverse', access => '0', from => 'channel');
var $version = '$Revision$';
var $description = 'Reverses users text.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '0';

var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%reverse', $this, 'reverse');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

function reverse(&$irc, &$data) {

global $toad;

	if($toad->is_disabled($irc->_network[1][9], $data->channel, 'reverse') == true)
	{
		$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}reverse\x02: This command is disabled for \x02{$data->channel}\x02.");
		return;
	}	

$message = implode(' ', array_slice($data->messageex, 1));

if(!$message) {

$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide text to reverse." );
return;

}

$reverse = strrev($message);

if(strtolower($reverse) == strtolower($message))
{
$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Reverse\x02: You tit, it's the same thing reversed.");
return;
}

$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Reversed\x02: $reverse" );

}

}

?>