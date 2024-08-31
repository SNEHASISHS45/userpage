<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts</title>
    <link rel="stylesheet" href="contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="contacts-section">
        <h1><i class="fa-solid fa-address-book"></i> Contacts</h1>
        <div class="import-options">
            <h2>Import Contacts</h2>
            <form id="import-form" action="import_contacts.php" method="post" enctype="multipart/form-data">
                <label for="contact_file"><i class="fa-solid fa-upload"></i> Import from File:</label>
                <input type="file" id="contact_file" name="contact_file" accept=".vcf,.csv">
                <input type="submit" value="Upload Contacts">
            </form>
            <button id="import-phone" class="import-button"><i class="fa-solid fa-mobile-alt"></i> Import from Phone</button>
            <input type="file" id="phone_contacts_file" style="display: none;" accept=".vcf,.csv">
        </div>
        <div class="contact-list">
            <!-- Contacts will be dynamically loaded here -->
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Your Website. All rights reserved.</p>
    </footer>

    <script>
        // Simulate contact import functionality for mobile users
        document.getElementById('import-phone').addEventListener('click', function() {
            if (isMobileDevice()) {
                document.getElementById('phone_contacts_file').click();
            } else {
                alert('This feature is available on mobile devices only.');
            }
        });

        document.getElementById('phone_contacts_file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Implement file reading and processing here
                const reader = new FileReader();
                reader.onload = function(e) {
                    const fileContent = e.target.result;
                    console.log('File content:', fileContent);
                    // Process the file content and import contacts
                };
                reader.readAsText(file);
            }
        });

        function isMobileDevice() {
            return /Mobi|Android/i.test(navigator.userAgent);
        }

        document.getElementById('import-form').addEventListener('submit', function(event) {
            const fileInput = document.getElementById('contact_file');
            if (fileInput.files.length === 0) {
                alert('Please select a file to upload.');
                event.preventDefault(); // Prevent form submission if no file is selected
            }
        });
    </script>
</body>
</html>
