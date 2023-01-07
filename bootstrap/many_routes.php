<?php

use App\Http\Request;
use App\Http\Response;
use App\Quicky;

$app = Quicky::create();
Quicky::session()->start();

$usedMethods = array("GET", "POST");
for ($i = 0; $i < 1000; $i++) {
    try {
        Quicky::route($usedMethods[random_int(0, 1)], "/" . uniqid(), function (Request $request, Response $response) use ($i) {
            $response->send("This is random route nr. %s", "$i");
        });
    } catch (Exception $e) {
    }
}

Quicky::route("GET", "/", function(Request $request, Response $response) {
   $router = Quicky::router();
   $router->dump();
});