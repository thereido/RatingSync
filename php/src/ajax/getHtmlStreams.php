<?php
namespace RatingSync;

function getHtmlStreams($film) {
    $streamsHtml = "";
    $streams = $film->getStreams();
    foreach ($streams as $stream) {
        $url = $stream->getUrl();
        if (!empty($url)) {
            $link = "<a href='$url' target='_blank'>".$stream->getProviderName()."</a>";
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