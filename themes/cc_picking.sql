-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 21, 2022 at 05:18 PM
-- Server version: 5.7.26
-- PHP Version: 5.6.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cityb2b`
--

-- --------------------------------------------------------

--
-- Table structure for table `cc_picking`
--

CREATE TABLE `cc_picking` (
  `id` int(11) NOT NULL,
  `orderId` varchar(50) NOT NULL,
  `userId` int(11) NOT NULL,
  `createTime` int(10) NOT NULL,
  `order_name` varchar(400) DEFAULT NULL COMMENT 'describe what are need picking',
  `business_userId` int(11) DEFAULT NULL COMMENT 'business id',
  `coupon_status` varchar(50) NOT NULL DEFAULT 'p01',
  `delivery_fees` decimal(10,2) NOT NULL DEFAULT '0.00',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `displayName` varchar(100) DEFAULT NULL COMMENT 'customer business name',
  `address` varchar(500) DEFAULT NULL,
  `house_number` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message_to_driver` text,
  `country` varchar(50) DEFAULT NULL,
  `postalcode` varchar(20) DEFAULT NULL,
  `logistic_schedule_id` int(11) NOT NULL DEFAULT '0',
  `logistic_truck_No` int(11) DEFAULT NULL,
  `logistic_sequence_No` int(11) DEFAULT NULL,
  `logistic_stop_No` int(6) DEFAULT NULL,
  `logistic_delivery_date` int(11) DEFAULT NULL COMMENT 'which day do the delivery',
  `logisitic_schedule_time` int(11) DEFAULT NULL COMMENT 'the delivery schedule tim',
  `logistic_delay_time` int(11) DEFAULT NULL,
  `logistic_arrived_time` int(11) DEFAULT NULL,
  `logistic_failed_type` int(11) NOT NULL DEFAULT '0' COMMENT '0 为成功 ，1 未联系上卖家 2 错误地址 3 错误物品 4 物品丢失 5 xxx',
  `logistic_driver_code` int(11) DEFAULT NULL,
  `logistic_priority` varchar(20) NOT NULL DEFAULT 'Medium' COMMENT 'Critical;High;Medium;Low',
  `boxesNumber` int(11) NOT NULL DEFAULT '1',
  `edit_boxesNumber` int(11) DEFAULT '0' COMMENT '修改后的总箱数',
  `driver_receipt_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '司机收货状态 0：待收货 1：已收货',
  `receipt_picture` varchar(255) DEFAULT NULL COMMENT '收货图片上传'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cc_picking`
--

INSERT INTO `cc_picking` (`id`, `orderId`, `userId`, `createTime`, `order_name`, `business_userId`, `coupon_status`, `delivery_fees`, `first_name`, `last_name`, `displayName`, `address`, `house_number`, `street`, `city`, `state`, `phone`, `email`, `message_to_driver`, `country`, `postalcode`, `logistic_schedule_id`, `logistic_truck_No`, `logistic_sequence_No`, `logistic_stop_No`, `logistic_delivery_date`, `logisitic_schedule_time`, `logistic_delay_time`, `logistic_arrived_time`, `logistic_failed_type`, `logistic_driver_code`, `logistic_priority`, `boxesNumber`, `edit_boxesNumber`, `driver_receipt_status`, `receipt_picture`) VALUES
(33985, 'p33985', 319227, 1658377407, 'trr', 319188, 'p01', '0.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 1658419200, NULL, NULL, NULL, 0, NULL, 'Medium', 1, 0, 0, NULL),
(33986, 'p33986', 319246, 1658389581, 'fgfgdfgsrgertetertesrt', 319188, 'p01', '0.00', 'Street rrr', NULL, NULL, 'Hoppers Crossing VIC 3029, Australia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 1658505600, NULL, NULL, NULL, 0, NULL, 'Medium', 1, 0, 0, NULL),
(33987, 'p33987', 319249, 1658391264, '请提货500kg 鸡腿，送货送错了。下午两点前到', 319188, 'p01', '0.00', 'JASON', NULL, NULL, '1845 Sturt St, Alfredton VIC 3350, Australia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 1658332800, NULL, NULL, NULL, 0, NULL, 'Medium', 1, 0, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cc_picking`
--
ALTER TABLE `cc_picking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`orderId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `createTime` (`createTime`),
  ADD KEY `business_userId` (`business_userId`),
  ADD KEY `logistic_delivery_date` (`logistic_delivery_date`),
  ADD KEY `logistic_delivery_date_2` (`logistic_delivery_date`),
  ADD KEY `logistic_schedule_id` (`logistic_schedule_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cc_picking`
--
ALTER TABLE `cc_picking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33988;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
