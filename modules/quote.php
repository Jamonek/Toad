<?php
  class Net_SmartIRC_module_quote
  {
      var $name = array(name => 'quote', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Quote DB.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%quote/', $this, 'quote');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      
      function quote(&$irc, &$data)
      {
          global $toad;
         
          if($toad->is_disabled($irc->_network[1][9], $data->channel, 'quote') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}quote\x02: This command is disabled for \x02{$data->channel}\x02.");
				return;
			}	
          
          if (count($data->messageex) == '1') {
              $query = "SELECT q_id, q_quote, q_date, q_user FROM quote ORDER BY RAND() LIMIT 1;";
              $result = $toad->query($query);
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Getting quote from database. ");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02SQL message\x02: " . mysql_error());
                  return;
              }
              $row = mysql_fetch_array($result);
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Quote\x02: (id: {$row['q_id']}) {$row['q_quote']} (By: {$row['q_user']}) (Date: {$row['q_date']})");
          } elseif ((count($data->messageex) >= '2') && ($data->messageex[1] == 'count') or ($data->messageex[1] == 'c')) {
              $user = $data->messageex[2];
              if (!$user) {
                  $query = "SELECT q_id FROM quote";
              } else {
                  $query = "SELECT q_user FROM quote WHERE q_user='{$user}'";
              }
              $result = $toad->query($query);
              $numrows = mysql_num_rows($result);
              if ($numrows > 1) {
                  $verb = 'quotes';
              } elseif ($numrows == 0) {
                  $verb = 'quotes';
              } else {
                  $verb = 'quote';
              }
              if (!$user) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Quote\x02: There are \x02$numrows\x02 $verb in the database.");
              } else {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Quote\x02: $user has \x02$numrows\x02 $verb in the database.");
              }
          } elseif ((count($data->messageex) >= '3') && ($data->messageex[1] == 'add') or ($data->messageex[1] == 'a')) {
              $quote = implode(' ', array_slice($data->messageex, 2));
              if (!$quote) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You did't provide a quote to add.");
                  return;
              }
              $nquote = mysql_real_escape_string($quote);
              if (!$nquote) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't connect to database. Try re-adding your quote.");
                  return;
              }
              $query = "INSERT INTO quote (`q_user`,`q_quote`,`q_date`) VALUES ('{$data->nick}','{$nquote}', '" . date('Y-m-d') . "')";
              $result = $toad->query($query);
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't add quote to the database. ");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02SQL message\x02: " . mysql_error());
                  return;
              }
              $queryo = "SELECT q_id FROM quote";
              $resulto = $toad->query($queryo);
              $numrows = mysql_num_rows($resulto);
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02$data->nick\x02: Successfully added your quote to the database. Quote number \x02$numrows\x02.");
          } elseif ((count($data->messageex) == '3') && ($data->messageex[1] == 'view') or ($data->messageex[1] == 'v')) {
              $id = $data->messageex[2];
              if (!$id) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a quote id.");
                  return;
              }
              $query = "SELECT q_id, q_user, q_quote, q_date FROM quote WHERE q_id='{$id}'";
              $result = $toad->query($query);
              $numrows = mysql_num_rows($result);
              if ($numrows === 0) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: The quote id you provided doesn't exist.");
                  return;
              }
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't retrieve the quote. " . mysql_error());
                  return;
              }
              while ($row = mysql_fetch_array($result)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Quote\x02: (id: {$row['q_id']}) {$row['q_quote']} (By: {$row['q_user']}) (Date: {$row['q_date']})");
              }
          } elseif ((count($data->messageex) == '2') && ($data->messageex[1] == 'latest') or ($data->messageex[1] == 'l')) {
              $query = "SELECT q_id, q_user, q_quote, q_date FROM quote ORDER BY q_id DESC LIMIT 1";
              $result = $toad->query($query);
              
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't retreive the latest quote.");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: " . mysql_error());
                  return;
              }
              
              $row = mysql_fetch_assoc($result);
              
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Quote\x02: (id: {$row['q_id']}) {$row['q_quote']} (By: {$row['q_user']}) (Date: {$row['q_date']})");
          } elseif ((count($data->messageex) == '3') && ($data->messageex[1] == 'find') or ($data->messageex[1] == 'f')) {
              $user = $data->messageex[2];
              if (!$user) {
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, "\x02Error\x02: You did not provide a user to look up.");
                  return;
              }
              $query = "SELECT q_id, q_quote, q_date, q_user FROM quote WHERE q_user='{$user}' LIMIT 10;";
              $result = $toad->query($query);
              $numrows = mysql_num_rows($result);
              if ($numrows === 0) {
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: The user you choose to search for didn't exist in the database.");
                  return;
              }
              $query1 = "SELECT q_id, q_quote, q_date, q_user FROM quote WHERE q_user='{$user}'";
              $result1 = $toad->query($query1);
              $numrows1 = mysql_num_rows($result1);
              $aquotes = $numrows1 - 10;
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, "\x02Error\x02: Getting quote from database.");
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, "\x02Error\x02: " . mysql_error());
                  return;
              }
              while ($row = mysql_fetch_array($result)) {
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Quote\x02: (id: {$row['q_id']}) {$row['q_quote']} (By: {$row['q_user']}) (Date: {$row['q_date']})");
              }
              if($aquotes >= 1) {
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Quote\x02: $user also had \x02$aquotes\x02 other quotes.");
 			} else {
 			return;
 			}
          } else {
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}quote\x02: You provided an unrecognized command, type {$toad->prefix}help quote.");
              return;
          }
      }
  }
?>