#!/bin/bash
for ip in $(cat hosts); do
	nohup ssh -o 'StrictHostKeyChecking no' -o 'ConnectTimeout=5' -X $ip 'pgrep -f ClientStart | xargs kill -9' &
done

