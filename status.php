<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function progressBarCalc($duration, $sec_left){
    $result =  (($duration - ($sec_left <= 0 ? $sec_left + 86400 : $sec_left) ) / $duration) * 100;
    return $result < 0 ? 0 : $result;
}

/*Status*/
$app->get("/status/info", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $get = $request->getQueryParams();
    if (isset($get['tab']) && $get['tab'] != null) {
        $status = authStatus($request, $response, $tokenData, $user);
        if ($status == OK) {
            $username = $tokenData->username;
            $number = $user[0]['number'];
            $role = $user[0]['role'];
            $db = new DbHandler();

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


            /*TODO: Produce smallest json when is possible!*/

            $json = array(
                "time" => $times["time"],
                "meals" => (array(
                    "b" => (array(
                        "progress" => progressBarCalc(5400, $b_sec_left),
                        "o_number" => empty($b_o_room) ? null : ($b_o_room[0]['q_confirm'] == 0 ? '*****' : $b_o_room[0]['o_number']),
                        "sec_left" => $b_sec_left,
                        "q_question" => intval(empty($b_q_question) ? 0 : $b_q_question[0]["q_question"]),
                        "q_username" => empty($b_q_username) ? null : $b_q_username[0]['q_username'],
                        "questions" => intval(empty($b_q_questions) ? 0 : $b_q_questions[0]["questions"]),
                        "o_confirm" => empty($b_o_confirm) ? null : $b_o_confirm[0]["o_confirm"],
                        "o_offer" => intval(empty($b_o_offer) ? 0 : $b_o_offer[0]["o_offer"]),
                        "offers" => intval(empty($b_offers) ? 0 : $b_offers[0]["offers"]),
                    )),
                    "l" => (array(
                        "progress" => progressBarCalc(9000, $l_sec_left),
                        "o_number" => empty($l_o_room) ? null : ($l_o_room[0]['q_confirm'] == 0 ? '*****' : $l_o_room[0]['o_number']),
                        "sec_left" => $l_sec_left,
                        "q_question" => intval(empty($l_q_question) ? 0 : $l_q_question[0]["q_question"]),
                        "q_username" => empty($l_q_username) ? null : $l_q_username[0]['q_username'],
                        "questions" => intval(empty($l_q_questions) ? 0 : $l_q_questions[0]["questions"]),
                        "o_confirm" => empty($l_o_confirm) ? null : $l_o_confirm[0]["o_confirm"],
                        "o_offer" => intval(empty($l_o_offer) ? 0 : $l_o_offer[0]["o_offer"]),
                        "offers" => intval(empty($l_offers) ? 0 : $l_offers[0]["offers"]),
                    )),
                    "d" => (array(
                        "progress" => progressBarCalc(6301, $d_sec_left),
                        "o_number" => empty($d_o_room) ? null : ($d_o_room[0]['q_confirm'] == 0 ? '*****' : $d_o_room[0]['o_number']),
                        "sec_left" => $d_sec_left,
                        "q_question" => intval(empty($d_q_question) ? 0 : $d_q_question[0]["q_question"]),
                        "q_username" => empty($d_q_username) ? null : $d_q_username[0]['q_username'],
                        "questions" => intval(empty($d_q_questions) ? 0 : $d_q_questions[0]["questions"]),
                        "o_confirm" => empty($d_o_confirm) ? null : $d_o_confirm[0]["o_confirm"],
                        "o_offer" => intval(empty($d_o_offer) ? 0 : $d_o_offer[0]["o_offer"]),
                        "offers" => intval(empty($d_offers) ? 0 : $d_offers[0]["offers"]),
                    )),
                ))
            );

            switch ($get['tab']) {
                case 'menu':
                    $query = file_get_contents("Restaurant-API/database/sql/menu/current_menu.sql");
                    $b_menu = $db->mysqli_prepared_query($query, "s", array('B'));
                    $l_menu = $db->mysqli_prepared_query($query, "s", array('L'));
                    $d_menu = $db->mysqli_prepared_query($query, "s", array('D'));
                    $menu = array(
                        "menu" => array(
                            "b" =>
                                array(
                                    "meal" => empty($b_menu[0]) ? null : $b_menu[0]['meal'],
                                    "date" => empty($b_menu[0]) ? null : $b_menu[0]['date'],
                                ),
                            "l" =>
                                array(
                                    "meal" => empty($l_menu[0]) ? null : $l_menu[0]['meal'],
                                    "date" => empty($l_menu[0]) ? null : $l_menu[0]['date'],
                                ),
                            "d" =>
                                array(
                                    "meal" => empty($d_menu[0]) ? null: $d_menu[0]['meal'],
                                    "date" => empty($d_menu[0]) ? null : $d_menu[0]['date'],
                                ),
                        )
                    );
                    $json = array_merge($json, $menu);
                    break;
                case 'take':
                    $query = file_get_contents("Restaurant-API/database/sql/take/info/priority.sql");
                    $b_priority = ($db->mysqli_prepared_query($query, "s", array('B')));
                    $l_priority = ($db->mysqli_prepared_query($query, "s", array('L')));
                    $d_priority = ($db->mysqli_prepared_query($query, "s", array('D')));
                    $priority = array(
                        "priority" => array(
                            "b" => $b_priority,
                            "l" => $l_priority,
                            "d" => $d_priority
                        )
                    );
                    $json = array_merge($json, $priority);
                    break;
                case 'give':
                    $query = file_get_contents("Restaurant-API/database/sql/give/info/planned_offers.sql");
                    $offersByDate = ($db->mysqli_prepared_query($query, "s", array($number)));
                    $offers = array(
                        "offersByDate" => $offersByDate
                    );
                    $json = array_merge($json, $offers);
                    break;
            }
            $out->write(json_encode($json));
        }
    } else {
        $status = BAD_REQUEST;
        $out->write(json_encode(handleError('Bad Request', "HTTP", $status)));
    }
    return $response->withStatus($status);
});
