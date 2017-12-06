<?php 
/**
 * PHP加密解密
 * @paramString$key关键字
 * @paramStringString$string加密的字符串
 * @paramStringStringString$decrypt0表示加密，1表示解密
 */
if (!function_exists('encryptDecrypt')) {
    function encryptDecrypt($key, $string, $decrypt){ 
        if($decrypt){ 
            $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode
                ($string), MCRYPT_MODE_CBC, md5(md5($key))), "12"); 
            return $decrypted; 
        }
        else{ 
            $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key),
                $string, MCRYPT_MODE_CBC, md5(md5($key)))); 
            return $encrypted; 
        } 
    }

}

