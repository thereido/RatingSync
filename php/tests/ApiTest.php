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
}

?>
