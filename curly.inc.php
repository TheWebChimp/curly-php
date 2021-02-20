<?php
	/**
	 *┌─┐┬ ┬┬─┐┬ ┬ ┬
	 *│  │ │├┬┘│ └┬┘
	 *└─┘└─┘┴└─┴─┘┴
	 *
	 * @copyright  Chimp Web Studio
	 * @author     webchimp <github.com/webchimp>
	 * @version    1.0
	 * @license    MIT
	 * @uses       Cacher <github.com/TheWebChimp/cacher-php>
	 */

	class Curly {

		protected $method;
		protected $url;
		protected $params;
		protected $fields;
		protected $headers;
		protected $response;
		protected $info;
		protected $options;
		protected $caching;
		protected $files;
		protected $cacert;

		function __construct() {
			$this->method = 'get';
			$this->url = 'http://localhost';
			$this->params = array();
			$this->fields = array();
			$this->headers = array();
			$this->options = array();
			$this->response = '';
			$this->info = array();
			$this->cacert = '';
			$this->caching = true;
			$this->files = false;
		}

		/**
		 * Generates a new instance of Curly
		 *
		 * @param bool $caching  Determines if the caching should be on or off
		 * @return object returns a new instance of Curly
		 * @access public
		 * @static
		 */
		static function newInstance($caching = true) {
			global $site;
			$new = new self();
			$new->caching = $caching;

			if(isset($site)) {
				$new->cacert = $site->baseDir('/cacert.pem');
			}

			return $new;
		}

		/**
		 * Sets the method for the curl request
		 *
		 * @param string $method  Method to use in curl (get, post, put, delete, etc)
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setMethod($method) {
			$this->method = $method;
			return $this;
		}

		/**
		 * Sets the method for the curl request
		 *
		 * @param string $url  Url for the curl request
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setURL($url) {
			$this->url = $url;
			return $this;
		}

		/**
		 * Sets the method for the curl request
		 *
		 * @param bool $flag  flag to define if files are present or not
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setFiles($flag) {
			$this->files = $flag;
			return $this;
		}

		/**
		 * Sets CA certification file location
		 *
		 * @param string $file  Cacert.pem file location
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setCacert($file) {
			$this->cacert = $file;
			return $this;
		}

		/**
		 * Sets params for get curl request
		 *
		 * @param array $params  Params for request
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setParams($params) {
			$this->params = $params;
			return $this;
		}

		/**
		 * Sets fields for post-like curl request
		 *
		 * @param mixed $field  Fields to pass curl request
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setFields($fields) {
			$this->fields = $fields;
			return $this;
		}

		/**
		 * Sets headers for curl request
		 *
		 * @param mixed $headers  Headers passed to curl request
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setHeaders($headers) {
			$this->headers = $headers;
			return $this;
		}

		/**
		 * Sets options for curl request
		 *
		 * @param mixed $options  Options (CuRL opts) passed to curl request
		 * @return object returns the Curly instance
		 * @access public
		 */
		function setOptions($options) {
			$this->options = $options;
			return $this;
		}

		/**
		 * Returns the response from curl request. It can be as HTML (default) or JSON
		 *
		 * @param string $format  Format in which the curl request should be returned
		 * @return mixed returns the curl response in the format specified by the $format param
		 * @access public
		 */
		function getResponse($format = 'html') {
			$ret = '';
			switch ($format) {
				case 'json':
					$ret = json_decode($this->response);
				break;
				default:
					$ret = $this->response;
				break;
			}
			return $ret;
		}

		/**
		 * Returns the response from curl request. It can be as HTML (default) or JSON
		 *
		 * @return array return the information of the curl request, for debugging purposes
		 * @access public
		 */
		function getInfo() {
			return $this->info;
		}

		/**
		 * Executes the curl request with all the configuration defined previously. This is an
		 * interface for the _execute function
		 *
		 * @return object returns the Curly instance
		 * @access public
		 */
		function execute() {
			if ($this->caching) {
				$url = $this->url;
				$query = http_build_query($this->params);
				if ($query && $this->method == 'get') {
					$url = "{$this->url}?{$query}";
				}
				$hash = md5($url);
				$data = Cacher::getFromCache($hash, 900);
				if (! $data ) {
					$this->_execute();
					$data = json_decode($this->response);
					Cacher::saveToCache($hash, $data);
				} else {
					$this->response = json_encode( $data );
				}
			} else {
				$this->_execute();
			}
			return $this;
		}

		/**
		 * This is the real curl execution function which sets up all the configuration to achieve
		 * a curly function.
		 *
		 * @return object returns the Curly instance
		 * @access protected
		 */
		protected function _execute() {
			# Create query string
			$query = http_build_query($this->params);
			$url = $this->url;
			if ($query) {
				$url = "{$this->url}?{$query}";
			}
			// print_a($url);
			# Open connection
			$ch = curl_init();
			# Set the url, number of POST vars, POST data, etc
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			# Extra options
			if ($this->options) {
				foreach ($this->options as $key => $value) {
					curl_setopt($ch, $key, $value);
				}
			}
			# Add headers
			if ($this->headers) {
				$headers = array();
				foreach ($this->headers as $key => $value) {
					$headers[] = "{$key}: {$value}";
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			}
			# SSL
			if ( preg_match('/https:\/\//', $url) === 1 ) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_CAINFO, $this->cacert);
			}
			# POST/PUT/DELETE
			if ($this->method != 'get') {
				if ( is_array($this->fields) && !$this->files ) {
					$fields = http_build_query($this->fields);
					curl_setopt($ch, CURLOPT_POST, count($this->fields));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
				} else {
					curl_setopt($ch, CURLOPT_POSTFIELDS, $this->fields);
				}
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
			}
			# Execute request
			$this->response = curl_exec($ch);
			$this->info = curl_getinfo($ch);
			if ( curl_errno($ch) ) {

				if(function_exists('log_to_file')) {
					log_to_file(curl_error($ch), 'curly');
				}
			}
			# Close connection
			curl_close($ch);
		}
	}

?>