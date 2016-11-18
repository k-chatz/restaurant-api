#QUERY OK
SELECT
	r.o_number AS 'o_number',
	q.confirmed AS 'q_confirm'
FROM
	reservations AS r,
	questions AS q
WHERE
	q.q_username = r.q_username
AND q.date = r.q_date
AND q.meal = r.q_meal
AND (
	(
		r.r_date = ADDDATE(CURRENT_DATE, INTERVAL - 1 DAY)
		AND (
			(
				TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
				AND TIMEDIFF(r.r_time, '09:30:00.0') >= 0
				AND r.o_meal = 'B'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
				AND TIMEDIFF(r.r_time, '15:30:00.0') >= 0
				AND r.o_meal = 'L'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
				AND TIMEDIFF(r.r_time, '20:15:00.0') >= 0
				AND r.o_meal = 'D'
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
					AND r.o_meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
					AND TIMEDIFF(r.r_time, '09:30:00.0') >= 0
					AND r.o_meal = 'B'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(r.r_time, '15:30:00.0') < 0
					AND r.o_meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
					AND TIMEDIFF(r.r_time, '15:30:00.0') >= 0
					AND r.o_meal = 'L'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(r.r_time, '20:15:00.0') < 0
					AND r.o_meal = 'D'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
					AND TIMEDIFF(r.r_time, '20:15:00.0') >= 0
					AND r.o_meal = 'D'
				)
			)
		)
	)
)
AND r.o_confirmed = TRUE
AND r.o_meal = ?
AND r.q_username = ?
ORDER BY
	r.r_date,
	r.r_time ASC
LIMIT 1
