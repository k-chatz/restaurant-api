SELECT
	COUNT(o.o_number) AS 'o_offer'
FROM
	offers AS o
WHERE
		o.date = ?
AND o.meal = ?
AND o.o_number = ?
