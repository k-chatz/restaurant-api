<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/*Give*/
$app->post("/give/offer", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                $out->write("401 Unauthorized (Visitors don't have 'give' actions!)");    // Unauthorized
                $status = 401;
            } else {
                try {
                    $db = new DbHandler();

                    /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                    $query = file_get_contents("Restaurant-API/database/sql/give/offer/new_offer.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                    $out->write(json_encode($result, true));
                } catch (Exception $e) {
                    $out->write($e->getMessage());
                    $status = 500;                 // Internal Server Error
                }
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});

$app->post("/give/cancel", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                $out->write("401 Unauthorized (Visitors don't have 'give' actions!)");    // Unauthorized
                $status = 401;
            } else {
                try {
                    $db = new DbHandler();

                    /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                    $query = file_get_contents("Restaurant-API/database/sql/give/cancel/cancel.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                    $out->write(json_encode($result, true));
                } catch (Exception $e) {
                    $out->write($e->getMessage());
                    $status = 500;                 // Internal Server Error
                }
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});

$app->post("/give/confirm", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                $out->write("401 Unauthorized (Visitors don't have 'give' actions!)");    // Unauthorized
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
                                $out->write(json_encode($result, true));
                                break;
                            case 0: /*Undefined - Update value of 'confirmed' field and set to true for record with specific date-meal*/
                                $query = file_get_contents("Restaurant-API/database/sql/give/confirm/planned_set.sql");
                                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                                $out->write(json_encode($result, true));
                                break;
                            case 1: /*Checked - Delete record with specific date-meal (cancel query)*/
                                $query = file_get_contents("Restaurant-API/database/sql/give/cancel/planned_cancel.sql");
                                $result = $db->mysqli_prepared_query($query, "sss", array($number, $post['meal'], $post['date']))[0];
                                $out->write(json_encode($result, true));
                                break;
                        }
                    } else {
                        $query = file_get_contents("Restaurant-API/database/sql/give/confirm/set.sql");
                        $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                        $out->write(json_encode($result, true));
                    }
                } catch (Exception $e) {
                    $out->write($e->getMessage());
                    $status = 500;                 // Internal Server Error
                }
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});

$app->post("/give/reject", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $number = $tokenData->number;
            $userRole = $tokenData->role;
            if ($userRole == 'V') {
                $out->write("401 Unauthorized (Visitors don't have 'give' actions!)");    // Unauthorized
                $status = 401;
            } else {
                try {
                    $db = new DbHandler();

                    /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                    $query = file_get_contents("Restaurant-API/database/sql/give/cancel/cancel.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($number, $post['meal']))[0];
                    $out->write(json_encode($result, true));
                } catch (Exception $e) {
                    $out->write($e->getMessage());
                    $status = 500;                 // Internal Server Error
                }
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});
