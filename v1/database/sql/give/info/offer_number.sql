#QUERY OK

SELECT
	r.o_number AS 'o_number',
	q.confirmed AS 'q_confirm'
FROM
reservations AS r
INNER JOIN questions AS q ON r.q_username = q.q_username AND r.q_meal = q.meal AND r.q_date = q.date
INNER JOIN offers AS o ON r.o_number = o.o_number AND r.o_meal = o.meal AND r.o_date = o.date
WHERE
o.confirmed = TRUE AND
r.o_meal = ?
AND r.q_username = ? AND
r.o_date = (
	CASE
	WHEN r.q_meal = 'B' THEN
	IF (TIMEDIFF('09:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN r.q_meal = 'L' THEN
	IF (TIMEDIFF('15:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN r.q_meal = 'D' THEN
		IF (TIMEDIFF('20:15:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	END
)
LIMIT 1
