SELECT
	COUNT(o.o_number) AS 'o_tomorrow'
FROM
	offers AS o
WHERE
	(
		o.date = CURRENT_DATE
		AND (
			(
				TIMEDIFF(o.time, '09:30:00.0') >= 0
				AND o.meal = 'B'
			)
			OR (
				TIMEDIFF(o.time, '15:30:00.0') >= 0
				AND o.meal = 'L'
			)
			OR (
				TIMEDIFF(o.time, '20:15:00.0') >= 0
				AND o.meal = 'D'
			)
		)
	)
AND o.meal = ?
AND o.o_number = ?
