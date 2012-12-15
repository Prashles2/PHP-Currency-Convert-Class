## Readme

This class will allow you to convert currencies and also calculate how much you need in a specific currency to achieve a final amount in another currency.  
  
The class supports caching. The files will be cached daily.

## Usage

Call the class. There are three optional parameters:  
  
__$cache__ - Set this to FALSE if you do not want to enabled cachine (TRUE by default)  
__$cacheFolder__ - Set your cache folder. By default, it is /classes/convert  
__$cacheTimeout__ - Set the amount of time the rates are cached for (in seconds), set to 1 day by default  

Sample usage in usage.php
  
## Methods

__convert()__ - Four parameters, $amount, $fromCurrency and $toCurrency and $round. Set $round to FALSE to disable rounding. TRUE by default.  
__amountTo()__ - Four parameters, $finalAmount, $fromCurrency, $toCurrency and $round. This will show you how much $fromCurrency you need to get $finalAmoun in $toCurrency. Set $round to FALSE to disable rounding. TRUE by default.    
__getRate()__ - Two parameters, $fromCurrency and $toCurrency. Returns the rate.  
__clearCache()__ - Deletes all cache files  


