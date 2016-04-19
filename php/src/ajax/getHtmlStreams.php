<?php
namespace RatingSync;

function getHtmlStreams($film) {
    $streamsHtml = "";
    $streams = $film->getStreams();
    while (list($sourceName, $stream) = each($streams)) {
        $streamUrl = $stream["url"];
        if (!empty($streamUrl)) {
            $streamsHtml .= "  <div class='stream'>\n";
            $streamsHtml .= "    <a href='$streamUrl' target='_blank'>\n";
            $streamsHtml .= "      <div class='stream-icon icon-$sourceName' title='Watch on $sourceName'></div></a>\n";
            $streamsHtml .= "    </a>";
            $streamsHtml .= "  </div>\n";
        }
    }

    $response  = "<div class='streams'>\n";
    $response .=    $streamsHtml;
    $response .= "</div>\n";
    
    return $response;
}

?>