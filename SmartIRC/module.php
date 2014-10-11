<?php
  class module extends Net_SmartIRC_base
  {

	private function loadmodule($filename) {
	
	$fileData = file_get_contents($filename);
	
	if($fileData == false) {
	$this->log("DEBUG_MODULES: Unable to load module '$filename'");
	return;
	}

}

?>