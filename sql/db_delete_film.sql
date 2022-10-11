DELETE FROM credit WHERE film_id=(SELECT id FROM film WHERE title LIKE '%FILM TITLE%');
DELETE FROM film_genre WHERE film_id=(SELECT id FROM film WHERE title LIKE '%FILM TITLE%');
DELETE FROM rating_archive WHERE film_id=(SELECT id FROM film WHERE title LIKE '%FILM TITLE%');
DELETE FROM rating WHERE film_id=(SELECT id FROM film WHERE title LIKE '%FILM TITLE%');
DELETE FROM film_source WHERE film_id=(SELECT id FROM film WHERE title LIKE '%FILM TITLE%');
DELETE FROM filmlist WHERE film_id=(SELECT id FROM film WHERE title LIKE '%FILM TITLE%');
DELETE FROM film_user WHERE film_id=(SELECT id FROM film WHERE title LIKE '%FILM TITLE%');
DELETE FROM film WHERE title LIKE '%FILM TITLE%';