SELECT
	users.`name`,
	users.surname,
	CASE q.date
WHEN CURRENT_DATE THEN
	'Today'
WHEN ADDDATE(
	CURRENT_DATE (),
	INTERVAL - 1 DAY
) THEN
	'Yesterday'
ELSE
	'Other day'
END AS 'day',
 q.time
FROM
	questions AS q
LEFT JOIN reservations AS r ON r.q_username = q.q_username
AND r.q_date = q.date
AND r.q_meal = q.meal
INNER JOIN users ON q.q_username = users.username
WHERE
	(
		(
			q.date = ADDDATE(
				CURRENT_DATE (),
				INTERVAL - 1 DAY
			)
			AND (
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(q.time, '09:30:00.0') >= 0
					AND q.meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(q.time, '15:30:00.0') >= 0
					AND q.meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(q.time, '20:15:00.0') >= 0
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
AND (
	r.o_confirmed = 0
	OR r.o_confirmed IS NULL
)
AND q.meal = ?
GROUP BY
	q.q_username
ORDER BY
	q.date ASC,
	q.time ASC