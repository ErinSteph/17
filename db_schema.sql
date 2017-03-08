SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `17` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `17`;

CREATE TABLE IF NOT EXISTS `balance` (
  `id` varchar(100) DEFAULT NULL,
  `bal` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `bets` (
  `guild` varchar(100) DEFAULT NULL,
  `channel` varchar(100) DEFAULT NULL,
  `user` varchar(100) DEFAULT NULL,
  `fixture` varchar(100) DEFAULT NULL,
  `team` varchar(100) DEFAULT NULL,
  `odds` varchar(100) DEFAULT NULL,
  `amount` varchar(100) DEFAULT NULL,
`no` int(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `logs` (
  `guild_id` varchar(100) DEFAULT NULL,
  `channel_id` varchar(100) DEFAULT NULL,
  `data` mediumtext,
  `last` varchar(100) DEFAULT NULL,
  `heartbeat` varchar(100) DEFAULT NULL,
  `running` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `balance`
 ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `bets`
 ADD PRIMARY KEY (`no`), ADD UNIQUE KEY `no` (`no`);


ALTER TABLE `bets`
MODIFY `no` int(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;