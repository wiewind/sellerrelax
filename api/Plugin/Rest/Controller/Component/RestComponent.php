<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 28.06.2018
 * Time: 10:49
 */
class RestComponent extends Component
{
    var $components = array('MySession', 'MyCookie');

    function callAPI ($method, $path, $data = false)
    {
        $url = Configure::read('system.rest.url') . $path;

        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data) {
                    if (strpos($url, '?') === false) {
                        $url = sprintf("%s?%s", $url, http_build_query($data));
                    } else { // for param "with" -- in url ---  Example: ?with[]=addresses&with[]=orderItems.variation
                        $url = sprintf("%s&%s", $url, http_build_query($data));
                    }
                }
        }

        $authorization ='Bearer';
        if ($path !== 'rest/login') {
            $token = $this->getToken();
            $authorization .= " ".$token;
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER,['Authorization: ' . $authorization]);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    function login () {
        $rest = $this->post('rest/login', [
            'username' => Configure::read('system.rest.username'),
            'password' => Configure::read('system.rest.password')
        ]);

        if ($rest) {
            $rest = json_decode($rest);

            $now = date('Y-m-d H:i:s');
            $model = ClassRegistry::init('RestToken');

            $model->save([
                'token_type' => $rest->token_type,
                'expires_in' => $rest->expires_in,
                'access_token' => $rest->access_token,
                'refresh_token' => $rest->refresh_token,
                'created' => $now
            ]);
        }
    }

    function getToken () {
        $model = ClassRegistry::init('RestToken');
        $token = $model->find('first', [
            'order' => 'created desc'
        ]);
        $token_created = ($token) ? $token['RestToken']['created'] : '';
        if (!$token_created || strtotime(date('Y-m-d H:i:s'))-strtotime($token_created) >= (86400-(60*60*2))) {
            $this->login();
            return $this->getToken ();
        }
        return $token['RestToken']['access_token'];
    }

    public function get($path, $params = []) {
        return $this->callAPI('GET', $path, $params);
    }
    public function post($path, $params = []) {
        return $this->callAPI('POST', $path, $params);
    }
    public function put($path, $params = []) {
        return $this->callAPI('PUT', $path, $params);
    }
    public function delete($path, $params = []) {
        return $this->callAPI('DELETE', $path, $params);
    }
}