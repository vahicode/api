-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 18, 2018 at 09:14 AM
-- Server version: 10.0.34-MariaDB-0ubuntu0.16.04.1
-- PHP Version: 7.0.30-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vahi_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','superadmin') NOT NULL,
  `login` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eyes`
--

CREATE TABLE `eyes` (
  `id` int(11) NOT NULL,
  `notes` mediumtext NOT NULL,
  `admin` int(3) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pictures`
--

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `hash` char(40) NOT NULL,
  `eye` int(4) DEFAULT NULL,
  `height` int(4) NOT NULL,
  `width` int(4) NOT NULL,
  `scale` float NOT NULL,
  `x` float NOT NULL,
  `y` float NOT NULL,
  `zone1` tinyint(1) NOT NULL DEFAULT '0',
  `zone2` tinyint(1) NOT NULL DEFAULT '0',
  `zone3` tinyint(1) NOT NULL DEFAULT '0',
  `zone4` tinyint(1) NOT NULL DEFAULT '0',
  `zone5` tinyint(1) NOT NULL DEFAULT '0',
  `zone6` tinyint(1) NOT NULL DEFAULT '0',
  `zone7` tinyint(1) NOT NULL DEFAULT '0',
  `zone8` tinyint(1) NOT NULL DEFAULT '0',
  `zone9` tinyint(1) NOT NULL DEFAULT '0',
  `zone10` tinyint(1) NOT NULL DEFAULT '0',
  `zone11` tinyint(1) NOT NULL DEFAULT '0',
  `zone12` tinyint(1) NOT NULL DEFAULT '0',
  `zone13` tinyint(1) NOT NULL DEFAULT '0',
  `admin` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user` int(4) NOT NULL,
  `eye` int(3) NOT NULL,
  `time` datetime NOT NULL,
  `v1` int(1) NOT NULL DEFAULT '0',
  `v2` int(1) NOT NULL DEFAULT '0',
  `v3` int(1) NOT NULL DEFAULT '0',
  `v4` int(1) NOT NULL DEFAULT '0',
  `v5` int(1) NOT NULL DEFAULT '0',
  `v6` int(1) NOT NULL DEFAULT '0',
  `v7` int(1) NOT NULL DEFAULT '0',
  `v8` int(1) NOT NULL DEFAULT '0',
  `v9` int(1) NOT NULL DEFAULT '0',
  `v10` int(1) NOT NULL DEFAULT '0',
  `v11` int(1) NOT NULL DEFAULT '0',
  `v12` int(1) NOT NULL DEFAULT '0',
  `v13` int(1) NOT NULL DEFAULT '0',
  `h1` int(1) NOT NULL DEFAULT '0',
  `h2` int(1) NOT NULL DEFAULT '0',
  `h3` int(1) NOT NULL DEFAULT '0',
  `h4` int(1) NOT NULL DEFAULT '0',
  `h5` int(1) NOT NULL DEFAULT '0',
  `h6` int(1) NOT NULL DEFAULT '0',
  `h7` int(1) NOT NULL DEFAULT '0',
  `h8` int(1) NOT NULL DEFAULT '0',
  `h9` int(1) NOT NULL DEFAULT '0',
  `h10` int(1) NOT NULL DEFAULT '0',
  `h11` int(1) NOT NULL DEFAULT '0',
  `h12` int(1) NOT NULL DEFAULT '0',
  `h13` int(1) NOT NULL DEFAULT '0',
  `i1` int(1) NOT NULL DEFAULT '0',
  `i2` int(1) NOT NULL DEFAULT '0',
  `i3` int(1) NOT NULL DEFAULT '0',
  `i4` int(1) NOT NULL DEFAULT '0',
  `i5` int(1) NOT NULL DEFAULT '0',
  `i6` int(1) NOT NULL DEFAULT '0',
  `i7` int(1) NOT NULL DEFAULT '0',
  `i8` int(1) NOT NULL DEFAULT '0',
  `i9` int(1) NOT NULL DEFAULT '0',
  `i10` int(1) NOT NULL DEFAULT '0',
  `i11` int(1) NOT NULL DEFAULT '0',
  `i12` int(1) NOT NULL DEFAULT '0',
  `i13` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(4) NOT NULL,
  `invite` varchar(8) NOT NULL,
  `notes` mediumtext,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `login` datetime NOT NULL,
  `admin` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `eyes`
--
ALTER TABLE `eyes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin` (`admin`);

--
-- Indexes for table `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash` (`hash`),
  ADD KEY `eye` (`eye`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invite` (`invite`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `eyes`
--
ALTER TABLE `eyes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `pictures`
--
ALTER TABLE `pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
