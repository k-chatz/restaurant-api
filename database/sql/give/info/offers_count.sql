#QUERY OK

SELECT
	COUNT(o.o_number) AS 'offers'
FROM
	offers AS o
LEFT JOIN reservations AS r ON r.o_number = o.o_number
AND r.o_date = o.date
AND r.o_meal = o.meal
WHERE
	r.o_number IS NULL
AND r.o_date IS NULL
AND r.o_meal IS NULL
AND o.meal = ?
AND o.o_number != ?
AND o.date = (
	CASE
	WHEN `meal` = 'B' THEN
	IF (TIMEDIFF('09:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN `meal` = 'L' THEN
	IF (TIMEDIFF('15:30:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	WHEN `meal` = 'D' THEN
		IF (TIMEDIFF('20:15:00.0', CURRENT_TIME) <= 0, ADDDATE(CURRENT_DATE, INTERVAL 1 DAY), CURRENT_DATE)
	END
)
