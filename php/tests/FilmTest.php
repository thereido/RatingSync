<?php
/**
 * Film PHPUnit
 */
namespace RatingSync;

require_once "Film.php";
require_once "Rating.php";
require_once "HttpJinni.php";

class FilmTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \RatingSync\Film::__construct
     */
    public function testObjectCanBeConstructedFromHttp()
    {
        $film = new Film(new HttpJinni("username"));
        return $film;
    }

    /**
     * @covers \RatingSync\Film::validContentType
     */
    public function testValidContentTypeTrue()
    {
        $this->assertTrue(Film::validContentType(Film::CONTENT_FILM), Film::CONTENT_FILM . " should be valid");
        $this->assertFalse(Film::validContentType("Bad_Type"), "Bad_Type should be invalid");
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testUrlNameCannotBeSetWithInvalidSource()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setUrlName("url_name", "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setUrlName(null, Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setUrlName("", Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithNonEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setUrlName("url_name", Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testUrlNameCannotBeGottenWithInvalidSource()
    {
        $film = new Film(new HttpJinni("username"));
        $film->getUrlName("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithNonEmpty
     */
    public function testGetUrlName()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setUrlName("url_name", Rating::SOURCE_IMDB);
        $this->assertEquals("url_name", $film->getUrlName(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetUrlNameNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $this->assertNull($film->getUrlName(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithNull
     */
    public function testGetNullUrlName()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setUrlName(null, Rating::SOURCE_IMDB);
        $this->assertNull($film->getUrlName(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithEmpty
     */
    public function testSetUrlNameWithEmptySetsToNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setUrlName("", Rating::SOURCE_IMDB);
        $this->assertNull($film->getUrlName(Rating::SOURCE_IMDB), "Setting empty URL name should be set to null");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithInvalidSource()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating(null, "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithNullRatingNullSource()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating(null, "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithString()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating("Bad_Arg", Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithNumber()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating(7, Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating(null, Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating("", Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRating()
    {
        $film = new Film(new HttpJinni("username"));
        $rating = new Rating(Rating::SOURCE_IMDB);
        $film->setRating($rating, Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithNoSource()
    {
        $film = new Film(new HttpJinni("username"));
        $rating = new Rating(Rating::SOURCE_IMDB);
        $film->setRating($rating);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \Exception
     */
    public function testSetRatingWithIncompatibleSource()
    {
        $film = new Film(new HttpJinni("username"));
        $rating = new Rating(Rating::SOURCE_IMDB);
        $film->setRating($rating, Rating::SOURCE_JINNI);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRating
     */
    public function testGetRating()
    {
        $film = new Film(new HttpJinni("username"));
        $rating = new Rating(Rating::SOURCE_IMDB);
        $rating->setYourScore(6);
        $film->setRating($rating);
        $this->assertEquals(6, $film->getRating(Rating::SOURCE_IMDB)->getYourScore());
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRating
     * @depends testGetRating
     */
    public function testGetRatingWithMultipleRatings()
    {
        $film = new Film(new HttpJinni("username"));
        $ratingJinni = new Rating(Rating::SOURCE_JINNI);
        $ratingImdb = new Rating(Rating::SOURCE_IMDB);
        $ratingJinni->setYourScore(7);
        $ratingImdb->setYourScore(6);
        $film->setRating($ratingJinni);
        $film->setRating($ratingImdb);
        $this->assertEquals(6, $film->getRating(Rating::SOURCE_IMDB)->getYourScore());
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetRatingNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $rating = $film->getRating(Rating::SOURCE_IMDB);
        $this->assertEquals(Rating::SOURCE_IMDB, $rating->getSource());
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRatingWithNull
     */
    public function testGetRatingWasSetNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating(null, Rating::SOURCE_IMDB);
        $this->assertNull($film->getRating(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRatingWithEmpty
     */
    public function testGetRatingWasSetEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setRating("", Rating::SOURCE_IMDB);
        $this->assertNull($film->getRating(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingWithInvalidSource()
    {
        $film = new Film(new HttpJinni("username"));
        $film->getRating("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithInvalidSource()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYourScore("your_score", "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithBadArg()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYourScore(6.5, Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYourScoreWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYourScore(null, Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYourScore("", Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYourScore()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYourScore(7, Rating::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYourScore
     */
    public function testGetYourScore()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYourScore(7, Rating::SOURCE_IMDB);
        $this->assertEquals(7, $film->getYourScore(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetYourScoreNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $this->assertNull($film->getYourScore(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYourScoreWithNull
     */
    public function testGetYourScoreWasSetNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYourScore(null, Rating::SOURCE_IMDB);
        $this->assertNull($film->getYourScore(Rating::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetYourScoreWithInvalidSource()
    {
        $film = new Film(new HttpJinni("username"));
        $film->getYourScore("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitleWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setTitle(null);
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitleWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setTitle("");
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitle()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setTitle("New_Title");
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitle
     */
    public function testGetTitle()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setTitle("New_Title");
        $this->assertEquals("New_Title", $film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetTitleNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $this->assertNull($film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitleWithNull
     */
    public function testGetTitleWasSetNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setTitle(null);
        $this->assertNull($film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitleWithEmpty
     */
    public function testGetTitleWasSetEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setTitle("");
        $this->assertEquals("", $film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYearWithBadArgFloat()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear(1999.5);
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYearWithBadArgStringCastToInt()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear("1999.5");
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear(null);
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear("");
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearInt()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear(1942);
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearString()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear("1942");
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearInt
     */
    public function testGetYear()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear(1942);
        $this->assertEquals(1942, $film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearString
     */
    public function testGetYearSetFromString()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear("1942");
        $this->assertEquals(1942, $film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetYearNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $this->assertNull($film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearWithNull
     */
    public function testGetYearAfterYearWasSetNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear(null);
        $this->assertNull($film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearWithEmpty
     */
    public function testGetYearWasSetEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setYear("");
        $this->assertNull($film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetContentTypeWithBadArg()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setContentType("Bad_ContentType");
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentTypeWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setContentType(null);
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentTypeWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setContentType("");
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentType()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setContentType(Film::CONTENT_FILM);
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentType
     */
    public function testGetContentType()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setContentType(Film::CONTENT_FILM);
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetContentTypeNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $this->assertNull($film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentTypeWithNull
     */
    public function testGetContentTypeWasSetNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setContentType(null);
        $this->assertNull($film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentTypeWithEmpty
     */
    public function testGetContentTypeWasSetEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setContentType("");
        $this->assertNull($film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setImage(null);
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setImage("");
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImage()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setImage("http://example.com/example.jpg");
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImage
     */
    public function testGetImage()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setImage("http://example.com/example.jpg");
        $this->assertEquals("http://example.com/example.jpg", $film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetImageNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $this->assertNull($film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithNull
     */
    public function testGetImageWasSetNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setImage(null);
        $this->assertNull($film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithEmpty
     */
    public function testGetImageWasSetEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setImage("");
        $this->assertEquals("", $film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddGenreWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre(null);
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddGenreWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenre()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreAddSecondGenre()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreDuplicate()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Comedy");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreMultiWithDuplicate()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Comedy");
    }    

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testGetGenres()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreAddSecondGenre
     */
    public function testGetGenresTwoGenres()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $this->assertEquals(array('Comedy', 'Horror'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreDuplicate
     */
    public function testGetGenresDuplicate()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreMultiWithDuplicate
     */
    public function testGetGenresMultiWithDuplicate()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy', 'Horror'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testGetGenresThreeGenres()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertEquals(array('Comedy', 'Horror', 'Drama'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::removeGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveGenre()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeGenre("Horror");
        $this->assertEquals(array('Comedy', 'Drama'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::removeGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveGenreWithMissingGenre()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeGenre("Sci-Fi");
        $this->assertEquals(array('Comedy', 'Horror', 'Drama'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::removeAllGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveAllGenres()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeAllGenres();
        $this->assertEmpty($film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testIsGenreTrue()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertTrue($film->isGenre("Horror"));
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testIsGenreFalse()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertFalse($film->isGenre("Sci-Fi"));
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::removeAllGenres
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testRemoveAllGenres
     * @depends testIsGenreTrue
     */
    public function testRemoveAllGenresThenAddOne()
    {
        $film = new Film(new HttpJinni("username"));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeAllGenres();
        $film->addGenre("Comedy");
        $this->assertTrue($film->isGenre("Comedy"));
    }

    /**
     * @covers  \RatingSync\Film::setDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetDirectorWithNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setDirector(null);
    }

    /**
     * @covers  \RatingSync\Film::setDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetDirectorWithEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setDirector("");
    }

    /**
     * @covers  \RatingSync\Film::setDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetDirector()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setDirector("Howard Hawks");
    }

    /**
     * @covers  \RatingSync\Film::setDirector
     * @covers  \RatingSync\Film::getDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetDirector
     */
    public function testGetDirector()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setDirector("Howard Hawks");
        $this->assertEquals("Howard Hawks", $film->getDirector());
    }

    /**
     * @covers  \RatingSync\Film::getDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetDirectorNeverSet()
    {
        $film = new Film(new HttpJinni("username"));
        $this->assertNull($film->getDirector());
    }

    /**
     * @covers  \RatingSync\Film::getDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetDirectorWithNull
     */
    public function testGetDirectorWasSetNull()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setDirector(null);
        $this->assertNull($film->getDirector());
    }

    /**
     * @covers  \RatingSync\Film::getDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetDirectorWithEmpty
     */
    public function testGetDirectorWasSetEmpty()
    {
        $film = new Film(new HttpJinni("username"));
        $film->setDirector("");
        $this->assertEquals("", $film->getDirector());
    }
}

?>
