INSERT INTO user (username, password, email, enabled) VALUES ('testratingsync', '$2y$10$F3rnoPbKTrj8PgEiEFc66uQf5IVPt.bp6t7Xlis9hGSlCsnI58LZS', 'testratingsync@example.com', TRUE);

INSERT INTO verify_user (user_id, verified, code, complete_ts) VALUES (1, TRUE, '123abc', CURRENT_TIMESTAMP);

INSERT INTO source (name) VALUES ('RatingSync');
INSERT INTO source (name) VALUES ('Jinni');
INSERT INTO source (name) VALUES ('IMDb');
INSERT INTO source (name) VALUES ('OMDb');
INSERT INTO source (name) VALUES ('TMDb');
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
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'OMDb', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Netflix', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'RottenTomatoes', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'xfinity', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Hulu', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'Amazon', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'YouTube', 'testratingsync', 'password');
INSERT INTO user_source (user_name, source_name, username, password) VALUES ('testratingsync', 'HBO', 'testratingsync', 'password');

INSERT INTO user_filmlist (user_name, listname, create_ts) VALUES ('testratingsync', 'Watchlist', CURRENT_TIMESTAMP);