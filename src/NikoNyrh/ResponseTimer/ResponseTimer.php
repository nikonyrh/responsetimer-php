<?php
namespace NikoNyrh\ResponseTimer;

class ResponseTimer {
	protected $logger;
	protected $start;
	
	protected function microtime() {
		return microtime(true);
	}
	
	public function __construct(ILogger $logger) {
		$this->logger = $logger;
		$this->start  = $this->microtime();
	}
	
	public function store(
		array $_server,
		array $_get,
		array $_post,
		$responseSize=null,
		array $meta=null
	) {
		$doc = array(
			'time'     => round(1000.0*$_server['REQUEST_TIME_FLOAT']),
			'duration' => $this->microtime() - $this->start,
			'route'    => $_server['DOCUMENT_URI'],
			'host'     => $_server['HTTP_HOST'],
			'method'   => $_server['REQUEST_METHOD'],
			'get'      => http_build_query($_get),
			'post'     => empty($_post) ? "" : sha1(json_encode($_post))
		);
		
		if (isset($_server['HTTP_COOKIE'])) {
			parse_str($_server['HTTP_COOKIE'], $session);
			if (isset($session['PHPSESSID'])) {
				$doc['session'] = $session['PHPSESSID'];
			}
		}
		
		if ($meta !== null) {
			$doc['meta'] = json_encode($meta);
		}
		
		if ($responseSize !== null) {
			$doc['size'] = $responseSize;
		}
		
		foreach (preg_split('_/+_', $doc['route'], -1, PREG_SPLIT_NO_EMPTY) as $i => $route) {
			// Record first 6 levels
			if ($i == 6) {
				break;
			}
			
			$doc[sprintf('route_%d', $i+1)] = $route;
		}
		
		$this->logger->store($doc);
		return $doc;
	}
}
