<?php
  class Net_SmartIRC_module_calc
  {
      var $name = array(name => 'calc', access => '0', from => 'channel');
      var $version = '$Revision$';
      var $description = 'Calculator.';
      var $author = 'Monie';
      var $license = 'GPL';
      var $access = '0';
      var $actionids = array();
      
      function module_init(&$irc)
      {
          $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^%calc/', $this, 'calc');
      }
      
      function module_exit(&$irc)
      {
          foreach ($this->actionids as $value) {
              $irc->unregisterActionid($value);
          }
      }
      
      
      function calc(&$irc, &$data)
      {
          global $toad;
          
          $calc = implode(' ', array_slice($data->messageex, 1));
          
          if (!$calc) {
              $irc->message(SMARTIRC - TYPE_CHANNEL, $data->channel, "\x02Error\x02: You didn't provide a calculation.");
              return;
          }
          
          if(preg_match('/[0-9\+\-\*\/\%\.\s]/',$calc)) {
          eval('$math = '.$calc.';');
          
          if(!$math) {
          $toad->privmsg($data->channel, "\x02{$toad->prefix}calc\x02: Was unable to do calculation, type {$toad->prefix}help calc." );
          return;
          }
          
          $toad->privmsg($data->channel, "$calc = \x02$math\x02");
          } else {
          $toad->privmsg($data->channel, "\x02{$toad->prefix}calc\x02: Couldn't do calculation, type {$toad->prefix}help calc." );
          return;
          }
        
      }
      
      function do_calcs($calc)
	{
		$test = str_replace(".", "", $calc);
		$test = str_replace(",", "", $test);
		$test = str_replace("+", "", $test);
		$test = str_replace("-", "", $test);
		$test = str_replace("*", "", $test);
		$test = str_replace("/", "", $test);
		$test = str_replace("\\", "", $test);
		$test = str_replace("x", "", $test);
		$test = str_replace("X", "", $test);
		$test = str_replace("%", "", $test);
		$test = str_replace("(", "", $test);
		$test = str_replace(")", "", $test);
		$test = str_replace(" ", "", $test);

		if (is_numeric($test))
		{
			$result = "";
			$calc = str_replace("x", "*", $calc);
			$calc = str_replace("X", "*", $calc);
			$calc = str_replace("\\", "/", $calc);
			$calc = "\$result = " . $calc . ";";
			eval($calc);

			if (!empty($result) || $result == 0) 
			{
				return $result;
			} else {
			return "Wrong syntax, please /tell <toadname> <pre>help <pre>calc";    
      }
 }
} 
}

?>