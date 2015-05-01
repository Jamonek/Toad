<?php
require_once 'SmartIRC.php';

class Toad {

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

$irc->connect("irc.freenode.net", "6667");
$irc->setCtcpVersion('Toad IRC Bot v1.1');
$irc->login('Toad', 'Toad v1.1', 8 ,'Toad');
$irc->mode("$irc->_nick", '+BT');
$irc->join(array('#ncat'));
$toad->get_users();
$irc->listen();
$irc->disconnect();
?>