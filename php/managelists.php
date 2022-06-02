<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$filmId = array_value_by_key("id", $_GET);
$newList = array_value_by_key("nl", $_GET);
$listnames = null;
$film = null;
$filmlistsHeader = "";
$filmlistSelectOptions = "<option>---</option>";
$displayNewListInput = "hidden";
$displayAddButton = "";
$filmHtml = "";

if (empty($pageNum)) {
    $pageNum = 1;
}

if ($newList != 1) {
    $newList = 0;
}

if (!empty($username)) {
    $listnames = Filmlist::getUserListsFromDbByParent($username, false);
    if (empty($listname) && !empty($filmId)) {
        $film = Film::getFilmFromDb($filmId, $username);
        
        $filmHtml .= '<input type="checkbox" class="form-check-input" id="filmlist-add-this" checked>';
        $filmHtml .= '<label class="form-check-label" for="filmlist-add-this">Add the film to this new list?</label>';
        $filmHtml .= "<input id='filmlist-filmid' value='$filmId' hidden></input>";
        if (!empty($film)) {
            $title = $film->getTitle();
            if ($film->getContentType() == Film::CONTENT_TV_EPISODE) {
                $title = $film->getEpisodeTitle();
            }
            $filmHtml .= '<div>';
            $filmHtml .= '  <img class="suggestion-poster rounded" src="'.$film->getImage().'">';
            $filmHtml .= '  <small>'. $title . ' (' . $film->getYear() . ')</small>';
            $filmHtml .= '</div>';
        }
    }

    $filmlistsHeaderText = "Lists";

    // New List input will be hidden unless "nl=1"
    if ($newList == 1) {
        $displayNewListInput = "";
        $displayAddButton = "hidden";
        $filmlistsHeaderText = "New List";
        $filmlistSelectOptions .= getHtmlFilmlistSelectOptions($listnames);
    }
}

$pageHeader = getPageHeader(true, $listnames);
$pageFooter = getPageFooter();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages(); ?>
    <title><?php echo Constants::SITE_NAME; ?> <?php echo $filmlistsHeaderText ?></title>
    <link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <script src="../js/managelistsPage.js"></script>
</head>

<body>

<div class="container">

    <?php echo $pageHeader; ?>

    <div class='card mt-3'>
        <div class="card-body">
            <h2><?php echo $filmlistsHeaderText; ?></h2>
        </div>
    </div>

    <div><p><span id="debug"></span></p></div>
    
    <div class="panel-body" <?php echo $displayNewListInput; ?>>
    <div class="row">
        <div class="col-auto">
            <form onsubmit="return createFilmlist()">
                <div class="form-group">
                    <label for="filmlist-listname">New list</label>
                    <input type="text" class="form-control" id="filmlist-listname">
                </div>
                <div class="form-group">
                    <label for="filmlist-parent">Parent List <small>(Optional)</small></label>
                    <select class="form-control" id="filmlist-parent" aria-describedby="parentHelp"><?php echo $filmlistSelectOptions; ?></select>
                    <small id="parentHelp" class="form-text rs-text-muted">You may choose to put the new list into a parent list.</small>
                </div>
                <div class="form-group form-check">
                    <?php echo $filmHtml; ?>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
    <span id="filmlist-create-result"></span>
    </div>

    <div class="mt-3 row mx-0" <?php echo $displayAddButton ?>>
        <div class="col-auto ml-auto">
            <a href="/php/managelists.php?nl=1"><button class="btn btn-primary fas fa-plus fa-xs"></button></a>
        </div>
    </div>

    <div id="filmlists" class="mt-1"></div>

    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-modal-label">Delete List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div>Delete "<span id="delete-modal-listname"></span>" and all of its sub-lists?</div>
                    <div>This cannot be undone.</div>
                    <input type="text" id="delete-listname" hidden>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="delete-modal-submit" type="button" class="btn btn-primary" onClick="deleteFilmlist()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-fail-modal" tabindex="-1" role="dialog" aria-labelledby="delete-fail-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-modal-label">Delete List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Something went wrong. Unable to delete the list.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rename-modal" tabindex="-1" role="dialog" aria-labelledby="rename-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rename-modal-label">Rename List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="text" id="rename-new-listname">
                    <input type="text" id="rename-old-listname" hidden>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="rename-modal-submit" type="button" class="btn btn-primary" onClick="renameFilmlist()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rename-fail-modal" tabindex="-1" role="dialog" aria-labelledby="rename-fail-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rename-modal-label">Rename List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Something went wrong. Unable to rename the list.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

  <?php echo $pageFooter; ?>
</div>

<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.ManageLists;
    var contextData = JSON.parse('{"filmlists":[]}');
    if (<?php echo $newList; ?> != 1) {
         getFilmlists();
    }

    $('#delete-modal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget) // Button that triggered the modal
      var listname = button.data('listname')
      var modal = $(this)
      modal.find('#delete-modal-listname').text(listname)
      modal.find('#delete-listname').val(listname);
    })

    $('#rename-modal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var listname = button.data('listname');
    var modal = $(this);
    modal.find('#rename-old-listname').val(listname);
    modal.find('#rename-new-listname').val(listname);
    })
</script>
          
</body>
</html>
