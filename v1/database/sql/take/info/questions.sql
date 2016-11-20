#QUERY OK

SELECT
	COUNT(q.q_username) AS 'questions'
FROM
	questions AS q
LEFT JOIN reservations AS r ON r.q_username = q.q_username
AND r.q_date = q.date
AND r.q_meal = q.meal
WHERE
	r.q_username IS NULL
AND r.q_date IS NULL
AND r.q_meal IS NULL
AND (
	(
		q.date = ADDDATE(
			CURRENT_DATE (),
			INTERVAL - 1 DAY
		)
		AND (
			(
			  TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0 AND
				TIMEDIFF(q.time, '09:30:00.0') >= 0
				AND q.meal = 'B'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0 AND
				TIMEDIFF(q.time, '15:30:00.0') >= 0
				AND q.meal = 'L'
			)
			OR (
				TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0 AND
				TIMEDIFF(q.time, '20:15:00.0') >= 0
				AND q.meal = 'D'
			)
		)
	)
	OR (
		q.date = CURRENT_DATE
		AND (
			(
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(q.time, '09:30:00.0') < 0
					AND q.meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
					AND TIMEDIFF(q.time, '09:30:00.0') >= 0
					AND q.meal = 'B'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(q.time, '15:30:00.0') < 0
					AND q.meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
					AND TIMEDIFF(q.time, '15:30:00.0') >= 0
					AND q.meal = 'L'
				)
			)
			OR (
				(
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(q.time, '20:15:00.0') < 0
					AND q.meal = 'D'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
					AND TIMEDIFF(q.time, '20:15:00.0') >= 0
					AND q.meal = 'D'
				)
			)
		)
	)
)
AND q.meal = ?
