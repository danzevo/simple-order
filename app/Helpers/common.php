<?php

function enkrip($id)
{
    $encrypt_method = "AES-256-CBC";
    $secret_key = env("APP_SECRET_KEY");
    $secret_iv = env("APP_SECRET_IV");
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    $output = openssl_encrypt($id, $encrypt_method, $key, 0, $iv);
    $output = base64_encode($output);
    return $output;
}

function dekrip($id_enkrip)
{
    $encrypt_method = "AES-256-CBC";
    $secret_key = env("APP_SECRET_KEY");
    $secret_iv = env("APP_SECRET_IV");
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    $output = openssl_decrypt(base64_decode($id_enkrip), $encrypt_method, $key, 0, $iv);
    return $output;
}
