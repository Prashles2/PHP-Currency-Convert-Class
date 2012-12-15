<?php

require_once 'classes/convert.php';

$convert = new Convert;

# Convert 15 USD to GBP
echo $convert->convert(10, 'USD', 'GBP');

echo '<br/>';

# Displays how much USD you need to get 100 INR - won't show the rounded value
echo $convert->amountTo(100, 'USD', 'INR', FALSE);