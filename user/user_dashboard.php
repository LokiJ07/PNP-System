<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../image/pnplogo.png">
  <title>PNP Manolo Fortich · Activity (Tailwind)</title>
  <!-- Tailwind via CDN + a few extra utilities for keyframes -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome 5 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    /* custom keyframes and loading spin – keep because tailwind doesn't include these by default */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    .animate-fadeIn { animation: fadeIn 0.5s ease; }
    .animate-slideIn { animation: slideIn 0.5s ease; }
    .animate-spin-custom { animation: spin 1s linear infinite; }

    /* keep image loading overlay positioning */
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
      z-index: 10;
    }
    /* small custom for file upload cursor */
    .file-upload * { cursor: pointer; }
  </style>
</head>
<body class="bg-[#eef2f5] min-h-screen p-4 md:p-8 font-sans">

  <div class="max-w-[1400px] mx-auto">

    <!-- ===== USER HEADER ===== -->
    <div class="bg-white rounded-2xl p-4 md:p-6 mb-6 md:mb-8 shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-l-4 border-[#0a2b3c]">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-[#0a2b3c] rounded-full flex items-center justify-center text-[#ffc107] text-2xl">
          <i class="fas fa-shield-alt"></i>
        </div>
        <div>
          <h2 class="text-[#0a2b3c] text-xl font-semibold">PO3 Juan Dela Cruz</h2>
          <p class="text-gray-600 text-sm"><i class="fas fa-id-badge text-[#ffc107] mr-1"></i> Badge #PNP-2024-0123 · Manolo Fortich MPS</p>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <span class="bg-[#0a2b3c] text-[#ffc107] px-5 py-2 rounded-full text-sm font-semibold border border-[#1e4a6a] flex items-center"><i class="fas fa-map-marker-alt mr-2"></i>Manolo Fortich, Bukidnon</span>
        <button class="bg-[#c41e3a] hover:bg-[#a01830] text-white px-5 py-2 rounded-lg font-semibold flex items-center gap-2 transition transform hover:-translate-y-0.5" onclick="logout()">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </div>
    </div>

    <!-- ===== MAIN GRID ===== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- LEFT COLUMN (FORMS) - 2/3 on large -->
      <div class="lg:col-span-2">

        <!-- FORM CARD -->
        <div class="bg-white rounded-2xl p-5 md:p-7 shadow-md border-t-4 border-[#0a2b3c]">
          
          <!-- Section title -->
          <div class="flex items-center gap-3 text-[#0a2b3c] mb-6 pb-3 border-b-2 border-[#e0e7ff]">
            <i class="fas fa-clipboard-list text-3xl text-[#ffc107]"></i>
            <h2 class="text-2xl md:text-3xl font-semibold">Report New Activity</h2>
          </div>

          <!-- Location badge (fixed) -->
          <div class="bg-[#f0f7ff] border-2 border-[#0a2b3c] rounded-full py-3 px-6 mb-6 inline-flex items-center gap-3 text-[#0a2b3c] font-semibold">
            <i class="fas fa-map-marker-alt text-[#ffc107] text-xl"></i>
            <span>Area of Responsibility: Manolo Fortich, Bukidnon</span>
          </div>

          <!-- TABS -->
          <div class="flex flex-wrap gap-3 mb-6">
            <button class="tab-btn active flex-1 min-w-[120px] py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 transition bg-[#0a2b3c] text-white" data-tab="patrol" onclick="switchTab('patrol')">
              <i class="fas fa-walking"></i> Patrol
            </button>
            <button class="tab-btn flex-1 min-w-[120px] py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 transition bg-gray-200 text-gray-700 hover:bg-gray-300" data-tab="checkpoint" onclick="switchTab('checkpoint')">
              <i class="fas fa-map-marker-alt"></i> Checkpoint
            </button>
            <button class="tab-btn flex-1 min-w-[120px] py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 transition bg-gray-200 text-gray-700 hover:bg-gray-300" data-tab="oplan" onclick="switchTab('oplan')">
              <i class="fas fa-shield-alt"></i> Oplan Bakal/Sita
            </button>
          </div>

          <!-- Barangay selector -->
          <div class="mb-6 bg-[#f0f7ff] p-4 rounded-xl">
            <p class="text-[#0a2b3c] mb-3 font-semibold flex items-center"><i class="fas fa-map-pin text-[#ffc107] mr-2"></i> Select Barangay:</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2" id="barangaySelector"></div>
          </div>

          <!-- ========== PATROL FORM ========== -->
          <div id="patrol-form" class="activity-form block">
            <form id="patrolActivityForm" onsubmit="submitActivity(event, 'patrol')">
              <!-- Basic info section -->
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-info-circle text-[#ffc107]"></i> Basic Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-tag w-5 text-[#0a2b3c]"></i> Patrol Type</label>
                    <select class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none bg-white" id="patrolType" required>
                      <option value="">Select patrol type</option>
                      <option value="Foot Patrol">Foot Patrol</option>
                      <option value="Mobile Patrol">Mobile Patrol</option>
                      <option value="Motorcycle Patrol">Motorcycle Patrol</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-location-dot w-5 text-[#0a2b3c]"></i> Specific Location</label>
                    <input type="text" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none" id="patrolLocation" placeholder="e.g., Poblacion, Brgy. Tankulan" required>
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-calendar w-5 text-[#0a2b3c]"></i> Date</label>
                    <input type="date" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none" id="patrolDate" required>
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-clock w-5 text-[#0a2b3c]"></i> Time</label>
                    <input type="time" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none" id="patrolTime" required>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-users w-5 text-[#0a2b3c]"></i> Number of Personnel Deployed</label>
                  <input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none" id="patrolPersonnel" placeholder="Enter number of personnel" min="1" required>
                </div>
              </div>

              <!-- Accomplishments (simplified) -->
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-trophy text-[#ffc107]"></i> Daily Accomplishments - Manolo Fortich</h4>
                <p class="text-gray-600 text-sm mb-4"><i class="fas fa-info-circle mr-2"></i>Describe in detail what you accomplished today in Manolo Fortich. You can add multiple accomplishments.</p>
                <div id="patrolAccomplishments"></div>
                <button type="button" class="bg-[#0a2b3c] hover:bg-[#1e4a6a] text-white px-6 py-2 rounded-full text-sm font-semibold inline-flex items-center gap-2 transition-all mt-2" onclick="addAccomplishment('patrol')">
                  <i class="fas fa-plus text-[#ffc107]"></i> Add Another Accomplishment
                </button>
              </div>

              <!-- Photo upload -->
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-camera text-[#ffc107]"></i> Photo Documentation (HD Images Supported)</h4>
                <div class="border-3 border-dashed border-[#d0dbe8] rounded-2xl p-6 text-center cursor-pointer hover:border-[#0a2b3c] hover:bg-[#f0f7ff] transition file-upload" onclick="document.getElementById('patrolImage').click()">
                  <i class="fas fa-cloud-upload-alt text-5xl text-[#0a2b3c] mb-3"></i>
                  <p class="text-gray-700">Click to upload photos (HD images up to 15MB)</p>
                  <small class="text-gray-500 block">Supports: JPG, PNG, JPEG, HEIC</small>
                  <div class="text-sm text-[#0a2b3c] font-medium mt-2" id="patrolFileInfo"></div>
                  <input type="file" id="patrolImage" accept="image/*" class="hidden" onchange="handleImageUpload(this, 'patrol')">
                </div>
                <!-- preview container -->
                <div id="patrol-preview" class="image-preview-container relative mt-4 rounded-2xl overflow-hidden border-2 border-[#d0dbe8] hidden">
                  <img src="" alt="Preview" id="patrolPreviewImg" class="w-full max-h-80 object-contain bg-gray-100">
                  <div class="bg-[#0a2b3c] bg-opacity-90 text-white px-4 py-2 text-sm flex justify-between items-center">
                    <span><i class="fas fa-image text-[#ffc107] mr-2"></i><span id="patrolImageSize"></span></span>
                    <button type="button" class="bg-[#c41e3a] hover:bg-[#a01830] px-4 py-1 rounded-full text-xs flex items-center gap-1" onclick="removeImage('patrol')"><i class="fas fa-trash"></i> Remove</button>
                  </div>
                </div>
              </div>

              <button type="submit" class="w-full bg-[#0a2b3c] hover:bg-[#1e4a6a] text-white py-4 rounded-xl font-semibold text-lg flex items-center justify-center gap-3 transition transform hover:-translate-y-1 shadow-lg" id="patrolSubmitBtn">
                <i class="fas fa-paper-plane text-[#ffc107]"></i> Submit Patrol Report - Manolo Fortich
              </button>
            </form>
          </div>

          <!-- ========== CHECKPOINT FORM (hidden initially) ========== -->
          <div id="checkpoint-form" class="activity-form hidden">
            <form id="checkpointActivityForm" onsubmit="submitActivity(event, 'checkpoint')">
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-info-circle text-[#ffc107]"></i> Checkpoint Information</h4>
                <div class="mb-4">
                  <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-location-dot w-5 text-[#0a2b3c]"></i> Specific Location</label>
                  <input type="text" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none" id="checkpointLocation" placeholder="e.g., National Highway, Brgy. Tankulan" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-calendar w-5 text-[#0a2b3c]"></i> Date</label>
                    <input type="date" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none" id="checkpointDate" required>
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-clock w-5 text-[#0a2b3c]"></i> Time</label>
                    <input type="time" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl focus:border-[#0a2b3c] outline-none" id="checkpointTime" required>
                  </div>
                </div>
              </div>

              <!-- Checkpoint operations -->
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-map-pin text-[#ffc107]"></i> Checkpoint Operations - Manolo Fortich</h4>
                <div class="space-y-4">
                  <div><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-border-all mr-2 text-[#0a2b3c]"></i> Border Control Point Ops</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="borderControlOps" min="0" value="0"></div>
                  <div><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-users mr-2 text-[#0a2b3c]"></i> Personnel (Border Control)</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="borderPersonnel" min="0" value="0"></div>
                  <div><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-sync-alt mr-2 text-[#0a2b3c]"></i> Overlapping Checkpoint Ops</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="overlappingOps" min="0" value="0"></div>
                  <div><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-truck mr-2 text-[#0a2b3c]"></i> Mobile Checkpoint Ops</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="mobileCheckpointOps" min="0" value="0"></div>
                  <div><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-users mr-2 text-[#0a2b3c]"></i> Personnel (Mobile Checkpoint)</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="mobilePersonnel" min="0" value="0"></div>
                </div>
              </div>

              <!-- Accomplishments specific -->
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-trophy text-[#ffc107]"></i> Accomplishments - Manolo Fortich</h4>
                <div class="mb-4"><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-file-alt mr-2 text-[#0a2b3c]"></i> TCT/OVR (DO NOT INCLUDE ROVMOS)</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="tctOvrAccom" min="0" value="0"></div>
                <div class="mb-4"><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-gavel mr-2 text-[#0a2b3c]"></i> ARRESTED/FOR FILING/REPORT TO R3</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="arrestedAccom" min="0" value="0"></div>
                <p class="text-gray-600 text-sm my-4"><i class="fas fa-info-circle mr-2"></i>Add detailed descriptions of other accomplishments:</p>
                <div id="checkpointAccomplishments"></div>
                <button type="button" class="bg-[#0a2b3c] hover:bg-[#1e4a6a] text-white px-6 py-2 rounded-full text-sm font-semibold inline-flex items-center gap-2" onclick="addAccomplishment('checkpoint')"><i class="fas fa-plus text-[#ffc107]"></i> Add Detailed Accomplishment</button>
              </div>

              <!-- photo upload checkpoint -->
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-camera text-[#ffc107]"></i> Photo Documentation</h4>
                <div class="border-3 border-dashed border-[#d0dbe8] rounded-2xl p-6 text-center cursor-pointer hover:border-[#0a2b3c]" onclick="document.getElementById('checkpointImage').click()">
                  <i class="fas fa-cloud-upload-alt text-5xl text-[#0a2b3c] mb-3"></i>
                  <p class="text-gray-700">Click to upload (HD up to 15MB)</p>
                  <small class="text-gray-500">JPG,PNG,JPEG,HEIC</small>
                  <div class="text-sm text-[#0a2b3c] font-medium mt-2" id="checkpointFileInfo"></div>
                  <input type="file" id="checkpointImage" accept="image/*" class="hidden" onchange="handleImageUpload(this, 'checkpoint')">
                </div>
                <div id="checkpoint-preview" class="image-preview-container relative mt-4 rounded-2xl overflow-hidden hidden border-2 border-[#d0dbe8]">
                  <img src="" alt="Preview" id="checkpointPreviewImg" class="w-full max-h-80 object-contain bg-gray-100">
                  <div class="bg-[#0a2b3c] bg-opacity-90 text-white px-4 py-2 flex justify-between items-center"><span><i class="fas fa-image text-[#ffc107] mr-2"></i><span id="checkpointImageSize"></span></span>
                    <button type="button" class="bg-[#c41e3a] hover:bg-[#a01830] px-4 py-1 rounded-full text-xs" onclick="removeImage('checkpoint')"><i class="fas fa-trash mr-1"></i>Remove</button>
                  </div>
                </div>
              </div>
              <button type="submit" class="w-full bg-[#0a2b3c] hover:bg-[#1e4a6a] text-white py-4 rounded-xl font-semibold text-lg flex items-center justify-center gap-3 transition" id="checkpointSubmitBtn"><i class="fas fa-paper-plane text-[#ffc107]"></i> Submit Checkpoint Report - Manolo Fortich</button>
            </form>
          </div>

          <!-- ========== OPLAN FORM (hidden) same as patrol ========== -->
          <div id="oplan-form" class="activity-form hidden">
            <form id="oplanActivityForm" onsubmit="submitActivity(event, 'oplan')">
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-info-circle text-[#ffc107]"></i> Basic Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div class="mb-4"><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-tag"></i> Oplan Type</label>
                    <select class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="oplanType" required><option value="">Select</option><option value="Oplan Bakal">Oplan Bakal</option><option value="Oplan Sita">Oplan Sita</option></select>
                  </div>
                  <div class="mb-4"><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-location-dot"></i> Specific Location</label><input type="text" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="oplanLocation" placeholder="e.g., Brgy. Tankulan Market" required></div>
                  <div class="mb-4"><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-calendar"></i> Date</label><input type="date" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="oplanDate" required></div>
                  <div class="mb-4"><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-clock"></i> Time</label><input type="time" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="oplanTime" required></div>
                </div>
                <div><label class="block text-gray-700 font-medium text-sm mb-2"><i class="fas fa-users"></i> Personnel Deployed</label><input type="number" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" id="oplanPersonnel" min="1" required></div>
              </div>
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-trophy text-[#ffc107]"></i> Daily Accomplishments - Manolo Fortich</h4>
                <p class="text-gray-600 text-sm mb-4"><i class="fas fa-info-circle mr-2"></i>Describe in detail</p>
                <div id="oplanAccomplishments"></div>
                <button type="button" class="bg-[#0a2b3c] hover:bg-[#1e4a6a] text-white px-6 py-2 rounded-full text-sm font-semibold inline-flex items-center gap-2" onclick="addAccomplishment('oplan')"><i class="fas fa-plus text-[#ffc107]"></i> Add Another Accomplishment</button>
              </div>
              <!-- photo upload oplan -->
              <div class="bg-[#f8faff] border border-[#d0dbe8] rounded-2xl p-5 mb-6">
                <h4 class="flex items-center gap-2 text-[#0a2b3c] font-medium border-b border-[#d0dbe8] pb-3 mb-4"><i class="fas fa-camera text-[#ffc107]"></i> Photo Documentation</h4>
                <div class="border-3 border-dashed border-[#d0dbe8] rounded-2xl p-6 text-center cursor-pointer" onclick="document.getElementById('oplanImage').click()">
                  <i class="fas fa-cloud-upload-alt text-5xl text-[#0a2b3c] mb-3"></i>
                  <p class="text-gray-700">Click to upload (HD up to 15MB)</p>
                  <small class="text-gray-500">JPG,PNG,JPEG,HEIC</small>
                  <div class="text-sm text-[#0a2b3c] font-medium mt-2" id="oplanFileInfo"></div>
                  <input type="file" id="oplanImage" accept="image/*" class="hidden" onchange="handleImageUpload(this, 'oplan')">
                </div>
                <div id="oplan-preview" class="image-preview-container relative mt-4 rounded-2xl overflow-hidden hidden border-2 border-[#d0dbe8]">
                  <img src="" alt="Preview" id="oplanPreviewImg" class="w-full max-h-80 object-contain bg-gray-100">
                  <div class="bg-[#0a2b3c] bg-opacity-90 text-white px-4 py-2 flex justify-between items-center"><span><i class="fas fa-image text-[#ffc107] mr-2"></i><span id="oplanImageSize"></span></span>
                    <button type="button" class="bg-[#c41e3a] hover:bg-[#a01830] px-4 py-1 rounded-full text-xs" onclick="removeImage('oplan')"><i class="fas fa-trash mr-1"></i>Remove</button>
                  </div>
                </div>
              </div>
              <button type="submit" class="w-full bg-[#0a2b3c] hover:bg-[#1e4a6a] text-white py-4 rounded-xl font-semibold text-lg flex items-center justify-center gap-3 transition" id="oplanSubmitBtn"><i class="fas fa-paper-plane text-[#ffc107]"></i> Submit Oplan Report - Manolo Fortich</button>
            </form>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN (stats & recent) -->
      <div class="space-y-6">
        <!-- stats card -->
        <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-[#0a2b3c]">
          <h3 class="flex items-center gap-2 text-[#0a2b3c] font-semibold text-lg border-b border-[#e0e7ff] pb-4 mb-5"><i class="fas fa-chart-pie text-[#ffc107]"></i> Manolo Fortich Summary</h3>
          <div class="space-y-3">
            <div class="stat-item flex justify-between items-center p-4 bg-gray-50 rounded-xl border-l-4 border-[#0a2b3c]"><span class="flex items-center gap-3"><i class="fas fa-walking w-6 text-[#ffc107]"></i> Foot Patrol</span><span class="font-bold text-[#0a2b3c] text-xl">24</span></div>
            <div class="stat-item flex justify-between items-center p-4 bg-gray-50 rounded-xl border-l-4 border-[#0a2b3c]"><span class="flex items-center gap-3"><i class="fas fa-car w-6 text-[#ffc107]"></i> Mobile Patrol</span><span class="font-bold text-[#0a2b3c] text-xl">24</span></div>
            <div class="stat-item flex justify-between items-center p-4 bg-gray-50 rounded-xl border-l-4 border-[#0a2b3c]"><span class="flex items-center gap-3"><i class="fas fa-motorcycle w-6 text-[#ffc107]"></i> Motorcycle Patrol</span><span class="font-bold text-[#0a2b3c] text-xl">24</span></div>
            <div class="stat-item flex justify-between items-center p-4 bg-gray-50 rounded-xl border-l-4 border-[#0a2b3c]"><span class="flex items-center gap-3"><i class="fas fa-map-marker-alt w-6 text-[#ffc107]"></i> Checkpoints</span><span class="font-bold text-[#0a2b3c] text-xl">3</span></div>
            <div class="stat-item flex justify-between items-center p-4 bg-gray-50 rounded-xl border-l-4 border-[#0a2b3c]"><span class="flex items-center gap-3"><i class="fas fa-shield-alt w-6 text-[#ffc107]"></i> Oplan Bakal</span><span class="font-bold text-[#0a2b3c] text-xl">20</span></div>
            <div class="stat-item flex justify-between items-center p-4 bg-gray-50 rounded-xl border-l-4 border-[#0a2b3c]"><span class="flex items-center gap-3"><i class="fas fa-gavel w-6 text-[#ffc107]"></i> Oplan Sita</span><span class="font-bold text-[#0a2b3c] text-xl">28</span></div>
          </div>
        </div>

        <!-- recent activities -->
        <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-[#0a2b3c]">
          <div class="flex justify-between items-center border-b border-[#e0e7ff] pb-4 mb-5">
            <h3 class="flex items-center gap-2 text-[#0a2b3c] font-semibold text-lg"><i class="fas fa-history text-[#ffc107]"></i> Recent Activities</h3>
            <span class="bg-[#0a2b3c] text-[#ffc107] px-4 py-1 rounded-full text-xs font-semibold">Last 5</span>
          </div>
          <div class="activity-list space-y-3" id="activityList"></div>
        </div>
      </div>
    </div>

    <!-- footer -->
    <div class="mt-8 text-center text-[#0a2b3c] text-sm border-t border-[#d0dbe8] pt-6">
      <i class="fas fa-shield-alt text-[#ffc107] mx-1"></i> Philippine National Police - Manolo Fortich Municipal Station <i class="fas fa-shield-alt text-[#ffc107] mx-1"></i><br>
      <small>Area of Responsibility: Municipality of Manolo Fortich, Bukidnon</small>
    </div>
  </div>

  <!-- success / error toasts (fixed) -->
  <div id="toastContainer" class="fixed top-5 right-5 z-50 space-y-2"></div>

  <script>
    // ---------- data & helpers ----------
    const manoloFortichBarangays = [
      "Agusan Canyon","Alae","Dahilayan","Dalirig","Damilag","Dicklum","Guilang-guilang",
      "Kalugmanan","Lindaban","Lurugan","Manolo Fortich Poblacion","Mambatangan","Minsuro",
      "Mantibugao","Sankanan","Santiago","Santo Niño","Tankulan","Ticala"
    ];
    let activities = JSON.parse(localStorage.getItem('pnpManoloFortichActivities')) || [];
    const MAX_FILE_SIZE = 15 * 1024 * 1024;

    document.addEventListener('DOMContentLoaded', () => {
      populateBarangays();
      displayActivities();
      setDefaultDates();
    });

    function populateBarangays() {
      const sel = document.getElementById('barangaySelector');
      sel.innerHTML = '';
      manoloFortichBarangays.forEach(b => {
        const div = document.createElement('div');
        div.className = 'barangay-option bg-white border-2 border-[#d0dbe8] rounded-full py-2 px-3 text-center cursor-pointer text-sm hover:border-[#0a2b3c] hover:bg-[#f0f7ff] transition flex items-center justify-center gap-1';
        div.innerHTML = `<i class="fas fa-map-marker-alt text-gray-500"></i> ${b}`;
        div.onclick = function() {
          document.querySelectorAll('.barangay-option').forEach(opt => opt.classList.remove('selected', 'bg-[#0a2b3c]', 'text-white', 'border-[#0a2b3c]'));
          this.classList.add('selected', 'bg-[#0a2b3c]', 'text-white', 'border-[#0a2b3c]');
          this.querySelector('i').classList.add('text-[#ffc107]');
        };
        sel.appendChild(div);
      });
    }

    function setDefaultDates() {
      const today = new Date().toISOString().split('T')[0];
      const now = new Date().toTimeString().slice(0,5);
      ['patrolDate','checkpointDate','oplanDate'].forEach(id => {
        const el = document.getElementById(id); if(el) el.value = today;
      });
      ['patrolTime','checkpointTime','oplanTime'].forEach(id => {
        const el = document.getElementById(id); if(el) el.value = now;
      });
    }

    window.switchTab = function(tab) {
      document.querySelectorAll('.activity-form').forEach(f => f.classList.add('hidden'));
      document.getElementById(tab+'-form').classList.remove('hidden');
      document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('bg-[#0a2b3c]','text-white');
        b.classList.add('bg-gray-200','text-gray-700');
      });
      const activeBtn = Array.from(document.querySelectorAll('.tab-btn')).find(btn => btn.dataset.tab === tab);
      if(activeBtn) {
        activeBtn.classList.remove('bg-gray-200','text-gray-700');
        activeBtn.classList.add('bg-[#0a2b3c]','text-white');
      }
    };

    // accomplishments (simplified)
    window.addAccomplishment = function(type) {
      const container = document.getElementById(type+'Accomplishments');
      const id = Date.now() + Math.random();
      const div = document.createElement('div');
      div.className = 'accomplishment-item bg-white border border-[#d0dbe8] rounded-2xl p-5 mb-5 shadow-sm';
      div.id = `accomplishment-${id}`;
      div.innerHTML = `
        <div class="flex justify-between items-center mb-3">
          <h5 class="text-[#0a2b3c] font-medium flex items-center gap-2"><i class="fas fa-check-circle text-[#ffc107]"></i> Accomplishment Details</h5>
          <span class="text-[#c41e3a] cursor-pointer text-xl hover:scale-110 transition" onclick="removeAccomplishment('${id}')"><i class="fas fa-times-circle"></i></span>
        </div>
        <div class="mb-4">
          <label class="text-sm font-medium text-gray-700 mb-1 block"><i class="fas fa-heading w-5 text-[#0a2b3c]"></i> Title/Type</label>
          <input type="text" class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" placeholder="e.g., Apprehension" id="accom-title-${id}">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1 block"><i class="fas fa-align-left w-5 text-[#0a2b3c]"></i> Detailed Description</label>
          <textarea class="w-full p-3 border-2 border-[#d0dbe8] rounded-xl" rows="3" placeholder="Describe in detail..." id="accom-desc-${id}"></textarea>
        </div>
      `;
      container.appendChild(div);
    };

    window.removeAccomplishment = (id) => document.getElementById(`accomplishment-${id}`)?.remove();

    // image upload with preview & size check
    window.handleImageUpload = (input, form) => {
      const file = input.files[0];
      if (!file) return;
      if (file.size > MAX_FILE_SIZE) {
        showToast(`File exceeds 15MB (${(file.size/1e6).toFixed(2)}MB)`, 'error');
        input.value = ''; return;
      }
      const previewDiv = document.getElementById(form+'-preview');
      const img = document.getElementById(form+'PreviewImg');
      const sizeSpan = document.getElementById(form+'ImageSize');
      const infoDiv = document.getElementById(form+'FileInfo');
      infoDiv.innerText = `Selected: ${file.name} (${(file.size/1e6).toFixed(2)}MB)`;

      previewDiv.style.display = 'block';
      img.style.opacity = '0.5';
      const loading = document.createElement('div');
      loading.className = 'image-loading'; loading.id = form+'-loading';
      loading.innerHTML = '<i class="fas fa-spinner animate-spin-custom text-[#0a2b3c]"></i> Loading HD image...';
      previewDiv.appendChild(loading);

      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById(form+'-loading')?.remove();
        img.style.opacity = '1';
        img.src = e.target.result;
        sizeSpan.innerText = `${(file.size/1e6).toFixed(2)} MB - HD`;
      };
      reader.onerror = () => { document.getElementById(form+'-loading')?.remove(); img.style.opacity = '1'; showToast('Error loading image','error'); };
      reader.readAsDataURL(file);
    };

    window.removeImage = (form) => {
      const preview = document.getElementById(form+'-preview');
      const fileInp = document.getElementById(form+'Image');
      preview.style.display = 'none';
      document.getElementById(form+'PreviewImg').src = '';
      fileInp.value = '';
      document.getElementById(form+'FileInfo').innerText = '';
      document.getElementById(form+'ImageSize').innerText = '';
    };

    function getSelectedBarangay() {
      const sel = document.querySelector('.barangay-option.selected');
      return sel ? sel.innerText.replace(/^\s*⏺\s*/, '').trim() : 'Not Selected';
    }

    window.submitActivity = (e, type) => {
      e.preventDefault();
      const barangay = getSelectedBarangay();
      if (barangay === 'Not Selected') { showToast('Please select a Barangay first!','error'); return; }

      const btn = document.getElementById(type+'SubmitBtn');
      btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';

      let activity = {
        id: Date.now(), type, timestamp: new Date().toISOString(), image: null,
        title: '', location: '', barangay, municipality: 'Manolo Fortich, Bukidnon',
        date: '', time: '', accomplishments: []
      };

      if (type === 'patrol') {
        activity.patrolType = document.getElementById('patrolType').value;
        activity.location = document.getElementById('patrolLocation').value;
        activity.date = document.getElementById('patrolDate').value;
        activity.time = document.getElementById('patrolTime').value;
        activity.personnel = document.getElementById('patrolPersonnel').value;
        activity.title = activity.patrolType + ' - ' + barangay;
        document.querySelectorAll('#patrolAccomplishments .accomplishment-item').forEach(item => {
          const id = item.id.split('-')[1];
          const title = document.getElementById(`accom-title-${id}`)?.value || '';
          const desc = document.getElementById(`accom-desc-${id}`)?.value || '';
          if (title || desc) activity.accomplishments.push({ title, description: desc });
        });
        handleImageAndSave(activity, 'patrol');
      } else if (type === 'checkpoint') {
        activity.location = document.getElementById('checkpointLocation').value;
        activity.date = document.getElementById('checkpointDate').value;
        activity.time = document.getElementById('checkpointTime').value;
        activity.title = 'Checkpoint Operation - ' + barangay;
        activity.borderControlOps = document.getElementById('borderControlOps').value;
        activity.borderPersonnel = document.getElementById('borderPersonnel').value;
        activity.overlappingOps = document.getElementById('overlappingOps').value;
        activity.mobileCheckpointOps = document.getElementById('mobileCheckpointOps').value;
        activity.mobilePersonnel = document.getElementById('mobilePersonnel').value;
        activity.tctOvrAccom = document.getElementById('tctOvrAccom').value;
        activity.arrestedAccom = document.getElementById('arrestedAccom').value;
        activity.additionalAccomplishments = [];
        document.querySelectorAll('#checkpointAccomplishments .accomplishment-item').forEach(item => {
          const id = item.id.split('-')[1];
          const title = document.getElementById(`accom-title-${id}`)?.value || '';
          const desc = document.getElementById(`accom-desc-${id}`)?.value || '';
          if (title || desc) activity.additionalAccomplishments.push({ title, description: desc });
        });
        handleImageAndSave(activity, 'checkpoint');
      } else if (type === 'oplan') {
        activity.oplanType = document.getElementById('oplanType').value;
        activity.location = document.getElementById('oplanLocation').value;
        activity.date = document.getElementById('oplanDate').value;
        activity.time = document.getElementById('oplanTime').value;
        activity.personnel = document.getElementById('oplanPersonnel').value;
        activity.title = activity.oplanType + ' - ' + barangay;
        document.querySelectorAll('#oplanAccomplishments .accomplishment-item').forEach(item => {
          const id = item.id.split('-')[1];
          const title = document.getElementById(`accom-title-${id}`)?.value || '';
          const desc = document.getElementById(`accom-desc-${id}`)?.value || '';
          if (title || desc) activity.accomplishments.push({ title, description: desc });
        });
        handleImageAndSave(activity, 'oplan');
      }
    };

    function handleImageAndSave(activity, formType) {
      const fileInput = document.getElementById(formType+'Image');
      if (fileInput.files.length) {
        const reader = new FileReader();
        reader.onload = e => {
          activity.image = e.target.result;
          activity.imageName = fileInput.files[0].name;
          activity.imageSize = (fileInput.files[0].size/1e6).toFixed(2) + 'MB';
          saveActivity(activity);
        };
        reader.readAsDataURL(fileInput.files[0]);
      } else {
        saveActivity(activity);
      }
    }

    function saveActivity(act) {
      activities.unshift(act);
      localStorage.setItem('pnpManoloFortichActivities', JSON.stringify(activities));
      resetForm(act.type);
      showToast(`${act.type.charAt(0).toUpperCase()+act.type.slice(1)} report submitted!`, 'success');
      displayActivities();
    }

    function resetForm(type) {
      document.getElementById(type+'ActivityForm').reset();
      removeImage(type);
      setDefaultDates();
      document.getElementById(type+'Accomplishments').innerHTML = '';
      document.querySelectorAll('.barangay-option').forEach(o => o.classList.remove('selected','bg-[#0a2b3c]','text-white','border-[#0a2b3c]'));
      const btn = document.getElementById(type+'SubmitBtn');
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane text-[#ffc107] mr-2"></i> Submit ' + (type==='patrol'?'Patrol':type==='checkpoint'?'Checkpoint':'Oplan') + ' Report - Manolo Fortich';
    }

    function displayActivities() {
      const list = document.getElementById('activityList');
      list.innerHTML = '';
      activities.slice(0,5).forEach(a => {
        const icon = { patrol:'fa-walking', checkpoint:'fa-map-marker-alt', oplan:'fa-shield-alt' }[a.type] || 'fa-file';
        const time = new Date(a.timestamp).toLocaleTimeString();
        const count = a.accomplishments?.length || 0;
        list.innerHTML += `
          <div class="activity-item bg-gray-50 rounded-xl p-4 flex gap-3 items-center border-l-4 ${a.type==='patrol'?'border-[#0a2b3c]':a.type==='checkpoint'?'border-[#1e4a6a]':'border-[#c41e3a]'}">
            <div class="w-10 h-10 bg-[#0a2b3c] rounded-lg flex items-center justify-center text-[#ffc107]"><i class="fas ${icon}"></i></div>
            <div class="flex-1"><div class="font-semibold text-[#0a2b3c]">${a.title}</div><div class="text-xs text-gray-600 flex flex-wrap gap-3"><span><i class="fas fa-map-pin text-[#ffc107] mr-1"></i>${a.barangay}</span><span><i class="fas fa-clock text-[#ffc107] mr-1"></i>${time}</span>${count?`<span><i class="fas fa-trophy text-[#ffc107] mr-1"></i>${count}</span>`:''}</div></div>
            <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center overflow-hidden">${a.image?`<img src="${a.image}" class="w-full h-full object-cover" title="${a.imageName||''}">`:'<i class="fas fa-camera text-gray-500"></i>'}</div>
          </div>`;
      });
    }

    function showToast(msg, type) {
      const toast = document.createElement('div');
      toast.className = `flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg text-white animate-slideIn border-l-4 ${type==='success'?'bg-[#0a2b3c] border-[#ffc107]':'bg-[#c41e3a] border-[#ffc107]'}`;
      toast.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'} text-[#ffc107]"></i><span>${msg}</span>`;
      document.getElementById('toastContainer').appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }

    window.logout = () => { showToast('Logged out successfully!','success'); setTimeout(() => location.reload(),1500); };
  </script>
</body>
</html>