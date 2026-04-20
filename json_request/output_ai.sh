#!/bin/bash
i=1
while true; do
	if [[ "$i" -eq 60 ]]; then
		exit 1
	fi
	((i++))
	sudo -u www-data php output_ai.php
	sleep 1;
done