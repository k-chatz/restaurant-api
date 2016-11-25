#QUERY OK

SELECT
r.q_username AS 'q_username',
o.confirmed AS 'o_confirm'
FROM
reservations AS r
INNER JOIN offers AS o ON r.o_number = o.o_number AND r.o_meal = o.meal AND r.o_date = o.date
WHERE
r.o_meal = ? AND
r.o_number = ? AND
r.q_date = (
	CASE
	WHEN `meal` = 'B' THEN
	IF (TIMEDIFF('09:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN `meal` = 'L' THEN
	IF (TIMEDIFF('15:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN `meal` = 'D' THEN
		IF (TIMEDIFF('20:15:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	END
)
LIMIT 1
