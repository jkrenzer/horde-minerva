-- $Horde: incubator/minerva/scripts/sql/minerva.mysql.sql,v 1.34 2008/09/12 16:22:10 jan Exp $

CREATE TABLE IF NOT EXISTS `minerva_articles` (
  `invoice_id` int(255) unsigned NOT NULL,
  `article_id` varchar(255) NOT NULL,
  `article_order` tinyint(1) unsigned NOT NULL default '0',
  `article_name` varchar(255) NOT NULL,
  `article_price` float unsigned NOT NULL default '0',
  `article_qt` float unsigned NOT NULL,
  `article_unit` float unsigned NOT NULL default '0',
  `article_discount` float unsigned NOT NULL default '0',
  `article_tax` int(5) unsigned NOT NULL,
  `article_total` float unsigned NOT NULL default '0',
  KEY `invoice_id` (`invoice_id`)
);

CREATE TABLE `minerva_banks` (
  `account` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort` int(1) unsigned NOT NULL,
  `updated` int(11) unsigned NOT NULL,
  `created` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`account`)
);

CREATE TABLE `minerva_clients` (
  `invoice_id` int(10) unsigned NOT NULL,
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postal_address` varchar(255) NOT NULL,
  `vat` varchar(255) NOT NULL,
  `obligated` int(1) NOT NULL,
  PRIMARY KEY  (`invoice_id`)
);

CREATE TABLE `minerva_currencies` (
  `invoice_id` int(10) unsigned NOT NULL,
  `int_curr_symbol` char(3) NOT NULL,
  `exchange_rate` decimal(6,3) unsigned NOT NULL default '0.000',
  `decimal_point` char(1) default NULL,
  `thousands_sep` char(1) default NULL,
  `currency_symbol` varchar(3) NOT NULL default '',
  `mon_decimal_point` char(1) default NULL,
  `mon_thousands_sep` char(1) default NULL,
  `positive_sign` char(1) default NULL,
  `negative_sign` char(1) default NULL,
  `int_frac_digits` tinyint(3) unsigned NOT NULL default '0',
  `frac_digits` tinyint(3) unsigned NOT NULL default '0',
  `p_cs_precedes` tinyint(1) unsigned NOT NULL default '0',
  `p_sep_by_space` tinyint(1) unsigned NOT NULL default '0',
  `n_cs_precedes` tinyint(1) unsigned NOT NULL default '0',
  `n_sep_by_space` tinyint(1) unsigned NOT NULL default '0',
  `p_sign_posn` tinyint(1) unsigned NOT NULL default '0',
  `n_sign_posn` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`int_curr_symbol`,`invoice_id`),
  KEY `exchange_rate` (`exchange_rate`)
);

CREATE TABLE `minerva_emails` (
  `id` int(1) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `minerva_tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `minerva_invoices` (
  `invoice_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `expire` int(100) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `service` varchar(255) NOT NULL,
  `total` float unsigned NOT NULL,
  `tax` float unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `place` varchar(255) NOT NULL,
  `updated` int(11) unsigned NOT NULL,
  `comment` varchar(255) NOT NULL,
  `tag` int(11) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY  (`invoice_id`),
  UNIQUE KEY `name` (`name`,`type`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `date` (`date`)
);

CREATE TABLE `minerva_items` (
  `id` int unsigned NOT NULL auto_increment,
  `model` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` float unsigned NOT NULL,
  `discount` float unsigned NOT NULL,
  `tax` int unsigned NOT NULL,
  `unit` int(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `minerva_log` (
  `log_id` int unsigned NOT NULL auto_increment,
  `invoice_id` int(10) unsigned NOT NULL,
  `horde_uid` varchar(32) NOT NULL,
  `log_time` int(11) unsigned NOT NULL,
  `log_host` varchar(50) NOT NULL,
  `log_type` varchar(32) NOT NULL,
  `log_data` text NOT NULL,
  KEY `invoice_id` (`invoice_id`),
  PRIMARY KEY  (`log_id`)
);

CREATE TABLE `minerva_recurrences` (
  `invoice_id` int(11) NOT NULL,
  `invoice_name` varchar(32) NOT NULL,
  `horde_uid` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `articles` int(1) unsigned NOT NULL,
  `client` int(1) unsigned NOT NULL,
  `draft` int(10) unsigned NOT NULL,
  `sendto` varchar(255) default NULL,
  `rstatus` varchar(32) default NULL,
  `rstart` date NOT NULL,
  `rend` int(11) unsigned NOT NULL,
  `rinterval` int(11) unsigned NOT NULL,
  `roccurred` int(10) unsigned NOT NULL,
  `rlast` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`invoice_id`)
);

CREATE TABLE `minerva_outcome` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `recived` date NOT NULL,
  `paid` date default NULL,
  `due` date default NULL,
  `client_name` varchar(255) NOT NULL,
  `client_vat` varchar(255) NOT NULL,
  `total` decimal(10,3) unsigned NOT NULL,
  `intend` varchar(255) default NULL,
  `refference` varchar(255) default NULL,
  `total_tax` decimal(8,3) unsigned default NULL,
  `bank` int(1) unsigned default '0',
  `currency` char(3) NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `tag` int(11) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY  (`id`),
  KEY `due` (`due`),
  KEY `paid` (`paid`)
);

CREATE TABLE `minerva_resellers` (
  `reseller_id` varchar(255) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `percentage` decimal(5,2) unsigned NOT NULL,
  PRIMARY KEY  (`reseller_id`(32),`client_id`(32))
);

CREATE TABLE `minerva_statuses` (
  `id` varchar(32) NOT NULL,
  `sort` int(1) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `minerva_taxes` (
  `invoice_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` decimal(6,2) unsigned NOT NULL,
  PRIMARY KEY  (`invoice_id`,`id`)
);

CREATE TABLE `minerva_types` (
  `id` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort` int(1) unsigned NOT NULL default '0',
  `offset` tinyint(3) unsigned NOT NULL,
  `statuses` varchar(255) default NULL,
  `updated` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `minerva_units` (
  `id` int(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `sort` int(1) unsigned NOT NULL,
  `updated` int(11) unsigned NOT NULL,
  `created` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sort` (`sort`)
);

