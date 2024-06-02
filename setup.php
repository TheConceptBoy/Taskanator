<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/setup.css">
    <script src="scripts\jquery-3.7.1.min.js"></script>
    <script src="scripts/setup.js"></script>
</head>
<body>
    <div class="top_text">Initial Setup for Taskanator</div>
    <div class="page_content">
        <h2>Establish Database:</h2>
        <div class="two_half">
            <div class="stage_desc">
                Provide the username and password for your database engine. Also provide the name of the database which will be created to store taskanator boards, users, notes and other data.
            </div>
            <div class="create_db_stage" id="setup_form">
                <input type="username" placeholder="username">
                <input type="password" placeholder="password">
                <input type="text" placeholder="database to create">
                <input type="button" value="Perform Initial Setup" onClick="run_setup(this)">
            </div>
        </div>

        <h2>Register Master User:</h2>
        <div class="two_half">
            <div class="stage_desc">
                This is where you register the master account under which you will be creating your project boards, notes, and managing other users who have access to your boards. 
            </div>
            <form id="master_user_data" class="create_db_stage">
                <input disabled type="username" name="master_user_email" placeholder="master user email">
                <input disabled type="password" name="master_password" placeholder="master user password">
                <input disabled type="text" name="master_fname" placeholder="master user first name">
                <input disabled type="text" name="master_lname" placeholder="master user last name">
                <input disabled type="button" value="Register Master User" onClick="register_master_user(this)">
            </form>
        </div>

    </div>
    
</body>
</html>