<?php
/**
 * Backpack API Datasource
 *
 * PHP Version 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       datasources
 * @subpackage    datasources.models.datasources
 * @since         CakePHP Datasources v 0.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 *
 *
 *
 * Define a connection in config/database.php:
 *	var $backpack = array(
 *		'datasource' => 'backpack',
 *		'host' => 'you.backpackit.com',		// Your Backpack URL
 *		'ssl' => true,						// SSL now required for all accounts
 *		'token' => 'token_from_backpack_my_info_page',
 *		'timeout' => 10,					// http connection timeout
 *	);
 *
 *
 * Define a model with the following vars set:
 *	var $useTable = 'pages';				// Instructs the backpack_datasource to connect to the "pages" API for this model. See BackpackSource::$_schema for available models to emulate.
 *	var $useDbConfig = 'backpack';			// Use the BackpackDatasource
 *
 *
 * WARNING: BackpackDatasource::read() is currently hardcoded to always return "all" results!
 *
 *
 * Working areas marked with "###TODO" comments.
 */


/**
 * BackpackSource
 *
 * @package datasources
 * @subpackage datasources.models.datasources
 */
class BackpackSource extends DataSource {
    
/**
 * Description
 *
 * @var string
 * @access public
 */
	public $description = '37signals Backpack API DataSource';

/**
 * Schema
 *
 * @var array
 * @access public
 */
	protected $_schema = array(
		'pages' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'title' => array(
				'type' => 'string',
				'null' => false,
				'key' => '',
				'length' => 255
			),
			'scope' => array(
				'type' => 'string',
				'null' => false,
				'key' => '',
				'length' => 255
			),
		),
		//###TODO: Define the rest of the schemata.
		'lists' => array(
		),
		'list_items' => array(
		),
		'notes' => array(
		),
		'separators' => array(
		),
		'tags' => array(
		),
		'reminders' => array(
		),
		'emails' => array(
		),
		'statuses' => array(
		),
		'journal_entries' => array(
		),
		'users' => array(
		),
		'bookmarks' => array(
		),
	);

/**
 * Connection status
 *
 * @var boolean
 * @access public
 */
	public $connected = false;

/**
 * Default configuration
 *
 * @var array
 * @access public
 */
	public $_baseConfig = array(
		'host' => '',
		'ssl' => true,
		'token' => '',
		'timeout' => 20,
	);

/**
 * HttpSocket instance
 *
 * @var HttpSocket
 * @access public
 */
	public $connection = null;


/**
 * Constructor
 *
 * @param array $config An array defining the configuration settings
 * @access public
 */
	public function __construct($config) {
		parent::__construct($config);
		App::import('Core', 'HttpSocket');
		App::import('Core', 'Xml');
		$this->connect();
	}

/**
 * Instantiates an HttpSocket
 *
 * @return boolean True on success, false on failure
 * @access public
 */ 
	public function connect() {
		$this->connection = new HttpSocket(array(
								'timeout' => $this->config['timeout'],
							));

		if ($this->connection) {
			$this->connected = true;
		}
		return $this->connected;
	}

/**
 * Sets the HttpSocket instance to null
 *
 * @return boolean True
 * @access public
 */
	public function close() {
		$this->connection = null;
		$this->connected = false;
		return true;
	}

/**
 * Returns the schema of the Backpack "table" matching $model's alias
 *
 * @return array Array containing table column information.
 * @access public
 */
public function describe($model) {
	if(isset($this->_schema[$model->alias])) {
		return $this->_schema[$model->alias];
	}
	
	return array();
}

/**
 * Returns the available Backpack "tables"
 *
 * @return array List of backpack tables
 * @access public
 */
	public function listSources() {
		return array_keys($this->_schema);
	}
	
/**
 * Create a new Backpack record based on the table name of $model
 *
 * To-be-overridden in subclasses.
 *
 * @param Model $model The Model to be created.
 * @param array $fields An Array of fields to be saved.
 * @param array $values An Array of values to save.
 * @return boolean success
 * @access public
 */
	function create(&$model, $fields = null, $values = null) {
		return false;
	}


/**
 * Fetch records from the Backpack table associated with the provided $model table.
 *
 * @param Model $model The model being read.
 * @param array $queryData An array of query data used to find the data you want
 * @return mixed
 * @access public
 */
	function read(&$model, $queryData = array()) {
		if(!$this->connected) {
			$this->log('not connected');
			return 'happy';
		}
		
		// Simple version
		
		//###DEBUG  ###TODO: Process the options from $queryData to determine what type of fetch we're doing. (all items, search, one item, etc.)
		$action = 'all';
		$table = $model->table;  //###TODO: Verify against $_schema.
		
		//###TODO: Abstract all this request setup stuff out for all CRUD methods to share. Everything except $body and $request[uri][path] are always the same.
		$options = array(
				'format' => 'tags', 
				'slug' => false, 
			);
		
		$body = array(
				'request' => array(
					'token' => "{$this->config['token']}",
					//'term' => '',  //###TODO: Used for searching. Populate from [conditions].
					),
				);
		$body = new Xml($body, $options);
		
		$request = array(
					'method' => 'POST',
					'uri' => array(
						'scheme' => 'https', //($this->config['ssl'] == true ? 'https' : 'http'),
						'host' => $this->config['host'],
						'path' => "/ws/{$table}/{$action}",
					),
					'header' => array(
						'Content-Type' => 'application/xml',
					),
					'body' => $body->toString(array('cdata' => false)),
				);
	
		// Fire the http request.
		if(($result = $this->connection->request($request)) === false) {
			return false;
		}		
		
		$result = new Xml($result, $options);
		$result = $result->toArray($options);

		// 37s server returned an error.
		if(strpos($result['Response']['success'], 'true') === false) {
			return false;
		}

		// Build a Cake-style array.
		$result = $result['Response']['Pages']['Page'];  //###TODO: Ugh! Hardcoded for pages! Use $model->(table|name|alias) instead!
		
		$results = array();
		foreach($result as $index => $r)
		{
			$results[] = array($model->alias => $r);
		}
		
		//###TODO: Honor "recursive" setting and build out associations. 37s does most of the hard work and usually returns everything together.
		
		return $results;
	}

/**
 * Update a record(s) in the datasource.
 *
 * To-be-overridden in subclasses.
 *
 * @param Model $model Instance of the model class being updated
 * @param array $fields Array of fields to be updated
 * @param array $values Array of values to be update $fields to.
 * @return boolean Success
 * @access public
 */
	function update(&$model, $fields = null, $values = null) {
		return false;
	}
 
/**
 * Delete a record(s) in the datasource.
 *
 * To-be-overridden in subclasses.
 *
 * @param Model $model The model class having record(s) deleted
 * @param mixed $id Primary key of the model
 * @access public
 */
	function delete(&$model, $id = null) {
		if ($id == null) {
			$id = $model->id;
		}
	}
}
