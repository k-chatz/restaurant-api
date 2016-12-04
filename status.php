<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/*Status*/
$app->get("/status/info", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $status = authStatus($request, $response, $tokenData);
    if ($status == 200) {
        $username = $tokenData->username;
        $number = $tokenData->number;
        $userRole = $tokenData->role;

        $db = new DbHandler();

        /*TODO: Get only useful info for each user when is possible!*/

        $query = file_get_contents("Restaurant-API/database/sql/status/times.sql");

        $times = $db->select($query)[0];
        $b_sec_left = intval($times["b_sec_left"]);
        $l_sec_left = intval($times["l_sec_left"]);
        $d_sec_left = intval($times["d_sec_left"]);
        $b_date = $b_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
        $l_date = $l_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
        $d_date = $d_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");

        /*Take tab queries*/
        $query = file_get_contents("Restaurant-API/database/sql/take/info/question_for_date.sql");
        $b_q_question = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
        $l_q_question = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
        $d_q_question = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

        /*Give*/
        $query = file_get_contents("Restaurant-API/database/sql/give/info/offer_number.sql");
        $b_o_room = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
        $l_o_room = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
        $d_o_room = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

        $query = file_get_contents("Restaurant-API/database/sql/give/info/offers_count.sql");
        $b_offers = ($db->mysqli_prepared_query($query, "ss", array('B', $number)));
        $l_offers = ($db->mysqli_prepared_query($query, "ss", array('L', $number)));
        $d_offers = ($db->mysqli_prepared_query($query, "ss", array('D', $number)));

        $query = file_get_contents("Restaurant-API/database/sql/take/info/priority.sql");
        $b_priority = ($db->mysqli_prepared_query($query, "s", array('B')));
        $l_priority = ($db->mysqli_prepared_query($query, "s", array('L')));
        $d_priority = ($db->mysqli_prepared_query($query, "s", array('D')));

        /*Give tab queries*/
        $query = file_get_contents("Restaurant-API/database/sql/give/info/offer_for_date.sql");
        $b_o_offer = ($db->mysqli_prepared_query($query, "ss", array('B', $number)));
        $l_o_offer = ($db->mysqli_prepared_query($query, "ss", array('L', $number)));
        $d_o_offer = ($db->mysqli_prepared_query($query, "ss", array('D', $number)));

        $query = file_get_contents("Restaurant-API/database/sql/give/confirm/get.sql");
        $b_o_confirm = ($db->mysqli_prepared_query($query, "ss", array('B', $number)));
        $l_o_confirm = ($db->mysqli_prepared_query($query, "ss", array('L', $number)));
        $d_o_confirm = ($db->mysqli_prepared_query($query, "ss", array('D', $number)));

        $query = file_get_contents("Restaurant-API/database/sql/take/info/q_username.sql");
        $b_q_username = ($db->mysqli_prepared_query($query, "ss", array('B', $number)));
        $l_q_username = ($db->mysqli_prepared_query($query, "ss", array('L', $number)));
        $d_q_username = ($db->mysqli_prepared_query($query, "ss", array('D', $number)));

        $query = file_get_contents("Restaurant-API/database/sql/take/info/questions.sql");
        $b_q_questions = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
        $l_q_questions = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
        $d_q_questions = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

        $query = file_get_contents("Restaurant-API/database/sql/give/info/planned_offers.sql");
        $offersByDate = ($db->mysqli_prepared_query($query, "s", array($number)));


        /*TODO: Produce smallest json when is possible!*/

        $json = array(
            "time" => $times["time"],
            "meals" => (array(
                "b" => (array(
                    "o_number" => empty($b_o_room) ? null : ($b_o_room[0]['q_confirm'] == 0 ? '*****' : $b_o_room[0]['o_number']),
                    "sec_left" => $b_sec_left,
                    "q_question" => intval(empty($b_q_question) ? 0 : $b_q_question[0]["q_question"]),
                    "q_username" => empty($b_q_username) ? null : $b_q_username[0]['q_username'],
                    "questions" => intval(empty($b_q_questions) ? 0 : $b_q_questions[0]["questions"]),
                    "o_offer" => intval(empty($b_o_offer) ? 0 : $b_o_offer[0]["o_offer"]),
                    "o_confirm" => empty($b_o_confirm) ? null : $b_o_confirm[0]["o_confirm"],
                    "offers" => intval(empty($b_offers) ? 0 : $b_offers[0]["offers"]),
                )),
                "l" => (array(
                    "o_number" => empty($l_o_room) ? null : ($l_o_room[0]['q_confirm'] == 0 ? '*****' : $l_o_room[0]['o_number']),
                    "sec_left" => $l_sec_left,
                    "q_question" => intval(empty($l_q_question) ? 0 : $l_q_question[0]["q_question"]),
                    "q_username" => empty($l_q_username) ? null : $l_q_username[0]['q_username'],
                    "questions" => intval(empty($l_q_questions) ? 0 : $l_q_questions[0]["questions"]),
                    "o_offer" => intval(empty($l_o_offer) ? 0 : $l_o_offer[0]["o_offer"]),
                    "o_confirm" => empty($l_o_confirm) ? null : $l_o_confirm[0]["o_confirm"],
                    "offers" => intval(empty($l_offers) ? 0 : $l_offers[0]["offers"]),
                )),
                "d" => (array(
                    "o_number" => empty($d_o_room) ? null : ($d_o_room[0]['q_confirm'] == 0 ? '*****' : $d_o_room[0]['o_number']),
                    "sec_left" => $d_sec_left,
                    "q_question" => intval(empty($d_q_question) ? 0 : $d_q_question[0]["q_question"]),
                    "q_username" => empty($d_q_username) ? null : $d_q_username[0]['q_username'],
                    "questions" => intval(empty($d_q_questions) ? 0 : $d_q_questions[0]["questions"]),
                    "o_offer" => intval(empty($d_o_offer) ? 0 : $d_o_offer[0]["o_offer"]),
                    "o_confirm" => empty($d_o_confirm) ? null : $d_o_confirm[0]["o_confirm"],
                    "offers" => intval(empty($d_offers) ? 0 : $d_offers[0]["offers"]),
                )),
            )),
            "priority" => array(
                "b" => $b_priority,
                "l" => $l_priority,
                "d" => $d_priority
            ),
            "offersByDate" => $offersByDate
        );
        $out->write(json_encode($json));
    }
    return $response->withStatus($status);
});
