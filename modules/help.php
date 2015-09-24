<?php
  class Net_SmartIRC_module_help
  {
      var $name = array(name => 'help', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'This shows the bot commands and provides help for them.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      var $status = '1';
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%help', $this, 'c_help');
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^help', $this, 'm_help');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function m_help(&$irc, &$data)
      {
          global $toad;
          
          
          if (isset($data->messageex[1])) {
              $data->messageex[1] = preg_replace('/[^\w]/', '', $data->messageex[1]);
              $data->messageex[1] = strtolower($data->messageex[1]);
              if (file_exists("m_help/{$data->messageex[1]}.help")) {
                  //$lines = file_get_contents("m_help/{$data->messageex[1]}.help", "r");
                  $lines = file("m_help/{$data->messageex[1]}.help");
                  foreach ($lines as $line) {
                      $line = str_replace('%prefix%', $toad->prefix, $line);
                      $line = str_replace('%bold%', '\x02', $line);
                      $line = str_replace('%nick%', $data->nick, $line);
                      $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "$line");
                  }
              } else {
                  $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x02{$toad->prefix}help\x02: Help information for \x02{$data->messageex[1]}\x02 couldn't be found, " . $data->nick . ".");
              }
          } else {
              $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "Welcome to \x02$irc->_nick\x02 for IRC! These commands are accepted in queries.");
              //if((get_host($data->host) == true) && (get_level($data->host) >= USER_LEVEL_MASTER)) {
               if(($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER))
			   {
                  $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x02Bot Owner\x02: quit prefix nick");
              }
              //if((get_host($data->host) == true) && (get_level($data->host) >= USER_LEVEL_ADMIN)) {
              if(($toad->users[$data->nick]['loggedin'] == true) && (($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_ADMIN)))
			  {
                  $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x02Bot Admin\x02: join part switch kick ban kb say");
              }
              if(($toad->users[$data->nick]['loggedin'] == true) && (($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_ADMIN) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_BOTOP)))
			  {
                  $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x02Bot operator\x02: kick ban kb switch");
              }
              $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x02User commands\x02: help, login, logout, password, register");
              $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "To get help with a command just type, \x02help [command]\x02.");
          }
      }
      
      
      function c_help(&$irc, &$data)
      {
          global $toad;
          
          
          if (isset($data->messageex[1])) {
              $data->messageex[1] = preg_replace('/[^\w]/', '', $data->messageex[1]);
              $data->messageex[1] = strtolower($data->messageex[1]);
              if (file_exists("c_help/{$data->messageex[1]}.help")) {
                  //$lines = file_get_contents("c_help/{$data->messageex[1]}.help", "r");
                  $lines = file("c_help/{$data->messageex[1]}.help");
                  foreach ($lines as $line) {
                      $line = str_replace('%prefix%', $toad->prefix, $line);
                      $line = str_replace('%bold%', '\x02', $line);
                      $line = str_replace('%nick%', $data->nick, $line);
                      $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "$line");
                  }
              } else {
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}help\x02: Help information for \x02{$data->messageex[1]}\x02 couldn't be found, " . $data->nick . ".");
              }
          } else {
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "Welcome to \x02$irc->_nick\x02 for IRC! These are the available channel commands.");
              //if((get_host($data->host) == true) && (get_level($data->host) >= USER_LEVEL_MASTER)) {
              if(($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER))
			  {
                  $commands = null;
                  foreach ($irc->_modules as $key => $value) {
                      if (($value->name['access'] == 1) && ($value->name['from'] == 'channel')) {
                          $commands .= ' ' . $value->name['name'];
                      }
                  }
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Bot Owner\x02:$commands module");
              }
              //if((get_host($data->host) == true) && (get_level($data->host) >= USER_LEVEL_ADMIN)) {
              if(($toad->users[$data->nick]['loggedin'] == true) && (($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_ADMIN)))
			  {
                  $commands = null;
                  foreach ($irc->_modules as $key => $value) {
                      if (($value->name['access'] == 2) && ($value->name['from'] == 'channel')) {
                          $commands .= ' ' . $value->name['name'];
                      }
                  }
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Bot Admin\x02:$commands");
              }
              if ($irc->isOpped($data->channel, $data->nick)) {
                  $commands = null;
                  foreach ($irc->_modules as $key => $value) {
                      if (($value->name['access'] == 'op') && ($value->name['from'] == 'channel')) {
                          if (!$value->name) {
                              $commands .= "Currently aren't any op commands.";
                          }
                          $commands .= ' ' . $value->name['name'];
                      }
                  }
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Channel Op\x02: kick $commands");
              }
              if(($toad->users[$data->nick]['loggedin'] == true) && (($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_ADMIN) || ($toad->users[$data->nick]['rank'] == TOAD_RANK_BOTOP)))
			  {
                  $commands = null;
                  foreach ($irc->_modules as $key => $value) {
                      if (($value->name['access'] >= 3) && ($value->name['from'] == 'channel')) {
                          $commands .= ' ' . $value->name['name'];
                      }
                  }
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Bot operator\x02:$commands");
              }
              //$irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02User Commands\x02: help, 8ball, uptime, bash, reverse, tinyurl, php, fc, quote, names, pie, calc, weather, pkmn, channels, md5, youtube, hostname, define, google" );
              $commands = null;
              foreach ($irc->_modules as $key => $value) {
                  if (($value->name['access'] == 0) && ($value->name['from'] == 'channel')) {
                      $commands .= ' ' . $value->name['name'];
                  }
              }
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02User Commands\x02:$commands");
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "Remember that all commands start with \02{$toad->prefix}\x02.");
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "To get help with a command just type, \x02{$toad->prefix}help [command]\x02.");
          }
      }
  }
?>