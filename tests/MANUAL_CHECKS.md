# Browser acceptance checks

Run after importing the schema/data and configuring PHP. These flows were not executed in the preparation environment.

1. Register two accounts using different emails/usernames. Duplicate email and username must fail without leaving extra contacts/users. Inspect the account tables.
2. Log in, log out, and try an incorrect password. Registration always creates a member, even if a manually modified request submits a role ID.
3. View Orbit at Dawn: two genres, two actors and two directors should appear. An Unfinished Map should show unknown runtime/date/rating.
4. Submit 5 stars for a movie from the first account and 1 star from the second. The average should be 3.0 with 2 reviews. Editing the first rating to 3 should give 2.0 with 2 reviews, not a third review.
5. Leave review text blank with a valid star rating. It should save. Submit text such as `<script>alert(1)</script>` and confirm it displays literally, without executing.
6. Verify a second user cannot alter the first user's review by adding or changing a `user_id` form field. The handler ignores submitted user IDs and uses the session.
7. Submit 0 or 6 stars in a modified request. It must be rejected. Remove/change the form token: request should receive HTTP 403.
8. Save a favorite twice (including a repeat request). Only one row should exist. Remove it and verify it disappears from the Favorites page.
9. Set available time to 90 minutes. The initial fixtures return The Last Bus Home (88) and Weekend on Mars (82). Set time to 82: only Weekend on Mars remains. Clear time: unknown runtime may appear again.
10. Combine the Comedy genre and a minimum rating. Results must satisfy both criteria. Unrated movies disappear only when a minimum rating is selected.
11. Copy a review link and open it logged out. It should open the movie page with the visible review anchor. A localhost URL only works on a machine serving this project; external sharing requires deployment later.
12. In phpMyAdmin, hide a test review by setting `active=0` for its exact ID. Confirm it disappears and the public average/count changes. Editing that review from its author's account must keep it hidden. Restore `active=1` afterward.
13. Deactivate a test user by setting `users.active=0` for its exact ID. An already-open session must stop working on the next request. Restore it afterward if needed.
14. Open the site at a narrow browser width. Confirm forms, cards and navigation remain usable. Replace the CSS with the team's Bootstrap layout after the data flow passes.
