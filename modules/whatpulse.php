<?php
class Net_SmartIRC_module_whatpulse
{
	public $name = array(name => 'whatpulse', access => '0', from => 'channel');
    public $version = '0.1a';
    public $description = 'WhatPulse API System.';
    public $author = 'Monie';
    public $license = 'GPL';
	public $access = '0';
    public $actionids = array();

	 function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^%whatpulse', $this, 'whatpulse');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
	  
	function whatpulse (&$irc, &$data)
	{
		global $toad;
		
		if(($data->messageex[1] == (('user') || ('u'))) && ($data->messageex[2] == (('save') || ('s')))) {
		
		$uid = $data->messageex[3];
		
			if(empty($uid)) {
			$toad->privmsg($data->channel, "\x02Error\x02: No user id was given to save.");
			return;
			}
		}
	}
}	
		