ALTER TABLE rating_archive DROP FOREIGN KEY rating_archive_ibfk_1;
ALTER TABLE rating_archive DROP FOREIGN KEY rating_archive_ibfk_2;
ALTER TABLE rating_archive DROP FOREIGN KEY rating_archive_ibfk_3;

RENAME TABLE IF EXISTS rating_archive TO obsolete_rating_archive;