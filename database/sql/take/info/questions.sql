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
AND q.meal = ?
AND q.q_username != ?
AND q.date = (
	CASE
	WHEN `meal` = 'B' THEN
	IF (TIMEDIFF('09:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN `meal` = 'L' THEN
	IF (TIMEDIFF('15:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN `meal` = 'D' THEN
		IF (TIMEDIFF('20:15:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	END
)
