<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Grutenko\Shikimori\Test\Entity;

use DateTime;
use Exception;
use Grutenko\Shikimori\Entity\Anime;
use PHPUnit\Framework\TestCase;

class AnimeTest extends TestCase
{
    /**
     *
     */
    public function testSuccessGetUrl()
    {
        $anime = new Anime([
            'url' => "/animes/10582-astarotte-no-omocha-ex"
        ]);
        $this->assertEquals('https://shikimori.one/animes/10582-astarotte-no-omocha-ex', $anime->getUrl());
    }

    /**
     * @dataProvider episodesCountDataProvider
     * @param array $arAnime
     * @param int $episodesAired
     */
    public function testSuccessGetEpisodesAiredCount($arAnime, $episodesAired)
    {
        $anime = new Anime($arAnime, true);
        $this->assertEquals($episodesAired, $anime->getEpisodesAiredCount());
    }

    /**
     * @return array[]
     */
    public function episodesCountDataProvider()
    {
        return [
            [[
                'status' => 'released',
                'episodes' => 26,
                'episodes_aired' => 0
            ], 26],
            [[
                'status' => 'ongoing',
                'episodes' => 12,
                'episodes_aired' => 6
            ], 6],
            [[
                'status' => 'anons',
                'episodes' => 24,
                'episodes_aired' => 1
            ], 0]
        ];
    }

    /**
     *
     */
    public function testSuccessGetAiredDate()
    {
        $anime = new Anime([
            'aired_on' => '1998-04-03'
        ], true);
        $airedDate = $anime->getAiredOn();
        $expectAiredDate = new DateTime('1998-04-03');

        $this->assertEquals($expectAiredDate->format('U'), $airedDate->format('U'));
    }

    /**
     * @dataProvider releasedOnProvider
     * @param array $arAnime
     * @param array $expectedDate
     * @throws Exception
     */
    public function testGetReleasedOn($arAnime, $expectedDate)
    {
        $anime = new Anime($arAnime, true);

        if($expectedDate == null) {
            $this->assertNull($anime->getReleasedOn());
        } else
        {
            $releasedDate = $anime->getReleasedOn();
            $expectReleasedDate = new DateTime($expectedDate);

            $this->assertEquals($expectReleasedDate->format('U'), $releasedDate->format('U'));
        }
    }

    public function releasedOnProvider()
    {
        return [
            [[
                'status' => 'released',
                'released_on' => '1999-04-24'
            ], '1999-04-24'],
            [[
                'status' => 'ongoing',
                'released_on' => '1999-04-24'
            ], null],
            [[
                'status' => 'anons',
                'released_on' => '1999-04-24'
            ], null]
        ];
    }
}
