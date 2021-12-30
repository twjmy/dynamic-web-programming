-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2020-12-23 15:29:33
-- 伺服器版本： 10.4.17-MariaDB
-- PHP 版本： 8.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `blackjack`
--

-- --------------------------------------------------------

--
-- 資料表結構 `account_data`
--

CREATE TABLE `account_data` (
  `uid` text COLLATE utf8_unicode_ci NOT NULL,
  `pwd` text COLLATE utf8_unicode_ci NOT NULL,
  `balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `record_data`
--

CREATE TABLE `record_data` (
  `Time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Username` text COLLATE utf8_unicode_ci NOT NULL,
  `Balance` int(11) NOT NULL,
  `Resault` int(11) NOT NULL,
  `Ante` int(11) NOT NULL,
  `Point` int(11) NOT NULL,
  `BankerPoint` int(11) NOT NULL,
  `Note` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
