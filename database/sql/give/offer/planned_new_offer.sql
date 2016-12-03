#QUERY OK

INSERT INTO `offers` (
	`o_number`,
	`meal`,
	`date`,
	`confirmed`,
	`moment`
)
VALUES
	(
		?,
		?,
		?,
		0,
		NOW()
	)
