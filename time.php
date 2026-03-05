<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNP Admin · Activity & uploads (frontend)</title>
    <!-- Font Awesome 5 for icons (optional but clean) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
        }
        body {
            background: #eef2f5;
            display: flex;
            min-height: 100vh;
        }
        /* --- SIDEBAR (exactly like image + logout) --- */
        .sidebar {
            width: 260px;
            background: #0b2b4f; /* dark navy typical for PNP admin */
            color: #ecf0f1;
            display: flex;
            flex-direction: column;
            padding: 2rem 0 1rem 0;
            box-shadow: 4px 0 12px rgba(0,0,0,0.1);
        }
        .sidebar h2 {
            font-size: 1.5rem;
            font-weight: 600;
            padding: 0 1.5rem 1.2rem 1.5rem;
            border-bottom: 1px solid #2c4b6c;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar h2 i {
            color: #ffc107;
        }
        .sidebar ul {
            list-style: none;
            margin-top: 1.5rem;
            flex: 1;
        }
        .sidebar ul li {
            padding: 0.85rem 1.5rem;
            margin: 0.2rem 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #d3e2f0;
            cursor: default;
            transition: 0.2s;
        }
        .sidebar ul li i {
            width: 24px;
            font-size: 1.2rem;
            color: #8fb4d3;
        }
        .sidebar ul li:hover {
            background: #1d3f60;
            color: white;
        }
        .sidebar ul li:last-child {
            margin-top: 2rem;
            border-top: 1px solid #2c4b6c;
            padding-top: 1.2rem;
            color: #f1b9b9;
        }
        .sidebar ul li:last-child i {
            color: #f1b9b9;
        }

        /* --- MAIN CONTENT (dashboard + new activity cards) --- */
        .main {
            flex: 1;
            padding: 1.8rem 2.2rem;
            overflow-y: auto;
        }
        .top-welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .top-welcome h1 {
            font-size: 1.9rem;
            font-weight: 500;
            color: #1a2b3c;
        }
        .top-welcome p {
            color: #4a5a6a;
            background: white;
            padding: 0.3rem 1.2rem;
            border-radius: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        /* summary cards (same as image but enhanced) */
        .summary-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin: 2rem 0 2.5rem 0;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 1.3rem 2rem 1.3rem 1.8rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.02), 0 6px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            min-width: 170px;
            gap: 1.2rem;
            border: 1px solid rgba(0,0,0,0.03);
        }
        .stat-icon {
            font-size: 2.2rem;
            color: #1e4a76;
        }
        .stat-content h3 {
            font-weight: 400;
            font-size: 0.95rem;
            color: #5f6c7a;
            letter-spacing: 0.3px;
        }
        .stat-content .number {
            font-size: 2rem;
            font-weight: 600;
            color: #0b2b4f;
            line-height: 1.2;
        }
        .patrol-stats {
            background: white;
            border-radius: 28px;
            padding: 1.4rem 2rem;
            display: flex;
            gap: 2.5rem;
            box-shadow: 0 8px 18px rgba(0,0,0,0.03);
            border: 1px solid #f0f3f7;
        }
        .patrol-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .patrol-item span:first-child {
            font-size: 1.8rem;
            font-weight: 600;
            color: #003366;
        }
        .patrol-item span:last-child {
            font-size: 0.85rem;
            color: #4e6b82;
            margin-top: 4px;
        }

        /* ----- ADD ACTIVITY SECTION (USER: patrol / checkpoint / oplan + picture) ----- */
        .section-title {
            font-size: 1.6rem;
            font-weight: 500;
            margin: 2.5rem 0 1.5rem 0;
            color: #132b41;
            border-left: 6px solid #ffc107;
            padding-left: 1rem;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .activity-card {
            background: white;
            border-radius: 28px;
            padding: 1.8rem 1.8rem 2rem 1.8rem;
            box-shadow: 0 12px 24px -8px rgba(0, 40, 80, 0.1);
            transition: 0.2s;
            border: 1px solid rgba(255,255,255,0.5);
        }
        .activity-card:hover {
            box-shadow: 0 20px 28px -8px rgba(0, 55, 100, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 600;
            color: #113355;
            margin-bottom: 1.5rem;
            border-bottom: 2px dashed #cbd6e3;
            padding-bottom: 0.8rem;
        }
        .card-header i {
            font-size: 2rem;
            color: #2a5f8a;
        }

        .form-row {
            margin-bottom: 1.4rem;
        }
        .form-row label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #2c3e50;
            display: block;
            margin-bottom: 0.3rem;
        }
        .form-row input, .form-row select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #cfdeed;
            border-radius: 18px;
            font-size: 0.95rem;
            background: #fafcff;
            outline: none;
            transition: 0.15s;
        }
        .form-row input:focus, .form-row select:focus {
            border-color: #2a5f8a;
            box-shadow: 0 0 0 3px rgba(0,70,130,0.1);
        }
        .form-row.file-row {
            border: 2px dashed #b7c9dd;
            border-radius: 26px;
            padding: 1rem 1.2rem;
            background: #f4f9ff;
            text-align: center;
            cursor: pointer;
        }
        .form-row.file-row i {
            font-size: 2rem;
            color: #2a5f8a;
            opacity: 0.7;
        }
        .form-row.file-row span {
            display: block;
            color: #2b4b6e;
            font-size: 0.9rem;
        }
        .image-preview {
            margin: 0.8rem 0 0.2rem 0;
            max-width: 100%;
            border-radius: 18px;
            border: 1px solid #c6d8eb;
            background: #ecf3fa;
            padding: 6px;
        }
        .image-preview img {
            width: 100%;
            max-height: 140px;
            object-fit: cover;
            border-radius: 14px;
        }

        .btn-add {
            background: #0b2b4f;
            color: white;
            border: none;
            padding: 0.9rem 1.8rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 0.5rem;
            border: 1px solid #1f4970;
        }
        .btn-add i {
            font-size: 1.2rem;
        }
        .btn-add:hover {
            background: #1a3f62;
            transform: scale(1.02);
        }

        /* activities feed (mock list) */
        .recent-feed {
            background: white;
            border-radius: 30px;
            padding: 1.5rem 2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.02);
            margin: 2rem 0;
        }
        .feed-item {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            border-bottom: 1px solid #e7edf4;
            padding: 0.9rem 0;
        }
        .feed-item:last-child {
            border: none;
        }
        .feed-badge {
            background: #dbeafe;
            color: #113355;
            border-radius: 30px;
            padding: 0.3rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 100px;
            text-align: center;
        }
        .feed-detail {
            flex: 1;
            color: #264e70;
        }
        .feed-detail small {
            color: #6a7e96;
        }
        .feed-thumb {
            width: 48px;
            height: 48px;
            background: #d9e4f0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #1e4a76;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR (exactly as described) -->
    <div class="sidebar">
        <h2><i class="fas fa-shield-alt"></i> PNP Admin</h2>
        <ul>
            <li><i class="fas fa-tachometer-alt"></i> Dashboard</li>
            <li><i class="fas fa-map-marker-alt"></i> Checkpoint</li>
            <li><i class="fas fa-walking"></i> Patrol</li>
            <li><i class="fas fa-tools"></i> Oplan Bakal / Sita</li>
            <li><i class="fas fa-users-cog"></i> Users</li>
            <li><i class="fas fa-sign-out-alt"></i> Logout</li>
        </ul>
    </div>

    <!-- MAIN PANEL -->
    <div class="main">
        <!-- Welcome and Dashboard Overview (exactly from image) -->
        <div class="top-welcome">
            <h1>Dashboard Overview</h1>
            <p><i class="fas fa-sync-alt" style="margin-right: 6px;"></i> Welcome back. System monitoring panel.</p>
        </div>

        <!-- Summary Cards (mirror image) -->
        <div class="summary-row">
            <div class="patrol-stats">
                <div class="patrol-item"><span>24</span><span>Foot Patrol</span></div>
                <div class="patrol-item"><span>24</span><span>Mobile Patrol</span></div>
                <div class="patrol-item"><span>24</span><span>Motorcycle Patrol</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-traffic-light"></i></div>
                <div class="stat-content">
                    <h3>Checkpoint</h3>
                    <div class="number">3</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shield"></i></div>
                <div class="stat-content">
                    <h3>Oplan Bakal</h3>
                    <div class="number">20</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-gavel"></i></div>
                <div class="stat-content">
                    <h3>Oplan Sita</h3>
                    <div class="number">28</div>
                </div>
            </div>
        </div>

        <!-- ========== USER ACTIVITY SECTION: ADD PATROL, CHECKPOINT, OPLAN + PICTURE ========== -->
        <div class="section-title">
            <i class="fas fa-plus-circle" style="margin-right: 0.6rem;"></i> Add new activity (user panel)
        </div>

        <div class="activity-grid">
            <!-- CARD: PATROL (foot / mobile / motorcycle) -->
            <div class="activity-card">
                <div class="card-header">
                    <i class="fas fa-shoe-prints"></i> Patrol
                </div>
                <div class="form-row">
                    <label>Type</label>
                    <select id="patrolTypeSelect">
                        <option value="Foot Patrol">Foot Patrol</option>
                        <option value="Mobile Patrol">Mobile Patrol</option>
                        <option value="Motorcycle Patrol">Motorcycle Patrol</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Short description (concise)</label>
                    <input type="text" id="patrolDesc" placeholder="e.g. Barangay round 10pm" value="night patrol brgy.1">
                </div>
                <div class="form-row file-row" onclick="document.getElementById('patrolImage').click();">
                    <i class="fas fa-camera"></i>
                    <span>Click to upload patrol picture</span>
                    <input type="file" id="patrolImage" accept="image/*" style="display: none;" onchange="previewImage(event, 'previewPatrol')">
                </div>
                <div id="previewPatrol" class="image-preview" style="display: none;"></div>
                <button class="btn-add" onclick="addPatrolActivity()"><i class="fas fa-plus"></i> Add patrol</button>
            </div>

            <!-- CARD: CHECKPOINT -->
            <div class="activity-card">
                <div class="card-header">
                    <i class="fas fa-map-pin"></i> Checkpoint
                </div>
                <div class="form-row">
                    <label>Location / name</label>
                    <input type="text" id="checkpointName" placeholder="e.g. North boundary" value="Santo Niño checkpoint">
                </div>
                <div class="form-row">
                    <label>Remarks (concise)</label>
                    <input type="text" id="checkpointRemarks" placeholder="optional" value="vehicle inspection">
                </div>
                <div class="form-row file-row" onclick="document.getElementById('checkpointImage').click();">
                    <i class="fas fa-camera"></i>
                    <span>Upload checkpoint image</span>
                    <input type="file" id="checkpointImage" accept="image/*" style="display: none;" onchange="previewImage(event, 'previewCheckpoint')">
                </div>
                <div id="previewCheckpoint" class="image-preview" style="display: none;"></div>
                <button class="btn-add" onclick="addCheckpointActivity()"><i class="fas fa-plus"></i> Add checkpoint</button>
            </div>

            <!-- CARD: OPLAN BAKAL / SITA -->
            <div class="activity-card">
                <div class="card-header">
                    <i class="fas fa-balance-scale"></i> Oplan
                </div>
                <div class="form-row">
                    <label>Oplan type</label>
                    <select id="oplanTypeSelect">
                        <option value="Oplan Bakal">Oplan Bakal</option>
                        <option value="Oplan Sita">Oplan Sita</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Brief detail</label>
                    <input type="text" id="oplanDetail" placeholder="e.g. checkpoint #12" value="junction 23 apprehend">
                </div>
                <div class="form-row file-row" onclick="document.getElementById('oplanImage').click();">
                    <i class="fas fa-camera"></i>
                    <span>Upload oplan photo</span>
                    <input type="file" id="oplanImage" accept="image/*" style="display: none;" onchange="previewImage(event, 'previewOplan')">
                </div>
                <div id="previewOplan" class="image-preview" style="display: none;"></div>
                <button class="btn-add" onclick="addOplanActivity()"><i class="fas fa-plus"></i> Add oplan</button>
            </div>
        </div>

        <!-- Recent added activities (feed / mockup) -->
        <div class="recent-feed">
            <h3 style="margin-bottom: 1.2rem; font-weight: 500;"><i class="fas fa-stream" style="margin-right: 0.8rem;"></i>Latest added activities (front‑end demo)</h3>
            <div id="activityFeedContainer">
                <!-- filled by javascript -->
                <div class="feed-item">
                    <div class="feed-badge">Foot Patrol</div>
                    <div class="feed-detail">night patrol brgy.1 <small>· 2 min ago</small></div>
                    <div class="feed-thumb"><i class="fas fa-image"></i></div>
                </div>
                <div class="feed-item">
                    <div class="feed-badge">Checkpoint</div>
                    <div class="feed-detail">Santo Niño · vehicle inspection <small>· 15 min ago</small></div>
                    <div class="feed-thumb"><i class="fas fa-camera-retro"></i></div>
                </div>
                <div class="feed-item">
                    <div class="feed-badge">Oplan Bakal</div>
                    <div class="feed-detail">junction 23 apprehend <small>· 32 min ago</small></div>
                    <div class="feed-thumb"><i class="fas fa-image"></i></div>
                </div>
            </div>
            <p style="text-align: right; color: #45749c; margin-top: 0.6rem;"><i class="fas fa-upload"></i> each new click adds a card (image simulated)</p>
        </div>
    </div>

    <script>
        // Simple image preview (only frontend, no actual upload)
        function previewImage(event, previewId) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            const previewDiv = document.getElementById(previewId);
            reader.onload = function(e) {
                previewDiv.style.display = 'block';
                previewDiv.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            }
            reader.readAsDataURL(file);
        }

        // Dummy counters for feed
        let activityCount = 3; // start with 3 mock entries

        // Helper to create a feed item (and prepend to list)
        function addFeedItem(typeLabel, description, imageAvailable) {
            const container = document.getElementById('activityFeedContainer');
            const newItem = document.createElement('div');
            newItem.className = 'feed-item';

            const badge = document.createElement('div');
            badge.className = 'feed-badge';
            badge.innerText = typeLabel;

            const detail = document.createElement('div');
            detail.className = 'feed-detail';
            const timeAgo = ' just now';
            detail.innerHTML = `${description} <small>· ${timeAgo}</small>`;

            const thumb = document.createElement('div');
            thumb.className = 'feed-thumb';
            if (imageAvailable) {
                thumb.innerHTML = '<i class="fas fa-camera" style="color:#1f5a8e;"></i>';
            } else {
                thumb.innerHTML = '<i class="fas fa-image"></i>';
            }

            newItem.appendChild(badge);
            newItem.appendChild(detail);
            newItem.appendChild(thumb);

            // Prepend to show latest first
            container.prepend(newItem);

            // keep max 7 items (optional)
            if (container.children.length > 7) {
                container.removeChild(container.lastElementChild);
            }
        }

        // Patrol add function
        function addPatrolActivity() {
            const select = document.getElementById('patrolTypeSelect');
            const patrolType = select.options[select.selectedIndex].text;
            const desc = document.getElementById('patrolDesc').value.trim() || 'patrol duty';
            const fileInput = document.getElementById('patrolImage');
            const hasImage = fileInput.files.length > 0;

            // type string for badge: use exactly as select (Foot Patrol, Mobile Patrol, Motorcycle Patrol)
            addFeedItem(patrolType, desc, hasImage);

            // (optional) reset preview and file input?
            // just for demo, we keep preview visible. you can reset if needed but it's fine.
            // also optionally show a little alert (but not necessary)
        }

        function addCheckpointActivity() {
            const name = document.getElementById('checkpointName').value.trim() || 'checkpoint';
            const remarks = document.getElementById('checkpointRemarks').value.trim();
            const desc = remarks ? `${name} · ${remarks}` : name;
            const fileInput = document.getElementById('checkpointImage');
            const hasImage = fileInput.files.length > 0;
            addFeedItem('Checkpoint', desc, hasImage);
        }

        function addOplanActivity() {
            const select = document.getElementById('oplanTypeSelect');
            const oplanType = select.options[select.selectedIndex].text; // "Oplan Bakal" or "Oplan Sita"
            const detail = document.getElementById('oplanDetail').value.trim() || 'operation';
            const fileInput = document.getElementById('oplanImage');
            const hasImage = fileInput.files.length > 0;
            addFeedItem(oplanType, detail, hasImage);
        }

        // preview functions already declared above
        // Make sure preview functions are bound globally
        window.previewImage = previewImage;
        window.addPatrolActivity = addPatrolActivity;
        window.addCheckpointActivity = addCheckpointActivity;
        window.addOplanActivity = addOplanActivity;
    </script>

    <!-- subtle extra: simulate that file inputs clear after click? not needed, frontend only -->
</body>
</html>