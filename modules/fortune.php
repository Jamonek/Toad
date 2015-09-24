<?php
  class Net_SmartIRC_module_fortune
  {
      var $name = array(name => 'fortune', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Random fortunes.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      var $actionids = array();
      public $file = './data/fortunes.txt';
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%fortune/', $this, 'fortune');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function fortune(&$irc, &$data)
      {
          global $toad;
         
         	if($toad->is_disabled($irc->_network[1][9], $data->channel, 'fortune') == true)
			{
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}fortune\x02: This command is disabled for \x02{$data->channel}\x02.");
			return;
			}	
          
          if (count($data->messageex) == '1') {
              $quotes = file($this->file);
              //$rand = $quotes[rand(0, sizeof($quotes) - 1)];
              
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: ".$quotes[rand(0, sizeof($quotes) - 1)]."");
          } else {
              $quotes = file($this->file);
              //$rand = $quotes[rand(0, sizeof($quotes) - 1)];
              
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->messageex[1]}\x02: ".$quotes[rand(0, sizeof($quotes) - 1)]."");
          }
      }
  }
?>