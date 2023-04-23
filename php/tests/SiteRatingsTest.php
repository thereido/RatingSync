<?php
/**
 * Site PHPUnit
 */
namespace RatingSync;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "SiteRatings.php";

require_once "SiteRatingsChild.php";
require_once "SiteTest.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

/**
 Suggested tests for a child class
   - All suggested tests from SiteTest
   - testGetRatingsUsernameWithNoMatch
   - testCacheRatingsPage
   - testGetRatings
   - testGetRatingsUsingCacheAlways
   - testGetRatingsUsingCacheNever
   - testGetRatingsUsingCacheWithRecentFiles
   - testGetRatingsUsingCacheWithOldFiles
   - testGetRatingsCount
   - testGetRatingsLimitPages
   - testGetRatingsBeginPage
   - testGetRatingsDetailsNoCache
   - testGetRatingsDetails
   - testExportRatingsXmlNoDetail
   - testExportRatingsXmlDetail
   - testGetRatingPageUrlWithArgsNull
   - testGetRatingPageUrlWithArgsEmpty
   - testGetRatingPageUrlWithPageIndexNull
   - testGetRatingPageUrlWithPageIndexEmpty
   - testGetRatingPageUrlWithPageIndexNotInt
   - testGetRatingPageUrl
   - testGetNextRatingPageNumberWithNull
   - testGetNextRatingPageNumberWithEmpty
   - testGetNextRatingPageNumberFirstPage
   - testGetNextRatingPageNumberLastPage
   - testFromExportFileToFilmObjectAndBackToXml
 */

class SiteRatingsTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testSiteRatings_NoTests()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>
