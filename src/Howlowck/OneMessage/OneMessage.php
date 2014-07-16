<?php namespace Howlowck\OneMessage;

use Illuminate\Session\Store;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Contracts\ArrayableInterface;

class OneMessage implements ArrayableInterface {

	protected $error;
	protected $success;
	protected $info;

	protected $messages;
	protected $flashMessages;
	protected $flashData;
	protected $purged;
	protected $sessionName = 'OneMessage';

	protected $config;

	public function __construct( Store $session, Repository $config)
	{
		$this->session = $session;
		$this->config = $config;

		$this->purge();

		if ($this->session->has($this->sessionName)) {
			$this->populateFromSession();
		}
	}

  public function __call( $method, $params ) {

    if( $var = $this->_validMessageMethod( $method, 'get{var}' ) ){
      return $this->_getMessageVar( $var, $params );
    }

    if( $var = $this->_validMessageMethod( $method, 'add{var}' ) ){
      return $this->_addMessageVar( $var, $params );
    }

    if( $var = $this->_validMessageMethod( $method, 'has{var}' ) ){
      return $this->_hasMessageVar( $var, $params );
    }

  }

	private function _validMessageMethod( $method, $method_patthern ){
		$match = NULL;
		$var_pattern = '(?P<var>[A-Za-z0-9]+)';
		preg_match('/^' . str_replace( '{var}', $var_pattern, $method_patthern ) . '$/', $method, $match );
		$var = ( isset( $match['var'] ) ? snake_case( $match['var'] ) : false );

		return $var && array_key_exists( $var, $this->messages ) ? $var : false;
	}

  private function _getMessageVar( $messageType, $params ){
		$key = isset( $params[0] ) ? $params[0] : NULL;
    return $this->getMessage( $messageType, $key );
  }

  private function _addMessageVar( $messageType, $params ){
		$messages = isset( $params[0] ) ? $params[0] : NULL;
		$flash = isset( $params[1] ) ? $params[1] : false;

		if( ! $messages ){
			throw new InvalidArgumentException("Method add$messageType 2nd argument is invalid  (Array or MessageBag expected)");
		}

		$this->addContent( $messageType, $messages, $flash );
    return true;
  }

	private function _hasMessageVar( $messageType, $params ){
		return !$this->messages[ $messageType ]->isEmpty();
	}

	public function getMessageTypes(){
		return (array) $this->config->get('one-message::message_types');
	}

	protected function addContent($name, $messages, $flash)
	{
		if ($messages instanceof MessageBag) {
				$messages = $messages->all();
		} else {
				$messages = (array) $messages;
		}

		if ($flash) {
			$this->addFlash($name, $messages);
		} else {
			$this->addMessage($name, $messages);
		}
	}

	protected function addMessage($name, $messages)
	{
		foreach( $messages as $key => $message) {
			$this->messages[$name]->put($key, $message);
		}
	}

	protected function addFlash($name, $messages)
	{
		foreach( $messages as $key => $message) {
				$this->flashMessages[$name]->put($key, $message);
		}

		$this->flash();
	}

	protected function flash()
	{
		$data = [];

		foreach( $this->flashMessages as $messageType => $messages ){
			$data[ $messageType ] = $messages->all();
		}

		$this->session->flash($this->sessionName, $data);
	}

	protected function populateFromSession()
	{
		$messages = $this->session->get( $this->sessionName );

		foreach( $messages as $messageType => $messages ){
			$this->messages[ $messageType ] = with(new Collection())->make( $messages );
		}

		$this->purged = false;
	}

	protected function purge()
	{
		$this->messages = array();
		$this->flashMessages = array();

		foreach( $this->getMessageTypes() as $messageType ){
			$this->messages[ $messageType ] = new Collection();
			$this->flashMessages[ $messageType ] = new Collection();
		}

		$this->purged = true;
	}

	protected function getMessage($name, $key = null)
	{
		if ($key) {
			return $this->messages[ $name ]->get($key);
		}
		return $this->messages[ $name ]->all();
	}

	public function toArray()
	{
		$return = [];
		foreach( $this->getMessageTypes() as $messageType ){
			$return[ $messageType ] = $this->getMessage( $messageType );
		}
		return $return;
	}
}

