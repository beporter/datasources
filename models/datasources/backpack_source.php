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
		// Pages -------------------------------
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
				'length' => 255,
			),
			'scope' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
			),
			'email_address' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'readonly' => true,  // Special key to identify columns we can't change through the API.
			),
		),

		// Lists -------------------------------
		'lists' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'name' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
			),
		),

		// ListItems ---------------------------
		'list_items' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'completed' => array(
				'type' => 'int',
				'null' => false,
				'length' => 1,
				'default' => 0,
			),
			'body' => array(
				'type' => 'string',
				'null' => false,
				'length' => 4096,
			),
		),

		// Notes -------------------------------
		'notes' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'title' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
			'created_at' => array(
				'type' => 'datetime',
				'null' => false,
			),
			'body' => array(
				'type' => 'string',
				'null' => false,
				'length' => 4096,
				'default' => '',
			),
		),

		// Separators --------------------------
		'separators' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'name' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'length' => 11,
			),
		),

		// Tags --------------------------------
		'tags' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'name' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
		),

		// Reminders ---------------------------
		'reminders' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'remind_at' => array(
				'type' => 'datetime',
				'null' => false,
			),
			'content' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
			'creator_id' => array(
				'type' => 'integer',
				'null' => false,
				'length' => 11,
			),
		),

		// Emails ------------------------------
		'emails' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'title' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
			'created_at' => array(
				'type' => 'datetime',
				'null' => false,
			),
			'body' => array(
				'type' => 'string',
				'null' => false,
				'length' => 4096,
				'default' => '',
			),
		),

		// Statuses ----------------------------
		'statuses' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'message' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
			'created_at' => array(
				'type' => 'datetime',
				'null' => false,
			),
			'user_id' => array(
				'type' => 'integer',
				'null' => false,
				'length' => 11,
			),
		),

		// JournalEntries ----------------------
		'journal_entries' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'body' => array(
				'type' => 'string',
				'null' => false,
				'length' => 4096,
				'default' => '',
			),
			'created_at' => array(
				'type' => 'datetime',
				'null' => false,
			),
			'updated_at' => array(
				'type' => 'datetime',
				'null' => false,
			),
			'user_id' => array(
				'type' => 'integer',
				'null' => false,
				'length' => 11,
			),
		),

		// Users -------------------------------
		'users' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'name' => array(
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
			'start_page_id' => array(
				'type' => 'integer',
				'null' => false,
				'length' => 11,
			),
		),

		// Bookmarks ---------------------------
		'bookmarks' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'user_id' => array( 
				'type' => 'string',
				'null' => false,
				'length' => 255,
				'default' => '',
			),
			'bookmarkable_type' => array(
				'type' => 'string',
				'null' => false,
				'length' => 50,
				'default' => 'Page',
			),
			'bookmarkable_id' => array(
				'type' => 'integer',
				'null' => false,
				'length' => 11,
			),
			'position' => array(
				'type' => 'integer',
				'null' => false,
				'length' => 11,
			),
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
 * Last HTTP response code
 *
 * @var integer
 * @access protected
 */
	protected $httpResponseCode = null;

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
 * Standard XML formatting options for all requests/responses.
 *
 * @var array
 * @access private
 */
	private $xml_options = array(
		'format' => 'tags', 
		'slug' => false
	);

/**
 * HttpSocket instance
 *
 * @var HttpSocket
 * @access public
 */
	public $Http = null;






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
		$this->Http = new HttpSocket(array(
								'timeout' => $this->config['timeout'],
							));

		if ($this->Http) {
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
		$this->Http = null;
		$this->connected = false;
		return true;
	}

/**
 * Returns the schema of the Backpack "table" matching $model's alias
 *
 * @return array Array containing table column information.
 * @access public
 */
public function describe(&$model) {
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
 * Fetch a list of page records.
 *
 * @return mixed Array of Page records on success, false on HTTP request failure or 37s API failure.
 * @access protected
 */
	protected function pages_list_all() {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/pages/all",
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		// Build a Cake-style array.
		$results = array();
		foreach($result['Response']['Pages']['Page'] as $index => $r)
		{
			$results[] = array($this->model->alias => $r);
		}
		
		return $results;
	}
	
/**
 * Search for pages containing a specific term.
 *
 * @param string $term Search string. HTML characters will be sanitized.
 * @return mixed Array of Page records on success, false on HTTP request failure or 37s API failure.
 * @access protected
 */
	protected function pages_search($term) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/pages/search",
				),
				'body' => (string)$this->requestBody(array('term' => htmlentities($term))),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		// Build a Cake-style array.
		$results = array();
		foreach($result['Response']['Pages']['Page'] as $index => $r)
		{
			$results[] = array($this->model->alias => $r);
		}
		
		return $results;
	}
	
/**
 * Create a new page.
 *
 * @param string $title Title of the new page.
 * @param string $description Initial description of the page. (No longer supported?)
 * @return mixed Array containing the new page, including ID if the page was created, false on failure.
 * @access protected
 */
	protected function pages_create($title, $description = null) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/pages/new",
				),
				'body' => (string)$this->requestBody(array(
									'page' => array(
										'title' => htmlentities($title),
										//'description' => htmlentities($description),
									),
								)),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return array($this->model->alias => $result['Response']['Page']);
	}
	

/**
 * Fetch a single page with all associated data.
 *
 * @param int $page_id The ID number of the page to fetch.
 * @return mixed Array containing the new page, false on failure.
 * @access protected
 */
	protected function pages_show($page_id) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id),
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return array($this->model->alias => $result['Response']['Page']);
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
 * Read wrapper. Outsources actual lookups to API-call-specific helpers.
 *
 * @param Model $model The model being read.
 * @param array $queryData An array of query data used to find the data you want
 * @return mixed
 * @access public
 */
	function read(&$model, $queryData = array()) {
		
		if(!$this->connected) {
			$this->log('not connected');  //###TODO
			return false;
		}
		
		$this->model = $model;


		//###TODO: Parse the $queryData to figure out which helper to call!
		
		//###DEBUG: Hardcoded for testing individual helpers!
		
		// Pages:
		//$results = $this->pages_list_all();
		//$results = $this->pages_search('Mac');
		//$results = $this->pages_create('Test3', 'Description is no longer supported. Use Notes instead.');
		$results = $this->pages_show('836576');


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

/**
 * XML request body generator. Creates a fully-formed XML string to submit to the API.
 *
 * @param array $additional_body_tags Associative array of additional body tags to include along with <token>. Example: array('term' => $searchterm).
 * @return string An XML formatted string containing the <request>...<request> to send to the API.
 * @access public
 */
	function requestBody($additional_body_tags = array()) {
		$body = array(
				'request' => array(
					'token' => "{$this->config['token']}"
				), 
			);
		$additional_body_tags = array('request' => $additional_body_tags);
		$body = array_replace_recursive($body, $additional_body_tags);  //###VER: 5.3+ only!
		$body = new Xml($body, $this->xml_options);
		$body = $body->toString(array('cdata' => false, 'whitespace' => false));
		return $body;
	}

/**
 * Generic request processor.
 *
 * @param array $request_overrides Associated array matching HttpSocket $request options. Only include elements that need to be overridden for the given request.
 * @return mixed Array of raw converted XML records on success, false on HTTP request failure or 37s API failure.
 * @access public
 */
	protected function _request($request_overrides = null) {	
		$request_defaults = array(
				'method' => 'POST',
				'uri' => array(
					'scheme' => 'https', //($this->config['ssl'] == true ? 'https' : 'http'),
					'host' => $this->config['host'],
				),
				'header' => array(
					'Content-Type' => 'application/xml',
				),
				'body' => (string)$this->requestBody(),
			);
		
		$request = array_replace_recursive($request_defaults, $request_overrides);  //###VER: 5.3+ only!
		
		//###DEBUG:	debug($request); exit;
		
		// Fire the http request.
		if(($response = $this->Http->request($request)) === false) {
			return false;
		}	
		
		//###DEBUG:	debug($this->Http->response['raw']);
	
		// Set the HTTP response code for later use. (Can't determine success/failure from the code without knowing the specific request. DELETEs return a '204 No content' on success for example.)
		$this->httpResponseCode = $this->Http->response['status']['code'];
		
		if( in_array($this->httpResponseCode, array(404,500,503)) ) {
			return false;
		}
		// Convert the response to an array
		$response = new Xml($response, $this->xml_options);
		$response = $response->toArray($this->xml_options);
		
		// 37s server returned an error in the message body.
		if(!isset($response['Response']['success']) || strpos($response['Response']['success'], 'false') !== false) {
			return false;
		}
		return $response;
	}

}
