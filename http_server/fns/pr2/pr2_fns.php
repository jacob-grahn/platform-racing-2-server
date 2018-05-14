<?php

// level backups
require_once QUERIES_DIR . '/level_backups/level_backups_insert.php';
require_once HTTP_FNS . '/pr2/backup_level.php';

// PMs
require_once QUERIES_DIR . '/users/user_select_power.php';
require_once QUERIES_DIR . '/pr2/pr2_select_true_rank.php';
require_once QUERIES_DIR . '/ignored/ignored_select.php';
require_once QUERIES_DIR . '/messages/message_insert.php';
require_once HTTP_FNS . '/pr2/send_pm.php';

// active guild members
require_once QUERIES_DIR . '/guilds/guild_select_active_member_count.php';
require_once HTTP_FNS . '/pr2/guild_count_active.php';
