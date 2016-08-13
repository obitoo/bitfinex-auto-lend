<?php

$currency = @$_GET['currency'] ? htmlspecialchars($_GET['currency']) : 'usd';

include_once("./config-$currency.php"); 
include_once('./functions.php');
include_once('./bitfinex.php');
include_once('./logging.php');

	// Logging class initialization
$log = new Logging();
 
	// set path and name of log file (optional)
//	$log->lfile('./output.log');
 
	// write message to the log file
 

$bfx = new Bitfinex($config['api_key'], $config['api_secret']);


$log->lwrite( 'Getting credits');
$credits = $bfx->get_credits();

	// Something is wrong most likely API key
if( array_key_exists('message', $credits) )
{
	die($credits['message']);
}
$amount_total=0;
$rate_total=0;
$count=count($credits);

foreach ($credits as $credit)
{
   $rate=$credit['rate'];
   $amount=$credit['amount'];

   $amount_total=$amount_total + $amount;
   $rate_total  =$rate_total   + $rate;

}
$rate_total=$rate_total/$count; //not weighted
$log->lwrite("Total $count loans for $amount_total");
$log->lwrite("Approx %$rate_total");






	// close log file
$log->lclose();

?>
