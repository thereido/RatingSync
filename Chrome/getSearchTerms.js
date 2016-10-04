
var url = window.location.href;
var sourceName = getSourceName(url);

if (!sourceName) {
    chrome.runtime.sendMessage( {action: "unsupportedUrl", url: url} );
}
else if (sourceName == "RS") {
    chrome.runtime.sendMessage( {action: "unsupportedUrl", url: url} );
}
else {
    var searchTerms = getSearchTerms(sourceName, document);
    chrome.runtime.sendMessage({
        action: "setSearchTerms",
        search: searchTerms
    });
}

function getSourceName(url) {
    if (!url) {
        return;
    } else if (-1 < url.indexOf("//localhost")) {
        return "RS";
    } else if (-1 < url.indexOf("imdb")) {
        return "IM";
    } else if (-1 < url.indexOf("netflix")) {
        return "NF";
    } else if (-1 < url.indexOf("rottentomatoes")) {
        return "RT";
    } else if (-1 < url.indexOf("xfinity")) {
        return "XF";
    } else if (-1 < url.indexOf("hulu")) {
        return "H";
    }

    return;
}

function getSearchTerms(source, document_root) {

    var searchTerms;
    var uniqueName;
    var uniqueEpisode;
    var uniqueAlt;
    var title;
    var year;
    var parentYear;
    var season;
    var episodeNumber;
    var episodeTitle;
    var streamUrl;
    var contentType;

    var url = window.location.href;
	if (source == "IM") {
		var index = url.indexOf("/tt");
        if (-1 < index) {
            var indexBegin = index + 1;
            var indexEnd = url.indexOf("/", indexBegin);
            uniqueName = url.substring(indexBegin, indexEnd);
        }
        var titleBlockEl = document_root.getElementsByClassName("title_block")[0];
        var titleWrapperEl = titleBlockEl.getElementsByClassName("title_wrapper")[0];
        var titleWrapperText = titleWrapperEl.textContent.trim();
        var titleParentElements = titleBlockEl.getElementsByClassName("titleParent");
        if (titleParentElements.length == 1) {
            contentType = "TvEpisode";
            if (/(.*)\n/.test(titleWrapperText)) {
                episodeTitle = /(.*)\n/.exec(titleWrapperText)[1];
                episodeTitle = episodeTitle.trim();
            }
            var titleParentEl = titleParentElements[0];
            title = titleParentEl.getElementsByTagName("A")[0].textContent;
            parentText = titleParentEl.getElementsByTagName("SPAN")[0].textContent;
            parentYear = parentText.substring(1, 5);
            var titleText = document_root.getElementsByTagName("TITLE")[0].textContent;
            var reYear = new RegExp("Episode ([0-9]{4})");
            if (reYear.test(titleText)) {
                year = reYear.exec(titleText)[1];
            }
            var navPanelEl = document_root.getElementsByClassName("button_panel navigation_panel")[0];
            var navText = navPanelEl.textContent.trim();
            var reSeason = new RegExp("Season ([1-9][0-9]*)");
            if (reSeason.test(navText)) {
                season = reSeason.exec(navText)[1];
            }
            var reEpisodeNum = new RegExp("Episode ([1-9][0-9]*)");
            if (reEpisodeNum.test(navText)) {
                episodeNumber = reEpisodeNum.exec(navText)[1];
            }
        } else {
		    index = titleWrapperText.indexOf("TV Series (");
            if (-1 < index) {
                contentType = "TvSeries";
                if (/(.*)\n/.test(titleWrapperText)) {
                    title = /(.*)\n/.exec(titleWrapperText)[1];
                    title = title.trim();
                }
                var reYear = new RegExp("([0-9]{4}).+$");
                if (reYear.test(titleWrapperText)) {
                    year = reYear.exec(titleWrapperText)[1];
                }
            } else {
                contentType = "FeatureFilm";
                var titleText = titleWrapperEl.getElementsByTagName("H1")[0].textContent;
                if (/(.*)\s+.[0-9]{4}./.test(titleText)) {
                    title = /(.*)\s+.[0-9]{4}./.exec(titleText)[1];
                }
                var reYear = new RegExp("([0-9]{4}).+$");
                if (reYear.test(titleText)) {
                    year = reYear.exec(titleText)[1];
                }
            }
        }
	}
    else if (source == "NF") {        
        var regex = new RegExp("jbv=([0-9]+)");
        if (regex.test(url)) {
            uniqueName = regex.exec(url)[1];
        }
        titleEl = document_root.getElementsByClassName("title has-jawbone-nav-transition")[0];
        yearEl = document_root.getElementsByClassName("year")[0];
        durationEl = document_root.getElementsByClassName("duration")[0];
        if (titleEl) {
            titleHtml = titleEl.innerHTML;
            regex = new RegExp("<img alt=\"([^\"]+)");
            if (regex.test(titleHtml)) {
                title = regex.exec(titleHtml)[1];
            } else {
                title = titleHtml;
            }
        }
        if (yearEl) {
            year = yearEl.innerHTML;
        }
        if (durationEl) {
            regex = new RegExp("[0-9]+ (Season)[s]?<");
            if (regex.test(durationEl.innerHTML)) {
                contentType = "TvSeries";
            }
        }
	}
    else if (source == "RT") {
        var titleElement;
        var titleRegex;
        var yearRegex;
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/m/");
        if (-1 < index) {
            indexBegin = index + 3;
            indexEnd = url.indexOf("/", indexBegin);
            titleElement = document_root.getElementById("movie-title");
            titleRegex = new RegExp("[ ]?([^<]+)<span");
        } else {
            index = url.indexOf("/tv/");
            if (-1 < index) {
                indexBegin = index + 4;
                indexEnd = url.indexOf("/", indexBegin);
                titleElement = document_root.getElementById("super_series_header");
                titleRegex = new RegExp("<h1>[ ]?([^<]+)<span");
            }
        }

        if (indexBegin != -1) {
		    uniqueName = url.substring(indexBegin, indexEnd);
        }

        var html = "";
        if (titleElement) {
            html = titleElement.innerHTML;
        }
        if (titleRegex && titleRegex.test(html)) {
            title = titleRegex.exec(html)[1];
        }
        yearRegex = new RegExp(">..([0-9][0-9][0-9][0-9])");
        if (yearRegex && yearRegex.test(html)) {
            year = yearRegex.exec(html)[1];
        }
	}
    else if (source == "XF") {
        // Find uniqueName from URL
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/entity/");
        if (-1 < index) {
            indexBegin = index + 8;
            if (indexBegin != -1) {
                uniqueName = url.substring(indexBegin);
            }
        }
        // Find title from html
        var elementsByClass = document_root.getElementsByClassName("style-scope tv-page-entity");
        var foundTitle = false;
        for (titleIndex = 0; titleIndex < elementsByClass.length && !foundTitle; titleIndex++) {
          var elByClass = elementsByClass[titleIndex];
          if (elByClass.tagName == "H1" && elByClass.id == "pageHeading") {
              title = elByClass.innerHTML;
              foundTitle = true;
          }
        }
        // Find year from html
        var badgeElements = document_root.getElementsByTagName("tv-badge-airing-type");
        if (badgeElements.length == 1) {
            var badgeEl = badgeElements[0];
            if (badgeEl) {
                var summaryEl = badgeEl.parentElement;
                var reYear = new RegExp("([0-9]{4})");
                if (reYear.test(summaryEl.textContent)) {
                    year = reYear.exec(summaryEl.textContent)[1];
                }
            }
        }
        // Find TV Season and Episode
        var episodesTabSelected = false;
        var selectedSeasonEl;
        var selectedEpisodeEl;
        var episodesEl = document_root.getElementById("tabEpisodes");
        if (episodesEl && !episodesEl.hasAttribute("hidden")) {
            if (-1 < episodesEl.getAttribute("class").indexOf("iron-selected")) {
                // The 'Episodes' tab is selected
                episodesTabSelected = true;
            }
        }
        if (episodesTabSelected) {
            // Find a selected season
            var seasonRowElements = document_root.getElementsByTagName("tv-list-row-group");
            var foundSeason = false;
            for (seasonIndex = 0; seasonIndex < seasonRowElements.length && !foundSeason; seasonIndex++) {
                if (seasonRowElements[seasonIndex].hasAttribute("opened")) {
                    foundSeason = true;
                    selectedSeasonEl = seasonRowElements[seasonIndex];
                    var seasonNumEl = selectedSeasonEl.getElementsByClassName("style-scope tv-list-row-group")[0];
                    var seasonNumText = seasonNumEl.textContent;
                    var reSeasonNum = new RegExp("Season ([0-9]+)");
                    if (reSeasonNum.test(seasonNumText)) {
                        season = reSeasonNum.exec(seasonNumText)[1];
                    }
                }
            }
        }
        if (selectedSeasonEl) {
            // Find a selected episode
            var episodeRowElements = selectedSeasonEl.getElementsByTagName("tv-list-row-collapsible");
            var foundEpisode = false;
            for (episodeIndex = 0; episodeIndex < episodeRowElements.length && !foundEpisode; episodeIndex++) {
                if (episodeRowElements[episodeIndex].hasAttribute("opened")) {
                    foundEpisode = true;
                    selectedEpisodeEl = episodeRowElements[episodeIndex];
                    var selEpH1El = selectedEpisodeEl.getElementsByTagName("H1")[0];
                    // Find selected episodeNumber
                    var selEpNumEl = selEpH1El.getElementsByClassName("episode-info style-scope tv-entity-episodes")[0];
                    var epNumText = selEpNumEl.textContent;
                    var reEpisodeNum = new RegExp("Ep([0-9]+)");
                    if (reEpisodeNum.test(epNumText)) {
                        episodeNumber = reEpisodeNum.exec(epNumText)[1];
                    }
                    // Find selected episodeTitle
                    var selEpTitleEl = selEpH1El.getElementsByTagName("SPAN")[2];
                    episodeTitle = selEpTitleEl.textContent;
                }
            }
        }
        // Find contentType
        contentType = "FeatureFilm";
        if (episodesEl && !episodesEl.hasAttribute("hidden")) {
            contentType = "TvSeries";
            if (season) {
                contentType = "TvSeason";
                if (episodeNumber) {
                    contentType = "TvEpisode";
                }
            }
        }
        // Find streamUrl
        streamUrl = url;
	}
    else if (source == "H") {
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/watch/");
        if (-1 < index) {
            indexBegin = index + 7;
            indexEnd = url.indexOf("/", indexBegin);
        }

        if (indexBegin != -1) {
            if (indexBegin < indexEnd) {
                uniqueName = url.substring(indexBegin, indexEnd);
            } else {
                uniqueName = url.substring(indexBegin);
            }
        }

        var titleEl = document_root.getElementsByClassName("episode-title")[0];
        if (titleEl) {
            var html = titleEl.innerHTML;
            var reTitle = new RegExp("(.*) .[0-9]{4}.");
            if (reTitle.test(html)) {
                title = reTitle.exec(html)[1];
            }
            var reYear = new RegExp("([0-9]{4}).$");
            if (reYear.test(html)) {
                year = reYear.exec(html)[1];
            }
        }
	}
    
    var text = '';
    text = text + '{"uniqueName": "' + uniqueName + '", ';
    text = text + '"uniqueEpisode": "' + uniqueEpisode + '", ';
    text = text + '"uniqueAlt": "' + uniqueAlt + '", ';
    text = text + '"source": "' + source + '", ';
    text = text + '"title": "' + title + '", ';
    text = text + '"year": "' + year + '", ';
    text = text + '"parentYear": "' + parentYear + '", ';
    text = text + '"season": "' + season + '", ';
    text = text + '"episodeNumber": "' + episodeNumber + '", ';
    text = text + '"episodeTitle": "' + episodeTitle + '", ';
    text = text + '"streamUrl": "' + streamUrl + '", ';
    text = text + '"contentType": "' + contentType + '"}';
    var json = JSON.parse(text);
    return json;
}