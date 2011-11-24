<?php

class Settings {
	const SETTING_FILE = "settings.json";
	private $_settings;
	
	public function __construct() {	
		if (file_exists(self::SETTING_FILE)) {
			if (!is_writable(self::SETTING_FILE)) {
				echo '<div class="error">"'.dirname($_SERVER["SCRIPT_FILENAME"]).'/'.self::SETTING_FILE.'" not writable</div>';
				exit;
			}
			$this->_settings = (array)json_decode(file_get_contents(self::SETTING_FILE));
		} else {
			if (!is_writable(".")) {
				echo '<div class="error">"'.dirname($_SERVER["SCRIPT_FILENAME"]).'/" not writable. Change permissions or create "'.dirname($_SERVER["SCRIPT_FILENAME"]).'/'.self::SETTING_FILE.'"</div>';
				exit;
			}
			$this->_settings = array();
		}
    }
    
    public function setDefault($var, $value) {
    	if (!isset($this->_settings[$var])) {
	    	$this->_settings[$var] = $value;
    	}
    }

    public function set($var, $value) {
    	$this->_settings[$var] = $value;
    	$this->save();
    }

    public function get($var) {
		return $this->_settings[$var];
    }

    private function save() {
    	file_put_contents(self::SETTING_FILE, json_encode($this->_settings));
    }
}
