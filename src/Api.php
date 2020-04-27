<?php
namespace Grutenko\Shikimori;

use Grutenko\Shikimori\Helper\OAuth2Helper;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class Api
 * @package Grutenko\Shikimori
 * @author Alexey Fedorenko
 */
final class Api
{
    /**
     * @var string Базовый URL ля всех запросов к API.
     */
    const BASE_URL = 'https://shikimori.one/api/';

    /**
     * @var array|null
     */
    public $token = null;

    /**
     * @var string
     */
    private $appName;

    /**
     * @var array|null Информация о последнем запросе из curl_getinfo().
     */
    public $lastRequestInfo = null;

    /**
     * @var OAuth2Helper|null
     */
    private $oauthHelper = null;

    /**
     * @var array Хранит обработчик обновления токена. Массив содежрит один 0 или 1 элементов - функцию для запуска
     */
    private $onRefresh = [];

    /**
     * Api constructor.
     * @param string $appName
     */
    public function __construct(string $appName = 'App')
    {
        $this->appName = $appName;
    }

    /**
     * @return bool
     */
    public function tokenIsSet(): bool
    {
        return is_array($this->token);
    }

    /**
     * @param OAuth2Helper $helper
     * @param array $token
     * @param callable $onRefresh
     */
    public function useOauth(OAuth2Helper $helper, array $token, callable $onRefresh = null)
    {
        if(!$this->checkOauthTokenData($token)) {
            throw new InvalidArgumentException('Token data must have: access_token,expires_in,refresh_token,'
                .'created_at');
        }

        $this->oauthHelper = $helper;
        $this->token = $token;

        if($onRefresh == null) {
            $onRefresh = function($token) {};
        }
        $this->onRefresh = [ $onRefresh ];
    }

    /**
     * @param array $token
     * @return bool
     */
    private function checkOauthTokenData(array $token): bool
    {
        $require = [
            'access_token',
            'expires_in',
            'refresh_token',
            'created_at'
        ];
        return count( array_intersect($require, array_keys($token)) ) == count($require);
    }

    /**
     * обновляет токен и запускает необходимые обработчики
     *
     * @return void
     * @throws RuntimeException
     */
    private function refreshToken()
    {
        $token = $this->oauthHelper->refreshToken($this->token['refresh_token']);

        if( isset($token['error']) ) {
            throw new RuntimeException($token['error_description']);
        }

        $this->token = $token;
        $this->onRefresh[0]($this->token);
    }

    /**
     * @param string $path
     * @param array $params
     * @param string $method
     *
     * @return array
     * @throws RuntimeException
     */
    public function fetch(string $path, array $params = [], string $method = 'GET'): array
    {
        $arData = $this->send($path, $params, $method);

        if($this->tokenIsSet()) {
            if( isset($arData['error']) && $arData['error'] == 'invalid_token' ) {
                $this->refreshToken();
                return $this->send($path, $params, $method);
            }
        }

        return $arData;
    }

    /**
     * @param string $path
     * @param array $params
     * @param string $method
     * @return array
     * @throws RuntimeException
     */
    private function send(string $path, array $params = [], string $method = 'GET'): array
    {
        $arParams = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $this->appName,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ];

        if( $this->isSafe($method) ) {
            $arParams[CURLOPT_URL] = $this->prepareUrl($path, $params);
        } else {
            $arParams[CURLOPT_URL] = $this->prepareUrl($path);
            $arParams[CURLOPT_POSTFIELDS] = json_encode($params);
            $arParams[CURLOPT_HTTPHEADER][] = "X-HTTP-Method-Override: {$method}";
        }

        if($this->tokenIsSet()) {
            $arParams[CURLOPT_HTTPHEADER][] = "Authorization: Bearer {$this->token['access_token']}";
        }

        $ch = curl_init();
        curl_setopt_array($ch, $arParams);
        $data = curl_exec($ch);

        if(curl_errno($ch) != 0) {
            throw new RuntimeException('CURL: '. curl_error($ch), curl_errno($ch));
        }

        $arData = json_decode($data, true);
        if(json_last_error() != 0) {
            throw new RuntimeException('JSON: '.json_last_error_msg(), json_last_error());
        }

        $this->lastRequestInfo = curl_getinfo($ch);
        curl_close($ch);

        return $arData;
    }

    /**
     * Вернет true, если метод безопасный.
     *
     * @param string $method
     * @return bool
     */
    private function isSafe( string $method ): bool
    {
        return in_array($method, ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * Подготавливает URL для отправки запроса.
     *
     * @param $path
     * @param array $params
     * @return string
     */
    private function prepareUrl($path, $params = []): string
    {
        if( count($params) == 0) {
            return static::BASE_URL . trim($path, '/');
        }

        return static::BASE_URL . trim($path, '/') . '?' . http_build_query($params);
    }
}