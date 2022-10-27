
-- Delete all archive rows from external sources
delete from rating_archive where source_name not like 'RatingSync';

-- Delete archive rows with no date and no score
delete from rating_archive where yourRatingDate is null and yourScore is null order by film_id desc;

-- Add a 'active' column to the archive
alter table rating_archive add active boolean default false not null after suggestedScore;

-- Create copyArchiveToRatingTable procedure
DROP PROCEDURE IF EXISTS copyArchiveToRatingTable;
DELIMITER $$
CREATE PROCEDURE copyArchiveToRatingTable (
	INOUT countCopied INT(11),
	INOUT countDups INT(11)
)
BEGIN
	DECLARE finished INTEGER DEFAULT 0;
	DECLARE username varchar(50);
	DECLARE sourceName varchar(50);
	DECLARE filmId int(11);
	DECLARE yourScore int(11);
	DECLARE yourRatingDate date;
	DECLARE suggestedScore int(11);
	DECLARE active tinyint(1);
	DECLARE ts timestamp;
	DECLARE countMatches INTEGER DEFAULT 0;

	-- declare cursor for employee email
	DEClARE curArchivedRatings
		CURSOR FOR
			SELECT DISTINCT * FROM rating_archive;

	-- declare NOT FOUND handler
	DECLARE CONTINUE HANDLER
        FOR NOT FOUND SET finished = 1;

	OPEN curArchivedRatings;

	getRating: LOOP
		FETCH curArchivedRatings INTO username, sourceName, filmId, yourScore, yourRatingDate, suggestedScore, active, ts;
		IF finished = 1 THEN
			LEAVE getRating;
		END IF;

		-- Check for a duplicate
		SELECT count(*) INTO countMatches FROM rating as r WHERE r.user_name=username AND r.source_name=sourceName AND r.film_id=filmId AND r.yourRatingDate=yourRatingDate;

		-- Insert to rating if it is not a dup
		IF countMatches = 0 THEN
		    INSERT INTO rating VALUES (username, sourceName, filmId, yourScore, yourRatingDate, suggestedScore, active, rating.ts);

	        SET countCopied = countCopied + 1;
		ELSE
		    SET countDups = countDups + 1;
        end if;


	END LOOP getRating;
	CLOSE curArchivedRatings;

END$$
DELIMITER ;

-- Use the stored procedure
SET @count = 0;
SET @countDups = 0;
CALL copyArchiveToRatingTable(@count,@countDups);
SELECT @countDups as 'Duplicates';
SELECT @count as 'Copied archive ratings';
