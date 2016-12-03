#QUERY OK

SELECT
	o.date,
	sum(
		CASE
		WHEN o.meal = 'B' THEN

		IF (
			TIMEDIFF('09:30:00.0', TIME(NOW())) <= 0 && o.date = CURRENT_DATE,
			- 1,
			o.confirmed
		)
		ELSE

		IF (
			TIMEDIFF('09:30:00.0', TIME(NOW())) <= 0 && o.date = CURRENT_DATE,
			- 1,
			NULL
		)
		END
	) b,
	sum(
		CASE
		WHEN o.meal = 'L' THEN

		IF (
			TIMEDIFF('15:30:00.0', TIME(NOW())) <= 0 && o.date = CURRENT_DATE,
			- 1,
			o.confirmed
		)
		ELSE

		IF (
			TIMEDIFF('15:30:00.0', TIME(NOW())) <= 0 && o.date = CURRENT_DATE,
			- 1,
			NULL
		)
		END
	) l,
	sum(
		CASE
		WHEN o.meal = 'D' THEN

		IF (
			TIMEDIFF('20:15:00.0', TIME(NOW())) <= 0 && o.date = CURRENT_DATE,
			- 1,
			o.confirmed
		)
		ELSE

		IF (
			TIMEDIFF('20:15:00.0', TIME(NOW())) <= 0 && o.date = CURRENT_DATE,
			- 1,
			NULL
		)
		END
	) d
FROM
	offers AS o
WHERE
	o.o_number = ?
AND date >= CURRENT_DATE
GROUP BY
	o.date
