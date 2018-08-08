CREATE TABLE IF NOT EXISTS user
    (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NULL DEFAULT NULL,
        email VARCHAR(50) NULL DEFAULT NULL,
        enabled BOOLEAN NOT NULL DEFAULT FALSE,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_username (username, enabled),
        INDEX idx_id (id, enabled)
    );

CREATE TABLE IF NOT EXISTS verify_user
    (
        user_id INT NOT NULL,
        verified BOOLEAN NOT NULL DEFAULT FALSE,
        code VARCHAR(50) NULL DEFAULT NULL,
        complete_ts TIMESTAMP NULL DEFAULT NULL,
        create_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX idx_user (user_id),
        INDEX idx_user_complete (user_id, complete_ts),
        
        FOREIGN KEY (user_id)
            REFERENCES user(id)
    );

CREATE TABLE IF NOT EXISTS source
    (
        name VARCHAR(50) NOT NULL PRIMARY KEY,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

CREATE TABLE IF NOT EXISTS user_source
    (
        user_name VARCHAR(50) NOT NULL,
        source_name VARCHAR(50) NOT NULL,
        username VARCHAR(50) NULL DEFAULT NULL,
        password VARCHAR(50) NULL DEFAULT NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (user_name, source_name),
        KEY (user_name),
        KEY (source_name),
        
        FOREIGN KEY (user_name)
            REFERENCES user(username),
        FOREIGN KEY (source_name)
            REFERENCES source(name)
    );
  
CREATE TABLE IF NOT EXISTS film
    (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        parent_id INT NULL,
        title VARCHAR(75) NOT NULL,
        year INT NULL DEFAULT NULL,
        contentType VARCHAR(50) NULL DEFAULT NULL,
        seasonCount INT NULL DEFAULT NULL,
        season VARCHAR(75) NULL DEFAULT NULL,
        episodeNumber INT NULL DEFAULT NULL,
        episodeTitle VARCHAR(75) NULL DEFAULT NULL,
        image VARCHAR(200) NULL DEFAULT NULL,
        refreshDate DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        UNIQUE KEY idx_title_year (title, year, season, episodeTitle)
    );

CREATE TABLE IF NOT EXISTS film_source
    (
        film_id INT NOT NULL,
        source_name VARCHAR(50) NOT NULL,
        image VARCHAR(200) NULL DEFAULT NULL,
        uniqueName VARCHAR(100) NULL DEFAULT NULL,
        uniqueEpisode VARCHAR(100) NULL DEFAULT NULL,
        uniqueAlt VARCHAR(100) NULL DEFAULT NULL,
        streamUrl VARCHAR(200) NULL DEFAULT NULL,
        streamDate DATE DEFAULT 0,
        criticScore INT NULL DEFAULT NULL,
        userScore INT NULL DEFAULT NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (film_id, source_name),
        KEY (film_id),
        KEY (source_name),
        
        FOREIGN KEY (film_id)
            REFERENCES film(id),
        FOREIGN KEY (source_name)
            REFERENCES source(name)
    );
  
CREATE TABLE IF NOT EXISTS rating
    (
        user_name VARCHAR(50) NOT NULL,
        source_name VARCHAR(50) NOT NULL,
        film_id INT NOT NULL,
        yourScore INT NULL DEFAULT NULL,
        yourRatingDate DATE NULL DEFAULT NULL,
        suggestedScore INT NULL DEFAULT NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (user_name, source_name, film_id),
        KEY (user_name),
        KEY (source_name),
        KEY (film_id),

        FOREIGN KEY (user_name)
            REFERENCES user(username),
        FOREIGN KEY (source_name)
            REFERENCES source(name),
        FOREIGN KEY (film_id)
            REFERENCES film(id)
    );
  
CREATE TABLE IF NOT EXISTS rating_archive
    (
        user_name VARCHAR(50) NOT NULL,
        source_name VARCHAR(50) NOT NULL,
        film_id INT NOT NULL,
        yourScore INT NULL DEFAULT NULL,
        yourRatingDate DATE NULL DEFAULT NULL,
        suggestedScore INT NULL DEFAULT NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        KEY (user_name, source_name, film_id, ts),
        KEY (user_name),
        KEY (source_name),
        KEY (film_id),

        FOREIGN KEY (user_name)
            REFERENCES user(username),
        FOREIGN KEY (source_name)
            REFERENCES source(name),
        FOREIGN KEY (film_id)
            REFERENCES film(id)
    );
  
CREATE TABLE IF NOT EXISTS genre
    (
        name VARCHAR(50) NOT NULL PRIMARY KEY
    );

CREATE TABLE IF NOT EXISTS film_genre
    (
        film_id INT NOT NULL,
        genre_name VARCHAR(50) NOT NULL,
        
        KEY (film_id),
        KEY (genre_name),
        UNIQUE KEY (film_id, genre_name),
        
        FOREIGN KEY (film_id)
            REFERENCES film(id),
        FOREIGN KEY (genre_name)
            REFERENCES genre(name)
    );

CREATE TABLE IF NOT EXISTS person
    (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(50) NULL DEFAULT NULL,
        lastname VARCHAR(50) NULL DEFAULT NULL,
        firstname VARCHAR(50) NULL DEFAULT NULL,
        birthdate DATE NULL DEFAULT NULL,
        image VARCHAR(150) NULL DEFAULT NULL,
        
        KEY (fullname),
        KEY (lastname, firstname),
        UNIQUE KEY idx_fullname_birthdate (fullname, birthdate)
    );

CREATE TABLE IF NOT EXISTS credit
    (
        person_id INT NOT NULL,
        film_id INT NOT NULL,
        position VARCHAR(50) NULL DEFAULT NULL,
        
        KEY (person_id),
        KEY (film_id),
        UNIQUE KEY (person_id, film_id, position),
        
        FOREIGN KEY (person_id)
            REFERENCES person(id),
        FOREIGN KEY (film_id)
            REFERENCES film(id)
    );
  
CREATE TABLE IF NOT EXISTS filmlist
    (
        user_name VARCHAR(50) NOT NULL,
        film_id INT NOT NULL,
        listname VARCHAR(50) NOT NULL,
        position INT NULL DEFAULT NULL,
        next_film_id INT NULL DEFAULT NULL,
        /* create_ts TIMESTAMP NOT NULL, */
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (user_name, film_id, listname),
        KEY (user_name),
        KEY (film_id),
        KEY (user_name, listname),

        FOREIGN KEY (user_name)
            REFERENCES user(username),
        FOREIGN KEY (film_id)
            REFERENCES film(id)
    );
ALTER TABLE filmlist ADD COLUMN create_ts TIMESTAMP NOT NULL DEFAULT 0 AFTER position;
ALTER TABLE filmlist ALTER COLUMN create_ts DROP DEFAULT;
  
CREATE TABLE IF NOT EXISTS user_filmlist
    (
        user_name VARCHAR(50) NOT NULL,
        listname VARCHAR(50) NOT NULL,
        parent_listname VARCHAR(50) NULL DEFAULT NULL,
        /* create_ts TIMESTAMP NOT NULL, */
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (user_name, listname),
        KEY (user_name),
        KEY (listname),
        KEY (user_name, listname, parent_listname),

        FOREIGN KEY (user_name)
            REFERENCES user(username)
    );
ALTER TABLE user_filmlist ADD COLUMN create_ts TIMESTAMP NOT NULL DEFAULT 0 AFTER parent_listname;
ALTER TABLE user_filmlist ALTER COLUMN create_ts DROP DEFAULT;