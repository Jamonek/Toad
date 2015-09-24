<?php

class Net_SmartIRC_module_say
{
var $name = array(name => 'say', access => '2', from => 'channel');
var $version = '$Revision$';
var $description = 'Says stuff to a channel.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '2';
var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^say', $this, 'say');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

 function say(&$irc, &$data) {
 
 	global $toad;
 
 if(($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER)) {
 $channel = $data->messageex[1];
 $message = implode(' ', array_slice($data->messageex, 2));
 if(!isset($channel)) {
 $irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You did not provide a channel." );
 return;
 }
 if(!isset($message)) {
 $irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You did not provide a message." );
 return;
 }
 $irc->message( SMARTIRC_TYPE_QUERY, $channel, "$message" );
 } else {
 $irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You don't have access to this command." );
 return;
 }
 }
 
 }
 
 ?>