<?php
namespace RatingSync;

function getHtmlStreams($film) {
    $title = $film->getTitle();
    $year = $film->getYear();
    $filmId = $film->getId();
    $rsId = $film->getUniqueName(Constants::SOURCE_RATINGSYNC);
    $streamsHtml = "";
    $streams = $film->getStreams();
    foreach (Source::validStreamProvidersBackground() as $sourceName) {
        $uniqueName = $film->getUniqueName($sourceName);
        $uniqueEpisode = $film->getUniqueEpisode($sourceName);
        $uniqueAlt = $film->getUniqueAlt($sourceName);
        $stream = array_value_by_key($sourceName, $streams);
        $streamUrl = array_value_by_key("url", $stream);
        $streamDate = array_value_by_key("date", $stream);

        $streamsHtml .= "    <div class='stream' id='$sourceName-$rsId' data-film-id='$filmId' data-source-name='$sourceName' data-title='$title' data-year='$year' data-uniquename='$uniqueName' data-unique-episode='$uniqueEpisode' data-unique-alt='$uniqueAlt' data-stream-date='$streamDate'>\n";
        if (!empty($streamUrl)) {
            $streamsHtml .= "      <a href='$streamUrl' target='_blank'>\n";
            $streamsHtml .= "        <div class='stream-icon icon-$sourceName' title='Watch on $sourceName'></div></a>\n";
            $streamsHtml .= "      </a>\n";
        }
        $streamsHtml .= "    </div>\n";
    }

    $response  = "  <div class='streams'>\n";
    $response .=      $streamsHtml;
    $response .= "  </div>\n";
    
    return $response;
}

?>