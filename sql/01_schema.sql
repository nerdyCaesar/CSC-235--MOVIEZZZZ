-- Moviezzz: run ONCE in a new, empty database selected in phpMyAdmin.
-- Create the database as moviezzz, collation utf8mb4_unicode_ci.
-- MySQL 8.0.16+ or MariaDB 10.2.1+ (CHECK enforcement required).
-- Does not DROP existing tables or disable foreign-key checks.
SET NAMES utf8mb4;

CREATE TABLE roles (
  role_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_type VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE contacts (
  contact_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(254) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  street_1 VARCHAR(255) NULL,
  street_2 VARCHAR(255) NULL,
  city VARCHAR(100) NULL,
  state_code VARCHAR(100) NULL,
  post_code VARCHAR(20) NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  contact_id INT UNSIGNED NOT NULL UNIQUE,
  role_id INT UNSIGNED NOT NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_contact FOREIGN KEY (contact_id) REFERENCES contacts(contact_id) ON DELETE RESTRICT,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT,
  CONSTRAINT ck_users_active CHECK (active IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE credentials (
  user_id INT UNSIGNED PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Schema support only: email delivery/reset PHP is a later milestone.
CREATE TABLE password_reset_tokens (
  reset_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE age_ratings (
  rating_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rating_name VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE genres (
  genre_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  genre_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE actors (
  actor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_name VARCHAR(200) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE directors (
  director_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  director_name VARCHAR(200) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE movies (
  movie_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  movie_title VARCHAR(255) NOT NULL,
  synopsis TEXT NULL,
  release_date DATE NULL,
  runtime_minutes SMALLINT UNSIGNED NULL,
  rating_id INT UNSIGNED NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_movies_title (movie_title),
  INDEX idx_movies_active_runtime (active, runtime_minutes),
  FOREIGN KEY (rating_id) REFERENCES age_ratings(rating_id) ON DELETE RESTRICT,
  CONSTRAINT ck_movies_runtime CHECK (runtime_minutes IS NULL OR runtime_minutes > 0),
  CONSTRAINT ck_movies_active CHECK (active IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE movie_genres (
  movie_id INT UNSIGNED NOT NULL,
  genre_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (movie_id, genre_id),
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
  FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE movie_actors (
  movie_id INT UNSIGNED NOT NULL,
  actor_id INT UNSIGNED NOT NULL,
  billing_order SMALLINT UNSIGNED NULL,
  PRIMARY KEY (movie_id, actor_id),
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
  FOREIGN KEY (actor_id) REFERENCES actors(actor_id) ON DELETE RESTRICT,
  CONSTRAINT ck_cast_billing CHECK (billing_order IS NULL OR billing_order > 0)
) ENGINE=InnoDB;

CREATE TABLE movie_directors (
  movie_id INT UNSIGNED NOT NULL,
  director_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (movie_id, director_id),
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
  FOREIGN KEY (director_id) REFERENCES directors(director_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE reviews (
  review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  movie_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  stars TINYINT UNSIGNED NOT NULL,
  review_text TEXT NULL,
  flagged BOOLEAN NOT NULL DEFAULT FALSE,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reviews_user_movie (user_id, movie_id),
  INDEX idx_reviews_movie_active (movie_id, active),
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT ck_review_stars CHECK (stars BETWEEN 1 AND 5),
  CONSTRAINT ck_review_flagged CHECK (flagged IN (0,1)),
  CONSTRAINT ck_review_active CHECK (active IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE favorites (
  user_id INT UNSIGNED NOT NULL,
  movie_id INT UNSIGNED NOT NULL,
  saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, movie_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Each forums row is ONE thread, matching the original diagram's use.
CREATE TABLE forums (
  forum_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  movie_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NOT NULL,
  forum_title VARCHAR(200) NOT NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT ck_forum_active CHECK (active IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE forum_posts (
  post_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  forum_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  post_text TEXT NOT NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (forum_id) REFERENCES forums(forum_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT ck_post_active CHECK (active IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE forum_votes (
  forum_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  vote_value TINYINT NOT NULL,
  PRIMARY KEY (forum_id, user_id),
  FOREIGN KEY (forum_id) REFERENCES forums(forum_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT ck_forum_vote CHECK (vote_value IN (-1,1))
) ENGINE=InnoDB;

-- Only visible reviews contribute. Flagging alone does not hide a review.
CREATE VIEW movie_rating_stats AS
SELECT movie_id, ROUND(AVG(stars), 1) AS average_rating, COUNT(*) AS review_count
FROM reviews WHERE active = TRUE GROUP BY movie_id;
