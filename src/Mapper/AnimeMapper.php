<?php
namespace Grutenko\Shikimori\Mapper;

use Cartalyst\Collections\Collection;
use Grutenko\Shikimori\Entity\Anime;
use Grutenko\Shikimori\Exception\NotFoundException;

class AnimeMapper extends Mapper
{
    /**
     * @param int $id
     * @return Anime|null
     */
    public function find($id): ?Anime
    {
        $arAnime = $this->api->fetch("animes/{$id}");
        if($this->api->lastRequestInfo['http_code'] != 200) {
            return null;
        }

        return new Anime($arAnime, true);
    }

    /**
     * Возвращает данные аниме или выбрасывает исключение, если такое не найдено.
     *
     * @param $id
     * @return Anime
     *
     * @throws NotFoundException
     */
    public function findOrFail($id): Anime
    {
        $anime = $this->find($id);
        if(null == $anime) {
            throw new NotFoundException("Anime #{$id} not found.");
        }

        return $anime;
    }

    /**
     * Возвращает список аниме по параметрам.
     *
     * @param array $params
     * @return Collection|null
     */
    public function list(array $params = []): ?Collection
    {
        foreach($params as $key => &$value) {
            if( is_array($value) ) {
                $value = implode(',', $value);
            }
        }

        $animes = $this->api->fetch('animes', $params);
        if($this->api->lastRequestInfo['http_code'] != 200) {
            return null;
        }

        $collection = new Collection();
        foreach($animes as $anime) {
            $collection->push(new Anime($anime, false));
        }

        return $collection;
    }

    /**
     * Запрашивает список аниме по параметрам до тех пор пока данные не закончатся
     * и для каждой страницы запускает $handler.
     * Если нужно прекратить запросы страниц $handler должен вернуть false.
     *
     * @param array $params
     * @param int $pages
     * @return null|Collection
     */
    public function paginate(array $params, $pages = -1): ?Collection
    {
        $animes = new Collection();

        for($page = 1; ; $page++) {
            $params['page'] = $page;
            $chunk = $this->list($params);

            if( null == $chunk || 0 == $chunk->count() || $page > $pages) {
                break;
            }

            foreach($chunk->toArray() as $anime) {
                $animes->push($anime);
            }
        }

        return $animes;
    }

    /**
     * @param array $params
     * @param callable $handler
     */
    public function paginateCallback(array $params, callable $handler)
    {
        for($page = 1; ; $page++) {

            $params['page'] = $page;
            $chunk = $this->list($params);

            if( null == $chunk || 0 == $chunk->count() || false == $handler($chunk, $page)) {
                break;
            }
        }
    }
}