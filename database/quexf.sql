-- phpMyAdmin SQL Dump
-- version 2.11.3
-- http://www.phpmyadmin.net
--
-- Host: database.dcarf
-- Generation Time: Jan 14, 2008 at 02:43 PM
-- Server version: 5.0.32
-- PHP Version: 5.2.0-8+etch9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

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
  KEY `bgid` (`bgid`)
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
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

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
-- Table structure for table `differences`
--

CREATE TABLE IF NOT EXISTS `differences` (
  `did` bigint(20) NOT NULL auto_increment,
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  PRIMARY KEY  (`did`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxes`
--

CREATE TABLE IF NOT EXISTS `formboxes` (
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `filled` double NOT NULL,
  PRIMARY KEY  (`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formboxverifychar`
--

CREATE TABLE IF NOT EXISTS `formboxverifychar` (
  `vid` int(11) NOT NULL,
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `val` char(1) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`vid`,`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

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
  PRIMARY KEY  (`fid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `ocrtrain`
--

CREATE TABLE IF NOT EXISTS `ocrtrain` (
  `val` char(1) collate utf8_unicode_ci NOT NULL,
  `r1` tinyint(1) unsigned NOT NULL,
  `r2` tinyint(1) unsigned NOT NULL,
  `r3` tinyint(1) unsigned NOT NULL,
  `r4` tinyint(1) unsigned NOT NULL,
  `r5` tinyint(1) unsigned NOT NULL,
  `r6` tinyint(1) unsigned NOT NULL,
  `r7` tinyint(1) unsigned NOT NULL,
  `r8` tinyint(1) unsigned NOT NULL,
  `r9` tinyint(1) unsigned NOT NULL,
  `r10` tinyint(1) unsigned NOT NULL,
  `r11` tinyint(1) unsigned NOT NULL,
  `r12` tinyint(1) unsigned NOT NULL,
  `r13` tinyint(1) unsigned NOT NULL,
  `r14` tinyint(1) unsigned NOT NULL,
  `r15` tinyint(1) unsigned NOT NULL,
  `r16` tinyint(1) unsigned NOT NULL,
  `r17` tinyint(1) unsigned NOT NULL,
  `r18` tinyint(1) unsigned NOT NULL,
  `r19` tinyint(1) unsigned NOT NULL,
  `r20` tinyint(1) unsigned NOT NULL,
  `r21` tinyint(1) unsigned NOT NULL,
  `r22` tinyint(1) unsigned NOT NULL,
  `r23` tinyint(1) unsigned NOT NULL,
  `r24` tinyint(1) unsigned NOT NULL,
  `r25` tinyint(1) unsigned NOT NULL,
  `ratio` tinyint(1) unsigned NOT NULL,
  `fid` bigint(20) NOT NULL,
  KEY `character` (`val`),
  KEY `formid` (`fid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;



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
  PRIMARY KEY  (`pid`),
  UNIQUE KEY `pidentifierval` (`pidentifierval`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process`
--

CREATE TABLE `process` (
  `process_id` bigint(20) NOT NULL auto_increment,
  `start` datetime NOT NULL,
  `stop` datetime default NULL,
  `kill` tinyint(1) NOT NULL default '0',
  `data` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`process_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processforms`
--

CREATE TABLE `processforms` (
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
  PRIMARY KEY  (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `sid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL,
  `description` text NOT NULL,
  `title` text NOT NULL,
  PRIMARY KEY  (`sid`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions2`
--

CREATE TABLE IF NOT EXISTS `sessions2` (
  `sesskey` varchar(64) NOT NULL default '',
  `expiry` datetime NOT NULL,
  `expireref` varchar(250) default '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `sessdata` longtext,
  PRIMARY KEY  (`sesskey`),
  KEY `sess2_expiry` (`expiry`),
  KEY `sess2_expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifierquestionnaire`
--

CREATE TABLE IF NOT EXISTS `verifierquestionnaire` (
  `vid` bigint(20) NOT NULL,
  `qid` bigint(20) NOT NULL,
  PRIMARY KEY  (`vid`,`qid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifiers`
--

CREATE TABLE IF NOT EXISTS `verifiers` (
  `vid` int(11) NOT NULL auto_increment,
  `description` text NOT NULL,
  `currentfid` bigint(20) default NULL,
  `http_username` varchar(255) NOT NULL,
  PRIMARY KEY  (`vid`),
  UNIQUE KEY `http_username` (`http_username`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clientquestionnaire`
--

CREATE TABLE IF NOT EXISTS `clientquestionnaire` (
  `cid` bigint(20) NOT NULL,
  `qid` bigint(20) NOT NULL,
  PRIMARY KEY  (`cid`,`qid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `cid` bigint(20) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`cid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


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


