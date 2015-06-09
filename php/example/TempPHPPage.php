<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>PHP Scratch Pad</title>
    </head>
    <body>
        <h1>PHP Scratch Pad</h1>

<?php
/** File Comment */

/** cURL */ /*
$filename = "php/output/example_curl.html";
$filenameHeaders = "php/output/example_headers.html";

$ch = curl_init();
$url = "http://www.jinni.com/info/about.html";

$fp = fopen($filename, "w");
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);

curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
fclose($fp);

echo "<pre>";
echo "URL=$url\n";
echo "HTTP Code: " . $info['http_code'] . "\n";
echo "Result file: $filename\n";
echo "</pre>";

$ch = curl_init();
$url = "http://www.jinni.com/not_found.html";
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
echo "<pre>";
echo "URL=$url\n";
echo "HTTP Code: " . $info['http_code'] . "\n";
echo "</pre>";

$ch = curl_init();
$url = "http://www.jinni.com/user/---Username--No--Match---/ratings";
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
echo "<pre>";
echo "URL=$url\n";
echo "HTTP Code: " . $info['http_code'] . "\n";
echo "</pre>";

$ch = curl_init();
$url = "http://www.jinni.com/user/...";
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
echo "<pre>";
echo "URL=$url\n";
echo "HTTP Code: " . $info['http_code'] . "\n";
echo "</pre>";

$ch = curl_init();
$url = "http://www.not.a.real.domain.dlrmtyocksrnskgog.com";
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$return = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
echo "<pre>";
echo "URL=$url\n";
echo "HTTP Code: " . $info['http_code'] . "\n";
echo "Return: " . $return . "\n";
echo "</pre>";
*/

/* Files */ /*
$outputDir = __DIR__ . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR;
$filename = $outputDir . "debug_result.html";
$fp = fopen($filename, "w");
fwrite($fp, "<html><body><h1>Hello World!</pre></body></html>");
fclose($fp);
echo "<pre>$filename</pre>";
*/

/* Class */ /*
class FooClass {
    protected $bar;

    public function fxn()
    {
        echo $bar;
    }
}
$fc = new FooClass();
$fc->fxn();
*/

/* Dates */ /*
$date = date_create_from_format('n/j/y', '1/21/13');
echo "<pre>";
echo "Day " . $date->format("j") . "\n";
echo "Month " . $date->format("n") . "\n";
echo "Year " . $date->format("Y") . "\n";
echo "</pre>";
*/

/* Directory Paths and Magic Constants */
echo "<pre>";
echo "\n__DIR__: " . __DIR__;
echo "\n__LINE__: " . __LINE__;
echo "\n__FILE__: " . __FILE__;
echo "\n__FUNCTION__: " . __FUNCTION__;
echo "\n__CLASS__: " . __CLASS__;
echo "\n__TRAIT__: " . __TRAIT__;
echo "\n__METHOD__: " . __METHOD__;
echo "\n__NAMESPACE__: " . __NAMESPACE__;
echo "\nPHP_OS: " . PHP_OS;
echo "\nDIRECTORY_SEPARATOR: " . DIRECTORY_SEPARATOR;
echo "\nPATH_SEPARATOR: " . PATH_SEPARATOR;
echo "\nSCANDIR_SORT_ASCENDING: " . SCANDIR_SORT_ASCENDING;
echo "\nSCANDIR_SORT_DESCENDING: " . SCANDIR_SORT_DESCENDING;
echo "\nSCANDIR_SORT_NONE: " . SCANDIR_SORT_NONE;
echo "\n_SERVER['DOCUMENT_ROOT']: " . $_SERVER['DOCUMENT_ROOT'];
echo "\n</pre>";
showVariables();

/* Jinni search */ /*
require_once "php/Jinni.php";
$jinni = new RatingSync\Jinni("testratingsync");
$films = $jinni->getSearchSuggestions("Shawshank");
$page = "<html><body><ul>";
foreach($films as $film) {
    $page .= "<li>" . $film->getTitle() . "</li>";
}
$page .= "</ul></body></html>";
$outputDir = __DIR__ . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR;
$filename = $outputDir . "debug_result.html";
$fp = fopen($filename, "w");
fwrite($fp,  $page);
fclose($fp);
echo "<a href='$filename'>" . $filename . "</href>";
*/

/* preg_match_all */ /*
$html = "<b>Directed by:</b>&nbsp;<a href=\"http://www.jinni.com/person/chris-buck/\" class=\"\">Chris Buck</a>, <a href=\"http://www.jinni.com/person/jennifer-lee-2/\" class=\"\">Jennifer Lee</a></div><div id=\"j_id488\">";
//$pattern = "@<b>Directed by:<\/b>.+>(.+)<\/a>@";
//$html = "<b>example: </b><div align=left>this is a test</div>";
$pattern = "@<[^>]+>(.*)</[^>]+>@U";
//preg_match_all($pattern, $html, $out);

preg_match_all($pattern, $html, $out);
echo "<pre>";
var_dump($out);

$credits = $out[1];
array_shift($credits);
foreach ($credits as $credit) {
    echo "Director: $credit \n";
}
echo "</pre>";
*/

?>


        
    </body>
</html>
