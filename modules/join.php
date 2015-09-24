<?php

class Net_SmartIRC_module_join
{
	var $name = array(name => 'join', access => '2', from => 'channel');
	var $version = '$Revision$';
	var $description = 'Joins certain channels.';
	var $author = 'Monie';
	var $license = 'GPL';
	var $access = '2';
	var $actionids = array();

	function module_init(&$irc)
	{
	$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%join', $this, 'join');
	$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^join', $this, 'join');
	}

	function module_exit(&$irc)
	{
	foreach ($this->actionids as $value) {
			$irc->unregisterActionid($value);
		}
	}

function join(&$irc, &$data) 
{

 global $toad;
 
if(($toad->users[$data->nick]['loggedin'] == true) && (($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_ADMIN))) {
 if(empty($data->messageex[1])) {
 $irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You did not provide a channel to join." );
 } 
 $channel = $data->messageex[1];
 if($channel == '#0,0') {
 $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: I will not join #0,0.");
 return;
 }
 $irc->join($channel);

} else {
$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You don't have access to this command." );

}
}

}

?>