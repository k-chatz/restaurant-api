SELECT
u.username,
u.number,
u.role,
u.fbLongAccessToken
FROM `users` u
WHERE
u.username = ?

