# Moviezzz data dictionary

The SQL file is authoritative. Auto-increment IDs use INT UNSIGNED consistently.

## roles

| Column | Definition |
|---|---|
| `role_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `role_type` | `VARCHAR(20) NOT NULL UNIQUE` |

## contacts

| Column | Definition |
|---|---|
| `contact_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `first_name` | `VARCHAR(100) NOT NULL` |
| `last_name` | `VARCHAR(100) NOT NULL` |
| `email` | `VARCHAR(254) NOT NULL UNIQUE` |
| `phone` | `VARCHAR(30) NULL` |
| `street_1` | `VARCHAR(255) NULL` |
| `street_2` | `VARCHAR(255) NULL` |
| `city` | `VARCHAR(100) NULL` |
| `state_code` | `VARCHAR(100) NULL` |
| `post_code` | `VARCHAR(20) NULL` |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` |

## users

| Column | Definition |
|---|---|
| `user_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `contact_id` | `INT UNSIGNED NOT NULL UNIQUE` |
| `role_id` | `INT UNSIGNED NOT NULL` |
| `active` | `BOOLEAN NOT NULL DEFAULT TRUE` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## credentials

| Column | Definition |
|---|---|
| `user_id` | `INT UNSIGNED PRIMARY KEY` |
| `username` | `VARCHAR(50) NOT NULL UNIQUE` |
| `password_hash` | `VARCHAR(255) NOT NULL` |

## password_reset_tokens

| Column | Definition |
|---|---|
| `reset_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `user_id` | `INT UNSIGNED NOT NULL` |
| `token_hash` | `CHAR(64) NOT NULL UNIQUE` |
| `expires_at` | `DATETIME NOT NULL` |
| `used_at` | `DATETIME NULL` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## age_ratings

| Column | Definition |
|---|---|
| `rating_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `rating_name` | `VARCHAR(20) NOT NULL UNIQUE` |

## genres

| Column | Definition |
|---|---|
| `genre_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `genre_name` | `VARCHAR(50) NOT NULL UNIQUE` |

## actors

| Column | Definition |
|---|---|
| `actor_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `actor_name` | `VARCHAR(200) NOT NULL` |

## directors

| Column | Definition |
|---|---|
| `director_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `director_name` | `VARCHAR(200) NOT NULL` |

## movies

| Column | Definition |
|---|---|
| `movie_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `movie_title` | `VARCHAR(255) NOT NULL` |
| `synopsis` | `TEXT NULL` |
| `release_date` | `DATE NULL` |
| `runtime_minutes` | `SMALLINT UNSIGNED NULL` |
| `rating_id` | `INT UNSIGNED NULL` |
| `active` | `BOOLEAN NOT NULL DEFAULT TRUE` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## movie_genres

| Column | Definition |
|---|---|
| `movie_id` | `INT UNSIGNED NOT NULL` |
| `genre_id` | `INT UNSIGNED NOT NULL` |

## movie_actors

| Column | Definition |
|---|---|
| `movie_id` | `INT UNSIGNED NOT NULL` |
| `actor_id` | `INT UNSIGNED NOT NULL` |
| `billing_order` | `SMALLINT UNSIGNED NULL` |

## movie_directors

| Column | Definition |
|---|---|
| `movie_id` | `INT UNSIGNED NOT NULL` |
| `director_id` | `INT UNSIGNED NOT NULL` |

## reviews

| Column | Definition |
|---|---|
| `review_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `movie_id` | `INT UNSIGNED NOT NULL` |
| `user_id` | `INT UNSIGNED NOT NULL` |
| `stars` | `TINYINT UNSIGNED NOT NULL` |
| `review_text` | `TEXT NULL` |
| `flagged` | `BOOLEAN NOT NULL DEFAULT FALSE` |
| `active` | `BOOLEAN NOT NULL DEFAULT TRUE` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` |

## favorites

| Column | Definition |
|---|---|
| `user_id` | `INT UNSIGNED NOT NULL` |
| `movie_id` | `INT UNSIGNED NOT NULL` |
| `saved_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## forums

| Column | Definition |
|---|---|
| `forum_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `movie_id` | `INT UNSIGNED NULL` |
| `user_id` | `INT UNSIGNED NOT NULL` |
| `forum_title` | `VARCHAR(200) NOT NULL` |
| `active` | `BOOLEAN NOT NULL DEFAULT TRUE` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## forum_posts

| Column | Definition |
|---|---|
| `post_id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `forum_id` | `INT UNSIGNED NOT NULL` |
| `user_id` | `INT UNSIGNED NOT NULL` |
| `post_text` | `TEXT NOT NULL` |
| `active` | `BOOLEAN NOT NULL DEFAULT TRUE` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` |

## forum_votes

| Column | Definition |
|---|---|
| `forum_id` | `INT UNSIGNED NOT NULL` |
| `user_id` | `INT UNSIGNED NOT NULL` |
| `vote_value` | `TINYINT NOT NULL` |


## Composite keys

- movie_genres: (movie_id, genre_id)
- movie_actors: (movie_id, actor_id)
- movie_directors: (movie_id, director_id)
- favorites: (user_id, movie_id)
- forum_votes: (forum_id, user_id)
- reviews also has UNIQUE(user_id, movie_id).

Foreign keys, delete behavior, checks and indexes appear in sql/01_schema.sql.
