<?php

$currency = @$_GET['currency'] ? htmlspecialchars($_GET['currency']) : 'usd';

include_once("./config-$currency.php"); 
include_once('./functions.php');
include_once('./bitfinex.php');

$bfx = new Bitfinex($config['api_key'], $config['api_secret']);
var_dump( $bfx);

print 'Getting offers';
$current_offers = $bfx->get_offers();
var_dump( $current_offers);

// Something is wrong most likely API key
if( array_key_exists('message', $current_offers) )
{
	die($current_offers['message']);
}

// Remove offers that weren't executed for too long
foreach( $current_offers as $item )
{
	$id = $item['id'];
	$timestamp = (int) $item['timestamp'];
	$current_timestamp = time();
	$diff_minutes = round(($current_timestamp - $timestamp) / 60);
	
	if( $config['remove_after'] <= $diff_minutes )
	{
		message("Removing offer # $id");

		$bfx->cancel_offer($id);
	}
}

print 'Getting balances';
$balances = $bfx->get_balances();
$available_balance = 0;
var_dump ($balances);

if( $balances )
{
	foreach( $balances as $item )
	{
		if( $item['type'] == 'deposit' && $item['currency'] == strtolower($config['currency']) )
		{
			$available_balance = floatval($item['available']);
			
			break;
		}
	}
}

// Is there enough balance to lend?
if( $available_balance >= $config['minimum_balance'] )
{
	message("Lending availabe balance of $available_balance");

	print 'Getting lendbook';
	$lendbook = $bfx->get_lendbook($config['currency']);
	$offers = $lendbook['asks'];
        var_dump ($offers);

	
	$total_amount 	= 0;
	$next_rate 		= 0;
	$next_amount 	= 0;
	$check_next 	= FALSE;
	
	// Find the right rate
	foreach( $offers as $item )
	{	
		// Save next closest item
		if( $check_next )
		{
			$next_rate 		= $item['rate'];	
			$next_amount 	= $item['amount'];
			$check_next 	= FALSE;
		}
		
		$total_amount += floatval($item['amount']);
	
		// Possible the closest rate to what we want to lend
		if( $total_amount <= $config['max_total_swaps'] )
		{
			$rate = $item['rate'];
			$check_next = TRUE;
		}
	}
	
	// Current rate is too low, move closer to the next rate
	if( $next_amount <= $config['max_total_swaps'] )
	{
		$rate = $next_rate - 0.01;
	}
	
	$daily_rate = daily_rate($rate);
        print "OK, daily rate is $daily_rate";
	
        print "About to make Lend offer of ".$config['currency']." $available_balance @ $rate (=".round(($rate/365),5).") for ".$config['period']." days";
exit (1);
	$result = $bfx->new_offer($config['currency'], (string) $available_balance, (string) $rate, $config['period'], 'lend');
	
	// Successfully lent
	if( array_key_exists('id', $result) )
	{
		message("$available_balance {$config['currency']} lent for {$config['period']} days at daily rate of $daily_rate%. Offer id {$result['id']}.");
	}
	else
	{
		// Something went wrong
		message($result);
	}
}
else
{
	message("Balance of $available_balance {$config['currency']} is below minimum of {$config['minimum_balance']} - not enough to lend.");
}

?>
