<?php
session_start();

// --- 1. INISIALISASI GAME STATE ---
if (!isset($_SESSION['game_started']) || isset($_POST['restart'])) {
    $_SESSION['game_started'] = true;
    $_SESSION['wongi'] = [
        'nama' => 'Wongi',
        'hp' => 100,
        'max_hp' => 100,
        'bark_damage' => 25,
        'tulang' => 10,
        'kostum' => 'Default (Polos)',
        'sniff' => false
    ];
    $_SESSION['boss'] = [
        'nama' => 'Prabu Celeng 🐗',
        'hp' => 100,
        'max_hp' => 100,
        'damage' => 15
    ];
    $_SESSION['log'] = ["Selamat Datang di Desa Gemah Ripah! Prabu Celeng mencuri Tulang Emas. Ayo rebut kembali!"];
}

$wongi = &$_SESSION['wongi'];
$boss = &$_SESSION['boss'];
$log = &$_SESSION['log'];

// --- 2. LOGIKA AKSI PEMAIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Aksi: Bark Attack
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'bark' && $boss['hp'] > 0 && $wongi['hp'] > 0) {
        // Wongi Serang Boss
        $boss['hp'] -= $wongi['bark_damage'];
        array_unshift($log, "🐾 Wongi menggunakan *BARK ATTACK*! 'WOOF WOOF!' Prabu Celeng terkena {$wongi['bark_damage']} DMG.");
        
        // Jika Boss belum mati, Boss serang balik
        if ($boss['hp'] > 0) {
            $wongi['hp'] -= $boss['damage'];
            array_unshift($log, "🐗 Prabu Celeng menyerang balik! Wongi kehilangan {$boss['damage']} HP.");
        } else {
            $boss['hp'] = 0;
            array_unshift($log, "🎉 Prabu Celeng tumbang! Wongi berhasil mendapatkan kembali TULANG EMAS SAKRAL!");
        }
        
        if ($wongi['hp'] <= 0) {
            $wongi['hp'] = 0;
            array_unshift($log, "😭 Wongi pingsan... Game Over. Desa Gemah Ripah tetap kekeringan.");
        }
    }

    // Aksi: Sniff Mode (Mengendus Tulang)
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'sniff' && $wongi['hp'] > 0) {
        $wongi['sniff'] = !$wongi['sniff'];
        if ($wongi['sniff']) {
            $dapat_tulang = rand(15, 30);
            $wongi['tulang'] += $dapat_tulang;
            array_unshift($log, "👃 Sniff Mode Aktif! Wongi melihat jejak emas, menggali tanah, dan menemukan 🦴 {$dapat_tulang} Tulang Perunggu!");
        } else {
            array_unshift($log, "👃 Sniff Mode Dinonaktifkan. Pandangan Wongi kembali normal.");
        }
    }

    // Aksi: Toko Mbok Sri (Beli Blangkon)
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'beli_blangkon' && $wongi['hp'] > 0) {
        if ($wongi['tulang'] >= 40) {
            $wongi['tulang'] -= 40;
            $wongi['kostum'] = "Segawon Blangkon 🧑‍";
            $wongi['bark_damage'] += 10; // Bonus damage dari kostum keren
            array_unshift($log, "🛍️ Membeli Blangkon dari Mbok Sri! Damage Gonggongan Wongi meningkat (+10 DMG)!");
        } else {
            array_unshift($log, "❌ Tulang Perunggu tidak cukup! Mbok Sri menolak berutang.");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Segawon Browser Engine</title>
    <style>
        body {
            background-color: #1a1a24;
            color: #e0e0e0;
            font-family: 'Courier New', Courier, monospace;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .game-container {
            width: 100%;
            max-width: 700px;
            background: #252632;
            border: 4px solid #474a5e;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        h1 { text-align: center; color: #ffbc42; margin-top: 0; text-shadow: 2px 2px #000; }
        .screen {
            background: <?= $wongi['sniff'] ? '#2b2b2b' : '#34495e' ?>;
            border: 2px solid #1a252f;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        .stats-grid { display: flex; justify-content: space-between; }
        .char-box { width: 45%; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 5px; }
        .hp-bar { background: #c0392b; height: 15px; border-radius: 3px; overflow: hidden; margin-top: 5px; }
        .hp-fill { background: #2ecc71; height: 100%; transition: width 0.3s; }
        .controls { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        button {
            background: #ffbc42; color: #1a1a24; border: none; padding: 12px; font-weight: bold;
            font-family: inherit; cursor: pointer; border-radius: 4px; box-shadow: 0 4px #d69615;
        }
        button:active { transform: translateY(3px); box-shadow: 0 1px #d69615; }
        button:disabled { background: #555; color: #888; box-shadow: none; cursor: not-allowed; }
        .btn-danger { background: #e74c3c; color: white; box-shadow: 0 4px #c0392b; }
        .btn-danger:active { box-shadow: 0 1px #c0392b; }
        .log-box {
            background: #111; height: 150px; overflow-y: auto; padding: 10px;
            border-radius: 4px; border: 1px solid #444; font-size: 13px;
        }
        .log-entry { margin-bottom: 8px; border-bottom: 1px solid #222; padding-bottom: 4px; }
    </style>
</head>
<body>

<div class="game-container">
    <h1>🐕 SUPER SEGAWON 🐕</h1>
    
    <!-- AUDIO VISUAL SCREEN SIMULATION -->
    <div class="screen">
        <div class="stats-grid">
            <!-- STATUS WONGI -->
            <div class="char-box">
                <strong>🐾 <?= $wongi['nama'] ?></strong> <small>(<?= $wongi['kostum'] ?>)</small>
                <div class="hp-bar">
                    <div class="hp-fill" style="width: <?= ($wongi['hp'] / $wongi['max_hp']) * 100 ?>%"></div>
                </div>
                <div style="font-size:12px; margin-top:3px;">HP: <?= $wongi['hp'] ?>/<?= $wongi['max_hp'] ?></div>
                <div style="font-size:12px;">🦴 Tulang: <?= $wongi['tulang'] ?></div>
                <div style="font-size:12px;">💥 Power: <?= $wongi['bark_damage'] ?> DMG</div>
            </div>

            <!-- STATUS BOSS -->
            <div class="char-box" style="text-align: right;">
                <strong><?= $boss['nama'] ?></strong>
                <div class="hp-bar">
                    <div class="hp-fill" style="width: <?= ($boss['hp'] / $boss['max_hp']) * 100 ?>%; background: #e74c3c;"></div>
                </div>
                <div style="font-size:12px; margin-top:3px;">HP: <?= $boss['hp'] ?>/<?= $boss['max_hp'] ?></div>
                <div style="font-size:12px; color: #e74c3c;">Zona: Benteng Akhir</div>
            </div>
        </div>
    </div>

    <!-- PANEL KENDALI (ACTION BUTTONS) -->
    <form method="POST">
        <div class="controls">
            <!-- Tombol Bark Attack -->
            <button type="submit" name="aksi" value="bark" <?= ($wongi['hp'] <= 0 || $boss['hp'] <= 0) ? 'disabled' : '' ?>>
                🔊 Bark Attack (Woof!)
            </button>
            
            <!-- Tombol Sniff Mode -->
            <button type="submit" name="aksi" value="sniff" <?= ($wongi['hp'] <= 0 || $boss['hp'] <= 0) ? 'disabled' : '' ?> style="background: #3498db; color: white; box-shadow: 0 4px #2980b9;">
                👃 <?= $wongi['sniff'] ? 'Matikan Sniff' : 'Sniff Mode (Cari Tulang)' ?>
            </button>
            
            <!-- Warung Mbok Sri -->
            <button type="submit" name="aksi" value="beli_blangkon" <?= ($wongi['hp'] <= 0 || $boss['hp'] <= 0 || $wongi['tulang'] < 40) ? 'disabled' : '' ?> style="background: #2ecc71; color: white; box-shadow: 0 4px #27ae60;">
                🧑‍ Beli Blangkon (40 Tulang)
            </button>

            <!-- Reset Game -->
            <button type="submit" name="restart" class="btn-danger">
                🔄 Mulai Ulang Petualangan
            </button>
        </div>
    </form>

    <!-- LOG PERMAINAN (CONSOLE GAME) -->
    <h3>📜 Log Petualangan:</h3>
    <div class="log-box">
        <?php foreach ($log as $l): ?>
            <div class="log-entry"><?= htmlspecialchars($l) ?></div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
