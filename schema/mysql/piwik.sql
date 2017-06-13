/*
SQLyog Community v12.09 (64 bit)
MySQL - 5.6.23-log : Database - piwik216oo
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`piwik216oo` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `piwik216oo`;

/*Table structure for table `piwik_access` */

DROP TABLE IF EXISTS `piwik_access`;

CREATE TABLE `piwik_access` (
  `login` varchar(100) NOT NULL,
  `idsite` int(10) unsigned NOT NULL,
  `access` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`login`,`idsite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_access` */

/*Table structure for table `piwik_goal` */

DROP TABLE IF EXISTS `piwik_goal`;

CREATE TABLE `piwik_goal` (
  `idsite` int(11) NOT NULL,
  `idgoal` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `match_attribute` varchar(20) NOT NULL,
  `pattern` varchar(255) NOT NULL,
  `pattern_type` varchar(10) NOT NULL,
  `case_sensitive` tinyint(4) NOT NULL,
  `allow_multiple` tinyint(4) NOT NULL,
  `revenue` float NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idsite`,`idgoal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_goal` */

/*Table structure for table `piwik_log_action` */

DROP TABLE IF EXISTS `piwik_log_action`;

CREATE TABLE `piwik_log_action` (
  `idaction` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `hash` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned DEFAULT NULL,
  `url_prefix` tinyint(2) DEFAULT NULL,
  PRIMARY KEY (`idaction`),
  KEY `index_type_hash` (`type`,`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_log_action` */

/*Table structure for table `piwik_log_conversion` */

DROP TABLE IF EXISTS `piwik_log_conversion`;

CREATE TABLE `piwik_log_conversion` (
  `idvisit` int(10) unsigned NOT NULL,
  `idsite` int(10) unsigned NOT NULL,
  `idvisitor` binary(8) NOT NULL,
  `server_time` datetime NOT NULL,
  `idaction_url` int(11) DEFAULT NULL,
  `idlink_va` int(11) DEFAULT NULL,
  `idgoal` int(10) NOT NULL,
  `buster` int(10) unsigned NOT NULL,
  `idorder` varchar(100) DEFAULT NULL,
  `items` smallint(5) unsigned DEFAULT NULL,
  `url` text NOT NULL,
  `visitor_days_since_first` smallint(5) unsigned NOT NULL,
  `visitor_days_since_order` smallint(5) unsigned NOT NULL,
  `visitor_returning` tinyint(1) NOT NULL,
  `visitor_count_visits` smallint(5) unsigned NOT NULL,
  `referer_keyword` varchar(255) DEFAULT NULL,
  `referer_name` varchar(70) DEFAULT NULL,
  `referer_type` tinyint(1) unsigned DEFAULT NULL,
  `location_city` varchar(255) DEFAULT NULL,
  `location_country` char(3) NOT NULL,
  `location_latitude` float(10,6) DEFAULT NULL,
  `location_longitude` float(10,6) DEFAULT NULL,
  `location_region` char(2) DEFAULT NULL,
  `revenue` float DEFAULT NULL,
  `revenue_discount` float DEFAULT NULL,
  `revenue_shipping` float DEFAULT NULL,
  `revenue_subtotal` float DEFAULT NULL,
  `revenue_tax` float DEFAULT NULL,
  `custom_var_k1` varchar(200) DEFAULT NULL,
  `custom_var_v1` varchar(200) DEFAULT NULL,
  `custom_var_k2` varchar(200) DEFAULT NULL,
  `custom_var_v2` varchar(200) DEFAULT NULL,
  `custom_var_k3` varchar(200) DEFAULT NULL,
  `custom_var_v3` varchar(200) DEFAULT NULL,
  `custom_var_k4` varchar(200) DEFAULT NULL,
  `custom_var_v4` varchar(200) DEFAULT NULL,
  `custom_var_k5` varchar(200) DEFAULT NULL,
  `custom_var_v5` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`idvisit`,`idgoal`,`buster`),
  UNIQUE KEY `unique_idsite_idorder` (`idsite`,`idorder`),
  KEY `index_idsite_datetime` (`idsite`,`server_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_log_conversion` */

/*Table structure for table `piwik_log_conversion_item` */

DROP TABLE IF EXISTS `piwik_log_conversion_item`;

CREATE TABLE `piwik_log_conversion_item` (
  `idsite` int(10) unsigned NOT NULL,
  `idvisitor` binary(8) NOT NULL,
  `server_time` datetime NOT NULL,
  `idvisit` int(10) unsigned NOT NULL,
  `idorder` varchar(100) NOT NULL,
  `idaction_sku` int(10) unsigned NOT NULL,
  `idaction_name` int(10) unsigned NOT NULL,
  `idaction_category` int(10) unsigned NOT NULL,
  `idaction_category2` int(10) unsigned NOT NULL,
  `idaction_category3` int(10) unsigned NOT NULL,
  `idaction_category4` int(10) unsigned NOT NULL,
  `idaction_category5` int(10) unsigned NOT NULL,
  `price` float NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`idvisit`,`idorder`,`idaction_sku`),
  KEY `index_idsite_servertime` (`idsite`,`server_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_log_conversion_item` */

/*Table structure for table `piwik_log_link_visit_action` */

DROP TABLE IF EXISTS `piwik_log_link_visit_action`;

CREATE TABLE `piwik_log_link_visit_action` (
  `idlink_va` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idsite` int(10) unsigned NOT NULL,
  `idvisitor` binary(8) NOT NULL,
  `idvisit` int(10) unsigned NOT NULL,
  `idaction_url_ref` int(10) unsigned DEFAULT '0',
  `idaction_name_ref` int(10) unsigned NOT NULL,
  `custom_float` float DEFAULT NULL,
  `server_time` datetime NOT NULL,
  `idaction_name` int(10) unsigned DEFAULT NULL,
  `idaction_url` int(10) unsigned DEFAULT NULL,
  `time_spent_ref_action` int(10) unsigned NOT NULL,
  `idaction_event_action` int(10) unsigned DEFAULT NULL,
  `idaction_event_category` int(10) unsigned DEFAULT NULL,
  `idaction_content_interaction` int(10) unsigned DEFAULT NULL,
  `idaction_content_name` int(10) unsigned DEFAULT NULL,
  `idaction_content_piece` int(10) unsigned DEFAULT NULL,
  `idaction_content_target` int(10) unsigned DEFAULT NULL,
  `custom_var_k1` varchar(200) DEFAULT NULL,
  `custom_var_v1` varchar(200) DEFAULT NULL,
  `custom_var_k2` varchar(200) DEFAULT NULL,
  `custom_var_v2` varchar(200) DEFAULT NULL,
  `custom_var_k3` varchar(200) DEFAULT NULL,
  `custom_var_v3` varchar(200) DEFAULT NULL,
  `custom_var_k4` varchar(200) DEFAULT NULL,
  `custom_var_v4` varchar(200) DEFAULT NULL,
  `custom_var_k5` varchar(200) DEFAULT NULL,
  `custom_var_v5` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`idlink_va`),
  KEY `index_idvisit` (`idvisit`),
  KEY `index_idsite_servertime` (`idsite`,`server_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_log_link_visit_action` */

/*Table structure for table `piwik_log_profiling` */

DROP TABLE IF EXISTS `piwik_log_profiling`;

CREATE TABLE `piwik_log_profiling` (
  `query` text NOT NULL,
  `count` int(10) unsigned DEFAULT NULL,
  `sum_time_ms` float DEFAULT NULL,
  UNIQUE KEY `query` (`query`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_log_profiling` */

/*Table structure for table `piwik_log_visit` */

DROP TABLE IF EXISTS `piwik_log_visit`;

CREATE TABLE `piwik_log_visit` (
  `idvisit` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idsite` int(10) unsigned NOT NULL,
  `idvisitor` binary(8) NOT NULL,
  `visit_last_action_time` datetime NOT NULL,
  `config_id` binary(8) NOT NULL,
  `location_ip` varbinary(16) NOT NULL,
  `user_id` varchar(200) DEFAULT NULL,
  `visit_first_action_time` datetime NOT NULL,
  `visit_goal_buyer` tinyint(1) NOT NULL,
  `visit_goal_converted` tinyint(1) NOT NULL,
  `visitor_days_since_first` smallint(5) unsigned NOT NULL,
  `visitor_days_since_order` smallint(5) unsigned NOT NULL,
  `visitor_returning` tinyint(1) NOT NULL,
  `visitor_count_visits` smallint(5) unsigned NOT NULL,
  `visit_entry_idaction_name` int(11) unsigned NOT NULL,
  `visit_entry_idaction_url` int(11) unsigned NOT NULL,
  `visit_exit_idaction_name` int(11) unsigned NOT NULL,
  `visit_exit_idaction_url` int(11) unsigned DEFAULT '0',
  `visit_total_actions` smallint(5) unsigned NOT NULL,
  `visit_total_searches` smallint(5) unsigned NOT NULL,
  `referer_keyword` varchar(255) DEFAULT NULL,
  `referer_name` varchar(70) DEFAULT NULL,
  `referer_type` tinyint(1) unsigned DEFAULT NULL,
  `referer_url` text NOT NULL,
  `location_browser_lang` varchar(20) NOT NULL,
  `config_browser_engine` varchar(10) NOT NULL,
  `config_browser_name` varchar(10) NOT NULL,
  `config_browser_version` varchar(20) NOT NULL,
  `config_device_brand` varchar(100) DEFAULT NULL,
  `config_device_model` varchar(100) DEFAULT NULL,
  `config_device_type` tinyint(100) DEFAULT NULL,
  `config_os` char(3) NOT NULL,
  `config_os_version` varchar(100) DEFAULT NULL,
  `visit_total_events` smallint(5) unsigned NOT NULL,
  `visitor_localtime` time NOT NULL,
  `visitor_days_since_last` smallint(5) unsigned NOT NULL,
  `config_resolution` varchar(9) NOT NULL,
  `config_cookie` tinyint(1) NOT NULL,
  `config_director` tinyint(1) NOT NULL,
  `config_flash` tinyint(1) NOT NULL,
  `config_gears` tinyint(1) NOT NULL,
  `config_java` tinyint(1) NOT NULL,
  `config_pdf` tinyint(1) NOT NULL,
  `config_quicktime` tinyint(1) NOT NULL,
  `config_realplayer` tinyint(1) NOT NULL,
  `config_silverlight` tinyint(1) NOT NULL,
  `config_windowsmedia` tinyint(1) NOT NULL,
  `visit_total_time` smallint(5) unsigned NOT NULL,
  `location_city` varchar(255) DEFAULT NULL,
  `location_country` char(3) NOT NULL,
  `location_latitude` float(10,6) DEFAULT NULL,
  `location_longitude` float(10,6) DEFAULT NULL,
  `location_region` char(2) DEFAULT NULL,
  `custom_var_k1` varchar(200) DEFAULT NULL,
  `custom_var_v1` varchar(200) DEFAULT NULL,
  `custom_var_k2` varchar(200) DEFAULT NULL,
  `custom_var_v2` varchar(200) DEFAULT NULL,
  `custom_var_k3` varchar(200) DEFAULT NULL,
  `custom_var_v3` varchar(200) DEFAULT NULL,
  `custom_var_k4` varchar(200) DEFAULT NULL,
  `custom_var_v4` varchar(200) DEFAULT NULL,
  `custom_var_k5` varchar(200) DEFAULT NULL,
  `custom_var_v5` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`idvisit`),
  KEY `index_idsite_config_datetime` (`idsite`,`config_id`,`visit_last_action_time`),
  KEY `index_idsite_datetime` (`idsite`,`visit_last_action_time`),
  KEY `index_idsite_idvisitor` (`idsite`,`idvisitor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_log_visit` */

/*Table structure for table `piwik_logger_message` */

DROP TABLE IF EXISTS `piwik_logger_message`;

CREATE TABLE `piwik_logger_message` (
  `idlogger_message` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `level` varchar(16) DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`idlogger_message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_logger_message` */

/*Table structure for table `piwik_option` */

DROP TABLE IF EXISTS `piwik_option`;

CREATE TABLE `piwik_option` (
  `option_name` varchar(255) NOT NULL,
  `option_value` longtext NOT NULL,
  `autoload` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_option` */

insert  into `piwik_option`(`option_name`,`option_value`,`autoload`) values ('branding_use_custom_logo','0',1),('enableBrowserTriggerArchiving','0',1),('enableUpdateCommunicationPlugins','0',0),('MobileMessaging_DelegatedManagement','false',0),('piwikUrl','http://localhost/piwik216o/',1),('PrivacyManager.doNotTrackEnabled','0',0),('PrivacyManager.ipAnonymizerEnabled','0',0),('SitesManager_DefaultTimezone','Asia/Singapore',0),('todayArchiveTimeToLive','150',1),('UpdateCheck_LastTimeChecked','1459662257',1),('UpdateCheck_LatestVersion','2.16.0',0),('UsersManager.lastSeen.sa','1459662610',1),('version_Actions','2.16.0',1),('version_Annotations','2.16.0',1),('version_API','2.16.0',1),('version_BulkTracking','2.16.0',1),('version_cacheBuster','1.4.2',1),('version_Contents','2.16.0',1),('version_core','2.16.0',1),('version_CoreAdminHome','2.16.0',1),('version_CoreConsole','2.16.0',1),('version_CoreHome','2.16.0',1),('version_CorePluginsAdmin','2.16.0',1),('version_CoreUpdater','2.16.0',1),('version_CoreVisualizations','2.16.0',1),('version_CustomVariables','2.16.0',1),('version_Dashboard','2.16.0',1),('version_DevicePlugins','2.16.0',1),('version_DevicesDetection','2.16.0',1),('version_Diagnostics','2.16.0',1),('version_Ecommerce','2.16.0',1),('version_Events','2.16.0',1),('version_ExampleAPI','1.0',1),('version_ExamplePlugin','0.1.0',1),('version_ExampleRssWidget','1.0',1),('version_Feedback','2.16.0',1),('version_Goals','2.16.0',1),('version_Heartbeat','2.16.0',1),('version_ImageGraph','2.16.0',1),('version_Insights','2.16.0',1),('version_Installation','2.16.0',1),('version_Intl','2.16.0',1),('version_LanguagesManager','2.16.0',1),('version_Live','2.16.0',1),('version_Login','2.16.0',1),('version_LogViewer','0.2.0',1),('version_log_conversion.revenue','float default NULL',1),('version_log_conversion.revenue_discount','float default NULL',1),('version_log_conversion.revenue_shipping','float default NULL',1),('version_log_conversion.revenue_subtotal','float default NULL',1),('version_log_conversion.revenue_tax','float default NULL',1),('version_log_link_visit_action.idaction_content_interaction','INTEGER(10) UNSIGNED DEFAULT NULL',1),('version_log_link_visit_action.idaction_content_name','INTEGER(10) UNSIGNED DEFAULT NULL',1),('version_log_link_visit_action.idaction_content_piece','INTEGER(10) UNSIGNED DEFAULT NULL',1),('version_log_link_visit_action.idaction_content_target','INTEGER(10) UNSIGNED DEFAULT NULL',1),('version_log_link_visit_action.idaction_event_action','INTEGER(10) UNSIGNED DEFAULT NULL',1),('version_log_link_visit_action.idaction_event_category','INTEGER(10) UNSIGNED DEFAULT NULL',1),('version_log_link_visit_action.idaction_name','INTEGER(10) UNSIGNED',1),('version_log_link_visit_action.idaction_url','INTEGER(10) UNSIGNED DEFAULT NULL',1),('version_log_link_visit_action.server_time','DATETIME NOT NULL',1),('version_log_link_visit_action.time_spent_ref_action','INTEGER(10) UNSIGNED NOT NULL',1),('version_log_visit.config_browser_engine','VARCHAR(10) NOT NULL',1),('version_log_visit.config_browser_name','VARCHAR(10) NOT NULL',1),('version_log_visit.config_browser_version','VARCHAR(20) NOT NULL',1),('version_log_visit.config_cookie','TINYINT(1) NOT NULL',1),('version_log_visit.config_device_brand','VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',1),('version_log_visit.config_device_model','VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',1),('version_log_visit.config_device_type','TINYINT( 100 ) NULL DEFAULT NULL',1),('version_log_visit.config_director','TINYINT(1) NOT NULL',1),('version_log_visit.config_flash','TINYINT(1) NOT NULL',1),('version_log_visit.config_gears','TINYINT(1) NOT NULL',1),('version_log_visit.config_java','TINYINT(1) NOT NULL',1),('version_log_visit.config_os','CHAR(3) NOT NULL',1),('version_log_visit.config_os_version','VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',1),('version_log_visit.config_pdf','TINYINT(1) NOT NULL',1),('version_log_visit.config_quicktime','TINYINT(1) NOT NULL',1),('version_log_visit.config_realplayer','TINYINT(1) NOT NULL',1),('version_log_visit.config_resolution','VARCHAR(9) NOT NULL',1),('version_log_visit.config_silverlight','TINYINT(1) NOT NULL',1),('version_log_visit.config_windowsmedia','TINYINT(1) NOT NULL',1),('version_log_visit.location_browser_lang','VARCHAR(20) NOT NULL',1),('version_log_visit.location_city','varchar(255) DEFAULT NULL1',1),('version_log_visit.location_country','CHAR(3) NOT NULL1',1),('version_log_visit.location_latitude','float(10, 6) DEFAULT NULL1',1),('version_log_visit.location_longitude','float(10, 6) DEFAULT NULL1',1),('version_log_visit.location_region','char(2) DEFAULT NULL1',1),('version_log_visit.referer_keyword','VARCHAR(255) NULL1',1),('version_log_visit.referer_name','VARCHAR(70) NULL1',1),('version_log_visit.referer_type','TINYINT(1) UNSIGNED NULL1',1),('version_log_visit.referer_url','TEXT NOT NULL',1),('version_log_visit.user_id','VARCHAR(200) NULL',1),('version_log_visit.visitor_count_visits','SMALLINT(5) UNSIGNED NOT NULL1',1),('version_log_visit.visitor_days_since_first','SMALLINT(5) UNSIGNED NOT NULL1',1),('version_log_visit.visitor_days_since_last','SMALLINT(5) UNSIGNED NOT NULL',1),('version_log_visit.visitor_days_since_order','SMALLINT(5) UNSIGNED NOT NULL1',1),('version_log_visit.visitor_localtime','TIME NOT NULL',1),('version_log_visit.visitor_returning','TINYINT(1) NOT NULL1',1),('version_log_visit.visit_entry_idaction_name','INTEGER(11) UNSIGNED NOT NULL',1),('version_log_visit.visit_entry_idaction_url','INTEGER(11) UNSIGNED NOT NULL',1),('version_log_visit.visit_exit_idaction_name','INTEGER(11) UNSIGNED NOT NULL',1),('version_log_visit.visit_exit_idaction_url','INTEGER(11) UNSIGNED NULL DEFAULT 0',1),('version_log_visit.visit_first_action_time','DATETIME NOT NULL',1),('version_log_visit.visit_goal_buyer','TINYINT(1) NOT NULL',1),('version_log_visit.visit_goal_converted','TINYINT(1) NOT NULL',1),('version_log_visit.visit_total_actions','SMALLINT(5) UNSIGNED NOT NULL',1),('version_log_visit.visit_total_events','SMALLINT(5) UNSIGNED NOT NULL',1),('version_log_visit.visit_total_searches','SMALLINT(5) UNSIGNED NOT NULL',1),('version_log_visit.visit_total_time','SMALLINT(5) UNSIGNED NOT NULL',1),('version_MobileMessaging','2.16.0',1),('version_Monolog','2.16.0',1),('version_Morpheus','2.16.0',1),('version_MultiSites','2.16.0',1),('version_Overlay','2.16.0',1),('version_PiwikPro','2.16.0',1),('version_PrivacyManager','2.16.0',1),('version_Proxy','2.16.0',1),('version_Referrers','2.16.0',1),('version_Resolution','2.16.0',1),('version_ScheduledReports','2.16.0',1),('version_SegmentEditor','2.16.0',1),('version_SEO','2.16.0',1),('version_SitesManager','2.16.0',1),('version_Transitions','2.16.0',1),('version_UserCountry','2.16.0',1),('version_UserCountryMap','2.16.0',1),('version_UserLanguage','2.16.0',1),('version_UsersManager','2.16.0',1),('version_VisitFrequency','2.16.0',1),('version_VisitorInterest','2.16.0',1),('version_VisitsSummary','2.16.0',1),('version_VisitTime','2.16.0',1),('version_WebsiteMeasurable','2.16.0',1),('version_Widgetize','2.16.0',1);

/*Table structure for table `piwik_report` */

DROP TABLE IF EXISTS `piwik_report`;

CREATE TABLE `piwik_report` (
  `idreport` int(11) NOT NULL AUTO_INCREMENT,
  `idsite` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `idsegment` int(11) DEFAULT NULL,
  `period` varchar(10) NOT NULL,
  `hour` tinyint(4) NOT NULL DEFAULT '0',
  `type` varchar(10) NOT NULL,
  `format` varchar(10) NOT NULL,
  `reports` text NOT NULL,
  `parameters` text,
  `ts_created` timestamp NULL DEFAULT NULL,
  `ts_last_sent` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idreport`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_report` */

/*Table structure for table `piwik_segment` */

DROP TABLE IF EXISTS `piwik_segment`;

CREATE TABLE `piwik_segment` (
  `idsegment` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `definition` text NOT NULL,
  `login` varchar(100) NOT NULL,
  `enable_all_users` tinyint(4) NOT NULL DEFAULT '0',
  `enable_only_idsite` int(11) DEFAULT NULL,
  `auto_archive` tinyint(4) NOT NULL DEFAULT '0',
  `ts_created` timestamp NULL DEFAULT NULL,
  `ts_last_edit` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idsegment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_segment` */

/*Table structure for table `piwik_sequence` */

DROP TABLE IF EXISTS `piwik_sequence`;

CREATE TABLE `piwik_sequence` (
  `name` varchar(120) NOT NULL,
  `value` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_sequence` */

/*Table structure for table `piwik_session` */

DROP TABLE IF EXISTS `piwik_session`;

CREATE TABLE `piwik_session` (
  `id` varchar(255) NOT NULL,
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_session` */

/*Table structure for table `piwik_site` */

DROP TABLE IF EXISTS `piwik_site`;

CREATE TABLE `piwik_site` (
  `idsite` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) NOT NULL,
  `main_url` varchar(255) NOT NULL,
  `ts_created` timestamp NULL DEFAULT NULL,
  `ecommerce` tinyint(4) DEFAULT '0',
  `sitesearch` tinyint(4) DEFAULT '1',
  `sitesearch_keyword_parameters` text NOT NULL,
  `sitesearch_category_parameters` text NOT NULL,
  `timezone` varchar(50) NOT NULL,
  `currency` char(3) NOT NULL,
  `exclude_unknown_urls` tinyint(1) DEFAULT '0',
  `excluded_ips` text NOT NULL,
  `excluded_parameters` text NOT NULL,
  `excluded_user_agents` text NOT NULL,
  `group` varchar(250) NOT NULL,
  `type` varchar(255) NOT NULL,
  `keep_url_fragment` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idsite`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `piwik_site` */

insert  into `piwik_site`(`idsite`,`name`,`main_url`,`ts_created`,`ecommerce`,`sitesearch`,`sitesearch_keyword_parameters`,`sitesearch_category_parameters`,`timezone`,`currency`,`exclude_unknown_urls`,`excluded_ips`,`excluded_parameters`,`excluded_user_agents`,`group`,`type`,`keep_url_fragment`) values (1,'Learnet','http://localhost/mqlp','2016-04-03 05:44:50',0,1,'','','Asia/Singapore','USD',0,'','','','','website',0);

/*Table structure for table `piwik_site_setting` */

DROP TABLE IF EXISTS `piwik_site_setting`;

CREATE TABLE `piwik_site_setting` (
  `idsite` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` longtext NOT NULL,
  PRIMARY KEY (`idsite`,`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_site_setting` */

/*Table structure for table `piwik_site_url` */

DROP TABLE IF EXISTS `piwik_site_url`;

CREATE TABLE `piwik_site_url` (
  `idsite` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`idsite`,`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_site_url` */

/*Table structure for table `piwik_user` */

DROP TABLE IF EXISTS `piwik_user`;

CREATE TABLE `piwik_user` (
  `login` varchar(100) NOT NULL,
  `password` char(32) NOT NULL,
  `alias` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token_auth` char(32) NOT NULL,
  `superuser_access` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `date_registered` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`login`),
  UNIQUE KEY `uniq_keytoken` (`token_auth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_user` */

insert  into `piwik_user`(`login`,`password`,`alias`,`email`,`token_auth`,`superuser_access`,`date_registered`) values ('anonymous','','anonymous','anonymous@example.org','anonymous',0,'2016-04-03 05:44:14'),('sa','5f4dcc3b5aa765d61d8327deb882cf99','sa','test@mqspectrum.com','8e133cc5b0b4fdf8e82524bf030b7389',1,'2016-04-03 05:44:37');

/*Table structure for table `piwik_user_dashboard` */

DROP TABLE IF EXISTS `piwik_user_dashboard`;

CREATE TABLE `piwik_user_dashboard` (
  `login` varchar(100) NOT NULL,
  `iddashboard` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `layout` text NOT NULL,
  PRIMARY KEY (`login`,`iddashboard`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_user_dashboard` */

/*Table structure for table `piwik_user_language` */

DROP TABLE IF EXISTS `piwik_user_language`;

CREATE TABLE `piwik_user_language` (
  `login` varchar(100) NOT NULL,
  `language` varchar(10) NOT NULL,
  `use_12_hour_clock` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `piwik_user_language` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
