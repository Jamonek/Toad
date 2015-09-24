<?php
class Net_SmartIRC_module_twitter
{
public $name = array(name => 'youtube', access => '0', from => 'channel');
public $version = '$Revision$';
public $description = 'twitter shiter.';
public $author = 'Monie';
public $license = 'GPL';
public $access = '0';
public $actionids = array();

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%twitter', $this, 'twitter');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}


function twitter(&$irc, &$data) {
	global $toad;

	$twitter_user = $data->messageex[1];
	
	if(empty($twitter_user))
	{
		$toad->privmsg($data->channel, 'No user provided.');
		return;
	}
	
	$twitter_user_content = $toad->_web('http://twitter.com/statuses/user_timeline.xml?screen_name='.$twitter_user.'&count=1');
	
	$xml = simplexml_load_string($twitter_user_content);
	
		if($xml->error == 'Not authorized')
		{
			$toad->privmsg($data->channel, 'Account is private.');
			return;
			} elseif($xml->error == 'This method requires authentication.') {
				$toad->privmsg($data->channel, 'Account does not exist.');
				return;
			} else {
			$toad->privmsg($data->channel, "{$xml->status->text} (http://twitter.com/".$xml->status->user->screen_name."/status/".$xml->status->id.")");
		}
	
	//$toad->privmsg($data->channel, print_r($xml, true));

}
}
?>