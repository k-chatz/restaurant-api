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

function validate($jwtData, &$out, &$user)
{
    $status = OK;
    try {
        $db = new DbHandler();
        $query = file_get_contents("Restaurant-API/database/sql/user/get.sql");
        $user = $db->mysqli_prepared_query($query, "s", array($jwtData->username));
        if (empty($user)) {
            $status = UNAUTHORIZED;
            $out->write(json_encode(handleError("User Not Found!", "Database", $status)));
        }
    } catch (Exception $e) {
        $status = INTERNAL_SERVER_ERROR;
        $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
    }
    return $status;
}

function authStatus(&$request, &$response, &$tokenData, &$user)
{
    global $config;
    $user = null;
    $out = $response->getBody();
    $authHeader = $request->getHeader('authorization')[0];

    if ($authHeader) {
        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if ($jwt) {
            try {
                $secretKey = base64_decode($config->get('jwt')->get('key'));
                $token = JWT::decode($jwt, $secretKey, [$config->get('jwt')->get('algorithm')]);
                /*Validate user at database with token data.*/
                $tokenData = $token->data;
                return validate($token->data, $out, $user);
            } catch (Exception $e) {
                $out->write(json_encode(handleError($e->getMessage(), "Json Web Token", $e->getCode())));
                return UNAUTHORIZED;
            }
        } else {
            return BAD_REQUEST;
        }
    } else {
        $out->write(json_encode(handleError('Token not found in request', "Json Web Token", 400)));
        return BAD_REQUEST;
    }
}

/*Get whole user information with a valid access token!*/
$app->get("/user", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $status = authStatus($request, $response, $jwtData, $user);
    if ($status == 200) {
        $outputJson = [
            'user' => array(
                'accessToken' => $user[0]['accessToken'],
                'isNew' => false,
                'username' => $user[0]['username'],
                'name' => $user[0]['name'],
                'number' => $user[0]['number'],
                'role' => $user[0]['role'],
                'picture' => $user[0]['picture'],
                'gender' => $user[0]['gender'],
                'fbLongAccessToken' => $user[0]['fbLongAccessToken']
            )
        ];
        $out->write(json_encode($outputJson));
    }
    return $response->withStatus($status);
});

/*User Do Connect:
Input:
    Facebook short access token from client
Output:
    User Object
*/
$app->post("/user/do/connect", function (Request $request, Response $response) {
    global $config;
    $status = OK;
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
            $fbResponse = $fb->get('/me?fields=id,name,gender,picture{url},groups{id}', $fbAccessToken);
            $me = $fbResponse->getGraphUser();
            $fbLongAccessToken = $fbResponse->getAccessToken();
            $username = $me->getId();
            $name = $me->getName();
            $picture = $me->getPicture()->getUrl();
            $gender = $me->getGender();
            $groups = $me->getField('groups');

            /*Set session duration between client and server*/
            $jwtDuration = 10000;
            $jwtData = [
                'username' => $username
            ];
            $accessToken = jwt($jwtData, 0, $jwtDuration);
            try {
                $db = new DbHandler();
                $query = file_get_contents("Restaurant-API/database/sql/user/get.sql");
                $user = $db->mysqli_prepared_query($query, "s", array($username));
                $user = empty($user) ? 0 : $user[0];
                if ($user) {
                    $query = file_get_contents("Restaurant-API/database/sql/user/update.sql");
                    $result = $db->mysqli_prepared_query($query, "sssss", array($name, $picture, $fbLongAccessToken, $accessToken, $username));
                    if (!empty($result) && $result[0] > 0) {
                        $outputJson = [
                            'user' => array(
                                'accessToken' => $accessToken,
                                'isNew' => false,
                                'username' => $username,
                                'name' => $name,
                                'number' => $user['number'],
                                'role' => $user['role'],
                                'picture' => $picture,
                                'gender' => $gender,
                                'fbLongAccessToken' => $user['fbLongAccessToken']
                            )
                        ];
                    } else {
                        $status = INTERNAL_SERVER_ERROR;
                        $outputJson = handleError("User not updated!", "Database", $status);
                    }
                    $out->write(json_encode($outputJson));
                } else {

                    /*User is new, register user with fb credentials*/
                    $query = file_get_contents("Restaurant-API/database/sql/user/set.sql");
                    $result = $db->mysqli_prepared_query($query, "ssssss", array($username, $name, $picture, $gender, $fbLongAccessToken, $accessToken));

                    if (!empty($result) && $result[0] > 0) {
                        $outputJson = [
                            'user' => array(
                                'accessToken' => $accessToken,
                                'isNew' => true,
                                'username' => $username,
                                'name' => $name,
                                'number' => null,
                                'role' => 'V',
                                'picture' => $picture,
                                'gender' => $gender,
                                'fbLongAccessToken' => $fbLongAccessToken
                            )
                        ];
                    } else {
                        $status = INTERNAL_SERVER_ERROR;
                        $outputJson = handleError("User not inserted!", "Database", $status);
                    }

                    $out->write(json_encode($outputJson));
                }
            } catch (Exception $e) {
                $status = INTERNAL_SERVER_ERROR;
                $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
            }
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $out->write(json_encode(handleError($e->getMessage(), "Facebook Graph", $e->getCode())));
            $status = UNAUTHORIZED;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            $out->write(json_encode(handleError($e->getMessage(), "Facebook SDK", $e->getCode())));
            $status = UNAUTHORIZED;
        }
    } else {
        $status = BAD_REQUEST;
        $out->write(json_encode(handleError("Bad Request", "API", $status)));
    }
    return $response->withStatus($status);
});

/*Deauthorize user from Facebook & delete your application account.*/
$app->post("/user/do/delink", function (Request $request, Response $response) {
    global $config;

    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $status = authStatus($request, $response, $tokenData, $user);
    $username = $user[0]['username'];
    $fbLongAccessToken = $user[0]['fbLongAccessToken'];
    if ($status == OK) {
        if ($fbLongAccessToken) {
            $fb = new \Facebook\Facebook([
                'app_id' => $config->get('fbApp')->get('id'),
                'app_secret' => $config->get('fbApp')->get('secret'),
                'default_graph_version' => $config->get('fbApp')->get('graph_version')
            ]);
            try {
                $fb->sendRequest("DELETE", 'me/permissions', [], $fbLongAccessToken, null, null);
                try {
                    $db = new DbHandler();
                    /*Delete user*/
                    $query = file_get_contents("Restaurant-API/database/sql/user/delete.sql");
                    $result = $db->mysqli_prepared_query($query, "s", array($username));
                    $outputJson = [
                        'fbDelinking' => true,
                        'userDeleted' => $result[0]
                    ];
                    $out->write(json_encode($outputJson));
                } catch (Exception $e) {
                    $status = INTERNAL_SERVER_ERROR;
                    $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
                }
            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                $out->write(json_encode(handleError($e->getMessage(), "Facebook Graph", $e->getCode())));
                $status = UNAUTHORIZED;
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                $out->write(json_encode(handleError($e->getMessage(), "Facebook SDK", $e->getCode())));
                $status = UNAUTHORIZED;
            }
        } else {
            $status = 404;      // Not Found
            //Facebook long access token does not exists.
            $out->write(json_encode(handleError("Facebook long access token does not exists.", "Facebook long access token", $status)));
        }
    }
    return $response->withStatus($status);
});

/*User insert number*/
$app->post("/user/do/insert/number", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $status = authStatus($request, $response, $tokenData, $user);
    if ($status == OK) {

        $username = $user[0]['username'];
        $userNumber = $user[0]['number'];

        $post = json_decode($request->getBody(), true);
        $newNumber = isset($post['newNumber']) ? $post['newNumber'] : 0;
        if(!empty($newNumber)){
            $newNumber = base64_decode($newNumber);
            if($newNumber !== false) {
                if (empty($userNumber)) {
                    try {
                        $db = new DbHandler();
                        /*Update user number and role*/
                        $query = file_get_contents("Restaurant-API/database/sql/user/update_number.sql");
                        $result = $db->mysqli_prepared_query($query, "sss", array($newNumber, 'B', $username));
                        if (!empty($result) && $result[0] > 0) {
                            $outputJson = [
                                'user' => array(
                                    'accessToken' => $user[0]['accessToken'],
                                    'isNew' => false,
                                    'username' => $user[0]['username'],
                                    'name' => $user[0]['name'],
                                    'number' => $newNumber,
                                    'role' => 'B',
                                    'picture' => $user[0]['picture'],
                                    'gender' => $user[0]['gender'],
                                    'fbLongAccessToken' => $user[0]['fbLongAccessToken']
                                )
                            ];
                            $out->write(json_encode($outputJson));
                        }
                        else{
                            $status = INTERNAL_SERVER_ERROR;
                            $out->write(json_encode(handleError('The card number insertion failed.', "Database", $status)));
                        }
                    } catch (Exception $e) {
                        $status = INTERNAL_SERVER_ERROR;
                        $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
                    }
                } else {
                    $status = FORBIDDEN;
                    $out->write(json_encode(handleError("The number can be changed only once.", "Number", $status)));
                }
            }
            else{
                $status = BAD_REQUEST;
                $out->write(json_encode(handleError("Invalid number.", "Number", $status)));
            }
        }
        else{
            $status = BAD_REQUEST;
            $out->write(json_encode(handleError("The new card number has not provided.", "Number", $status)));
        }
    }
    return $response->withStatus($status);
});

/*This route is temporary, only for debugging!*/
$app->get("/user/token/data", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $status = authStatus($request, $response, $jwtData, $user);
    if ($status == OK) {
        $out->write(json_encode(['tokenData' => $jwtData]));
    }
    return $response->withStatus($status);
});
