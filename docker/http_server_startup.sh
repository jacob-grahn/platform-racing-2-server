#!/bin/sh

php /pr2/common/cron/minute.php
php /pr2/common/cron/hourly.php
exec apache2-foreground