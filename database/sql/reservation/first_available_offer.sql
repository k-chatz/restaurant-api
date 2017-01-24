SELECT
	o.o_number,
	o.date,
	o.confirmed,
	o.moment
FROM
	offers AS o
LEFT JOIN reservations AS r ON r.o_number = o.o_number
AND r.o_date = o.date
AND r.o_meal = o.meal
LEFT JOIN users AS u ON u.number = o.o_number
WHERE
	(
		r.o_number IS NULL
		AND r.o_date IS NULL
		AND r.o_meal IS NULL
	)
AND u.username != ?
AND (
	CASE
	WHEN o.meal = 'B' THEN

	IF (
		TIMEDIFF('09:30:00.0', TIME(NOW())) < 0,
		o.date = ADDDATE(CURRENT_DATE, INTERVAL 1 DAY),
		o.date = CURRENT_DATE
	)
	WHEN o.meal = 'L' THEN

	IF (
		TIMEDIFF('15:30:00.0', TIME(NOW())) < 0,
		o.date = ADDDATE(CURRENT_DATE, INTERVAL 1 DAY),
		o.date = CURRENT_DATE
	)
	WHEN o.meal = 'D' THEN

	IF (
		TIMEDIFF('20:15:00.0', TIME(NOW())) < 0,
		o.date = ADDDATE(CURRENT_DATE, INTERVAL 1 DAY),
		o.date = CURRENT_DATE
	)
	END
)
AND o.meal = ?
AND o.confirmed = 1
ORDER BY
	o.moment ASC
LIMIT 1;
