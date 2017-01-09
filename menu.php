<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/*Take*/
$app->post("/menu", function (Request $request, Response $response) {
    $out = $response->getBody();
    $response = $response->withHeader('Content-type', 'application/json');
    $post = json_decode($request->getBody(), true);
    if (isset($post['meal']) && $post['meal'] != null) {
        $status = authStatus($request, $response, $tokenData, $user);
        if ($status == 200) {
            try {
                $db = new DbHandler();
                $query = file_get_contents("Restaurant-API/database/sql/menu/current_menu.sql");
                $menu = $db->mysqli_prepared_query($query, "s", array($post['meal']));
                $output = [
                    "menu" => array(
                        "meal" => empty($menu) ? null : $menu[0]['meal'],
                        "date" => empty($menu) ? null : $menu[0]['date']
                    )
                ];
                $out->write(json_encode($output, true));
            } catch (Exception $e) {
                $status = 500;                 // Internal Server Error
                $out->write(json_encode(handleError($e->getMessage(), "Database", $status)));
            }
        }
    } else {
        $status = 400;
        $out->write(json_encode(handleError('Bad Request', "HTTP", $status)));
    }


    return $response->withStatus($status);
});

