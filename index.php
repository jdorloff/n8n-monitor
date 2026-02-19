<?php
session_start();
require_once __DIR__ . '/config.php';
$error = '';
if (isset($_GET['logout'])) { session_destroy(); header('Location: /n8n/'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    if ($_POST['username'] === AUTH_USERNAME && $_POST['password'] === AUTH_PASSWORD) {
        $_SESSION['n8n_logged_in'] = true; header('Location: /n8n/'); exit;
    } else { $error = 'Invalid username or password.'; }
}
if (!isset($_SESSION['n8n_logged_in'])) { ?>
<!DOCTYPE html><html><head><title>n8n Monitor</title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:-apple-system,sans-serif;background:#0f0f1a;color:#e0e0e0;display:flex;align-items:center;justify-content:center;min-height:100vh}.box{background:#13131f;border:1px solid #222235;border-radius:12px;padding:40px;width:360px}h2{font-size:20px;font-weight:700;color:#fff;margin-bottom:4px}.sub{font-size:12px;color:#444;margin-bottom:28px}label{display:block;font-size:12px;font-weight:600;color:#888;margin-bottom:5px}input{width:100%;padding:10px 14px;background:#0f0f1a;border:1px solid #222235;border-radius:8px;color:#e0e0e0;font-size:14px;margin-bottom:14px}input:focus{outline:none;border-color:#6c5ce7}button{width:100%;padding:11px;background:#6c5ce7;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}.err{color:#ef4444;font-size:13px;margin-bottom:14px;padding:10px;background:rgba(239,68,68,.1);border-radius:6px}</style>
</head><body><div class="box"><h2>n8n Monitor</h2><div class="sub">PushkinVoice</div>
<?php if($error):?><div class="err"><?=htmlspecialchars($error)?></div><?php endif?>
<form method="POST"><label>Username</label><input type="text" name="username" autofocus><label>Password</label><input type="password" name="password"><button type="submit">Sign In</button></form>
</div></body></html>
<?php exit; } ?>
<!DOCTYPE html>
<html>
<head>
<title>n8n Monitor</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f0f1a;color:#e0e0e0;display:flex;height:100vh;overflow:hidden;font-size:14px}

/* SIDEBAR */
.sidebar{width:210px;flex-shrink:0;background:#13131f;border-right:1px solid #222235;display:flex;flex-direction:column;height:100vh;overflow:hidden}
.sb-head{padding:16px 14px;border-bottom:1px solid #222235;flex-shrink:0}
.sb-head h1{font-size:14px;font-weight:700;color:#fff}
.sb-head p{font-size:11px;color:#444;margin-top:2px}
.sb-nav{flex:1;overflow-y:auto;padding:8px}
.sb-label{font-size:10px;font-weight:700;color:#444;text-transform:uppercase;letter-spacing:.8px;padding:8px 8px 4px}
.sb-item{display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-radius:6px;cursor:pointer;font-size:13px;color:#888;margin-bottom:1px}
.sb-item:hover{background:#1a1a2e;color:#ccc}
.sb-item.active{background:rgba(108,92,231,.15);color:#6c5ce7;font-weight:600}
.sb-badge{background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:1px 5px;border-radius:8px}
.sb-del{background:none;border:none;color:transparent;cursor:pointer;font-size:15px;padding:0;line-height:1}
.sb-item:hover .sb-del{color:#555}
.sb-item:hover .sb-del:hover{color:#ef4444}
.sb-divider{height:1px;background:#1e1e30;margin:6px 0}
.sb-add{display:flex;gap:6px;padding:6px 8px;flex-shrink:0;border-top:1px solid #222235}
.sb-add input{flex:1;min-width:0;background:#0f0f1a;border:1px solid #222235;color:#e0e0e0;border-radius:6px;padding:6px 8px;font-size:12px}
.sb-add input:focus{outline:none;border-color:#6c5ce7}
.sb-add button{background:#6c5ce7;border:none;color:#fff;border-radius:6px;padding:6px 10px;font-size:12px;cursor:pointer;font-weight:600;white-space:nowrap}
.sb-foot{padding:8px;flex-shrink:0;border-top:1px solid #222235}
.sb-foot button{width:100%;padding:7px;font-size:12px;border:1px solid #222235;border-radius:6px;background:transparent;color:#555;cursor:pointer}
.sb-foot button:hover{color:#ccc;background:#1a1a2e}

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}
.topbar{padding:13px 20px;border-bottom:1px solid #222235;background:#13131f;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.topbar h2{font-size:15px;font-weight:700;color:#fff}
.topbar-r{display:flex;align-items:center;gap:10px}
.ts{font-size:11px;color:#444}
.ref-btn{padding:5px 12px;font-size:12px;border:1px solid #222235;border-radius:6px;background:transparent;color:#666;cursor:pointer}
.ref-btn:hover{background:#1a1a2e;color:#ccc}
.tab-bar{display:flex;gap:0;padding:0 20px;background:#13131f;border-bottom:1px solid #222235;flex-shrink:0}
.tab{padding:10px 16px;font-size:13px;font-weight:600;cursor:pointer;color:#444;border-bottom:2px solid transparent;margin-bottom:-1px}
.tab:hover{color:#888}
.tab.active{color:#6c5ce7;border-bottom-color:#6c5ce7}
.content{flex:1;overflow-y:auto;padding:16px 20px}

/* FILTER BAR */
.fbar{display:flex;gap:8px;margin-bottom:14px;align-items:center;flex-wrap:wrap}
.fbar input,.fbar select{background:#13131f;border:1px solid #222235;color:#ccc;border-radius:6px;padding:6px 10px;font-size:13px;cursor:pointer}
.fbar input{width:200px;cursor:text}
.fbar input:focus,.fbar select:focus{outline:none;border-color:#6c5ce7}
.fcount{font-size:12px;color:#444;margin-left:auto}

/* TABLE */
.tbl-wrap{background:#13131f;border:1px solid #222235;border-radius:8px;overflow:hidden}
table{width:100%;border-collapse:collapse}
thead th{padding:9px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#444;border-bottom:1px solid #222235;background:#0f0f1a;white-space:nowrap}
tbody tr{border-bottom:1px solid #1a1a28}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#191928}
tbody td{padding:9px 14px;font-size:13px;color:#aaa;vertical-align:middle}
.wfname{font-weight:500;color:#ddd}
.pill{display:inline-block;padding:2px 8px;border-radius:8px;font-size:11px;font-weight:700}
.p-success{background:rgba(34,197,94,.1);color:#22c55e}
.p-failed{background:rgba(239,68,68,.1);color:#ef4444}
.p-enabled{background:rgba(34,197,94,.1);color:#22c55e}
.p-disabled{background:rgba(100,100,100,.1);color:#555}
.gtag{display:inline-block;padding:2px 7px;border-radius:4px;font-size:11px;background:rgba(108,92,231,.15);color:#8b7cf8}
.mode-txt{font-size:11px;color:#444}
.dur-txt{font-size:12px;color:#444;font-family:monospace}
.time-txt{font-size:12px;color:#555}
.empty{text-align:center;padding:50px;color:#444}
.grp-sel{background:#0f0f1a;border:1px solid #222235;color:#777;border-radius:4px;padding:3px 6px;font-size:12px;cursor:pointer}
.grp-sel:focus{outline:none;border-color:#6c5ce7}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sb-head">
        <h1>n8n Monitor</h1>
        <p>PushkinVoice</p>
    </div>
    <div class="sb-nav">
        <div class="sb-label">Views</div>
        <div class="sb-item" id="nav-wf" onclick="setView('wf')">Workflows</div>
        <div class="sb-item active" id="nav-exec" onclick="setView('exec')">Executions <span class="sb-badge" id="fail-badge" style="display:none"></span></div>
        <div class="sb-divider"></div>
        <div class="sb-label">Groups</div>
        <div class="sb-item active" id="grp-all" onclick="setGroup(null)">All</div>
        <div id="grp-list"></div>
    </div>
    <div class="sb-add">
        <input type="text" id="new-grp" placeholder="New group..." onkeydown="if(event.key==='Enter')addGroup()">
        <button onclick="addGroup()">Add</button>
    </div>
    <div class="sb-foot">
        <button onclick="location.href='?logout'">Logout</button>
    </div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h2 id="page-title">Executions</h2>
        <div class="topbar-r">
            <span class="ts" id="ts"></span>
            <button class="ref-btn" onclick="loadData()">↺ Refresh</button>
        </div>
    </div>

    <div class="tab-bar" id="tab-bar">
        <div class="tab active" onclick="setStatus('all',this)">All</div>
        <div class="tab" onclick="setStatus('success',this)">Success</div>
        <div class="tab" onclick="setStatus('failed',this)">Failed</div>
    </div>

    <div class="content">

        <!-- EXECUTIONS VIEW -->
        <div id="view-exec">
            <div class="fbar">
                <input type="text" id="s-exec" placeholder="Search workflow..." oninput="renderExec()">
                <select id="f-exec-status" onchange="syncStatusFromSelect()">
                    <option value="all">All Statuses</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
                <select id="f-exec-assign" onchange="renderExec()">
                    <option value="all">All</option>
                    <option value="assigned">Assigned</option>
                    <option value="unassigned">Unassigned</option>
                </select>
                <select id="f-exec-group" onchange="renderExec()">
                    <option value="">All Groups</option>
                </select>
                <span class="fcount" id="exec-count"></span>
            </div>
            <div class="tbl-wrap">
                <table>
                    <thead><tr><th>Workflow</th><th>Group</th><th>Status</th><th>Mode</th><th>Started</th><th>Duration</th></tr></thead>
                    <tbody id="exec-body"><tr><td colspan="6" class="empty">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- WORKFLOWS VIEW -->
        <div id="view-wf" style="display:none">
            <div class="fbar">
                <input type="text" id="s-wf" placeholder="Search workflow..." oninput="renderWf()">
                <select id="f-wf-assign" onchange="renderWf()">
                    <option value="all">All</option>
                    <option value="assigned">Assigned</option>
                    <option value="unassigned">Unassigned</option>
                </select>
                <select id="f-wf-group" onchange="renderWf()">
                    <option value="">All Groups</option>
                </select>
                <span class="fcount" id="wf-count"></span>
            </div>
            <div class="tbl-wrap">
                <table>
                    <thead><tr><th>Workflow</th><th>Enabled</th><th>Last Run</th><th>Group</th></tr></thead>
                    <tbody id="wf-body"><tr><td colspan="4" class="empty">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
const TK='n8nmon_pushkin_2026';
let execs=[], groups=[], workflows=[], groupFilter=null, statusFilter='all', curView='exec', timer;

// ── LOAD ──
async function loadData(){
    try{
        const r=await fetch(`api.php?action=executions&limit=50&token=${TK}`);
        const d=await r.json();
        if(d.error){document.getElementById('ts').textContent='Error: '+d.error;return;}
        execs=d.executions||[];
        groups=d.groups||[];
        const fc=d.failure_count||0;
        const b=document.getElementById('fail-badge');
        b.textContent=fc; b.style.display=fc>0?'':'none';
        renderGroupList();
        populateGroupDropdowns();
        renderExec();
        document.getElementById('ts').textContent='Updated '+new Date().toLocaleTimeString();
    }catch(e){document.getElementById('ts').textContent='Load error';}
    clearTimeout(timer); timer=setTimeout(loadData,60000);
}

async function loadWorkflows(){
    const r=await fetch(`api.php?action=workflows&token=${TK}`);
    workflows=await r.json();
    renderWf();
}

// ── VIEW SWITCH ──
function setView(v){
    curView=v;
    document.getElementById('view-exec').style.display=v==='exec'?'':'none';
    document.getElementById('view-wf').style.display=v==='wf'?'':'none';
    document.getElementById('tab-bar').style.display=v==='exec'?'':'none';
    document.getElementById('page-title').textContent=v==='exec'?'Executions':'Workflows';
    document.getElementById('nav-exec').classList.toggle('active',v==='exec');
    document.getElementById('nav-wf').classList.toggle('active',v==='wf');
    if(v==='wf' && workflows.length===0) loadWorkflows();
    else if(v==='wf') renderWf();
}

// ── STATUS ──
function setStatus(s,el){
    statusFilter=s;
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    el.classList.add('active');
    const sel=document.getElementById('f-exec-status');
    if(sel) sel.value=s;
    renderExec();
}
function syncStatusFromSelect(){
    const s=document.getElementById('f-exec-status').value;
    statusFilter=s;
    document.querySelectorAll('.tab').forEach(t=>{
        t.classList.toggle('active', t.textContent.toLowerCase()===s||(s==='all'&&t.textContent==='All'));
    });
    renderExec();
}

// ── GROUP SIDEBAR ──
function renderGroupList(){
    const gmap=buildGmap();
    const c=document.getElementById('grp-list');
    c.innerHTML='';
    groups.forEach(g=>{
        const gids=new Set((g.workflows||[]).map(w=>w.id));
        const fails=execs.filter(e=>gids.has(e.workflow_id)&&e.status==='failed').length;
        const d=document.createElement('div');
        d.className='sb-item'+(groupFilter===g.name?' active':'');
        d.innerHTML=`<span>${esc(g.name)}</span>
            <span style="display:flex;align-items:center;gap:4px">
                ${fails>0?`<span class="sb-badge">${fails}</span>`:''}
                <button class="sb-del" onclick="event.stopPropagation();deleteGroup('${esc(g.name)}')">×</button>
            </span>`;
        d.onclick=()=>setGroup(g.name);
        c.appendChild(d);
    });
}

function setGroup(name){
    groupFilter=name;
    document.getElementById('grp-all').classList.toggle('active',name===null);
    document.querySelectorAll('#grp-list .sb-item').forEach(el=>{
        el.classList.toggle('active',el.querySelector('span')?.textContent.trim()===name);
    });
    renderExec();
    if(curView==='wf') renderWf();
}

function populateGroupDropdowns(){
    const opts='<option value="">All Groups</option>'+groups.map(g=>`<option value="${esc(g.name)}">${esc(g.name)}</option>`).join('');
    ['f-exec-group','f-wf-group'].forEach(id=>{
        const el=document.getElementById(id);
        if(!el) return;
        const cur=el.value;
        el.innerHTML=opts;
        el.value=cur;
    });
}

// ── EXEC TABLE ──
function buildGmap(){
    const m={};
    groups.forEach(g=>(g.workflows||[]).forEach(w=>{m[w.id]=g.name;}));
    return m;
}

function renderExec(){
    const search=(document.getElementById('s-exec').value||'').toLowerCase();
    const assignF=document.getElementById('f-exec-assign')?.value||'all';
    const groupSel=document.getElementById('f-exec-group')?.value||'';
    const gmap=buildGmap();

    // sidebar group filter
    let sideGids=null;
    if(groupFilter){
        const g=groups.find(g=>g.name===groupFilter);
        sideGids=new Set((g?.workflows||[]).map(w=>w.id));
    }

    let rows=execs.filter(e=>{
        if(sideGids && !sideGids.has(e.workflow_id)) return false;
        if(statusFilter!=='all' && e.status!==statusFilter) return false;
        if(search && !e.workflow_name.toLowerCase().includes(search)) return false;
        const grp=gmap[e.workflow_id]||'';
        if(assignF==='assigned' && !grp) return false;
        if(assignF==='unassigned' && grp) return false;
        if(groupSel && grp!==groupSel) return false;
        return true;
    });

    document.getElementById('exec-count').textContent=rows.length+' executions';
    const tbody=document.getElementById('exec-body');
    if(!rows.length){tbody.innerHTML='<tr><td colspan="6" class="empty">No executions found.</td></tr>';return;}

    tbody.innerHTML=rows.map(e=>{
        const sc=e.status==='success'?'p-success':'p-failed';
        const grp=gmap[e.workflow_id];
        const grpCell=grp?`<span class="gtag">${esc(grp)}</span>`:'<span style="color:#2a2a3e">—</span>';
        const started=e.started_at?new Date(e.started_at).toLocaleString():'-';
        const dur=e.duration_sec!=null?fmtDur(e.duration_sec):'-';
        return `<tr>
            <td class="wfname">${esc(e.workflow_name)}</td>
            <td>${grpCell}</td>
            <td><span class="pill ${sc}">${e.status}</span></td>
            <td class="mode-txt">${e.mode||'-'}</td>
            <td class="time-txt">${started}</td>
            <td class="dur-txt">${dur}</td>
        </tr>`;
    }).join('');
}

// ── WF TABLE ──
function renderWf(){
    const search=(document.getElementById('s-wf').value||'').toLowerCase();
    const assignF=document.getElementById('f-wf-assign')?.value||'all';
    const groupSel=document.getElementById('f-wf-group')?.value||'';
    const gmap=buildGmap();

    // last run map
    const lr={};
    execs.forEach(e=>{if(!lr[e.workflow_id]||e.started_at>lr[e.workflow_id])lr[e.workflow_id]=e.started_at;});

    let sideGids=null;
    if(groupFilter){
        const g=groups.find(g=>g.name===groupFilter);
        sideGids=new Set((g?.workflows||[]).map(w=>w.id));
    }

    let rows=workflows.filter(w=>{
        if(sideGids && !sideGids.has(w.id)) return false;
        if(search && !w.name.toLowerCase().includes(search)) return false;
        const grp=gmap[w.id]||'';
        if(assignF==='assigned' && !grp) return false;
        if(assignF==='unassigned' && grp) return false;
        if(groupSel && grp!==groupSel) return false;
        return true;
    });

    document.getElementById('wf-count').textContent=rows.length+' workflows';
    const tbody=document.getElementById('wf-body');
    if(!rows.length){tbody.innerHTML='<tr><td colspan="4" class="empty">No workflows found.</td></tr>';return;}

    const gopts=groups.map(g=>`<option value="${esc(g.name)}">${esc(g.name)}</option>`).join('');

    tbody.innerHTML=rows.map(w=>{
        const sc=w.active?'p-enabled':'p-disabled';
        const lbl=w.active?'Enabled':'Disabled';
        const lrStr=lr[w.id]?new Date(lr[w.id]).toLocaleString():'—';
        const grp=gmap[w.id]||'';
        const grpDisp=grp?`<span class="gtag" style="margin-right:6px">${esc(grp)}</span>`:'';
        const rmOpt=grp?`<option value="__remove__">Remove from group</option>`:'';
        const safeName=w.name.replace(/\\/g,'\\\\').replace(/'/g,"\\'");
        return `<tr>
            <td class="wfname">${esc(w.name)}</td>
            <td><span class="pill ${sc}">${lbl}</span></td>
            <td class="time-txt">${lrStr}</td>
            <td>${grpDisp}<select class="grp-sel" onchange="assignGroup('${w.id}','${safeName}',this)">
                <option value="">Assign group...</option>
                ${gopts}${rmOpt}
            </select></td>
        </tr>`;
    }).join('');
}

// ── GROUP CRUD ──
async function addGroup(){
    const inp=document.getElementById('new-grp');
    const name=inp.value.trim(); if(!name)return;
    const r=await fetch(`groups.php?group_action=add_group&token=${TK}`,{method:'POST',body:new URLSearchParams({name})});
    const d=await r.json();
    if(d.success){inp.value='';await loadData();populateGroupDropdowns();if(curView==='wf')renderWf();}
}

async function deleteGroup(name){
    if(!confirm(`Delete group "${name}"?`))return;
    await fetch(`groups.php?group_action=delete_group&token=${TK}`,{method:'POST',body:new URLSearchParams({name})});
    if(groupFilter===name)groupFilter=null;
    await loadData();
    populateGroupDropdowns();
    if(curView==='wf')renderWf();
}

async function assignGroup(wid,wname,sel){
    const val=sel.value; if(!val)return;
    if(val==='__remove__'){
        await fetch(`groups.php?group_action=remove_workflow&token=${TK}`,{method:'POST',body:new URLSearchParams({workflow_id:wid})});
    } else {
        await fetch(`groups.php?group_action=add_workflow&token=${TK}`,{method:'POST',body:new URLSearchParams({group_name:val,workflow_id:wid,workflow_name:wname})});
    }
    sel.value='';
    await loadData();
    populateGroupDropdowns();
    renderWf();
}

function fmtDur(s){return s<60?s+'s':Math.floor(s/60)+'m '+(s%60)+'s';}
function esc(s){if(!s)return'';const d=document.createElement('div');d.textContent=s;return d.innerHTML;}

loadData();
</script>
</body>
</html>
