<?php

/*
*
* PHP Currency Conversion Class
* 
* The currency rates are fetched and cached for the whole day
*
* http://prash.me
* http://github.com/prashles
*
* Uses http://rate-exchange.appspot.com/currency currency API
* Returns JSON - based on Google's API
*
* @author Prash Somaiya
*
*/

Class Convert {

	/*
	* This version of script uses the jsonrates.com API, because Google's API is out of date.
	*/
	private $jsonRatesAPI = "xxxxxxxxxxxxxxxxxxxxxx";
	
	/*
	* Constructor sets to TRUE if $cacheFolder is writable
	*
	* FALSE by default
	*/
	
	private $cachable = FALSE;
	
	/*
	* The folder where the cache files are stored
	* 
	* Set in the constructor. //convert by default
	*/
	
	private $cacheFolder;
	
	/*
	* Length of cache in seconds
	*
	* Default is 2 hours
	*/
	
	private $cacheTimeout;
	
	/*
	* Check if folder is writable for caching
	*
	* Set $cache to FALSE on call to disable caching
	* $folder is where the cache files will be stored
	*
	* Set $folder to 'dcf' for the default folder
	*
	* Set $cacheTimeout for length of caching in seconds
	*/
	
	public function __construct($cache = TRUE, $folder = 'dcf', $cacheTimeout = 7200)
	{
		$this->cacheFolder = ($folder == 'dcf') ? dirname(__FILE__).'/convert/' : $folder;
	
		if (is_writable($this->cacheFolder) && $cache == TRUE) { 		
			$this->cachable     = TRUE;
			$this->cacheTimeout = $cacheTimeout;		
		}
	}
	
	/*
	* Main function for converting
	*
	* Set $round to FALSE to return full amount
	*/
	
	public function convert($amount, $from, $to, $round = TRUE)
	{
		
		# Check if cache file exists and pull rate

		$rate = $this->getCache($from.$to);
		
		if ($rate !== FALSE) {			
			$return = $rate * $amount;			
		}
		
		else {
			
			if (!$this->validateCurrency($to, $from)) {
				throw new Exception('Invalid currency code - must be exactly 3 letters');				
			}
			
			$response = $this->fetch($amount, $from, $to);
			
			if (isset($response['err'])) {
				throw new Exception('Invalid input');
			}
			
			$return = $response['v'];
			
			$this->newCache($from.$to, $response['rate']);
		
		}
		
		$result = ($round) ? abs(round($return, 2)) : abs($return);

		# After first script run result is equals zero
		# check it and get new result
		if($result == 0) {
			$result = $this->convert($amount, $from, $to, $round);
		}
		
		return $result;
				
	}
	
	/*
	* Fetches data from external API
	*/
	
	protected function fetch($amount, $from, $to)
	{	
		$apiKey = $this->jsonRatesAPI;
		$amount = (float) $amount;
		$url    = "http://jsonrates.com/get/?amount={$amount}&from={$from}&to={$to}&apiKey={$apiKey}";
		
		$response = json_decode(file_get_contents($url), true);
		
		# Caches the rate for future
		$this->newCache($from.$to, $response['amount']);
		
		return $response;
	}
	
	/*
	* Checks if file is cached then returns rate
	*/
	
	protected function getCache($file) {
	
		if ($this->cachable && file_exists($this->cacheFolder.strtoupper($file).'.convertcache')) {				
			$file = file($this->cacheFolder.$file.'.convertcache');
			
			if ($file[0] < (time() - $this->cacheTimeout)) {	
				return FALSE;				
			}
			
			return $file[1];						
		}
		
		return FALSE;
		
	}
	
	/*
	* Calculates amount needed in currency to achieve finish currency
	*
	* Set $round to FALSE to get full value
	*/
	
	public function amountTo($finalAmount, $from, $to, $round = TRUE)
	{
		$finalAmount = (float) $finalAmount;
		
		if ($finalAmount == 0) {			
			return 0;
		}
		
		if (!$this->validateCurrency($from, $to)) {		
			throw new Exception('Invalid currency code - must be exactly 3 letters');			
		}
		
		# Gets the rate
		$rate = $this->getRate($from, $to);
		
		# Work it out
		$out = $finalAmount / $rate;
		
		return ($round) ? round($out, 2) : $out;
	}
	
	/*
	* Returns rate of two currencies
	*/
	
	public function getRate($from = 'GBP', $to = 'USD')
	{
		
		# Check cache
		
		$rate = $this->getCache($from.$to);
		
		if (!$rate) {			
			$rate = $this->fetch(1, $from, $to);
			$rate = $rate['rate'];			
		}
		
		return $rate;
		
	}
	
	/*
	* Deletes all .convertcache files in cache folder
	*/
	
	public function clearCache()
	{
		$files = glob($this->cacheFolder.'*.convertcache');

		if (!empty($files)) {
			array_map('unlink', $files);			
		}

	}
	
	/*
	* Validates the currency identifier
	*/
	
	protected function validateCurrency()
	{
		foreach (func_get_args() as $val) {		
			if (strlen($val) !== 3 || !ctype_alpha($val)) {
				if (strtoupper($val) != 'BEAC') {			
					return FALSE;				
				}
			}
		}
		
		return TRUE;	
		
	}
	
	/*
	* Checks if file is cacheable then creates new file
	*/
	
	protected function newCache($file, $rate)
	{
	
		if ($this->cachable) {			
			$file = strtoupper($file).'.convertcache';
			
			$data = time().PHP_EOL.$rate;
			file_put_contents($this->cacheFolder.$file, $data);			
		}
		
	}
	
}