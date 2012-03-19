-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 22 2012 г., 22:45
-- Версия сервера: 5.1.58
-- Версия PHP: 5.3.6-13ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `lily_sample`
--

-- --------------------------------------------------------

--
-- Структура таблицы `tbl_lily_account`
--

CREATE TABLE IF NOT EXISTS `tbl_lily_account` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `service` varchar(255) NOT NULL,
  `id` varchar(255) NOT NULL,
  `hidden` tinyint(1) DEFAULT NULL,
  `data` blob,
  `created` int(11) DEFAULT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `service_id` (`service`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `tbl_lily_email_account_activation`
--

CREATE TABLE IF NOT EXISTS `tbl_lily_email_account_activation` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `created` int(11) DEFAULT NULL,
  PRIMARY KEY (`code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `tbl_lily_onetime`
--

CREATE TABLE IF NOT EXISTS `tbl_lily_onetime` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `created` int(11) DEFAULT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `tbl_lily_session`
--

CREATE TABLE IF NOT EXISTS `tbl_lily_session` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) DEFAULT NULL,
  `data` blob,
  `ssid` varchar(255) NOT NULL,
  `created` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `tbl_lily_user`
--

CREATE TABLE IF NOT EXISTS `tbl_lily_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `inited` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `tbl_migration`
--

CREATE TABLE IF NOT EXISTS `tbl_migration` (
  `version` varchar(255) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tbl_migration`
--

INSERT INTO `tbl_migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1329929078),
('m120131_112629_lily_tables_create', 1329929087);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
