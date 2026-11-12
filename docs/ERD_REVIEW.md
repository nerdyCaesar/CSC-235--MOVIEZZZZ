# Proposed ERD corrections

Source: `Presentation.pptx`, slide 6 (the actual Part 2 ERD), with slide 5 for authentication and slide 4 for scope. The ERD was embedded as an EMF containing a PDF; its entities and relationships were inspected directly. The professor's specific comments were not included. These are independently identified improvements, pending his feedback.

## Main changes

| Original design | Proposed design | Reason |
|---|---|---|
| `Movies.genre_id` | `movie_genres(movie_id, genre_id)` | A movie can be both comedy and science fiction; each genre applies to many movies |
| `Movies.actor_id` | `movie_actors(movie_id, actor_id, billing_order)` | Several actors can appear in one movie; one actor appears in many movies |
| `Movies.director_id` | `movie_directors(movie_id, director_id)` | Supports co-directed movies |
| Stored `Movies.avg_review` | `movie_rating_stats` view | Avoids an outdated average after a review changes or is hidden/deleted |
| `runtime TIME()` | `runtime_minutes SMALLINT UNSIGNED` | Clear duration unit; makes “fits in 90 minutes” a simple numerical comparison |
| `release_date date()` | `release_date DATE` | Correct SQL type syntax; unknown dates remain NULL |
| Any integer `stars` | `CHECK(stars BETWEEN 1 AND 5)` plus PHP validation | Invalid ratings are rejected |
| Multiple reviews for the same user/movie possible | `UNIQUE(user_id,movie_id)` | Users update their existing review |
| `Favorites.favorite_id` with no pair uniqueness | Composite primary key `(user_id,movie_id)` | The same movie cannot be saved twice by one user |
| Credential primary key is editable username; user has many credentials | `credentials.user_id` is PK/FK, username UNIQUE | Stable identity and at most one credential record per account |
| Contact can be referenced by many accounts | `UNIQUE(users.contact_id)` | At most one account per contact in this app |
| `post_code INT(5)` | `VARCHAR(20)` | Retains leading zeroes and allows alphanumeric postal codes |
| `password_salted` | `password_hash VARCHAR(255)` | Stores PHP's complete password hash; no homemade salt column |
| Like/dislike threads listed but no corresponding entity | `forum_votes(forum_id,user_id,vote_value)` | One +1/-1 vote per user/thread; score is calculated |
| Forgot password listed but no supporting records | `password_reset_tokens` | Stores a hashed random token, expiry and used time; reset PHP/email comes later |
| Forum/post moderation listed but no visibility fields | `active` on forums and posts | Admins can hide content without deleting conversation history |

Actors and directors remain separate entities to stay close to your team's diagram. Combining them into `people` and role-based `credits` is an optional later simplification, not necessary for the initial implementation. Person names are not UNIQUE because two people can share a name. Movie titles are not UNIQUE because remakes can share titles.

## Cardinalities and business rules

- One age-rating category can apply to zero or many movies. A movie has zero or one category while metadata is incomplete. “Unknown” is NULL; “Unrated” is an explicit category.
- Each junction row references exactly one movie and one genre/actor/director. Together the two FKs form its primary key. A parent can have zero or many junction rows. Publishing completeness checks belong in the future admin form.
- Each review references exactly one user and one movie. Each user/movie can have zero or many reviews overall, but the same pair has at most one. Written review text is optional; stars are required.
- Each favorite is exactly one user/movie pair.
- Each `forums` record represents one thread, following the original diagram. `movie_id` may be NULL for a general discussion. A thread has one author and zero or many posts/votes. Opening a thread and its first post should be a single PHP transaction when you build that screen.
- `forum_posts` are flat replies. Nested replies would need a self-referencing parent ID and rules ensuring the parent belongs to the same thread; that is outside this starter.
- One role can belong to many users. Each user has exactly one role and contact. Each contact has at most one user, and each user at most one credentials row. The database permits temporary orphan contacts/users; the registration transaction creates all three account records together so the normal workflow completes atomically.
- Phone/address fields remain optional to preserve your Part 1 layout. Registration only asks for names, email, username and password. If your professor permits reducing scope, the unused address columns can be removed.

## Derived data and moderation

The `movie_rating_stats` view groups visible (`active=1`) reviews by movie. It calculates a one-decimal average and a review count. Catalog queries LEFT JOIN this view so a movie without reviews still appears. No reviews means **Unrated**, not a fabricated zero-star rating. The minimum-rating filter uses the displayed rounded average.

`flagged=1` means a review needs attention; it remains visible until an administrator sets `active=0`. These two flags describe separate facts. Updating a hidden review in the starter does not restore it. A persistent restriction on an abusive user's future reviews would require an account-level moderation policy, which is not implemented here.

Do not join reviews directly to genres/cast before calculating averages: multiplying rows can inflate review counts. The view aggregates reviews first, and genre filtering uses EXISTS.

## Deletion choices

Foreign keys enforce valid parent references. Junction rows, reviews, favorites and credentials use CASCADE when their parent is physically deleted. Lookup categories use RESTRICT so removing a category does not erase movies. Deleting a movie sets its forum threads' movie reference to NULL, preserving those discussions.

Forum authorship uses RESTRICT on user deletion. The initial administrative “remove user” behavior should deactivate the account (`users.active=0`), preventing subsequent requests through the starter's current-user check. This deliberately preserves discussion authorship. If your course requires physical account deletion, first define how to anonymize or reassign forum content, then delete the account and its contact in a transaction. Do not blindly copy the HR sample's CASCADE choices.

Prefer hiding movies (`movies.active=0`) in the future admin interface. Physical movie deletion removes associated reviews and favorites. This is a documented design choice, not an operation performed by this package.

## Extra feature: fits my available time

This is not listed in slide 4. A user enters a maximum runtime, optionally selects a genre and minimum rating, and receives matching movies ranked by displayed rating, then review count and title. For example, “90 minutes, comedy” selects qualifying comedy movies without adding another table.

`runtime_minutes <= ?` excludes NULL runtimes. With no time limit, movies with unknown runtimes remain available. With a minimum rating, unrated movies are excluded; otherwise they appear after rated movies. The UI explains these cases.

This is a useful, achievable search feature. It should not be presented as machine learning or assumed to satisfy a professor's requirement for an “advanced” feature without checking that rubric.

## Files for your presentation

Use the four PNG/SVG diagrams in this folder. They split the schema for readability: accounts, catalog, reviews/favorites and forums. All 18 tables are represented. The SQL is authoritative for exact constraints, composite keys and delete actions. The original presentation was not overwritten.

The drawing uses numeric multiplicity (1, 0..1, 0..many). PK on both columns of a junction table means one **composite** primary key, not two independent keys. The shared `users` and `movies` boxes are abbreviated references in the review/forum diagrams.

## Technical references

- PHP PDO prepared statements: https://www.php.net/manual/en/pdo.prepare.php
- PHP password hashing: https://www.php.net/manual/en/function.password-hash.php
- MariaDB constraints: https://mariadb.com/docs/server/reference/sql-statements/data-definition/constraint
- MariaDB foreign keys: https://mariadb.com/docs/server/architecture/server-constraints/foreign-key-constraints

PDO parameters carry form values rather than concatenating them into SQL. `password_hash` generates the salt and encoded hash, and `password_verify` checks the submitted password. Database constraints complement server-side validation.
