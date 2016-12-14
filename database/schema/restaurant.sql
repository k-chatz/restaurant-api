/*
Navicat MySQL Data Transfer

Source Server         : Local Root
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : restaurant

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-12-14 19:14:32
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
-- Records of meals
-- ----------------------------

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
  `q_username` varchar(15) NOT NULL,
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
  `q_username` varchar(15) NOT NULL,
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
  `username` varchar(6) NOT NULL,
  `notifications` tinyint(1) NOT NULL DEFAULT '0',
  KEY `fk_settings_users` (`username`),
  CONSTRAINT `fk_settings_users` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of settings
-- ----------------------------

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `username` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `number` varchar(6) DEFAULT NULL,
  `role` varchar(1) NOT NULL,
  `picture` varchar(150) DEFAULT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `lastconnect` datetime DEFAULT NULL,
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
DROP TRIGGER IF EXISTS `reservation1`;
DELIMITER ;;
CREATE TRIGGER `reservation1` AFTER INSERT ON `offers` FOR EACH ROW BEGIN
DECLARE done INT DEFAULT FALSE;
DECLARE Username VARCHAR (15);
DECLARE Date date;
DECLARE Meal VARCHAR (1);
DECLARE Moment DATETIME DEFAULT NOW();

DECLARE c_first_available_question CURSOR FOR SELECT
	q.q_username,
	q.date,
	q.meal,
	q.moment
FROM
	questions AS q
LEFT JOIN reservations AS r ON r.q_username = q.q_username
AND r.q_date = q.date
AND r.q_meal = q.meal

WHERE
r.q_username IS NULL
AND r.q_date IS NULL
AND r.q_meal IS NULL

AND
q.date = (
	CASE
	WHEN q.meal = 'B' THEN
	IF (TIMEDIFF('09:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN q.meal = 'L' THEN
	IF (TIMEDIFF('15:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN q.meal = 'D' THEN
		IF (TIMEDIFF('20:15:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	END
)

AND q.meal = NEW.meal
ORDER BY q.moment ASC
LIMIT 1;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN c_first_available_question;

FETCH c_first_available_question INTO Username, Date, Meal, Moment;

IF done = FALSE AND NEW.confirmed = TRUE THEN
	INSERT INTO `reservations`
VALUES(
		Username,
        		Meal,
		Date,
		NEW.o_number,
        		Meal,
		Date,
		NOW()
	);
END IF;

CLOSE c_first_available_question;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `reservation2`;
DELIMITER ;;
CREATE TRIGGER `reservation2` AFTER UPDATE ON `offers` FOR EACH ROW BEGIN
DECLARE done INT DEFAULT FALSE;
DECLARE Username VARCHAR (15);
DECLARE Date date;
DECLARE Meal VARCHAR (1);
DECLARE Moment DATETIME DEFAULT NOW();

DECLARE c_first_available_question CURSOR FOR SELECT
	q.q_username,
	q.date,
	q.meal,
	q.moment
FROM
	questions AS q
LEFT JOIN reservations AS r ON r.q_username = q.q_username
AND r.q_date = q.date
AND r.q_meal = q.meal

WHERE
r.q_username IS NULL
AND r.q_date IS NULL
AND r.q_meal IS NULL

AND
q.date = (
	CASE
	WHEN q.meal = 'B' THEN
	IF (TIMEDIFF('09:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN q.meal = 'L' THEN
	IF (TIMEDIFF('15:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN q.meal = 'D' THEN
		IF (TIMEDIFF('20:15:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	END
)

AND q.meal = NEW.meal
ORDER BY q.moment ASC
LIMIT 1;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN c_first_available_question;

FETCH c_first_available_question INTO Username, Date, Meal, Moment;

IF done = FALSE AND NEW.confirmed = TRUE THEN
	INSERT INTO `reservations`
VALUES(
		Username,
        		Meal,
		Date,
		NEW.o_number,
        		Meal,
		Date,
		NOW()
	);
END IF;

CLOSE c_first_available_question;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `reservations`;
DELIMITER ;;
CREATE TRIGGER `reservations` AFTER INSERT ON `questions` FOR EACH ROW BEGIN
DECLARE done INT DEFAULT FALSE;

DECLARE Number VARCHAR (6);
DECLARE Date date;
DECLARE Confirmed tinyint(1);
DECLARE Moment DATETIME DEFAULT NOW();

DECLARE Q_STEP_1 CURSOR FOR SELECT
	o.o_number,
	o.date,
	o.confirmed,
	o.moment
FROM
	offers AS o
LEFT JOIN reservations AS r ON r.o_number = o.o_number
AND r.o_date = o.date
AND r.o_meal = o.meal
LEFT JOIN users AS u ON u.number = o.o_number
WHERE
	(
		r.o_number IS NULL
		AND r.o_date IS NULL
		AND r.o_meal IS NULL
	)
AND u.username != NEW.q_username
AND (
	CASE
	WHEN o.meal = 'B' THEN

	IF (
		TIMEDIFF('09:30:00.0', TIME(NOW())) < 0,
		o.date = ADDDATE(CURRENT_DATE, INTERVAL 1 DAY),
		o.date = CURRENT_DATE
	)
	WHEN o.meal = 'L' THEN

	IF (
		TIMEDIFF('15:30:00.0', TIME(NOW())) < 0,
		o.date = ADDDATE(CURRENT_DATE, INTERVAL 1 DAY),
		o.date = CURRENT_DATE
	)
	WHEN o.meal = 'D' THEN

	IF (
		TIMEDIFF('20:15:00.0', TIME(NOW())) < 0,
		o.date = ADDDATE(CURRENT_DATE, INTERVAL 1 DAY),
		o.date = CURRENT_DATE
	)
	END
)
AND o.meal = NEW.meal
AND o.confirmed = 1
ORDER BY
	o.moment ASC
LIMIT 1;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN Q_STEP_1;

FETCH Q_STEP_1 INTO Number, Date, Confirmed, Moment;

IF done = FALSE THEN

INSERT INTO `reservations`  VALUES(
		NEW.q_username,
        		NEW.meal,
		NEW.date,
		Number,
        		NEW.meal,
		Date,
		NOW()
	);

END IF;

CLOSE Q_STEP_1;

END
;;
DELIMITER ;
