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
		?,
		CURRENT_TIME
	)
