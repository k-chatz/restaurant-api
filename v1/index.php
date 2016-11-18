<?php
require_once 'database/dbHandler.php';
require_once 'user/passwordHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->contentType('application/json; charset=utf-8');

$_SESSION['username'] = 'kwstarikanos';

/*Status*/
$app->get("/status/info", function () use ($app) {
    $username = $_SESSION['username'];
    $db = new DbHandler();

    $query = file_get_contents("database/sql/take/info/times.sql");
    $times = $db->select($query);

    $query = file_get_contents("database/sql/take/info/question_today.sql");
    $b_q_today = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
    $l_q_today = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
    $d_q_today = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

    $query = file_get_contents("database/sql/take/info/question_tomorrow.sql");
    $b_q_tomorrow = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
    $l_q_tomorrow = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
    $d_q_tomorrow = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

    $query = file_get_contents("database/sql/take/info/o_number.sql");
    $b_o_room = ($db->mysqli_prepared_query($query, "ss", array('B', $username)));
    $l_o_room = ($db->mysqli_prepared_query($query, "ss", array('L', $username)));
    $d_o_room = ($db->mysqli_prepared_query($query, "ss", array('D', $username)));

    $query = file_get_contents("database/sql/take/info/offers.sql");
    $b_offers = ($db->mysqli_prepared_query($query, "s", array('B')));
    $l_offers = ($db->mysqli_prepared_query($query, "s", array('L')));
    $d_offers = ($db->mysqli_prepared_query($query, "s", array('D')));

    $query = file_get_contents("database/sql/take/info/priority.sql");
    $b_priority = ($db->mysqli_prepared_query($query, "s", array('B')));
    $l_priority = ($db->mysqli_prepared_query($query, "s", array('L')));
    $d_priority = ($db->mysqli_prepared_query($query, "s", array('D')));

    $json = array(
        "time" => $times[0]["time"],
        "meals" => (array(
            "b" => (array(
                "o_number" => empty($b_o_room) ? null : ( $b_o_room[0]['q_confirm'] == 0 ? '*****' : $b_o_room[0]['o_number']),
                "sec_left" => intval($times[0]["b_sec_left"]),
                "q_today" => intval(empty($b_q_today) ? 0 : $b_q_today[0]["q_today"]),
                "q_tomorrow" => intval(empty($b_q_tomorrow) ? 0 : $b_q_tomorrow[0]["q_tomorrow"]),
                "offers" => intval(empty($b_offers) ? 0 : $b_offers[0]["offers"]),
            )),
            "l" => (array(
                "o_number" => empty($l_o_room) ? null : ( $l_o_room[0]['q_confirm'] == 0 ? '*****' : $l_o_room[0]['o_number']),
                "sec_left" => intval($times[0]["l_sec_left"]),
                "q_today" => intval(empty($l_q_today) ? 0 : $l_q_today[0]["q_today"]),
                "q_tomorrow" => intval(empty($l_q_tomorrow) ? 0 : $l_q_tomorrow[0]["q_tomorrow"]),
                "offers" => intval(empty($l_offers) ? 0 : $l_offers[0]["offers"]),
            )),
            "d" => (array(
                "o_number" => empty($d_o_room) ? null : ( $d_o_room[0]['q_confirm'] == 0 ? '*****' : $d_o_room[0]['o_number']),
                "sec_left" => intval($times[0]["d_sec_left"]),
                "q_today" => intval(empty($d_q_today) ? 0 : $d_q_today[0]["q_today"]),
                "q_tomorrow" => intval(empty($d_q_tomorrow) ? 0 : $d_q_tomorrow[0]["q_tomorrow"]),
                "offers" => intval(empty($d_offers) ? 0 : $d_offers[0]["offers"]),
            )),
        )),
        "priority" => array(
            "b" => $b_priority,
            "l" => $l_priority,
            "d" => $d_priority
        )
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
    $params = array($username, $post['meal']);
    $db->mysqli_prepared_query($query, "ss", $params)[0];

    /*Insert question*/
    $query = file_get_contents("database/sql/take/question/question.sql");
    $params = array($username, $post['meal']);
    $db->mysqli_prepared_query($query, "ss", $params)[0];
});

$app->post("/take/cancel", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/take/cancel/cancel.sql");
    $params = array($username, $post['meal']);
    $result = $db->mysqli_prepared_query($query, "ss", $params)[0];
});

$app->post("/take/confirm", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/take/confirm/confirm.sql");
    $params = array($username, $post['meal']);
    $result = $db->mysqli_prepared_query($query, "ss", $params)[0];
});

$app->post("/take/reject", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/take/cancel/cancel.sql");
    $params = array($username, $post['meal']);
    $result = $db->mysqli_prepared_query($query, "ss", $params)[0];
});



/*Give*/
$app->post("/give/offer", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/give/offer/offer.sql");
    $params = array($username, $post['meal']);
    $result = $db->mysqli_prepared_query($query, "ss", $params)[0];
});

$app->post("/give/offers", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/give/offers/offers.sql");
    $params = array($username, $post['meal']);
    $result = $db->mysqli_prepared_query($query, "ss", $params)[0];
});

$app->post("/give/cancel", function () use ($app) {
    $username = $_SESSION['username'];
    $json = $app->request->getBody();
    $post = json_decode($json, true);
    $db = new DbHandler();
    $query = file_get_contents("database/sql/give/cancel/cancel.sql");
    $params = array($username, $post['meal']);
    $result = $db->mysqli_prepared_query($query, "ss", $params)[0];
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
