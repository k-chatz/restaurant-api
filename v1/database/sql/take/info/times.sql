SELECT
	TIME(NOW()) AS 'time',
	TIME_TO_SEC(
		TIMEDIFF('09:30:00.0', TIME(NOW()))
	) AS 'b_sec_left',
	TIME_TO_SEC(
		TIMEDIFF('15:30:00.0', TIME(NOW()))
	) AS 'l_sec_left',
	TIME_TO_SEC(
		TIMEDIFF('20:15:00.0', TIME(NOW()))
	) AS 'd_sec_left'
