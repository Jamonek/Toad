<?php
  class Net_SmartIRC_module_google
  {
      var $name = array(name => 'google', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Google.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%google', $this, 'google');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      // google
      function google(&$irc, &$data)
      {
          global $toad;
          
          /*
          if($bot->_isIgnored($data->host) == true) {
$bot->notice($data->nick, "\x02{$data->nick}\x02: You are not allowed to access \037\x02{$irc->_nick}\x02\037");
return;
}*/
          
          $qu = implode(' ', array_slice($data->messageex, 1));
          
          if (!$qu) {
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a query.");
              return;
          }
          
          $searchstring = $qu;
          $searchstring = str_replace(' ', '+', $searchstring);
          $google = file_get_contents("http://www.google.com/search?q=".urlencode($searchstring));
          preg_match('/<h2 class=r><a href="([^"]+)"[^>]*>(.+)<\/a><\/h2>.+size=[^>]*>(.+)<br><span.* - ([^ ]*) /Ui', $google, $match);
          
          if (!$match) {
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: No results.");
              return;
          }
          
          $match = str_replace('&#39;', "'", $match);
          $match = str_replace('&quot;', '"', $match);
          $match = str_replace('&amp;', '&', $match);
          
          $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02".strip_tags($match[2])."\x02 - ".strip_tags($match[1])." - ".strip_tags($match[4])." => \"".strip_tags($match[3])."\"");
      }
  }
?>