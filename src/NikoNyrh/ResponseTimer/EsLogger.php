<?php
namespace NikoNyrh\ResponseTimer;

class EsLogger implements ILogger {
	protected $client;
	protected $index;
	
	public function __construct(
		$client, $index
	) {
		$this->client = $client;
		$this->index  = $index;
	}
	
	protected function indexExists($index) {
		return $this->client->indices()->exists(array(
			'index' => $index
		));
	}
	
	protected function createIndex() {
		if ($this->indexExists($this->index)) {
			return;
		}
		
		$type = function ($t, $extra=array()) {
			if ($t == 'string') {
				$extra['index'] = 'not_analyzed';
				$extra['norms'] = array('enabled' => false);
			}
			
			return array_merge(array(
				'fielddata' => array('format' => 'doc_values'),
				'type'      => $t
			), $extra);
		};
		
		$this->client->indices()->create(array(
			'index' => $this->index,
			'body'  => array(
				'settings' => array(
					'number_of_shards'   => 1,
					'number_of_replicas' => 0
				),
				'mappings' => array(
					'log' => array(
						'_all'    => array('enabled' => false),
						'_source' => array('enabled' => true),
						'properties' => array(
							'time'     => $type('date'),
							'duration' => $type('float'),
							'route'    => $type('string'),
							'host'     => $type('string'),
							'method'   => $type('string'),
							'get'      => $type('string'),
							'post'     => $type('string'),
							'session'  => $type('string'),
							'size'     => $type('integer'),
							'route_1'  => $type('string'),
							'route_2'  => $type('string'),
							'route_3'  => $type('string'),
							'route_4'  => $type('string'),
							'route_5'  => $type('string'),
							'route_6'  => $type('string'),
							'meta'     => $type('string'),
						)
					)
				)
			)
		));
	}
	
	public function store(array $result) {
		$this->createIndex();
		
		$this->client->index(array(
			'index' => $this->index,
			'type'  => 'log',
			'body'  => $result
		));
	}
}
