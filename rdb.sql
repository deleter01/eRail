-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2024 at 02:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rdb`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `assign_berth` (IN `tnum` INT, IN `tdate` DATE, IN `tcoach` VARCHAR(50), IN `name` VARCHAR(50), IN `age` INT, IN `gender` VARCHAR(50), IN `pnr_no` VARCHAR(12))  NO SQL BEGIN
	DECLARE bseats INT;
    DECLARE tseats INT;
    DECLARE berth_no INT;
    DECLARE coach_no INT;
    DECLARE berth_type VARCHAR(10);
    DECLARE msg varchar(250) DEFAULT '';
    
     -- update
    IF tcoach like 'ac' THEN
        UPDATE trains_status
        SET seats_b_ac = seats_b_ac + 1
        WHERE t_number = tnum AND t_date = tdate;
    ELSE
        UPDATE trains_status
        SET seats_b_sleeper = seats_b_sleeper + 1
        WHERE t_number = tnum AND t_date = tdate;
    END IF;
    IF tcoach like 'ac' THEN
        SET tseats = 18;
        SELECT seats_b_ac
        FROM trains_status 
        WHERE t_number = tnum AND t_date = tdate
        INTO bseats;
    ELSE 
        SET tseats = 24;
        SELECT seats_b_sleeper
        FROM trains_status
        WHERE t_number = tnum AND t_date = tdate
        INTO bseats;
    END IF;
    
    -- berth_no & coach_no
    IF bseats % tseats = 0 THEN
        SET coach_no = bseats/tseats;
        SET berth_no = tseats;
    ELSE
        SET coach_no = floor(bseats/tseats) + 1;
        SET berth_no = bseats%tseats;
    END IF;
	
    -- berth_type
    IF tcoach like 'ac' THEN
        IF berth_no % 6 = 1 OR berth_no % 6 = 2 THEN
            SET berth_type = 'LB';
        ELSEIF berth_no % 6 = 3 OR berth_no % 6 = 4 THEN
            SET berth_type = 'UB';
        ELSEIF berth_no % 6 = 5 THEN
            SET berth_type = 'SL';
        ELSE
            SET berth_type = 'SU';
        END IF;
    ELSE
        IF berth_no % 8 = 1 OR berth_no % 8 = 4 THEN
            SET berth_type = 'LB';
        ELSEIF berth_no % 8 = 2 OR berth_no % 8 = 5 THEN
            SET berth_type = 'MB';
        ELSEIF berth_no % 8 = 3 OR berth_no % 8 = 6 THEN
            SET berth_type = 'UB';
        ELSEIF berth_no % 8 = 7 THEN
            SET berth_type = 'SL';
        ELSE
            SET berth_type = 'SU';
        END IF;
    END IF;
   
    -- insert
    INSERT INTO passengers 
    (name, age, gender, pnr_no, berth_no, berth_type, coach_no)
    VALUES(name, age, gender, pnr_no, berth_no, berth_type, coach_no);
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `check_admin_credentials` (IN `n` VARCHAR(10), IN `p` VARCHAR(50))  NO SQL BEGIN
	DECLARE name VARCHAR(10);
	DECLARE pass VARCHAR(50);
    DECLARE message VARCHAR(128) DEFAULT '';
    DECLARE finished INT DEFAULT 0;
	DEClARE user_info CURSOR
    	FOR SELECT * FROM admin;
	DECLARE CONTINUE HANDLER 
    	FOR NOT FOUND SET finished = 1;
        
    OPEN user_info;

	get_info: LOOP
		FETCH user_info INTO name, pass;
		IF finished = 1 THEN 
			LEAVE get_info;
		END IF;
        IF name = n AND pass = p THEN
        	SET message = 'Found';
        END IF;
 
	END LOOP get_info;
	CLOSE user_info;
    
    IF message like '' THEN
		SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = 'Invalid Username or Password';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `check_seats_availabilty` (IN `tnum` INT, IN `tdate` DATE, IN `type` VARCHAR(50), IN `num_p` INT)  NO SQL BEGIN
	DECLARE avail_a INT;
    DECLARE avail_s INT;
    DECLARE book_a INT;
    DECLARE book_s INT;
    DECLARE m1 VARCHAR(128) DEFAULT '';
    DECLARE m2 VARCHAR(128) DEFAULT '';
  
    SELECT num_ac, num_sleeper
    FROM trains
    WHERE t_number = tnum AND t_date = tdate
    INTO avail_a, avail_s;
    
    SELECT seats_b_ac, seats_b_sleeper
    FROM trains_status
    WHERE t_number = tnum AND t_date = tdate
    INTO book_a, book_s;
    
    IF type like 'ac' THEN
    	IF avail_a = 0 THEN
        	SET m1 = CONCAT('No AC Coach is available in Train- ', tnum, ' Dated- ', tdate);
        ELSEIF avail_a*18 = book_a THEN
        	SET m1 = CONCAT('AC Coaches of Train- ', tnum, ' Dated- ', tdate, ' are already booked!');
        ELSEIF avail_a*18 < book_a + num_p THEN
        	SET m1 = CONCAT('AC Coach of Train- ', tnum, ' Dated- ', tdate, ' has only ' , avail_a*18-book_a, ' seats available!'); 
        END IF;
    ELSEIF type like 'sleeper' THEN
    	IF avail_s = 0 THEN
        	SET m1 = CONCAT('No Sleeper Coach is available in Train- ', tnum, ' Dated- ', tdate);
        ELSEIF avail_s*24 = book_s THEN
        	SET m1 = CONCAT('Sleeper Coaches of Train- ', tnum, ' Dated- ', tdate, ' are already booked!');
        ELSEIF avail_s*24 < book_s + num_p THEN
        	SET m1 = CONCAT('Sleeper Coach of Train- ', tnum, ' Dated- ', tdate, ' has only ' , avail_s*24-book_s, ' seats available!'); 
        END IF;
    END IF;
    
    IF m1 not like '' THEN
		SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = m1;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `check_valid_pnr` (IN `pnr` VARCHAR(12))  NO SQL BEGIN
	DECLARE msg VARCHAR(255) DEFAULT '';
    DECLARE p VARCHAR(12);
	DECLARE finished INT DEFAULT 0;
	DEClARE ticket_info CURSOR
    	FOR SELECT pnr_no FROM ticket;
	DECLARE CONTINUE HANDLER 
    	FOR NOT FOUND SET finished = 1;
        
    OPEN ticket_info;
	get_info: LOOP
		FETCH ticket_info INTO p;
		IF finished = 1 THEN 
			LEAVE get_info;
		END IF;
        IF p like pnr THEN
        	SET msg = 'Found';
        END IF;
	END LOOP get_info;
	CLOSE ticket_info;
    
    IF msg like '' THEN
		SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = 'Please enter vaild PNR Number';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_pnr` (IN `u_name` VARCHAR(50), OUT `pnr_no` VARCHAR(12), IN `coach` VARCHAR(50), IN `t_number` INT, IN `t_date` DATE)  NO SQL BEGIN
	DECLARE p1 INT;
    DECLARE p2 INT;
    DECLARE p3 INT;
    SET p1 = LPAD(cast(conv(substring(md5(u_name), 1, 16), 16, 10)%1000 as unsigned integer), 3, '0');
    SET p2 = LPAD(FLOOR(RAND() * 999999.99), 3, '0');
    SET p3 = LPAD(cast(conv(substring(md5(CURRENT_TIMESTAMP()), 1, 16), 16, 10)%10000 as unsigned integer), 4, '0');
    SET pnr_no = RPAD(CONCAT(p1, '-', p2, '-', p3), 12, '0');
 	INSERT INTO ticket
    VALUES('',pnr_no, coach, u_name, CURRENT_TIMESTAMP(), t_number, t_date);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `passenger`
--

CREATE TABLE `passenger` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `pnr_no` varchar(12) NOT NULL,
  `berth_no` int(11) NOT NULL,
  `berth_type` varchar(10) NOT NULL,
  `coach_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `berth_type` varchar(100) NOT NULL,
  `berth_no` int(11) DEFAULT NULL,
  `coach_no` int(11) DEFAULT NULL,
  `pnr_no` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`id`, `name`, `age`, `gender`, `berth_type`, `berth_no`, `coach_no`, `pnr_no`) VALUES
(1, 'de', 12, 'Female', '864-873-1648', 2, 0, '1'),
(2, 'de', 21, 'Male', '864', 3, 0, '1'),
(3, 'qwe', 21, 'Female', 'SU', NULL, NULL, '864'),
(4, 'siamoo', 19, 'Female', 'SU', NULL, NULL, '864'),
(5, 'qqww', 12, 'Female', 'SU', NULL, NULL, '864-895-9648'),
(6, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-949-7376'),
(7, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-763-4048'),
(8, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-609-7520'),
(9, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-209-6928'),
(10, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-582-6928'),
(11, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-462-8960'),
(12, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-931-8960'),
(13, 'jamal', 21, 'Male', 'SU', NULL, NULL, '288-721-1920'),
(14, 'dfjl', 23, 'Female', 'SU', NULL, NULL, '888-443-8336'),
(15, 'Kevin is a regular c', 2, 'Female', 'LB', 1, 1, '888-575-6672'),
(16, 'jhjkhhhhhh', 22, 'Female', 'LB', 2, 1, '888-548-3344');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` bigint(20) NOT NULL,
  `role_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'LEVEL 1 ADMIN'),
(2, 'RAIL MANAGER'),
(3, 'RAIL STAFF'),
(4, 'RAIL USERS');

-- --------------------------------------------------------

--
-- Table structure for table `site_activity`
--

CREATE TABLE `site_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_name` text NOT NULL,
  `action_type` text NOT NULL,
  `table_id` text NOT NULL,
  `ip` text DEFAULT NULL,
  `browser` text DEFAULT NULL,
  `previous_data` text DEFAULT NULL,
  `present_data` text DEFAULT NULL,
  `login` int(11) DEFAULT 0,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `site_activity`
--

INSERT INTO `site_activity` (`id`, `user_id`, `table_name`, `action_type`, `table_id`, `ip`, `browser`, `previous_data`, `present_data`, `login`, `date`) VALUES
(74, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '43528705964055fc6c8ca0f996b60c02c77f043ce44142d6625e295876a22570', 1, '2024-06-21 21:03:24'),
(73, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '43528705964055fc6c8ca0f996b60c02c77f043ce44142d6625e295876a22570', 1, '2024-06-21 21:02:41'),
(72, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '43528705964055fc6c8ca0f996b60c02c77f043ce44142d6625e295876a22570', 1, '2024-06-21 21:00:15'),
(71, 1, 'users', 'Register User', '1', '::1', 'Google Chrome', '', 'f780db569e195339b4f8a9484fc4c44ada8a35b2ff7386d40c7051abfedf581d', 0, '2024-06-21 20:56:39'),
(70, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'f24d6ba35411b672f7e359358a4c6909a72d20432a2802bf46d556e8e13474fa', 1, '2024-06-21 20:39:57'),
(69, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'cfc95764c4303698dcf9eb6645ee3cb917ba3fed0322b1f11610405d23538649', 1, '2024-06-21 20:27:22'),
(68, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '37d11af4154c4a807f885ff5e7b1e82bf1cd7017626f626b170ce9e80798db55', 1, '2024-06-21 18:13:27'),
(67, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '84a8069706d116a65d2ff0e535514e443ebfdd06dd0bbd82a631589065c7c464', 1, '2024-06-21 18:13:07'),
(66, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '84a8069706d116a65d2ff0e535514e443ebfdd06dd0bbd82a631589065c7c464', 1, '2024-06-21 18:13:00'),
(65, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '909666e18597b39901935f20585a8c384810cc68113892c5feb9e4d2cd68f31a', 1, '2024-06-21 18:12:52'),
(64, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '1de8e78210f5f944f6f4838e692c48bd7d3e51fa10d8ea572280677fb5cfd5a1', 1, '2024-06-21 18:12:42'),
(63, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '091cb0816a1abcb45d83a77e6c1f4c30cf0a5a3f82b88eca8fa278aa0a03d6bb', 1, '2024-06-21 18:10:44'),
(62, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '313ae9e0999202dc5588882ca9e71dd0796c6d22f46f48f87a964d2b79be73d2', 1, '2024-06-21 18:09:25'),
(61, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'd1c1392d189e82ae995dc44a61696276296e1c5de31fb20cd228b098acbe1638', 1, '2024-06-21 17:42:06'),
(60, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '35ddecfc89db2ded4031bfc7b54453fb2757cc21d04de3654acf7e69bba918a6', 1, '2024-06-21 17:41:07'),
(59, 1, 'trains', 'Release a Train', '1', '::1', 'Google Chrome', '', '(train_number => 114,date => 2024-06-24,num_ac => 10)', 0, '2024-06-21 17:20:00'),
(58, 1, 'trains', 'Fail to log in', '1', '::1', 'Google Chrome', '', 'f5d90bcbb1a54d2abdc232eae0a50ae8262f29737b9d90607eadc71280b6f0da', 0, '2024-06-21 17:18:35'),
(75, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'd1c1392d189e82ae995dc44a61696276296e1c5de31fb20cd228b098acbe1638', 1, '2024-06-21 21:03:32'),
(76, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '1d1c32217cf1409b08a332e625885422e64e2de6da1890f46a05794e60208b35', 1, '2024-06-21 21:04:29'),
(77, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '3c90e9b4c42973372adeadf20d67b086d8ca9027a1abf944114182d61ab1041a', 1, '2024-06-21 21:07:14'),
(78, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 12:11:12'),
(79, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 13:37:37'),
(80, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 13:42:16'),
(81, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 13:44:15'),
(82, 1, 'login', 'success to log in', '1', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 13:52:24'),
(83, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 14:01:04'),
(84, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 14:08:20'),
(85, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 14:15:40'),
(86, 1, 'login', 'success to log in', '1', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 14:16:59'),
(87, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 14:24:45'),
(88, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 14:28:48'),
(89, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 14:34:35'),
(90, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 15:39:43'),
(91, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 15:40:04'),
(92, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 16:30:29'),
(93, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 16:45:59'),
(94, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 16:50:33'),
(95, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 16:52:08'),
(96, 1, 'login', 'success to log in', '1', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 17:10:04'),
(97, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-27 17:30:16'),
(98, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '9e5891bf14e35a6beb0b41bc34938a6f63c96f4388ca75aa7e55e8f56cd2cffc', 1, '2024-06-27 17:39:31'),
(99, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'e5770c6fc7545c4bbe185b324627d6112ef95bf6cdbf02d6ee3b66680d1db8a3', 1, '2024-06-27 17:39:42'),
(100, 4, 'users', 'Register User', '4', '::1', 'Google Chrome', '', '3353dd5b28783b37b4d900a8cc39d0df62d35143cdd70697c0cd0fbedd05a89c', 0, '2024-06-27 17:59:50'),
(101, 4, 'users', 'Register User', '4', '::1', 'Google Chrome', '', 'fe0b46658a88fff27b53d26e1e0fcb3ccc8607909e598fa06c75d248a8311cec', 0, '2024-06-28 06:14:43'),
(102, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 07:48:08'),
(103, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 07:50:19'),
(104, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 07:53:55'),
(105, 4, 'users', 'Fail to Register User', '4', '::1', 'Google Chrome', '', '2e7c7029ccdc0ca44da98e79a6e609245c2e667cc46e211a81890923bf259a43', 0, '2024-06-28 09:27:58'),
(106, 4, 'users', 'Register User', '4', '::1', 'Google Chrome', '', '2e7c7029ccdc0ca44da98e79a6e609245c2e667cc46e211a81890923bf259a43', 0, '2024-06-28 09:55:58'),
(107, 4, 'users', 'Register User', '4', '::1', 'Google Chrome', '', '2e7c7029ccdc0ca44da98e79a6e609245c2e667cc46e211a81890923bf259a43', 0, '2024-06-28 10:07:31'),
(108, 4, 'users', 'Register User', '4', '::1', 'Google Chrome', '', '8797a53a67fc6e2ab036bfd01da26b6230de8efb1bcfeae1ed7c3250736e428c', 0, '2024-06-28 11:08:14'),
(109, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'd78e1a0dc878d53410e951986d79043e8bb1c6021b31d95256927061981eeb05', 1, '2024-06-28 11:09:17'),
(110, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'd78e1a0dc878d53410e951986d79043e8bb1c6021b31d95256927061981eeb05', 1, '2024-06-28 11:12:23'),
(111, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'd78e1a0dc878d53410e951986d79043e8bb1c6021b31d95256927061981eeb05', 1, '2024-06-28 11:14:57'),
(112, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '0e1807b74315ace572dec554e58d6a85b85edf435a794f998f82952222c42d89', 1, '2024-06-28 21:49:04'),
(113, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'dae1b2acb47f5d7c417168eb02c4cf19f2c8f2533d377c7253b5386b3e912da2', 1, '2024-06-28 22:15:08'),
(114, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'c75e955414d4493351564700da800a6c9f29e8e2b87bddf1e968a01a4abec5c3', 1, '2024-06-28 22:25:41'),
(115, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'c75e955414d4493351564700da800a6c9f29e8e2b87bddf1e968a01a4abec5c3', 1, '2024-06-28 22:25:53'),
(116, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'd78e1a0dc878d53410e951986d79043e8bb1c6021b31d95256927061981eeb05', 1, '2024-06-28 22:26:37'),
(117, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '4a2f698efbc0224c2a996d0727e715807ccee5f6d230db19fab6ed5990d09e5c', 1, '2024-06-28 22:26:57'),
(118, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'b3fde356ad9a4a376cadc27101da1b34484c68548830864aeb9aca634cde2470', 1, '2024-06-28 22:39:21'),
(119, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '796c0bb8f50ce26e7724e48c225d7b881b8231a8774147b7d7128aa204fb5126', 1, '2024-06-28 22:42:00'),
(120, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '3d60cc7f006ea8108080288ec2b5144cb54681d169df60929bc7425c86d3b397', 1, '2024-06-28 22:42:35'),
(121, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '48a3e88006afa37ba2e3d77932bbc8379344e8c138f610966e8a40c2337485f6', 1, '2024-06-28 22:45:04'),
(122, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'dd8f20ec14b0222076944afe3a75a2d661ddfbdf3f61f258b60d34470514159a', 1, '2024-06-28 22:45:27'),
(123, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'f8914931b0f8177f66371afe5cd593ce5ccb1ef0bf782eb98075f9751dafc9a4', 1, '2024-06-28 22:51:25'),
(124, 24, 'login', 'success to log in', '24', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 22:52:12'),
(125, 24, 'login', 'success to log in', '24', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 22:52:23'),
(126, 24, 'login', 'success to log in', '24', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 22:52:49'),
(127, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '5add6000a0a1985968664e1a077c84d68b13aa10dc52a95e496a6037060d3e44', 1, '2024-06-28 22:53:01'),
(128, 24, 'login', 'success to log in', '24', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 22:53:12'),
(129, 24, 'login', 'success to log in', '24', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-28 22:54:22'),
(130, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', 'cd1ae05f5e965f441b63119413ad562eaeb60dfbc6938c15ecb990d66b89cc1d', 1, '2024-06-29 00:50:08'),
(131, 24, 'login', 'success to log in', '24', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 00:50:24'),
(132, 25, 'login', 'success to Activate Account', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:41:34'),
(133, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '2c145ce16a5e1669c1958b6dde6f6c3fb2afcf0288d81223d7e53ca6c1e26d55', 1, '2024-06-29 01:42:21'),
(134, 25, 'login', 'success to log in', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:42:36'),
(135, 25, 'login', 'success to Activate Account', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:43:58'),
(136, 25, 'login', 'success to Activate Account', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:44:33'),
(137, 0, 'login', 'Fail to Activate Account', '', '::1', 'Google Chrome', '', 'dfaa90b9f1d0497f2836e7a73b6e332125ba378ff023382491970708da76b0d5', 1, '2024-06-29 01:45:03'),
(138, 25, 'login', 'success to Activate Account', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:45:14'),
(139, 25, 'login', 'success to Activate Account', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:46:24'),
(140, 25, 'login', 'success to Activate Account', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:47:17'),
(141, 25, 'login', 'success to Activate Account', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:52:25'),
(142, 25, 'login', 'success to log in', '25', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 01:53:10'),
(143, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '5e40e040b26f24605358db468d308875cd515738ce7d7bf52a2671cadd897550', 1, '2024-06-29 01:54:28'),
(144, 27, 'login', 'success to log in', '27', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:16:45'),
(145, 27, 'login', 'success to log in', '27', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:17:36'),
(146, 27, 'login', 'success to log in', '27', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:20:57'),
(147, 27, 'login', 'success to log in', '27', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:21:29'),
(148, 27, 'login', 'success to log in', '27', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:22:15'),
(149, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '00b5bd6980687bfd90bb37100bf96455fbc2d4c79c6bcd0ba22ead9815fc1e8f', 1, '2024-06-29 13:26:25'),
(150, 26, 'login', 'success to log in', '26', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:27:23'),
(151, 26, 'login', 'success to Activate Account', '26', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:29:36'),
(152, 26, 'login', 'success to log in', '26', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:31:34'),
(153, 1, 'login', 'success to log in', '1', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:32:53'),
(154, 1, 'trains', 'Release a Train', '1', '::1', 'Google Chrome', '', '(train_number => 225,date => 2024-06-30,num_ac => 20,num_sleeper => 25,released => admin)', 0, '2024-06-29 13:33:33'),
(155, 1, 'trains_status', 'Train Status', '1', '::1', 'Google Chrome', '', '(train_number => 225,date => 2024-06-30)', 0, '2024-06-29 13:33:33'),
(156, 26, 'login', 'success to log in', '26', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:40:50'),
(157, 0, 'login', 'Fail to Activate Account', '', '::1', 'Google Chrome', '', 'dfaa90b9f1d0497f2836e7a73b6e332125ba378ff023382491970708da76b0d5', 1, '2024-06-29 13:52:18'),
(158, 28, 'login', 'success to Activate Account', '28', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:52:58'),
(159, 28, 'login', 'success to Activate Account', '28', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 13:53:48'),
(160, 26, 'login', 'success to log in', '26', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 14:02:02'),
(161, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '20f52a4ad037dbed4aea2f506030b3537582d5731cb421f242d16bed34112bce', 1, '2024-06-29 14:21:44'),
(162, 1, 'login', 'success to log in', '1', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 14:21:57'),
(163, 0, 'login', 'Fail to log in', '', '::1', 'Google Chrome', '', '20f52a4ad037dbed4aea2f506030b3537582d5731cb421f242d16bed34112bce', 1, '2024-06-29 14:43:08'),
(164, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 14:43:37'),
(165, 4, 'login', 'success to log in', '4', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 14:44:08'),
(166, 1, 'login', 'success to log in', '1', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 14:44:34'),
(167, 29, 'login', 'success to Activate Account', '29', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 14:48:33'),
(168, 29, 'login', 'success to log in', '29', '::1', 'Google Chrome', '', ' - ', 1, '2024-06-29 14:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `system_permissions`
--

CREATE TABLE `system_permissions` (
  `permission_id` bigint(20) NOT NULL,
  `permission_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_permissions`
--

INSERT INTO `system_permissions` (`permission_id`, `permission_name`) VALUES
(1, 'ADMIN_LOGIN'),
(2, 'USER_LOGIN'),
(3, 'BOOK TICKET');

-- --------------------------------------------------------

--
-- Table structure for table `system_permission_to_roles`
--

CREATE TABLE `system_permission_to_roles` (
  `ref_id` bigint(20) NOT NULL,
  `role_id` bigint(20) DEFAULT NULL,
  `permission_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_permission_to_roles`
--

INSERT INTO `system_permission_to_roles` (`ref_id`, `role_id`, `permission_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 4, 2),
(6, 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `system_users_to_roles`
--

CREATE TABLE `system_users_to_roles` (
  `ref_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `role_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_users_to_roles`
--

INSERT INTO `system_users_to_roles` (`ref_id`, `user_id`, `role_id`) VALUES
(1, 1, 1),
(2, 4, 4),
(4, 22, 4),
(5, 23, 4),
(6, 24, 4),
(7, 25, 4),
(8, 26, 4),
(9, 27, 4),
(10, 28, 4),
(11, 29, 4);

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE `ticket` (
  `id` int(11) NOT NULL,
  `pnr_no` varchar(12) NOT NULL,
  `coach` varchar(50) NOT NULL,
  `booked_by` varchar(50) NOT NULL,
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `t_number` int(11) NOT NULL,
  `t_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket`
--

INSERT INTO `ticket` (`id`, `pnr_no`, `coach`, `booked_by`, `booked_at`, `t_number`, `t_date`) VALUES
(1, '864-651-7104', 'ac', 'dele', '2024-06-19 01:01:50', 223, '2024-06-23'),
(2, '864-559-7168', 'ac', 'dele', '2024-06-19 01:09:42', 223, '2024-06-23'),
(3, '864-873-1648', 'ac', 'dele', '2024-06-19 01:10:48', 223, '2024-06-23'),
(4, '864-738-5120', 'sleeper', 'dele', '2024-06-19 01:12:12', 223, '2024-06-22'),
(5, '864-573-7360', 'sleeper', 'dele', '2024-06-19 01:24:52', 223, '2024-06-22'),
(7, '864-556-2896', 'ac', 'dele', '2024-06-20 08:32:41', 223, '2024-06-23'),
(8, '864-986-8368', 'sleeper', 'dele', '2024-06-20 08:42:13', 221, '2024-06-23'),
(9, '864-953-2272', 'sleeper', 'dele', '2024-06-20 08:44:03', 221, '2024-06-23'),
(10, '864-114-6128', 'sleeper', 'dele', '2024-06-20 08:44:51', 221, '2024-06-23'),
(11, '864-789-6144', 'ac', 'dele', '2024-06-20 08:58:14', 223, '2024-06-22'),
(12, '864-255-4576', 'ac', 'dele', '2024-06-20 09:07:38', 223, '2024-06-22'),
(13, '864-917-9024', 'ac', 'dele', '2024-06-20 09:10:49', 223, '2024-06-22'),
(14, '864-895-9648', 'ac', 'dele', '2024-06-20 09:12:16', 223, '2024-06-22'),
(15, '288-949-7376', 'sleeper', 'jamal', '2024-06-20 09:21:16', 221, '2024-06-23'),
(16, '288-763-4048', 'sleeper', 'jamal', '2024-06-20 09:21:20', 221, '2024-06-23'),
(17, '288-609-7520', 'sleeper', 'jamal', '2024-06-20 09:21:21', 221, '2024-06-23'),
(18, '288-209-6928', 'sleeper', 'jamal', '2024-06-20 09:21:22', 221, '2024-06-23'),
(19, '288-582-6928', 'sleeper', 'jamal', '2024-06-20 09:21:22', 221, '2024-06-23'),
(20, '288-462-8960', 'sleeper', 'jamal', '2024-06-20 09:21:23', 221, '2024-06-23'),
(21, '288-931-8960', 'sleeper', 'jamal', '2024-06-20 09:21:23', 221, '2024-06-23'),
(22, '288-721-1920', 'sleeper', 'jamal', '2024-06-20 09:22:26', 221, '2024-06-23'),
(23, '888-443-8336', 'ac', 'jose', '2024-06-29 11:27:49', 221, '2024-06-23'),
(24, '888-575-6672', 'ac', 'jose', '2024-06-29 11:42:31', 225, '2024-06-30'),
(25, '888-548-3344', 'ac', 'jose', '2024-06-29 12:02:33', 225, '2024-06-30');

-- --------------------------------------------------------

--
-- Table structure for table `trains`
--

CREATE TABLE `trains` (
  `id` int(11) NOT NULL,
  `num_ac` int(11) NOT NULL,
  `num_sleeper` int(11) NOT NULL,
  `released_by` varchar(100) NOT NULL,
  `t_date` date NOT NULL,
  `t_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `trains`
--

INSERT INTO `trains` (`id`, `num_ac`, `num_sleeper`, `released_by`, `t_date`, `t_number`) VALUES
(1, 20, 20, 'admin', '2024-06-22', 223),
(2, 20, 20, 'admin', '2024-06-23', 221),
(3, 20, 20, 'admin', '2024-06-23', 223),
(4, 10, 10, 'admin', '2024-06-24', 112),
(5, 10, 10, 'admin', '2024-06-24', 113),
(6, 10, 10, 'admin', '2024-06-24', 114),
(7, 20, 25, 'admin', '2024-06-30', 225);

-- --------------------------------------------------------

--
-- Table structure for table `trains_status`
--

CREATE TABLE `trains_status` (
  `id` int(11) NOT NULL,
  `seats_b_ac` int(11) NOT NULL,
  `seats_b_sleeper` int(11) NOT NULL,
  `t_date` date NOT NULL,
  `t_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `trains_status`
--

INSERT INTO `trains_status` (`id`, `seats_b_ac`, `seats_b_sleeper`, `t_date`, `t_number`) VALUES
(1, 3, 0, '2024-06-23', 223),
(2, 0, 0, '2024-06-24', 113),
(3, 0, 0, '2024-06-24', 114),
(4, 2, 0, '2024-06-30', 225);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `address` varchar(128) NOT NULL,
  `role` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `enable_account` tinyint(1) NOT NULL DEFAULT 0,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `failed_login` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user`, `name`, `email`, `address`, `role`, `password`, `token`, `enable_account`, `last_login`, `failed_login`) VALUES
(1, 'admin', 'admin', 'admin@gmail.com', 'Dodoma', 1, '$2y$12$jivZFAyTRvfU/Ta9atQZjOcddXFfIcuDm8WJeXl2i5/6s/RHrgWBi', '', 1, '2024-06-29 11:32:53', 0),
(4, 'dele', 'Ismail Haji', 'dele@gmail.com', 'Dodoma', 2, '$2y$12$jivZFAyTRvfU/Ta9atQZjOcddXFfIcuDm8WJeXl2i5/6s/RHrgWBi', '', 1, '2024-06-29 12:44:08', 0),
(29, 'ismail', 'Ismail Haji', 'ismailalihaji01@gmail.com', 'Dodoma', 4, '$2y$12$eI70mHmZjggsfdK.fg1KHO1wJUdRf5qkn9KcIGhqw0TeQwS/ACdyG', '487247', 1, '2024-06-29 12:48:37', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `site_activity`
--
ALTER TABLE `site_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_permissions`
--
ALTER TABLE `system_permissions`
  ADD PRIMARY KEY (`permission_id`);

--
-- Indexes for table `system_permission_to_roles`
--
ALTER TABLE `system_permission_to_roles`
  ADD PRIMARY KEY (`ref_id`);

--
-- Indexes for table `system_users_to_roles`
--
ALTER TABLE `system_users_to_roles`
  ADD PRIMARY KEY (`ref_id`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trains`
--
ALTER TABLE `trains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trains_status`
--
ALTER TABLE `trains_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `site_activity`
--
ALTER TABLE `site_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `system_permissions`
--
ALTER TABLE `system_permissions`
  MODIFY `permission_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_permission_to_roles`
--
ALTER TABLE `system_permission_to_roles`
  MODIFY `ref_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_users_to_roles`
--
ALTER TABLE `system_users_to_roles`
  MODIFY `ref_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `trains`
--
ALTER TABLE `trains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `trains_status`
--
ALTER TABLE `trains_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
