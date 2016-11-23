SELECT
	o.date,
	sum(
		CASE
		WHEN o.meal = 'B' THEN
			o.confirmed
		ELSE
			NULL
		END
	) b,
	sum(
		CASE
		WHEN o.meal = 'L' THEN
			o.confirmed
		ELSE
			NULL
		END
	) l,
	sum(
		CASE
		WHEN o.meal = 'D' THEN
			o.confirmed
		ELSE
			NULL
		END
	) d
FROM
	offers AS o
WHERE
	o.o_number = ?
AND (
	date = ADDDATE(CURRENT_DATE, INTERVAL - 1 DAY)
	AND (
		(
			TIMEDIFF(time, '09:30:00.0') >= 0
			AND meal = 'B'
		)
		OR (
			TIMEDIFF(time, '15:30:00.0') >= 0
			AND meal = 'L'
		)
		OR (
			TIMEDIFF(time, '20:15:00.0') >= 0
			AND meal = 'D'
		)
	)
)
OR date >= CURRENT_DATE
GROUP BY
	o.date
