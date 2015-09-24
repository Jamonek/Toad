<?php
class Net_SmartIRC_module_xboxdata
{
var $name = array(name => 'xboxdata', access => '0', from => 'channel');
var $version = '$Revision$';
var $description = 'Xbox API Information.';
var $author = 'Monie';
var $license = 'GPL';
var $access = '0';
var $actionids = array();
var $API_URL = 'http://duncanmackenzie.net/services/GetXboxInfo.aspx?GamerTag=';

function module_init(&$irc)
{
$this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%gamertag/', $this, 'gamertag');
}

function module_exit(&$irc)
{
foreach ($this->actionids as $value) {
$irc->unregisterActionid($value);
}
}

function gamertag(&$irc, &$data)
{
global $toad;
$gamertag = implode(' ', array_slice($data->messageex, 1));
	
	if(empty($gamertag)) {
$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02Error\x02: You did not provide a gamertag.");
return;
	}

$gamertag_url = $this->API_URL.trim(urlencode($gamertag));
$file = $toad->_web($gamertag_url);


$xml = simplexml_load_string($file);
if($xml->PresenceInfo->Valid == 'false') {
$toad->notice($data->nick, "\x02Error\x02: The gamertag you provided does not exist.");
return;
} else {
//$toad->notice("Monie", print_r($xml, true));

$data_line = "Gamertag: ".$xml->Gamertag." | Gamerscore: ".$xml->GamerScore."\n";
$data_line .= "Country: ".$xml->Country." | Zone: ".$xml->Zone."\n";
$online_status = ($xml->PresenceInfo->Online == 'true') ? "Online" : "Offline";
$data_line .= "Tier: ".$xml->AccountStatus." | Status: ".$online_status."\n";
$data_line .= "Info: ".$xml->PresenceInfo->Info." | Extra Info: ".$xml->PresenceInfo->Info2."\n";
$data_line .= "Playing: ".$xml->PresenceInfo->Title." | Reputation: ".$xml->Reputation."\n";
$data_line .= "--- Last Game Played ---\n";
$data_line .= "".$xml->RecentGames->XboxUserGameInfo[0]->Game->Name." | Gamerscore: ".$xml->RecentGames->XboxUserGameInfo[0]->GamerScore."/".$xml->RecentGames->XboxUserGameInfo[0]->Game->TotalGamerScore."\n";
$data_line .= "--- End Last Game Played ---\n";
$data_line .= "Profile URL: ".$xml->ProfileUrl."\n";
$toad->notice($data->nick, $data_line);

}
}
}
?>