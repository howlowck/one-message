<?php

use Howlowck\OneMessage\OneMessage;
use Mockery as m;

class OneMessageTest extends \PHPUnit_Framework_TestCase {
	protected $session;
	protected $message;

	public function setUp()
	{
		$this->prepareMessage(false);
	}

	public function tearDown()
	{
		m::close();
	}

	public function testOneMessageCreatedWithNoSessionData()
	{
		$this->assertInstanceOf('Howlowck\OneMessage\OneMessage', $this->message);
	}

	public function testOneMessageCreateWithSessionData()
	{
		$this->redirectSomewhere();		
		$this->assertInstanceOf('Howlowck\OneMessage\OneMessage', $this->message);
	}
	public function testOneMessageSetStringToMessage()
	{
		$this->message->addError('hello!');
		$this->assertEquals(['hello!'], $this->message->getError());
	}
	public function testGetMessages()
	{
		$data = [
			'test' => 'wow cool!'
		];
		$this->message->addError($data);
		$this->message->addSuccess($data);
		$this->message->addInfo($data);

		$this->assertEquals($data, $this->message->getError());
		$this->assertEquals($data, $this->message->getSuccess());
		$this->assertEquals($data, $this->message->getInfo());
	}

	public function testGetFlashMessagesSameRequest()
	{
		$data = [
			'test' => 'wow that is nice'
		];
		$this->session->shouldReceive('flash')->times(3)->andReturn(null);
		$this->message->addError($data, true);
		$this->message->addSuccess($data, true);
		$this->message->addInfo($data, true);

		$this->assertEmpty($this->message->getError());
		$this->assertEmpty($this->message->getSuccess());
		$this->assertEmpty($this->message->getInfo());
	}

	public function testSetFlashMessagesNextRequest()
	{
		$data = [
			'info' => ['something' => 'wow that is nice'],
			'success' => ['yay' => 'good job!'],
			'error' => ['password' => 'wrong password']
		];
		$this->session->shouldReceive('flash')->times(3)->andReturn(null);
		$this->message->addError($data, true);
		$this->message->addSuccess($data, true);
		$this->message->addInfo($data, true);

		$this->redirectSomewhere($data);

		$this->assertEquals($data['error'], $this->message->getError());
		$this->assertEquals($data['info'], $this->message->getInfo());
		$this->assertEquals($data['success'], $this->message->getSuccess());
	}

	public function testGetMessageFromMessageBag()
	{
		$data = [
			'password' => 'this is the wrong password!',
			'email' => 'this is a duplicate email!'
		];
		
		$messageBag = m::mock('Illuminate\Support\MessageBag');
		$messageBag->shouldReceive('all')->once()->andReturn($data);

		$this->message->addError($messageBag);
		$this->assertEquals($data['email'], $this->message->getError('email'));
	}

	public function testGetFlashMessageFromMessageBagWhenSetToFlash()
	{
		$data = [
		'error' => 
			[
			'password' => 'this is the wrong password!',
			'email' => 'this is a duplicate email!'
			],
		'success' => [],
		'info' => []
		];
		
		$messageBag = m::mock('Illuminate\Support\MessageBag');
		$messageBag->shouldReceive('all')->once()->andReturn($data);
		$this->session->shouldReceive('flash')->once()->andReturn(null);
		$this->message->addError($messageBag, true);
		
		$this->redirectSomewhere($data);

		$this->assertEquals($data['error']['email'], $this->message->getError('email'));
	}

	public function testGetNoMessageFromMessageBagWhenNotSetToFlash()
	{
		$data = [
			'error' => [
				'password' => 'this is the wrong password!',
				'email' => 'this is a duplicate email!'
			],
			'success' => [],
			'info' => []
		];
		
		$messageBag = m::mock('Illuminate\Support\MessageBag');
		$messageBag->shouldReceive('all')->once()->andReturn($data);

		$this->message->addError($messageBag);
		
		$this->redirectSomewhere();

		$this->assertNull($this->message->getError('email'));
	}

	protected function redirectSomewhere($data = [])
	{
		$this->prepareMessage(true, $data);
	}

	protected function prepareMessage($hasSession = false, $data = [])
	{
		if (empty($data)) {
			$data = ['info' => [],
				 'success' => [],
				 'error' => []
				];
		}
		$this->session = m::mock('stdClass');
		if ($hasSession) {
			$this->session->shouldReceive('has')->once()->andReturn(true);
			$this->session->shouldReceive('get')->once()->andReturn($data);
		} else {
			$this->session->shouldReceive('has')->once()->andReturn(false);
		}

		$this->message = new OneMessage($this->session);
	}
}