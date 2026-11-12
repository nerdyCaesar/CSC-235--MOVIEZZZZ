-- Run ONCE after 01_schema.sql. All movies and people below are FICTIONAL fixtures.
-- Real catalog data can be entered later. Register an account through PHP.
START TRANSACTION;
INSERT INTO roles (role_id, role_type) VALUES (1,'member'),(2,'admin');
INSERT INTO age_ratings (rating_id, rating_name) VALUES (1,'G'),(2,'PG'),(3,'PG-13'),(4,'R'),(5,'Unrated');
INSERT INTO genres (genre_id, genre_name) VALUES (1,'Adventure'),(2,'Comedy'),(3,'Drama'),(4,'Mystery'),(5,'Science Fiction');
INSERT INTO actors (actor_id, actor_name) VALUES (1,'Avery Vale'),(2,'Mira Stone'),(3,'Theo Reed');
INSERT INTO directors (director_id, director_name) VALUES (1,'Sam Rowan'),(2,'Alex Lane');
INSERT INTO movies (movie_id,movie_title,synopsis,release_date,runtime_minutes,rating_id) VALUES
 (1,'Orbit at Dawn','A fictional crew follows a mysterious signal.','2024-03-15',95,3),
 (2,'The Last Bus Home','Two strangers share an unexpected journey.','2023-06-02',88,2),
 (3,'Paper Lanterns','A fictional family reunites for a festival.','2022-09-10',120,2),
 (4,'The Quiet Platform','A detective investigates an abandoned station.','2025-01-17',105,3),
 (5,'Weekend on Mars','A comedy about a disastrous space vacation.','2024-07-12',82,2),
 (6,'An Unfinished Map','An upcoming fictional adventure with an unknown runtime.',NULL,NULL,NULL);
INSERT INTO movie_genres VALUES (1,1),(1,5),(2,2),(2,3),(3,3),(4,4),(5,2),(5,5),(6,1);
INSERT INTO movie_actors VALUES (1,1,1),(1,2,2),(2,3,1),(3,2,1),(4,1,1),(5,3,1),(6,2,1);
INSERT INTO movie_directors VALUES (1,1),(1,2),(2,2),(3,1),(4,2),(5,1),(6,2);
COMMIT;
