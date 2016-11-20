UPDATE `offers`
SET `confirmed` = TRUE
WHERE
	(
		`o_number` = ?
	)
AND (`meal` = ?)
AND (`date` = CURRENT_DATE)
LIMIT 1
