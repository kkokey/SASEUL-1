#!/usr/bin/env bash

while :
do
    sleep 5

    echo 'SyncTracker!';
    script/saseul_script SyncTracker

    echo 'Start!'
    saseuld/saseuld_node
done
