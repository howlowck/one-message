<?php namespace Howlowck\OneMessage;

use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Contracts\ArrayableInterface;

class OneMessage implements ArrayableInterface {
	protected $error;
    protected $success;
    protected $info;
    protected $flashData;
    protected $purged;
    protected $sessionName = 'OneMessage';

    public function __construct($session)
    {
    	$this->session = $session;
    	$this->purge();

        if ($this->session->has($this->sessionName)) {
            $this->populateFromSession();
        }
    }

    public function hasError()
    {
        return ! $this->error->isEmpty();
    }

    public function hasSuccess()
    {
        return ! $this->success->isEmpty();
    }

    public function hasInfo()
    {
        return ! $this->info->isEmpty();
    }

    public function addError($messages, $flash = false)
    {
        $this->addContent('error', $messages, $flash);
    }

    public function addSuccess($messages, $flash = false)
    {
        $this->addContent('success', $messages, $flash);
    }

    public function addInfo($messages, $flash = false)
    {
        $this->addContent('info', $messages, $flash);
    }

    public function getError($key = null)
    {
        return $this->getMessage('error', $key);
    }

    public function getSuccess($key = null)
    {
        return $this->getMessage('success', $key);
    }

    public function getInfo($key = null)
    {
        return $this->getMessage('info', $key);
    }

    protected function addContent($name, $messages, $flash)
    {
        if ($flash) {
        	$this->addFlash($name, $messages);
        } else {
        	$this->addMessage($name, $messages);
        }
    }

    /**
     * Adding a Message with a given type
     * @param $name
     * @param mixed $messages
     */
    protected function addMessage($name, $messages)
    {
    	if ($messages instanceof MessageBag) {
    		$messages = $messages->all();
    	}

        foreach( $messages as $key => $message) {
            $this->$name->put($key , $message);
        }
    }

    protected function addFlash($name, $messages)
    {
    	if ($messages instanceof MessageBag) {
    		$messages = $messages->all();
    	}
        foreach( $messages as $key => $message) {
            $this->flashData[$name]->put($key, $message);
        }
        $this->flash();
    }

    protected function getMessage($name, $key = null)
    {
        if ($key) {
            return $this->$name->get($key);
        }
        return $this->$name->all();
    }

    protected function flash()
    {
        $data = [];
        $data['error'] = $this->flashData['error']->all();
        $data['success'] = $this->flashData['success']->all();
        $data['info'] = $this->flashData['info']->all();
        $this->session->flash($this->sessionName, $data);
    }

    protected function populateFromSession()
    {
    	$messages = $this->session->get($this->sessionName);
    	$this->error = with(new Collection())->make($messages['error']);
    	$this->success = with(new Collection())->make($messages['success']);
    	$this->info = with(new Collection())->make($messages['info']);
    	$this->purged = false;
    }

    protected function purge()
    {
        $this->error = new Collection();
        $this->success = new Collection();
        $this->info = new Collection;
        $this->flashData = [
            'error' => new Collection(),
            'success' => new Collection(),
            'info' => new Collection(),
        ];
        $this->purged = true;
    }
    public function toArray()
    {
    	return [
    		'error' => getMessage('error'),
    		'success' => getMessage('success'),
    		'info' => getMessage('info')
    	];
    }
}

