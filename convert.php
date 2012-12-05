<?php

require_once 'classes/convert.php';

$convert = new Convert;

//echo $convert->convert(15, 'USD', 'INR');

echo $convert->amount_to(100, 'USD', 'INR');



