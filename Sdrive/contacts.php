<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Backup</title>
    <link rel="stylesheet" href="css/contacts/contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body >

<script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.69/build/spline-viewer.js"></script>
 <spline-viewer url="https://prod.spline.design/Ylco7b1CsvLH5v89/scene.splinecode"></spline-viewer>

<div class="fc">
<form id="addContactForm">
            <input type="text" id="name" placeholder="Contact Name" required>
            <input type="tel" id="phone" placeholder="Phone Number" required pattern="[0-9]{10,15}" title="Enter a valid phone number (10-15 digits)">
            <input type="text" id="group" placeholder="Group Name">
            <button type="submit" style="background-color:rgba(38, 186, 255, 0.82);"><i class="fa-solid fa-user-plus"></i> ADD</button>
        </form>
        </div>

        <div class="scarch">
        <input type="text" id="search" placeholder="🔍 Search contacts...">
        </div>


<div class="container1">
        
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Group</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="contactList" style="color: whitesmoke; font-family: 'Courier New', Courier, monospace; font-weight: 900; font-display: swap; font-size: 17px;"></tbody>
        </table>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadContacts();

            document.getElementById("addContactForm").addEventListener("submit", function(e) {
                e.preventDefault();
                let name = document.getElementById("name").value.trim();
                let phone = document.getElementById("phone").value.trim();
                let group = document.getElementById("group").value.trim();

                if (name === "" || phone === "") {
                    alert("Please enter a valid name and phone number.");
                    return;
                }

                fetch("contacts_api.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=add&name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}&group=${encodeURIComponent(group)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("addContactForm").reset();
                        loadContacts();
                    } else {
                        alert(data.error || "Error adding contact.");
                    }
                });
            });

            document.getElementById("search").addEventListener("input", function() {
                let filter = this.value.toLowerCase();
                document.querySelectorAll("#contactList tr").forEach(row => {
                    let text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? "" : "none";
                });
            });
        });

        function loadContacts() {
            fetch("contacts_api.php", {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(response => response.json())
            .then(contacts => {
                let list = document.getElementById("contactList");
                list.innerHTML = "";

                if (contacts.length === 0) {
                    list.innerHTML = "<tr><td colspan='4'>No contacts found.</td></tr>";
                    return;
                }

                contacts.forEach(contact => {
                    let row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${contact.name}</td>
                        <td>${contact.phone}</td>
                        <td>${contact.group_name || "N/A"}</td>
                        <td>
                            <button onclick="renameContact(${contact.id})" style="background-color: #318CE7;"><i class="fa-solid fa-user-pen"></i></button>
                            <button onclick="updateGroup(${contact.id})" style="background-color:rgb(237, 165, 116);"><i class="fa-solid fa-people-group"></i></button>
                            <button onclick="deleteContact(${contact.id})" style="background-color: #FF033E;"><i class="fa-solid fa-eraser"></i></button>
                        </td>
                    `;
                    list.appendChild(row);
                });
            })
            .catch(error => console.error("Error loading contacts:", error));
        }

        function deleteContact(id) {
            if (confirm("Are you sure you want to delete this contact?")) {
                fetch("contacts_api.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=delete&id=${id}`
                }).then(() => loadContacts());
            }
        }

        function renameContact(id) {
            let newName = prompt("Enter new name:");
            if (newName) {
                fetch("contacts_api.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=rename&id=${id}&new_name=${encodeURIComponent(newName)}`
                }).then(() => loadContacts());
            }
        }

        function updateGroup(id) {
            let newGroup = prompt("Enter new group name:");
            if (newGroup) {
                fetch("contacts_api.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=update_group&id=${id}&new_group=${encodeURIComponent(newGroup)}`
                }).then(() => loadContacts());
            }
        }
    </script>

</body>
</html>
