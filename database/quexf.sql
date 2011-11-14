-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 03, 2011 at 12:17 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-1+lenny13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `quexf`
--

-- --------------------------------------------------------

--
-- Table structure for table `boxes`
--

CREATE TABLE IF NOT EXISTS `boxes` (
  `bid` bigint(20) unsigned NOT NULL auto_increment,
  `tlx` int(11) NOT NULL,
  `tly` int(11) NOT NULL,
  `brx` int(11) NOT NULL,
  `bry` int(11) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `bgid` bigint(20) NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci default NULL,
  `label` text collate utf8_unicode_ci,
  PRIMARY KEY  (`bid`),
  KEY `bgid` (`bgid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `boxgroupstype`
--

CREATE TABLE IF NOT EXISTS `boxgroupstype` (
  `bgid` bigint(20) unsigned NOT NULL auto_increment,
  `btid` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `varname` text collate utf8_unicode_ci NOT NULL,
  `sortorder` int(11) NOT NULL,
  `label` text collate utf8_unicode_ci,
  `sid` int(11) default NULL,
  PRIMARY KEY  (`bgid`),
  KEY `btid` (`btid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `boxgrouptypes`
--

CREATE TABLE IF NOT EXISTS `boxgrouptypes` (
  `btid` int(11) NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`btid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- --------------------------------------------------------
--
-- Dumping data for table `boxgrouptypes`
--

INSERT INTO `boxgrouptypes` VALUES(0, 'Temporary');
INSERT INTO `boxgrouptypes` VALUES(1, 'Single choice');
INSERT INTO `boxgrouptypes` VALUES(2, 'Multiple choice');
INSERT INTO `boxgrouptypes` VALUES(3, 'Text');
INSERT INTO `boxgrouptypes` VALUES(4, 'Number');
INSERT INTO `boxgrouptypes` VALUES(5, 'Barcode');
INSERT INTO `boxgrouptypes` VALUES(6, 'Long text');


--
-- Table structure for table `clientquestionnaire`
--

CREATE TABLE IF NOT EXISTS `clientquestionnaire` (
  `cid` bigint(20) NOT NULL,
  `qid` bigint(20) NOT NULL,
  PRIMARY KEY  (`cid`,`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `cid` bigint(20) NOT NULL auto_increment,
  `username` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`cid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxes`
--

CREATE TABLE IF NOT EXISTS `formboxes` (
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `filled` double NOT NULL,
  PRIMARY KEY  (`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxverifychar`
--

CREATE TABLE IF NOT EXISTS `formboxverifychar` (
  `vid` int(11) NOT NULL,
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `val` char(1) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (`vid`,`bid`,`fid`),
  KEY `val` (`val`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxverifytext`
--

CREATE TABLE IF NOT EXISTS `formboxverifytext` (
  `vid` int(11) NOT NULL,
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `val` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`vid`,`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formpagenote`
--

CREATE TABLE IF NOT EXISTS `formpagenote` (
  `fpnid` int(11) NOT NULL auto_increment,
  `fid` bigint(20) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `vid` int(11) NOT NULL,
  `note` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`fpnid`),
  KEY `fid` (`fid`),
  KEY `pid` (`pid`),
  KEY `vid` (`vid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formpages`
--

CREATE TABLE IF NOT EXISTS `formpages` (
  `fid` bigint(20) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `filename` text collate utf8_unicode_ci NOT NULL,
  `image` mediumblob NOT NULL,
  `offx` int(11) default NULL COMMENT 'Offset X value',
  `offy` int(11) default NULL COMMENT 'Offset Y value',
  `costheta` double default NULL,
  `sintheta` double default NULL,
  `scalex` double default NULL,
  `scaley` double default NULL,
  `centroidx` double default NULL,
  `centroidy` double default NULL,
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  PRIMARY KEY  (`fid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE IF NOT EXISTS `forms` (
  `fid` bigint(20) NOT NULL auto_increment,
  `qid` bigint(20) NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `pfid` bigint(20) default NULL,
  `assigned_vid` bigint(20) default NULL,
  `done` int(11) NOT NULL default '0',
  `rpc_id` int(11) default NULL,
  PRIMARY KEY  (`fid`),
  KEY `assigned_vid` (`assigned_vid`),
  KEY `done` (`done`),
  KEY `pfid` (`pfid`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `missingpages`
--

CREATE TABLE IF NOT EXISTS `missingpages` (
  `mpid` bigint(20) NOT NULL auto_increment,
  `fid` bigint(20) NOT NULL,
  `image` mediumblob NOT NULL,
  PRIMARY KEY  (`mpid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrkb`
--

CREATE TABLE IF NOT EXISTS `ocrkb` (
  `kb` int(11) NOT NULL auto_increment,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`kb`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrkbboxgroup`
--

CREATE TABLE IF NOT EXISTS `ocrkbboxgroup` (
  `btid` int(11) NOT NULL,
  `kb` int(11) NOT NULL,
  `qid` int(11) NOT NULL,
  PRIMARY KEY  (`btid`,`kb`,`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ocrkbdata`
--

CREATE TABLE IF NOT EXISTS `ocrkbdata` (
  `val` char(1) character set utf8 collate utf8_bin NOT NULL,
  `m1` double NOT NULL,
  `m2` double NOT NULL,
  `m3` double NOT NULL,
  `m4` double NOT NULL,
  `m5` double NOT NULL,
  `m6` double NOT NULL,
  `m7` double NOT NULL,
  `m8` double NOT NULL,
  `m9` double NOT NULL,
  `m10` double NOT NULL,
  `m11` double NOT NULL,
  `m12` double NOT NULL,
  `m13` double NOT NULL,
  `m14` double NOT NULL,
  `m15` double NOT NULL,
  `m16` double NOT NULL,
  `v1` double NOT NULL,
  `v2` double NOT NULL,
  `v3` double NOT NULL,
  `v4` double NOT NULL,
  `v5` double NOT NULL,
  `v6` double NOT NULL,
  `v7` double NOT NULL,
  `v8` double NOT NULL,
  `v9` double NOT NULL,
  `v10` double NOT NULL,
  `v11` double NOT NULL,
  `v12` double NOT NULL,
  `v13` double NOT NULL,
  `v14` double NOT NULL,
  `v15` double NOT NULL,
  `v16` double NOT NULL,
  `kb` int(11) NOT NULL,
  PRIMARY KEY  (`val`,`kb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrprocess`
--

CREATE TABLE IF NOT EXISTS `ocrprocess` (
  `ocrprocessid` int(11) NOT NULL auto_increment,
  `fid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `vid` int(11) NOT NULL,
  `val` char(1) character set utf8 collate utf8_bin NOT NULL,
  `kb` int(11) NOT NULL,
  PRIMARY KEY  (`ocrprocessid`),
  KEY `kb` (`kb`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ocrtrain`
--

CREATE TABLE IF NOT EXISTS `ocrtrain` (
  `ocrtid` bigint(20) NOT NULL auto_increment,
  `kb` int(11) NOT NULL default '1',
  `val` char(1) character set utf8 collate utf8_bin NOT NULL,
  `f1` double NOT NULL,
  `f2` double NOT NULL,
  `f3` double NOT NULL,
  `f4` double NOT NULL,
  `f5` double NOT NULL,
  `f6` double NOT NULL,
  `f7` double NOT NULL,
  `f8` double NOT NULL,
  `f9` double NOT NULL,
  `f10` double NOT NULL,
  `f11` double NOT NULL,
  `f12` double NOT NULL,
  `f13` double NOT NULL,
  `f14` double NOT NULL,
  `f15` double NOT NULL,
  `f16` double NOT NULL,
  `fid` bigint(20) NOT NULL,
  `vid` bigint(20) NOT NULL,
  `bid` bigint(20) NOT NULL,
  PRIMARY KEY  (`ocrtid`),
  KEY `character` (`val`),
  KEY `fid` (`fid`,`vid`,`bid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `pid` bigint(20) NOT NULL auto_increment,
  `qid` bigint(20) NOT NULL,
  `pidentifierbgid` bigint(20) NOT NULL,
  `pidentifierval` varchar(16) collate utf8_unicode_ci NOT NULL,
  `tlx` int(11) NOT NULL,
  `tly` int(11) NOT NULL,
  `trx` int(11) NOT NULL,
  `try` int(11) NOT NULL,
  `blx` int(11) NOT NULL,
  `bly` int(11) NOT NULL,
  `brx` int(11) NOT NULL,
  `bry` int(11) NOT NULL,
  `rotation` double NOT NULL default '0' COMMENT 'rotation of image in radians',
  `image` mediumblob NOT NULL,
  `store` binary(1) NOT NULL default '1',
  `process` binary(1) NOT NULL default '1',
  `width` int(11) NOT NULL default '2480',
  `height` int(11) NOT NULL default '3508',
  `TL_VERT_TLX` int(11) NOT NULL default '54',
  `TL_VERT_TLY` int(11) NOT NULL default '90',
  `TL_VERT_BRX` int(11) NOT NULL default '390',
  `TL_VERT_BRY` int(11) NOT NULL default '603',
  `TL_HORI_TLX` int(11) NOT NULL default '54',
  `TL_HORI_TLY` int(11) NOT NULL default '60',
  `TL_HORI_BRX` int(11) NOT NULL default '669',
  `TL_HORI_BRY` int(11) NOT NULL default '384',
  `TR_VERT_TLX` int(11) NOT NULL default '2010',
  `TR_VERT_TLY` int(11) NOT NULL default '81',
  `TR_VERT_BRX` int(11) NOT NULL default '2433',
  `TR_VERT_BRY` int(11) NOT NULL default '639',
  `TR_HORI_TLX` int(11) NOT NULL default '1770',
  `TR_HORI_TLY` int(11) NOT NULL default '66',
  `TR_HORI_BRX` int(11) NOT NULL default '2433',
  `TR_HORI_BRY` int(11) NOT NULL default '387',
  `BL_VERT_TLX` int(11) NOT NULL default '54',
  `BL_VERT_TLY` int(11) NOT NULL default '2922',
  `BL_VERT_BRX` int(11) NOT NULL default '432',
  `BL_VERT_BRY` int(11) NOT NULL default '3402',
  `BL_HORI_TLX` int(11) NOT NULL default '54',
  `BL_HORI_TLY` int(11) NOT NULL default '3105',
  `BL_HORI_BRX` int(11) NOT NULL default '672',
  `BL_HORI_BRY` int(11) NOT NULL default '3405',
  `BR_VERT_TLX` int(11) NOT NULL default '2028',
  `BR_VERT_TLY` int(11) NOT NULL default '2901',
  `BR_VERT_BRX` int(11) NOT NULL default '2433',
  `BR_VERT_BRY` int(11) NOT NULL default '3381',
  `BR_HORI_TLX` int(11) NOT NULL default '1752',
  `BR_HORI_TLY` int(11) NOT NULL default '3084',
  `BR_HORI_BRX` int(11) NOT NULL default '2433',
  `BR_HORI_BRY` int(11) NOT NULL default '3384',
  `VERT_WIDTH` int(11) NOT NULL default '6',
  `HORI_WIDTH` int(11) NOT NULL default '6',
  PRIMARY KEY  (`pid`),
  UNIQUE KEY `pidentifierval` (`pidentifierval`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process`
--

CREATE TABLE IF NOT EXISTS `process` (
  `process_id` bigint(20) NOT NULL auto_increment,
  `type` int(11) NOT NULL default '1',
  `start` datetime NOT NULL,
  `stop` datetime default NULL,
  `kill` tinyint(1) NOT NULL default '0',
  `data` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process_log`
--

CREATE TABLE IF NOT EXISTS `process_log` (
  `process_log_id` bigint(20) NOT NULL auto_increment,
  `process_id` bigint(20) NOT NULL,
  `datetime` datetime NOT NULL,
  `data` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`process_log_id`),
  KEY `process_id` (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processforms`
--

CREATE TABLE IF NOT EXISTS `processforms` (
  `pfid` bigint(20) NOT NULL auto_increment,
  `filepath` varchar(1024) collate utf8_unicode_ci NOT NULL,
  `filehash` char(40) collate utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `allowanother` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`pfid`),
  KEY `filepath` (`filepath`(255)),
  KEY `filehash` (`filehash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questionnaires`
--

CREATE TABLE IF NOT EXISTS `questionnaires` (
  `qid` bigint(20) NOT NULL auto_increment,
  `description` text collate utf8_unicode_ci NOT NULL,
  `sheets` int(11) NOT NULL,
  `page_size` enum('A4','A3') collate utf8_unicode_ci NOT NULL default 'A4',
  `rpc_server_url` text collate utf8_unicode_ci COMMENT 'XML RPC server to send verified data to',
  `rpc_username` text collate utf8_unicode_ci,
  `rpc_password` text collate utf8_unicode_ci,
  `limesurvey_sid` int(11) default NULL,
  PRIMARY KEY  (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `sid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `title` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`sid`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions2`
--

CREATE TABLE IF NOT EXISTS `sessions2` (
  `sesskey` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `expiry` datetime NOT NULL,
  `expireref` varchar(250) collate utf8_unicode_ci default '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `sessdata` longtext collate utf8_unicode_ci,
  PRIMARY KEY  (`sesskey`),
  KEY `sess2_expiry` (`expiry`),
  KEY `sess2_expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifierquestionnaire`
--

CREATE TABLE IF NOT EXISTS `verifierquestionnaire` (
  `vid` bigint(20) NOT NULL,
  `qid` bigint(20) NOT NULL,
  PRIMARY KEY  (`vid`,`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifiers`
--

CREATE TABLE IF NOT EXISTS `verifiers` (
  `vid` int(11) NOT NULL auto_increment,
  `description` text collate utf8_unicode_ci NOT NULL,
  `currentfid` bigint(20) default NULL,
  `http_username` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`vid`),
  UNIQUE KEY `http_username` (`http_username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worklog`
--

CREATE TABLE IF NOT EXISTS `worklog` (
  `vid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `assigned` datetime NOT NULL,
  `completed` datetime NOT NULL,
  PRIMARY KEY  (`vid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
