<?php
require_once('soap/lib/nusoap.php');

$c = new soapclient('http://localhost/statgolf/soapserver.php');

$stockprice = $c->call('getStockQuote', array('symbol' => 'ABC'));

echo "The stock price for 'ABC' is $stockprice.";

?>
