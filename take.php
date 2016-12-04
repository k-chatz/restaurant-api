<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/*Take*/
$app->post("/take/question", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                /*Delete expired questions for current user and current meal*/
                $query = file_get_contents("Restaurant-API/database/sql/take/question/delete_expired.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];

                /*Insert question*/
                $query = file_get_contents("Restaurant-API/database/sql/take/question/question.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
                $out->write(json_encode($result, true));
            } catch (Exception $e) {
                $out->write($e->getMessage());
                $status = 500;                 // Internal Server Error
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});

$app->post("/take/cancel", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                $query = file_get_contents("Restaurant-API/database/sql/take/cancel/cancel.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
                $out->write(json_encode($result, true));
            } catch (Exception $e) {
                $out->write($e->getMessage());
                $status = 500;                 // Internal Server Error
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});

$app->post("/take/confirm", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                $query = file_get_contents("Restaurant-API/database/sql/take/confirm/set.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
                $out->write(json_encode($result, true));
            } catch (Exception $e) {
                $out->write($e->getMessage());
                $status = 500;                 // Internal Server Error
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});

$app->post("/take/reject", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData);
        if ($status == 200) {
            $username = $tokenData->username;
            $userRole = $tokenData->role;
            try {
                $db = new DbHandler();

                /*TODO: Get Query execution errors or information eg affected_rows, dublicate entry etc.*/

                $query = file_get_contents("Restaurant-API/database/sql/take/cancel/cancel.sql");
                $result = $db->mysqli_prepared_query($query, "ss", array($username, $post['meal']))[0];
                $out->write(json_encode($result, true));
            } catch (Exception $e) {
                $out->write($e->getMessage());
                $status = 500;                 // Internal Server Error
            }
        }
    } else {
        $out->write("400 Bad Request");
        $status = 400;
    }
    return $response->withStatus($status);
});
