
UPDATE rating SET watched=0 WHERE 1=1;
UPDATE rating SET watched=1 WHERE source_name='RatingSync';
