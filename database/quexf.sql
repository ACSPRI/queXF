-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 22, 2014 at 01:17 PM
-- Server version: 5.5.38
-- PHP Version: 5.3.10-1ubuntu3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


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
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tlx` int(11) NOT NULL,
  `tly` int(11) NOT NULL,
  `brx` int(11) NOT NULL,
  `bry` int(11) NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `bgid` int(10) unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`bid`),
  KEY `bgid` (`bgid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `boxgroupstype`
--

CREATE TABLE IF NOT EXISTS `boxgroupstype` (
  `bgid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `btid` tinyint(3) unsigned NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `varname` text COLLATE utf8_unicode_ci NOT NULL,
  `sortorder` int(11) NOT NULL,
  `label` text COLLATE utf8_unicode_ci,
  `sid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`bgid`),
  KEY `btid` (`btid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `boxgrouptypes`
--

CREATE TABLE IF NOT EXISTS `boxgrouptypes` (
  `btid` tinyint(3) unsigned NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`btid`)
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
  `cid` int(10) unsigned NOT NULL,
  `qid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cid`,`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxes`
--

CREATE TABLE IF NOT EXISTS `formboxes` (
  `bid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `filled` float NOT NULL,
  PRIMARY KEY (`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxverifychar`
--

CREATE TABLE IF NOT EXISTS `formboxverifychar` (
  `fbvcid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) unsigned NOT NULL,
  `vid` smallint(5) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `val` char(1) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`fbvcid`),
  KEY `val` (`val`),
  KEY `bid` (`bid`),
  KEY `fid` (`fid`),
  KEY `vid` (`vid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxverifytext`
--

CREATE TABLE IF NOT EXISTS `formboxverifytext` (
  `fbvtid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vid` smallint(5) unsigned NOT NULL,
  `bid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `val` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`fbvtid`),
  KEY `bid` (`bid`),
  KEY `fid` (`fid`),
  KEY `vid` (`vid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formpagenote`
--

CREATE TABLE IF NOT EXISTS `formpagenote` (
  `fpnid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `vid` smallint(5) unsigned NOT NULL,
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`fpnid`),
  KEY `fid` (`fid`),
  KEY `pid` (`pid`),
  KEY `vid` (`vid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formpages`
--

CREATE TABLE IF NOT EXISTS `formpages` (
  `fpid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `filename` text COLLATE utf8_unicode_ci NOT NULL,
  `image` mediumblob NOT NULL,
  `offx` smallint(6) DEFAULT NULL COMMENT 'Offset X value',
  `offy` smallint(6) DEFAULT NULL COMMENT 'Offset Y value',
  `costheta` float DEFAULT NULL,
  `sintheta` float DEFAULT NULL,
  `scalex` float DEFAULT NULL,
  `scaley` float DEFAULT NULL,
  `centroidx` float DEFAULT NULL,
  `centroidy` float DEFAULT NULL,
  `width` smallint(5) unsigned NOT NULL DEFAULT '0',
  `height` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fpid`),
  UNIQUE KEY `fid` (`fid`,`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE IF NOT EXISTS `forms` (
  `fid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qid` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `pfid` int(10) unsigned DEFAULT NULL,
  `assigned_vid` smallint(5) unsigned DEFAULT NULL,
  `done` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `rpc_id` int(10) unsigned DEFAULT NULL,
  `assigned` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  PRIMARY KEY (`fid`),
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
  `mpid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `image` mediumblob NOT NULL,
  PRIMARY KEY (`mpid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrkb`
--

CREATE TABLE IF NOT EXISTS `ocrkb` (
  `kb` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`kb`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrkbboxgroup`
--

CREATE TABLE IF NOT EXISTS `ocrkbboxgroup` (
  `btid` tinyint(3) unsigned NOT NULL,
  `kb` int(10) unsigned NOT NULL,
  `qid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`btid`,`kb`,`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrkbdata`
--

CREATE TABLE IF NOT EXISTS `ocrkbdata` (
  `val` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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
  `kb` int(10) unsigned NOT NULL,
  PRIMARY KEY (`val`,`kb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrprocess`
--

CREATE TABLE IF NOT EXISTS `ocrprocess` (
  `ocrprocessid` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `bid` int(10) unsigned NOT NULL,
  `vid` smallint(5) unsigned NOT NULL,
  `val` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `kb` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ocrprocessid`),
  KEY `kb` (`kb`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocrtrain`
--

CREATE TABLE IF NOT EXISTS `ocrtrain` (
  `ocrtid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kb` int(10) unsigned NOT NULL DEFAULT '1',
  `val` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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
  `fid` int(10) unsigned NOT NULL,
  `vid` smallint(5) unsigned NOT NULL,
  `bid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ocrtid`),
  KEY `character` (`val`),
  KEY `fid` (`fid`,`vid`,`bid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qid` int(10) unsigned NOT NULL,
  `pidentifierval` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `tlx` int(11) NOT NULL,
  `tly` int(11) NOT NULL,
  `trx` int(11) NOT NULL,
  `try` int(11) NOT NULL,
  `blx` int(11) NOT NULL,
  `bly` int(11) NOT NULL,
  `brx` int(11) NOT NULL,
  `bry` int(11) NOT NULL,
  `rotation` float NOT NULL DEFAULT '0' COMMENT 'rotation of image in radians',
  `image` mediumblob NOT NULL,
  `store` binary(1) NOT NULL DEFAULT '1',
  `process` binary(1) NOT NULL DEFAULT '1',
  `width` int(11) NOT NULL DEFAULT '2480',
  `height` int(11) NOT NULL DEFAULT '3508',
  `TL_VERT_TLX` int(11) NOT NULL DEFAULT '54',
  `TL_VERT_TLY` int(11) NOT NULL DEFAULT '90',
  `TL_VERT_BRX` int(11) NOT NULL DEFAULT '390',
  `TL_VERT_BRY` int(11) NOT NULL DEFAULT '603',
  `TL_HORI_TLX` int(11) NOT NULL DEFAULT '54',
  `TL_HORI_TLY` int(11) NOT NULL DEFAULT '60',
  `TL_HORI_BRX` int(11) NOT NULL DEFAULT '669',
  `TL_HORI_BRY` int(11) NOT NULL DEFAULT '384',
  `TR_VERT_TLX` int(11) NOT NULL DEFAULT '2010',
  `TR_VERT_TLY` int(11) NOT NULL DEFAULT '81',
  `TR_VERT_BRX` int(11) NOT NULL DEFAULT '2433',
  `TR_VERT_BRY` int(11) NOT NULL DEFAULT '639',
  `TR_HORI_TLX` int(11) NOT NULL DEFAULT '1770',
  `TR_HORI_TLY` int(11) NOT NULL DEFAULT '66',
  `TR_HORI_BRX` int(11) NOT NULL DEFAULT '2433',
  `TR_HORI_BRY` int(11) NOT NULL DEFAULT '387',
  `BL_VERT_TLX` int(11) NOT NULL DEFAULT '54',
  `BL_VERT_TLY` int(11) NOT NULL DEFAULT '2922',
  `BL_VERT_BRX` int(11) NOT NULL DEFAULT '432',
  `BL_VERT_BRY` int(11) NOT NULL DEFAULT '3402',
  `BL_HORI_TLX` int(11) NOT NULL DEFAULT '54',
  `BL_HORI_TLY` int(11) NOT NULL DEFAULT '3105',
  `BL_HORI_BRX` int(11) NOT NULL DEFAULT '672',
  `BL_HORI_BRY` int(11) NOT NULL DEFAULT '3405',
  `BR_VERT_TLX` int(11) NOT NULL DEFAULT '2028',
  `BR_VERT_TLY` int(11) NOT NULL DEFAULT '2901',
  `BR_VERT_BRX` int(11) NOT NULL DEFAULT '2433',
  `BR_VERT_BRY` int(11) NOT NULL DEFAULT '3381',
  `BR_HORI_TLX` int(11) NOT NULL DEFAULT '1752',
  `BR_HORI_TLY` int(11) NOT NULL DEFAULT '3084',
  `BR_HORI_BRX` int(11) NOT NULL DEFAULT '2433',
  `BR_HORI_BRY` int(11) NOT NULL DEFAULT '3384',
  `usepagesetup` tinyint(1) NOT NULL DEFAULT '0',
  `VERT_WIDTH` int(11) NOT NULL DEFAULT '6',
  `HORI_WIDTH` int(11) NOT NULL DEFAULT '6',
  `VERT_WIDTH_BOX` int(11) NOT NULL DEFAULT '54',
  `HORI_WIDTH_BOX` int(11) NOT NULL DEFAULT '54',
  PRIMARY KEY (`pid`),
  UNIQUE KEY `pidentifierval` (`pidentifierval`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process`
--

CREATE TABLE IF NOT EXISTS `process` (
  `process_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `start` datetime NOT NULL,
  `stop` datetime DEFAULT NULL,
  `kill` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processforms`
--

CREATE TABLE IF NOT EXISTS `processforms` (
  `pfid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filepath` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `filehash` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `allowanother` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pfid`),
  KEY `filepath` (`filepath`(255)),
  KEY `filehash` (`filehash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process_log`
--

CREATE TABLE IF NOT EXISTS `process_log` (
  `process_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `process_id` int(10) unsigned NOT NULL,
  `datetime` datetime NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`process_log_id`),
  KEY `process_id` (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questionnaires`
--

CREATE TABLE IF NOT EXISTS `questionnaires` (
  `qid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `sheets` int(11) NOT NULL,
  `page_size` enum('A4','A3') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A4',
  `rpc_server_url` text COLLATE utf8_unicode_ci COMMENT 'XML RPC server to send verified data to',
  `rpc_username` text COLLATE utf8_unicode_ci,
  `rpc_password` text COLLATE utf8_unicode_ci,
  `limesurvey_sid` int(11) DEFAULT NULL,
  PRIMARY KEY (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qid` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sid`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions2`
--

CREATE TABLE IF NOT EXISTS `sessions2` (
  `sesskey` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `expiry` datetime NOT NULL,
  `expireref` varchar(250) COLLATE utf8_unicode_ci DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `sessdata` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`sesskey`),
  KEY `sess2_expiry` (`expiry`),
  KEY `sess2_expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifierquestionnaire`
--

CREATE TABLE IF NOT EXISTS `verifierquestionnaire` (
  `vid` smallint(5) unsigned NOT NULL,
  `qid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`vid`,`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifiers`
--

CREATE TABLE IF NOT EXISTS `verifiers` (
  `vid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `currentfid` int(10) unsigned DEFAULT NULL,
  `http_username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`vid`),
  UNIQUE KEY `http_username` (`http_username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
