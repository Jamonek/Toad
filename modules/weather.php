<?php
  class Net_SmartIRC_module_weather
  {
      var $name = array(name => 'weather', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Weather.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%weather', $this, 'weather');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function parse_search(&$irc, $nick, $loc)
      {
      		global $toad;
      		global $data;
      		
          $content = $toad->_web("http://xoap.weather.com/search/search?where=" . urlencode($loc) . "");
          if(!$content) {
          $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't get data from weather.com");
          return;
          }
          preg_match_all('/<loc id=\"(.+)\"\ type=\".\">(.+)\<\/loc>/i', $content, $matches);
          
          if (count($matches[1]) > 1) {
              $irc->message(SMARTIRC_TYPE_NOTICE, $nick, "There were multiple matches for your search. Confirm the location you want by typing, %weather {id}");
              $list = ' ';
              foreach ($matches[1] as $key => $value) {
                  $irc->message(SMARTIRC_TYPE_NOTICE, $nick, "location: \x02" . $matches[2][$key] . "\x02 [id]: $value");
              }
              return 'There were multiple matches for your search.';
          } else {
              return $matches[1][0];
          }
      }
      
      function weather(&$irc, &$data)
      {
          global $toad;
          
if($toad->is_disabled($irc->_network[1][9], $data->channel, 'weather') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}weather\x02: This command is disabled for \x02{$data->channel}\x02.");
				return;
			}	
          
          $key = '0915e3e3a639bc74';
          $par = '1041634590';
          
          //$weather_data = './data/weather/'.$data->nick.'.txt';
          
          if ((count($data->messageex) == '3') && ($data->messageex[1] == 'add') or ($data->messageex[1] == 'a')) {
              $loc = implode(' ', array_slice($data->messageex, 2));
              
              if (!$loc) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a location.");
                  return;
              }
              
              if (!preg_match("/^\d{4,5}$|^.+$/", $loc)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Your location wasn't valid.");
                  return;
              }
              
              $zip = $this->parse_search(&$irc, $data->nick, $loc);
              
              if (preg_match('/There were multiple matches for your search./i', $zip)) {
                  return;
              }
              $sql = "SELECT NULL FROM weather WHERE w_nick='{$data->nick}'";
              $r = $toad->query($sql);
              if(mysql_num_rows($r) > '0') {
              $q = "UPDATE weather SET `w_loc` = '{$zip}' WHERE `w_nick` = '{$data->nick}'";
              $res = $toad->query($q);
              if(!$res) {
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't update your weather location.");
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: " .mysql_error());
              return;
              }
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: Successfully updated your location.");
              
              } else {
              $query = "INSERT INTO weather (`w_loc`, `w_nick`) VALUES ('{$zip}','{$data->nick}')";
              $result = $toad->query($query);
              
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't add your weather information.");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: " . mysql_error());
                  return;
              }
              
              
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$data->nick}\x02: Your location was successfully added.");
          }
          } elseif (count($data->messageex) == '1') {
              $query = "SELECT w_loc, w_nick FROM weather WHERE w_nick='{$data->nick}'";
              $result = $toad->query($query);
              
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't get your weather information.");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: " . mysql_error());
                  return;
              }
              
              $numrows = mysql_num_rows($result);
              
              if ($numrows == '0') {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: $data->nick, you are not in the database. To add your zipcode type {$toad->prefix}help weather.");
                  return;
              }
              
              
              $row = mysql_fetch_assoc($result);
              
              
              $zip = $this->parse_search(&$irc, $data->nick, $row['w_loc']);
              
              if (preg_match('/There were multiple matches for your search./i', $zip)) {
                  return;
              }
              
              $page = $toad->_web("http://xoap.weather.com/weather/local/" . urlencode($zip) . "?cc=*&link=xoap&prod=xoap&day1=5&par=" . urlencode($par) . "&key=" . urlencode($key) . "");
										//http://xoap.weather.com/weather/local/30339?cc=*&dayf=5&link=xoap&prod=xoap&par=[PartnerID]&key=[LicenseKey]
//echo $page;
										preg_match('/<dnam>(.+)\<\/dnam>/', $page, $loc);
              preg_match('/<lsup>.+? (.+?)\<\/lsup>/', $page, $lastup);
              if (preg_match('/<tmp>(.+?)<\/tmp>/', $page, $one)) {
                  $tempc = (($one[1] - 32) / 1.8);
                  $tempc = sprintf("%.0f", $tempc) . "C";
                  $tempf = "$one[1]F";
              }
              if (preg_match('/<flik>(.+)<\/flik>/', $page, $two)) {
                  $flikc = (($two[1] - 32) / 1.8);
                  $flikc = sprintf("%.0f", $flikc) . "C";
                  $flikf = "$two[1]F";
              }
              preg_match('/<\/flik>\s*<t>(.+)\<\/t>/', $page, $cond);
              preg_match('/<s>(.+)<\/s>/', $page, $windz);
              if (preg_match_all('/<\/d>\s*<t>(.+)<\/t>\s*<\/wind>/s', $page, $windd)) {
                  $winddir = strtoupper($windd[1][0]);
                  $abbrs = array('N' => 'North', 'S' => 'South', 'E' => 'East', 'W' => 'West', 'SW' => 'Southwest', 'SE' => 'Southeast', 'NW' => 'Northwest', 'NE' => 'Northeast', 'SSW' => 'South-Southwest', 'SSE' => 'South-Southeast', 'NNW' => 'North-Northwest', 'NNE' => 'North-Northeast', 'WNW' => 'West-Northwest', 'WSW' => 'West-Southwest', 'ENE' => 'East-Northeast', 'ESE' => 'East-Southeast', );
                  
                  $winddir = (!empty($abbrs[$winddir]) ? $abbrs[$winddir] : $winddir);
                  
                  if ($windz[1] != 'calm') {
                      $wind = "$winddir at $windz[1] MPH";
                  } else {
                      $wind = 'calm';
                  }
              }
              
              preg_match('/<hmid>(.+)<\/hmid>/', $page, $hmid);
              preg_match('/<ud>.<i>(.+)<\/i>.<t>(.+)<\/t>.<\/uv>/', $page, $uv);
              preg_match('/<r>(.+)<\/r>/', $page, $r);
			  preg_match('/<sunr>(.*)<\/sunr>/', $page, $sunrise);
			  preg_match('/<suns>(.*)<\/suns>/', $page, $sunset);
              preg_match('/<d>(.+)<\/d>/', $page, $d);
			  
              if ($uv[1] > 2) {
                  $uvi = ", UV Index: $uv[1] ($uv[2])";
              } else {
                  unset($uv);
              }
              
              if (!$loc) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: I was unable to get the weather for \"{$row['w_loc']}\".");
                  return;
              }
              
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Weather for $loc[1] as of $lastup[1]\x02: Condition: $cond[1], Temperature: $tempf ($tempc), Feels like: $flikf ($flikc), Wind: $wind, Barometer: $r[1] and $d[1], Humidity: $hmid[1]%$uvi \x02Sunrise\x02: {$sunrise[1]} \x02Sunset\x02: {$sunset[1]}");
          } elseif ((count($data->messageex) == '3') && ($data->messageex[1] == 'get') or ($data->messageex[1] == 'g')) {
              $user = $data->messageex[2];
              
              if (!$user) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a user, type {$toad->prefix}help weather.");
                  return;
              }
              
              $query = "SELECT w_loc, w_nick FROM weather WHERE w_nick='{$user}'";
              $result = $toad->query($query);
              
              if (!$result) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Couldn't get your weather information.");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: " . mysql_error());
                  return;
              }
              
              $numrows = mysql_num_rows($result);
              
              if ($numrows == '0') {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: $user does not have a code in the database.");
                  return;
              }
              
              $row = mysql_fetch_assoc($result);
              
              $zip = $this->parse_search(&$irc, $data->nick, $row['w_loc']);
              
              if (preg_match('/There were multiple matches for your search./i', $zip)) {
                  return;
              }
              
$page = $toad->_web("http://xoap.weather.com/weather/local/" . urlencode($zip) . "?cc=*&link=xoap&prod=xoap&day1=5&par=" . urlencode($par) . "&key=" . urlencode($key) . "");              preg_match('/<dnam>(.+)\<\/dnam>/', $page, $loc);
              preg_match('/<lsup>.+? (.+?)\<\/lsup>/', $page, $lastup);
              if (preg_match('/<tmp>(.+?)<\/tmp>/', $page, $one)) {
                  $tempc = (($one[1] - 32) / 1.8);
                  $tempc = sprintf("%.0f", $tempc) . "C";
                  $tempf = "$one[1]F";
              }
              if (preg_match('/<flik>(.+)<\/flik>/', $page, $two)) {
                  $flikc = (($two[1] - 32) / 1.8);
                  $flikc = sprintf("%.0f", $flikc) . "C";
                  $flikf = "$two[1]F";
              }
              preg_match('/<\/flik>\s*<t>(.+)\<\/t>/', $page, $cond);
              preg_match('/<s>(.+)<\/s>/', $page, $windz);
              if (preg_match_all('/<\/d>\s*<t>(.+)<\/t>\s*<\/wind>/s', $page, $windd)) {
                  $winddir = strtoupper($windd[1][0]);
                  $abbrs = array('N' => 'North', 'S' => 'South', 'E' => 'East', 'W' => 'West', 'SW' => 'Southwest', 'SE' => 'Southeast', 'NW' => 'Northwest', 'NE' => 'Northeast', 'SSW' => 'South-Southwest', 'SSE' => 'South-Southeast', 'NNW' => 'North-Northwest', 'NNE' => 'North-Northeast', 'WNW' => 'West-Northwest', 'WSW' => 'West-Southwest', 'ENE' => 'East-Northeast', 'ESE' => 'East-Southeast', );
                  
                  $winddir = (!empty($abbrs[$winddir]) ? $abbrs[$winddir] : $winddir);
                  
                  if ($windz[1] != 'calm') {
                      $wind = "$winddir at $windz[1] MPH";
                  } else {
                      $wind = 'calm';
                  }
              }
              
              preg_match('/<hmid>(.+)<\/hmid>/', $page, $hmid);
              preg_match('/<ud>.<i>(.+)<\/i>.<t>(.+)<\/t>.<\/uv>/', $page, $uv);
              preg_match('/<r>(.+)<\/r>/', $page, $r);
			  preg_match('/<sunr>(.*)<\/sunr>/', $page, $sunrise);
			  preg_match('/<suns>(.*)<\/suns>/', $page, $sunset);
              preg_match('/<d>(.+)<\/d>/', $page, $d);
              if ($uv[1] > 2) {
                  $uvi = ", UV Index: $uv[1] ($uv[2])";
              } else {
                  unset($uv);
              }
              
              if (!$loc) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: I was unable to get the weather for \"{$row['w_loc']}\".");
                  return;
              }
              
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Weather for $loc[1] as of $lastup[1]\x02: Condition: $cond[1], Temperature: $tempf ($tempc), Feels like: $flikf ($flikc), Wind: $wind, Barometer: $r[1] and $d[1], Humidity: $hmid[1]%$uvi \x02Sunrise\x02: {$sunrise[1]} \x02Sunset\x02: {$sunset[1]}");
          } elseif (count($data->messageex) >= '2') {
              $locz = implode(' ', array_slice($data->messageex, 1));
              
              if (!$locz) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a location.");
                  return;
              }
              
              if (!preg_match("/^\d{4,5}$|^.+$/", $locz)) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Your location wasn't valid.");
                  return;
              }
              
              $zip = $this->parse_search($irc, $data->nick, $locz);
              
              if (preg_match('/There were multiple matches for your search./i', $zip)) {
                  return;
              }
              
              
$page = $toad->_web("http://xoap.weather.com/weather/local/" . urlencode($zip) . "?cc=*&link=xoap&prod=xoap&day1=5&par=" . urlencode($par) . "&key=" . urlencode($key) . "");              
preg_match('/<dnam>(.+)\<\/dnam>/', $page, $loc);
              preg_match('/<lsup>.+? (.+?)\<\/lsup>/', $page, $lastup);
              if (preg_match('/<tmp>(.+?)<\/tmp>/', $page, $one)) {
                  $tempc = (($one[1] - 32) / 1.8);
                  $tempc = sprintf("%.0f", $tempc) . "C";
                  $tempf = "$one[1]F";
              }
			  preg_match('/<sunr>(.*)<\/sunr>/', $page, $sunrise);
			  preg_match('/<suns>(.*)<\/suns>/', $page, $sunset);
              if (preg_match('/<flik>(.+)<\/flik>/', $page, $two)) {
                  $flikc = (($two[1] - 32) / 1.8);
                  $flikc = sprintf("%.0f", $flikc) . "C";
                  $flikf = "$two[1]F";
              }
              preg_match('/<\/flik>\s*<t>(.+)\<\/t>/', $page, $cond);
              preg_match('/<s>(.+)<\/s>/', $page, $windz);
              if (preg_match_all('/<\/d>\s*<t>(.+)<\/t>\s*<\/wind>/s', $page, $windd)) {
                  $winddir = strtoupper($windd[1][0]);
                  $abbrs = array('N' => 'North', 'S' => 'South', 'E' => 'East', 'W' => 'West', 'SW' => 'Southwest', 'SE' => 'Southeast', 'NW' => 'Northwest', 'NE' => 'Northeast', 'SSW' => 'South-Southwest', 'SSE' => 'South-Southeast', 'NNW' => 'North-Northwest', 'NNE' => 'North-Northeast', 'WNW' => 'West-Northwest', 'WSW' => 'West-Southwest', 'ENE' => 'East-Northeast', 'ESE' => 'East-Southeast', );
                  
                  $winddir = (!empty($abbrs[$winddir]) ? $abbrs[$winddir] : $winddir);
                  
                  if ($windz[1] != 'calm') {
                      $wind = "$winddir at $windz[1] MPH";
                  } else {
                      $wind = 'calm';
                  }
              }
              
              preg_match('/<hmid>(.+)<\/hmid>/', $page, $hmid);
              preg_match('/<ud>.<i>(.+)<\/i>.<t>(.+)<\/t>.<\/uv>/', $page, $uv);
              preg_match('/<r>(.+)<\/r>/', $page, $r);
              preg_match('/<d>(.+)<\/d>/', $page, $d);
              if ($uv[1] > 2) {
                  $uvi = ", UV Index: $uv[1] ($uv[2])";
              } else {
                  unset($uv);
              }
              if (!$loc) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: I was unable to get the weather for \"$locz\".");
                  return;
              }
              $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Weather for $loc[1] as of $lastup[1]\x02: Condition: $cond[1], Temperature: $tempf ($tempc), Feels like: $flikf ($flikc), Wind: $wind, Barometer: $r[1] and $d[1], Humidity: $hmid[1]%$uvi \x02Sunrise\x02: {$sunrise[1]} \x02Sunset\x02: {$sunset[1]}");
              $query1 = "SELECT w_nick FROM weather WHERE w_nick='{$data->nick}'";
              $result1 = $toad->query($query1);
              
              if (!$result1) {
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: Could't get data from database.");
                  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: " . mysql_error());
                  return;
              }
              
              $numrows = mysql_num_rows($result1);
              
              if ($numrows == '0') {
                  $query2 = "INSERT INTO weather (`w_loc`, `w_nick`) VALUES ('{$zip}','{$data->nick}')";
                  $result2 = $toad->query($query2);
                  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$prefix}weather\x02: \x02$loc[1]\x02 - now set as your default location.");
              }
          } else {
              $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$prefix}weather\x02: The command you provided doesn't exist, type {$toad->prefix}help weather.");
              return;
          }
      }
  }
?>