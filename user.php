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
    $status = 200;      // OK
    try {
        $db = new DbHandler();
        $query = file_get_contents("Restaurant-API/database/sql/user/get/validate.sql");
        $user = $db->mysqli_prepared_query($query, "s", array($jwtData->username));
        if (empty($user)) {
            $status = 401;              // Unauthorized
            $out->write(json_encode(handleError("User Not Found!", "Database", $status)));
        }
    } catch (Exception $e) {
        $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
        $status = 500;                 // Internal Server Error
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
                return 401;                 // Unauthorized
            }
        } else {
            return 400;                     // Bad Request
        }
    } else {
        $out->write(json_encode(handleError('Token not found in request', "Json Web Token", 400)));
        return 400;                         // Bad Request
    }
}

/*User Do Connect:
Input:
    Facebook short access token from client
Output:
    Json Web Token
*/
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
            try {
                $db = new DbHandler();
                $query = file_get_contents("Restaurant-API/database/sql/user/get/user.sql");
                $user = $db->mysqli_prepared_query($query, "s", array($username));
                $user = empty($user) ? 0 : $user[0];
                if ($user) {
                    if ($name != $user['name'] || $picture != $user['picture']) {

                        /*TODO: Update user information eg name*/
                        $query = file_get_contents("Restaurant-API/database/sql/user/update/user.sql");
                        $result = $db->mysqli_prepared_query($query, "ssss", array($name, $picture, $username, $fbLongAccessToken));
                        $user['name'] = $name;
                        $user['picture'] = $picture;
                    }
                    $jwtData = [
                        'username' => $username
                    ];
                    $outputJson = [
                        'userIsNew' => false,
                        'jwt' => jwt($jwtData, 0, $jwtDuration)
                    ];
                    $out->write(json_encode($outputJson));
                } else {

                    /*User is new, register user with fb credentials*/
                    $query = file_get_contents("Restaurant-API/database/sql/user/set/user.sql");
                    $result = $db->mysqli_prepared_query($query, "sssss", array($username, $name, $picture, $gender, $fbLongAccessToken));

                    if (!empty($result) && $result[0] > 0) {
                        $jwtData = [
                            'username' => $username
                        ];
                        $outputJson = [
                            'userIsNew' => true,
                            'jwt' => jwt($jwtData, 0, $jwtDuration)
                        ];
                    } else {
                        $status = 500;
                        $outputJson = handleError("User not inserted!", "Database", $status);
                    }

                    $out->write(json_encode($outputJson));
                }
            } catch (Exception $e) {
                $status = 500;              // Internal Server Error
                $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
            }
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $out->write(json_encode(handleError($e->getMessage(), "Facebook Graph", $e->getCode())));
            $status = 401;      // Unauthorized
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            $out->write(json_encode(handleError($e->getMessage(), "Facebook SDK", $e->getCode())));
            $status = 401;      // Unauthorized
        }
    } else {
        $status = 400;                      // Bad Request
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
    if ($status == 200) {
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
                    $query = file_get_contents("Restaurant-API/database/sql/user/delete/delete.sql");
                    $result = $db->mysqli_prepared_query($query, "s", array($username));
                    $outputJson = [
                        'fbDelinking' => true,
                        'userDeleted' => $result[0]
                    ];
                    $out->write(json_encode($outputJson));
                } catch (Exception $e) {
                    $status = 500;   // Internal Server Error
                    $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
                }
            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                $out->write(json_encode(handleError($e->getMessage(), "Facebook Graph", $e->getCode())));
                $status = 401;      // Unauthorized
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                $out->write(json_encode(handleError($e->getMessage(), "Facebook SDK", $e->getCode())));
                $status = 401;      // Unauthorized
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
    if ($status == 200) {

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
                        $query = file_get_contents("Restaurant-API/database/sql/user/update/number.sql");
                        $result = $db->mysqli_prepared_query($query, "sss", array($newNumber, 'B', $username));
                        $outputJson = [
                            'userNumberChanged' => $result[0]
                        ];
                        $out->write(json_encode($outputJson));
                    } catch (Exception $e) {
                        $status = 500;   // Internal Server Error
                        $out->write(json_encode(handleError($e->getMessage(), "Database", $e->getCode())));
                    }
                } else {
                    $status = 401;   // Unauthorized
                    $out->write(json_encode(handleError("The number can be changed only once.", "Number", $status)));
                }
            }
            else{
                $status = 400;   // Bad request
                $out->write(json_encode(handleError("Invalid number", "Number", $status)));
            }
        }
        else{
            $status = 400;   // Bad request
            $out->write(json_encode(handleError("New number has not provided.", "Number", $status)));
        }
    }
    return $response->withStatus($status);
});

/*This route is temporary, only for debugging!*/
$app->get("/user/token/data", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $status = authStatus($request, $response, $jwtData, $user);
    if ($status == 200) {
        $out->write(json_encode(['tokenData' => $jwtData]));
    }
    return $response->withStatus($status);
});
