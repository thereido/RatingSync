<?php
namespace RatingSync;

function getHtmlStreams($film) {
    $streamsHtml = "";
    $streams = $film->getStreams();
    while (list($sourceName, $streamUrl) = each($streams)) {
        if (!empty($streamUrl)) {
            $link = "<a href='$streamUrl' target='_blank'>$sourceName</a>";
            $streamsHtml .= "<div class='stream'>\n";
            $streamsHtml .= "  <span>$link</span>\n";
            $streamsHtml .= "</div>\n";
        }
    }

    $response  = "<div class='streams'>\n";
    $response .=    $streamsHtml;
    $response .= "</div>\n";
    
    return $response;
}

?>