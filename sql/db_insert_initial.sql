use ratingsync_db;

INSERT INTO user (username, password) VALUES ('testratingsync', 'password');

INSERT INTO source (name) VALUES ('RatingSync');
INSERT INTO source (name) VALUES ('Jinni');
INSERT INTO source (name) VALUES ('IMDb');

INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'RatingSync', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Jinni', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'IMDb', 'testratingsync', 'password');