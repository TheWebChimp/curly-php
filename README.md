curly-php
=========

Easy to use, general purpose CuRL wrapper

### Basic usage

You'll need to include `cacher.inc.php` [from here](https://github.com/biohzrdmx/cacher-php) before including `curly.inc.php`.

	// Just grab a new instance, the boolean parameter controls caching:
	$curly = Curly::newInstance(false)
		->setMethod('get')
		->setURL('http://api.icndb.com/jokes/random')
		->setParams([ 'limitTo' => 'nerdy' ])
		->execute();
	// Then just get the response, you may even specify the format ('plain' or 'json')
	$res = $curly->getResponse('json');
	// Then just get the response, you may even specify the format ('plain' or 'json')
	$res = $curly->getResponse('json');
	// And just use the returned data
	if ($res && $res->type == 'success') {
		# Error checking may vary, here the API sets a } `type` member
		echo $res->value->joke;
	} else {
		echo 'API error: ' . $curly->getError();
	}

For `HTTPS` just grab a copy of `cacert.pem` [from here](https://curl.haxx.se/docs/caextract.html) and drop it on the same folder where the `curly.inc.php` file is located.

### Licensing

This software is released under the MIT license.
