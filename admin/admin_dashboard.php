<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../image/pnplogo.png">
<title>PNP | Dashboard</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial;
}

/* layout */

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

/* logo */

.logo{
display:flex;
align-items:center;
gap:10px;
margin-bottom:25px;
}

.logo img{
width:32px;
height:32px;
object-fit:contain;
}

.logo h2{
font-size:20px;
font-weight:600;
}

/* menu */

.sidebar ul{
list-style:none;
}

.sidebar ul li{
padding:12px;
cursor:pointer;
border-radius:5px;
}

.sidebar ul li:hover{
background:#0a3d62;
}

/* dropdown */

.dropdown-content{
display:none;
padding-left:15px;
margin-top:5px;
}

.dropdown-content li{
font-size:14px;
padding:8px 0;
}

.dropdown:hover .dropdown-content{
display:block;
}

/* links */

.sidebar a{
color:white;
text-decoration:none;
display:block;
}

/* main content */

.main{
flex:1;
padding:30px;
background:#eef2f6;
}

/* cards */

.cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-top:20px;
}

.card{
background:white;
padding:20px;
border-radius:8px;
box-shadow:0 3px 8px rgba(0,0,0,0.1);
}

/* patrol summary */

.patrol-list{
margin-top:10px;
}

.patrol-item{
display:flex;
justify-content:space-between;
padding:6px 0;
border-bottom:1px solid #eee;
font-size:15px;
}

.patrol-item:last-child{
border-bottom:none;
}

.card p{
font-size:24px;
color:#0a3d62;
font-weight:bold;
}

/* charts */

.chart-area{
margin-top:25px;
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.chart-box{
height:220px;
background:white;
border-radius:8px;
box-shadow:0 3px 8px rgba(0,0,0,0.1);
}

</style>
</head>

<body>

<div class="sidebar">

<div class="logo">
<img src="../image/pnplogo.png" alt="PNP Logo">
<h2>PNP Admin</h2>
</div>

<ul>

<li>Dashboard</li>

<li><a style="color: white;" href="checkpoint.php">Checkpoint</a></li>

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

<li><a style="color:white;" href="admin_users.php">Users</a></li>

<li>
<a href="../index.php">Logout</a>
</li>

</ul>


</div>

<div class="main">

<h2>Dashboard Overview</h2>
<p>Welcome back. System monitoring panel.</p>

<div class="cards">

<div class="card">

<h3>Patrol Summary</h3>

<div class="patrol-list">

<div class="patrol-item">
<span>Foot Patrol</span>
<span>24</span>
</div>

<div class="patrol-item">
<span>Mobile Patrol</span>
<span>24</span>
</div>

<div class="patrol-item">
<span>Motorcycle Patrol</span>
<span>24</span>
</div>

</div>

</div>

<div class="card">
<h3>Checkpoint</h3>
<p>3</p>
</div>

<div class="card">
<h3>Oplan Bakal</h3>
<p>20</p>
</div>

<div class="card">
<h3>Oplan Sita</h3>
<p>28</p>
</div>

</div>

<div class="chart-area">

<div class="chart-box"></div>
<div class="chart-box"></div>

</div>

</div>

</body>
</html>