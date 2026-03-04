<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pnp System</title>
</head>
<body>
    
    <div class="background"></div>

<div class="login-container">

    <img src="image/pnplogo.png" class="logo">

    <h2>LOGIN</h2>

    <form>

        <label>Email</label>
        <input type="email">

        <label>Password</label>
        <input type="password">

        <button type="submit">LOGIN</button>

        <a style="color:black; " href="register.php" class="register">Register & Create Account</a>

    </form>

</div>


</body>
</html>

<style>
    *{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Times New Roman', Times, serif;
}

body{
height:100vh;
display:flex;
align-items:center;
justify-content:center;
background-image:url("image/pnpBGlogo.jpg");
background-position:center;
background-repeat:no-repeat;
background-size:cover;
background-color:#0a3d62;
}

.background{
position:absolute;
width:100%;
height:100%;
background:rgba(0,0,0,0.2);
}

.login-container{
position:relative;
width:420px;
padding:40px;
background:rgba(255,255,255,0.35);
backdrop-filter:blur(5px);
border-radius:20px;
text-align:center;
}

.logo{
width:80px;
margin-bottom:10px;
}

h2{
margin-bottom:20px;
font-size:28px;
}

form{
display:flex;
flex-direction:column;
}

label{
text-align:left;
font-size:14px;
margin-top:10px;
}

input{
padding:10px;
margin-top:5px;
border:2px solid black;
border-radius:3px;
}

button{
margin-top:20px;
padding:12px;
background:#1f6fb2;
color:white;
font-size:16px;
border:none;
border-radius:25px;
cursor:pointer;
}

.register{
margin-top:10px;
font-size:15px;
}
</style>