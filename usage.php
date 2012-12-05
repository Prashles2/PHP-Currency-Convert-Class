<?php

require_once 'classes/convert.php';

$convert = new Convert;

# Convert 15 USD to GBP
echo $convert->convert(10, 'USD', 'GBP');

echo '<br/>';

# Displays how much USD you need to get 100 INR
echo $convert->amount_to(100, 'USD', 'INR');