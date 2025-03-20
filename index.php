<?php include 'includes/session_handler.php'; ?>

<?php
ob_start();
// Remove the session_start() call here since it's already in session_handler.php
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
    <!-- Preload critical CSS -->
    <link rel="preload" href="css/index.css" as="style">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/indexres.css">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Load fonts asynchronously -->
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    
    <!-- Defer non-critical CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">
</head>

<body>
    <header class="header">
    <div class="logo_container">
  <a href="index.php">
    <img src="css/images/logo.png" alt="Sdrive Logo" class="logo-image">
  </a>
</div>
        <nav class="nav-container" id="nav-container">
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> HOME</a></li>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> DASHBOARD</a></li>
                <li><a href="vaults.php"><i class="fas fa-vault"></i> VAULTS</a></li>
            </ul>
            <div class="user-info dropdown">
                <div class="user-profile-wrapper">
                    <div class="dropdown-content">
                        <div class="dropdown-header">
                            <?php if (isset($user_data)): ?>
                            <img src="<?php echo !empty($user_data['profile_pic']) ? htmlspecialchars($user_data['profile_pic']) : 'default-avatar.png'; ?>" class="dropdown-profile-pic" alt="Profile">
                            <div class="dropdown-user-info">
                                <p class="dropdown-username"><?php echo htmlspecialchars($user_data['username']); ?></p>
                                <p class="dropdown-email"><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <a href="dashboard.php"><i class="fas fa-cog"></i> Dashboard</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        <hr>
                        <p><i class="fas fa-users"></i> Switch Account</p>
                        <div id="saved-accounts"></div>
                    </div>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        $sql = "SELECT * FROM users WHERE id='$user_id'";
                        $result = mysqli_query($conn, $sql);
                        $user_data = mysqli_fetch_assoc($result);

                        if ($user_data) {
                            echo "<div class='user-info-inner'>";
                            $profile_pic = !empty($user_data['profile_pic']) ? htmlspecialchars($user_data['profile_pic']) : 'default-avatar.png';
                            echo "<img src='" . $profile_pic . "' class='profile-pic' alt='Profile Picture'>";
                            echo "<p class='username'>" . htmlspecialchars($user_data['username']) . "</p>";
                            echo "<i class='fas fa-chevron-down dropdown-icon'></i>";
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
            <div class="photos-container">
                <?php
                include 'photos.php';
                ?>
            </div>
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
            include 'notes.php';
            ?>
        </div>
    </div>

    <!-- Lazy load the Spline container -->
    <div class="spline-container" id="spline-container">
        <!-- Will be loaded via JavaScript -->
    </div>

    <div class="floating-nav">
        <nav role="navigation" class="nav-menu-2 w-nav-menu menu">
            <button class="floating-nav__link w-nav-link menu__item" aria-label="Photos" data-section="secphotos" onclick="toggleDiv('secphotos')">
                <i class="fa-solid fa-images"></i>
                <span class="nav-indicator"></span>
            </button>
            <button class="floating-nav__link w-nav-link menu__item" aria-label="Contacts" data-section="seccontacts" onclick="toggleDiv('seccontacts')">
                <i class="fa-solid fa-address-book"></i>
                <span class="nav-indicator"></span>
            </button>
            <button class="floating-nav__link w-nav-link menu__item" aria-label="Documents" data-section="secdocuments" onclick="toggleDiv('secdocuments')">
                <i class="fa-solid fa-folder"></i>
                <span class="nav-indicator"></span>
            </button>
            <button class="floating-nav__link w-nav-link menu__item" aria-label="Notes" data-section="secvaults" onclick="toggleDiv('secvaults')">
                <i class="fa-solid fa-note-sticky"></i>
                <span class="nav-indicator"></span>
            </button>
        </nav>

        <div class="svg-container">
            <svg viewBox="0 0 202.9 45.5">
                <clipPath id="menu" clipPathUnits="objectBoundingBox" transform="scale(0.0049285362247413 0.021978021978022)">
                    <path d="M6.7,45.5c5.7,0.1,14.1-0.4,23.3-4c5.7-2.3,9.9-5,18.1-10.5c10.7-7.1,11.8-9.2,20.6-14.3c5-2.9,9.2-5.2,15.2-7
                    c7.1-2.1,13.3-2.3,17.6-2.1c4.2-0.2,10.5,0.1,17.6,2.1c6.1,1.8,10.2,4.1,15.2,7c8.8,5,9.9,7.1,20.6,14.3c8.3,5.5,12.4,8.2,18.1,10.5
                    c9.2,3.6,17.6,4.2,23.3,4H6.7z" />
                </clipPath>
            </svg>
        </div>
    </div>



    <script>
        // Load non-critical resources after page load
        window.addEventListener('load', function() {
            // Lazy load Spline viewer
            const splineContainer = document.getElementById('spline-container');
            splineContainer.innerHTML = `
                <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.72/build/spline-viewer.js"><\/script>
                <spline-viewer url="https://prod.spline.design/Ylco7b1CsvLH5v89/scene.splinecode"></spline-viewer>
            `;
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Add scroll event listener to minimize user info
            window.addEventListener('scroll', function() {
                const userInfo = document.querySelector('.user-info');
                const header = document.querySelector('.header');
                
                if (window.scrollY > 50) {
                    userInfo.classList.add('minimized');
                    header.classList.add('scrolled');
                } else {
                    userInfo.classList.remove('minimized');
                    header.classList.remove('scrolled');
                }
            });
            
            // Saved profiles dropdown
            let profiles = JSON.parse(localStorage.getItem("saved_profiles")) || [];
            let dropdown = document.getElementById("saved-accounts");
            let currentUserId = "<?php echo $_SESSION['user_id'] ?? ''; ?>";

            // Add this function to save the current user profile
            function saveCurrentUserProfile() {
                // Get current user data
                const userId = "<?php echo $_SESSION['user_id'] ?? ''; ?>";
                const username = "<?php echo $_SESSION['username'] ?? ''; ?>";
                const profilePic = "<?php echo $user_data['profile_pic'] ?? ''; ?>";
                
                console.log("Saving profile with data:", {userId, username, profilePic});
                
                if (!userId) return;
                
                // Get existing profiles
                let profiles = JSON.parse(localStorage.getItem("saved_profiles")) || [];
                
                // Check if this user is already saved
                const existingProfileIndex = profiles.findIndex(p => p.id == userId);
                
                // Create profile object
                const profileData = {
                    id: userId,
                    username: username,
                    profile_picture: profilePic
                };
                
                // Update or add the profile
                if (existingProfileIndex >= 0) {
                    profiles[existingProfileIndex] = profileData;
                } else {
                    profiles.push(profileData);
                }
                
                // Save back to localStorage
                localStorage.setItem("saved_profiles", JSON.stringify(profiles));
                console.log("Saved profile:", profileData);
            }
            
            // Call the function to save the current user profile
            saveCurrentUserProfile();

            if (profiles.length > 0) {
                dropdown.innerHTML = ""; // Clear previous entries
                profiles.forEach(profile => {
                    if (profile.id != currentUserId) {
                        // Fix profile picture handling for Cloudinary URLs
                        let profilePicture = 'default-avatar.png';
                        
                        // Check both profile_pic and profile_picture properties
                        const picturePath = profile.profile_pic || profile.profile_picture || '';
                        
                        if (picturePath) {
                            console.log("Profile picture path:", picturePath);
                            // Check if it's a Cloudinary URL (contains cloudinary.com)
                            if (picturePath.includes('cloudinary.com')) {
                                profilePicture = picturePath;
                            } 
                            // Check if it's a local path that needs the upload prefix
                            else if (!picturePath.startsWith('upload/') && !picturePath.startsWith('/')) {
                                profilePicture = 'upload/' + picturePath;
                            }
                            // Use as is for other cases
                            else {
                                profilePicture = picturePath;
                            }
                        }
                        
                        let item = document.createElement("a");
                        item.href = "switch_profile.php?id=" + profile.id;
                        item.classList.add("dropdown-item");
                        item.innerHTML = `
                            <div class="saved-profile">
                                <img src="${profilePicture}" width="30" height="30" style="border-radius: 50%;" alt="${profile.username}">
                                <span>${profile.username}</span>
                            </div>`;
                        dropdown.appendChild(item);
                    }
                });
            } else {
                dropdown.innerHTML = "<p>No saved profiles</p>";
            }

            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const navContainer = document.getElementById('nav-container');
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', () => {
                    mobileMenuToggle.classList.toggle('active');
                    navContainer.classList.toggle('active');
                });
            }
            
            // Load the last active section from localStorage
            const lastActiveSection = localStorage.getItem('lastActiveSection');
            
            // Initialize all sections as hidden
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
            });
            
            // If we have a saved section, show it and update the nav
            if (lastActiveSection) {
                const sectionToShow = document.getElementById(lastActiveSection);
                if (sectionToShow) {
                    sectionToShow.style.display = 'block';
                    
                    // Update floating nav position
                    const nav = document.querySelector('.floating-nav');
                    nav.classList.add('active');
                    
                    // Add menu-loaded class after a small delay
                    setTimeout(() => {
                        nav.classList.add('menu-loaded');
                    }, 100);
                    
                    // Update the active menu item
                    menuItems.forEach(item => {
                        item.classList.remove('active');
                        if (item.getAttribute('data-section') === lastActiveSection) {
                            item.classList.add('active');
                            activeItem = item;
                            setTimeout(() => offsetMenuBorder(activeItem, menuBorder), 50);
                        }
                    });
                }
            }
        });

        // Menu border animation
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
            if (!element || !menuBorder) return;
            
            const offsetActiveItem = element.getBoundingClientRect();
            const left = Math.floor(offsetActiveItem.left - menu.offsetLeft - (menuBorder.offsetWidth - offsetActiveItem.width) / 2) + "px";
            menuBorder.style.transform = `translate3d(${left}, 0 , 0)`;
        }

        // Initialize menu border
        if (activeItem && menuBorder) {
            offsetMenuBorder(activeItem, menuBorder);
        }

        // Add click event to menu items
        menuItems.forEach((item, index) => {
            item.addEventListener("click", () => clickItem(item, index));
        });

        // Adjust menu border on window resize
        window.addEventListener("resize", () => {
            if (activeItem && menuBorder) {
                offsetMenuBorder(activeItem, menuBorder);
                menu.style.setProperty("--timeOut", "none");
            }
        });

        // Toggle section visibility and save state to localStorage
        function toggleDiv(sectionId) {
            let activeSection = document.getElementById(sectionId);
            let isVisible = activeSection.style.display === 'block';
            
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
            });
            
            const nav = document.querySelector('.floating-nav');
            
            if (!isVisible) {
                // Show the section and move nav to left
                activeSection.style.display = 'block';
                nav.classList.add('active');
                
                // Add a small delay before adding the menu-loaded class
                setTimeout(() => {
                    nav.classList.add('menu-loaded');
                }, 100);
                
                // Save the active section to localStorage
                localStorage.setItem('lastActiveSection', sectionId);
                
                // Update the active menu item
                menuItems.forEach(item => {
                    item.classList.remove('active');
                    if (item.getAttribute('data-section') === sectionId) {
                        item.classList.add('active');
                        activeItem = item;
                        offsetMenuBorder(activeItem, menuBorder);
                    }
                });
            } else {
                // If we're hiding the section, remove from localStorage
                localStorage.removeItem('lastActiveSection');
                nav.classList.remove('active');
                nav.classList.remove('menu-loaded');
                
                // Remove active class from all menu items
                menuItems.forEach(item => {
                    item.classList.remove('active');
                });
            }
            
            // Update the menu border for the new orientation
            if (activeItem && menuBorder) {
                setTimeout(() => offsetMenuBorder(activeItem, menuBorder), 50);
            }
        }
    </script>

    
</body>

</html>