<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../image/pnplogo.png">
<title>PNP | Users Management</title>

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial;
}

body{
display:flex;
background:#0a3d62;
}

/* sidebar */

.sidebar{
width:240px;
height:100vh;
background:#08324f;
color:white;
padding:20px;
}

.logo{
display:flex;
align-items:center;
gap:10px;
margin-bottom:25px;
}

.logo img{
width:30px;
height:30px;
}

.sidebar ul{
list-style:none;
}

.sidebar ul li{
padding:12px;
border-radius:5px;
cursor:pointer;
}

.sidebar ul li:hover{
background:#0a3d62;
}

.sidebar a{
color:white;
text-decoration:none;
display:block;
}

/* dropdown */

.dropdown-content{
display:none;
padding-left:15px;
}

.dropdown:hover .dropdown-content{
display:block;
}

/* main */

.main{
flex:1;
background:#eef2f6;
padding:30px;
}

/* tabs */

.tabs{
margin-top:20px;
}

.tab-btn{
background:#08324f;
color:white;
padding:8px 16px;
border:none;
border-radius:4px;
cursor:pointer;
margin-right:5px;
}

.tab-btn:hover{
background:#06263d;
}

/* table */

.table-container{
background:white;
padding:20px;
border-radius:8px;
box-shadow:0 3px 8px rgba(0,0,0,0.1);
margin-top:20px;
}

table{
width:100%;
border-collapse:collapse;
}

table th{
background:#08324f;
color:white;
padding:12px;
text-align:left;
}

table td{
padding:12px;
border-bottom:1px solid #ddd;
}

.view-btn, .status-btn{
background:#0a3d62;
color:white;
padding:6px 12px;
border:none;
border-radius:4px;
cursor:pointer;
margin-right:5px;
}

.view-btn:hover, .status-btn:hover{
background:#06263d;
}
</style>
</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

<div class="logo">
<img src="../image/pnplogo.png">
<h2>PNP Admin</h2>
</div>

<ul>

<li><a href="admin_dashboard.php">Dashboard</a></li>

<li><a href="checkpoint.php">Checkpoint</a></li>

<li class="dropdown">
Patrol
<ul class="dropdown-content">
<li>Foot Patrol</li>
<li>Mobile Patrol</li>
<li>Motorcycle Patrol</li>
</ul>
</li>

<li class="dropdown">
Oplan Bakal / Sita
<ul class="dropdown-content">
<li>Oplan Bakal</li>
<li>Oplan Sita</li>
</ul>
</li>

<li>Users</li>

<li><a href="../index.php">Logout</a></li>

</ul>

</div>

<!-- MAIN -->

<div class="main">

<h2>Users Management</h2>

<div class="tabs">
<button class="tab-btn" onclick="showTab('active')">Active Users</button>
<button class="tab-btn" onclick="showTab('inactive')">Inactive Users</button>
</div>

<!-- Active Users Table -->
<div id="active" class="table-container">
<table>
<tr>
<th>Name</th>
<th>Email</th>
<th>Status</th>
<th>Action</th>
</tr>
<tr>
<td>Juan Dela Cruz</td>
<td>juan@example.com</td>
<td>Active</td>
<td>
<button class="status-btn">Deactivate</button>
<button class="view-btn">View</button>
</td>
</tr>
<tr>
<td>Maria Santos</td>
<td>maria@example.com</td>
<td>Active</td>
<td>
<button class="status-btn">Deactivate</button>
<button class="view-btn">View</button>
</td>
</tr>
</table>
</div>

<!-- Inactive Users Table -->
<div id="inactive" class="table-container" style="display:none;">
<table>
<tr>
<th>Name</th>
<th>Email</th>
<th>Status</th>
<th>Action</th>
</tr>
<tr>
<td>Pedro Reyes</td>
<td>pedro@example.com</td>
<td>Inactive</td>
<td>
<button class="status-btn">Activate</button>
<button class="view-btn">View</button>
</td>
</tr>
</table>
</div>

</div>

<script>
function showTab(tabName){
document.getElementById('active').style.display='none';
document.getElementById('inactive').style.display='none';
document.getElementById(tabName).style.display='block';
}
</script>

</body>
</html>