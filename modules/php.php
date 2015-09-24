<?php

class Net_SmartIRC_module_php {

var $name = array(name => 'php', access => '0', from => 'channel');
var $version = '$Revision$';
var $description = 'PHP.net function lookup.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '0';
var $actionids = array();
public $error;

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%php/', $this, 'php');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

function php(&$irc, &$data) {

	global $toad;
	
		if($toad->is_disabled($irc->_network[1][9], $data->channel, 'php') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}php\x02: This command is disabled for \x02{$data->channel}\x02.");
				return;
			}	
	
	$query = implode(' ', array_slice($data->messageex, 1));
	$query = strtolower($query);
	
	if(!$query) {
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a function to look up." );
	return;
	}
	
	$query = str_replace('_', '-', $query);
			$ch = curl_init();
          	curl_setopt($ch, CURLOPT_URL, "http://us2.php.net/manual/en/function.".urlencode($query).".php");
          	curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, '15');
			$host = curl_exec($ch);
			$this->error = curl_error($ch);
			curl_close($ch);
			
			//$host = file_get_contents("http://us2.php.net/".urlencode($query));
	
	
	if(!isset($host)) {
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: No matches were found." );
	return;
	}
	
	
	preg_match('/<div class="refentry" lang="en"><a name="function.(.+)"><\/a><div class="refnamediv"><h1>(.+)<\/h1><p>(.+)<\/p><p>(.+)<\/p><\/div><h2>Description<\/h2>(.+?) <b>(.+)<\/b>(.+)/i',$host, $php);

	
	$array = array(
	'&amp;',
	'<br>',
	'<p>',
	);
	
	$php = str_replace($array, '', $php);
	$php[2] = str_replace('_','-', $php[2]);
	$php = str_replace('&gt;', '>', $php);


	if($php) {
	if(!$php[6]) {
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02PHP\x02: No matches were found." );
 	return;
 	}
 	
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02PHP\x02 - $php[6] $php[3]" );
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02PHP\x02 - ".strip_tags($php[4])."" );
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02PHP\x02 - Description: ".strip_tags($php[5])." \x02".strip_tags($php[6])."\x02 ".strip_tags($php[7])."" );
	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02PHP\x02 - URL: http://php.net/$php[2]" );
 	} else {
 	$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02PHP\x02: No matches were found." );
 	return;
 	}
}

}
?>