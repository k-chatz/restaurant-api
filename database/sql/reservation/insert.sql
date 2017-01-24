INSERT INTO `reservations`
            (`q_username`,
             `q_meal`,
             `q_date`,
             `o_number`,
             `o_meal`,
             `o_date`,
             `moment`)
VALUES      (?,
             ?,
             ?,
             ?,
             ?,
             ?,
             NOW())
