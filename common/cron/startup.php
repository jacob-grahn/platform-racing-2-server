<?php

require_once GEN_HTTP_FNS;
require_once FNS_DIR . '/cron/cron_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/artifact_location.php';
require_once QUERIES_DIR . '/servers.php';

$pdo = pdo_connect();

regenerate_vault_items($pdo);
generate_level_list($pdo, 'newest');
generate_level_list($pdo, 'best');
generate_level_list($pdo, 'best_week');
generate_level_list($pdo, 'campaign');
update_artifact($pdo);
write_server_status($pdo);
