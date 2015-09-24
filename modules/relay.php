<?php
  class Net_SmartIRC_module_relay
  {
      var $name = array(name => 'relay', access => '3', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Relays messages between channels.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '3';
      var $channel;
      var $to;
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%relay/', $this, 'relaying');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function relaying(&$irc, &$data)
      {
          global $toad;
          
          	if($toad->is_disabled($irc->_network[1][9], $data->channel, 'relay') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}relay\x02: This command is disabled for \x02{$data->channel}\x02.");
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
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Relay\x02: I was not relaying any channels." );
              return;
              }
               $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Relay\x02: Relaying on \x02{$this->channel}\x02 has been terminated." );
               $irc->message(SMARTIRC_TYPE_CHANNEL, $this->channel, "\x02Relay\x02: Relaying on \x02{$this->to}\x02 has been terminated.");
                  unset($this->channel);
                  unset($this->to);
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'relay');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'relay_join');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_PART, '.*', $this, 'relay_part');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_ACTION, '.*', $this, 'relay_act');
                  $irc->unregisterActionhandler(SMARTIRC_TYPE_QUIT, '.*', $this, 'relay_quit');
                  //$irc->unregisterActionhandler(SMARTIRC_TYPE_KICK, '.*', $this, 'mimic_kick' );
                  return;
              } else {
                  if ($irc->isJoined($data->messageex[1]) == false) {
                  		//$irc->join(array($data->messageex[1]));
                      //$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Relay\x02: I am currently not in \x02{$data->messageex[1]}\x02, Now joining...");
                      	$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Spy\x02: I am currently not in \x02{$data->messageex[1]}\x02.");
                      	// Check if bot is banned...
                      	/*
                      		if ($irc->isJoined($data->messageex[1]) == false) {
                      			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Relay\x02: I'm possibly banned from \x02{$data->messageex[1]}\x02");
                      			return;
                      		}
                      		*/
                      return;
                  }
                  if($this->channel) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Relay\x02: There is currently a channel already being relayed, type %relay." );
                  return;
                  }
                  $this->channel = strtolower($data->messageex[1]);
                  if($this->channel == strtolower($data->channel)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "I will not relay to the same channel." );
                  unset($this->channel);
                  return;
                  }
                  $this->to = $data->channel;
                  $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'relay');
                  $irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'relay_join');
                  $irc->registerActionhandler(SMARTIRC_TYPE_PART, '.*', $this, 'relay_part');
                  $irc->registerActionhandler(SMARTIRC_TYPE_ACTION, '.*', $this, 'relay_act');
                  $irc->registerActionhandler(SMARTIRC_TYPE_QUIT, '.*', $this, 'relay_quit' );
                //  $irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $this, 'mimic_kick' );
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Relay\x02: Now linking \x02{$data->messageex[1]}\x02.");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->messageex[1], "\x02Relay\x02: Attempting to link this channel to \x02{$data->channel}\x02.");
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
		
  $irc->message( SMARTIRC_TYPE_CHANNEL, $this->to, "\x02$i\x02 users on {$this->channel}:$ulist" );
  unset($ulist);unset($i);unset($userlist);
  unset($channel);
  $channel = strtolower($this->to);
  $userlist = '';
			$i;
		foreach ($irc->channel[$channel]->users as $key => $value) {
		$i++;
		 $userlist .= ' '.$value->nick;
		 }
		 $names = explode(' ', $userlist);
		sort($names);
		$ulist = implode(' ',$names);
		
		$irc->message( SMARTIRC_TYPE_CHANNEL, $this->channel, "\x02$i\x02 users on {$this->to}:$ulist" );
		*/
		
            }
          } else {
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You don't have access to this command.");
              return;
          }
      }
      
      
      
      function relay(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          $message = '';
          for ($i = 0; $i < count($data->messageex); $i++) {
              $message .= ' ' . $data->messageex[$i];
          }
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "<{$data->nick}/{$this->channel}> " . trim($message) . "");
          } elseif (strtolower($data->channel) == strtolower($this->to)) {
          	$irc->message(SMARTIRC_TYPE_CHANNEL, $this->channel, "<{$data->nick}/{$this->to}> " . trim($message) . "");
          	}
      }
      
      function relay_join(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "\x02{$data->nick}\x02 has joined {$this->channel}");
          } elseif (strtolower($data->channel) == strtolower($this->to)) {
          	$irc->message(SMARTIRC_TYPE_CHANNEL, $this->channel, "\x02{$data->nick}\x02 has joined {$this->to}");
          	}
      }
      
      function relay_part(&$irc, &$data)
      {
          $channel = $data->channel;
          $to = $this->to;
          if ($this->channel == strtolower($data->channel)) {
              $data->channel = $channel;
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "\x02{$data->nick}\x02 has left {$this->channel}");
          } elseif (strtolower($data->channel) == strtolower($this->to)) {
          	$irc->message(SMARTIRC_TYPE_CHANNEL, $this->channel, "\x02{$data->nick}\x02 has left {$this->to}");
          	}
      }
      
      function relay_act(&$irc, &$data)
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
              $irc->message(SMARTIRC_TYPE_CHANNEL, $to, "* {$data->nick}/{$this->channel} " . trim($message) . "");
          } elseif (strtolower($data->channel) == strtolower($this->to)) {
          	$irc->message(SMARTIRC_TYPE_CHANNEL, $this->channel, "* {$data->nick}/{$this->to} " . trim($message) . "");
          	}
      }
      
            function relay_quit(&$irc, &$data)
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
          } elseif (strtolower($data->channel) == strtolower($this->to)) {
          	$irc->message(SMARTIRC_TYPE_CHANNEL, $this->channel, "{$data->nick} has quit" . trim($message) . "");
          	}
      }
      
             function relay_kick(&$irc, &$data)
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
          } elseif (strtolower($data->channel) == strtolower($this->to)) {
          	$irc->message(SMARTIRC_TYPE_CHANNEL, $this->channel, "{$data->nick} has kicked" . trim($message) . "");
          	}
      }
  }
?>