<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Grutenko\Shikimori\Test;

use Grutenko\Shikimori\Sdk;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SdkTest extends TestCase
{
    /**
     * @dataProvider successConfigDataProvider
     * @param array $config
     */
    public function testSuccessConfigClassInstance(array $config)
    {
        try {
            new Sdk($config);
        } catch (InvalidArgumentException $e)
        {
            $this->fail($e->getMessage());
        }
        $this->assertTrue(true);
    }

    /**
     * @dataProvider failConfigDataProvider
     * @param array $config
     */
    public function testFailConfigClassInstance(array $config)
    {
        $this->expectException(InvalidArgumentException::class);
        new Sdk($config);
    }

    /**
     * @depends testSuccessConfigClassInstance
     */
    public function testValidSetOauthToken()
    {
        $sdk = new Sdk([
           'app_name'       => 'Test app',
           'client_id'      => 'valid_client_id',
           'client_secret'  => 'valid_client_secret'
        ]);
        $sdk->useOauthToken([
            "access_token" => "1lS-nBxBYuq-UpKNO-ZoSJt-Ydqy6aNNAiuTB2Y9py8",
            "token_type" => "Bearer",
            "expires_in" => 86400,
            "refresh_token" => "EbFX9K9DEdM3TsNBkgVisP7Ct3sYxNKlmNQ2wJYKn10",
            "scope" => "user_rates comments topics",
            "created_at" => 1587974274
        ]);
        $this->assertTrue($sdk->isUseOauthToken(), 'SDK is not valid set oauth token.');
    }

    /**
     * @depends      testSuccessConfigClassInstance
     * @dataProvider failTokenDataProvider
     * @param array $config
     */
    public function testInvalidSetOauthToken(array $config)
    {
        $sdk = new Sdk([
            'app_name'       => 'Test app',
            'client_id'      => 'valid_client_id',
            'client_secret'  => 'valid_client_secret'
        ]);

        $this->expectException(InvalidArgumentException::class);
        $sdk->useOauthToken($config);
    }

    public function failTokenDataProvider()
    {
        return [
            [[
                "access_token" => "1lS-nBxBYuq-UpKNO-ZoSJt-Ydqy6aNNAiuTB2Y9py8",
                "expires_in" => 86400,
                "refresh_token" => "EbFX9K9DEdM3TsNBkgVisP7Ct3sYxNKlmNQ2wJYKn10",
            ]],
            [[
                "access_token" => "1lS-nBxBYuq-UpKNO-ZoSJt-Ydqy6aNNAiuTB2Y9py8",
                "expires_in" => 86400,
            ]],
            [[
                "expires_in" => 86400,
                "refresh_token" => "EbFX9K9DEdM3TsNBkgVisP7Ct3sYxNKlmNQ2wJYKn10"
            ]],
            [[
                "access_token" => "1lS-nBxBYuq-UpKNO-ZoSJt-Ydqy6aNNAiuTB2Y9py8",
            ]],
            [[
                "expires_in" => 86400
            ]],
            [[
                "refresh_token" => "EbFX9K9DEdM3TsNBkgVisP7Ct3sYxNKlmNQ2wJYKn10"
            ]],
            [[
                "created_at" => 1587974274
            ]],
        ];
    }

    /**
     * @return string[][][]
     */
    public function successConfigDataProvider()
    {
        return [
            [[
                'app_name'      => 'Test app name',
                'client_id'     => 'valid client id',
                'client_secret' => 'valid client secret'
            ]],
            [[
                'client_id'     => 'valid client id',
                'client_secret' => 'valid client secret'
            ]]
        ];
    }

    /**
     * @return array
     */
    public function failConfigDataProvider()
    {
        return [
            [[

            ]],
            [[
                'app_name' => 'Test app name'
            ]],
            [[
                'app_name'  => 'Test app name',
                'client_id' => 'valid client id'
            ]],
            [[
                'app_name'  => 'Test app name',
                'client_secret' => 'valid client secret'
            ]],
            [[
                'client_id' => 'valid client id'
            ]],
            [[
                'client_secret' => 'valid client secret'
            ]]
        ];
    }
}
