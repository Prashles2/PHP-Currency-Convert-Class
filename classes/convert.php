<?php

Class Convert {

	private $cachable    = FALSE;
	private $cacheFolder;
	
	/*
	* Check if folder is writable for caching
	*
	* Set $cache to FALSE on call to disable caching
	* $folder is where the cache files will be stored
	*/
	
	public function __construct($cache = TRUE, $folder = 'dcf')
	{
		$this->cacheFolder = ($folder == 'dcf') ? dirname(__FILE__).'/convert/' : $folder;
	
		if (is_writable($this->cacheFolder) && $cache == TRUE) {
			$this->cachable = TRUE;

		}
	}
	
	/*
	* Main function for converting
	*/
	
	public function convert($amount = 1, $from = 'GBP', $to = 'USD')
	{

		# The filename for a cached file
		
		$file = md5($from.$to.date('Ymd')).'.convertcache';
		
		# Check if cache file exists and pull rate

		$rate = $this->get_cache($file);
		
		if ($rate !== FALSE) {
			
			$return = $rate * $amount;
			
		}
		
		else {
			
			if (!$this->validate_currency($to, $from)) {
				
				throw new Exception('Invalid currency code - must be exactly 3 letters');
				
			}
			
			$response = $this->fetch($amount, $from, $to);
			
			if (isset($response['err'])) {
				throw new Exception('Invalid input');
			}
			
			$return = $response['v'];
						
			if ($this->cachable) {
			
				file_put_contents($this->cacheFolder.$file, $response['rate']);
			}
		
		}
		
		return $return;
				
	}
	
	/*
	* Fetches data from external API
	*/
	
	protected function fetch($amount, $from, $to)
	{
		$amount = (float) $amount;
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, "http://rate-exchange.appspot.com/currency?q={$amount}&from={$from}&to={$to}");
		
		$response = json_decode(curl_exec($ch), true);
		
		if ($this->cachable) {
			
			$file = md5($from.$to.date('Ymd')).'.convertcache';
			file_put_contents($this->cacheFolder.$file, $response['rate']);
		
		}
		
		return $response;		
	
	}
	
	/*
	* Checks if file is cached then returns rate
	*/
	
	protected function get_cache($file) {
	
		if ($this->cachable && file_exists($this->cacheFolder.$file)) {
						
			return file_get_contents($this->cacheFolder.$file);
						
		}
		
		return FALSE;
		
	}
	
	/*
	* Calculates amount needed in currency to achieve finish currency
	*/
	
	public function amount_to($finalAmount, $from, $to)
	{
		$finalAmount = (float) $finalAmount;
		
		if (!$this->validate_currency($from, $to)) {
		
			throw new Exception('Invalid currency code - must be exactly 3 letters');
			
		}
		
		# Check cache
		$rate = $this->get_rate($from, $to);
		
		# Work it out
		$out = $finalAmount / $rate;
		
		return $out;
	}
	
	/*
	* Returns rate of two currencies
	*/
	
	public function get_rate($from = 'GBP', $to = 'USD')
	{
		
		# Check cache
		$file = md5($from.$to.date('Ymd'));
		
		$rate = $this->get_cache($file);
		
		if (!$rate) {
			
			$rate = $this->fetch(1, $from, $to);
			$rate = $rate['rate'];
			
		}
		
		return $rate;
		
	}
	
	/*
	* Deletes all .convertcache files in cache folder
	*/
	
	public function clear_cache()
	{
		foreach (glob($this->cacheFolder.'*.convertcache') as $file) {
			unlink($file);
		}
	}
	
	/*
	* Validates the currency identifier
	*/
	
	protected function validate_currency()
	{
		foreach (func_get_args() as $val) {
		
			if (strlen($val) !== 3 || !ctype_alpha($val)) {
			
				return FALSE;
				
			}

		}
		
		return TRUE;	
		
	}
	
}