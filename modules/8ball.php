<?php

class Net_SmartIRC_module_8ball {

var $name = array(name => '8ball', access => '0', from => 'channel');
var $version = '$Revision$';
var $description = 'Ask the magic 8ball questions.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '0';
var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%8ball/', $this, 'eightball');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

function eightball(&$irc, &$data) {

	global $toad;
	
	if($toad->is_disabled($irc->_network[1][9], $data->channel, '8ball') == true)
	{
		$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}8ball\x02: This command is disabled for \x02{$data->channel}\x02.");
		return;
	}	
	
 if ((isset($data->messageex[1])) && (substr($data->messageex[count($data->messageex) - 1], -1, 1) == '?')) {
 if(count($data->messageex) <= 2) {
 $irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x028ball\x02: Question wasn't long enough." );
 return;
 }
		$replies = array(
			'Signs point to yes',
			'Yes',
			'Most likely',
			'Without a doubt',
			'Yes - definitely',
			'As I see it, yes',
			'You may rely on it',
			'Outlook good',
			'It is certain',
			'It is decidedly so',
			'Reply hazy, try again',
			'Better not tell you now',
			'Ask again later',
			'Concentrate and ask again',
			'Cannot predict now',
			'My sources say no',
			'Very doubtful',
			'My reply is no',
			'Outlook not so good',
			'Don\'t count on it'
		);
		$reply = $replies[mt_rand(0, count($replies) - 1)];
		$irc->message( SMARTIRC_TYPE_ACTION, $data->channel, "Shakes up the magic 8ball" );
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "The 8ball says: {$reply}" );
	} else {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "8ball: You did not ask a question." );
	}
 
 }

}

?>