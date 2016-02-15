# ************************************************************
# Sequel Pro SQL dump
# Version 4499
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.5.42)
# Database: test1
# Generation Time: 2016-02-14 02:45:49 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table complexes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `complexes`;

CREATE TABLE `complexes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `strComplexName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table notes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `notes`;

CREATE TABLE `notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `numErf` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strKey` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `memNotes` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notes_strkey_index` (`strKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table owners
# ------------------------------------------------------------

DROP TABLE IF EXISTS `owners`;

CREATE TABLE `owners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `strIDNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TITLE` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `INITIALS` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `NAME` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strSurname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strFirstName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strHomePhoneNo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strWorkPhoneNo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strCellPhoneNo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `EMAIL` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owners_stridnumber_index` (`strIDNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table properties
# ------------------------------------------------------------

DROP TABLE IF EXISTS `properties`;

CREATE TABLE `properties` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `strSuburb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numErf` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numPortion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strStreetNo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numStreetNo` int(11) DEFAULT NULL,
  `strStreetName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strSqMeters` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strComplexNo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numComplexNo` int(11) DEFAULT NULL,
  `strComplexName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dtmRegDate` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strAmount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strBondHolder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strBondAmount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strOwners` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strIdentity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strSellers` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strTitleDeed` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `strKey` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `properties_strkey_index` (`strKey`),
  KEY `properties_stridentity_index` (`strIdentity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table streets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `streets`;

CREATE TABLE `streets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `strStreetName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
