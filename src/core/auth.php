<?php
namespace Ziki\Core;

use Ziki\Core\filesystem as FileSystem;

session_start();

class Auth {
    /**
     * This function will get the auth details from specified url
     */
    public static function getAuth($data, $role){
        $user['name'] = $data->name;
        $user['email'] = $data->email;
        $user['image'] = $data->image;
        $user['last_login'] = $data->updated_at;
        $user['role'] = $role;
        $user['login_token'] = md5($data->id.$data->name.$email);
        $_SESSION['login_user'] = $user;
        return true;
    }

    // Log in user check
    public function is_logged_in() {
        // Check if user session has been set
        if (isset($_SESSION['login_user']) && ($_SESSION['login_user']['login_token'] != '')) {
            return $_SESSION;
        }
    }

    // Log out user
    public function log_out() {
        // Destroy and unset active session
        session_destroy();
        unset($_SESSION['login_user']);
        return true;
    }

    public function validateAuth($params) {
        $auth_response =  array();
        $data =  explode(",", $params);
        $provider = $data[0];
        $token = $data[1];
        $ch = curl_init();
        //Set the URL that you want to GET by using the CURLOPT_URL option.
        curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/authcheck/{$provider}/{$token}");
        
        //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        //Execute the request.
        $result = curl_exec($ch);
        
        //Close the cURL handle.
        curl_close($ch);
        $res = json_decode($result);
        //var_dump($result); die();
        //Save User data to settings.json
        $dir = "./src/config/settings.json";
        $check_settings = FileSystem::read($dir);
        if(!$check_settings) {
            $json_user = FileSystem::write($dir, $result);
            if($json_user){
                $role = "admin";
                $auth =self::getAuth($res, $role);
                $auth_response = $auth;
            }
        }
        else{
            $check_prev = json_decode($check_settings);
            if($check_prev->email == $res->email){
                $role = "admin";
                $auth = self::getAuth($check_prev, $role);
                $auth_response = $auth;
            }
            else{
                $role = "guest";
                $auth =self::getAuth($res, $role);
                $auth_response = $auth;
            }
        }  
        return $auth_response;  
    }
}
