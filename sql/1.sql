DROP DATABASE IF EXISTS `triage_system`;

CREATE DATABASE `triage_system`;

USE `triage_system`;


CREATE TABLE `mxcel_triaging_category_rule_master` (
  `triaging_category_id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(300) DEFAULT NULL,
  `added_by` int(1) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`triaging_category_id`),
  KEY `deleted` (`deleted`)
);

INSERT INTO `mxcel_triaging_category_rule_master` VALUES (1, 'Vitals', 0, CURRENT_TIMESTAMP, 0), (2, 'Symptoms', 0, CURRENT_TIMESTAMP, 0);

CREATE TABLE `mxcel_triaging_color_master` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(300) DEFAULT NULL,
  `hexadecimal_code` varchar(400) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `priority` tinyint(1) DEFAULT NULL,
  `added_by` int(1) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`color_id`),
  KEY `is_default` (`is_default`),
  KEY `priority` (`priority`),
  KEY `deleted` (`deleted`),
  KEY `added_by` (`added_by`),
  KEY `added_on` (`added_on`)
);

INSERT INTO `mxcel_triaging_color_master` VALUES (1,'Orange','#FF4500',0,1,1,CURRENT_TIMESTAMP,0),(2,'Red','#CD5C5C',0,2,1,CURRENT_TIMESTAMP,0),(3,'Yellow','#ffff00',0,3,1,CURRENT_TIMESTAMP,0),(4,'Green','#008000',1,4,1,CURRENT_TIMESTAMP,0),(5,'Black','#000000',0,5,1,CURRENT_TIMESTAMP,0);

CREATE TABLE `mxcel_medical_management_age_group_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `age_group_name` varchar(200) DEFAULT NULL,
  `age_from` int(11) DEFAULT '0',
  `age_to` int(11) DEFAULT '0',
  `gender` varchar(20) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `age_from` (`age_from`),
  KEY `age_to` (`age_to`),
  KEY `gender` (`gender`),
  KEY `deleted` (`deleted`)
);

CREATE TABLE `mxcel_triage_group_master` (
  `triage_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `triage_id` int(11) NOT NULL,
  `age_group_id` int(11) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`triage_group_id`),
  KEY `triage_id` (`triage_id`),
  KEY `age_group_id` (`age_group_id`),
  KEY `deleted` (`deleted`)
);

CREATE TABLE `mxcel_triaging_master` (
  `triage_id` int(11) NOT NULL AUTO_INCREMENT,
  `added_by` int(1) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `freeze` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`triage_id`),
  KEY `freeze` (`freeze`),
  KEY `deleted` (`deleted`)
);

CREATE TABLE `mxcel_triaging_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `triage_id` int(11) DEFAULT NULL,
  `triage_name` varchar(300) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `description` text,
  `added_by` int(1) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `triage_id` (`triage_id`),
  KEY `color_id` (`color_id`),
  KEY `deleted` (`deleted`),
  KEY `added_by` (`added_by`),
  KEY `added_on` (`added_on`)
);

CREATE TABLE `mxcel_triage_rule_master` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `triage_group_id` int(11) NOT NULL,
  `triage_id` int(11) NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`rule_id`),
  KEY `triage_group_id` (`triage_group_id`),
  KEY `triage_id` (`triage_id`),
  KEY `deleted` (`deleted`),
  KEY `rule_id_3` (`rule_id`)
);

CREATE TABLE `mxcel_triage_rule_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) NOT NULL,
  `triage_group_id` int(11) NOT NULL,
  `triage_id` int(11) NOT NULL,
  `catetory_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `operator` text,
  `value` text,
  `added_by` int(11) DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rule_id` (`rule_id`),
  KEY `triage_group_id` (`triage_group_id`),
  KEY `triage_id` (`triage_id`),
  KEY `entity_id` (`entity_id`),
  KEY `catetory_id` (`catetory_id`),
  KEY `deleted` (`deleted`)
);

CREATE TABLE `mxcel_vital_master` (  
  `vital_id` INT(11) NOT NULL AUTO_INCREMENT,
  `vital_name` VARCHAR(200) NOT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`vital_id`) ,
  INDEX (`deleted`)
);

CREATE TABLE `mxcel_chief_complaint_list_master` (
  `chief_complaint_id` int(11) NOT NULL AUTO_INCREMENT,
  `chief_complaint` varchar(200) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`chief_complaint_id`),
  KEY `deleted` (`deleted`)
);

CREATE TABLE `mxcel_triage_prescription_details` (  
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `triage_id` INT(11) NOT NULL,
  `triage_group_id` INT(11) NOT NULL,
  `prescription` JSON NOT NULL,
  `added_on` DATETIME NOT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) ,
  INDEX (`triage_id`),
  INDEX (`triage_group_id`),
  INDEX (`deleted`)
);

CREATE TABLE `mxcel_triage_diagnosis_details` (  
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `triage_id` INT(11) NOT NULL,
  `triage_group_id` INT(11) NOT NULL,
  `diagnosis` JSON NOT NULL,
  `added_on` DATETIME NOT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) ,
  INDEX (`triage_id`),
  INDEX (`triage_group_id`),
  INDEX (`deleted`)
);