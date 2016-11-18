INSERT INTO `questions` (
	`q_username`,
	`meal`,
	`date`,
	`time`
)
VALUES
	(
		?,
		?,
		CURRENT_DATE,
		CURRENT_TIME
	)