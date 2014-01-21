# OneMessage

For Easy Management of Messages in One singleton.  Anywhere in the process you want to add a message just add it in the singleton and in the view just use it to display your messages.

## Feature:
1. Handles Flash or Same-Request Messages
2. Handles Validation Messages
3. Easy API

## Setup:

in `app/config/app.php`

- Add Service provider
add `'Howlowck\OneMessage\OneMessageServiceProvider'` in `providers` array

- Add Facade
add `'OneMessage'	  => 'Howlowck\OneMessage\Facades\OneMessage'` in `aliases` array

## Usage:

There are three type of messages: Error, Success, and Info.

**Add Message**

	OneMessage::addError(['authorization' => 'You are unauthorized!!!']);

  ***or you can throw a MessageBag in there***

	$v = Validator::make($data, $rules);
	if ($v->fails()) {
		OneMessage::addError($v->errors());
	}
	

**Add Message for Flash**  
When adding to the flash data, it will not be available in the current request.

	OneMessage::addError(['authorization' => 'You are unauthorized!!!'], true);


**get Message**

	OneMessage::getError();

  ***or get a specific message with key***

	OneMessage::getError('authorization');