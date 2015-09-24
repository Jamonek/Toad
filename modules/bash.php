<?php
  class Net_SmartIRC_module_bash
  {
      var $name = array(name => 'bash', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Bash.org quote viewer.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%bash', $this, 'bash');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function bash(&$irc, &$data)
      {
          global $toad;
          
	
          
          if (count($data->messageex) == '1') {
          	$ch = curl_init();
          	curl_setopt($ch, CURLOPT_URL, "http://bash.org/?random");
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, '15');
			$page = @curl_exec($ch);
			curl_close($ch);
            //$page = file_get_contents("http://bash.org/?random");
              
              if ($page) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't connect to bash.org");
                  return;
              }
              
             
              preg_match('/<a href=".+" title="Permanent link to this quote."><b>(.+?)<\/b><\/a>/i',$ch, $number);
              preg_match('/<p class="qt">(.*)<\/p>/Uis', $ch, $match);
              $arrayo = array('&lt;', '&gt;', '&quot;', '&nbsp;', '<br />');
              
              $array = array('<', '>', '"', '', '');
              //var_dump($match);
              
              if (!isset($match[1])) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't get bash.");
                  return;
              }
              
              $match[1] = str_replace($arrayo, $array, $match[1]);
              
              $bash = explode("\n", $match[1]);
              //var_dump($bash);
              
              if (!isset($bash)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't get bash.");
                  return;
              }
              
              if (count($bash) > 5) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Quote is too big, sending in notice.");
                  foreach ($bash as $matchz => $value) {
                      $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "[\x02Bash {$number[1]}\x02] $value");
                  }
              } elseif (count($bash) > 1) {
                  foreach ($bash as $matchz => $value) {
                      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "[\x02Bash {$number[1]}\x02] $value");
                  }
              } else {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "[\x02Bash {$number[1]}\x02] $bash[0]");
              }
          } elseif (count($data->messageex) == '2') {
              $q = $data->messageex[1];
              
              if (!$q) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a id.");
                  return;
              }
              
              $q = str_replace('#', '', $q);
              
              $page = file_get_contents("http://bash.org/?" . urlencode($q) . "");
              
              if (!$page) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't connect to bash.org");
                  return;
              }
              
              preg_match('/<a href=".+" title="Permanent link to this quote."><b>(.+?)<\/b><\/a>/i',$page, $number);
              preg_match('/<p class="qt">(.*)<\/p>/Uis', $page, $match);
              $arrayo = array('&lt;', '&gt;', '&quot;', '&nbsp;', '<br />');
              
              $array = array('<', '>', '"', '', '');
              //var_dump($match);
              if (!isset($match[1])) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't get bash.");
                  return;
              }
              
              $match[1] = str_replace($arrayo, $array, $match[1]);
              
              $bash = explode("\n", $match[1]);
              //var_dump($bash);
              
              if (!isset($bash)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't get bash.");
                  return;
              }
              
              if (count($bash) > 5) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Quote is too big, sending in notice.");
                  foreach ($bash as $matchz => $value) {
                      $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "[\x02Bash {$number[1]}\x02] $value");
                  }
              } elseif (count($bash) > 1) {
                  foreach ($bash as $matchz => $value) {
                      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "[\x02Bash {$number[1]}\02] $value");
                  }
              } else {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "[\x02Bash {$number[1]}\x02] $bash[0]");
              }
          }
      }
  }
?>