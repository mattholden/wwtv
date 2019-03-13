<?php

class Logger extends Library {

	private $file = null;

	public function __construct($config = []) { parent::__construct($config); }
	public function __destruct() { if (isset($this->file)) fclose($this->file); }

	private function _openFile() {
		if ($this->file != null)
			return;

		$fileName = $this->getFilename();
		$this->file = fopen($fileName, "a+");
	}

	public function getFilename() {
		return $this->config['log_dir'] . date('Y-m-d', time()) . ".log";
	}

	public function currentURL() {
		$pageURL = 'http';
		if ($_SERVER["SERVER_PORT"] == 443) {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		$pageURL = str_replace("&", "&amp;", $pageURL);
		return $pageURL;
	}

	public function write($string = null) {
		$this->_openFile();

		$write = date('Y-m-d H:i:s', time());
		$write .= " [Guest] ";

		$uri = $this->scrubPasswords($_SERVER['REQUEST_URI']);

		$write .= $_SERVER['REQUEST_METHOD'] . " ";
		$write .= $uri;
		$write .= "\n";

		$write .= $_SERVER['REMOTE_ADDR'] . " - " . $_SERVER['HTTP_USER_AGENT'] . "\n";

		if ($string != null)
			$write .= $string . "\n";

		$write .= "\n";


		fwrite($this->file, $write);
	}

	public function writeCron($string = null) {

		global $argv;

		//if (strpos($argv[0], "gmail_keepalive.php") !== FALSE) return;

		$this->_openFile();
		$write = date('Y-m-d H:i:s', time());

		$write .= " CRON " . $argv[0]. "\n";
		if ($string != null)
			$write .= $string . "\n";

		$write .= "\n";
		fwrite($this->file, $write);
	}

	public function scrubPasswords($_uri) {
		$uri = $_uri;
		$pw = stripos($uri, "password=");
		if ($pw !== false) {
			$pw += 9;
			$endpw = strpos($uri, "&", $pw);
			if ($endpw === false)
				$endpw = strlen($uri);

			$thepw = substr($uri, $pw, ($endpw - $pw));
			$uri = str_replace($thepw, "[redacted]", $uri);
		}
		$pw = stripos($uri, "pass=");
		if ($pw !== false) {
			$endpw = strpos($uri, "&", $pw);
			if ($endpw === false)
				$endpw = strlen($uri);
			$pw += 5;

			$thepw = substr($uri, $pw, ($endpw - $pw));
			$uri = str_replace($thepw, "[redacted]", $uri);
		}
		return $uri;
	}
}
?>
