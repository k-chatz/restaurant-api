UPDATE `questions`
SET `confirmed` = TRUE
WHERE
	(
		`q_username` = ?
	)
AND (`meal` = ?)
AND (`date` = CURRENT_DATE)
LIMIT 1
