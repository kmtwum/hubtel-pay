<?php

class Helper
{
    public static function postCall($payload, $key)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Basic " . base64_encode($key)
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_URL => "https://devp-reqsendmoney-230622-api.hubtel.com/request-money/" . $payload['mobileNumber'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            self::tail('POST ERR');
            self::tail($error);
        }
        return $response;
    }

    public static function tail($str)
    {
        @file_put_contents(__DIR__ . '/bro.txt', print_r($str, true) . "\r\n", FILE_APPEND | LOCK_EX);
    }

    public static function generateId($prefix): string
    {
        $date = new DateTime ();
        $stamp = $date->format('Y-m-d');
        return $prefix . str_replace('-', '', $stamp) . mt_rand(10000, 50000);
    }

}