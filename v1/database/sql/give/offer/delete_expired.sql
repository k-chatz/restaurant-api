#QUERY OK

DELETE
FROM
	offers
WHERE
date < CURRENT_DATE
AND o_number = ?
AND meal = ?
