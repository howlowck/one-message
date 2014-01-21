<?php namespace Howlowck\OneMessage\Facades;

use Illuminate\Support\Facades\Facade;

class OneMessage extends Facade {
	protected static function getFacadeAccessor() { return 'onemessage'; }
}