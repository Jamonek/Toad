<?php
require_once 'SmartIRC.php';

DEFINE('TOAD_RANK_USER', 0);
DEFINE('TOAD_RANK_BOTOP', 1);
define('TOAD_RANK_ADMIN', 2);
define('TOAD_RANK_OWNER', 3);

$start_time = time();
class Toad {

   	public $users;
	public $prefix = '%';
	public $_dbHandle;
	public $_NickServPass = '';
    // Is the DB already open?
    public $_dbOpen = false;
      
	    function query($query)
      {
          $this->_db_open();
          //$result = mysql_db_query('moniebot', $query, $mysql_link);
          $result = mysql_query($query);
          return $result;
      }
	  
      function _db_open()
      {
          if ($this->_dbOpen === false) {
              if (($this->_dbHandle = mysql_connect('', '', '')) === false) {
                  die('Could not open DB: ' . mysql_error());
              }
              if (mysql_select_db('toad') === false) {
                  die('Could not open DB: ' . mysql_error());
              }
              $this->_dbOpen = true;
              //$this->_dbAutoCloseTimer = $this->_dbAutoCloseDelay; # Not in use with mysql_pconnect()
          }
      }
	  
	     function db_ping(&$irc)
      {
          if (mysql_ping()) {
          return;
          } else {
          $this->_db_open();
          }
      }
	function get_users() {
	$this->users = unserialize(file_get_contents('data/users.txt'));
	}
	
	function login(&$irc, &$data) {
		$username = $data->messageex[1];
		
		$password = hash('sha512',strlen($data->messageex[2]).$data->messageex[2].strlen($data->messageex[1]));
	
		
		if($this->users[$username]['loggedin'] == true) {
		$this->notice($data->nick, "\x02Error\x02: You are already identified.");
		return;
		}
		if($password == $this->users[$username]['password'] && $username == $this->users[$username]['username']) 
		{
		
			$this->users[$username]['loggedin'] = true;
			$this->notice($data->nick, "\x02{$data->nick}\x02: You are now identified to \x02{$irc->_nick}\x02.");
			
		} else {
		//$irc->message(SMARTIRC_TYPE_CHANNEL, '#Toad', $this->users[$username]['username']);
		$this->notice($data->nick, "\x02Error\x02: The username or password combination you provided is incorrect.");
		return;
		
		}
	}
	
	function register(&$irc, &$data) {
		$username = $data->messageex[1];
		
		$password = $data->messageex[2];
		
		if(!isset($username))
		{ 
			$this->notice($data->nick, "\x02Error\x02: You did not provide a username to register with.");
			return;
		} elseif(!isset($password))
		{
			$this->notice($data->nick, "\x02Error\x02: You did not provide a password to register with.");
			return;	
		
		} elseif(isset($this->users[$username])) 
		{
			$this->notice($data->nick, "\x02Error\x02: The username you have tried to register with is already taken.");
			return;
			
		} else {
			
		$newpass = 	hash('sha512',strlen($data->messageex[2]).$data->messageex[2].strlen($data->messageex[1]));
		$this->users[$username]['username'] = $username;
		$this->users[$username]['password'] = $newpass;
		$this->users[$username]['rank'] = TOAD_RANK_USER;
		$this->users[$username]['loggedin'] = false;
		
		$this->notice($data->nick, "You are now registered to Toad, with username \x02{$username}\x02.");
		$this->notice($data->nick, "Remember usernames are case-sensitive.");
		file_put_contents('data/users.txt', serialize($this->users));
		
		
		}
		
	}
	
	function logout(&$irc, &$data) {
	
		if($this->users[$data->nick]['loggedin'] != true) 
		{
			$this->notice($data->nick, "\x02Error\x02: You are not logged into \x02{$irc->_nick}\x02.");
			return;
		} else {
			$this->notice($data->nick, "Attempting to log you out.");
			$this->users[$data->nick]['loggedin'] = false;
			sleep('1');
			$this->notice($data->nick, "You are now successfully logged out.");
		}
		
	}	
	
	function module(&$irc, &$data) {
	
	if(($this->users[$data->nick]['loggedin'] == true) && ($this->users[$data->nick]['rank'] == TOAD_RANK_OWNER))
	{
	
		if(count($data->messageex) == 1) 
		{
			// Only list the current modules loaded..
			$mod = '';
            $c = null;
            foreach ($irc->_modules as $key => $value) {
            $c++;
            $mod .= ' '.$value->name['name'];
            }
			
			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "There are currently \x02{$c}\x02 modules loaded.");
			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Modules\x02:{$mod}");
			
		} elseif($data->messageex[1] == 'load') {
			if(empty($data->messageex[2])) {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You have forgotten an argument.");
				return;
			}
			
				if($irc->loadModule($data->messageex[2]) == true) 
				{
					$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Successfully loaded module \x02{$data->messageex[2]}\x02.");
				} else {
					$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "There was an error loading module \x02{$data->messageex[2]}\x02.");
					return;
				}
				
		} elseif($data->messageex[1] == 'unload') {
			if(empty($data->messageex[2])) {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You have forgotten an argument.");
				return;
			}
			
			
			if($irc->unloadModule($data->messageex[2]) == true)
			{
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Successfully unloaded module \x02{$data->messageex[2]}\x02.");
			} else {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "There was an error unloading module \x02{$data->messageex[2]}\x02.");
				return;
			}
	  }
	 
	 } else {
	 $irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02Error\x02: You do not have access to this command.");
	 return;
	}
  }	
  
  function _web($url)
  {
	
	$ch = 	curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
          	curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, '15');
			$page_html = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);
			
	if(!empty($error))
		{
			return false;
		} else {
		
		return $page_html;
		
		}
	}
	
	function is_disabled($network, $chan, $command)
	{
		global $irc;
      	$file = "./data/switch/".strtolower($irc->_address)."-".strtolower($chan)."-".strtolower($command).".txt";
		
		$fh = @fopen($file, 'r');
		$status = @fgets($fh);
		@fclose($fh);
		
		if($status == strtolower('disabled'))
		{
		return true;
		} elseif($status == strtolower('enabled'))
		{
		return false;
		} else {
		return false;
		}
	}

		     function memory_get_usage()
      {
          //If its Windows
          //Tested on Win XP Pro SP2. Should work on Win 2003 Server too
          //Doesn't work for 2000
          //If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memory-get-usage.php#54642
          if (substr(PHP_OS, 0, 3) == 'WIN') {
              if (substr(PHP_OS, 0, 3) == 'WIN') {
                  $output = array();
                  exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);
                  
                  return preg_replace('/[\D]/', '', $output[5]) * 1024;
              }
          } else {
              //We now assume the OS is UNIX
              //Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
              //This should work on most UNIX systems
              $pid = getmypid();
              exec("ps -eo%mem,rss,pid | grep $pid", $output);
              $output = explode("  ", $output[0]);
              //rss is given in 1024 byte units
              return $output[1] * 1024;
          }
      }
      
      // returns the size of a certain amount of bytes
      function ByteSize($bytes)
      {
          $size = $bytes / 1024;
          if ($size < 1024) {
              $size = number_format($size, 2);
              $size .= 'KB';
          } else {
              if ($size / 1024 < 1024) {
                  $size = number_format($size / 1024, 2);
                  $size .= 'MB';
              } elseif ($size / 1024 / 1024 < 1024) {
                  $size = number_format($size / 1024 / 1024, 2);
                  $size .= 'GB';
              }
          }
          return $size;
      }
	  
	  function privmsg($to, $msg)
      {
          global $irc, $data;
          $mesg = explode("\n", $msg);
          foreach ($mesg as $mesgg) {
              $irc->message(SMARTIRC_TYPE_QUERY, $to, $mesgg);
          }
      }
      
      function notice($to, $msg)
      {
          global $irc, $data;
          $mesg = explode("\n", $msg);
          foreach ($mesg as $mesgg) {
              $irc->message(SMARTIRC_TYPE_NOTICE, $to, $mesgg);
          }
      }
      
      function action($to, $msg)
      {
          global $irc, $data;
          $mesg = explode("\n", $msg);
          foreach ($mesg as $mesgg) {
              $irc->message(SMARTIRC_TYPE_ACTION, $to, $mesgg);
          }
      }
	  
	  function services_id(&$irc)
	  {
	  global $id_once;
	  //$irc->oper('Monie', 'Dpx45e42');
	   $irc->message(SMARTIRC_TYPE_QUERY, 'Authserv@services.sinirc.net', 'AUTH Toad '.$this->_NickServPass);
	  $irc->unregisterTimeid($id_once);
	  }
      
}

 	$toad = new Toad();
  	$irc = new Net_SmartIRC();
  	$irc->setChannelSyncing(true);
	$irc->setTransmitTimeout(200);
	$irc->setUserSyncing(true);
	$irc->setDebug(SMARTIRC_DEBUG_ALL);
  	$irc->setUseSockets(true);
  	$irc->setAutoRetry(true);
  	$irc->setAutoReconnect(true);
  	$irc->setAutoRetryMax('3');
  	$irc->setReceiveTimeout(600);
  	$irc->setTransmitTimeout(600);
  	$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^login', $toad, 'login');
  	$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^register', $toad, 'register');
  	$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^logout', $toad, 'logout');
  	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%module', $toad, 'module');
  	
  	/*
  	Module loading information
  	*/
	$irc->registerTimehandler(10000, $toad, 'db_ping');
	//$id_once = $irc->registerTimehandler(500, $toad, 'services_id');
  	$modules = array('switch', 'quote', 'weather', 'memory',
					'kick', 'nick', 'uptime', 'join','part',
					'eval', 'spy', 'relay', 'say', 'lovecalc',
					'8ball', 'fortune', 'reverse', 'help',
					'guess', 'xboxdata', 'epenis', 'twitter'
					);
  	
  	foreach($modules as $mod)
  	{
  		$irc->loadModule($mod);
  	}	
/*
	if(!$argv[1]) {
	echo "php ".$argv[0]." <server> <port>\n";
	return;
	}
	if(isset($argv[2])) {
		$irc->connect("{$argv[1]}", "{$argv[2]}");
		} else {
			$irc->connect("{$argv[1]}", 6667);
	}	
*/
  	$irc->connect("irc.jcink.com", "6667");
  	$irc->setCtcpVersion('Toad IRC Bot v.4.1');
  	$irc->login('Toad', 'Toad v4.1', 8 ,'Toad');
  	$irc->mode("$irc->_nick", '+BT');
	$irc->join(array('#monie'));
  	$toad->get_users();
  	$irc->listen();
  	$irc->disconnect();
  	?>