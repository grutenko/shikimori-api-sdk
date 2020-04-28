<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Grutenko\Shikimori;

use Grutenko\Shikimori\Helper\OAuth2Helper;
use Grutenko\Shikimori\Mapper\AnimeMapper;
use Grutenko\Shikimori\Mapper\UserMapper;
use InvalidArgumentException;

/**
 * Class Sdk
 * @package Grutenko\Shikimori
 */
final class Sdk
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $appName;

    /**
     * @param array $config
     * @param array $token
     * @param callable $onRefresh
     * @return Sdk
     */
    public static function createWithOauth(array $config, array $token, callable $onRefresh): Sdk
    {
        $sdk = new self($config);
        $sdk->useOauthToken($token, $onRefresh);

        return $sdk;
    }

    /**
     * Sdk constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        if( !$this->checkConfig($config) ) {
            throw new InvalidArgumentException('Please, set all required params: client_id, client_secret');
        }

        list(
            'app_name'      => $this->appName,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret
            ) = $this->setDefaults($config);

        $this->api = new Api($this->appName);
    }

    /**
     * @return bool
     */
    public function isUseOauthToken()
    {
        return $this->api->tokenIsSet();
    }

    /**
     * @param array $config
     * @return bool
     */
    private function checkConfig(array $config): bool
    {
        return isset($config['client_id']) && isset($config['client_secret']);
    }

    /**
     * @param array $config
     * @return array
     */
    private function setDefaults(array $config)
    {
        $defaults = [
            'app_name'  => 'App'
        ];
        return array_merge($defaults, $config);
    }

    /**
     * @param array $token
     * @param callable $onRefresh
     */
    public function useOauthToken(array $token, callable $onRefresh = null)
    {
        $this->api->useOauth($this->auth(), $token, $onRefresh);
    }

    /**
     * @return OAuth2Helper
     */
    public function auth(): OAuth2Helper
    {
        return new OAuth2Helper($this->clientId, $this->clientSecret);
    }

    /**
     * @return AnimeMapper
     */
    public function anime(): AnimeMapper
    {
        return new AnimeMapper($this->api);
    }

    /**
     * @return UserMapper
     */
    public function user(): UserMapper
    {
        return new UserMapper($this->api);
    }
}