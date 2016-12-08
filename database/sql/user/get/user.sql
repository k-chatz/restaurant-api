SELECT
u.username,
u.`name`,
u.number,
u.role,
u.picture,
u.gender
FROM `users` u
WHERE u.username = ?

