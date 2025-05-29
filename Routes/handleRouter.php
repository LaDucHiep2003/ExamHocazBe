<?php
class HandleRoute
{
    public static function handleroute($routers, $methodRequest, $UriRequest)
    {
        if ($methodRequest === 'OPTIONS') {
            if (isset($routers['OPTIONS'])) {
                $routers['OPTIONS']();
            }
        }
        if (isset($routers[$methodRequest])) {
            foreach ($routers[$methodRequest] as $router => $function) {
                if (preg_match("#^$router$#", $UriRequest, $value)) {
                    // ví dụ http là : API_EXAMPLE/exams?id=1 thì array_shift($value)=1;
                    array_shift($value);
                    return call_user_func_array($function, $value);
                }
            }
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['message' => 'Not found']);
        }
    }
}