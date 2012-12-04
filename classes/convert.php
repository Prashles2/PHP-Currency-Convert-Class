<?php

Class Convert {

	private $cachable    = FALSE;
	private $cacheFolder;
	
	/*
	* Check if folder is writable for caching
	*
	* Set $cache to FALSE on call to disable caching
	*/
	
	public function __construct($cache = TRUE)
	{
		$this->cacheFolder = dirname(__FILE__).'/convert/';
	
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
		
		$file = md5($from.$to.date('Ymd')).'.convertcache';
		
		# Check the file exists

		
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