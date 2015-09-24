<?php
class Net_SmartIRC_module_logger
{
	public $name = array(name => 'logger', access => '0', from => 'channel');
    public $version = '0.1a';
    public $description = 'Channel logger system.';
    public $author = 'Monie';
    public $license = 'GPL';
	public $access = '0';
    public $actionids = array();

	 function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%logger', $this, 'c_logger');
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^logger', $this, 'm_logger');
		  $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'log_message');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
	  
	function c_logger(&$irc, &$data)
	{
		// Will use MySQL for storing information.
		
		global $toad;
		
		if(preg_match('/NETWORK=/i', $irc->_network[1][10])) {
		$network = str_replace('NETWORK=', '', $irc->_network[1][10]);
		} elseif(preg_match('/NETWORK=/i', $irc->_network[1][9])) {
		$network = str_replace('NETWORK=', '', $irc->_network[1][9]);
		}
		
		if($data->messageex[1] == 'latest') {
			// Get the channel (lowercased) and get the last 5 messages
			$sql = 'SELECT * FROM `logger` WHERE `channel` = "'.strtolower($data->channel).'" AND `network` = "'.$strtolower($network).'" ORDER BY `id` DESC LIMIT 0,5';
			$result = $toad->query($sql);
			
			if(mysql_num_rows($result) === 0) {
				// Return with an error
				$toad->privmsg($data->channel, "\x02Error\x02: There was an error getting the results.");
				return;
			}
				
			while($row = mysql_fetch_array($result)) {
			$toad->notice($data->nick, "<".$row['nick']."> ".$row['message']."");
			}
		}	
	}
	
	function m_logger(&$irc, &$data)
	{
		// So we control what channels are being logged...
		global $toad;
		
		if(preg_match('/NETWORK=/i', $irc->_network[1][10])) {
		$network = str_replace('NETWORK=', '', $irc->_network[1][10]);
		} elseif(preg_match('/NETWORK=/i', $irc->_network[1][9])) {
		$network = str_replace('NETWORK=', '', $irc->_network[1][9]);
		}
		$command = $data->messageex[1];
		
		if(empty($command)) {
			$toad->privmsg($data->nick, "\x02Error\x02: You did not provide me a command.");
			return;
		}	
		
		if(($data->messageex[1] == 'add') && ($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER)) {
		$channel = strtolower($data->messageex[2]);
		
		if(empty($channel)) {
			$toad->privmsg($data->nick, "\x02Error\x02: You did not provide me a channel to add.");
			return;
		}	
		
		$sql = 'INSERT INTO `logger_channels` (`channel`, `time`, `who_did`, `network`) values("'.mysql_real_escape_string($channel, $toad->_dbHandle).'", "'.time().'", "'.mysql_real_escape_string($data->nick, $toad->_dbHandle).'", "'.mysql_real_escape_string(strtolower($network), $toad->_dbHandle).'")';
		$result = $toad->query($sql) or $toad->privmsg($data->channel, mysql_error());
		
		if(!$result) {
			$toad->privmsg($data->nick, "\x02Error\x02: There was an error adding the channel.");
			return;		
		}
		
		$toad->privmsg($data->nick, "\x02{$data->nick}\x02: Now logging {$channel}");
		return;
		
		} elseif(($data->messageex[1] == 'del') && ($toad->users[$data->nick]['loggedin'] == true) && ($toad->users[$data->nick]['rank'] == TOAD_RANK_OWNER)) {
		
		$channel = strtolower($data->messageex[2]);
		
		if(empty($channel)) {
			$toad->privmsg($data->nick, "\x02Error\x02: You did not provide me a channel to delete.");
			return;
		}	
		
		$sql = 'DELETE FROM `logger_channels` WHERE `channel` = "'.mysql_real_escape_string($channel).'" AND `network` = "'.mysql_real_escape_string(strtolower($network)).'"';
		$result = $toad->query($sql);
		
		if(!$result) {
			$toad->privmsg($data->nick, "\x02Error\x02: There was an error deleting the channel.");
			return;		
		}
		
		$toad->privmsg($data->nick, "\x02{$data->nick}\x02: No longer logging {$channel}.");
		return;
		}
	}

	function log_message(&$irc, &$data)
	{
		global $toad;
		
		if(preg_match('/NETWORK=/i', $irc->_network[1][10])) {
		$network = str_replace('NETWORK=', '', $irc->_network[1][10]);
		} elseif(preg_match('/NETWORK=/i', $irc->_network[1][9])) {
		$network = str_replace('NETWORK=', '', $irc->_network[1][9]);
		}
		
		$sql = 'SELECT `channel` FROM `logger_channels` WHERE `channel` = "'.mysql_real_escape_string(strtolower($data->channel)).'" AND `network` = "'.mysql_real_escape_string(strtolower($network)).'"';
		$result = $toad->query($sql) or $toad->privmsg($data->channel, mysql_error());
		
		if(!$result) {
		$toad->privmsg($data->channel, "\x02Error\x02: There was an error logging this channel.");
		return;
		}
		
	if(mysql_num_rows($result) === 0) {
		return;
		} else {
			$message = '';
				for ($i = 0; $i < count($data->messageex); $i++) {
				$message .= ' ' . $data->messageex[$i];
				}
			$sql = 'INSERT INTO `logger` (`channel`, `time`, `nick`, `message`, `network`) 
					values("'.mysql_real_escape_string(strtolower($data->channel)).'", "'.time().'", "'.mysql_real_escape_string($data->nick).'", "'.mysql_real_escape_string(trim($message)).'", "'.mysql_real_escape_string(strtolower($network)).'")';
			$result = $toad->query($sql) or $toad->privmsg($data->channel, mysql_error());

			if(!$result) {
			$toad->privmsg($data->channel, "\x02Error\x02: Can't add log to database,");
			$toad->privmsg($data->channel, mysql_error());
			return;
			}
			return;
		}
	}	
}