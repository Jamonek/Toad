<?php

class Net_SmartIRC_module_url
{
	var $name = array(name => 'url', access => '0', from => 'channel');
	var $version = '$Revision$';
	var $description = 'Gets title of a site.';
	var $author = 'Monie';
	var $license = 'GPL';
	var $access = '0';
	var $actionids = array();

	function module_init(&$irc)
	{
		$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'url' );
	}

	function module_exit(&$irc)
	{
	foreach ($this->actionids as $value) {
		$irc->unregisterActionid($value);
		}
	}




// url

function url(&$irc, &$data) 
{

	global $toad;
	
	$query = implode(' ', array_slice($data->messageex, 1));
	
	
	$file = fopen($query, 'r');
	
	$page = stream_get_contents($file, 1024);
	
	if(!$page) {
	//$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$bot->prefix}url\x02: Couldn't get title of \"$query\"." );
	fclose($page);
	return;
	}
	//print_r("$page\n");
	
	preg_match('/<title>(.*?)<\/title>/i', $page, $match);
	
	if(!$match) {
	//$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$bot->prefix}url\x02: Couldn't get title of \"$query\"." );
	return;
	}
	
	//$match = str_replace('&gt;', '>', $match);
	//$match = str_replace('&lt;', '<', $match);
	//$match = str_replace('&raquo;', 'È', $match);
	
$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Title for $query: ".htmlspecialchars_decode($match[1])."" );
fclose($file);
}

}
?>
