<?php
chdir(dirname(__DIR__));

require_once('vendor/autoload.php');

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

require_once 'database/dbHandler.php';
require_once 'user/passwordHash.php';
require 'libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$config = Factory::fromFile('Restaurant-API/config/config.php', true);

$app->contentType('application/json; charset=utf-8');


/*TODO: user.php*/
function jwt($data, $delay, $duration)
{
    global $config;

    $algorithm = $config->get('jwt')->get('algorithm');
    $secretKey = base64_decode($config->get('jwt')->get('key'));
    $serverName = $config->get('serverName');

    $tokenId = base64_encode(mcrypt_create_iv(32));
    $issuedAt = time();
    $notBefore = $issuedAt + $delay;
    $expire = $notBefore + $duration;

    $payload = [
        'iat' => $issuedAt,
        'jti' => $tokenId,
        'iss' => $serverName,
        'nbf' => $notBefore,
        'exp' => $expire,
        'data' => $data
    ];
    return JWT::encode($payload, $secretKey, $algorithm);
}

function authStatus($request, &$tokenData)
{
    global $config;
    $authHeader = $request->headers('authorization');
    if ($authHeader) {
        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if ($jwt) {
            try {
                $secretKey = base64_decode($config->get('jwt')->get('key'));
                $token = JWT::decode($jwt, $secretKey, [$config->get('jwt')->get('algorithm')]);
                $tokenData = $token->data;
                return 200;                 // Ok
            } catch (Exception $e) {
                echo $e->getMessage();

                /*TODO: If token is expired, then produce new token and send back to the user via http header*/

                return 401;                 // Unauthorized
            }
        } else {
            return 400;                     // Bad Request
        }
    } else {
        echo 'Token not found in request';
        return 400;                         // Bad Request
    }
}

$app->post("/user/do/connect", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    $userid = isset($post['userid']) ? $post['userid'] : 0;

    if ($userid) {
        try {
            $db = new DbHandler();
            $query = file_get_contents("Restaurant-API/database/sql/user/get/user.sql");
            $user = $db->mysqli_prepared_query($query, "s", array($userid));
            $user = empty($user) ? 0 : $user[0];
            if ($user) {
                if ($userid == $user['username']) {
                    echo json_encode(['jwt' => jwt($user, 0, 300)]);
                } else {
                    $app->response()->setStatus(401); // Unauthorized
                }
            } else {

                /*TODO: User is new, register user with fb credentials*/

                echo "You are new user, i will try to do register for you!";

                //$app->response()->setStatus(404); // Not Found
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            $app->response()->setStatus(500); // Internal Server Error
        }
    } else {
        $app->response()->setStatus(400); // Bad Request
    }
});

$app->get("/user/get/info", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    $tokenData = null;
    $status = authStatus($app->request(), $tokenData);
    if ($status == 200) {
        $asset = base64_encode(file_get_contents('http://lorempixel.com/200/300/cats/'));
        echo json_encode(['img' => $asset]);
    }
    $app->response()->setStatus($status);
});


/*TODO: status.php*/
/*Status*/
$app->get("/status/info", function () use ($app) {
    $status = authStatus($app->request(), $tokenData);
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
        echo json_encode($json);
    }
    $app->response()->setStatus($status);
});


/*TODO: take.php*/
/*Take*/
$app->post("/take/question", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                /*Delete expired questions for current user and current meal*/
                $query = file_get_contents("Restaurant-API/database/sql/take/question/delete_expired.sql");
                $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];

                /*Insert question*/
                $query = file_get_contents("Restaurant-API/database/sql/take/question/question.sql");
                $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
            } catch (Exception $e) {
                echo $e->getMessage();
                return 500;                 // Internal Server Error
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});

$app->post("/take/cancel", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                $query = file_get_contents("Restaurant-API/database/sql/take/cancel/cancel.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
            } catch (Exception $e) {
                echo $e->getMessage();
                return 500;                 // Internal Server Error
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});

$app->post("/take/confirm", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                $query = file_get_contents("Restaurant-API/database/sql/take/confirm/set.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
            } catch (Exception $e) {
                echo $e->getMessage();
                return 500;                 // Internal Server Error
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});

$app->post("/take/reject", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                $query = file_get_contents("Restaurant-API/database/sql/take/cancel/cancel.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
            } catch (Exception $e) {
                echo $e->getMessage();
                return 500;                 // Internal Server Error
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});


/*TODO: give.php*/
/*Give*/
$app->post("/give/offer", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                echo "401 Unauthorized (Visitors don't have 'give' actions!)";    // Unauthorized
                $status = 401;
            } else {
                try {
                    $db = new DbHandler();

                    /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                    $query = file_get_contents("Restaurant-API/database/sql/give/offer/new_offer.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                } catch (Exception $e) {
                    echo $e->getMessage();
                    return 500;                 // Internal Server Error
                }
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});

$app->post("/give/cancel", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                echo "401 Unauthorized (Visitors don't have 'give' actions!)";    // Unauthorized
                $status = 401;
            } else {
                try {
                    $db = new DbHandler();

                    /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                    $query = file_get_contents("Restaurant-API/database/sql/give/cancel/cancel.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                } catch (Exception $e) {
                    echo $e->getMessage();
                    return 500;                 // Internal Server Error
                }
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});

$app->post("/give/confirm", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                echo "401 Unauthorized (Visitors don't have 'give' actions!)";    // Unauthorized
                $status = 401;
            } else {
                try {
                    $db = new DbHandler();

                    /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                    if (isset($post['status']) && isset($post['date'])) {
                        switch ($post['status']) {
                            case -1: /*Unchecked - Add new record for specific date-meal*/
                                $query = file_get_contents("Restaurant-API/database/sql/give/offer/planned_new_offer.sql");
                                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                                break;
                            case 0: /*Undefined - Update value of 'confirmed' field and set to true for record with specific date-meal*/
                                $query = file_get_contents("Restaurant-API/database/sql/give/confirm/planned_set.sql");
                                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                                break;
                            case 1: /*Checked - Delete record with specific date-meal (cancel query)*/
                                $query = file_get_contents("Restaurant-API/database/sql/give/cancel/planned_cancel.sql");
                                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                                break;
                        }
                    } else {
                        $query = file_get_contents("Restaurant-API/database/sql/give/confirm/set.sql");
                        $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                    return 500;                 // Internal Server Error
                }
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});

$app->post("/give/reject", function () use ($app) {
    $post = json_decode($app->request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($app->request(), $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                echo "401 Unauthorized (Visitors don't have 'give' actions!)";    // Unauthorized
                $status = 401;
            } else {
                try {
                    $db = new DbHandler();

                    /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                    $query = file_get_contents("Restaurant-API/database/sql/give/cancel/cancel.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                } catch (Exception $e) {
                    echo $e->getMessage();
                    return 500;                 // Internal Server Error
                }
            }
        }
    } else {
        echo "400 Bad Request";
        $status = 400;
    }
    $app->response()->setStatus($status);
});

$app->run();
?>
