<?php
/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 01.12.2016
 * Time: 11:14
 */

App::uses('Component', 'Controller');

class LoginComponent extends Component {

    var $components = ['MySession', 'MyCookie', 'Rest'];

    public function checkLoginData ($username, $password) {
        if (!$username || !$password) {
            throw new Exception(__('Invalid username or password, please try again.' . $username));
        }

        if ($_SERVER['HTTP_HOST'] !== 'srx.local') {
            $resp = $this->Rest->login($username, $password);
            $rest = json_decode($resp);
            if (!isset($rest->access_token)) {
                throw new Exception(__('Invalid username or password, please try again2.'));
            }
        }

        $this->MySession->write('user', array(
            'username' => $username
        ));
    }

    public function checkLogin () {
        $return = $this->MySession->check('user.username');
        return $return;
    }

    public function logout () {
        $this->MySession->delete('user');
    }
}