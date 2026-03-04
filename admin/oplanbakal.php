<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../image/pnplogo.png">
<title>PNP | Oplan Bakal Reports</title>

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

.sidebar ul{
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

/* main */

.main{
flex:1;
background:#eef2f6;
padding:30px;
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

.view-btn{
background:#0a3d62;
color:white;
padding:6px 12px;
border:none;
border-radius:4px;
cursor:pointer;
}

.view-btn:hover{
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
<a href="javascript:void(0)" class="dropbtn">Patrol</a>
<ul class="dropdown-content">
<li><a href="footpatrol.php">Foot Patrol</a></li>
<li><a href="mobilepatrol.php">Mobile Patrol</a></li>
<li><a href="motorpatrol.php">Motorcycle Patrol</a></li>
</ul>
</li>

<li class="dropdown">
<a href="javascript:void(0)" class="dropbtn">Oplan Bakal / Sita</a>
<ul class="dropdown-content">
<li><a href="oplanbakal.php">Oplan Bakal</a></li>
<li><a href="oplansita.php">Oplan Sita</a></li>
</ul>
</li>

<li><a href="admin_users.php">Users</a></li>

<li><a href="../index.php">Logout</a></li>

</ul>

</div>

<script>
const dropdowns = document.querySelectorAll('.dropdown');

dropdowns.forEach(drop => {
    const btn = drop.querySelector('.dropbtn');
    const menu = drop.querySelector('.dropdown-content');

    btn.addEventListener('click', () => {
        // Close other dropdowns
        dropdowns.forEach(d => {
            if(d !== drop) d.querySelector('.dropdown-content').style.display = 'none';
        });

        // Toggle this dropdown
        if(menu.style.display === 'block'){
            menu.style.display = 'none';
        } else {
            menu.style.display = 'block';
        }
    });
});
</script>

<!-- MAIN -->

<div class="main">

<h2>Oplan Bakal Reports</h2>

<div class="table-container">

<table>

<tr>
<th>Name</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Action</th>
</tr>

<tr>
<td>Juan Dela Cruz</td>
<td>June 10, 2026</td>
<td>9:30 AM</td>
<td>Pending</td>
<td><button class="view-btn">View</button></td>
</tr>

<tr>
<td>Maria Santos</td>
<td>June 10, 2026</td>
<td>10:15 AM</td>
<td>Approved</td>
<td><button class="view-btn">View</button></td>
</tr>

<tr>
<td>Pedro Reyes</td>
<td>June 11, 2026</td>
<td>7:45 AM</td>
<td>Pending</td>
<td><button class="view-btn">View</button></td>
</tr>

</table>

</div>

</div>

</body>
</html>