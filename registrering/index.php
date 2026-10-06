<?php

require __DIR__ . '/db.php';

session_start();

$pdo = db();
$status = '';
$aktiviteter = $pdo->query('SELECT * FROM aktiviteter ORDER BY id')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $navn = trim($_POST['name'] ?? '');
    $gyldige = array_column($aktiviteter, 'id');
    $valgte = array_values(array_intersect(array_map('intval', (array) ($_POST['aktiviteter'] ?? [])), $gyldige));

    if ($navn === '' || !$valgte) {
        $status = 'Fyll inn navn og velg minst én dato.';
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO gjest (navn) VALUES (?)')->execute([$navn]);
            $gjestId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO paamelding (idNavn, idAktivitet) VALUES (?, ?)');
            foreach ($valgte as $id) {
                $stmt->execute([$gjestId, $id]);
            }
            $pdo->commit();

            $_SESSION['velkommen'] = $navn;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $ex) {
            $pdo->rollBack();
            error_log($ex->getMessage());
            $status = 'Problemer med å lagre. Sjekk om navnet allerede er registrert, og prøv eventuelt med suffix/etternavn.';
        }
    }
}

$velkommen = $_SESSION['velkommen'] ?? null;
unset($_SESSION['velkommen']);
if ($velkommen !== null) {
    $status = 'Påmeldingen er lagret!';
}

$gjester = $pdo->query('SELECT id, navn FROM gjest ORDER BY id')->fetchAll();
$paameldinger = [];
foreach ($pdo->query('SELECT idNavn, idAktivitet FROM paamelding') as $row) {
    $paameldinger[$row['idNavn']][$row['idAktivitet']] = true;
}
?>
<!doctype html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Afternoon Tea!</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container">
    <section class="hero">
      <p class="eyebrow">Afternoon tea 🎉</p>
      <h1>Afternoon tea</h1>
      <p>Tusen takk for den fantastiske bursdagspresangen. Jeg gleder meg til å treffes til Afternoon tea!</p>
      <p>For å gjøre det enklere med å finne en dag det passer for alle /flest mulig, så kan dere regisrere de lørdagene som passer. så håper jeg at vi finner en dag som passer for alle. PS. lenger ned på siden kan du se alle regisrerte datoer som andre har signet opp for. Jeg gleder meg til å treffes!</p>
    </section>

    <section class="card">
      <h2>Registrer deg</h2>

      <?php if ($velkommen !== null): ?>
        <div id="welcome-modal">
          <div class="modal-content">
            <h2>🎉 Gleder meg!</h2>
            <p>Gleder meg til å se deg, <?= e($velkommen) ?>!</p>
            <img src="hilde.jpg" alt="">
            <a class="button" href="<?= e($_SERVER['PHP_SELF']) ?>">Lukk</a>
          </div>
        </div>
      <?php endif; ?>

      <form method="post">
        <input name="name" placeholder="Navn" required>

        <div id="activity-options">
          <?php foreach ($aktiviteter as $a): ?>
            <label class="activity-option">
              <input type="checkbox" name="aktiviteter[]" value="<?= (int) $a['id'] ?>" <?= $a['id'] == 3 ? 'disabled' : '' ?>>
              <span><?= e($a['aktivitet']) ?></span>
              <span class="info" title="<?= e($a['beskrivelse']) ?>">ⓘ</span>
            </label>
          <?php endforeach; ?>
        </div>

        <button type="submit">Registrer datoer</button>
      </form>

      <p id="status"><?= e($status) ?></p>
    </section>

    <section class="card">
      <div class="section-header">
        <h2>Registrerte datoer</h2>
        <a class="button secondary" href="<?= e($_SERVER['PHP_SELF']) ?>">Oppdater</a>
      </div>

      <div class="responses">
        <?php if (!$gjester): ?>
          <p>Ingen påmeldinger ennå.</p>
        <?php else: ?>
          <table class="responses-table">
            <thead>
              <tr>
                <th>Navn</th>
                <?php foreach ($aktiviteter as $a): ?>
                  <th><?= e($a['aktivitet']) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($gjester as $g): ?>
                <tr>
                  <td><strong><?= e($g['navn']) ?></strong></td>
                  <?php foreach ($aktiviteter as $a): ?>
                    <td class="alignMiddle"><?= isset($paameldinger[$g['id']][$a['id']]) ? '✓' : '' ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>
