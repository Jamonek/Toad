<?php
class Net_SmartIRC_module_uptime
{
	var $name = array(name => 'uptime', access => '0', from => 'channel');
	var $version = '$Revision$';
	var $description = 'Bot uptime.';
	var $author = 'Monie';
	var $license = 'GPL';
	var $access = '0';
	var $actionids = array();

	function module_init(&$irc)
	{
		$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%uptime/', $this, 'uptime');
	}

	function module_exit(&$irc)
	{
	foreach ($this->actionids as $value) {
		$irc->unregisterActionid($value);
		}
	}
	
	
	function uptime(&$irc, &$data)
		{
 	global $toad;
 	
	if($toad->is_disabled($irc->_network[1][9], $data->channel, 'uptime') == true)
			{
				$irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}uptime\x02: This command is disabled for \x02{$data->channel}\x02.");
				return;
			}	
if((count($data->messageex) == '2') && ($data->messageex[1] == 'load') or ($data->messageex[1] == 'l')) {
$load = shell_exec("uptime");
$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Load\x02: $load" );
} elseif(count($data->messageex) == '1') {
 global $start_time;
 
	      $time = time() - $start_time;

        // Convert the interger to an array of segments
        $seconds = (int)$time;
        
        // Define our periods
        $periods = array (
                    'weeks'    => 604800,
                    'days'     => 86400,
                    'hours'    => 3600,
                    'minutes'  => 60,
                    'seconds'  => 1
                    );

        // Loop through
        foreach ($periods as $period => $value) {
            $count = floor($seconds / $value);

            if ($count == 0) {
                continue;
            }
            $duration[$period] = $count;
            $seconds = $seconds % $value;
        }

        // Loop through the interval array
        foreach ($duration as $key => $value) {
            // Chop the end of the duration key
            $segment_name = substr($key, 0, -1);

            // Create our segment in the format of eg. '4 day'
            $segment = $value.' '.$segment_name;

            // If the duration segment is anything other than 1, we need an 's'
            if ($value != 1) {
                $segment .= 's';
            }
            
            // Plop it into the array
            $array[] = $segment;
        }

        // Implode the array as a string, this way we get commas between each segment
        $timestring = implode(', ', $array);

        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02{$irc->_nick}\x02 has been running for ".$timestring.".");
        } else {
        $irc->message( SMARTIRC_TYPE_NOTICE, $data->nick, "\x02{$toad->prefix}uptime\x02: The command you provided didn't exist, type {$bot->prefix}help uptime." );
        return;
        }
        }
	
	}
	
	?>