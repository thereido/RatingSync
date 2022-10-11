
INSERT INTO film_user (film_id, user_id, seen, seenDate, neverWatch, neverWatchDate)
    SELECT DISTINCT r.film_id, u.id, true, r.yourRatingDate, false, NULL
        FROM user u, rating r
        WHERE r.active=1
          AND r.source_name='RatingSync'
          AND r.user_name=u.username;
