<?php
// CLI only. All fixture writes are rolled back; auto-increment gaps can remain.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/bootstrap.php';
$pdo=db();
function assert_true(bool $condition, string $label): void {
    if (!$condition) throw new RuntimeException('FAIL: '.$label);
    echo 'PASS: '.$label.PHP_EOL;
}
function rejected(callable $write, array $codes, string $label): void {
    try { $write(); }
    catch (PDOException $ex) {
        if (!in_array((int)($ex->errorInfo[1] ?? 0),$codes,true)) throw $ex;
        echo 'PASS: '.$label.PHP_EOL; return;
    }
    throw new RuntimeException('FAIL: '.$label.' (write unexpectedly succeeded)');
}
function fixture_user(string $marker, int $role): int {
    query('INSERT INTO contacts (first_name,last_name,email) VALUES (?,?,?)',['Test','Fixture',$marker.'@example.invalid']);
    $contact=(int)db()->lastInsertId();
    query('INSERT INTO users (contact_id,role_id) VALUES (?,?)',[$contact,$role]);
    $id=(int)db()->lastInsertId();
    query('INSERT INTO credentials (user_id,username,password_hash) VALUES (?,?,?)',[$id,$marker,password_hash('Local test password only',PASSWORD_DEFAULT)]);
    return $id;
}
$pdo->beginTransaction();
try {
    $marker='test_'.bin2hex(random_bytes(5));
    query('INSERT INTO roles (role_type) VALUES (?)',[$marker]);
    $role=(int)$pdo->lastInsertId();
    $u1=fixture_user($marker.'a',$role); $u2=fixture_user($marker.'b',$role);
    query('INSERT INTO movies (movie_title,runtime_minutes) VALUES (?,?)',[$marker,90]);
    $movie=(int)$pdo->lastInsertId();
    query('INSERT INTO reviews (movie_id,user_id,stars) VALUES (?,?,?)',[$movie,$u1,5]);
    rejected(fn()=>query('INSERT INTO reviews (movie_id,user_id,stars) VALUES (?,?,?)',[$movie,$u1,3]),[1062],'Duplicate review rejected');
    rejected(fn()=>query('INSERT INTO reviews (movie_id,user_id,stars) VALUES (?,?,?)',[$movie,$u2,6]),[3819,4025],'Six-star review rejected');
    rejected(fn()=>query('UPDATE users SET role_id=0 WHERE user_id=?',[$u1]),[1452],'Unknown role foreign key rejected');
    rejected(fn()=>query('INSERT INTO credentials (user_id,username,password_hash) VALUES (?,?,?)',[$u1,$marker.'c','test']),[1062],'Second credentials row rejected');
    query('INSERT INTO reviews (movie_id,user_id,stars) VALUES (?,?,?)',[$movie,$u2,1]);
    $s=query('SELECT * FROM movie_rating_stats WHERE movie_id=?',[$movie])->fetch();
    assert_true((float)$s['average_rating']===3.0 && (int)$s['review_count']===2,'Average and count are correct');
    query('UPDATE reviews SET active=0 WHERE movie_id=? AND user_id=?',[$movie,$u2]);
    $s=query('SELECT * FROM movie_rating_stats WHERE movie_id=?',[$movie])->fetch();
    assert_true((float)$s['average_rating']===5.0 && (int)$s['review_count']===1,'Hidden review excluded from average');
    query('INSERT INTO favorites (user_id,movie_id) VALUES (?,?)',[$u1,$movie]);
    rejected(fn()=>query('INSERT INTO favorites (user_id,movie_id) VALUES (?,?)',[$u1,$movie]),[1062],'Duplicate favorite rejected');
    query('INSERT INTO forums (movie_id,user_id,forum_title) VALUES (?,?,?)',[$movie,$u1,$marker]);
    $forum=(int)$pdo->lastInsertId();
    query('INSERT INTO forum_votes (forum_id,user_id,vote_value) VALUES (?,?,?)',[$forum,$u1,1]);
    rejected(fn()=>query('INSERT INTO forum_votes (forum_id,user_id,vote_value) VALUES (?,?,?)',[$forum,$u1,-1]),[1062],'Second vote for the same thread rejected');
    rejected(fn()=>query('INSERT INTO forum_votes (forum_id,user_id,vote_value) VALUES (?,?,?)',[$forum,$u2,0]),[3819,4025],'Zero-valued vote rejected');
    assert_true((int)query('SELECT COUNT(*) FROM movies WHERE movie_id=? AND runtime_minutes<=90',[$movie])->fetchColumn()===1,'Runtime boundary includes exactly 90 minutes');
    query('DELETE FROM movies WHERE movie_id=?',[$movie]);
    assert_true((int)query('SELECT COUNT(*) FROM reviews WHERE movie_id=?',[$movie])->fetchColumn()===0,'Deleting movie cascades reviews');
    assert_true(query('SELECT movie_id FROM forums WHERE forum_id=?',[$forum])->fetch()['movie_id']===null,'Deleting movie preserves discussion with NULL movie');
    $pdo->rollBack(); echo 'All checks passed. Fixture writes rolled back.'.PHP_EOL;
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR,$ex->getMessage().PHP_EOL); exit(1);
}
