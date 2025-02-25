<?php
ob_start();
session_start();

require 'config.php'; // Include database connection

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check Remember Me Before Redirect
if (!isset($_SESSION["user_id"]) && isset($_COOKIE["remember_me"]) && isset($_COOKIE["user_id"])) {
    $token = $_COOKIE["remember_me"];
    $user_id = $_COOKIE["user_id"];

    $stmt = $conn->prepare("SELECT id, username, profile_picture, remember_token FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $profile_picture, $stored_hashed_token);
        $stmt->fetch();

        if (password_verify($token, $stored_hashed_token)) {
            // Set session variables
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["profile_picture"] = $profile_picture;
            session_regenerate_id(true); // Prevent session fixation
        } else {
            // Invalid token - clear cookies
            setcookie("remember_me", "", time() - 3600, "/", "", false, true);
            setcookie("user_id", "", time() - 3600, "/", "", false, true);
        }
    }
    $stmt->close();
}

// Now, Redirect if Session is Still Not Set


ob_end_flush();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yourdrive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
</head>

<body>
<header class="header">
<div class="logo_container">
            <a href="index.php" class="logo">SNEHASISH</a>
            </div>
        <nav class="nav-container">
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php" class="active">HOME</a></li>
                <li><a href="dashboard.php">DASHBOARD</a></li>
                <li><a href="vaults.php">VAULTS</a></li>
                </ul>
                <div class="user-info dropdown">
                    <div>
                        <div class="dropdown-content">
                            <a href="logout.php">Logout</a>
                            <hr>
                            <p>Switch Account</p>
                            <div id="saved-accounts"></div>
                        </div>
                        <?php
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            $sql = "SELECT * FROM users WHERE id='$user_id'";
                            $result = mysqli_query($conn, $sql);
                            $user_data = mysqli_fetch_assoc($result);

                            if ($user_data) {
                                echo "<div class='user-info'>";
                                $profile_pic = !empty($user_data['profile_pic']) ? 'upload/' . $user_data['profile_pic'] : 'default-avatar.png';
                                echo "<img src='" . $profile_pic . "' class='profile-pic'>";
                                echo "<p class='username'>" . htmlspecialchars($user_data['username']) . "</p>";
                                echo "</div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            
        </nav>
    </header>


    

    <div class="main">
    <div class="secphotos section" id="secphotos">
        <?php
        include 'photos.php';
        ?>
    </div>

    <div class="seccontacts section" id="seccontacts">
        <?php
        include 'contacts.php';
        ?>
    </div>

    <div class="secdocuments section" id="secdocuments">
        <?php
        include 'documents.php';
        ?>
    </div>

    <div class="secvaults section" id="secvaults">
        <?php
        include 'vaults.php';
        ?>
    </div>
    </div>

 
 <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.69/build/spline-viewer.js"></script>
 <spline-viewer url="https://prod.spline.design/Ylco7b1CsvLH5v89/scene.splinecode"></spline-viewer>





<div class="floating-nav">

<nav role="navigation" class="nav-menu-2 w-nav-menu menu">

<button class="floating-nav__link w-nav-link menu__item active" style="--bgColorItem: #f4f4f4;" onclick="toggleDiv('secphotos')">photos</button>
<button class="floating-nav__link w-nav-link menu__item" style="--bgColorItem: #f4f4f4;" onclick="toggleDiv('seccontacts')">contacts</button>
<button class="floating-nav__link w-nav-link menu__item" style="--bgColorItem: #f4f4f4;" onclick="toggleDiv('secdocuments')">documents</button>
<button class="floating-nav__link w-nav-link menu__item" style="--bgColorItem: #f4f4f4;" onclick="toggleDiv('secvaults')">vaults</button>
      <div class="w-nav-button">
        <div class="w-icon-nav-menu"></div>
      </div>
    <div class="menu__border"></div>

  </nav>

  <div class="svg-container">
    <svg viewBox="0 0 202.9 45.5" >
      <clipPath id="menu" clipPathUnits="objectBoundingBox" transform="scale(0.0049285362247413 0.021978021978022)">
        <path  d="M6.7,45.5c5.7,0.1,14.1-0.4,23.3-4c5.7-2.3,9.9-5,18.1-10.5c10.7-7.1,11.8-9.2,20.6-14.3c5-2.9,9.2-5.2,15.2-7
          c7.1-2.1,13.3-2.3,17.6-2.1c4.2-0.2,10.5,0.1,17.6,2.1c6.1,1.8,10.2,4.1,15.2,7c8.8,5,9.9,7.1,20.6,14.3c8.3,5.5,12.4,8.2,18.1,10.5
          c9.2,3.6,17.6,4.2,23.3,4H6.7z"/>
      </clipPath>
    </svg>
  </div>
      
      

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
    let profiles = JSON.parse(localStorage.getItem("saved_profiles")) || [];
    let dropdown = document.getElementById("saved-accounts");
    let currentUserId = "<?php echo $_SESSION['user_id']; ?>";

    if (profiles.length > 0) {
        dropdown.innerHTML = ""; // Clear previous entries
        profiles.forEach(profile => {
            if (profile.id != currentUserId) {
                let profilePicture = profile.profile_picture ? 'upload/' + profile.profile_picture : 'default-avatar.png';
                let item = document.createElement("a");
                item.href = "switch_profile.php?id=" + profile.id;
                item.classList.add("dropdown-item");
                item.innerHTML = `
                    <div class="saved-profile">
                        <img src="${profilePicture}" width="30" height="30" style="border-radius: 50%;">
                        <span>${profile.username}</span>
                    </div>`;
                dropdown.appendChild(item);
            }
        });
    } else {
        dropdown.innerHTML = "<p>No saved profiles</p>";
    }
});

    </script>



<script>
   
   

    "use strict"; 

const body = document.body;
const bgColorsBody = ["#f4f4f4"];
const menu = body.querySelector(".menu");
const menuItems = menu.querySelectorAll(".menu__item");
const menuBorder = menu.querySelector(".menu__border");
let activeItem = menu.querySelector(".active");

function clickItem(item, index) {

    menu.style.removeProperty("--timeOut");
    
    if (activeItem == item) return;
    
    if (activeItem) {
        activeItem.classList.remove("active");
    }

    
    item.classList.add("active");
    body.style.backgroundColor = bgColorsBody[index];
    activeItem = item;
    offsetMenuBorder(activeItem, menuBorder);
    
    
}

function offsetMenuBorder(element, menuBorder) {

    const offsetActiveItem = element.getBoundingClientRect();
    const left = Math.floor(offsetActiveItem.left - menu.offsetLeft - (menuBorder.offsetWidth  - offsetActiveItem.width) / 2) +  "px";
    menuBorder.style.transform = `translate3d(${left}, 0 , 0)`;

}

offsetMenuBorder(activeItem, menuBorder);

menuItems.forEach((item, index) => {

    item.addEventListener("click", () => clickItem(item, index));
    
})

window.addEventListener("resize", () => {
    offsetMenuBorder(activeItem, menuBorder);
    menu.style.setProperty("--timeOut", "none");
});
</script>


<script>
        function toggleDiv(sectionId) {
            let sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.style.display = 'none';
            });

            let activeSection = document.getElementById(sectionId);
            if (activeSection.style.display === 'block') {
                activeSection.style.display = 'none';
            } else {
                activeSection.style.display = 'block';
            }
        }
        


        function toggleDiv(sectionId) {
            let activeSection = document.getElementById(sectionId);
            let isVisible = activeSection.style.display === 'block';
            document.querySelectorAll('.section').forEach(section => section.style.display = 'none');
            
            if (!isVisible) {
                activeSection.style.display = 'block';
            }
        }
    </script>
    
</body>

</html>