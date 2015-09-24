<?php

class Net_SmartIRC_module_urban {

	var $name = array(name => 'urban', access => '0', from => 'channel');
	var $version = '$Revision$';
	var $description = 'Urbandictionary.com lookup.';
	var $author = 'Monie';
	var $license = 'GPL';
	var $access = '0';
	var $actionids = array();


	function module_init(&$irc)
	{
		$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%urban/', $this, 'urban');
	}

	function module_exit(&$irc)
	{
	foreach ($this->actionids as $value) {
		$irc->unregisterActionid($value);
		}
	}
	
	
	function urban(&$irc, &$data) {
	
	global $toad;
	
    
    $q = implode(' ', array_slice($data->messageex, 1));	
    
    if(empty($q)) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Urban\x02: Syntax error, type %help urban.");
    return;
    }
    
    		$ch = curl_init();
          	curl_setopt($ch, CURLOPT_URL, "http://www.urbandictionary.com/define.php?term=".urlencode($q)."");
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, '15');
			$page = curl_exec($ch) or die(curl_error());
			curl_close($ch);

    if(empty($page)) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Urban\x02: Couldn't get definition.1");
    return;
    }
    
    //$page = str_replace("\n", "", $page);
    preg_match('/<td class=\'word\'>(.*)<\/td>/i', $page, $title);
    //preg_match('/<td class="def_word">(.+?)<\/td>/', $page, $title);
    preg_match("/<div class='definition'>(.*)<br\/>/", $page, $def);
    //preg_match("/<div class=\"def_p\">\s*<p>(.+?)<\/p>/",$page, $def);
    //preg_match('/<td nowrap><span id=".+"><strong>(.+?)<\/strong> up, <strong>(.+?)<\/strong> down<\/span><\/td>/iU',$page, $vote);
    //$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $page);
    $title[1] = str_replace('&quot;', '"', $title[1]);
    $def[1] = str_replace('&quot;', '"', $def[1]);
    $def[1] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $def[1]);
    
    if(empty($title[1])) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Urban\x02: Couldn't get definition.2");
    //return;
    }
    if(empty($def[1])) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Urban\x02: Couldn't get definition.3");
    //return;
    }
  /*  if(empty($vote[1])) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Urban\x02: Couldn't get definition.");
    return;
    }
    if(empty($vote[2])) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Urban\x02: Couldn't get definition.");
    return;
    }*/
    
    if($data->channel == strtolower('#oneclickwifi')) {
    $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Urban Dictionary\x02: [Word]: {$title[1]}");
    $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "[Definition]: ".strip_tags($def[1]));
    } else {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Urban Dictionary\x02: [Word]: {$title[1]}");
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "[Definition]: ".strip_tags($def[1]));
  }
    
    }
	
	}
	?>
	
	
	