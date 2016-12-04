<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;

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

function authStatus(&$request, &$response, &$tokenData)
{
    global $config;
    $out = $response->getBody();
    $authHeader = $request->getHeader('authorization')[0];

    if ($authHeader) {
        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if ($jwt) {
            try {
                $secretKey = base64_decode($config->get('jwt')->get('key'));
                $token = JWT::decode($jwt, $secretKey, [$config->get('jwt')->get('algorithm')]);
                $tokenData = $token->data;
                return 200;                 // Ok
            } catch (Exception $e) {
                $out->write($e->getMessage());

                /*TODO: If token is expired, then produce new token and send back to the user via http header*/

                return 401;                 // Unauthorized
            }
        } else {
            return 400;                     // Bad Request
        }
    } else {
        $out->write('Token not found in request');
        return 400;                         // Bad Request
    }
}

$app->post("/user/do/connect", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    $userid = isset($post['userid']) ? $post['userid'] : 0;
    $status = 200;  // Ok
    if ($userid) {
        try {
            $db = new DbHandler();
            $query = file_get_contents("Restaurant-API/database/sql/user/get/user.sql");
            $user = $db->mysqli_prepared_query($query, "s", array($userid));
            $user = empty($user) ? 0 : $user[0];
            if ($user) {
                if ($userid == $user['username']) {
                    $out->write(json_encode(['jwt' => jwt($user, 0, 172800)]));
                } else {
                    $status = 401;      // Unauthorized
                }
            } else {

                /*TODO: User is new, register user with fb credentials*/

                echo "You are new user, i will try to do register for you!";
                //$status = 404;        // Not Found
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            $status = 500;              // Internal Server Error
        }
    } else {
        $status = 400;                  // Bad Request
    }

    return $response->withStatus($status);
});
