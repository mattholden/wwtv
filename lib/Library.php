<?

/**
 * Base class for libraries such as databases, API wrappers, etc. with a config array
 * defined in Secrets.php. Instances of Libraries will be maintained in the Config singleton.
 * 
 * @author Matt Holden
 */
class Library {

    /** @var the config array from Secrets.php */
    protected $config;

    /** @var Boolean to indicate if we're using the sandbox version of the library. 
     * A 'null' indicates no sandbox implementation is available for this library.
    */
    protected $isSandbox = null;
    
    /**
     * Construct the library
     * @param $config array An associative array of secrets and other config options 
     */
	public function __construct($config) {
		$this->config = $config;
	}

    /**
     * In PHP 5.5 and up, prevents var_dump from accidentally spilling secrets if called on a library
     * @return string A message telling the client that you can't have any information.
     */
	public function __debugInfo() { 
        return "Debug info for libraries with secrets is forbidden."; 
    }

    /**
     * Accessor for the configuration array.
     * This is very, very rarely used and should trigger additional security scrutiny if you see it
     * called. 
     * @return the configuration array, possibly including secrets. 
     */
	public function getConfig() {
        if ($this->isSandbox === null)
            return $this->config;

        else if (isset($this->config["sandbox"]) && $this->isSandbox)
            return $this->config["sandbox"];
        else if (isset($this->config["production"]) && !$this->isSandbox)
            return $this->config["production"];
        else
            return $this->config;
   }
}
