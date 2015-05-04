<?php
/**
 * Jinni PHPUnit
 */
namespace RatingSync;

require_once "Jinni.php";

const TEST_USERNAME = "testratingsync";

class JinniTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers            \RatingSync\Jinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        new Jinni(null);
    }

    /**
     * @covers            \RatingSync\Jinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        new Jinni("");
    }

    /**
     * @covers \RatingSync\Jinni::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $jinni = new Jinni(TEST_USERNAME);
        return $jinni;
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatings()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings();
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsCount()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings();
        $this->assertCount(21, $films);
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsLimitPages()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings(1);
        $this->assertCount(20, $films);
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsBeginPage()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings(null, 2);
        $this->assertEquals("The Shawshank Redemption", $films[0]->getTitle());
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatingsLimitPages
     * @depends testGetRatingsBeginPage
     */
    public function testGetRatingsDetails()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings(1, 1, true);
        $film = $films[0];
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(), 'Image link');
        $this->assertEquals("Jennifer Lee", $film->getDirector(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'URL Name');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
    }

    /**
     * @covers \RatingSync\Jinni::getSearchSuggestions
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchSuggestions()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getSearchSuggestions("Shawshank");
        $titles = array();
        foreach ($films as $film) {
            $titles[] = $film->getTitle();
        }
        $this->assertTrue(in_array("The Shawshank Redemption", $titles));
    }
}

?>
