<?php
  class Net_SmartIRC_module_lovecalc
  {
      var $name = array(name => 'lovecalc', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Calcs the chance of a successful relationship between two people/thing.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%lovecalc/', $this, 'lovecalc');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      
      function lovecalc(&$irc, &$data)
      {
          global $toad;
          
          	if($toad->is_disabled($irc->_network[1][9], $data->channel, 'lovecalc') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}lovecalc\x02: This command is disabled for \x02{$data->channel}\x02.");
				return;
			}	
          
          $mes = implode(' ', array_slice($data->messageex, 1));
          if (empty($mes)) {
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Syntax error, type %help lovecalc for more information.");
              return;
          }
          
          $user = explode(",", $mes);
          
          if (empty($user)) {
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Syntax error, type %help lovecalc for more information.");
              return;
          }
          
          $one = trim($user[0]);
          if(empty($one)) {
          $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Syntax error, type %help lovecalc for more information.");
          return;
          }
          
          $two = trim($user[1]);
          if(empty($two)) {
          $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Syntax error, type %help lovecalc for more information.");
          return;
          }
          
          if(strtolower($one) == strtolower($two)) {
          $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Lovecalc\x02 predicts that $one loves alot of his/her self.");
          return;
          }
          
          $rand = mt_rand('0', '100');
          
          $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Lovecalc\x02 predicts the chance of a successful relation between $one and $two is " . $rand . "%.");
                
      }
  }
?>