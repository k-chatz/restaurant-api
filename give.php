<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/*Give*/
$app->post("/give/offer", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData, $user);
        if ($status == OK) {
            $number = $user[0]['number'];
            $userRole = $user[0]['role'];
            if ($userRole == 'V') {
                $status = FORBIDDEN;
                $out->write(json_encode(handleError("Visitors don't have 'give' actions!", "User role", $status)));
            } else {
                try {
                    $db = new DbHandler();
                    $query = file_get_contents("Restaurant-API/database/sql/give/offer/new_offer.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                    $output = [
                        "give" => array(
                            "offer" => $result
                        )
                    ];
                    $out->write(json_encode($output, true));
                } catch (Exception $e) {
                    $status = INTERNAL_SERVER_ERROR;
                    $out->write(json_encode(handleError($e->getMessage(), "Database", $status)));
                }
            }
        }
    } else {
        $status = BAD_REQUEST;
        $out->write(json_encode(handleError('Bad Request', "HTTP", $status)));
    }
    return $response->withStatus($status);
});

$app->post("/give/cancel", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData, $user);
        if ($status == OK) {
            $number = $user[0]['number'];
            $role = $user[0]['role'];
            if ($role == 'V') {
                $status = FORBIDDEN;
                $out->write(json_encode(handleError("Visitors don't have 'give' actions!", "User role", $status)));
            } else {
                try {
                    $db = new DbHandler();
                    $query = file_get_contents("Restaurant-API/database/sql/give/cancel/cancel.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                    $output = [
                        "give" => array(
                            "cancel" => $result
                        )
                    ];
                    $out->write(json_encode($output, true));
                } catch (Exception $e) {
                    $status = INTERNAL_SERVER_ERROR;
                    $out->write(json_encode(handleError($e->getMessage(), "Database", $status)));
                }
            }
        }
    } else {
        $status = BAD_REQUEST;
        $out->write(json_encode(handleError('Bad Request', "HTTP", $status)));
    }
    return $response->withStatus($status);
});

$app->post("/give/confirm", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData, $user);
        if ($status == OK) {
            $number = $user[0]['number'];
            $role = $user[0]['role'];
            if ($role == 'V') {
                $status = FORBIDDEN;
                $out->write(json_encode(handleError("Visitors don't have 'give' actions!", "User role", $status)));
            } else {
                try {
                    $db = new DbHandler();
                    $result = null;
                    $output = null;
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
                        $output = [
                            "give" => array(
                                "status" => $post['status'],
                                "date" => $post['date'],
                                "confirm" => $result
                            )
                        ];
                    } else {
                        $query = file_get_contents("Restaurant-API/database/sql/give/confirm/set.sql");
                        $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                        $output = [
                            "give" => array(
                                "confirm" => $result
                            )
                        ];
                    }
                    $out->write(json_encode($output, true));
                } catch (Exception $e) {
                    $status = INTERNAL_SERVER_ERROR;
                    $out->write(json_encode(handleError($e->getMessage(), "Database", $status)));
                }
            }
        }
    } else {
        $status = BAD_REQUEST;
        $out->write(json_encode(handleError('Bad Request', "HTTP", $status)));
    }
    return $response->withStatus($status);
});

$app->post("/give/reject", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData, $user);
        if ($status == OK) {
            $number = $user[0]['number'];
            $role = $user[0]['role'];
            if ($role == 'V') {
                $status = FORBIDDEN;
                $out->write(json_encode(handleError("Visitors don't have 'give' actions!", "User role", $status)));
            } else {
                try {
                    $db = new DbHandler();
                    $query = file_get_contents("Restaurant-API/database/sql/give/cancel/cancel.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                    $output = [
                        "give" => array(
                            "reject" => $result
                        )
                    ];
                    $out->write(json_encode($output, true));
                } catch (Exception $e) {
                    $status = INTERNAL_SERVER_ERROR;
                    $out->write(json_encode(handleError($e->getMessage(), "Database", $status)));
                }
            }
        }
    } else {
        $status = BAD_REQUEST;
        $out->write(json_encode(handleError('Bad Request', "HTTP", $status)));
    }
    return $response->withStatus($status);
});
