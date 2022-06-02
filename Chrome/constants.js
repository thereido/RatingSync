const IMDB_FILM_BASEURL = "http://www.imdb.com/title/";
const TMDB_FILM_BASEURL = "https://www.themoviedb.org/";

const CONTENT_FILM = "FeatureFilm";
const CONTENT_TV_SERIES = "TvSeries";
const CONTENT_TV_EPISODE = "TvEpisode";

const URL_FIND_TMDB = "https://api.themoviedb.org/3/find";

const SEARCH_URL = {
    OMBb: "https://private.omdbapi.com?json=1",
    TMDb: "https://api.themoviedb.org/3/search/multi?page=1"
};

const SITE_PAGE = {
    Detail: 'Detail',
    Edit: 'Edit',
    Ratings: "Ratings",
    Userlist: "Userlist",
    ManageLists: "ManageLists",
    Search: "Search",
    Export: "Export",
    Import: "Import"
};

const SOURCE_NAME = {
    RatingSync: 'RatingSync',
    Internal: 'RatingSync',
    IMDb: "IMDb",
    TMDb: "TMDb",
    OMDb: "OMDb"
}
