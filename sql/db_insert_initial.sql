INSERT INTO user (username, password) VALUES ('testratingsync', 'cf5c3cbc0b0f4c5b3c98a4fec08307f9');

INSERT INTO source (name) VALUES ('RatingSync');
INSERT INTO source (name) VALUES ('Jinni');
INSERT INTO source (name) VALUES ('IMDb');
INSERT INTO source (name) VALUES ('Netflix');
INSERT INTO source (name) VALUES ('RottenTomatoes');
INSERT INTO source (name) VALUES ('xfinity');
INSERT INTO source (name) VALUES ('Hulu');
INSERT INTO source (name) VALUES ('Amazon');
INSERT INTO source (name) VALUES ('YouTube');
INSERT INTO source (name) VALUES ('HBO');

INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'RatingSync', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Jinni', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'IMDb', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Netflix', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'RottenTomatoes', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'xfinity', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Hulu', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Amazon', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'YouTube', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'HBO', 'testratingsync', 'password');

INSERT INTO user_filmlist (user_name, listname) VALUES ('testratingsync', 'Watchlist');