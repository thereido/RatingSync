<?php
/**
 * api.php PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "ajax" . DIRECTORY_SEPARATOR . "api.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";

require_once "10DatabaseTest.php";

class ApiTest extends RatingSyncTestCase
{
    public function setUp()
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testApi_getSearchFilm()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        $uniqueName = "tt0457433";
        $uniqueEpisode = NULL;
        $uniqueAlt = NULL;
        $title = NULL;
        $year = NULL;
        $season = NULL;
        $episodeNumber = NULL;
        $episodeTitle = NULL;
        /*RT $contentType = "FeatureFilm";*/ $contentType = NULL;
        $sourceName = "IM";

        $searchTerms = array();
        $searchTerms['q'] = $uniqueName;
        $searchTerms['ue'] = $uniqueEpisode;
        $searchTerms['ua'] = $uniqueAlt;
        $searchTerms['t'] = $title;
        $searchTerms['y'] = $year;
        $searchTerms['s'] = $season;
        $searchTerms['en'] = $episodeNumber;
        $searchTerms['et'] = $episodeTitle;
        $searchTerms['ct'] = $contentType;
        $searchTerms['source'] = $sourceName;

        // Test
        $responseJson = api_getSearchFilm(Constants::TEST_RATINGSYNC_USERNAME, $searchTerms);
/*RT
echo "\ndump response\n";
var_dump($responseJson);
echo "\nprint response\n";
print $responseJson;
echo "\necho response\n$responseJson\n";
*RT*/

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
/*RT*
echo "\ndump decode\n";
var_dump($obj);
*RT*/
    }

    public function testApi_getFilm()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        $uniqueName = "tt1277737";
        $title = "The Stoning of Soraya M.";
        $rsonly = "0";

        $get = array(); // HTML submit $_GET
        $get['imdb'] = $uniqueName;
        $get['rsonly'] = $rsonly;

        // Test
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $this->assertEquals($title, $obj->title);
        $db = getDatabase();
        $query = "SELECT title FROM film, film_source WHERE uniqueName='$uniqueName' AND source_name='IMDb' AND id=film_id";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There should be one result");
        $titleDb = $result->fetch_assoc()['title'];
        $this->assertEquals($title, $titleDb, "Title from the db should match '$title'");
    }

    public function testApi_getFilmFailure()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        $uniqueName = "tt0042897";

        $get = array(); // HTML submit $_GET
        $get['imdb'] = $uniqueName;

        // Test
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $this->assertEquals("false", $obj->Success);
    }
}

?>
