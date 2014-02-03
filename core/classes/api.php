<?php
/**
 * Description of route
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */

class Api extends Config
{
	protected $prefix='';
	protected $isAjax=false;
	protected $JSON=null;
	
	protected $params=null;
	
	/**
	 * @var Token
	 */
	private $token;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->token = new Token();
		
		$class = get_class($this);
		
		$this->logger = new Logger($class);

		$ns = explode('\\', $class);
		
		if (isset ($ns[1])) {
			//We are using namespace
			$this->prefix = strtolower($ns[1]);
		}
	}
	
	final public function run()
	{
		$rawPost = file_get_contents('php://input');
		if ($rawPost) {
			$json = json_decode($rawPost, true);
			if (is_array ($json)) {
				$_POST = array_merge($_POST, $json);
			}
		}
		$this->fireHooks();
	}
	
	final public function end()
	{
		$this->after();
	}
	
	/**
	 * Fire hooks
	 * @return boolean 
	 */
	protected function fireHooks()
	{
		$this->before();
		
		// Let do some hooks
		if (!empty($_FILES)) {
			$this->_FILES();
		}
		if (!empty($_GET)) {
			$this->_GET();
		}
		if (!empty($_POST)) {
			$this->_POST();
		}
		if (\Url::isSuccess()) {
			$this->_SUCCESS();
		}
		if (\Url::isError()) {
			$this->_ERROR();
		}
		if(
			!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
		) {
			$this->isAjax = true;
			$this->_AJAX();
			exit;
		}
		
	}
	
	public function __destruct() {
		;
	}
	
	protected function before()
	{
		
	}
	
	protected function after()
	{
		
	}
	
	/**
	 * Hook for AJAX 
	 */
	protected function _AJAX()
	{
		
	}
	
	/**
	 * Hook for POST 
	 */
	protected function _POST()
	{
		
	}
	
	/**
	 * Hook for FILES upload
	 */
	protected function _FILES()
	{
		
	}

	/**
	 * Hook for GET
	 */
	protected function _GET()
	{
		
	}
	
	/**
	 * Hook for success 
	 */
	protected function _SUCCESS()
	{
		
	}
	
	/**
	 * Hook for error
	 */
	protected function _ERROR()
	{
		
	}
	
	/**
	 * Hook for index method
	 */
	public function _INDEX()
	{
		
	}
	
	/**
	 * Set language for TOKEN
	 * @param String $lang
	 * @return void
	 */
	public function setLang($lang)
	{
		$this->token->setLang($lang);
	}
	
	/**
	 * Return current language for TOKEN
	 * @return String
	 */
	public function getLang()
	{
		return $this->token->getLang();
	}
	
	/**
	 * Return TOKEN value
	 * @param String $name
	 * @return String
	 */
	public function t($name)
	{
		if (is_array ($name)) {
			$ret = array ();
			foreach ($name as $k=>$v) {
				$ret[$k] = $this->token->get($v);
			}
		} else {
			$ret = $this->token->get($name);
		}
		return $ret;
	}
	
	/**
	 * Outputs token value
	 * @param String $name 
	 */
	public function e($name)
	{
		echo $this->t($name, false);
	}
	
	/**
	 * External method to start rendering ALL 
	 */
	public function render()
	{
		echo $this->export();
	}
	
	/**
	 * Return rendered HTML or set new one
	 * @param String $html Optional. 
	 * @return String
	 */
	public function export($params=null)
	{
		if ($params !== null) {
			if ($this->JSON) {
				$a = json_decode($this->JSON, true);
				$params = array_merge($a, $params);
			}
			$this->JSON = json_encode($params);
		}
		return $this->JSON;
	}
	
}
