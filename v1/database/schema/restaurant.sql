/*
Navicat MySQL Data Transfer

Source Server         : Local Root
Source Server Version : 100113
Source Host           : localhost:3306
Source Database       : restaurant

Target Server Type    : MYSQL
Target Server Version : 100113
File Encoding         : 65001

Date: 2016-11-17 14:04:50
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for meals
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
-- Table structure for meal_types
-- ----------------------------
DROP TABLE IF EXISTS `meal_types`;
CREATE TABLE `meal_types` (
  `type` varchar(1) NOT NULL,
  `start` time(1) DEFAULT NULL,
  `end` time(1) DEFAULT NULL,
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for numbers
-- ----------------------------
DROP TABLE IF EXISTS `numbers`;
CREATE TABLE `numbers` (
  `number` varchar(6) NOT NULL,
  PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for offers
-- ----------------------------
DROP TABLE IF EXISTS `offers`;
CREATE TABLE `offers` (
  `o_number` varchar(6) NOT NULL,
  `meal` varchar(1) NOT NULL,
  `date` date NOT NULL,
  `time` time(1) NOT NULL,
  `confirmed` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  PRIMARY KEY (`o_number`,`date`,`meal`),
  KEY `fk_serving_users_idx` (`o_number`),
  KEY `fk_offers_meal_types` (`meal`),
  KEY `o_number` (`o_number`,`meal`,`date`,`confirmed`) USING BTREE,
  CONSTRAINT `fk_offers_meal_types` FOREIGN KEY (`meal`) REFERENCES `meal_types` (`type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offers_users` FOREIGN KEY (`o_number`) REFERENCES `users` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for questions
-- ----------------------------
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `q_username` varchar(15) NOT NULL,
  `meal` varchar(1) NOT NULL,
  `date` date NOT NULL,
  `time` time(1) NOT NULL,
  `confirmed` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  PRIMARY KEY (`q_username`,`date`,`meal`),
  KEY `fk_questions_meal_types` (`meal`),
  KEY `q_username` (`q_username`,`meal`,`date`),
  CONSTRAINT `fk_questions_meal_types` FOREIGN KEY (`meal`) REFERENCES `meal_types` (`type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_users` FOREIGN KEY (`q_username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for reservations
-- ----------------------------
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `q_username` varchar(15) NOT NULL,
  `q_meal` varchar(1) NOT NULL,
  `q_date` date NOT NULL,
  `q_time` time(1) NOT NULL,
  `o_number` varchar(6) NOT NULL,
  `o_meal` varchar(1) NOT NULL,
  `o_date` date NOT NULL,
  `o_time` time(1) NOT NULL,
  `o_confirmed` tinyint(1) unsigned zerofill NOT NULL,
  `r_date` date NOT NULL,
  `r_time` time(1) NOT NULL,
  PRIMARY KEY (`q_username`,`q_meal`,`q_date`,`o_number`,`o_meal`,`o_date`),
  KEY `fk_R_offers1_idx` (`o_number`,`o_date`,`o_meal`,`o_confirmed`),
  KEY `fk_reservations_offers` (`o_number`,`o_meal`,`o_date`,`o_confirmed`),
  CONSTRAINT `fk_reservations_offers` FOREIGN KEY (`o_number`, `o_meal`, `o_date`, `o_confirmed`) REFERENCES `offers` (`o_number`, `meal`, `date`, `confirmed`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservations_questions` FOREIGN KEY (`q_username`, `q_meal`, `q_date`) REFERENCES `questions` (`q_username`, `meal`, `date`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `username` varchar(6) NOT NULL,
  `notifications` tinyint(1) NOT NULL DEFAULT '0',
  KEY `fk_settings_users` (`username`),
  CONSTRAINT `fk_settings_users` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `username` varchar(15) NOT NULL,
  `password` varchar(45) NOT NULL,
  `number` varchar(6) DEFAULT NULL,
  `role` varchar(1) NOT NULL,
  `name` varchar(15) NOT NULL,
  `surname` varchar(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `mac` varchar(17) NOT NULL,
  `priority_for_b` int(11) unsigned zerofill NOT NULL DEFAULT '00000000000',
  `priority_for_l` int(11) unsigned zerofill NOT NULL DEFAULT '00000000000',
  `priority_for_d` int(11) unsigned zerofill NOT NULL DEFAULT '00000000000',
  PRIMARY KEY (`username`),
  UNIQUE KEY `number_UNIQUE` (`number`) USING BTREE,
  KEY `fk_users_user_roles` (`role`),
  CONSTRAINT `fk_users_rooms` FOREIGN KEY (`number`) REFERENCES `numbers` (`number`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_user_roles` FOREIGN KEY (`role`) REFERENCES `user_roles` (`role`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user_roles
-- ----------------------------
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `role` varchar(1) NOT NULL,
  PRIMARY KEY (`role`),
  UNIQUE KEY `role_UNIQUE` (`role`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `reservation1`;
DELIMITER ;;
CREATE TRIGGER `reservation1` AFTER INSERT ON `offers` FOR EACH ROW BEGIN
DECLARE done INT DEFAULT FALSE;

DECLARE Username VARCHAR (15);
DECLARE Date date;
DECLARE Time time;
DECLARE Meal VARCHAR (1);

DECLARE c_first_available_question CURSOR FOR SELECT
	q.q_username,
	q.date,
	q.time,
	q.meal
FROM
	questions AS q
LEFT JOIN reservations AS r ON r.q_username = q.q_username
AND r.q_date = q.date
AND r.q_meal = q.meal
WHERE
	(
		(
			r.q_username IS NULL
			AND r.q_date IS NULL
			AND r.q_meal IS NULL
		)
		OR (
			r.q_username = q.q_username
			AND r.q_date = q.date
			AND r.q_meal = q.meal
			AND r.o_confirmed = 0
		)
	)
AND (
	(
		q.date = ADDDATE(
			CURRENT_DATE (),
			INTERVAL - 1 DAY
		)
		AND (
			(
				TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
				AND TIMEDIFF(q.time, '09:30:00.0') >= 0
				AND q.meal = 'B'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
				AND TIMEDIFF(q.time, '15:30:00.0') >= 0
				AND q.meal = 'L'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
				AND TIMEDIFF(q.time, '20:15:00.0') >= 0
				AND q.meal = 'D'
			)
		)
	)
	OR (
		q.date = CURRENT_DATE
		AND (
			(
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(q.time, '09:30:00.0') < 0
					AND q.meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
					AND TIMEDIFF(q.time, '09:30:00.0') >= 0
					AND q.meal = 'B'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(q.time, '15:30:00.0') < 0
					AND q.meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
					AND TIMEDIFF(q.time, '15:30:00.0') >= 0
					AND q.meal = 'L'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(q.time, '20:15:00.0') < 0
					AND q.meal = 'D'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
					AND TIMEDIFF(q.time, '20:15:00.0') >= 0
					AND q.meal = 'D'
				)
			)
		)
	)
)
AND q.meal = NEW.meal
ORDER BY
	q.date,
	q.time ASC
LIMIT 1;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN c_first_available_question;

FETCH c_first_available_question INTO Username, Date, Time, Meal;

IF done = FALSE AND NEW.confirmed = TRUE THEN
	INSERT INTO `reservations`
VALUES(
		Username,
        		NEW.meal,
		Date,
		Time,
		NEW.o_number,
        		NEW.meal,
		NEW.date,
		NEW.time,
		NEW.confirmed,
		NEW.date,
		NEW.time
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
DECLARE Time time;
DECLARE Meal VARCHAR (1);

DECLARE c_first_available_question CURSOR FOR SELECT
	q.q_username,
	q.date,
	q.time,
	q.meal
FROM
	questions AS q
LEFT JOIN reservations AS r ON r.q_username = q.q_username
AND r.q_date = q.date
AND r.q_meal = q.meal
WHERE
	(
		(
			r.q_username IS NULL
			AND r.q_date IS NULL
			AND r.q_meal IS NULL
		)
		OR (
			r.q_username = q.q_username
			AND r.q_date = q.date
			AND r.q_meal = q.meal
			AND r.o_confirmed = 0
		)
	)
AND (
	(
		q.date = ADDDATE(
			CURRENT_DATE (),
			INTERVAL - 1 DAY
		)
		AND (
			(
				TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
				AND TIMEDIFF(q.time, '09:30:00.0') >= 0
				AND q.meal = 'B'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
				AND TIMEDIFF(q.time, '15:30:00.0') >= 0
				AND q.meal = 'L'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
				AND TIMEDIFF(q.time, '20:15:00.0') >= 0
				AND q.meal = 'D'
			)
		)
	)
	OR (
		q.date = CURRENT_DATE
		AND (
			(
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(q.time, '09:30:00.0') < 0
					AND q.meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
					AND TIMEDIFF(q.time, '09:30:00.0') >= 0
					AND q.meal = 'B'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(q.time, '15:30:00.0') < 0
					AND q.meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
					AND TIMEDIFF(q.time, '15:30:00.0') >= 0
					AND q.meal = 'L'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(q.time, '20:15:00.0') < 0
					AND q.meal = 'D'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
					AND TIMEDIFF(q.time, '20:15:00.0') >= 0
					AND q.meal = 'D'
				)
			)
		)
	)
)
AND q.meal = NEW.meal
ORDER BY
	q.date,
	q.time ASC
LIMIT 1;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN c_first_available_question;

FETCH c_first_available_question INTO Username, Date, Time, Meal;

IF done = FALSE AND NEW.confirmed = TRUE THEN
	INSERT INTO `reservations`
VALUES(
		Username,
        		NEW.meal,
		Date,
		Time,
		NEW.o_number,
        		NEW.meal,
		NEW.date,
		NEW.time,
		NEW.confirmed,
		NEW.date,
		NEW.time
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
DECLARE Time time;
DECLARE Confirmed tinyint(1);

DECLARE Q_STEP_1 CURSOR FOR SELECT
	o.o_number,
	o.date,
	o.time,
	o.confirmed
FROM
	offers AS o
LEFT JOIN reservations AS r ON r.o_number = o.o_number
AND r.o_date = o.date
AND r.o_meal = o.meal
WHERE
	(
		r.o_number IS NULL
		AND r.o_date IS NULL
		AND r.o_meal IS NULL
	)
AND (
	(
		o.date = ADDDATE(
			CURRENT_DATE (),
			INTERVAL - 1 DAY
		)
		AND (
			(
				TIMEDIFF(o.time, '09:30:00.0') >= 0
				AND o.meal = 'B'
			)
			OR (
				TIMEDIFF(o.time, '15:30:00.0') >= 0
				AND o.meal = 'L'
			)
			OR (
				TIMEDIFF(o.time, '20:15:00.0') >= 0
				AND o.meal = 'D'
			)
		)
	)
	OR (
		o.date = CURRENT_DATE
		AND (
			(
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(o.time, '09:30:00.0') < 0
					AND o.meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
					AND TIMEDIFF(o.time, '09:30:00.0') >= 0
					AND o.meal = 'B'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(o.time, '15:30:00.0') < 0
					AND o.meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
					AND TIMEDIFF(o.time, '15:30:00.0') >= 0
					AND o.meal = 'L'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(o.time, '20:15:00.0') < 0
					AND o.meal = 'D'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
					AND TIMEDIFF(o.time, '20:15:00.0') >= 0
					AND o.meal = 'D'
				)
			)
		)
	)
)
AND o.meal = NEW.meal
AND o.confirmed = 1
ORDER BY
	o.date,
	o.time ASC
LIMIT 1;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN Q_STEP_1;

FETCH Q_STEP_1 INTO Number, Date, Time, Confirmed;

IF done = FALSE THEN

INSERT INTO `reservations`  VALUES(
		NEW.q_username,
        		NEW.meal,
		NEW.date,
		NEW.time,
		Number,
        		NEW.meal,
		Date,
		Time,
		Confirmed,
		NEW.date,
		NEW.time
	);

END IF;

CLOSE Q_STEP_1;

END
;;
DELIMITER ;
SET FOREIGN_KEY_CHECKS=1;
