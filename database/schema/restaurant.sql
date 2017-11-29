/*
Navicat MySQL Data Transfer

Source Server         : Local Root
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : restaurant

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-02-05 17:58:42
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `meals`
-- ----------------------------
DROP TABLE IF EXISTS `meals`;
CREATE TABLE `meals` (
  `meal` varchar(255) NOT NULL,
  `type` varchar(1) NOT NULL,
  `date` date NOT NULL,
  KEY `fk_meals_meal_types` (`type`),
  CONSTRAINT `fk_meals_meal_types` FOREIGN KEY (`type`) REFERENCES `meal_types` (`type`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `meal_types`
-- ----------------------------
DROP TABLE IF EXISTS `meal_types`;
CREATE TABLE `meal_types` (
  `type` varchar(1) NOT NULL,
  `start` time(1) DEFAULT NULL,
  `end` time(1) DEFAULT NULL,
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of meal_types
-- ----------------------------
INSERT INTO `meal_types` VALUES ('B', '08:00:00.0', '09:30:00.0');
INSERT INTO `meal_types` VALUES ('L', '13:00:00.0', '15:30:00.0');
INSERT INTO `meal_types` VALUES ('D', '18:30:00.0', '20:15:00.0');

-- ----------------------------
-- Table structure for `numbers`
-- ----------------------------
DROP TABLE IF EXISTS `numbers`;
CREATE TABLE `numbers` (
  `number` varchar(6) NOT NULL,
  PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of numbers
-- ----------------------------
INSERT INTO `numbers` VALUES ('AN-111');
INSERT INTO `numbers` VALUES ('Α1010');
INSERT INTO `numbers` VALUES ('Α1040');
INSERT INTO `numbers` VALUES ('Α1324');
INSERT INTO `numbers` VALUES ('Α2056');
INSERT INTO `numbers` VALUES ('Α3104');
INSERT INTO `numbers` VALUES ('Α3213');
INSERT INTO `numbers` VALUES ('Α3249');
INSERT INTO `numbers` VALUES ('Α3255');
INSERT INTO `numbers` VALUES ('Α3302');
INSERT INTO `numbers` VALUES ('Α3402');
INSERT INTO `numbers` VALUES ('Α4020');
INSERT INTO `numbers` VALUES ('Α4353');
INSERT INTO `numbers` VALUES ('Α5020');
INSERT INTO `numbers` VALUES ('Α6553');
INSERT INTO `numbers` VALUES ('Β234');
INSERT INTO `numbers` VALUES ('Β314');
INSERT INTO `numbers` VALUES ('Β433');
INSERT INTO `numbers` VALUES ('Γ1023');
INSERT INTO `numbers` VALUES ('Γ1034');
INSERT INTO `numbers` VALUES ('Γ1323');
INSERT INTO `numbers` VALUES ('Γ2312');
INSERT INTO `numbers` VALUES ('Γ4321');
INSERT INTO `numbers` VALUES ('Δ1234');
INSERT INTO `numbers` VALUES ('Δ2345');
INSERT INTO `numbers` VALUES ('Δ4005');
INSERT INTO `numbers` VALUES ('Δ4006');

-- ----------------------------
-- Table structure for `offers`
-- ----------------------------
DROP TABLE IF EXISTS `offers`;
CREATE TABLE `offers` (
  `o_number` varchar(6) NOT NULL,
  `meal` varchar(1) NOT NULL,
  `date` date NOT NULL,
  `confirmed` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  `moment` datetime(1) NOT NULL DEFAULT '0000-00-00 00:00:00.0',
  PRIMARY KEY (`o_number`,`date`,`meal`),
  KEY `fk_serving_users_idx` (`o_number`),
  KEY `fk_offers_meal_types` (`meal`),
  KEY `o_number_2` (`o_number`,`meal`,`date`),
  CONSTRAINT `fk_offers_meal_types` FOREIGN KEY (`meal`) REFERENCES `meal_types` (`type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offers_users` FOREIGN KEY (`o_number`) REFERENCES `users` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `questions`
-- ----------------------------
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `q_username` varchar(20) NOT NULL,
  `meal` varchar(1) NOT NULL,
  `date` date NOT NULL,
  `confirmed` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  `moment` datetime(1) NOT NULL DEFAULT '0000-00-00 00:00:00.0',
  PRIMARY KEY (`q_username`,`date`,`meal`),
  KEY `fk_questions_meal_types` (`meal`),
  KEY `q_username` (`q_username`,`meal`,`date`),
  CONSTRAINT `fk_questions_meal_types` FOREIGN KEY (`meal`) REFERENCES `meal_types` (`type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_users` FOREIGN KEY (`q_username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `reservations`
-- ----------------------------
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `q_username` varchar(20) NOT NULL,
  `q_meal` varchar(1) NOT NULL,
  `q_date` date NOT NULL,
  `o_number` varchar(6) NOT NULL,
  `o_meal` varchar(1) NOT NULL,
  `o_date` date NOT NULL,
  `moment` datetime(1) NOT NULL,
  PRIMARY KEY (`q_username`,`q_meal`,`q_date`,`o_number`,`o_meal`,`o_date`),
  UNIQUE KEY `fk_R_offers1_idx` (`o_number`,`o_date`,`o_meal`) USING BTREE,
  UNIQUE KEY `fk_reservations_offers` (`o_number`,`o_meal`,`o_date`) USING BTREE,
  CONSTRAINT `fk_reservations_offers` FOREIGN KEY (`o_number`, `o_meal`, `o_date`) REFERENCES `offers` (`o_number`, `meal`, `date`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservations_questions` FOREIGN KEY (`q_username`, `q_meal`, `q_date`) REFERENCES `questions` (`q_username`, `meal`, `date`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `settings`
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `username` varchar(20) NOT NULL,
  `notifications` tinyint(1) NOT NULL DEFAULT '0',
  KEY `fk_settings_users` (`username`),
  CONSTRAINT `fk_settings_users` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `username` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `number` varchar(6) DEFAULT NULL,
  `role` varchar(1) NOT NULL,
  `picture` varchar(300) DEFAULT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `lastconnect` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `fbLongAccessToken` longtext NOT NULL,
  `accessToken` longtext NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `number_UNIQUE` (`number`) USING BTREE,
  KEY `fk_users_user_roles` (`role`),
  CONSTRAINT `fk_users_rooms` FOREIGN KEY (`number`) REFERENCES `numbers` (`number`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_user_roles` FOREIGN KEY (`role`) REFERENCES `user_roles` (`role`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_roles`
-- ----------------------------
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `role` varchar(1) NOT NULL,
  PRIMARY KEY (`role`),
  UNIQUE KEY `role_UNIQUE` (`role`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_roles
-- ----------------------------
INSERT INTO `user_roles` VALUES ('A');
INSERT INTO `user_roles` VALUES ('B');
INSERT INTO `user_roles` VALUES ('V');
