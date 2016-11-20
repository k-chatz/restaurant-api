INSERT INTO `offers` (
	`o_number`,
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