#QUERY OK

DELETE
FROM
	offers
WHERE
	! (
		(
			date = ADDDATE(
				CURRENT_DATE (),
				INTERVAL - 1 DAY
			)
			AND (
				(
					TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
					AND TIMEDIFF(time, '09:30:00.0') >= 0
					AND meal = 'B'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
					AND TIMEDIFF(time, '15:30:00.0') >= 0
					AND meal = 'L'
				)
				OR (
					TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
					AND TIMEDIFF(time, '20:15:00.0') >= 0
					AND meal = 'D'
				)
			)
		)
		OR (
			date = CURRENT_DATE
			AND (
				(
					(
						TIMEDIFF(CURRENT_TIME, '09:30:00.0') < 0
						AND TIMEDIFF(time, '09:30:00.0') < 0
						AND meal = 'B'
					)
					OR (
						TIMEDIFF(CURRENT_TIME, '09:30:00.0') >= 0
						AND TIMEDIFF(time, '09:30:00.0') >= 0
						AND meal = 'B'
					)
				)
				OR (
					(
						TIMEDIFF(CURRENT_TIME, '15:30:00.0') < 0
						AND TIMEDIFF(time, '15:30:00.0') < 0
						AND meal = 'L'
					)
					OR (
						TIMEDIFF(CURRENT_TIME, '15:30:00.0') >= 0
						AND TIMEDIFF(time, '15:30:00.0') >= 0
						AND meal = 'L'
					)
				)
				OR (
					(
						TIMEDIFF(CURRENT_TIME, '20:15:00.0') < 0
						AND TIMEDIFF(time, '20:15:00.0') < 0
						AND meal = 'D'
					)
					OR (
						TIMEDIFF(CURRENT_TIME, '20:15:00.0') >= 0
						AND TIMEDIFF(time, '20:15:00.0') >= 0
						AND meal = 'D'
					)
				)
			)
		)
	)
AND o_number = ?
AND meal = ?
