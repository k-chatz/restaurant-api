#QUERY OK

SELECT
	COUNT(o.o_number) AS 'offers'
FROM
	offers AS o
LEFT JOIN reservations AS r ON r.o_number = o.o_number
AND r.o_date = o.date
AND r.o_meal = o.meal
AND r.o_confirmed = o.confirmed
WHERE
	r.o_number IS NULL
AND r.o_date IS NULL
AND r.o_meal IS NULL
AND r.o_confirmed IS NULL
AND o.meal = ?
AND o.o_number != ?
AND o.date = ?
