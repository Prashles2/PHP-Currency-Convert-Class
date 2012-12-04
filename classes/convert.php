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
	
	public function convert($amount = 1, $from = 'GBP', $to = 'USD')
	{
	
		/*
		* ERROR HANDLING
		* PL0X
		*/
		
		# The filename for a cached file
		
		$file = md5($from.$to.date('Ymd')).'.convertcache';
		
		# Check if cache file exists and pull rate
		
		if ($this->cachable && file_exists($this->cacheFolder.$file)) {
						
			$rate = file_get_contents($this->cacheFolder.$file);
			
			$return = $rate * $amount;
			
		}
		
		else {
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, "http://rate-exchange.appspot.com/currency?q={$amount}&from={$from}&to={$to}");
			
			$response = json_decode(curl_exec($ch), true);
			
			$return = $response['v'];
			
			if ($this->cachable) {
			
				file_put_contents($this->cacheFolder.$file, $response['rate']);
			}
		
		}
		
		return $return;
				
	}
	
}