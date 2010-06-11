-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 11, 2010 at 12:13 AM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ninja_cloudfrontimages`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_image`
--

CREATE TABLE IF NOT EXISTS `tbl_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filePath` varchar(255) NOT NULL,
  `imageType` enum('GIF','JPG','PNG') NOT NULL,
  `version` tinyint(3) unsigned NOT NULL,
  `filePath_hashIndex` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fileNameCRC` (`filePath_hashIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `tbl_image`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_imageDimensions`
--

CREATE TABLE IF NOT EXISTS `tbl_imageDimensions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `keyname` varchar(50) NOT NULL,
  `description` varchar(500) NOT NULL,
  `width` smallint(11) unsigned NOT NULL,
  `height` smallint(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `tbl_imageDimensions`
--

INSERT INTO `tbl_imageDimensions` (`id`, `keyname`, `description`, `width`, `height`) VALUES
(1, 'thumb_small', 'Small Thumbnail', 78, 100),
(2, 'thumb_large', 'Large Thumbnail', 100, 100),
(3, 'xlarge', 'Very large', 700, 700),
(4, 'marketplace_listing', '', 200, 260),
(5, 'original', 'The original dimensions', 0, 0),
(6, 'bookthumb_carousel', 'For carousel on homepage', 130, 168),
(7, 'browsebox_thumb', 'for browsebox', 104, 134),
(8, 'thumbnail', 'for the create page upload widgets', 136, 174),
(9, 'tinythumb', 'recently viewed', 20, 30);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_imageDimensionsMap`
--

CREATE TABLE IF NOT EXISTS `tbl_imageDimensionsMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imageId` int(11) unsigned NOT NULL,
  `imageDimensionsId` int(11) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `version` tinyint(11) unsigned NOT NULL,
  PRIMARY KEY (`imageId`,`imageDimensionsId`),
  UNIQUE KEY `id` (`id`),
  KEY `imageDimensionsId` (`imageDimensionsId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `tbl_imageDimensionsMap`
--

