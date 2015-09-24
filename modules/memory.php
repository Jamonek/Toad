<?php
  class Net_SmartIRC_module_memory
  {
      var $name = array(name => 'memory', access => '1', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Shows bot memory usage.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '1';
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/%memory/', $this, 'memory');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      
      // get memory usage
      function memory(&$irc, &$data)
      {
          global $toad;
          
	
	if(($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER)) {
          
        
              $mem = $toad->memory_get_usage();
              $memory = $toad->ByteSize($mem);
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Memory\x02: I'm using " . $memory . " of memory.");
          } else {
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}memory\x02: You don't have access to this command.");
              return;
          }
      }
  }
?>