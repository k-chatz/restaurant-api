SELECT
	COUNT(q.q_username) AS 'q_today'
FROM
	questions AS q
WHERE
	(
		(
			q.date = ADDDATE(CURRENT_DATE, INTERVAL - 1 DAY)
			AND (
				(
					TIMEDIFF(q.time, '09:30:00.0') >= 0
					AND q.meal = 'B'
				)
				OR (
					TIMEDIFF(q.time, '15:30:00.0') >= 0
					AND q.meal = 'L'
				)
				OR (
					TIMEDIFF(q.time, '20:15:00.0') >= 0
					AND q.meal = 'D'
				)
			)
		)
		OR (
			q.date = CURRENT_DATE
			AND (
				(
					TIMEDIFF(q.time, '09:30:00.0') < 0
					AND q.meal = 'B'
				)
				OR (
					TIMEDIFF(q.time, '15:30:00.0') < 0
					AND q.meal = 'L'
				)
				OR (
					TIMEDIFF(q.time, '20:15:00.0') < 0
					AND q.meal = 'D'
				)
			)
		)
	)
AND q.meal = ?
AND q.q_username = ?
