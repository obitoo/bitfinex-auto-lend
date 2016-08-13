#!/bin/bash
logfile=/home/owen/bitfinex-auto-lend/lend.php.log

tmpfile=/tmp/daily_bal.tmp

grep "USD Deposit balance" $logfile | sed 's/:..:..:..]//' | sed 's/\[//' > $tmpfile

lastline=
while read line
do
 if [ "$line" != "$lastline" ]
 then
    echo $line
 fi
 lastline=$line
done < $tmpfile






