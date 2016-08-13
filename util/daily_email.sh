#!/bin/bash

logfile=/home/owen/bitfinex-auto-lend/lend.php.log
dstring=$(date +"%d/%b/%Y")

# Get epoch seconds
USERDATE=$(date +%s )
# Subtract 1 week
((USERDATE -= (60*60*24*7) ))

# Convert from epoch seconds into YYYYMMDD.
# -d assumes strings beginning with @ are epoch seconds.
dstring7days=$(date -d "@$USERDATE" +"%d/%b/%Y")






echo "//-------------------Balance Summary------"
opening_bal=$(grep $dstring $logfile | grep "USD Deposit balance" | head -1 | cut -c30-)
closing_bal=$(grep $dstring $logfile | grep "USD Deposit balance" | tail -1 | cut -c30-)
echo "open: "$opening_bal
echo "close: "$closing_bal
echo "//-------------------Loan Summary------"
/usr/bin/php /home/owen/bitfinex-auto-lend/lend_status.php

echo "//-------------------1 week Summary------"
str_1=$(grep $dstring7days $logfile | grep "USD Deposit balance" | tail -1)
str_2=$(grep $dstring      $logfile | grep "USD Deposit balance" | tail -1)

echo $str_1
echo $str_2


bal1=$(echo $str_1 | cut -c54- | cut -d. -f1)
bal2=$(echo $str_2 | cut -c54- | cut -d. -f1)
profit=$(( bal2 - bal1))
capital=$bal1
profitPercent=$(( 100 * 52 * profit / capital  ))

echo "Capital was \$$CAPITAL"
echo "Profit was \$$profit (approx apr $profitPercent%)"



echo "//-------------------Offer summary------"
grep $dstring $logfile | grep "About to make Lend" 


