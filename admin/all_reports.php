<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Accomplishment Report</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
        
        /* Print styles - hide everything except the report content starting from Republic */
        @media print {
            @page {
                size: A4;
                margin: 1.5cm;
            }
            
            body { 
                background: white; 
                margin: 0;
                padding: 0;
            }
            
            /* Hide everything by default */
            .no-print, 
            .sidebar, 
            .flex-1 > .bg-white:first-of-type,
            .flex-1 > .bg-white:nth-of-type(2) {
                display: none !important;
            }
            
            /* Show only the report content starting from Republic */
            #reportContent {
                display: block !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                padding: 0 !important;
                margin: 0 !important;
                background: white;
                box-shadow: none;
            }
            
            /* Ensure Republic header is at the very top */
            #reportContent .text-center:first-of-type {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Remove any extra spacing */
            .flex-1 {
                padding: 0 !important;
                background: white !important;
            }
            
            /* Ensure tables print properly */
            table {
                page-break-inside: avoid;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
        }
        
        .report-header {
            border-bottom: 3px solid #08324f;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 5px;
        }
    </style>
</head>
<body class="flex bg-[#0a3d62] print:bg-white">

    <!-- Sidebar (hidden when printing) -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto no-print">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_dashboard.php" class="text-white no-underline block">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="checkpoint.php" class="text-white no-underline block">
                    <i class="fas fa-map-marker-alt mr-3"></i> Checkpoint
                </a>
            </li>
            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-walking mr-3"></i> Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="motorpatrol.php" class="text-white no-underline block">Motorcycle Patrol</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-shield-alt mr-3"></i> Oplan Bakal / Sita</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_users.php" class="text-white no-underline block">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </li>
            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="accomplishment_report.php" class="text-white no-underline block">
                    <i class="fas fa-file-alt mr-3"></i> Accomplishment Report
                </a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../index.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen print:bg-white print:p-0">
        
        <!-- Header with Controls (hidden when printing) -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 flex flex-wrap items-center justify-between gap-4 no-print">
            <div>
                <h2 class="text-2xl font-bold text-[#08324f]">Accomplishment Report</h2>
                <p class="text-gray-600 mt-1">Based on actual user-submitted reports</p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62] flex items-center gap-2">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <button onclick="exportToPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> Save as PDF
                </button>
            </div>
        </div>

        <!-- Date Range Filter (hidden when printing) -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 no-print">
            <h3 class="text-md font-semibold text-[#08324f] mb-4">Select Report Period</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From Month</label>
                    <select id="fromMonth" class="w-full p-2.5 border border-gray-300 rounded-lg">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From Year</label>
                    <select id="fromYear" class="w-full p-2.5 border border-gray-300 rounded-lg">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To Month</label>
                    <select id="toMonth" class="w-full p-2.5 border border-gray-300 rounded-lg">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To Year</label>
                    <select id="toYear" class="w-full p-2.5 border border-gray-300 rounded-lg">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="generateReport()" class="w-full bg-[#1f6fb2] text-white py-2.5 rounded-lg hover:bg-[#0a3d62]">
                        <i class="fas fa-sync-alt mr-2"></i> Generate Report
                    </button>
                </div>
            </div>
            
            <!-- Officer Filter -->
            <div class="mt-4">
                <label class="block text-xs text-gray-500 mb-1">Filter by Officer (Optional)</label>
                <select id="officerFilter" class="w-full md:w-1/3 p-2.5 border border-gray-300 rounded-lg">
                    <option value="all">All Officers</option>
                    <option value="1">PO3 Juan Dela Cruz</option>
                    <option value="2">SPO1 Maria Santos</option>
                    <option value="3">PO2 Pedro Reyes</option>
                    <option value="4">SPO2 Ana Lopez</option>
                </select>
            </div>
        </div>

        <!-- ===== THE ACTUAL ACCOMPLISHMENT REPORT WITH USER DATA ===== -->
        <!-- This is the only content that will print, starting with Republic header -->
        <div id="reportContent" class="bg-white p-8 rounded-lg shadow-md print:shadow-none print:p-0 print:m-0">
            
            <!-- REPUBLIC HEADER - This will be at the very top when printing -->
            <div class="text-center mb-6 print:mt-0">
                <div class="flex justify-center items-center gap-4 mb-2">
                    <img src="../image/pnplogo.png" class="w-16 h-16" alt="PNP Logo">
                    <div>
                        <h1 class="text-2xl font-bold text-[#08324f]">REPUBLIC OF THE PHILIPPINES</h1>
                        <h2 class="text-xl font-semibold">NATIONAL POLICE COMMISSION</h2>
                        <h3 class="text-lg">PHILIPPINE NATIONAL POLICE</h3>
                    </div>
                </div>
                <div class="border-t-2 border-b-2 border-[#08324f] py-2 mt-2">
                    <p class="font-bold">MANOLO FORTICH MUNICIPAL POLICE STATION</p>
                    <p class="text-sm">Poblacion, Manolo Fortich, Bukidnon</p>
                </div>
            </div>

            <!-- Report Title -->
            <div class="text-center my-6">
                <h2 class="text-xl font-bold uppercase underline">Consolidated Accomplishment Report</h2>
                <p class="text-lg" id="reportPeriodDisplay">For the Period: June 1-30, 2026</p>
            </div>

            <!-- Officer Summary Card -->
            <div class="mb-6">
                <h3 class="font-bold text-md bg-gray-100 p-2 mb-3">PERSONNEL INVOLVED</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                    <div class="bg-blue-50 p-3 rounded border-l-4 border-blue-500">
                        <p class="text-xs text-gray-600">Total Officers</p>
                        <p class="text-2xl font-bold text-[#08324f]">24</p>
                        <p class="text-xs text-gray-500">Active personnel</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded border-l-4 border-green-500">
                        <p class="text-xs text-gray-600">Officers with Reports</p>
                        <p class="text-2xl font-bold text-[#08324f]">18</p>
                        <p class="text-xs text-gray-500">75% participation</p>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded border-l-4 border-yellow-500">
                        <p class="text-xs text-gray-600">Total Man Hours</p>
                        <p class="text-2xl font-bold text-[#08324f]">2,456</p>
                        <p class="text-xs text-gray-500">Combined duty hours</p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded border-l-4 border-purple-500">
                        <p class="text-xs text-gray-600">Avg Reports/Officer</p>
                        <p class="text-2xl font-bold text-[#08324f]">24.3</p>
                        <p class="text-xs text-gray-500">Per active officer</p>
                    </div>
                </div>
                
                <!-- Top Performing Officers -->
                <table class="w-full border-collapse border border-gray-300 mb-3">
                    <thead>
                        <tr class="bg-[#08324f] text-white">
                            <th class="border border-gray-300 p-2 text-left">Rank</th>
                            <th class="border border-gray-300 p-2 text-left">Name</th>
                            <th class="border border-gray-300 p-2 text-center">Patrols</th>
                            <th class="border border-gray-300 p-2 text-center">Checkpoints</th>
                            <th class="border border-gray-300 p-2 text-center">Oplan</th>
                            <th class="border border-gray-300 p-2 text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-300 p-2">PO3</td>
                            <td class="border border-gray-300 p-2">Juan Dela Cruz</td>
                            <td class="border border-gray-300 p-2 text-center">45</td>
                            <td class="border border-gray-300 p-2 text-center">12</td>
                            <td class="border border-gray-300 p-2 text-center">8</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">65</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">SPO1</td>
                            <td class="border border-gray-300 p-2">Maria Santos</td>
                            <td class="border border-gray-300 p-2 text-center">38</td>
                            <td class="border border-gray-300 p-2 text-center">24</td>
                            <td class="border border-gray-300 p-2 text-center">6</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">68</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">PO2</td>
                            <td class="border border-gray-300 p-2">Pedro Reyes</td>
                            <td class="border border-gray-300 p-2 text-center">52</td>
                            <td class="border border-gray-300 p-2 text-center">8</td>
                            <td class="border border-gray-300 p-2 text-center">12</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">72</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- I. PATROL ACCOMPLISHMENTS (BY OFFICER) -->
            <div class="mb-6">
                <h3 class="font-bold text-md bg-gray-100 p-2 mb-3">I. PATROL OPERATIONS (By Officer)</h3>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-[#08324f] text-white">
                            <th class="border border-gray-300 p-2 text-left">Officer</th>
                            <th class="border border-gray-300 p-2 text-center">Foot Patrol</th>
                            <th class="border border-gray-300 p-2 text-center">Mobile Patrol</th>
                            <th class="border border-gray-300 p-2 text-center">Motor Patrol</th>
                            <th class="border border-gray-300 p-2 text-center">Total</th>
                            <th class="border border-gray-300 p-2 text-left">Accomplishments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-300 p-2">PO3 J. Dela Cruz</td>
                            <td class="border border-gray-300 p-2 text-center">18</td>
                            <td class="border border-gray-300 p-2 text-center">15</td>
                            <td class="border border-gray-300 p-2 text-center">12</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">45</td>
                            <td class="border border-gray-300 p-2">12 assists, 3 citations</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">SPO1 M. Santos</td>
                            <td class="border border-gray-300 p-2 text-center">12</td>
                            <td class="border border-gray-300 p-2 text-center">14</td>
                            <td class="border border-gray-300 p-2 text-center">12</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">38</td>
                            <td class="border border-gray-300 p-2">8 assists, 5 citations</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">PO2 P. Reyes</td>
                            <td class="border border-gray-300 p-2 text-center">20</td>
                            <td class="border border-gray-300 p-2 text-center">18</td>
                            <td class="border border-gray-300 p-2 text-center">14</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">52</td>
                            <td class="border border-gray-300 p-2">15 assists, 7 citations</td>
                        </tr>
                        <tr class="bg-gray-100 font-semibold">
                            <td class="border border-gray-300 p-2">TOTAL</td>
                            <td class="border border-gray-300 p-2 text-center">50</td>
                            <td class="border border-gray-300 p-2 text-center">47</td>
                            <td class="border border-gray-300 p-2 text-center">38</td>
                            <td class="border border-gray-300 p-2 text-center">135</td>
                            <td class="border border-gray-300 p-2">35 assists, 15 citations</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- II. CHECKPOINT ACCOMPLISHMENTS (BY OFFICER) -->
            <div class="mb-6">
                <h3 class="font-bold text-md bg-gray-100 p-2 mb-3">II. CHECKPOINT OPERATIONS (By Officer)</h3>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-[#08324f] text-white">
                            <th class="border border-gray-300 p-2 text-left">Officer</th>
                            <th class="border border-gray-300 p-2 text-center">Border Control</th>
                            <th class="border border-gray-300 p-2 text-center">Mobile CP</th>
                            <th class="border border-gray-300 p-2 text-center">Overlapping</th>
                            <th class="border border-gray-300 p-2 text-center">Total</th>
                            <th class="border border-gray-300 p-2 text-center">TCT/OVR</th>
                            <th class="border border-gray-300 p-2 text-center">Arrests</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-300 p-2">PO3 J. Dela Cruz</td>
                            <td class="border border-gray-300 p-2 text-center">5</td>
                            <td class="border border-gray-300 p-2 text-center">4</td>
                            <td class="border border-gray-300 p-2 text-center">3</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">12</td>
                            <td class="border border-gray-300 p-2 text-center">4</td>
                            <td class="border border-gray-300 p-2 text-center">2</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">SPO1 M. Santos</td>
                            <td class="border border-gray-300 p-2 text-center">10</td>
                            <td class="border border-gray-300 p-2 text-center">8</td>
                            <td class="border border-gray-300 p-2 text-center">6</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">24</td>
                            <td class="border border-gray-300 p-2 text-center">8</td>
                            <td class="border border-gray-300 p-2 text-center">5</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">PO2 P. Reyes</td>
                            <td class="border border-gray-300 p-2 text-center">3</td>
                            <td class="border border-gray-300 p-2 text-center">3</td>
                            <td class="border border-gray-300 p-2 text-center">2</td>
                            <td class="border border-gray-300 p-2 text-center font-bold">8</td>
                            <td class="border border-gray-300 p-2 text-center">2</td>
                            <td class="border border-gray-300 p-2 text-center">1</td>
                        </tr>
                        <tr class="bg-gray-100 font-semibold">
                            <td class="border border-gray-300 p-2">TOTAL</td>
                            <td class="border border-gray-300 p-2 text-center">18</td>
                            <td class="border border-gray-300 p-2 text-center">15</td>
                            <td class="border border-gray-300 p-2 text-center">11</td>
                            <td class="border border-gray-300 p-2 text-center">44</td>
                            <td class="border border-gray-300 p-2 text-center">14</td>
                            <td class="border border-gray-300 p-2 text-center">8</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- III. OPLAN ACCOMPLISHMENTS (BY OFFICER) -->
            <div class="mb-6">
                <h3 class="font-bold text-md bg-gray-100 p-2 mb-3">III. OPLAN OPERATIONS (By Officer)</h3>
                
                <table class="w-full border-collapse border border-gray-300 mb-3">
                    <thead>
                        <tr class="bg-[#08324f] text-white">
                            <th class="border border-gray-300 p-2 text-left" rowspan="2">Officer</th>
                            <th class="border border-gray-300 p-2 text-center" colspan="3">Oplan Bakal</th>
                            <th class="border border-gray-300 p-2 text-center" colspan="3">Oplan Sita</th>
                        </tr>
                        <tr class="bg-[#1f6fb2] text-white">
                            <th class="border border-gray-300 p-2 text-center">Ops</th>
                            <th class="border border-gray-300 p-2 text-center">Firearms</th>
                            <th class="border border-gray-300 p-2 text-center">Arrests</th>
                            <th class="border border-gray-300 p-2 text-center">Ops</th>
                            <th class="border border-gray-300 p-2 text-center">Contraband</th>
                            <th class="border border-gray-300 p-2 text-center">Arrests</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-300 p-2">PO3 J. Dela Cruz</td>
                            <td class="border border-gray-300 p-2 text-center">3</td>
                            <td class="border border-gray-300 p-2 text-center">1</td>
                            <td class="border border-gray-300 p-2 text-center">2</td>
                            <td class="border border-gray-300 p-2 text-center">5</td>
                            <td class="border border-gray-300 p-2 text-center">2.5kg</td>
                            <td class="border border-gray-300 p-2 text-center">4</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">SPO1 M. Santos</td>
                            <td class="border border-gray-300 p-2 text-center">2</td>
                            <td class="border border-gray-300 p-2 text-center">0</td>
                            <td class="border border-gray-300 p-2 text-center">1</td>
                            <td class="border border-gray-300 p-2 text-center">4</td>
                            <td class="border border-gray-300 p-2 text-center">1.8kg</td>
                            <td class="border border-gray-300 p-2 text-center">3</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2">PO2 P. Reyes</td>
                            <td class="border border-gray-300 p-2 text-center">5</td>
                            <td class="border border-gray-300 p-2 text-center">2</td>
                            <td class="border border-gray-300 p-2 text-center">3</td>
                            <td class="border border-gray-300 p-2 text-center">7</td>
                            <td class="border border-gray-300 p-2 text-center">3.2kg</td>
                            <td class="border border-gray-300 p-2 text-center">5</td>
                        </tr>
                        <tr class="bg-gray-100 font-semibold">
                            <td class="border border-gray-300 p-2">TOTAL</td>
                            <td class="border border-gray-300 p-2 text-center">10</td>
                            <td class="border border-gray-300 p-2 text-center">3</td>
                            <td class="border border-gray-300 p-2 text-center">6</td>
                            <td class="border border-gray-300 p-2 text-center">16</td>
                            <td class="border border-gray-300 p-2 text-center">7.5kg</td>
                            <td class="border border-gray-300 p-2 text-center">12</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- IV. DETAILED ACCOMPLISHMENTS (By Officer) -->
            <div class="mb-6">
                <h3 class="font-bold text-md bg-gray-100 p-2 mb-3">IV. DETAILED ACCOMPLISHMENTS</h3>
                
                <!-- PO3 Juan Dela Cruz -->
                <div class="mb-4 border border-gray-300 p-3">
                    <h4 class="font-bold text-[#08324f] mb-2">PO3 JUAN DELA CRUZ (Badge: PNP-2024-0123)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-semibold">Patrol Accomplishments:</p>
                            <ul class="list-disc list-inside text-sm ml-2">
                                <li>45 total patrols (18 foot, 15 mobile, 12 motor)</li>
                                <li>Assisted 12 senior citizens</li>
                                <li>Issued 3 traffic citations</li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Checkpoint Accomplishments:</p>
                            <ul class="list-disc list-inside text-sm ml-2">
                                <li>12 checkpoint operations</li>
                                <li>4 TCT/OVR accomplishments</li>
                                <li>2 arrests made</li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Oplan Accomplishments:</p>
                            <ul class="list-disc list-inside text-sm ml-2">
                                <li>3 Oplan Bakal ops - 1 firearm seized, 2 arrests</li>
                                <li>5 Oplan Sita ops - 2.5kg contraband, 4 arrests</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- SPO1 Maria Santos -->
                <div class="mb-4 border border-gray-300 p-3">
                    <h4 class="font-bold text-[#08324f] mb-2">SPO1 MARIA SANTOS (Badge: PNP-2024-0124)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-semibold">Patrol Accomplishments:</p>
                            <ul class="list-disc list-inside text-sm ml-2">
                                <li>38 total patrols (12 foot, 14 mobile, 12 motor)</li>
                                <li>Assisted 8 civilians</li>
                                <li>Issued 5 traffic citations</li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Checkpoint Accomplishments:</p>
                            <ul class="list-disc list-inside text-sm ml-2">
                                <li>24 checkpoint operations</li>
                                <li>8 TCT/OVR accomplishments</li>
                                <li>5 arrests made</li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Oplan Accomplishments:</p>
                            <ul class="list-disc list-inside text-sm ml-2">
                                <li>2 Oplan Bakal ops - 1 arrest</li>
                                <li>4 Oplan Sita ops - 1.8kg contraband, 3 arrests</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- V. NARRATIVE REPORT -->
            <div class="mb-6">
                <h3 class="font-bold text-md bg-gray-100 p-2 mb-3">V. NARRATIVE REPORT</h3>
                <div class="border border-gray-300 p-4">
                    <p class="mb-2">During the reporting period, <strong>18 out of 24 personnel</strong> (75% participation rate) submitted accomplishment reports totaling <strong>135 patrol operations, 44 checkpoint operations, and 26 Oplan operations</strong>.</p>
                    <p class="mb-2">Total man-hours logged: <strong>2,456 hours</strong> with an average of <strong>24.3 reports per officer</strong>. The top performing officer was <strong>PO2 Pedro Reyes</strong> with 72 total activities.</p>
                    <p class="mb-2">Key accomplishments include: <strong>35 civilian assists, 15 traffic citations, 14 TCT/OVR accomplishments, 8 arrests from checkpoints, 3 firearms seized, 7.5kg contraband, and 12 arrests from Oplan operations</strong>.</p>
                    <p>All operations were conducted professionally and in accordance with PNP standards. No administrative or operational issues were reported.</p>
                </div>
            </div>

            <!-- VI. SIGNATORIES -->
            <div class="grid grid-cols-2 gap-8 mt-10 mb-4">
                <div class="text-center">
                    <p class="font-bold">Prepared by:</p>
                    <div class="mt-8">
                        <p class="font-semibold">PO3 JUAN DELA CRUZ</p>
                        <p class="text-sm">Police Officer 3</p>
                        <p class="text-sm">Administrative Officer</p>
                    </div>
                    <div class="signature-line mx-auto"></div>
                    <p class="text-xs mt-1">Signature Over Printed Name</p>
                </div>
                <div class="text-center">
                    <p class="font-bold">Noted by:</p>
                    <div class="mt-8">
                        <p class="font-semibold">PMAJ. MARIA SANTOS</p>
                        <p class="text-sm">Chief of Police</p>
                        <p class="text-sm">Manolo Fortich MPS</p>
                    </div>
                    <div class="signature-line mx-auto"></div>
                    <p class="text-xs mt-1">Signature Over Printed Name</p>
                </div>
            </div>

            <!-- Date -->
            <div class="text-right mt-4">
                <p class="text-sm"><span class="font-semibold">Date:</span> <span id="reportDate">June 30, 2026</span></p>
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown(element) {
            const parent = element.closest('.dropdown');
            parent.classList.toggle('active');
            const arrow = element.querySelector('.fa-chevron-down');
            if (arrow) arrow.classList.toggle('rotate-180');
        }

        document.querySelectorAll('.dropdown > div').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const current = this.closest('.dropdown');
                document.querySelectorAll('.dropdown').forEach(drop => {
                    if (drop !== current) {
                        drop.classList.remove('active');
                        const arrow = drop.querySelector('.fa-chevron-down');
                        if (arrow) arrow.classList.remove('rotate-180');
                    }
                });
            });
        });

        // Generate Report based on selected filters
        function generateReport() {
            const fromMonth = document.getElementById('fromMonth').value;
            const fromYear = document.getElementById('fromYear').value;
            const toMonth = document.getElementById('toMonth').value;
            const toYear = document.getElementById('toYear').value;
            const officer = document.getElementById('officerFilter').value;
            
            const monthNames = {
                '01': 'January', '02': 'February', '03': 'March', '04': 'April',
                '05': 'May', '06': 'June', '07': 'July', '08': 'August',
                '09': 'September', '10': 'October', '11': 'November', '12': 'December'
            };
            
            let periodText = '';
            if (fromMonth === toMonth && fromYear === toYear) {
                periodText = `${monthNames[fromMonth]} ${fromYear}`;
            } else {
                periodText = `${monthNames[fromMonth]} ${fromYear} to ${monthNames[toMonth]} ${toYear}`;
            }
            
            document.getElementById('reportPeriodDisplay').innerHTML = `For the Period: ${periodText}`;
            document.getElementById('reportDate').innerHTML = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            
            let officerText = officer === 'all' ? 'All Officers' : 'Selected Officer';
            alert(`Generating report for ${periodText}\nFilter: ${officerText}\n\nIn a real application, this would query the database for actual user-submitted reports.`);
        }

        // Export to PDF
        function exportToPDF() {
            window.print();
        }

        // Set default to current month
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            
            document.getElementById('fromMonth').value = month;
            document.getElementById('fromYear').value = year;
            document.getElementById('toMonth').value = month;
            document.getElementById('toYear').value = year;
            
            const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            document.getElementById('reportPeriodDisplay').innerHTML = `For the Period: ${monthNames[now.getMonth()]} ${year}`;
        });
    </script>
</body>
</html>