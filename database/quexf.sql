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
  PRIMARY KEY  (`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `boxeschar`
--
CREATE TABLE IF NOT EXISTS `boxeschar` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`sortorder` int(11)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `boxesfillable`
--
CREATE TABLE IF NOT EXISTS `boxesfillable` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `boxesgroupstypes`
--
CREATE TABLE IF NOT EXISTS `boxesgroupstypes` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`qid` bigint(20)
,`varname` text
,`sortorder` int(11)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `boxesnumber`
--
CREATE TABLE IF NOT EXISTS `boxesnumber` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`sortorder` int(11)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `boxesbarcode`
--
CREATE TABLE IF NOT EXISTS `boxesbarcode` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`sortorder` int(11)
);
-- --------------------------------------------------------



--
-- Stand-in structure for view `boxessinglemultiple`
--
CREATE TABLE IF NOT EXISTS `boxessinglemultiple` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`sortorder` int(11)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `boxestofill`
--
CREATE TABLE IF NOT EXISTS `boxestofill` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`filename` text
,`fid` bigint(20)
,`image` blob
,`offx` int(11)
,`offy` int(11)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Stand-in structure for view `boxestype`
--
CREATE TABLE IF NOT EXISTS `boxestype` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Table structure for table `boxgroups`
--

CREATE TABLE IF NOT EXISTS `boxgroups` (
  `bgid` bigint(20) unsigned NOT NULL,
  `bid` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`bgid`,`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `boxgroupstype`
--

CREATE TABLE IF NOT EXISTS `boxgroupstype` (
  `bgid` bigint(20) unsigned NOT NULL auto_increment,
  `btid` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `varname` text NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY  (`bgid`),
  KEY `btid` (`btid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `boxgrouptypes`
--

CREATE TABLE IF NOT EXISTS `boxgrouptypes` (
  `btid` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`btid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `differences`
--

CREATE TABLE IF NOT EXISTS `differences` (
  `did` bigint(20) NOT NULL auto_increment,
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  PRIMARY KEY  (`did`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `formboxes`
--

CREATE TABLE IF NOT EXISTS `formboxes` (
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `filled` double NOT NULL,
  PRIMARY KEY  (`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `formboxesgroupstype`
--
CREATE TABLE IF NOT EXISTS `formboxesgroupstype` (
`bid` bigint(20)
,`fid` bigint(20)
,`filled` double
,`bgid` bigint(20) unsigned
,`btid` int(11)
,`width` int(11)
,`varname` text
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `formboxestoverify`
--
CREATE TABLE IF NOT EXISTS `formboxestoverify` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`fid` bigint(20)
,`filled` double
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `formboxestoverify2`
--
CREATE TABLE IF NOT EXISTS `formboxestoverify2` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`fid` bigint(20)
,`offx` int(11)
,`offy` int(11)
,`filled` double
,`sortorder` int(11)
,`val` char(1)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `formboxestoverifychar`
--
CREATE TABLE IF NOT EXISTS `formboxestoverifychar` (
`bid` bigint(20) unsigned
,`tlx` int(11)
,`tly` int(11)
,`brx` int(11)
,`bry` int(11)
,`pid` bigint(20)
,`btid` int(11)
,`bgid` bigint(20) unsigned
,`fid` bigint(20)
,`image` blob
,`offx` int(11)
,`offy` int(11)
,`filled` double
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `formboxgroupssinglemin`
--
CREATE TABLE IF NOT EXISTS `formboxgroupssinglemin` (
`min( filled )` double
,`fid` bigint(20)
,`bgid` bigint(20) unsigned
,`bid` bigint(20)
);

--
-- Table structure for table `formboxverifychar`
--

CREATE TABLE IF NOT EXISTS `formboxverifychar` (
  `vid` int(11) NOT NULL,
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `val` char(1) default NULL,
  PRIMARY KEY  (`vid`,`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `formboxverifytext`
--

CREATE TABLE IF NOT EXISTS `formboxverifytext` (
  `vid` int(11) NOT NULL,
  `bid` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `val` longtext NOT NULL,
  PRIMARY KEY  (`vid`,`bid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `formpages`
--

CREATE TABLE IF NOT EXISTS `formpages` (
  `fid` bigint(20) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `filename` text NOT NULL,
  `image` blob NOT NULL,
  `offx` int(11) default NULL COMMENT 'Offset X value',
  `offy` int(11) default NULL COMMENT 'Offset Y value',
  PRIMARY KEY  (`fid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE IF NOT EXISTS `forms` (
  `fid` bigint(20) NOT NULL auto_increment,
  `qid` bigint(20) NOT NULL,
  `description` text NOT NULL,
  `assigned_vid` bigint(20) default NULL,
  `done` int(11) NOT NULL default '0',
  PRIMARY KEY  (`fid`),
  KEY `assigned_vid` (`assigned_vid`),
  KEY `done` (`done`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `missingpages`
--

CREATE TABLE IF NOT EXISTS `missingpages` (
  `mpid` bigint(20) NOT NULL auto_increment,
  `fid` bigint(20) NOT NULL,
  `image` blob NOT NULL,
  PRIMARY KEY  (`mpid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `pid` bigint(20) NOT NULL auto_increment,
  `qid` bigint(20) NOT NULL,
  `pidentifierbgid` bigint(20) NOT NULL,
  `pidentifierval` varchar(16) NOT NULL,
  `tlx` int(11) NOT NULL,
  `tly` int(11) NOT NULL,
  `trx` int(11) NOT NULL,
  `try` int(11) NOT NULL,
  `blx` int(11) NOT NULL,
  `bly` int(11) NOT NULL,
  `brx` int(11) NOT NULL,
  `bry` int(11) NOT NULL,
  `image` blob NOT NULL,
  `store` binary(1) NOT NULL default '1',
  `process` binary(1) NOT NULL default '1',
  PRIMARY KEY  (`pid`),
  UNIQUE KEY `pidentifierval` (`pidentifierval`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `questionnaires`
--

CREATE TABLE IF NOT EXISTS `questionnaires` (
  `qid` bigint(20) NOT NULL auto_increment,
  `description` text NOT NULL,
  `sheets` int(11) NOT NULL,
  `page_size` enum('A4','A3') NOT NULL default 'A4',
  PRIMARY KEY  (`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `verifierquestionnaire`
--

CREATE TABLE IF NOT EXISTS `verifierquestionnaire` (
  `vid` bigint(20) NOT NULL,
  `qid` bigint(20) NOT NULL,
  PRIMARY KEY  (`vid`,`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure for view `boxeschar`
--
DROP TABLE IF EXISTS `boxeschar`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxeschar` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`t`.`btid` AS `btid`,`g`.`bgid` AS `bgid`,`t`.`sortorder` AS `sortorder` from ((`boxes` `b` join `boxgroups` `g`) join `boxgroupstype` `t`) where ((`b`.`bid` = `g`.`bid`) and (`g`.`bgid` = `t`.`bgid`) and (`t`.`btid` = 3));

-- --------------------------------------------------------

--
-- Structure for view `boxesfillable`
--
DROP TABLE IF EXISTS `boxesfillable`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxesfillable` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`t`.`btid` AS `btid`,`g`.`bgid` AS `bgid` from ((`boxes` `b` join `boxgroups` `g`) join `boxgroupstype` `t`) where ((`b`.`bid` = `g`.`bid`) and (`g`.`bgid` = `t`.`bgid`) and ((`t`.`btid` = 1) or (`t`.`btid` = 2) or (`t`.`btid` = 3) or (`t`.`btid` = 4)));

-- --------------------------------------------------------

--
-- Structure for view `boxesgroupstypes`
--
DROP TABLE IF EXISTS `boxesgroupstypes`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxesgroupstypes` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`t`.`btid` AS `btid`,`g`.`bgid` AS `bgid`,`p`.`qid` AS `qid`,`t`.`width` AS `width`,`t`.`varname` AS `varname`,`t`.`sortorder` AS `sortorder` from (((`boxes` `b` join `boxgroups` `g`) join `boxgroupstype` `t`) join `pages` `p`) where ((`b`.`bid` = `g`.`bid`) and (`g`.`bgid` = `t`.`bgid`) and (`p`.`pid` = `b`.`pid`));

-- --------------------------------------------------------

--
-- Structure for view `boxesnumber`
--
DROP TABLE IF EXISTS `boxesnumber`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxesnumber` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`t`.`btid` AS `btid`,`g`.`bgid` AS `bgid`,`t`.`sortorder` AS `sortorder` from ((`boxes` `b` join `boxgroups` `g`) join `boxgroupstype` `t`) where ((`b`.`bid` = `g`.`bid`) and (`g`.`bgid` = `t`.`bgid`) and (`t`.`btid` = 4));

-- --------------------------------------------------------

--
-- Structure for view `boxesbarcode`
--
DROP TABLE IF EXISTS `boxesbarcode`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxesbarcode` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`t`.`btid` AS `btid`,`g`.`bgid` AS `bgid`,`t`.`sortorder` AS `sortorder` from ((`boxes` `b` join `boxgroups` `g`) join `boxgroupstype` `t`) where ((`b`.`bid` = `g`.`bid`) and (`g`.`bgid` = `t`.`bgid`) and (`t`.`btid` = 5));

-- --------------------------------------------------------



--
-- Structure for view `boxessinglemultiple`
--
DROP TABLE IF EXISTS `boxessinglemultiple`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxessinglemultiple` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`t`.`btid` AS `btid`,`g`.`bgid` AS `bgid`,`t`.`sortorder` AS `sortorder` from ((`boxes` `b` join `boxgroups` `g`) join `boxgroupstype` `t`) where ((`b`.`bid` = `g`.`bid`) and (`g`.`bgid` = `t`.`bgid`) and ((`t`.`btid` = 1) or (`t`.`btid` = 2)));

-- --------------------------------------------------------

--
-- Structure for view `boxestofill`
--
DROP TABLE IF EXISTS `boxestofill`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxestofill` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`b`.`btid` AS `btid`,`f`.`filename` AS `filename`,`f`.`fid` AS `fid`,`f`.`image` AS `image`,`f`.`offx` AS `offx`,`f`.`offy` AS `offy` from ((`boxesfillable` `b` left join `formpages` `f` on((`f`.`pid` = `b`.`pid`))) left join `formboxes` `fb` on(((`fb`.`bid` = `b`.`bid`) and (`fb`.`fid` = `f`.`fid`)))) where isnull(`fb`.`filled`) order by `b`.`pid`,`f`.`fid`;

-- --------------------------------------------------------

--
-- Structure for view `boxestype`
--
DROP TABLE IF EXISTS `boxestype`;

CREATE ALGORITHM=UNDEFINED  VIEW `boxestype` AS select `b`.`bid` AS `bid`,`b`.`tlx` AS `tlx`,`b`.`tly` AS `tly`,`b`.`brx` AS `brx`,`b`.`bry` AS `bry`,`b`.`pid` AS `pid`,`t`.`btid` AS `btid` from ((`boxes` `b` join `boxgroups` `g`) join `boxgroupstype` `t`) where ((`b`.`bid` = `g`.`bid`) and (`g`.`bgid` = `t`.`bgid`));

-- --------------------------------------------------------

--
-- Structure for view `formboxesgroupsbelow90`
--
DROP TABLE IF EXISTS `formboxesgroupsbelow90`;

CREATE ALGORITHM=UNDEFINED  VIEW `formboxesgroupsbelow90` AS select `formboxesgroupstype`.`fid` AS `fid`,`formboxesgroupstype`.`bgid` AS `bgid`,`formboxesgroupstype`.`bid` AS `bid` from `formboxesgroupstype` where ((`formboxesgroupstype`.`btid` = 1) or ((`formboxesgroupstype`.`btid` = 2) and (`formboxesgroupstype`.`filled` < 0.90))) group by `formboxesgroupstype`.`fid`,`formboxesgroupstype`.`bgid`;

-- --------------------------------------------------------

--
-- Structure for view `formboxesgroupstype`
--
DROP TABLE IF EXISTS `formboxesgroupstype`;

CREATE ALGORITHM=UNDEFINED  VIEW `formboxesgroupstype` AS select `fb`.`bid` AS `bid`,`fb`.`fid` AS `fid`,`fb`.`filled` AS `filled`,`bg`.`bgid` AS `bgid`,`bt`.`btid` AS `btid`,`bt`.`width` AS `width`,`bt`.`varname` AS `varname` from ((`formboxes` `fb` join `boxgroups` `bg`) join `boxgroupstype` `bt`) where ((`fb`.`bid` = `bg`.`bid`) and (`bg`.`bgid` = `bt`.`bgid`));

-- --------------------------------------------------------

--
-- Structure for view `formboxestoverify`
--
DROP TABLE IF EXISTS `formboxestoverify`;

CREATE ALGORITHM=UNDEFINED  VIEW `formboxestoverify` AS select `bc`.`bid` AS `bid`,`bc`.`tlx` AS `tlx`,`bc`.`tly` AS `tly`,`bc`.`brx` AS `brx`,`bc`.`bry` AS `bry`,`bc`.`pid` AS `pid`,`bc`.`btid` AS `btid`,`bc`.`bgid` AS `bgid`,`fp`.`fid` AS `fid`,`fbox`.`filled` AS `filled` from (((`boxesgroupstypes` `bc` left join `formpages` `fp` on((`fp`.`pid` = `bc`.`pid`))) left join `formboxverifychar` `fb` on(((`fb`.`vid` = 0) and (`fb`.`bid` = `bc`.`bid`) and (`fb`.`fid` = `fp`.`fid`)))) left join `formboxes` `fbox` on(((`fbox`.`bid` = `bc`.`bid`) and (`fbox`.`fid` = `fp`.`fid`)))) where (isnull(`fb`.`val`) and (`bc`.`btid` > 0)) order by `fp`.`fid`,`bc`.`pid`;

-- --------------------------------------------------------

--
-- Structure for view `formboxestoverify2`
--
DROP TABLE IF EXISTS `formboxestoverify2`;

CREATE ALGORITHM=UNDEFINED  VIEW `formboxestoverify2` AS (select `bc`.`bid` AS `bid`,`bc`.`tlx` AS `tlx`,`bc`.`tly` AS `tly`,`bc`.`brx` AS `brx`,`bc`.`bry` AS `bry`,`bc`.`pid` AS `pid`,`bc`.`btid` AS `btid`,`bc`.`bgid` AS `bgid`,`fp`.`fid` AS `fid`,`fp`.`offx` AS `offx`,`fp`.`offy` AS `offy`,`fbox`.`filled` AS `filled`,`bc`.`sortorder` AS `sortorder`,`fb2`.`val` AS `val` from ((((`boxesgroupstypes` `bc` left join `formpages` `fp` on((`fp`.`pid` = `bc`.`pid`))) left join `formboxverifychar` `fb` on(((`fb`.`vid` > 0) and (`fb`.`bid` = `bc`.`bid`) and (`fb`.`fid` = `fp`.`fid`)))) left join `formboxverifychar` `fb2` on(((`fb2`.`vid` = 0) and (`fb2`.`bid` = `bc`.`bid`) and (`fb2`.`fid` = `fp`.`fid`)))) left join `formboxes` `fbox` on(((`fbox`.`bid` = `bc`.`bid`) and (`fbox`.`fid` = `fp`.`fid`)))) where ((`bc`.`btid` > 0) and isnull(`fb`.`val`)));

-- --------------------------------------------------------

--
-- Structure for view `formboxestoverifychar`
--
DROP TABLE IF EXISTS `formboxestoverifychar`;

CREATE ALGORITHM=UNDEFINED  VIEW `formboxestoverifychar` AS select `bc`.`bid` AS `bid`,`bc`.`tlx` AS `tlx`,`bc`.`tly` AS `tly`,`bc`.`brx` AS `brx`,`bc`.`bry` AS `bry`,`bc`.`pid` AS `pid`,`bc`.`btid` AS `btid`,`bc`.`bgid` AS `bgid`,`fp`.`fid` AS `fid`,`fp`.`image` AS `image`,`fp`.`offx` AS `offx`,`fp`.`offy` AS `offy`,`fbox`.`filled` AS `filled` from (((`boxeschar` `bc` left join `formpages` `fp` on((`fp`.`pid` = `bc`.`pid`))) left join `formboxverifychar` `fb` on(((`fb`.`vid` = 0) and (`fb`.`bid` = `bc`.`bid`) and (`fb`.`fid` = `fp`.`fid`)))) left join `formboxes` `fbox` on(((`fbox`.`bid` = `bc`.`bid`) and (`fbox`.`fid` = `fp`.`fid`)))) where isnull(`fb`.`val`) order by `fp`.`fid`,`bc`.`pid`;

-- --------------------------------------------------------

--
-- Structure for view `formboxgroupssinglemin`
--
DROP TABLE IF EXISTS `formboxgroupssinglemin`;

CREATE ALGORITHM=UNDEFINED  VIEW `formboxgroupssinglemin` AS select min(`formboxesgroupstype`.`filled`) AS `min( filled )`,`formboxesgroupstype`.`fid` AS `fid`,`formboxesgroupstype`.`bgid` AS `bgid`,`formboxesgroupstype`.`bid` AS `bid` from `formboxesgroupstype` where (`formboxesgroupstype`.`btid` = 1) group by `formboxesgroupstype`.`fid`,`formboxesgroupstype`.`bgid`;

-- --------------------------------------------------------
--
-- Dumping data for table `boxgrouptypes`
--

INSERT INTO `boxgrouptypes` VALUES(0, 'Temporary');
INSERT INTO `boxgrouptypes` VALUES(1, 'Single choice');
INSERT INTO `boxgrouptypes` VALUES(2, 'Multiple choice');
INSERT INTO `boxgrouptypes` VALUES(3, 'Text');
INSERT INTO `boxgrouptypes` VALUES(4, 'Number');
INSERT INTO `boxgrouptypes` VALUES(5, 'Interleaved 2 of 5');
INSERT INTO `boxgrouptypes` VALUES(6, 'Long text');


