#!/bin/sh

su -s /bin/sh www-data -c "php /pr2/common/cron/startup.php"
exec apache2-foreground
