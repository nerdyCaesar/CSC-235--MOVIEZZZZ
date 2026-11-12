-- Select moviezzz in phpMyAdmin first. These queries are read-only.

-- 1. Confirm table creation and engine/version.
SELECT VERSION();
SHOW TABLES;

-- 2. One movie can have several genres.
SELECT m.movie_title, g.genre_name
FROM movies m
JOIN movie_genres mg ON mg.movie_id=m.movie_id
JOIN genres g ON g.genre_id=mg.genre_id
ORDER BY m.movie_id,g.genre_name;

-- 3. Averages include only visible reviews. NULL means no reviews, not zero stars.
SELECT m.movie_title, rs.average_rating, COALESCE(rs.review_count,0) AS review_count
FROM movies m
LEFT JOIN movie_rating_stats rs ON rs.movie_id=m.movie_id
WHERE m.active=1;

-- 4. New feature: fits in 90 minutes. Seed returns Weekend on Mars + The Last Bus Home.
SELECT movie_title,runtime_minutes FROM movies
WHERE active=1 AND runtime_minutes<=90
ORDER BY runtime_minutes;

-- 5. Forum thread scores. One vote per user per thread is enforced by the PK.
SELECT f.forum_id,f.forum_title,COALESCE(SUM(v.vote_value),0) AS score
FROM forums f LEFT JOIN forum_votes v ON v.forum_id=f.forum_id
WHERE f.active=1 GROUP BY f.forum_id,f.forum_title;
