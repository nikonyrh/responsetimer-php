responsetimer-php
--------
A lightweight PHP library to log response times.


Usage
--------
Installation via PHP composer:

```json
{
    "require": {
        "nikonyrh/responsetimer-php": "master"
    },
	"minimum-stability": "dev",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/nikonyrh/responsetimer-php"
        }
    ]
}
```

Usage in a sample PHP API:
```php
// Assuming this file is under "src/" or something similar...
include(__DIR__ . '/../vendor/autoload.php');

// Create the standard ElasticSearch client object
$client = new \Elasticsearch\Client(array(
    'hosts' => array('localhost:9200'),
));

// Use the currently only implementation of ILogger,
// response time counter starts when this object is created.
$indexName = 'api_durations';
$timer     = new \NikoNyrh\ResponseTimer\ResponseTimer(
	new \NikoNyrh\ResponseTimer\EsLogger($esClient, $indexName)
);

// Create the "exit" function
$json = function($data) use ($timer) {
	header("Content-Type: application/json");
	$data = json_encode($data);
	
	$log = $timer->store($_SERVER, $_GET, $_POST, strlen($data));
	// To see the logged document:
	// die(json_encode($log));
	
	die($data);
};

// Create a JSON response, it is automaticly stored to ElasticSearch
$json(array('time' => explode(' ', microtime())));
```

TODO
--------
 - Other persistence options such as MySQL, MongoDB, ...
 - Configuration options
 - Filtering (for example store only requests which took over 10 ms to respond)
 - Unit tests
 - Sample aggregation queries (TOP N, percentiles, ...)
