<?php
require_once 'database/dbHandler.php';
require_once 'user/passwordHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->contentType('application/json; charset=utf-8');

$_SESSION['username'] = 'kwstarikanos';
$_SESSION['number'] = 'Δ4005';

/*Status*/
$app->get("/status/info", function () use ($app) {
    $username = $_SESSION['username'];
    $number = $_SESSION['number'];

    $db = new DbHandler();

    $query = file_get_contents("database/sql/status/times.sql");
    $times = $db->select($query)[0];
    $b_sec_left = intval($times["b_sec_left"]);
    $l_sec_left = intval($times["l_sec_left"]);
    $d_sec_left = intval($times["d_sec_left"]);
    $b_date = $b_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
    $l_date = $l_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
    $d_date = $d_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");

    /*Take tab queries*/
    $query = file_get_contents("database/sql/take/info/question_for_date.sql");
    $b_q_question = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
    $l_q_question = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
    $d_q_question = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

    /*Give*/
    $query = file_get_contents("database/sql/give/info/offer_number.sql");
    $b_o_room = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
    $l_o_room = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
    $d_o_room = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

    $query = file_get_contents("database/sql/give/info/offers_count.sql");
    $b_offers = ($db->mysqli_prepared_query($query, "sss", array('B', $number, $b_date)));
    $l_offers = ($db->mysqli_prepared_query($query, "sss", array('L', $number, $l_date)));
    $d_offers = ($db->mysqli_prepared_query($query, "sss", array('D', $number, $d_date)));




    $query = file_get_contents("database/sql/take/info/priority.sql");
    $b_priority = ($db->mysqli_prepared_query($query, "s", array('B')));
    $l_priority = ($db->mysqli_prepared_query($query, "s", array('L')));
    $d_priority = ($db->mysqli_prepared_query($query, "s", array('D')));

    /*Give tab queries*/
    $query = file_get_contents("database/sql/give/info/offer_for_date.sql");
    $b_o_offer = ($db->mysqli_prepared_query($query, "sss", array($b_date, 'B', $number)));
    $l_o_offer = ($db->mysqli_prepared_query($query, "sss", array($l_date, 'L', $number)));
    $d_o_offer = ($db->mysqli_prepared_query($query, "sss", array($d_date, 'D', $number)));


    $query = file_get_contents("database/sql/give/confirm/get.sql");
    $b_o_confirm = ($db->mysqli_prepared_query($query, "sss", array('B', $number, $b_date)));
    $l_o_confirm = ($db->mysqli_prepared_query($query, "sss", array('L', $number, $l_date)));
    $d_o_confirm = ($db->mysqli_prepared_query($query, "sss", array('D', $number, $d_date)));


    $query = file_get_contents("database/sql/take/info/q_username.sql");
    $b_q_username = ($db->mysqli_prepared_query($query, "ss", array('B', $number)));
    $l_q_username = ($db->mysqli_prepared_query($query, "ss", array('L', $number)));
    $d_q_username = ($db->mysqli_prepared_query($query, "ss", array('D', $number)));

    $query = file_get_contents("database/sql/take/info/questions.sql");
    $b_q_questions = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
    $l_q_questions = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
    $d_q_questions = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

    $query = file_get_contents("database/sql/give/info/planned_offers.sql");
    $offersByDate = ($db->mysqli_prepared_query($query, "s", array($number)));


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

    echo json_encode($json);
});


/*Take*/
$app->post("/take/question", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();

    /*Delete expired questions for current user and current meal*/
    $query = file_get_contents("database/sql/take/question/delete_expired.sql");
    $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];

    /*Insert question*/
    $query = file_get_contents("database/sql/take/question/question.sql");
    $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
});

$app->post("/take/cancel", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/take/cancel/cancel.sql");
    $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
});

$app->post("/take/confirm", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/take/confirm/set.sql");
    $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
});

$app->post("/take/reject", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/take/cancel/cancel.sql");
    $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
});


/*Give*/
$app->post("/give/offer", function () use ($app) {
    $username = $_SESSION['username'];
    $number = $_SESSION['number'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();

    $query = file_get_contents("database/sql/status/times.sql");
    $times = $db->select($query)[0];

    switch ($post['meal']){
        case 'B':
            $b_sec_left = intval($times["b_sec_left"]);
            $date = $b_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
            break;
        case 'L':
            $l_sec_left = intval($times["l_sec_left"]);
            $date = $l_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
            break;
        case 'D':
            $d_sec_left = intval($times["d_sec_left"]);
            $date = $d_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
            break;
    }

    $query = file_get_contents("database/sql/give/offer/new_offer.sql");
    $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $date))[0];
});

$app->post("/give/cancel", function () use ($app) {
    $username = $_SESSION['username'];
    $number = $_SESSION['number'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();

    $query = file_get_contents("database/sql/status/times.sql");
    $times = $db->select($query)[0];

    switch ($post['meal']){
        case 'B':
            $b_sec_left = intval($times["b_sec_left"]);
            $date = $b_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
            break;
        case 'L':
            $l_sec_left = intval($times["l_sec_left"]);
            $date = $l_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
            break;
        case 'D':
            $d_sec_left = intval($times["d_sec_left"]);
            $date = $d_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
            break;
    }

    $query = file_get_contents("database/sql/give/cancel/cancel.sql");
    $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $date))[0];
});

$app->post("/give/confirm", function () use ($app) {
    $username = $_SESSION['username'];
    $number = $_SESSION['number'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();

    if (isset($post['status']) && isset($post['date'])) {
        switch ($post['status']) {
            case -1:
                /*Add new record for specific date-meal*/
                $query = file_get_contents("database/sql/give/offer/new_offer.sql");
                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                break;
            case 0:
                /*Update value of 'confirmed' field and set to true for record with specific date-meal*/
                $query = file_get_contents("database/sql/give/confirm/planned_set.sql");
                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                break;
            case 1:
                /*Delete record with specific date-meal (cancel query)*/
                $query = file_get_contents("database/sql/give/cancel/planned_cancel.sql");
                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                break;
        }
    } else {

        $query = file_get_contents("database/sql/status/times.sql");
        $times = $db->select($query)[0];

        switch ($post['meal']){
            case 'B':
                $b_sec_left = intval($times["b_sec_left"]);
                $date = $b_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
                break;
            case 'L':
                $l_sec_left = intval($times["l_sec_left"]);
                $date = $l_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
                break;
            case 'D':
                $d_sec_left = intval($times["d_sec_left"]);
                $date = $d_sec_left < 0 ? date('Y-m-d', strtotime(date("Y-m-d") . " + 1 day")) : date("Y-m-d");
                break;
        }

        $query = file_get_contents("database/sql/give/confirm/set.sql");
        $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $date))[0];
    }

});

$app->post("/give/reject", function () use ($app) {
    $username = $_SESSION['username'];
    $number = $_SESSION['number'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/give/cancel/cancel.sql");
    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
});


//require_once 'authentication.php';
/**Verifying required params posted or not
 * function verifyRequiredParams($required_fields,$request_params) {
 * $error = false;
 * $error_fields = "";
 * foreach ($required_fields as $field) {
 * if (!isset($request_params->$field) || strlen(trim($request_params->$field)) <= 0) {
 * $error = true;
 * $error_fields .= $field . ', ';
 * }
 * }
 *
 * if ($error) {
 * // Required field(s) are missing or empty
 * // echo error json and stop the app
 * $response = array();
 * $app = \Slim\Slim::getInstance();
 * $response["status"] = "error";
 * $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
 * echoResponse(200, $response);
 * $app->stop();
 * }
 * }
 *
 * function echoResponse($status_code, $response) {
 * $app = \Slim\Slim::getInstance();
 * // Http response code
 * $app->status($status_code);
 *
 * // setting response content type to json
 * $app->contentType('application/json');
 *
 * echo json_encode($response);
 * }
 */


$app->run();
?>
