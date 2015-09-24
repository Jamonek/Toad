<?php
  class Net_SmartIRC_module_spy
  {
      var $name = array(name => 'spy', access => '3', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Spys on a channel.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '3';
      var $channel;
      var $to;
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%spy/', $this, 'spying');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function spying(&$irc, &$data)
      {
          global $toad;
          
          	if($toad->is_disabled($irc->_network[1][9], $data->channel, 'spy') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}spy\x02: This command is disabled for \x02{$data->channel}\x02.");
				return;
			}	
	
          /*
          if($bot->_isIgnored($data->host) == true) {
$bot->notice($data->nick, "\x02{$data->nick}\x02: You are not allowed to access \037\x02{$irc->_nick}\x02\037");
return;
}*/
          
          //if((get_host($data->host) == true) && (get_level($data->host) >= USER_LEVEL_OPERATOR)) {
          //if (($bot->_hasPriv($data, '3')) or ($bot->_hasPriv($data, '2')) or ($bot->_hasPriv($data, '1')) /*or ($irc->isOpped($data->channel, $data->nick))*/) {
              if(($toad->users[$data->nick]['loggedin'] == true) && (($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_ADMIN))) {
              if (!isset($data->messageex[1])) {
              if(!isset($this->channel)) {
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: I was not spying on any channels." );
              return;
              }
               $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: Spying on \x02{$this->channel}\x02 has been terminated." );
                  unset($this->channel);
                  unset($this->to);
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'spy');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'spy_join');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_PART, '.*', $this, 'spy_part');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_ACTION, '.*', $this, 'spy_act');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_QUIT, '.*', $this, 'spy_quit');
                  //$irc->unregisterActionhandler(SMARTIRC_TYPE_KICK, '.*', $this, 'mimic_kick' );
                  return;
              } else {
                  if ($irc->isJoined($data->messageex[1]) == false) {
                  		//$irc->join(array($data->messageex[1]));
                      //$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: I am currently not in \x02{$data->messageex[1]}\x02, Now joining...");
                      	$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: I am currently not in \x02{$data->messageex[1]}\x02.");
                      	// Check if bot is banned...
                      	/*
                      		if ($irc->isJoined($data->messageex[1]) == false) {
                      			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: I'm possibly banned from \x02{$data->messageex[1]}\x02");
                      			return;
                      		}	
                      	*/	
                      return;
                  }
                  if($this->channel) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: There is currently a channel already being spyed on, type %spy." );
                  return;
                  }
                  $this->channel = strtolower($data->messageex[1]);
                  if($this->channel == strtolower($data->channel)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "I will not spy on the same channel." );
                  unset($this->channel);
                  return;
                  }
                  $this->to = $data->channel;
                  $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'spy');
                  $irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'spy_join');
                  $irc->registerActionhandler(SMARTIRC_TYPE_PART, '.*', $this, 'spy_part');
                  $irc->registerActionhandler(SMARTIRC_TYPE_ACTION, '.*', $this, 'spy_act');
                  $irc->registerActionhandler(SMARTIRC_TYPE_QUIT, '.*', $this, 'spy_quit' );
                //  $irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $this, 'mimic_kick' );
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: Now spying on \x02{$data->messageex[1]}\x02.");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Channel Modes\x02: +".$irc->_channels[''.strtolower($data->messageex[1]).'']->mode.".");
                  if(empty($irc->_channels[strtolower($data->messageex[1])]->topic))
                  {
                  	
                  	$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Topic\x02: No topic set.");
                  	
                  	} else {
                  	
                  	$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Topic\x02: ".$irc->_channels[''.strtolower($data->messageex[1]).'']->topic.".");
                  	
                  }
            /*
            $channel = strtolower($this->channel);

			$userlist = '';
			$i;
		foreach ($irc->channel[$channel]->users as $key => $value) {
		$i++;
		 $userlist .= ' '.$value->nick;
		 }
		 $names = explode(' ', $userlist);
		sort($names);
		$ulist = implode(' ',$names);

  $irc->message( SMARTIRC_TYPE_CHANNEL, $this->to, "\x02$i\x02 users on $this->channel:$ulist" );
  */
            }
          } else {
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You don't have access to this command.");
              return;
          }
      }
      
      
      
      function spy(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          $message = '';
          for ($i = 0; $i < count($data->messageex); $i++) {
              $message .= ' ' . $data->messageex[$i];
          }
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "<{$data->nick}> " . trim($message) . "");
          }
      }
      
      function spy_join(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "\x02{$data->nick}\x02 has joined {$this->channel}");
          }
      }
      
      function spy_part(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "\x02{$data->nick}\x02 has left {$this->channel}");
          }
      }
      
      function spy_act(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          $message = '';
          for ($i = 1; $i < count($data->messageex); $i++) {
              $message .= ' ' . $data->messageex[$i];
          }
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $message= str_replace(chr(1),"",$message);
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "* {$data->nick} " . trim($message) . "");
          }
      }
      
            function spy_quit(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          $message = '';
          for ($i = 1; $i < count($data->messageex); $i++) {
              $message .= ' ' . $data->messageex[$i];
          }
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "{$data->nick} has quit" . trim($message) . "");
          }
      }
      
             function spy_kick(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          $message = '';
          for ($i = 1; $i < count($data->messageex); $i++) {
              $message .= ' ' . $data->messageex[$i];
          }
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "{$data->nick} has kicked" . trim($message) . "");
          }
      }
  }
?>