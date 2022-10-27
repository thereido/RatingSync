alter table rating
    add active boolean default false not null after suggestedScore;

update rating set active=true where active=false;

delete from rating where user_name='delete_me';

delete from rating
  where yourRatingDate is null and source_name not like 'RatingSync' order by film_id desc;

delete from rating
  where yourRatingDate is null and yourScore is null order by film_id desc;

alter table rating
    drop primary key;

alter table rating
    add primary key (user_name, source_name, film_id, yourRatingDate);