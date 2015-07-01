use ratingsync_db;

CREATE TABLE IF NOT EXISTS user
    (
        username VARCHAR(50) NOT NULL PRIMARY KEY,
        password VARCHAR(50) NULL DEFAULT NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
        title VARCHAR(75) NOT NULL,
        year INT NULL DEFAULT NULL,
        contentType VARCHAR(50) NULL DEFAULT NULL,
        image VARCHAR(150) NULL DEFAULT NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        UNIQUE KEY idx_title_year (title, year)
    );

CREATE TABLE IF NOT EXISTS film_source
    (
        film_id INT NOT NULL,
        source_name VARCHAR(50) NOT NULL,
        image VARCHAR(150) NULL DEFAULT NULL,
        filmName VARCHAR(50) NULL DEFAULT NULL,
        urlName VARCHAR(50) NULL DEFAULT NULL,
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
        criticScore INT NULL DEFAULT NULL,
        userScore INT NULL DEFAULT NULL,
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
  
CREATE TABLE IF NOT EXISTS wishlist
    (
        user_name VARCHAR(50) NOT NULL,
        film_id INT NOT NULL,
        position INT NULL DEFAULT NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (user_name, film_id),
        KEY (user_name),
        KEY (film_id),

        FOREIGN KEY (user_name)
            REFERENCES user(username),
        FOREIGN KEY (film_id)
            REFERENCES film(id)
    );