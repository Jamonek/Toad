<?php

class Net_SmartIRC_module_part
{
	var $name = array(name => 'part', access => '2', from => 'channel');
	var $version = '$Revision$';
	var $description = 'Parts channel.';
	var $author = 'Monie';
	var $license = 'GPL';
	var $access = '2';
	var $actionids = array();

	function module_init(&$irc)
	{
		$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%part', $this, 'part');
		$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^part', $this, 'part');
	}

	function module_exit(&$irc)
	{
	foreach ($this->actionids as $value) {
		$irc->unregisterActionid($value);
		}
	}

function part(&$irc, &$data)
{ 
 
 global $toad;
 
if(($toad->users[$data->nick]['loggedin'] == true) && (($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_ADMIN))) {
  
 if(!$data->messageex[1]) {
 $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You did not provide a channel to part." );
 } else {
 $channel = $data->messageex[1];
 }
 
 $irc->part($channel);
 
 } else {
 $irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You don't have access to this command." );
 }
 }
 
 }
 
 ?>