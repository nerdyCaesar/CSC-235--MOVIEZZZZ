<?php
require __DIR__ . '/../app/bootstrap.php';
$user = current_user();
$search = input($_GET, 'q');
$genre = filter_var(input($_GET, 'genre'), FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: null;
$minutes = filter_var(input($_GET, 'minutes'), FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>1000]]) ?: null;
$minRating = filter_var(input($_GET, 'rating'), FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>5]]) ?: null;
$favorites = input($_GET, 'favorites') === '1';
if ($favorites && !$user) { header('Location: login.php'); exit; }
$where = ['m.active=1']; $params = [];
if ($search !== '') { $where[]='m.movie_title LIKE ?'; $params[]='%'.$search.'%'; }
if ($genre) { $where[]='EXISTS (SELECT 1 FROM movie_genres mg WHERE mg.movie_id=m.movie_id AND mg.genre_id=?)'; $params[]=$genre; }
if ($minutes) { $where[]='m.runtime_minutes <= ?'; $params[]=$minutes; }
if ($minRating) { $where[]='rs.average_rating >= ?'; $params[]=$minRating; }
if ($favorites) { $where[]='EXISTS (SELECT 1 FROM favorites f WHERE f.movie_id=m.movie_id AND f.user_id=?)'; $params[]=$user['user_id']; }
$sql='SELECT m.*, rs.average_rating, COALESCE(rs.review_count,0) AS review_count FROM movies m LEFT JOIN movie_rating_stats rs ON rs.movie_id=m.movie_id WHERE '.implode(' AND ', $where).' ORDER BY rs.average_rating DESC, review_count DESC, m.movie_title ASC LIMIT 100';
$movies = query($sql,$params)->fetchAll();
$genres = query('SELECT * FROM genres ORDER BY genre_name')->fetchAll();
page_start($favorites ? 'Your favorites' : 'Find your next movie');
?>
<p>Have 90 minutes? Find a movie that fits. Combine time, genre and community rating.</p>
<form class="filters" method="get">
  <?php if ($favorites): ?><input type="hidden" name="favorites" value="1"><?php endif; ?>
  <label>Movie title<input name="q" value="<?= e($search) ?>" maxlength="255"></label>
  <label>Genre<select name="genre"><option value="">Any genre</option><?php foreach($genres as $g): ?><option value="<?= e($g['genre_id']) ?>" <?= $genre == $g['genre_id'] ? 'selected' : '' ?>><?= e($g['genre_name']) ?></option><?php endforeach; ?></select></label>
  <label>Time available (minutes)<input type="number" name="minutes" min="1" max="1000" value="<?= e($minutes) ?>" placeholder="90"></label>
  <label>Minimum stars<select name="rating"><option value="">Any rating</option><?php for($i=1;$i<=5;$i++): ?><option value="<?= $i ?>" <?= $minRating === $i ? 'selected' : '' ?>><?= $i ?>+</option><?php endfor; ?></select></label>
  <button>Find movies</button>
</form>
<p class="muted">Unrated movies appear when “Any rating” is selected. Unknown runtimes are excluded when a time limit is set. Showing up to 100 results.</p>
<section class="grid">
<?php foreach($movies as $m): ?>
<article><p class="tag"><?= e($m['runtime_minutes'] ? $m['runtime_minutes'].' min' : 'Runtime pending') ?></p><h2><a href="movie.php?id=<?= e($m['movie_id']) ?>"><?= e($m['movie_title']) ?></a></h2><p><?= e($m['synopsis']) ?></p><p><strong><?= e($m['average_rating'] ?? 'Unrated') ?></strong> <?= $m['average_rating'] !== null ? '/ 5' : '' ?> · <?= e($m['review_count']) ?> reviews</p></article>
<?php endforeach; ?>
</section>
<?php if (!$movies): ?><p>No movies match. Try a longer time limit or fewer filters.</p><?php endif; ?>
<?php page_end(); ?>
