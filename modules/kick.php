<?php

class Net_SmartIRC_module_kick {

var $name = array(name =>'kick', access => array('op', '3'), from => 'channel');
var $version = '$Revision$';
var $description = 'Kicks users out channels.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '3';

var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%kick', $this, 'kick');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

function kick(&$irc, &$data)
{
global $toad;

if($toad->is_disabled($irc->_network[1][9], $data->channel, 'kick') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}kick\x02: This command is disabled for \x02{$data->channel}\x02.");
				return;
			}	

$nick = $data->messageex[1];
$reason = implode(' ', array_slice($data->messageex, 2));

if(empty($nick)) {
$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a user to kick." );
return;
}

if(!$reason) {
$reason = 'Requested';
}

if($irc->isOpped($data->channel))  {
	if(($irc->isOpped($data->channel, $data->nick)) or ($irc->isOwnered($data->channel, $data->nick)) or ($irc->isAdmined($data->channel, $data->nick)) or ($irc->isHalfoped($data->channel, $data->nick)) or (($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] >= TOAD_RANK_ADMIN))) {
		if($irc->isJoined($data->channel, $nick)) {
		if(strtolower($nick) == strtolower($irc->_nick)) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02$data->nick\x02: I will not kick myself." );
		return;
		}
		
		$irc->kick($data->channel, $nick, $reason);
		} else {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: {$nick} isn't in the channel." );
		return;
		}
		} else {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: You do not have access in {$data->channel}." );
		return;
		}
		} else {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: I am not opped in {$data->channel}." );
		return;
		}
		
}

}

?>