<?php

namespace RatingSync;

use Exception;
use DateTime;

class UserSpecificFilmInfo
{

    private int $userId;
    private int $filmId;
    private ?string $username;
    private ?bool $seen = null;
    private ?DateTime $seenDate = null;
    private ?bool $neverWatch = null;
    private ?DateTime $neverWatchDate = null;

    /**
     * @throws Exception
     */
    private function __construct(int $userId, int $filmId)
    {
        if ( $userId < 0 || $filmId < 0 ) {
            if ( $userId < 0 && $filmId < 0 ) {
                throw new Exception("UserSpecificFilmInfo constructor. Invalid userId ($userId) and invalid filmId ($filmId).");
            }
            elseif ( $userId < 0 ) {
                throw new Exception("UserSpecificFilmInfo constructor. Invalid userId ($userId).");
            }
            elseif ( $filmId < 0 ) {
                throw new Exception("UserSpecificFilmInfo constructor. Invalid filmId ($filmId).");
            }
        }

        $this->userId = $userId;
        $this->filmId = $filmId;
    }

    public static function newDbObject(string $username, int $filmId): ?UserSpecificFilmInfo
    {
        $userId = self::getUserIdFromDb($username);
        if ( $userId < 0 ) {
            return null;
        }

        if ( ! self::validateFilmId($filmId) ) {
            return null;
        }

        try {
            $newObject = new UserSpecificFilmInfo($userId, $filmId);
            $newObject->setUsername($username);
        }
        catch (Exception $e) {
            return null;
        }

        return $newObject;
    }

    /**
     * @throws Exception
     */
    public static function getFromDb(string $username, int $filmId): UserSpecificFilmInfo
    {
        $db = getDatabase();
        $result = $db->query("SELECT fu.* FROM film_user fu, user u WHERE fu.film_id=$filmId AND fu.user_id=u.id AND u.username='$username'");
        if ( $result->rowCount() != 1 ) {
            $usfi = self::newDbObject($username, $filmId);

            if ( is_null($usfi) ) {
                throw new Exception("Unable to construct UserSpecificFilmInfo(username=$username, filmId=$filmId)");
            }

            $usfi->saveToDb();
        }
        else {
            $usfi = self::getFromDbRow( $result->fetch(), $username );
        }

        return $usfi;
    }

    public function setFilmId(int $filmId): void
    {
        $this->filmId = $filmId;
    }

    public function getFilmId(): int
    {
        return $this->filmId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setSeen(bool $seen): void
    {
        $this->seen = $seen;
    }

    public function getSeen(): bool
    {
        return $this->seen ?: false;
    }

    public function setSeenDate(?DateTime $seenDate): void
    {
        $this->seenDate = $seenDate;
    }

    public function getSeenDate(): ?DateTime
    {
        return $this->seenDate;
    }

    public function setNeverWatch(bool $neverWatch): void
    {
        $this->neverWatch = $neverWatch;
    }

    public function getNeverWatch(): bool
    {
        return $this->neverWatch ?: false;
    }

    public function setNeverWatchDate(?DateTime $neverWatchDate): void
    {
        $this->neverWatchDate = $neverWatchDate;
    }

    public function getNeverWatchDate(): ?DateTime
    {
        return $this->neverWatchDate;
    }

    /**
     * @throws Exception
     */
    public function saveToDb($overwriteWithDefaultWhenEmpty = true): bool
    {
        if ( empty($this->getUserId()) || empty($this->getFilmId()) ) {
            throw new Exception("userId and filmId must be set before calling saveToDb().");
        }

        $db = getDatabase();

        $seen = 0;
        if ( $this->getSeen() ) {
            $seen = 1;
        }

        $neverWatch = 0;
        if ( $this->getNeverWatch() ) {
            $neverWatch = 1;
        }

        $userId = $this->getUserId();
        $filmId = $this->getFilmId();
        $seenDateSql = $this->getSeenDate() ? "'" . $this->getSeenDate()->format('Y-m-d') . "'" : "NULL";
        $neverWatchDateSql = $this->getNeverWatchDate() ? "'" . $this->getNeverWatchDate()->format('Y-m-d') . "'" : "NULL";

        $stmt = "REPLACE film_user (user_id, film_id, seen, neverWatch, seenDate, neverWatchDate)";
        $stmt .= "    VALUES ($userId, $filmId, $seen, $neverWatch, $seenDateSql, $neverWatchDateSql)";

        logDebug($stmt, __CLASS__."::".__FUNCTION__." ".__LINE__);

        return $db->query($stmt) !== false;
    }

    private static function getUserIdFromDb(string $username): int
    {
        if ( empty($username) ) {
            return -1;
        }

        $db = getDatabase();
        $result = $db->query("SELECT id FROM user WHERE username='$username'");
        if ( $result->rowCount() != 1 ) {
            return -1;
        }

        $row = $result->fetch();
        return $row["id"];
    }

    private static function validateFilmId(int $filmId): bool
    {
        $db = getDatabase();
        $result = $db->query("SELECT EXISTS(SELECT id FROM film WHERE id=$filmId)");
        $row = $result->fetch();

        return $row[0];
    }

    public function setSeenToDb(bool $seen, DateTime $date = null): Film | false
    {
        // Give a date when setting the boolean field, even the caller tries to use null
        $date = $date ?: new DateTime();

        $this->setSeen($seen);
        $this->setSeenDate($date);

        return $this->saveToDbAndGetFilm();
    }

    public function setNeverWatchToDb(bool $neverWatch, DateTime $date = null): Film | false
    {
        // Give a date when setting the boolean field, even the caller tries to use null
        $date = $date ?: new DateTime();

        $this->setNeverWatch($neverWatch);
        $this->setNeverWatchDate($date);

        return $this->saveToDbAndGetFilm();
    }

    private function saveToDbAndGetFilm(): Film | false
    {
        try {
            $this->saveToDb();
            $film = Film::getFilmFromDb($this->filmId, $this->getUsername());
        }
        catch (Exception $e) {
            return false;
        }

        if ( empty($film) ) {
            return false;
        }
        else {
            return $film;
        }
    }

    /**
     * @throws Exception
     */
    public static function getFromDbRow($row, $username = null): UserSpecificFilmInfo
    {
        $userId = $row["user_id"];
        $filmId = $row["film_id"];
        $seen = $row["seen"];
        $seenDate = $row["seenDate"] ? new DateTime($row["seenDate"]) : NULL;
        $neverWatch = $row["neverWatch"];
        $neverWatchDate = $row["neverWatchDate"] ? new DateTime($row["neverWatchDate"]) : NULL;

        $usfi = new UserSpecificFilmInfo($userId, $filmId); // throws exception in failure

        $usfi->setUsername($username);
        $usfi->setSeen($seen);
        $usfi->setSeenDate($seenDate);
        $usfi->setNeverWatch($neverWatch);
        $usfi->setNeverWatchDate($neverWatchDate);

        return $usfi;
    }

}