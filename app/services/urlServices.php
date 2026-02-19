<?php

class urlService {

    public static function generateCode($length = 6) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL)
            && preg_match('/^https?:\/\//', $url);
    }
}
