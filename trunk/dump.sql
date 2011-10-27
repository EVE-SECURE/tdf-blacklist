-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 27, 2011 at 03:59 AM
-- Server version: 5.1.54
-- PHP Version: 5.3.5-1ubuntu7.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tdf_idTest`
--
CREATE DATABASE `tdf_idTest` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `tdf_idTest`;

-- --------------------------------------------------------

--
-- Table structure for table `id`
--

CREATE TABLE IF NOT EXISTS `id` (
  `characterID` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `type` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lookup_character`
--

CREATE TABLE IF NOT EXISTS `lookup_character` (
  `race` varchar(255) NOT NULL,
  `bloodline` varchar(255) NOT NULL,
  `corp` varchar(255) NOT NULL,
  `corpID` varchar(255) NOT NULL,
  `alliance` varchar(255) NOT NULL,
  `allianceID` varchar(255) NOT NULL,
  `sec` varchar(255) NOT NULL,
  `characterID` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;