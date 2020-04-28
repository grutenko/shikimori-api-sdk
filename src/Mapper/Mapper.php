<?php


namespace Grutenko\Shikimori\Mapper;


use Grutenko\Shikimori\Api;

/**
 * Class Mapper
 * @package Grutenko\Shikimori\Mapper
 * @author Alexey Fedorenko
 */
abstract class Mapper
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * Mapper constructor.
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }
}