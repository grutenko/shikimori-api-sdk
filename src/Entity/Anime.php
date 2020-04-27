<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Grutenko\Shikimori\Entity;


use Cartalyst\Collections\Collection;
use DateTime;
use Exception;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7;

/**
 * @property mixed|null image
 * @property mixed|null url
 * @property mixed|null aired_on
 * @property mixed|null released_on
 * @property mixed|null status
 * @property mixed|null updated_at
 * @property mixed|null episodes
 * @property mixed|null episodes_aired
 * @property mixed|null next_episode_at
 * @property mixed|null genres
 * @property mixed|null studios
 * @property mixed|null videos
 * @property mixed|null user_rate
 * @property mixed|null name
 * @property mixed|null id
 */
class Anime extends Entity
{
    /**
     * @var bool
     */
    private $detail;

    /**
     * Anime constructor.
     * @param array $data
     * @param bool $isDetail
     */
    public function __construct(array $data, bool $isDetail = false)
    {
        parent::__construct($data);
        $this->detail = $isDetail;
    }

    /**
     * @return bool
     */
    public function isDetail(): bool
    {
        return $this->detail;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return "https://shikimori.one" . $this->url;
    }

    /**
     * @return int|null
     */
    public function getEpisodesAiredCount(): ?int
    {
        if( !$this->isDetail() ) {
            return null;
        }

        switch($this->status) {
            case 'released': return $this->getEpisodesCount(); break;
            case 'ongoing': return $this->episodes_aired; break;
            default: return 0;
        }
    }

    /**
     * @return int|null
     */
    public function getEpisodesCount(): ?int
    {
        if( !$this->isDetail() ) {
            return null;
        }

        return $this->episodes;
    }

    /**
     * @return DateTime|null
     */
    public function getAiredOn(): ?DateTime
    {
        if( !$this->isDetail() ) {
            return null;
        }

        try {
            return new DateTime($this->aired_on);
        } catch(Exception $e)
        {
            return null;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getReleasedOn(): ?DateTime
    {
        if( !$this->isDetail() || $this->status != 'released') {
            return null;
        }

        try {
            return new DateTime($this->released_on);
        } catch(Exception $e)
        {
            return null;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        if( !$this->isDetail() ) {
            return null;
        }

        try {
            return new DateTime($this->updated_at);
        } catch(Exception $e)
        {
            return null;
        }
    }

    /**
     * @return DateTime|null
     */
    public function nextEpisodeDate(): ?DateTime
    {
        if( !$this->isDetail() ) {
            return null;
        }

        try {
            return new DateTime($this->next_episode_at);
        } catch(Exception $e)
        {
            return null;
        }
    }

    /**
     * Вернет true если у аниме есть постер с таким размером.
     * Доступные размеры:
     * - original
     * - preview
     * - x96
     * - x48
     *
     * @param string $size
     * @return bool
     */
    public function hasPoster( string $size )
    {
        return isset( $this->image[$size] );
    }

    /**
     * Вернет поток с постером. Если постера нет или ссылка сломана вернет null.
     *
     * @param string $size
     * @return StreamInterface|null
     */
    public function getPoster( string $size = 'original' )
    {
        if( null == ($url = $this->getPosterUrl($size)) ) {
            return null;
        }
        if( substr(get_headers($url)[0], 9, 3) != 200 ) {
            return null;
        }
        return Psr7\stream_for(fopen($url, 'r'));
    }

    /**
     * @param string $size
     * @return string|null
     */
    public function getPosterFilename(string $size = 'original'): ?string
    {
        if( null == ($url = $this->getPosterUrl($size)) ) {
            return null;
        }

        $arUrl = explode('/', parse_url($url, PHP_URL_PATH));
        return array_pop($arUrl);
    }

    /**
     * @param string $size
     * @return string|null
     */
    public function getPosterUrl(string $size = 'original')
    {
        if( !isset($this->image) || count($this->image) == 0 ) {
            return null;
        }
        if(!$this->hasPoster($size)) {
            $size = array_keys($this->image)[0];
        }
        return "https://shikimori.one" . $this->image[$size];
    }

    /**
     * @return Collection<Genre>|null
     */
    public function getGenres(): ?Collection
    {
        if( !$this->isDetail() ) {
            return null;
        }
        return $this->wrapArray(Genre::class, $this->genres);
    }

    /**
     * @return Collection<Studio>|null
     */
    public function getStudios(): ?Collection
    {
        if( !$this->isDetail() ) {
            return null;
        }
        return $this->wrapArray(Studio::class, $this->studios);
    }

    /**
     * @return Collection<Video>|null
     */
    public function getVideos(): ?Collection
    {
        if( !$this->isDetail() ) {
            return null;
        }
        return $this->wrapArray(Video::class, $this->videos);
    }

    /**
     * @return Collection<Screenshot>|null
     */
    public function getScreenshots(): ?Collection
    {
        if( !$this->isDetail() ) {
            return null;
        }
        return $this->wrapArray(Screenshot::class, $this->videos);
    }

    /**
     * @return bool
     */
    public function existRate(): bool
    {
        return isset($this->user_rate) && $this->user_rate != null;
    }

    /**
     * @return Rate|null
     */
    public function getRate(): ?Rate
    {
        if( !$this->existRate() ) {
            return null;
        }

        return new Rate($this->user_rate);
    }

    /**
     * @param string $className
     * @param array $data
     * @return Collection
     */
    private function wrapArray(string $className, array $data): Collection
    {
        if( !is_array($data) ) {
            return new Collection();
        }

        $arResult = array_map(function($item) use($className) {
            return new $className($item);
        }, $data);

        return new Collection($arResult);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "[{$this->id}] {$this->name}\r\n";
    }
}