<?php

// some function files
require_once COMMON_DIR . '/manage_socket/socket_manage_fns.php';
require_once HTTP_FNS . '/data_fns.php';
require_once HTTP_FNS . '/query_fns.php';
require_once HTTP_FNS . '/rand_crypt/random_str.php';

// some queries
require_once QUERIES_DIR . '/users/user_apply_temp_pass.php';
require_once QUERIES_DIR . '/users/id_to_name.php';
require_once QUERIES_DIR . '/users/name_to_id.php';
require_once QUERIES_DIR . '/users/user_select.php'; // select user (no hashes) by id
require_once QUERIES_DIR . '/users/user_select_mod.php';
require_once QUERIES_DIR . '/users/user_select_by_name.php';
require_once QUERIES_DIR . '/users/user_select_full_by_name.php';
require_once QUERIES_DIR . '/users/user_select_power.php';
require_once QUERIES_DIR . '/tokens/token_select.php';
require_once QUERIES_DIR . '/epic_upgrades/epic_upgrades_select.php';
require_once QUERIES_DIR . '/epic_upgrades/epic_upgrades_update_field.php';
require_once QUERIES_DIR . '/pr2/pr2_select.php';
require_once QUERIES_DIR . '/pr2/pr2_update_part_array.php';
require_once QUERIES_DIR . '/part_awards/part_awards_insert.php';
require_once QUERIES_DIR . '/bans/ban_select_active_by_user_id.php';
require_once QUERIES_DIR . '/bans/ban_select_active_by_ip.php';
require_once QUERIES_DIR . '/levels/levels_select_campaign.php';
require_once QUERIES_DIR . '/levels/levels_select_best.php';
require_once QUERIES_DIR . '/levels/levels_select_best_today.php';
require_once QUERIES_DIR . '/levels/levels_select_newest.php';
