<?php
/**
 * Genre Class
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

class Genre {
    protected $name;

    public function __construct($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Genre contructor must have a name');
        }

        $this->name = $name;
    }

    public function setName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Function Genre::setName() must have a name');
        }

        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public static function getGenresFromDb()
    {
        $db = getDatabase();
        $genres = array();
        
        $result = $db->query("SELECT * FROM genre ORDER BY name ASC");
        while ($row = $result->fetch_assoc()) {
            $genres[] = $row['name'];
        }

        return $genres;
    }
}

?>
