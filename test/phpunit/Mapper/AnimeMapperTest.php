<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Grutenko\Shikimori\Test\Mapper;

use Cartalyst\Collections\Collection;
use Grutenko\Shikimori\Api;
use Grutenko\Shikimori\Entity\Anime;
use Grutenko\Shikimori\Exception\NotFoundException;
use Grutenko\Shikimori\Mapper\AnimeMapper;
use PHPUnit\Framework\TestCase;

class AnimeMapperTest extends TestCase
{

    /**
     * @var AnimeMapper
     */
    private $animeMapper;

    public function setUp(): void
    {
        $this->animeMapper = new AnimeMapper(new Api('Test app'));
    }

    /**
     *
     */
    public function testSuccessGetById()
    {
        $cowboyBebopId = 1;
        $anime = $this->animeMapper->find($cowboyBebopId);

        $this->assertInstanceOf(Anime::class, $anime);
        $this->assertEquals($anime->id, $cowboyBebopId);
    }

    /**
     *
     */
    public function testNotFoundGetById()
    {
        $notFoundId = -1;
        $arData = $this->animeMapper->find($notFoundId);

        $this->assertNull($arData);
    }

    /**
     *
     */
    public function testNotFoundFindOrFail()
    {
        $notFoundId = -1;

        $this->expectException(NotFoundException::class);
        $this->animeMapper->findOrFail($notFoundId);
    }

    /**
     *
     */
    public function testSuccessFindOrFail()
    {
        $cowboyBebopId = 1;

        try {
            $anime = $this->animeMapper->findOrFail($cowboyBebopId);
            $this->assertInstanceOf(Anime::class, $anime);
            $this->assertEquals($anime->id, $cowboyBebopId);
        } catch(NotFoundException $e)
        {
            $this->fail('NotFoundException is thrown at 1:Cowboy Bebop anime.');
        }
    }

    /**
     *
     */
    public function testSuccessGetListWithoutFilter()
    {
        $animes = $this->animeMapper->list();
        $this->assertInstanceOf(Collection::class, $animes);
    }

    /**
     *
     */
    public function testSuccessPaginateWithoutFilter()
    {
        $animes = $this->animeMapper->paginate(['limit' => 50], 3);

        $this->assertEquals(150, $animes->count());
        $this->assertTrue( isset($animes[0]->id) );
    }
}
