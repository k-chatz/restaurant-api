#QUERY OK
SELECT
	r.q_username AS 'q_username',
	o.confirmed AS 'o_confirm'
FROM
	reservations AS r,
	offers AS o
WHERE
	o.o_number = r.o_number
AND o.date = r.o_date
AND o.meal = r.o_meal
AND (
	(
		r.r_date = ADDDATE(CURRENT_DATE, INTERVAL - 1 DAY)
		AND (
			(
				TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
				AND TIMEDIFF(r.r_time, '09:30:00.0') >= 0
				AND r.q_meal = 'B'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
				AND TIMEDIFF(r.r_time, '15:30:00.0') >= 0
				AND r.q_meal = 'L'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
				AND TIMEDIFF(r.r_time, '20:15:00.0') >= 0
				AND r.q_meal = 'D'
			)
		)
	)
	OR (
		r.r_date = CURRENT_DATE
		AND (
			(
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(r.r_time, '09:30:00.0') < 0
					AND r.q_meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
					AND TIMEDIFF(r.r_time, '09:30:00.0') >= 0
					AND r.q_meal = 'B'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(r.r_time, '15:30:00.0') < 0
					AND r.q_meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
					AND TIMEDIFF(r.r_time, '15:30:00.0') >= 0
					AND r.q_meal = 'L'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(r.r_time, '20:15:00.0') < 0
					AND r.q_meal = 'D'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
					AND TIMEDIFF(r.r_time, '20:15:00.0') >= 0
					AND r.q_meal = 'D'
				)
			)
		)
	)
)
AND r.q_meal = ?
AND r.o_number = ?
ORDER BY
	r.r_date,
	r.r_time ASC
LIMIT 1
