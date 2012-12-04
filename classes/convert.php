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
			
			if (strlen($from) !== 3 || strlen($to) !== 3 || !ctype_alpha($from) || !ctype_alpha($to)) {
				throw new Exception('Invalid currency code - must be exactly 3 letters');
			}
			
			$amount = (float) $amount;
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, "http://rate-exchange.appspot.com/currency?q={$amount}&from={$from}&to={$to}");
			
			$response = json_decode(curl_exec($ch), true);
			
			if (isset($response['err'])) {
				throw new Exception('Invalid input');
			}
			
			$return = $response['v'];
			
			echo $response; exit;
			
			if ($this->cachable) {
			
				file_put_contents($this->cacheFolder.$file, $response['rate']);
			}
		
		}
		
		return $return;
				
	}
	
}