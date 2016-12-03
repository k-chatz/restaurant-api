#QUERY OK

UPDATE `offers`
SET `confirmed` = TRUE
WHERE
	o_number = ?
AND meal = ?
AND date = ?
LIMIT 1
