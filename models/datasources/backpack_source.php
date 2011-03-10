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
 * Connection status
 *
 * @var boolean
 * @access public
 */
	public $connected = false;

/**
 * HttpSocket instance
 *
 * @var HttpSocket
 * @access public
 */
	public $Http = null;

/**
 * Last HTTP response code
 *
 * @var integer
 * @access public
 */
	public $httpResponseCode = null;

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
			'page_id' => array(
				'type' => 'integer',
				'null' => false,
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
			'list_id' => array(
				'type' => 'integer',
				'null' => false,
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
			'page_id' => array(
				'type' => 'integer',
				'null' => false,
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
			'page_id' => array(
				'type' => 'integer',
				'null' => false,
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

		// Emails ------------------------------
		'emails' => array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'key' => 'primary',
				'length' => 11,
			),
			'page_id' => array(
				'type' => 'integer',
				'null' => false,
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
 * Standard XML formatting options for all requests/responses.
 *
 * @var array
 * @access private
 */
	private $__xml_options = array(
		'format' => 'tags', // or 'attributes'
		'slug' => false,
		//'root' => '#document',
		//'version' => '1.0',
		//'encoding' => 'UTF-8',
		//'tags' => array('list' => array('slug' => true, 'name' => 'BLAH', ), ),  // Tag-specific options.
	);






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
 * @return array List of backpack "tables" exposed by the 37s API.
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
		
		// All of our CRUD functions are going to have to somehow switch on the Backpack "table" we're dealing with.
// 		switch($this->model->useTable) {
// 			default:
// 				break;
// 		}


		//###TODO: Parse the $queryData to figure out which helper to call!
		
		//###DEBUG: Hardcoded for testing individual helpers!
		
		// Pages:
		//$results = $this->_page_list_all();
		//$results = $this->_page_search('Mac');
		//$results = $this->_page_create('Test3', 'Description is no longer supported. Use Notes instead.');
		$page_id = '2328789'; //$results[$this->model->alias]['id'];

  		$results = $this->_page_read($page_id);  // 2328789 = Datasource Test
// 		foreach($results[$this->model->alias]['Belonging'] as $index => $belonging)
// 		{
// 			$type = $belonging['Widget']['type'];
// 			$extract = "/{$type}[id={$belonging['Widget']['id']}]/.";
// 			//debug($extract);
// 			$widget = array_shift(Set::extract($extract, $results[$this->model->alias]));
// 			$results[$this->model->alias]['Belonging'][$index][$type] = $widget;
// 		}
		
		//$results = $this->_page_reorder_items($page_id, array('11933472') ); // 11933140 = separator, 9259675 = todo list
		//$results = $this->_page_update_title($page_id, 'Datasource New Name' );
		//$results = $this->_page_duplicate($page_id); 
		//$results = $this->_page_email($page_id); 
		//$results = $this->_page_delete('$page_id); 


		//$results = $this->_list_create($page_id, 'Funny Things'); 
		//$results = $this->_list_read($page_id); 
		//$list_id = $results[$this->model->alias]['id'];
		//$results = $this->_list_update_title($page_id, $list_id, 'Really Funny Things'); 
		//$results = $this->_list_delete($page_id, $list_id); 
		


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
		$body = Set::merge($body, $additional_body_tags);
		$body = new Xml($body, $this->__xml_options);
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
				'body' => $this->requestBody(),
			);
		
		$request = Set::merge($request_defaults, $request_overrides);
		
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
		$response = new Xml($response, $this->__xml_options);
		$response = $response->toArray($this->__xml_options);
		
		// 37s server returned an error in the message body.
		if(!isset($response['Response']['success']) || strpos($response['Response']['success'], 'false') !== false) {
			return false;
		}
		return $response;
	}

/**
 * Convert an XML-style Page array into a Cake-style array so scaffolding, etc. work more automatically.
 *
 * @param int $raw_xml_array An array converted from the raw XML the 37s API returns representing a single Page.
 * @return array Reformatted array that follows Cake model naming and nesting conventions.
 * @access protected
 */
	protected function _reformat($raw_xml_array) {
		
		$types = array(
			'Belongings' 	=> 'Belonging',
			'Notes' 		=> 'Note',
			'Separators' 	=> 'Separator',
			'Emails' 		=> 'Email',
			'Lists' 		=> 'List',
			'Items' 		=> 'Item',
		);
		
		foreach($types as $plural => $singular) {
		
			if(isset($raw_xml_array[$plural][$singular]) || isset($raw_xml_array[$plural][strtolower($singular)])) {
				
				if(isset($raw_xml_array[$plural][$singular]['id'])) {  // Single item returned
					$raw_xml_array[$singular] = array($raw_xml_array[$plural][$singular]);  // Make sure we can still loop over it.
				}
				elseif(isset($raw_xml_array[$plural][strtolower($singular)]['id'])) {  // Single item returned, but somehow not slugged correctly.
					$raw_xml_array[ucfirst($singular)] = array($raw_xml_array[$plural][strtolower($singular)]);
				}
				else {
					$raw_xml_array[$singular] = $raw_xml_array[$plural][$singular];
				}
				
				unset($raw_xml_array[$plural]);  // Remove the old plural key.
			}
			elseif(isset($raw_xml_array[strtolower($plural)]) && empty($raw_xml_array[strtolower($plural)])) {  // No items returned, and somehow not slugged correctly.
				$raw_xml_array[ucfirst($singular)] = $raw_xml_array[strtolower($plural)];
				unset($raw_xml_array[strtolower($plural)]);  // Remove the old plural key.
			}
		}
		
		// Handle the nested List Items.
		if(isset($raw_xml_array['List'])) {

			if(isset($raw_xml_array['List']['id'])) {  // Single item returned
				$raw_xml_array['List'] = array($raw_xml_array['List']);  // Make sure we can still loop over it.
			}

			foreach($raw_xml_array['List'] as $index => $list) {
				$raw_xml_array['List'][$index] = array_shift($this->_reformat($list));
			}
		}
		
		return array($this->model->alias => $raw_xml_array);
	}


// Pages ====================================================================

/**
 * Create a new page.
 *
 * @param string $title Title of the new page.
 * @param string $description Initial description of the page. (No longer supported?)
 * @return mixed Array containing the new page, including ID if the page was created, false on failure.
 * @access protected
 */
	protected function _page_create($title, $description = null) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/pages/new",
				),
				'body' => $this->requestBody(array(
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
 * Duplicate a page. 
 *
 * @param int $page_id The ID number of the page to retitle.
 * @return mixed An array containing the new page's id and name on success, false on failure.
 * @access protected
 */
	protected function _page_duplicate($page_id) {

		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/duplicate',
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return $this->_reformat($result['Response']['Page']);
	}

/**
 * Fetch a single page with all associated data.
 *
 * @param int $page_id The ID number of the page to fetch.
 * @return mixed Array containing the new page, false on failure.
 * @access protected
 */
	protected function _page_read($page_id) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id),
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		// Knock down a few sub-arrays.
		return $this->_reformat($result['Response']['Page']);
	}
		
		
/**
 * Fetch a list of page records.
 *
 * @return mixed Array of Page records on success, false on HTTP request failure or 37s API failure.
 * @access protected
 */
	protected function _page_list_all() {
		
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
			$results[] = $this->_reformat($r);
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
	protected function _page_search($term) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/pages/search",
				),
				'body' => $this->requestBody(array('term' => htmlentities($term))),
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
 * Reorder items on a page. An incomplete list of IDs 
 * will be sorted among themselves, and moved below any un-included IDs. 
 * Specifying a single ID will essentially move it to the end of the page.
 *
 * @param int $page_id The ID number of the page to reorder.
 * @param array $belonging_id_order_array An ordered array containing the IDs of the belongings on the given page.
 * @return boolean True on success, false on failure.
 * @access protected
 */
	protected function _page_reorder_items($page_id, $belonging_id_order_array) {
		
		// Behavioral questions:
		// What happens if not all the IDs are valid? Do the valid ones still get sorted?
		// What if not all of the belongings are included? Example: Items (1, 2, 3, 4) on the page, but we only get (3, 1). Resulting order will be (2, 4, 3, 1).
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/reorder',
				),
				'body' => $this->requestBody(array(
									'belongings' => implode( ' ', $belonging_id_order_array ),  //implode( ' ', array_map('intval', $belonging_id_order_array) ),
								)),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return true;
	}
	
/**
 * Update a page's title. 
 *
 * @param int $page_id The ID number of the page to retitle.
 * @param string $title The new title to use.
 * @return boolean True on success, false on failure.
 * @access protected
 */
	protected function _page_update_title($page_id, $title) {

		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/update_title',
				),
				'body' => $this->requestBody(array(
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
		
		return true;
	}
	
/**
 * Delete a page. 
 *
 * @param int $page_id The ID number of the page to delete.
 * @return boolean True if Backpack says it was able to successfully delete the page, false on failure.
 * @access protected
 */
	protected function _page_delete($page_id) {

		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/destroy',
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return true;
	}
	
/**
 * Email yourself a copy of a page. 
 *
 * @param int $page_id The ID number of the page to retitle.
 * @return boolean True if Backpack says it was able to successfully generate the email, false on failure.
 * @access protected
 */
	protected function _page_email($page_id) {

		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/email',
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return true;
	}

/**
 * Change the sharing permissions for a page in your account. A page can be shared privately with specified email addresses, and/or made public for anyone to access.
 *
 * @param int $page_id The ID number of the page to retitle.
 * @param array $email_addresses Optional array of email addresses the page should be shared with.
 * @param boolean $public Whether the page should only be shared with $email_addresses, or accessible by anyone knowing the URL.
 * @return boolean True if Backpack says it was able to successfully set the sharing options, false on failure.
 * @access protected
 */
	protected function _page_sharing($page_id, $email_addresses = array(), $public = null) {

		App::import('Core' , 'Validation');
		$Validation = new Validation();
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/share',
				),
			);
	
		// Validate the provided email addresses.
		$valid_emails = array();
		if(is_array($email_addresses)) {
			foreach($email_addresses as $e) {
				if($Validation->email($e, true)) {
					$valid_emails[] = $e;
				}
			}
		}
		
		// Build the XML request body based on passed args.
		if(count($valid_emails)) {
			$request_overrides['body']['email_addresses'] = implode(' ', $valid_emails);
		}
		
		if(!is_bool($public)) {
			$request_overrides['body']['page']['public'] = ($public ? '1' : '0');
		}
		
		
		//###TODO
		// Fire the http request.
// 		if(($result = $this->_request($request_overrides)) === false) {
// 			return false;
// 		}		
		
		return false;
	}

/**
 * Remove a linked page shared from someone else's account 
 *
 * @param int $page_id The ID number of the page to retitle.
 * @return boolean True if Backpack says it was able to successfully unlink the page from your account, false on failure.
 * @access protected
 */
	protected function _page_unshare_friend_page($page_id) {

		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/unshare_friend_page',
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return false;
	}


	
// Lists ====================================================================

//###TODO

/**
 * Create a new list.
 *
 * @param int $page_id The ID number of the page.
 * @param string $title Title of the new list.
 * @return mixed Array containing the new page, including ID if the page was created, false on failure.
 * @access protected
 */
	protected function _list_create($page_id, $title) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/lists/add',
				),
				'body' => $this->requestBody(array(
									'name' => htmlentities($title),
								)),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return array($this->model->alias => $result['Response']['List']);
	}
	
/**
 * Fetch a list of all lists associated with a page.
 *
 * @param int $page_id The ID number of the page.
 * @return mixed Array containing a list of Lists (id and name) on the page.
 * @access protected
 */
	protected function _list_read($page_id) {
		
		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/lists/list',
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		// Knock down a few sub-arrays.
		return $this->_reformat($result['Response']['Lists']);
	}
		
/**
 * Update a list's title (which is the only editable attribute.)
 *
 * @param int $page_id The ID number of the page the list is on.
 * @param int $list_id The ID number of the list to retitle.
 * @param string $title The new title to use for the list.
 * @return boolean True on success, false on failure.
 * @access protected
 */
	protected function _list_update_title($page_id, $list_id, $title) {

		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/lists/update/'.preg_replace('/[^0-9]/', '', $list_id),
				),
				'body' => $this->requestBody(array(
									'list' => array(
										'name' => htmlentities($title),
										//'description' => htmlentities($description),
									),
								)),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return true;
	}
	
/**
 * Delete a list from a page. 
 *
 * @param int $page_id The ID number of the page to delete.
 * @param int $list_id The ID number of the list to retitle.
 * @return boolean True if Backpack says it was able to successfully delete the page, false on failure.
 * @access protected
 */
	protected function _list_delete($page_id, $list_id) {

		$request_overrides = array(
				'uri' => array(
					'path' => "/ws/page/".preg_replace('/[^0-9]/', '', $page_id).'/lists/destroy/'.preg_replace('/[^0-9]/', '', $list_id),
				),
			);
	
		// Fire the http request.
		if(($result = $this->_request($request_overrides)) === false) {
			return false;
		}		
		
		return true;
	}
	
















/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
Ref: http://www.linuxjournal.com/article/9585?page=0,3
*/
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}
}
