<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNP Manolo Fortich · Activity Reporting</title>
    <!-- Font Awesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #eef2f5;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* User Header - PNP Admin Colors */
        .user-header {
            background: white;
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #0a2b3c;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: #0a2b3c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffc107;
            font-size: 1.5rem;
        }

        .user-details h2 {
            color: #0a2b3c;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .user-details p {
            color: #4a5a6a;
            font-size: 0.9rem;
        }

        .user-details p i {
            color: #ffc107;
            margin-right: 0.3rem;
        }

        .badge {
            background: #0a2b3c;
            color: #ffc107;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid #1e4a6a;
        }

        .badge i {
            margin-right: 0.3rem;
        }

        .logout-btn {
            background: #c41e3a;
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #a01830;
            transform: translateY(-2px);
        }

        /* Main Content Layout */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        /* Left Column - Forms */
        .forms-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-top: 5px solid #0a2b3c;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #0a2b3c;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #e0e7ff;
        }

        .section-title i {
            color: #ffc107;
            font-size: 1.8rem;
        }

        .section-title h2 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        /* Activity Tabs */
        .activity-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 1rem 2rem;
            border: none;
            background: #f0f0f0;
            border-radius: 12px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.3s;
            flex: 1;
            justify-content: center;
        }

        .tab-btn i {
            font-size: 1.2rem;
        }

        .tab-btn.active {
            background: #0a2b3c;
            color: white;
        }

        .tab-btn.active i {
            color: #ffc107;
        }

        .tab-btn:hover:not(.active) {
            background: #e0e0e0;
        }

        /* Activity Forms */
        .activity-form {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .activity-form.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Location Badge - Manolo Fortich Only */
        .location-badge {
            background: #f0f7ff;
            border: 2px solid #0a2b3c;
            border-radius: 50px;
            padding: 0.8rem 1.5rem;
            margin-bottom: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            color: #0a2b3c;
            font-weight: 600;
        }

        .location-badge i {
            color: #ffc107;
            font-size: 1.2rem;
        }

        .location-badge span {
            font-size: 1rem;
        }

        /* Form Sections */
        .form-section {
            background: #f8faff;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #d0dbe8;
        }

        .form-section h4 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #0a2b3c;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #d0dbe8;
        }

        .form-section h4 i {
            color: #ffc107;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label i {
            margin-right: 0.5rem;
            color: #0a2b3c;
            width: 20px;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid #d0dbe8;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: 0.3s;
            background: white;
        }

        .form-control:focus {
            border-color: #0a2b3c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(10, 43, 60, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
            line-height: 1.5;
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230a2b3c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
        }

        /* Manolo Fortich Specific Locations */
        .barangay-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.8rem;
            margin-top: 0.5rem;
        }

        .barangay-option {
            background: white;
            border: 2px solid #d0dbe8;
            border-radius: 30px;
            padding: 0.6rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .barangay-option:hover {
            border-color: #0a2b3c;
            background: #f0f7ff;
        }

        .barangay-option.selected {
            background: #0a2b3c;
            color: white;
            border-color: #0a2b3c;
        }

        .barangay-option.selected i {
            color: #ffc107;
        }

        /* Accomplishment Items */
        .accomplishment-item {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #d0dbe8;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .accomplishment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .accomplishment-header h5 {
            color: #0a2b3c;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .accomplishment-header h5 i {
            color: #ffc107;
        }

        .remove-accomplishment {
            color: #c41e3a;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.2s;
        }

        .remove-accomplishment:hover {
            transform: scale(1.1);
        }

        .add-accomplishment-btn {
            background: #0a2b3c;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.3s;
            margin-top: 0.5rem;
        }

        .add-accomplishment-btn:hover {
            background: #1e4a6a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 43, 60, 0.3);
        }

        .add-accomplishment-btn i {
            color: #ffc107;
        }

        /* HD Image Upload */
        .file-upload {
            border: 3px dashed #d0dbe8;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            background: #f9f9f9;
        }

        .file-upload:hover {
            border-color: #0a2b3c;
            background: #f0f7ff;
        }

        .file-upload i {
            font-size: 3.5rem;
            color: #0a2b3c;
            margin-bottom: 1rem;
        }

        .file-upload p {
            color: #2c3e50;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .file-upload small {
            color: #666;
            font-size: 0.85rem;
            display: block;
        }

        .file-upload .file-info {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #0a2b3c;
            font-weight: 500;
        }

        .image-preview-container {
            margin-top: 1rem;
            display: none;
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid #d0dbe8;
        }

        .image-preview-container img {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            background: #f5f5f5;
        }

        .image-info {
            background: rgba(10, 43, 60, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .image-info span i {
            margin-right: 0.3rem;
            color: #ffc107;
        }

        .remove-image {
            background: #c41e3a;
            color: white;
            border: none;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: 0.2s;
        }

        .remove-image:hover {
            background: #a01830;
        }

        /* Loading indicator for large images */
        .image-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            padding: 1rem 2rem;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .image-loading i {
            font-size: 1.5rem;
            color: #0a2b3c;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background: #0a2b3c;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: 0.3s;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: #1e4a6a;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(10, 43, 60, 0.3);
        }

        .submit-btn i {
            color: #ffc107;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Right Column */
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Stats Card - PNP Admin Colors */
        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-top: 5px solid #0a2b3c;
        }

        .stats-card h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #0a2b3c;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e7ff;
        }

        .stats-card h3 i {
            color: #ffc107;
        }

        .stats-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #0a2b3c;
        }

        .stat-label {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .stat-label i {
            width: 24px;
            color: #ffc107;
        }

        .stat-value {
            font-weight: 700;
            color: #0a2b3c;
            font-size: 1.2rem;
        }

        /* Recent Activities */
        .recent-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-top: 5px solid #0a2b3c;
        }

        .recent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e7ff;
        }

        .recent-header h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #0a2b3c;
        }

        .recent-header h3 i {
            color: #ffc107;
        }

        .recent-badge {
            background: #0a2b3c;
            color: #ffc107;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .activity-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            transition: 0.3s;
            border-left: 4px solid;
        }

        .activity-item.patrol { border-left-color: #0a2b3c; }
        .activity-item.checkpoint { border-left-color: #1e4a6a; }
        .activity-item.oplan { border-left-color: #c41e3a; }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #0a2b3c;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffc107;
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #0a2b3c;
            margin-bottom: 0.2rem;
        }

        .activity-meta {
            font-size: 0.8rem;
            color: #666;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .activity-meta i {
            margin-right: 0.3rem;
            color: #ffc107;
        }

        .activity-thumb {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            overflow: hidden;
        }

        .activity-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-thumb i {
            font-size: 1.2rem;
        }

        /* Manolo Fortich Watermark/Footer */
        .mf-footer {
            margin-top: 2rem;
            text-align: center;
            color: #0a2b3c;
            font-size: 0.9rem;
            padding: 1rem;
            border-top: 1px solid #d0dbe8;
        }

        .mf-footer i {
            color: #ffc107;
            margin: 0 0.3rem;
        }

        /* Success Message */
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0a2b3c;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideIn 0.5s ease;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-left: 5px solid #ffc107;
        }

        .success-message i {
            color: #ffc107;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0%); opacity: 1; }
        }

        /* Error Message */
        .error-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #c41e3a;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideIn 0.5s ease;
            z-index: 1000;
            border-left: 5px solid #ffc107;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- User Header - PNP Admin Style -->
        <div class="user-header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="user-details">
                    <h2>PO3 Juan Dela Cruz</h2>
                    <p><i class="fas fa-id-badge"></i> Badge #PNP-2024-0123 · Manolo Fortich MPS</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span class="badge"><i class="fas fa-map-marker-alt"></i> Manolo Fortich, Bukidnon</span>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Left Column - Forms -->
            <div class="forms-section">
                <div class="section-title">
                    <i class="fas fa-clipboard-list"></i>
                    <h2>Report New Activity</h2>
                </div>

                <!-- Location Badge - Fixed to Manolo Fortich -->
                <div class="location-badge">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Area of Responsibility: Manolo Fortich, Bukidnon</span>
                </div>

                <!-- Activity Tabs -->
                <div class="activity-tabs">
                    <button class="tab-btn active" onclick="switchTab('patrol')">
                        <i class="fas fa-walking"></i> Patrol
                    </button>
                    <button class="tab-btn" onclick="switchTab('checkpoint')">
                        <i class="fas fa-map-marker-alt"></i> Checkpoint
                    </button>
                    <button class="tab-btn" onclick="switchTab('oplan')">
                        <i class="fas fa-shield-alt"></i> Oplan Bakal/Sita
                    </button>
                </div>

                <!-- Barangays of Manolo Fortich -->
                <div style="margin-bottom: 1.5rem; background: #f0f7ff; padding: 1rem; border-radius: 12px;">
                    <p style="color: #0a2b3c; margin-bottom: 0.5rem; font-weight: 600;">
                        <i class="fas fa-map-pin"></i> Select Barangay:
                    </p>
                    <div class="barangay-selector" id="barangaySelector">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Patrol Form -->
                <div id="patrol-form" class="activity-form active">
                    <form id="patrolActivityForm" onsubmit="submitActivity(event, 'patrol')">
                        <!-- Basic Info Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-tag"></i> Patrol Type</label>
                                    <select class="form-control" id="patrolType" required>
                                        <option value="">Select patrol type</option>
                                        <option value="Foot Patrol">Foot Patrol</option>
                                        <option value="Mobile Patrol">Mobile Patrol</option>
                                        <option value="Motorcycle Patrol">Motorcycle Patrol</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-location-dot"></i> Specific Location</label>
                                    <input type="text" class="form-control" id="patrolLocation" placeholder="e.g., Poblacion, Brgy. Tankulan" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar"></i> Date</label>
                                    <input type="date" class="form-control" id="patrolDate" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-clock"></i> Time</label>
                                    <input type="time" class="form-control" id="patrolTime" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Number of Personnel Deployed</label>
                                <input type="number" class="form-control" id="patrolPersonnel" placeholder="Enter number of personnel" min="1" required>
                            </div>
                        </div>

                        <!-- Accomplishments Section with Detailed Description -->
                        <div class="form-section">
                            <h4><i class="fas fa-trophy"></i> Daily Accomplishments - Manolo Fortich</h4>
                            <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> Describe in detail what you accomplished today in Manolo Fortich. You can add multiple accomplishments.
                            </p>
                            <div id="patrolAccomplishments">
                                <!-- Accomplishment items will be added here -->
                            </div>
                            <button type="button" class="add-accomplishment-btn" onclick="addAccomplishment('patrol')">
                                <i class="fas fa-plus"></i> Add Another Accomplishment
                            </button>
                        </div>

                        <!-- HD Photo Upload Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-camera"></i> Photo Documentation (HD Images Supported)</h4>
                            <div class="file-upload" onclick="document.getElementById('patrolImage').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload photos (HD images up to 15MB)</p>
                                <small>Supports: JPG, PNG, JPEG, HEIC</small>
                                <div class="file-info" id="patrolFileInfo"></div>
                                <input type="file" id="patrolImage" accept="image/*" style="display: none;" onchange="handleImageUpload(this, 'patrol')">
                            </div>
                            <div id="patrol-preview" class="image-preview-container">
                                <img src="" alt="Preview" id="patrolPreviewImg">
                                <div class="image-info">
                                    <span><i class="fas fa-image"></i> <span id="patrolImageSize"></span></span>
                                    <button type="button" class="remove-image" onclick="removeImage('patrol')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="patrolSubmitBtn">
                            <i class="fas fa-paper-plane"></i> Submit Patrol Report - Manolo Fortich
                        </button>
                    </form>
                </div>

                <!-- Checkpoint Form -->
                <div id="checkpoint-form" class="activity-form">
                    <form id="checkpointActivityForm" onsubmit="submitActivity(event, 'checkpoint')">
                        <!-- Basic Info Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-info-circle"></i> Checkpoint Information</h4>
                            <div class="form-group">
                                <label><i class="fas fa-location-dot"></i> Specific Location in Manolo Fortich</label>
                                <input type="text" class="form-control" id="checkpointLocation" placeholder="e.g., National Highway, Brgy. Tankulan" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar"></i> Date</label>
                                    <input type="date" class="form-control" id="checkpointDate" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-clock"></i> Time</label>
                                    <input type="time" class="form-control" id="checkpointTime" required>
                                </div>
                            </div>
                        </div>

                        <!-- Checkpoint Operations Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-map-pin"></i> Checkpoint Operations - Manolo Fortich</h4>
                            
                            <div class="form-group">
                                <label><i class="fas fa-border-all"></i> Number of Checkpoint Operations in Border Control Point</label>
                                <input type="number" class="form-control" id="borderControlOps" placeholder="Enter number" min="0" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Number of Personnel Deployed (Border Control)</label>
                                <input type="number" class="form-control" id="borderPersonnel" placeholder="Enter number" min="0" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-sync-alt"></i> Number of Operations in Overlapping Checkpoint</label>
                                <input type="number" class="form-control" id="overlappingOps" placeholder="Enter number" min="0" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-truck"></i> Number of Operations in MOBILE CHECKPOINT</label>
                                <input type="number" class="form-control" id="mobileCheckpointOps" placeholder="Enter number" min="0" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Number of Personnel Deployed (Mobile Checkpoint)</label>
                                <input type="number" class="form-control" id="mobilePersonnel" placeholder="Enter number" min="0" value="0">
                            </div>
                        </div>

                        <!-- Accomplishments Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-trophy"></i> Accomplishments - Manolo Fortich</h4>
                            
                            <div class="form-group">
                                <label><i class="fas fa-file-alt"></i> No. of ACCOMPLISHMENT TCT/OVR (DO NOT INCLUDE ROVMOS ACCOM. HERE!)</label>
                                <input type="number" class="form-control" id="tctOvrAccom" placeholder="Enter number" min="0" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-gavel"></i> ARRESTED/FOR FILING/WITH ACCOMPLISHMENT REPORT TO R3</label>
                                <input type="number" class="form-control" id="arrestedAccom" placeholder="Enter number" min="0" value="0">
                            </div>
                            
                            <p style="color: #666; margin: 1rem 0; font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> Add detailed descriptions of other accomplishments in Manolo Fortich:
                            </p>
                            <div id="checkpointAccomplishments">
                                <!-- Additional accomplishment items will be added here -->
                            </div>
                            <button type="button" class="add-accomplishment-btn" onclick="addAccomplishment('checkpoint')">
                                <i class="fas fa-plus"></i> Add Detailed Accomplishment
                            </button>
                        </div>

                        <!-- HD Photo Upload Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-camera"></i> Photo Documentation (HD Images Supported)</h4>
                            <div class="file-upload" onclick="document.getElementById('checkpointImage').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload photos (HD images up to 15MB)</p>
                                <small>Supports: JPG, PNG, JPEG, HEIC</small>
                                <div class="file-info" id="checkpointFileInfo"></div>
                                <input type="file" id="checkpointImage" accept="image/*" style="display: none;" onchange="handleImageUpload(this, 'checkpoint')">
                            </div>
                            <div id="checkpoint-preview" class="image-preview-container">
                                <img src="" alt="Preview" id="checkpointPreviewImg">
                                <div class="image-info">
                                    <span><i class="fas fa-image"></i> <span id="checkpointImageSize"></span></span>
                                    <button type="button" class="remove-image" onclick="removeImage('checkpoint')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="checkpointSubmitBtn">
                            <i class="fas fa-paper-plane"></i> Submit Checkpoint Report - Manolo Fortich
                        </button>
                    </form>
                </div>

                <!-- Oplan Form -->
                <div id="oplan-form" class="activity-form">
                    <form id="oplanActivityForm" onsubmit="submitActivity(event, 'oplan')">
                        <!-- Basic Info Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-tag"></i> Oplan Type</label>
                                    <select class="form-control" id="oplanType" required>
                                        <option value="">Select oplan type</option>
                                        <option value="Oplan Bakal">Oplan Bakal</option>
                                        <option value="Oplan Sita">Oplan Sita</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-location-dot"></i> Specific Location in Manolo Fortich</label>
                                    <input type="text" class="form-control" id="oplanLocation" placeholder="e.g., Brgy. Tankulan Public Market" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar"></i> Date</label>
                                    <input type="date" class="form-control" id="oplanDate" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-clock"></i> Time</label>
                                    <input type="time" class="form-control" id="oplanTime" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Number of Personnel Deployed</label>
                                <input type="number" class="form-control" id="oplanPersonnel" placeholder="Enter number of personnel" min="1" required>
                            </div>
                        </div>

                        <!-- Accomplishments Section with Detailed Description -->
                        <div class="form-section">
                            <h4><i class="fas fa-trophy"></i> Daily Accomplishments - Manolo Fortich</h4>
                            <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> Describe in detail what you accomplished today in Manolo Fortich. You can add multiple accomplishments.
                            </p>
                            <div id="oplanAccomplishments">
                                <!-- Accomplishment items will be added here -->
                            </div>
                            <button type="button" class="add-accomplishment-btn" onclick="addAccomplishment('oplan')">
                                <i class="fas fa-plus"></i> Add Another Accomplishment
                            </button>
                        </div>

                        <!-- HD Photo Upload Section -->
                        <div class="form-section">
                            <h4><i class="fas fa-camera"></i> Photo Documentation (HD Images Supported)</h4>
                            <div class="file-upload" onclick="document.getElementById('oplanImage').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload photos (HD images up to 15MB)</p>
                                <small>Supports: JPG, PNG, JPEG, HEIC</small>
                                <div class="file-info" id="oplanFileInfo"></div>
                                <input type="file" id="oplanImage" accept="image/*" style="display: none;" onchange="handleImageUpload(this, 'oplan')">
                            </div>
                            <div id="oplan-preview" class="image-preview-container">
                                <img src="" alt="Preview" id="oplanPreviewImg">
                                <div class="image-info">
                                    <span><i class="fas fa-image"></i> <span id="oplanImageSize"></span></span>
                                    <button type="button" class="remove-image" onclick="removeImage('oplan')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="oplanSubmitBtn">
                            <i class="fas fa-paper-plane"></i> Submit Oplan Report - Manolo Fortich
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column - Stats and Recent -->
            <div class="right-column">
                <!-- Stats Card -->
                <div class="stats-card">
                    <h3><i class="fas fa-chart-pie"></i> Manolo Fortich Summary</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-walking"></i> Foot Patrol</span>
                            <span class="stat-value">24</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-car"></i> Mobile Patrol</span>
                            <span class="stat-value">24</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-motorcycle"></i> Motorcycle Patrol</span>
                            <span class="stat-value">24</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-map-marker-alt"></i> Checkpoints</span>
                            <span class="stat-value">3</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-shield-alt"></i> Oplan Bakal</span>
                            <span class="stat-value">20</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-gavel"></i> Oplan Sita</span>
                            <span class="stat-value">28</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="recent-card">
                    <div class="recent-header">
                        <h3><i class="fas fa-history"></i> Recent Activities - Manolo Fortich</h3>
                        <span class="recent-badge">Last 5</span>
                    </div>
                    <div class="activity-list" id="activityList">
                        <!-- Activities will be added here dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Manolo Fortich Footer -->
        <div class="mf-footer">
            <i class="fas fa-shield-alt"></i> Philippine National Police - Manolo Fortich Municipal Station <i class="fas fa-shield-alt"></i><br>
            <small>Area of Responsibility: Municipality of Manolo Fortich, Bukidnon</small>
        </div>
    </div>

    <script>
        // List of Barangays in Manolo Fortich, Bukidnon
        const manoloFortichBarangays = [
            "Agusan Canyon",
            "Alae",
            "Dahilayan",
            "Dalirig",
            "Damilag",
            "Dicklum",
            "Guilang-guilang",
            "Kalugmanan",
            "Lindaban",
            "Lurugan",
            "Manolo Fortich Poblacion",
            "Mambatangan",
            "Minsuro",
            "Mantibugao",
            "Sankanan",
            "Santiago",
            "Santo Niño",
            "Tankulan",
            "Ticala"
        ];

        // Store activities
        let activities = JSON.parse(localStorage.getItem('pnpManoloFortichActivities')) || [];

        // Max file size: 15MB
        const MAX_FILE_SIZE = 15 * 1024 * 1024; // 15MB in bytes

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            populateBarangays();
            displayActivities();
            setDefaultDates();
        });

        // Populate barangay selector
        function populateBarangays() {
            const selector = document.getElementById('barangaySelector');
            selector.innerHTML = '';
            
            manoloFortichBarangays.forEach(barangay => {
                const option = document.createElement('div');
                option.className = 'barangay-option';
                option.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${barangay}`;
                option.onclick = function() {
                    // Remove selected class from all
                    document.querySelectorAll('.barangay-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    // Add selected class to clicked
                    this.classList.add('selected');
                    
                    // Store selected barangay
                    document.getElementById('selectedBarangay').value = barangay;
                };
                selector.appendChild(option);
            });
        }

        // Set default dates to today
        function setDefaultDates() {
            const today = new Date().toISOString().split('T')[0];
            const now = new Date().toTimeString().split(' ')[0].substring(0,5);
            
            const patrolDate = document.getElementById('patrolDate');
            const patrolTime = document.getElementById('patrolTime');
            const checkpointDate = document.getElementById('checkpointDate');
            const checkpointTime = document.getElementById('checkpointTime');
            const oplanDate = document.getElementById('oplanDate');
            const oplanTime = document.getElementById('oplanTime');
            
            if (patrolDate) patrolDate.value = today;
            if (patrolTime) patrolTime.value = now;
            if (checkpointDate) checkpointDate.value = today;
            if (checkpointTime) checkpointTime.value = now;
            if (oplanDate) oplanDate.value = today;
            if (oplanTime) oplanTime.value = now;
        }

        // Switch between tabs
        function switchTab(tab) {
            document.querySelectorAll('.activity-form').forEach(form => {
                form.classList.remove('active');
            });
            document.getElementById(tab + '-form').classList.add('active');
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Add accomplishment with detailed description
        function addAccomplishment(type) {
            const container = document.getElementById(type + 'Accomplishments');
            const accomplishmentId = Date.now() + Math.random();
            
            const accomplishmentDiv = document.createElement('div');
            accomplishmentDiv.className = 'accomplishment-item';
            accomplishmentDiv.id = `accomplishment-${accomplishmentId}`;
            
            accomplishmentDiv.innerHTML = `
                <div class="accomplishment-header">
                    <h5><i class="fas fa-check-circle"></i> Accomplishment Details - Manolo Fortich</h5>
                    <span class="remove-accomplishment" onclick="removeAccomplishment('${accomplishmentId}')">
                        <i class="fas fa-times-circle"></i>
                    </span>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Title/Type of Accomplishment</label>
                    <input type="text" class="form-control" placeholder="e.g., Apprehension, Rescue Operation, Patrol Completion" id="accom-title-${accomplishmentId}">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Detailed Description</label>
                    <textarea class="form-control" placeholder="Describe in detail what happened, who were involved, what was achieved in Manolo Fortich..." id="accom-desc-${accomplishmentId}" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-hashtag"></i> Quantity/Number</label>
                        <input type="number" class="form-control" placeholder="Enter number" min="0" value="1" id="accom-qty-${accomplishmentId}">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Status</label>
                        <select class="form-control" id="accom-status-${accomplishmentId}">
                            <option value="Completed">Completed</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Pending">Pending</option>
                            <option value="For Filing">For Filing</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Barangay</label>
                    <select class="form-control" id="accom-barangay-${accomplishmentId}">
                        <option value="">Select Barangay</option>
                        ${manoloFortichBarangays.map(b => `<option value="${b}">${b}</option>`).join('')}
                    </select>
                </div>
            `;
            
            container.appendChild(accomplishmentDiv);
        }

        // Remove accomplishment
        function removeAccomplishment(id) {
            const element = document.getElementById(`accomplishment-${id}`);
            if (element) {
                element.remove();
            }
        }

        // Handle HD image upload with size check
        function handleImageUpload(input, formType) {
            const file = input.files[0];
            if (!file) return;

            // Check file size (max 15MB)
            if (file.size > MAX_FILE_SIZE) {
                showErrorMessage(`File size exceeds 15MB. Please choose a smaller file. (Current: ${(file.size / (1024 * 1024)).toFixed(2)}MB)`);
                input.value = '';
                return;
            }

            // Show loading
            const previewContainer = document.getElementById(formType + '-preview');
            const previewImg = document.getElementById(formType + 'PreviewImg');
            const sizeSpan = document.getElementById(formType + 'ImageSize');
            const fileInfo = document.getElementById(formType + 'FileInfo');

            // Display file info
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            fileInfo.innerHTML = `Selected: ${file.name} (${fileSizeMB}MB)`;

            // Show loading indicator
            previewContainer.style.display = 'block';
            previewImg.style.opacity = '0.5';
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'image-loading';
            loadingDiv.id = formType + '-loading';
            loadingDiv.innerHTML = '<i class="fas fa-spinner"></i> Loading HD image...';
            previewContainer.appendChild(loadingDiv);

            // Read and display image
            const reader = new FileReader();
            reader.onload = function(e) {
                // Remove loading indicator
                const loading = document.getElementById(formType + '-loading');
                if (loading) loading.remove();
                
                previewImg.style.opacity = '1';
                previewImg.src = e.target.result;
                sizeSpan.innerHTML = `${fileSizeMB} MB - HD Image`;
            };
            reader.onerror = function() {
                // Remove loading indicator on error
                const loading = document.getElementById(formType + '-loading');
                if (loading) loading.remove();
                previewImg.style.opacity = '1';
                showErrorMessage('Error loading image. Please try again.');
            };
            reader.readAsDataURL(file);
        }

        // Remove image
        function removeImage(form) {
            const previewContainer = document.getElementById(form + '-preview');
            const previewImg = document.getElementById(form + 'PreviewImg');
            const fileInput = document.getElementById(form + 'Image');
            const fileInfo = document.getElementById(form + 'FileInfo');
            const sizeSpan = document.getElementById(form + 'ImageSize');
            
            previewContainer.style.display = 'none';
            previewImg.src = '';
            fileInput.value = '';
            if (fileInfo) fileInfo.innerHTML = '';
            if (sizeSpan) sizeSpan.innerHTML = '';
        }

        // Show error message
        function showErrorMessage(message) {
            const msg = document.createElement('div');
            msg.className = 'error-message';
            msg.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(msg);

            setTimeout(() => {
                msg.remove();
            }, 5000);
        }

        // Get selected barangay
        function getSelectedBarangay() {
            const selected = document.querySelector('.barangay-option.selected');
            return selected ? selected.textContent.trim() : 'Not Selected';
        }

        // Submit activity
        function submitActivity(event, type) {
            event.preventDefault();

            // Check if barangay is selected
            const selectedBarangay = getSelectedBarangay();
            if (selectedBarangay === 'Not Selected') {
                showErrorMessage('Please select a Barangay in Manolo Fortich first.');
                return;
            }

            // Disable submit button to prevent double submission
            const submitBtn = document.getElementById(type + 'SubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            let activity = {
                id: Date.now(),
                type: type,
                timestamp: new Date().toISOString(),
                image: null,
                title: '',
                location: '',
                barangay: selectedBarangay,
                municipality: 'Manolo Fortich, Bukidnon',
                date: '',
                time: '',
                accomplishments: []
            };

            switch(type) {
                case 'patrol':
                    activity.patrolType = document.getElementById('patrolType').value;
                    activity.location = document.getElementById('patrolLocation').value;
                    activity.date = document.getElementById('patrolDate').value;
                    activity.time = document.getElementById('patrolTime').value;
                    activity.personnel = document.getElementById('patrolPersonnel').value;
                    activity.title = activity.patrolType + ' - ' + selectedBarangay;
                    
                    // Collect detailed accomplishments
                    const patrolAccomplishments = document.querySelectorAll('#patrolAccomplishments .accomplishment-item');
                    patrolAccomplishments.forEach(item => {
                        const id = item.id.split('-')[1];
                        const title = document.getElementById(`accom-title-${id}`)?.value || '';
                        const desc = document.getElementById(`accom-desc-${id}`)?.value || '';
                        const qty = document.getElementById(`accom-qty-${id}`)?.value || 0;
                        const status = document.getElementById(`accom-status-${id}`)?.value || 'Completed';
                        const barangay = document.getElementById(`accom-barangay-${id}`)?.value || selectedBarangay;
                        
                        if (title || desc) {
                            activity.accomplishments.push({ 
                                title: title,
                                description: desc, 
                                quantity: qty,
                                status: status,
                                barangay: barangay
                            });
                        }
                    });
                    
                    const patrolImage = document.getElementById('patrolImage');
                    if (patrolImage.files && patrolImage.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            activity.image = e.target.result;
                            activity.imageName = patrolImage.files[0].name;
                            activity.imageSize = (patrolImage.files[0].size / (1024 * 1024)).toFixed(2) + 'MB';
                            saveActivity(activity);
                        };
                        reader.readAsDataURL(patrolImage.files[0]);
                    } else {
                        saveActivity(activity);
                    }
                    break;

                case 'checkpoint':
                    activity.location = document.getElementById('checkpointLocation').value;
                    activity.date = document.getElementById('checkpointDate').value;
                    activity.time = document.getElementById('checkpointTime').value;
                    activity.title = 'Checkpoint Operation - ' + selectedBarangay;
                    
                    // Checkpoint specific fields
                    activity.borderControlOps = document.getElementById('borderControlOps').value;
                    activity.borderPersonnel = document.getElementById('borderPersonnel').value;
                    activity.overlappingOps = document.getElementById('overlappingOps').value;
                    activity.mobileCheckpointOps = document.getElementById('mobileCheckpointOps').value;
                    activity.mobilePersonnel = document.getElementById('mobilePersonnel').value;
                    activity.tctOvrAccom = document.getElementById('tctOvrAccom').value;
                    activity.arrestedAccom = document.getElementById('arrestedAccom').value;
                    
                    // Collect detailed accomplishments
                    const checkpointAccomplishments = document.querySelectorAll('#checkpointAccomplishments .accomplishment-item');
                    checkpointAccomplishments.forEach(item => {
                        const id = item.id.split('-')[1];
                        const title = document.getElementById(`accom-title-${id}`)?.value || '';
                        const desc = document.getElementById(`accom-desc-${id}`)?.value || '';
                        const qty = document.getElementById(`accom-qty-${id}`)?.value || 0;
                        const status = document.getElementById(`accom-status-${id}`)?.value || 'Completed';
                        const barangay = document.getElementById(`accom-barangay-${id}`)?.value || selectedBarangay;
                        
                        if (title || desc) {
                            activity.additionalAccomplishments = activity.additionalAccomplishments || [];
                            activity.additionalAccomplishments.push({ 
                                title: title,
                                description: desc, 
                                quantity: qty,
                                status: status,
                                barangay: barangay
                            });
                        }
                    });
                    
                    const checkpointImage = document.getElementById('checkpointImage');
                    if (checkpointImage.files && checkpointImage.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            activity.image = e.target.result;
                            activity.imageName = checkpointImage.files[0].name;
                            activity.imageSize = (checkpointImage.files[0].size / (1024 * 1024)).toFixed(2) + 'MB';
                            saveActivity(activity);
                        };
                        reader.readAsDataURL(checkpointImage.files[0]);
                    } else {
                        saveActivity(activity);
                    }
                    break;

                case 'oplan':
                    activity.oplanType = document.getElementById('oplanType').value;
                    activity.location = document.getElementById('oplanLocation').value;
                    activity.date = document.getElementById('oplanDate').value;
                    activity.time = document.getElementById('oplanTime').value;
                    activity.personnel = document.getElementById('oplanPersonnel').value;
                    activity.title = activity.oplanType + ' - ' + selectedBarangay;
                    
                    // Collect detailed accomplishments
                    const oplanAccomplishments = document.querySelectorAll('#oplanAccomplishments .accomplishment-item');
                    oplanAccomplishments.forEach(item => {
                        const id = item.id.split('-')[1];
                        const title = document.getElementById(`accom-title-${id}`)?.value || '';
                        const desc = document.getElementById(`accom-desc-${id}`)?.value || '';
                        const qty = document.getElementById(`accom-qty-${id}`)?.value || 0;
                        const status = document.getElementById(`accom-status-${id}`)?.value || 'Completed';
                        const barangay = document.getElementById(`accom-barangay-${id}`)?.value || selectedBarangay;
                        
                        if (title || desc) {
                            activity.accomplishments.push({ 
                                title: title,
                                description: desc, 
                                quantity: qty,
                                status: status,
                                barangay: barangay
                            });
                        }
                    });
                    
                    const oplanImage = document.getElementById('oplanImage');
                    if (oplanImage.files && oplanImage.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            activity.image = e.target.result;
                            activity.imageName = oplanImage.files[0].name;
                            activity.imageSize = (oplanImage.files[0].size / (1024 * 1024)).toFixed(2) + 'MB';
                            saveActivity(activity);
                        };
                        reader.readAsDataURL(oplanImage.files[0]);
                    } else {
                        saveActivity(activity);
                    }
                    break;
            }
        }

        // Save activity
        function saveActivity(activity) {
            activities.unshift(activity);
            localStorage.setItem('pnpManoloFortichActivities', JSON.stringify(activities));
            
            resetForm(activity.type);
            
            // Re-enable submit button
            const submitBtn = document.getElementById(activity.type + 'SubmitBtn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit ' + 
                (activity.type === 'patrol' ? 'Patrol' : activity.type === 'checkpoint' ? 'Checkpoint' : 'Oplan') + ' Report - Manolo Fortich';
            
            showSuccessMessage(activity.type.charAt(0).toUpperCase() + activity.type.slice(1) + ' report for Manolo Fortich submitted successfully!');
            displayActivities();
        }

        // Reset form
        function resetForm(type) {
            const form = document.getElementById(type + 'ActivityForm');
            form.reset();
            removeImage(type);
            setDefaultDates();
            
            // Clear accomplishments
            const container = document.getElementById(type + 'Accomplishments');
            if (container) {
                container.innerHTML = '';
            }
            
            // Remove selected barangay
            document.querySelectorAll('.barangay-option').forEach(opt => {
                opt.classList.remove('selected');
            });
        }

        // Display activities
        function displayActivities() {
            const activityList = document.getElementById('activityList');
            activityList.innerHTML = '';

            activities.slice(0, 5).forEach(activity => {
                const item = document.createElement('div');
                item.className = `activity-item ${activity.type}`;

                let icon = '';
                switch(activity.type) {
                    case 'patrol': icon = 'fa-walking'; break;
                    case 'checkpoint': icon = 'fa-map-marker-alt'; break;
                    case 'oplan': icon = 'fa-shield-alt'; break;
                }

                // Get time from timestamp
                const time = new Date(activity.timestamp).toLocaleTimeString();

                // Count accomplishments
                const accomCount = activity.accomplishments ? activity.accomplishments.length : 0;

                item.innerHTML = `
                    <div class="activity-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">${activity.title}</div>
                        <div class="activity-meta">
                            <span><i class="fas fa-map-pin"></i> ${activity.barangay || 'Manolo Fortich'}</span>
                            <span><i class="fas fa-clock"></i> ${time}</span>
                            ${accomCount > 0 ? `<span><i class="fas fa-trophy"></i> ${accomCount} accomplishment(s)</span>` : ''}
                        </div>
                    </div>
                    <div class="activity-thumb">
                        ${activity.image ? 
                            `<img src="${activity.image}" alt="Activity" title="${activity.imageName || 'Activity photo'} (${activity.imageSize || 'HD'})">` : 
                            `<i class="fas fa-camera"></i>`
                        }
                    </div>
                `;

                activityList.appendChild(item);
            });
        }

        // Show success message
        function showSuccessMessage(message) {
            const msg = document.createElement('div');
            msg.className = 'success-message';
            msg.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(msg);

            setTimeout(() => {
                msg.remove();
            }, 3000);
        }

        // Logout function
        function logout() {
            showSuccessMessage('Logged out successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }

        // Global functions
        window.switchTab = switchTab;
        window.removeImage = removeImage;
        window.submitActivity = submitActivity;
        window.logout = logout;
        window.addAccomplishment = addAccomplishment;
        window.removeAccomplishment = removeAccomplishment;
        window.handleImageUpload = handleImageUpload;
    </script>
</body>
</html>