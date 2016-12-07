<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;

function jwt($data, $delay, $duration)
{
    global $config;

    $algorithm = $config->get('jwt')->get('algorithm');
    $secretKey = base64_decode($config->get('jwt')->get('key'));

    $issuedAt = time();
    $tokenId = base64_encode(mcrypt_create_iv(32));
    $serverName = $config->get('serverName');
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
                /*TODO: Query::Validate user at database with token data.*/
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
    global $config;
    $status = 200;  // Ok
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    $fbAccessToken = isset($post['fbAccessToken']) ? $post['fbAccessToken'] : 0;

    if ($fbAccessToken) {
        $fb = new \Facebook\Facebook([
            'app_id' => $config->get('fbApp')->get('id'),
            'app_secret' => $config->get('fbApp')->get('secret'),
            'default_graph_version' => $config->get('fbApp')->get('graph_version')
        ]);
        try {
            $fbResponse = $fb->get('/me', $fbAccessToken);
            $me = $fbResponse->getGraphUser();
            $userId = $me->getId();
            $name = $me->getName();
            try {
                $db = new DbHandler();
                $query = file_get_contents("Restaurant-API/database/sql/user/get/user.sql");
                $user = $db->mysqli_prepared_query($query, "s", array($userId));
                $user = empty($user) ? 0 : $user[0];
                if ($user) {
                    if ($userId == $user['username']) {

                        /*TODO: Update user information eg name*/
                        $out->write(json_encode(['jwt' => jwt($user, 0, 5000)]));
                    } else {
                        $status = 401;      // Unauthorized
                    }
                } else {
                    /*TODO: User is new, register user with fb credentials*/

                    $query = file_get_contents("Restaurant-API/database/sql/user/set/user.sql");
                    $result = $db->mysqli_prepared_query($query, "ss", array($userId, $name));
                    $out->write(json_encode($result));
                }
            } catch (Exception $e) {
                $out->write($e->getMessage());
                $status = 500;              // Internal Server Error
            }
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $out->write('Graph returned an error: ' . $e->getMessage());
            $status = 501;                  // Not Implemented
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            $out->write('Facebook SDK returned an error: ' . $e->getMessage());
            $status = 500;                  // Internal Server Error
        }
    } else {
        $status = 400;                      // Bad Request
    }
    return $response->withStatus($status);
});
