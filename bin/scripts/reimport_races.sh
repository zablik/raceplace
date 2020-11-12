#!/bin/bash

cd /var/www/raceplace

. bin/scripts/rebuild_db.sh

IFS=$'\n'

events=(
'zhuk-trail-kupalle-2020'
'xcm-kupalle-2020'
'marathon-kupalle-2020'
'xcm-naliboki-2020'
'trail-naliboki-2020'
'multi-naliboki-2020'
)

for event in ${events[*]}
do
php bin/console rp:import:race $event
php bin/console rp:import:profiles $event
php bin/console rp:import:race-results $event
done

