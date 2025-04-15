<?php
namespace RatingSync;

use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . "ExternalAdapterCsv.php";

class TmdbAdapter extends ExternalAdapterCsv
{
    // https://www.themoviedb.org/settings/import-list
    // Trakt_v2

    protected array $supportedExportFormats = [ExportFormat::TMDB_RATINGS];

    public function __construct( string $username, ExportFormat $format )
    {
        parent::__construct( username: $username, format: $format, className: __CLASS__ );
    }

    protected function getHeader(): string
    {
        return "rated_at,type,title,year,trakt_rating,trakt_id,imdb_id,tmdb_id,tvdb_id,url,released,season_number,episode_number,episode_title,episode_released,episode_trakt_rating,episode_trakt_id,episode_imdb_id,episode_tmdb_id,episode_tvdb_id,genres,rating";
    }

    protected function validateExternalFilm( Film $film ): array
    {
        return TmdbFilm::validateExternalFilm( $film );
    }

    /**
     * @throws Exception
     */
    protected function createExternalFilm( Film $film, Rating|null $earliestRating = null ): ExternalFilm
    {
        return new TmdbFilm( $film );
    }

}

class TmdbFilm extends ExternalFilm
{

    /**
     * @throws Exception
     */
    public function __construct( Film $film )
    {
        if ( $film->getUniqueName( source: Constants::SOURCE_TMDBAPI  ) == null ) {
            throw new Exception(ExportFormat::TMDB_RATINGS->toString() . " id must be provided");
        }

        $this->film = $film;
    }

    static public function validateExternalFilm( Film $film ): array
    {
        $problems = [];

        if ( $film->getUniqueName( source: Constants::SOURCE_TMDBAPI  ) == null ) {
            $problems[] = "Empty TMDb id";
        }

        return $problems;
    }

    public function ratingEntry( ?Rating $rating ): string
    {
        // Example from tmdb.org
        //
        // rated_at,type,title,year,trakt_rating,trakt_id,imdb_id,tmdb_id,tvdb_id,url,released,season_number,episode_number,episode_title,episode_released,episode_trakt_rating,episode_trakt_id,episode_imdb_id,episode_tmdb_id,episode_tvdb_id,genres,rating
        // 2018-08-24T13:48:52Z,movie,Guardians of the Galaxy,2014,8.34601,82405,tt2015381,118340,,https://trakt.tv/movies/guardians-of-the-galaxy-2014,2014-08-01,,,,,,,,,,"adventure,science-fiction,action",9
        // 2018-08-24T12:06:07Z,show,Yellowstone,2018,7.70732,126995,tt4236770,73586,341164,https://trakt.tv/shows/yellowstone-2018,2018-06-21,,,,,,,,,,"drama,western",8
        // 2018-08-24T00:19:16Z,movie,Skyscraper,2018,7.0518,293862,tt5758778,447200,,https://trakt.tv/movies/skyscraper-2018,2018-07-13,,,,,,,,,,"action,thriller,drama",7
        // 2018-08-22T23:10:09Z,movie,Ocean's Eight,2018,7.21611,247337,tt5164214,402900,,https://trakt.tv/movies/ocean-s-eight-2018,2018-06-08,,,,,,,,,,"thriller,crime,comedy,action",7

        $this->film->populateImdbIdToDb();

        $tmdbId         = $this->tmdbId();
        $imdbId         = $this->imdbId();
        $episodeTmdbId  = $this->episodeTmdbId();
        $episodeImdbId  = $this->episodeImdbId();
        $tmdbType       = $this->getExternalFilmType( $this->film->getContentType() );
        $seasonNum      = $this->film->getSeason();
        $episodeNum     = $this->film->getEpisodeNumber();
        $title          = $this->film->getTitle();
        $year           = $this->film->getYear();
        $episodeTitle   = $this->film->getEpisodeTitle();
        $score          = $rating?->getYourScore();
        $ratingAt       = $rating?->getYourRatingDate()?->format("Y-m-d\TH:i:s\Z");

        // All:  rated_at,type     ,title ,year ,trakt_rating,trakt_id,imdb_id,tmdb_id,tvdb_id,url,released,season_number,episode_number,episode_title,episode_released,episode_trakt_rating,episode_trakt_id,episode_imdb_id,episode_tmdb_id,episode_tvdb_id,genres,rating
        // Used: rated_at,type     ,title ,year ,____________,________,imdb_id,tmdb_id,_______,___,________,season_number,episode_number,episode_title,________________,____________________,________________,episode_imdb_id,episode_tmdb_id,_______________,______,rating
        return "$ratingAt,$tmdbType,$title,$year,,,$imdbId,$tmdbId,,,,$seasonNum,$episodeNum,$episodeTitle,,,,$episodeImdbId,$episodeTmdbId,,,$score";
    }

    public function filmEntry(): string|array
    {
        return $this->ratingEntry( rating: null );
    }

    private function getExternalFilmType( string $contentType ): string
    {
        return match ($contentType) {
            Film::CONTENT_FILM => "movie",
            Film::CONTENT_TV_SERIES => "show",
            Film::CONTENT_TV_EPISODE => "episode",
            default => "",
        };
    }

    private function tmdbId(): ?string
    {
        return self::getTmdbId( $this->film );
    }

    private function seriesTmdbId(): ?string
    {
        return self::getSeriesTmdbId($this->film);
    }

    private function episodeTmdbId(): ?string
    {
        return self::getEpisodeTmdbId($this->film);
    }

    private function imdbId(): ?string
    {
        $imdbId = $this->film->getUniqueName( source: Constants::SOURCE_IMDB );

        return match ($this->film->getContentType()) {
            Film::CONTENT_TV_EPISODE    => $this->seriesImdbId(),
            default                     => $imdbId,
        };
    }

    private function seriesImdbId(): ?string
    {
        $imdbId = $this->film->getUniqueName( source: Constants::SOURCE_IMDB );
        $parentImdbId   = $this->film->getParentUniqueName( source: Constants::SOURCE_IMDB );

        return match ($this->film->getContentType()) {
            Film::CONTENT_TV_SERIES => $imdbId,
            Film::CONTENT_TV_EPISODE => $parentImdbId,
            default => null,
        };
    }

    private function episodeImdbId(): ?string
    {
        return match ($this->film->getContentType()) {
            Film::CONTENT_TV_EPISODE => $this->film->getUniqueName( source: Constants::SOURCE_IMDB ),
            default => null,
        };
    }

    static public function getTmdbId( Film $film ): ?string
    {
        $internalTmdbId = $film->getUniqueName( source: Constants::SOURCE_TMDBAPI );

        return match ($film->getContentType()) {
            Film::CONTENT_TV_EPISODE    => self::getSeriesTmdbId($film),
            default                     => self::stripTmdbId( $internalTmdbId ),
        };
    }

    static public function getSeriesTmdbId(Film $film): ?string
    {
        $internalTmdbId = $film->getUniqueName( source: Constants::SOURCE_TMDBAPI );
        $parentTmdbId   = $film->getParentUniqueName( source: Constants::SOURCE_TMDBAPI );

        return match ($film->getContentType()) {
            Film::CONTENT_TV_SERIES     => self::stripTmdbId( $internalTmdbId ),
            Film::CONTENT_TV_EPISODE    => self::stripTmdbId( $parentTmdbId ),
            default => null,
        };
    }

    static public function getEpisodeTmdbId(Film $film): ?string
    {
        if ($film->getContentType() == Film::CONTENT_TV_EPISODE) {
            $internalTmdbId = $film->getUniqueName( source: Constants::SOURCE_TMDBAPI );
            return self::stripTmdbId( $internalTmdbId );
        }
        else {
            return null;
        }
    }

    static private function stripTmdbId( ?string $internalTmdbId ): string
    {
        if (empty($internalTmdbId)) {
            return "";
        }

        return substr($internalTmdbId, offset: 2);
    }

}
