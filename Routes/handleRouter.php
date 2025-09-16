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
            foreach ($routers[$methodRequest] as $router => $config) {
                if (preg_match("#^$router$#", $UriRequest, $value)) {
                    array_shift($value);

                    // Nếu route có middleware
                    if (is_array($config)) {
                        [$function, $middleware] = $config;

                        if ($middleware && !call_user_func($middleware)) {
                            http_response_code(401);
                            echo json_encode(['message' => 'Unauthorized']);
                            return;
                        }

                        return call_user_func_array($function, $value);
                    } else {
                        return call_user_func_array($config, $value);
                    }
                }
            }
            http_response_code(404);
            echo json_encode(['message' => 'Not found']);
        }
    }
}
