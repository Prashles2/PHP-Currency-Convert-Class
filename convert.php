<?php

require_once 'classes/convert.php';

$convert = new Convert;

$get = $convert->convert(15, 'INR');

print_r($get);

