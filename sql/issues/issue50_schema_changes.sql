
CREATE TABLE IF NOT EXISTS film_user
(
    film_id INT NOT NULL,
    user_id INT NOT NULL,
    seen BOOLEAN DEFAULT FALSE NOT NULL,
    seenDate DATE NULL DEFAULT NULL,
    neverWatch BOOLEAN DEFAULT FALSE NOT NULL,
    neverWatchDate DATE NULL DEFAULT NULL,
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (film_id, user_id),
    KEY (film_id),
    KEY (user_id),

    FOREIGN KEY (film_id)
        REFERENCES film(id),
    FOREIGN KEY (user_id)
        REFERENCES user(id)
);

ALTER TABLE rating
    ADD watched BOOLEAN DEFAULT TRUE NOT NULL
        AFTER suggestedScore;
