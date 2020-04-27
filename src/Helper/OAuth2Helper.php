<?php


namespace Grutenko\Shikimori\Helper;

use RuntimeException;

/**
 * Class OAuth2Helper
 * @package Grutenko\Shikimori
 * @author Alexey Fedorenko
 */
class OAuth2Helper
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * OAuth2Helper constructor.
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Возвращает URL на который нужно отправить пользователя для подтверждения авторизации.
     *
     * @param string $redirectURL
     * @return string
     */
    public function generateAuthUrl( string $redirectURL ): string
    {
        return "https://shikimori.one/oauth/authorize?client_id={$this->clientId}&redirect_uri=".urlencode($redirectURL)
            ."&response_type=code&scope=";
    }

    /**
     * Возвращает массив с данными токена.
     *
     * @param string $code
     * @param string $redirectUri
     * @return array
     *
     * @throws RuntimeException
     */
    public function getAccessToken(string $code, string $redirectUri): array
    {
        return $this->send([
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri
        ]);
    }

    /**
     *  Возвращает обновленный токен.
     *
     * @param string $refreshToken
     * @return array
     */
    public function refreshToken( string $refreshToken): array
    {
        return $this->send([
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken
        ]);
    }

    /**
     * Отправляет запрос на $url с x-www-urlencoded параметрами $fields. Результат возвращает в виде
     * массива.
     *
     * @param array $fields
     * @return array
     */
    private function send(array $fields): array
    {
        $ch = curl_init('https://shikimori.one/oauth/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields)
        ]);
        $data = curl_exec($ch);

        if(curl_errno($ch) != 0) {
            throw new RuntimeException('CURL: '. curl_error($ch), curl_errno($ch));
        }

        $arData = json_decode($data, true);
        if(json_last_error() != 0) {
            throw new RuntimeException('JSON: '.json_last_error_msg(), json_last_error());
        }

        return $arData;
    }
}