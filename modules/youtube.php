<?php

class Net_SmartIRC_module_youtube {
var $name = array(name => 'youtube', access => '0', from => 'channel');
var $version = '$Revision$';
var $description = 'Youtubes videos.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '0';
var $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%youtube', $this, 'youtube');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

// youtube search
function youtube(&$irc, &$data) {

global $toad;

    $q = implode(' ', array_slice($data->messageex, 1));
    
    if(!$q) {
    $irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$toad->prefix}youtube\x02: You didn't provide a query." );
    return;
    }
   /* 
   $pag = fopen("http://youtube.com/results?search_query=".urlencode($q)."&search=", 'r');
    if(!$pag) {
    $irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't connect to youtube.com" );
    return;
    }
    */
	$page = file_get_contents("http://youtube.com/results?search_query=".urlencode($q)."&search=");
	//$page = stream_get_contents($pag, 1804, 10);
	$vids = explode("<!-- end vEntry -->", str_replace("\x09", "", $page));
	$vid_data = null;
	foreach($vids as $vid){
		ereg("<a class=\"newvtitlelink\" href=\"\\/watch\\?v=([[:print:]]+)\" rel=\"nofollow\" onclick=\"([[:print:]]+)\">([[:print:]]+)</a><br/>\n", $vid, $urls);
		$count = count($vid_data);
		$vid_data[$count] = array($urls[1], html_entity_decode(ereg_replace("(<b>|</b>)", "", $urls[3])));
	}
	unset($vid_data[$count]);
	if($vid_data){
		$x = 0;
		$result = null;
		foreach($vid_data as $datab){
			$x++;
			if($x <= 3){
				$result .= "\x0314[ $x: \x0304$datab[1] \x0314- \x0304http://youtube.com/watch?v=$datab[0]\x0314 ]";
			}
		}
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "$result" );
	}else{
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0314No results for \x0304$q\x0304." );
	}
  }
  
}

?>