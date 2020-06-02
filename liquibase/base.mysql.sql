--liquibase formatted sql

--changeset root:1574908323264-1
CREATE TABLE admin_actions (action_id INT AUTO_INCREMENT NOT NULL, mod_id INT NOT NULL, message text NOT NULL, type VARCHAR(30) NOT NULL, time BIGINT NOT NULL, ip VARCHAR(100) NOT NULL, CONSTRAINT PK_ADMIN_ACTIONS PRIMARY KEY (action_id));

--changeset root:1574908323264-2
CREATE TABLE artifact_location (artifact_id INT DEFAULT 0 NOT NULL, level_id INT NOT NULL, x INT NOT NULL, y INT NOT NULL, rot INT(3) NOT NULL, updated_time datetime NOT NULL, first_finder INT NOT NULL, bubbles_winner INT NOT NULL, CONSTRAINT PK_ARTIFACT_LOCATION PRIMARY KEY (artifact_id));

--changeset root:1574908323264-3
CREATE TABLE artifacts_found (user_id INT NOT NULL, artifacts MEDIUMINT DEFAULT 0 NOT NULL, artifacts_first MEDIUMINT DEFAULT 0 NOT NULL, time datetime NOT NULL, new_time BIGINT NOT NULL, CONSTRAINT PK_ARTIFACTS_FOUND PRIMARY KEY (user_id));

--changeset root:1574908323264-4
CREATE TABLE bans (ban_id MEDIUMINT AUTO_INCREMENT NOT NULL, banned_ip VARCHAR(100) NOT NULL, banned_user_id MEDIUMINT NOT NULL, mod_user_id MEDIUMINT NOT NULL, time BIGINT NOT NULL, expire_time BIGINT NOT NULL, reason VARCHAR(100) NOT NULL, record TEXT NOT NULL, response VARCHAR(500) DEFAULT '' NOT NULL, lifted BIT DEFAULT 0 NOT NULL, mod_name TINYTEXT NOT NULL, banned_name TINYTEXT NOT NULL, lifted_by VARCHAR(100) NULL, lifted_reason VARCHAR(500) NULL, lifted_time BIGINT NULL, account_ban TINYINT(3) DEFAULT 1 NOT NULL, ip_ban TINYINT(3) DEFAULT 1 NOT NULL, scope VARCHAR(1) DEFAULT 'g' NOT NULL, notes TEXT NULL, modifed_time BIGINT NULL, modified_time datetime NULL, CONSTRAINT PK_BANS PRIMARY KEY (ban_id));

--changeset root:1574908323264-5
CREATE TABLE best_levels (level_id INT DEFAULT 0 NOT NULL, CONSTRAINT PK_BEST_LEVELS PRIMARY KEY (level_id));

--changeset root:1574908323264-6
CREATE TABLE campaigns (level_id MEDIUMINT AUTO_INCREMENT NOT NULL, campaign BIT DEFAULT 0 NOT NULL, level_num TINYINT(3) NOT NULL, prize INT NOT NULL, info VARCHAR(100) NOT NULL, prize_type VARCHAR(10) NOT NULL, prize_id INT NOT NULL, CONSTRAINT PK_CAMPAIGNS PRIMARY KEY (level_id));

--changeset root:1574908323264-7
CREATE TABLE changing_emails (user_id INT NOT NULL, old_email VARCHAR(100) NOT NULL, new_email VARCHAR(100) NOT NULL, code VARCHAR(30) NOT NULL, date datetime NOT NULL, change_id INT AUTO_INCREMENT NOT NULL, request_ip VARCHAR(100) NOT NULL, confirm_ip VARCHAR(100) NULL, status VARCHAR(10) NOT NULL, CONSTRAINT PK_CHANGING_EMAILS PRIMARY KEY (change_id));

--changeset root:1574908323264-8
CREATE TABLE contest_prizes (prize_id INT AUTO_INCREMENT NOT NULL, contest_id INT NOT NULL, part_type VARCHAR(5) NOT NULL, part_id INT NOT NULL, added INT NOT NULL, CONSTRAINT PK_CONTEST_PRIZES PRIMARY KEY (prize_id));

--changeset root:1574908323264-9
CREATE TABLE contest_winners (id INT AUTO_INCREMENT NOT NULL, contest_id INT NOT NULL, winner_id INT NOT NULL, comment VARCHAR(255) NOT NULL, win_time INT NOT NULL, awarded_by INT NOT NULL, awarder_ip VARCHAR(100) NOT NULL, prizes_awarded VARCHAR(1000) NOT NULL, CONSTRAINT PK_CONTEST_WINNERS PRIMARY KEY (id));

--changeset root:1574908323264-10
CREATE TABLE contests (contest_id INT AUTO_INCREMENT NOT NULL, contest_name VARCHAR(100) NOT NULL, `description` VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, user_id INT NOT NULL, active INT NOT NULL, updated INT NOT NULL, awarding VARCHAR(255) NOT NULL, max_awards INT NOT NULL, CONSTRAINT PK_CONTESTS PRIMARY KEY (contest_id));

--changeset root:1574908323264-11
CREATE TABLE epic_upgrades (user_id INT NOT NULL, epic_hats VARCHAR(1000) DEFAULT '' NOT NULL, epic_heads VARCHAR(1000) DEFAULT '' NOT NULL, epic_bodies VARCHAR(1000) DEFAULT '' NOT NULL, epic_feet VARCHAR(1000) DEFAULT '' NOT NULL, CONSTRAINT PK_EPIC_UPGRADES PRIMARY KEY (user_id));

--changeset root:1574908323264-12
CREATE TABLE exp_today (look VARCHAR(23) NOT NULL, exp INT DEFAULT 0 NOT NULL, CONSTRAINT PK_EXP_TODAY PRIMARY KEY (look));

--changeset root:1574908323264-13
CREATE TABLE folding_at_home (user_id MEDIUMINT NOT NULL, crown_hat BIT DEFAULT 0 NOT NULL, cowboy_hat BIT DEFAULT 0 NOT NULL, r1 BIT DEFAULT 0 NOT NULL, r2 BIT DEFAULT 0 NOT NULL, r3 BIT DEFAULT 0 NOT NULL, r4 BIT DEFAULT 0 NOT NULL, r5 BIT DEFAULT 0 NOT NULL, r6 BIT DEFAULT 0 NOT NULL, r7 BIT DEFAULT 0 NOT NULL, r8 BIT DEFAULT 0 NOT NULL, CONSTRAINT PK_FOLDING_AT_HOME PRIMARY KEY (user_id));

--changeset root:1574908323264-14
CREATE TABLE friends (user_id INT DEFAULT 0 NOT NULL, friend_id INT DEFAULT 0 NOT NULL);

--changeset root:1574908323264-15
CREATE TABLE gp (user_id INT NOT NULL, guild_id INT NOT NULL, gp_today INT NOT NULL, gp_total INT NOT NULL);

--changeset root:1574908323264-16
CREATE TABLE guild_invitations (guild_id INT NOT NULL, user_id INT NOT NULL, date datetime NOT NULL);

--changeset root:1574908323264-17
CREATE TABLE guild_transfers (transfer_id INT AUTO_INCREMENT NOT NULL, guild_id INT NULL, old_owner_id INT NULL, new_owner_id INT NULL, code TEXT NULL, date datetime NULL, request_ip TEXT NULL, confirm_ip TEXT NULL, status TEXT NULL, CONSTRAINT PK_GUILD_TRANSFERS PRIMARY KEY (transfer_id));

--changeset root:1574908323264-18
CREATE TABLE guilds (guild_id INT AUTO_INCREMENT NOT NULL, guild_name VARCHAR(20) NOT NULL, creation_date datetime NULL, active_date datetime NULL, member_count INT NOT NULL, emblem VARCHAR(30) NOT NULL, gp_total INT DEFAULT 0 NOT NULL, gp_today INT DEFAULT 0 NOT NULL, owner_id INT NOT NULL, note VARCHAR(100) NOT NULL, CONSTRAINT PK_GUILDS PRIMARY KEY (guild_id), UNIQUE (guild_name));

--changeset root:1574908323264-19
CREATE TABLE ignored (user_id INT DEFAULT 0 NOT NULL, ignore_id INT DEFAULT 0 NOT NULL);

--changeset root:1574908323264-20
CREATE TABLE `keys` (address VARCHAR(20) NOT NULL, port SMALLINT NOT NULL, in_key VARCHAR(30) NOT NULL, out_key VARCHAR(30) NOT NULL);

--changeset root:1574908323264-21
CREATE TABLE level_backups (backup_id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, level_id INT NOT NULL, title VARCHAR(50) NOT NULL, version MEDIUMINT NOT NULL, date datetime NOT NULL, live TINYINT(3) DEFAULT 0 NOT NULL, rating FLOAT(12) DEFAULT 0 NOT NULL, votes INT DEFAULT 0 NOT NULL, note TINYTEXT NOT NULL, min_level TINYINT(3) DEFAULT 0 NOT NULL, song TINYINT(3) DEFAULT 0 NOT NULL, play_count INT DEFAULT 0 NOT NULL, CONSTRAINT PK_LEVEL_BACKUPS PRIMARY KEY (backup_id));

--changeset root:1574908323264-22
CREATE TABLE levels (level_id MEDIUMINT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT 0 NOT NULL, version MEDIUMINT DEFAULT 1 NOT NULL, live BIT DEFAULT 0 NOT NULL, ip VARCHAR(100) NOT NULL, title VARCHAR(50) DEFAULT '' NOT NULL, rating FLOAT(12) DEFAULT 0 NOT NULL, votes INT DEFAULT 0 NOT NULL, time INT DEFAULT 0 NOT NULL, note TINYTEXT NOT NULL, min_level TINYINT(3) DEFAULT 0 NOT NULL, song TINYINT(3) DEFAULT 0 NOT NULL, play_count INT DEFAULT 0 NOT NULL, pass VARCHAR(40) NULL, type VARCHAR(1) DEFAULT 'r' NOT NULL, CONSTRAINT PK_LEVELS PRIMARY KEY (level_id));

--changeset root:1574908323264-64
CREATE TABLE levels_reported (level_id mediumint(9) NOT NULL, version mediumint(9) DEFAULT 1 NOT NULL, creator_user_id int(9) DEFAULT 0 NOT NULL, creator_ip varchar(100) NOT NULL, title varchar(50) DEFAULT '' NOT NULL, note TINYTEXT NOT NULL, report_reason TINYTEXT NOT NULL, reporter_user_id int(9) DEFAULT 0 NOT NULL, reporter_ip varchar(100) NOT NULL, reported_time int(11) NOT NULL, archived BIT DEFAULT 0 NOT NULL, CONSTRAINT PK_LEVELS PRIMARY KEY (level_id), UNIQUE (level_id));

--changeset root:1574908323264-23
CREATE TABLE messages (message_id INT AUTO_INCREMENT NOT NULL, to_user_id MEDIUMINT DEFAULT 0 NOT NULL, from_user_id MEDIUMINT DEFAULT 0 NOT NULL, message TEXT NOT NULL, guild_message BIT DEFAULT 0 NOT NULL, time INT DEFAULT 0 NOT NULL, ip VARCHAR(100) NOT NULL, CONSTRAINT PK_MESSAGES PRIMARY KEY (message_id));

--changeset root:1574908323264-24
CREATE TABLE messages_reported (message_id INT NOT NULL, to_user_id MEDIUMINT DEFAULT 0 NOT NULL, from_user_id MEDIUMINT DEFAULT 0 NOT NULL, message TEXT NOT NULL, sent_time INT NOT NULL, reported_time INT DEFAULT 0 NOT NULL, from_ip VARCHAR(100) NOT NULL, reporter_ip VARCHAR(100) NOT NULL, archived BIT DEFAULT 0 NOT NULL, CONSTRAINT PK_MESSAGES_REPORTED PRIMARY KEY (message_id), UNIQUE (message_id));

--changeset root:1574908323264-25
CREATE TABLE mod_actions (action_id INT AUTO_INCREMENT NOT NULL, mod_id INT NOT NULL, message text NOT NULL, type VARCHAR(30) NOT NULL, time BIGINT NOT NULL, ip VARCHAR(100) NOT NULL, CONSTRAINT PK_MOD_ACTIONS PRIMARY KEY (action_id));

--changeset root:1574908323264-26
CREATE TABLE mod_power (user_id INT NOT NULL, max_ban INT DEFAULT 60 NOT NULL, bans_per_hour INT DEFAULT 10 NOT NULL, can_ban_ip TINYINT(3) DEFAULT 0 NOT NULL, can_ban_account TINYINT(3) DEFAULT 0 NOT NULL, can_unpublish_level TINYINT(3) DEFAULT 0 NOT NULL, CONSTRAINT PK_MOD_POWER PRIMARY KEY (user_id));

--changeset root:1574908323264-27
CREATE TABLE new_levels (level_id MEDIUMINT NOT NULL, time INT DEFAULT 0 NOT NULL, ip VARCHAR(100) NOT NULL, CONSTRAINT PK_NEW_LEVELS PRIMARY KEY (level_id));

--changeset root:1574908323264-28
CREATE TABLE part_awards (user_id INT NOT NULL, type VARCHAR(10) NOT NULL, part TEXT NOT NULL, dateline datetime NOT NULL);

--changeset root:1574908323264-29
CREATE TABLE pr2 (user_id MEDIUMINT DEFAULT 0 NOT NULL, `rank` TINYINT(3) DEFAULT 0 NOT NULL, exp_points INT DEFAULT 0 NOT NULL, hat_color INT DEFAULT 16777215 NOT NULL, head_color INT DEFAULT 16777215 NOT NULL, body_color INT DEFAULT 16777215 NOT NULL, feet_color INT DEFAULT 16777215 NOT NULL, hat TINYINT(3) DEFAULT 1 NOT NULL, head TINYINT(3) DEFAULT 1 NOT NULL, body TINYINT(3) DEFAULT 1 NOT NULL, feet TINYINT(3) DEFAULT 1 NOT NULL, hat_array VARCHAR(1000) DEFAULT '1' NOT NULL, head_array VARCHAR(1000) DEFAULT '1,2,3,4,5,6,7,8,9' NOT NULL, body_array VARCHAR(1000) DEFAULT '1,2,3,4,5,6,7,8,9' NOT NULL, feet_array VARCHAR(1000) DEFAULT '1,2,3,4,5,6,7,8,9' NOT NULL, speed TINYINT(3) DEFAULT 50 NOT NULL, acceleration TINYINT(3) DEFAULT 50 NOT NULL, jumping TINYINT(3) DEFAULT 50 NOT NULL, hat_color_2 INT DEFAULT 16777215 NOT NULL, head_color_2 INT DEFAULT 16777215 NOT NULL, body_color_2 INT DEFAULT 16777215 NOT NULL, feet_color_2 INT DEFAULT 16777215 NOT NULL, CONSTRAINT PK_PR2 PRIMARY KEY (user_id));

--changeset root:1574908323264-30
CREATE TABLE promotion_log (power TINYINT(3) NULL, message VARCHAR(100) NULL, time INT NULL);

--changeset root:1574908323264-31
CREATE TABLE purchases (purchase_id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, guild_id INT NOT NULL, product VARCHAR(15) NOT NULL, date datetime NOT NULL, kong_id VARCHAR(100) NOT NULL, order_id INT DEFAULT 0 NOT NULL, CONSTRAINT PK_PURCHASES PRIMARY KEY (purchase_id));

--changeset root:1574908323264-32
CREATE TABLE rank_token_rentals (guild_id INT NOT NULL, date datetime NOT NULL, user_id INT NOT NULL);

--changeset root:1574908323264-33
CREATE TABLE rank_tokens (user_id INT NOT NULL, used_tokens TINYINT(3) DEFAULT 0 NOT NULL, available_tokens TINYINT(3) DEFAULT 0 NOT NULL, CONSTRAINT PK_RANK_TOKENS PRIMARY KEY (user_id));

--changeset root:1574908323264-34
CREATE TABLE ratings (level_id MEDIUMINT DEFAULT 0 NOT NULL, user_id MEDIUMINT DEFAULT 0 NOT NULL, rating TINYINT(3) DEFAULT 0 NOT NULL, weight TINYINT(3) DEFAULT 0 NOT NULL, time INT DEFAULT 0 NOT NULL, ip VARCHAR(100) NOT NULL);

--changeset root:1574908323264-35
CREATE TABLE recent_logins (user_id INT NOT NULL, ip VARCHAR(100) NOT NULL, country VARCHAR(2) NOT NULL, date datetime NOT NULL);

--changeset root:1574908323264-36
CREATE TABLE servers (server_id INT AUTO_INCREMENT NOT NULL, server_name VARCHAR(20) NOT NULL, address VARCHAR(20) NOT NULL, port INT NOT NULL, expire_date datetime NOT NULL, population INT DEFAULT 0 NOT NULL, status VARCHAR(10) DEFAULT 'down' NOT NULL, active TINYINT(3) DEFAULT 1 NOT NULL, salt VARCHAR(40) DEFAULT 'QHE0NSNwKWZZQVEhU19xMA==' NOT NULL, guild_id INT DEFAULT 0 NOT NULL, tournament BIT DEFAULT 0 NOT NULL, happy_hour INT DEFAULT 0 NOT NULL, CONSTRAINT PK_SERVERS PRIMARY KEY (server_id));

--changeset root:1574908323264-37
CREATE TABLE tokens (user_id INT NOT NULL, token VARCHAR(55) NOT NULL, time datetime NOT NULL);

--changeset root:1574908323264-38
CREATE TABLE users (user_id MEDIUMINT AUTO_INCREMENT NOT NULL, name VARCHAR(20) DEFAULT '' NOT NULL, email VARCHAR(100) DEFAULT '' NOT NULL, register_ip VARCHAR(100) NOT NULL, ip VARCHAR(100) NOT NULL, time INT DEFAULT 0 NOT NULL COMMENT 'the last time this user logged in', register_time INT NOT NULL, power TINYINT(3) DEFAULT 1 NOT NULL, status VARCHAR(100) DEFAULT 'offline' NOT NULL, server_id INT DEFAULT 0 NOT NULL, read_message_id INT DEFAULT 0 NOT NULL, guild INT DEFAULT 0 NOT NULL, register_date datetime DEFAULT NOW() NOT NULL, active_date datetime DEFAULT NOW() NOT NULL, pass_hash TINYTEXT NULL, temp_pass_hash TINYTEXT NULL, CONSTRAINT PK_USERS PRIMARY KEY (user_id), UNIQUE (name));

--changeset root:1574908323264-39
ALTER TABLE campaigns ADD CONSTRAINT `level_id-campaign` UNIQUE (level_id, campaign);

--changeset root:1574908323264-40
ALTER TABLE gp ADD CONSTRAINT lookup UNIQUE (user_id, guild_id);

--changeset root:1574908323264-41
ALTER TABLE `keys` ADD CONSTRAINT pk UNIQUE (address, port);

--changeset root:1574908323264-42
ALTER TABLE friends ADD CONSTRAINT `unique` UNIQUE (user_id, friend_id);

--changeset root:1574908323264-43
ALTER TABLE ignored ADD CONSTRAINT `unique` UNIQUE (user_id, ignore_id);

--changeset root:1574908323264-62
CREATE INDEX archived ON levels_reported(archived);

--changeset root:1574908323264-44
CREATE INDEX archived ON messages_reported(archived);

--changeset root:1574908323264-45
CREATE INDEX banned_ip ON bans(banned_ip);

--changeset root:1574908323264-46
CREATE INDEX banned_user_id ON bans(banned_user_id);

--changeset root:1574908323264-47
CREATE INDEX from_user_id ON messages(from_user_id);

--changeset root:1574908323264-48
CREATE INDEX guild ON users(guild);

--changeset root:1574908323264-49
CREATE INDEX ip ON new_levels(ip);

--changeset root:1574908323264-50
CREATE INDEX ip ON ratings(ip);

--changeset root:1574908323264-51
CREATE INDEX level_id ON ratings(level_id);

--changeset root:1574908323264-52
CREATE INDEX lookup ON messages(to_user_id, time);

--changeset root:1574908323264-63
CREATE INDEX reported_time ON levels_reported(reported_time);

--changeset root:1574908323264-53
CREATE INDEX reported_time ON messages_reported(reported_time);

--changeset root:1574908323264-54
CREATE INDEX time ON new_levels(time);

--changeset root:1574908323264-55
CREATE INDEX title ON levels(title);

--changeset root:1574908323264-56
CREATE INDEX token ON tokens(token);

--changeset root:1574908323264-57
CREATE INDEX user_id ON level_backups(user_id);

--changeset root:1574908323264-58
CREATE INDEX user_id ON levels(user_id);

--changeset root:1574908323264-59
CREATE INDEX user_id ON ratings(user_id);

--changeset root:1574908323264-60
CREATE INDEX user_id ON recent_logins(user_id);

--changeset root:1574908323264-61
CREATE INDEX votes ON levels(votes);

