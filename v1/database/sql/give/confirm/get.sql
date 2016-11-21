SELECT
	o.confirmed AS o_confirm
FROM
	offers AS o
WHERE
	o.meal = ?
AND o.o_number = ?
AND (
	(
		o.date = ADDDATE(CURRENT_DATE, INTERVAL - 1 DAY)
		AND (
			(
				TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
				AND TIMEDIFF(o.time, '09:30:00.0') >= 0
				AND o.meal = 'B'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
				AND TIMEDIFF(o.time, '15:30:00.0') >= 0
				AND o.meal = 'L'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
				AND TIMEDIFF(o.time, '20:15:00.0') >= 0
				AND o.meal = 'D'
			)
		)
	)
	OR (
		o.date = CURRENT_DATE
		AND (
			(
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(o.time, '09:30:00.0') < 0
					AND o.meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
					AND TIMEDIFF(o.time, '09:30:00.0') >= 0
					AND o.meal = 'B'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(o.time, '15:30:00.0') < 0
					AND o.meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
					AND TIMEDIFF(o.time, '15:30:00.0') >= 0
					AND o.meal = 'L'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(o.time, '20:15:00.0') < 0
					AND o.meal = 'D'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
					AND TIMEDIFF(o.time, '20:15:00.0') >= 0
					AND o.meal = 'D'
				)
			)
		)
	)
)
