<?php

namespace Helpers;

final class Fcm
{
    /**
     * aes加密
     * @param $secret
     * @param $data
     * @param string $cipher
     * @return array
     */
    public static function aesEncode($secret, $data, $cipher = "aes-128-gcm")
    {
        if (!is_string($data)) {
            $data = json_encode($data, 256);
        }

        $key = hex2bin($secret);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypt = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

        return [
            'data' => base64_encode(($iv . $encrypt . $tag))
        ];
    }

    /**
     * 生成签名
     * @param $secret
     * @param $sign_data
     * @param $body
     * @return string
     */
    public static function createSign($secret, $sign_data, $body = '')
    {
        unset($sign_data['Content-Type'], $sign_data['sign']);

        if (!empty($body) and !is_string($body)) {
            $body = json_encode($body, 256);
        }

        ksort($sign_data);
        $sign_data = http_build_query($sign_data, '', '');
        $sign_data = str_replace('=', '', $sign_data);

        $sign_str = $secret . $sign_data . $body;

        return hash('sha256', $sign_str);
    }

}
