<?php
class Net_SmartIRC_module_switch
{
	public $name = array(name => 'switch', access => '3', from => 'channel');
    public $version = '$Revision$';
    public $description = 'Channel disabler/enabler.';
    public $author = 'Monie';
    public $license = 'GPL';
    public $access = '0';
      
    public $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%switch', $this, 'switchz');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      function switchz(&$irc, &$data)
      {
      
      	global $toad;
if (((($toad->users[$data->nick]['loggedin'] == false) && ($toad->users[$data->nick]['rank'] < TOAD_RANK_ADMIN))) || (!$irc->isOpped($data->channel, $data->nick) && !$irc->isHalfoped($data->channel, $data->nick) && !$irc->isAdmined($data->channel, $data->nick) && !$irc->isOwnered($data->channel, $data->nick))) {		
$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You do not have sufficient access to this command, \x02{$data->nick}\x02.");
		return;
		}
		
		
      	$command = $data->messageex[1];
      	
		//$command_sql =
      	$command_array = array(
      	'help',
      	'lovecalc',
      	'8ball',
      	'fortune',
      	'reverse',
      	'uptime',
      	'kick',
		'weather',
		'quote',
		'epenis');
      	
      	$command = str_replace('%', '', $command);
      	
      	if(in_array($command, $command_array))
      	{
      	
      	$file = "./data/switch/".strtolower($irc->_address)."-".strtolower($data->channel)."-".strtolower($command).".txt";
      	if(file_exists($file))
      	{
      		$fh = fopen($file, 'r');
      		
      		$status = fgets($fh);
      		
      		fclose($fh);
      		
      		if($status == strtolower('disabled'))
      		{
      			$new_status = 'enabled';
      			
      			$nfh = fopen($file, 'w');
      	
				fwrite($nfh, $new_status);
				
				fclose($nfh);
				
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02".strtolower($command)."\x02: Has been \x02enabled\x02 for \x02{$data->channel}\x02.");
				
			} elseif($status == strtolower('enabled')) {
			
				$new_status = 'disabled';
      			
      			$nfh = fopen($file, 'w');
      	
				fwrite($nfh, $new_status);
				
				fclose($nfh);
				
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02".strtolower($command)."\x02: Has been \x02disabled\x02 for \x02{$data->channel}\x02.");
			
			}
			
		} else {
		
		$new_status = 'disabled';
      			
      			$nfh = fopen($file, 'w');
      	
				fwrite($nfh, $new_status);
				
				fclose($nfh);
				
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02".strtolower($command)."\x02: Has been \x02disabled\x02 for \x02{$data->channel}\x02.");
		
		}
		
	} else {
	
	$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: That command does not exist.");
	return;
	
	}
  }
}

?>