<?php
  class Net_SmartIRC_module_guess
  {
      var $name = array(name => 'guess', access => '0', from => 'channel');
      var $version = '0.0.1';
      var $description = 'Number guessing game.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      public $gnum = array();
      public $gchan = array();
      public $gcount = array();
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%guess/', $this, 'guess');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function guess(&$irc, &$data)
      {
          global $toad;
          
          if (count($data->messageex) == '2') {
              $number = $data->messageex[1];
              $number = str_replace(',', '', $number);
              
              if (empty($number)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: No number provided, type {$toad->prefix}help guess for more information.");
                  return;
              }
              
              if (!isset($this->gchan[$data->channel])) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: This channel doesn't have a game going on..");
                  return;
              }
              
              if (preg_match('/[0-9]/', $data->messageex[1]) == false) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02%guess\x02: Syntax error, type {$toad->prefix}help guess for more information.");
                  return;
              }
              
              if ($this->gnum[$data->channel] == $data->messageex[1]) {
                  if (!isset($this->gcount[$data->nick])) {
                      $count = '0 guesses.';
                  } else {
                      //$this->gcount[$data->nick]++;
                      $count = "{$this->gcount[$data->nick]} guesses.";
                  }
                  $sql = "SELECT NULL FROM `guess` WHERE g_user='{$data->nick}'";
                  $q = $toad->query($sql);
                  if (mysql_num_rows($q) == 0) {
                      $nick = mysql_escape_string(strtolower($data->nick));
                      $sql1 = "INSERT INTO `guess` (`g_user`,`g_points`) VALUES('{$nick}', '10')";
                      $q1 = $toad->query($sql1);
                  } else {
                      $sql1 = "SELECT * FROM `guess` WHERE g_user='".mysql_real_escape_string(strtolower($data->nick))."'";
                      $q1 = $toad->query($sql1);
                      $row = mysql_fetch_array($q1);
                  }
                  if (mysql_num_rows($q) == 0) {
                      $pointz = '10';
                      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: Congratulations, You guessed the right number. It took you {$count} \x02{$pointz}\x02 points!!!");
                  } else {
                      $pointz = '10';
                      $sql1 = "SELECT * FROM `guess` WHERE g_user='".mysql_real_escape_string(strtolower($data->nick))."'";
                      $q1 = $toad->query($sql1);
                      $row1 = mysql_fetch_array($q1);
                      $opoint = $row1['g_points'];
                      $points = $row1['g_points'] + 10;
                      $sql2 = "UPDATE `guess` SET g_points='{$points}' WHERE g_user='".mysql_real_escape_string(strtolower($data->nick))."'";
                      $q2 = $toad->query($sql2);
                      $sql = "SELECT * FROM `guess` WHERE g_user='".mysql_real_escape_string(strtolower($data->nick))."'";
                      $result = $toad->query($sql);
                      $row = mysql_fetch_array($result);
                      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: Congratulations, You guessed the right number. It took you {$count} \x02{$opoint}\x02+10 = \x02{$row['g_points']}\x02 points!!!");
                  }
                  
                  unset($this->gchan[$data->channel]);
                  unset($this->gnum[$data->channel]);
                  unset($this->gcount[$data->nick]);
                  return;
              } elseif ($this->gnum[$data->channel] < $data->messageex[1]) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: You guessed to high, try guessing lower.");
                  $this->gcount[$data->nick]++;
                  //print_r($this->gcount[$data->nick]);
                  return;
              } elseif ($this->gnum[$data->channel] > $data->messageex[1]) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: You guessed to low, try guessing higher.");
                  $this->gcount[$data->nick]++;
                  //print_r($this->gcount[$data->nick]);
                  
                  return;
              } else {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: Sorry, you guessed the wrong number.");
                  return;
              }
          } elseif ((count($data->messageex) >= '2') && ($data->messageex[1] == 'start') or ($data->messageex[1] == 's')) {
              if (empty($data->messageex[2])) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$toad->prefix}guess\x02: Syntax error, type {$toad->prefix}help guess for more information.");
                  return;
              }
              
              if (isset($this->gchan[$data->channel])) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: This channel already has a game going on.");
                  return;
              }
              
              if (preg_match('/[0-9]/', $data->messageex[2]) == false) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$toad->prefix}guess\x02: Syntax error, type {$toad->prefix}help guess for more information.");
                  return;
              }
              
              if ($data->messageex[2] > 5000) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$toad->prefix}guess\x02: Syntax error, type {$toad->prefix}help guess for more information.");
                  return;
              }
              if($data->messageex[2] <= 4) {
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$toad->prefix}guess\x02: Syntax error, type {$toad->prefix}help guess for more information.");
              return;
              }
              
              $this->gnum[$data->channel] = mt_rand('1', "{$data->messageex[2]}");
              $this->gchan[$data->channel] = $data->channel;
              //print_r($this->gnum);
              //print_r($this->gchan);
              
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "A game has been started for \x02{$data->channel}\x02. Guess the right number, it's a random number between 1 and {$data->messageex[2]}.");
          } elseif ((count($data->messageex) >= '2') && ($data->messageex[1] == 'points') or ($data->messageex[1] == 'p')) {
			if(empty($data->messageex[2])) {
			$user = strtolower($data->nick);
			} else {
			$user = strtolower($data->messageex[2]);
			}
			
			$sql = 'SELECT g_points FROM `guess` WHERE `g_user` = "'.mysql_real_escape_string($user).'"';
			$result = $toad->query($sql);
			
			if(!$result) {
			$toad->privmsg($data->channel, "\x02Error\x02: There was an error with the SQL Structure.");
			return;
			}
			
			if(mysql_num_rows($result) === 0) {
			$toad->privmsg($data->channel, "\x02Error\x02: Username is not in the database.");
			return;
			}
			
			$point_user_array = mysql_fetch_array($result);
			
			if(!empty($data->messageex[2])) {
			$toad->privmsg($data->channel, "\x02{$data->messageex[2]}\x02 has a total of \x02{$point_user_array['g_points']}\x02 points.");
			return;
			} else {
			$toad->privmsg($data->channel, "\x02{$data->nick}\x02 has a total of \x02{$point_user_array['g_points']}\x02");
			return;
			}
			}
      }
  }
?>