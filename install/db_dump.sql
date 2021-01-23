--
-- PHP Login Script SQL File
--
-- Run this SQL file to create the neccessary tables and default
-- database entries needed to get the script working.
--
-- Created by The Angry Frog - http://www.angry-frog.com

-- --------------------------------------------------------

--
-- Table structure for table `active_guests`
--

CREATE TABLE IF NOT EXISTS `active_guests` (
  `ip` varchar(15) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `active_users`
--

CREATE TABLE IF NOT EXISTS `active_users` (
  `username` varchar(30) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `banlist`
--

CREATE TABLE IF NOT EXISTS `banlist` (
  `ban_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ban_username` varchar(255) NOT NULL,
  `ban_userid` mediumint(8) unsigned NOT NULL,
  `ban_ip` varchar(40) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ban_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `configuration`
--

CREATE TABLE IF NOT EXISTS `configuration` (
  `config_name` varchar(20) NOT NULL,
  `config_value` varchar(64) NOT NULL,
  PRIMARY KEY `config_name` (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`config_name`, `config_value`) VALUES
('ACCOUNT_ACTIVATION', '1'),
('ALL_LOWERCASE', '0'),
('COOKIE_EXPIRE', '100'),
('COOKIE_PATH', '/'),
('DATE_FORMAT', 'M j, Y, g:i a'),
('EMAIL_FROM_NAME', 'PHP Login Script'),
('EMAIL_FROM_ADDR', 'email_address@your-website.com'),
('EMAIL_WELCOME', '1'),
('ENABLE_CAPTCHA', '0'),
('GUEST_TIMEOUT', '5'),
('HASH', 'sha256'),
('home_page', 'index.php'),
('login_page', 'index.php'),
('max_user_chars', '36'),
('min_user_chars', '5'),
('max_pass_chars', '120'),
('min_pass_chars', '8'),
('NO_ADMIN_REDIRECT', '1'),
('record_online_date', ''),
('record_online_users', ''),
('SITE_DESC', 'PHP Login Script'),
('SITE_NAME', 'PHP Login Script'),
('TRACK_VISITORS', '1'),
('TURN_ON_INDIVIDUAL', '0'),
('USER_HOME_PATH', '/'),
('HOME_SETBYADMIN', '1'),
('USERNAME_REGEX', 'letter_num_spaces'),
('USER_TIMEOUT', '10'),
('Version', '2.4'),
('WEB_ROOT', 'http://localhost/');

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE IF NOT EXISTS `users_groups` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) NOT NULL,
  `group_level` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`group_id`, `group_name`, `group_level`) VALUES
(1, 'Administrators', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11)  unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(36) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `password` varchar(64) default NULL,
  `usersalt` varchar(8) NOT NULL,
  `userid` varchar(32) default NULL,
  `userlevel` tinyint(1) unsigned NOT NULL,
  `email` varchar(50) default NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `previous_visit` int(11) unsigned DEFAULT 0,
  `actkey` varchar(35) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `regdate` int(11) unsigned NOT NULL,
  `lastip` varchar(15) NULL,
  `user_login_attempts` tinyint(4) NULL,
  `user_home_path` varchar(50) NULL,
  UNIQUE KEY `id` (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log_table`
--

CREATE TABLE `log_table` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` int(11) UNSIGNED NOT NULL,
  `timestamp` int(11) UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL,
  `log_operation` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
