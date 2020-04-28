<?php


namespace Grutenko\Shikimori\Mapper;


use RuntimeException;

class UserMapper extends Mapper
{
    /**
     * @return array
     * @throws RuntimeException
     */
    public function current(): array
    {
        if(!$this->api->tokenIsSet()) {
            throw new RuntimeException('Этот метод может быть вызван только после упешной OAuth2.0 вторизации.');
        }

        return $this->api->fetch('/users/whoami');
    }
}