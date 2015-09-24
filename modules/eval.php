<?php

class Net_SmartIRC_module_eval {
var $name = array(name => 'eval', access => '1', from => 'channel');
var $version = '$Revision$';
var $description = 'Eval.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '1';
var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%eval', $this, 'meval');
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^eval', $this, 'meval');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

// eval
function meval(&$irc, &$data) {

	global $toad;
	
	if(($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER)) {
	
	$message = implode(' ', array_slice($data->messageex, 1));
	
	if(!$message) {
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You did not provide any text." );
	return;
	}
	
	$result = eval($message);
	$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $result);
	
	} else {
	
	$irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You don't have access to this command." );
	return;
	}
	
  }	

}

?>