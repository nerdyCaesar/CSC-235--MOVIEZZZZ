<?php
require __DIR__ . '/../app/bootstrap.php';
$id=filter_var(input($_GET,'id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$id) { http_response_code(400); exit('Invalid movie ID.'); }
$movie=query('SELECT m.*, ar.rating_name FROM movies m LEFT JOIN age_ratings ar ON ar.rating_id=m.rating_id WHERE m.movie_id=? AND m.active=1',[$id])->fetch();
if (!$movie) { http_response_code(404); exit('Movie not found.'); }
$user=current_user(); $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $user=require_user(); check_csrf();
    $action=input($_POST,'action');
    try {
        if ($action==='review') {
            $stars=filter_var(input($_POST,'stars'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>5]]);
            $text=input($_POST,'review_text');
            if (!$stars || strlen($text)>10000) { $error='Choose 1–5 stars and keep the review within 10,000 bytes.'; }
            else {
                // The user ID always comes from the session. Editing never unhides a moderated review.
                query('INSERT INTO reviews (movie_id,user_id,stars,review_text) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE stars=?,review_text=?',[$id,$user['user_id'],$stars,$text ?: null,$stars,$text ?: null]);
            }
        } elseif ($action==='delete_review') {
            query('DELETE FROM reviews WHERE movie_id=? AND user_id=?',[$id,$user['user_id']]);
        } elseif ($action==='favorite') {
            query('INSERT INTO favorites (user_id,movie_id) VALUES (?,?) ON DUPLICATE KEY UPDATE saved_at=saved_at',[$user['user_id'],$id]);
        } elseif ($action==='unfavorite') {
            query('DELETE FROM favorites WHERE user_id=? AND movie_id=?',[$user['user_id'],$id]);
        } else { http_response_code(400); $error='Unknown action.'; }
        if (!$error) { header('Location: movie.php?id='.$id.'&saved=1'); exit; }
    } catch (PDOException $ex) {
        error_log('Moviezzz save failed. SQLSTATE: '.$ex->getCode());
        $error='Unable to save. Please try again.';
    }
}
$stats=query('SELECT * FROM movie_rating_stats WHERE movie_id=?',[$id])->fetch();
$genres=query('SELECT g.genre_name FROM genres g JOIN movie_genres mg ON mg.genre_id=g.genre_id WHERE mg.movie_id=? ORDER BY g.genre_name',[$id])->fetchAll(PDO::FETCH_COLUMN);
$actors=query('SELECT a.actor_name FROM actors a JOIN movie_actors ma ON ma.actor_id=a.actor_id WHERE ma.movie_id=? ORDER BY ma.billing_order IS NULL,ma.billing_order,a.actor_name',[$id])->fetchAll(PDO::FETCH_COLUMN);
$directors=query('SELECT d.director_name FROM directors d JOIN movie_directors md ON md.director_id=d.director_id WHERE md.movie_id=? ORDER BY d.director_name',[$id])->fetchAll(PDO::FETCH_COLUMN);
$reviews=query('SELECT r.*, c.username FROM reviews r JOIN credentials c ON c.user_id=r.user_id WHERE r.movie_id=? AND r.active=1 ORDER BY r.created_at DESC LIMIT 100',[$id])->fetchAll();
$mine=$user ? query('SELECT * FROM reviews WHERE movie_id=? AND user_id=?',[$id,$user['user_id']])->fetch() : false;
$favorite=$user ? query('SELECT 1 FROM favorites WHERE movie_id=? AND user_id=?',[$id,$user['user_id']])->fetchColumn() : false;
page_start($movie['movie_title']);
?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<?php if (input($_GET,'saved')==='1'): ?><p class="notice">Saved.</p><?php endif; ?>
<p><?= e($movie['synopsis']) ?></p>
<p><strong><?= e($stats['average_rating'] ?? 'Unrated') ?></strong> <?= $stats ? '/ 5' : '' ?> · <?= e($stats['review_count'] ?? 0) ?> reviews</p>
<p><?= e($movie['runtime_minutes'] ? $movie['runtime_minutes'].' minutes' : 'Runtime unknown') ?> · <?= e($movie['rating_name'] ?? 'Age rating unknown') ?> · <?= e($movie['release_date'] ?? 'Release date unknown') ?></p>
<p><strong>Genres:</strong> <?= e(implode(', ',$genres)) ?><br><strong>Cast:</strong> <?= e(implode(', ',$actors)) ?><br><strong>Directors:</strong> <?= e(implode(', ',$directors)) ?></p>
<?php if ($user): ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="<?= $favorite ? 'unfavorite' : 'favorite' ?>"><button><?= $favorite ? 'Remove favorite' : 'Save to favorites' ?></button></form>
<hr><h2><?= $mine ? 'Update your review' : 'Your review' ?></h2>
<?php if ($mine && !$mine['active']): ?><p class="notice">Your review is hidden by moderation. Updating it keeps it hidden until an administrator restores it.</p><?php endif; ?>
<form method="post" class="stack"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="review">
<label>Stars<select name="stars" required><?php for($i=1;$i<=5;$i++): ?><option value="<?= $i ?>" <?= (int)($mine['stars'] ?? 5)===$i ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></label>
<label>Written review (optional)<textarea rows="5" name="review_text" maxlength="10000"><?= e($mine['review_text'] ?? '') ?></textarea></label><button>Save review</button></form>
<?php if ($mine): ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="delete_review"><p><button>Delete my review</button></p></form><?php endif; ?>
<?php else: ?><p><a href="login.php">Log in</a> to review or save this movie.</p><?php endif; ?>
<hr><h2>Community reviews</h2>
<?php if (!$reviews): ?><p>No visible reviews yet.</p><?php endif; ?>
<?php foreach($reviews as $r): ?><article id="review-<?= e($r['review_id']) ?>"><h3><?= e($r['username']) ?> · <?= e($r['stars']) ?>/5</h3><p class="review-text"><?= e($r['review_text'] ?? '') ?></p><a href="movie.php?id=<?= e($id) ?>#review-<?= e($r['review_id']) ?>">Review link</a></article><?php endforeach; ?>
<?php page_end(); ?>
