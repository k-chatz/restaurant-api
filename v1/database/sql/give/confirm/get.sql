SELECT
	o.confirmed AS o_confirm
FROM
	offers AS o
WHERE
	o.meal = ?
AND o.o_number = ?
AND o.date = ?
