#!/bin/sh
set -x
php /home/bitnami/htdocs/artisan database:dump > /tmp/cron.log 2>&1
