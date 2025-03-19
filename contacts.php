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
    <title>Contact Manager</title>
    <link rel="stylesheet" href="css/contacts/contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="app-container">
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search" placeholder="Search contacts...">
                <div class="search-filter">
                    <select id="searchFilter">
                        <option value="all">All</option>
                        <option value="name">Name</option>
                        <option value="phone">Phone</option>
                        <option value="group">Group</option>
                    </select>
                </div>
            </div>
            <div class="view-options">
                <button id="gridView" class="view-btn active" title="Grid View">
                    <i class="fas fa-th"></i>
                </button>
                <button id="listView" class="view-btn" title="List View">
                    <i class="fas fa-list"></i>
                </button>
            </div>

        <div class="content">
            <div class="sidebar">
                <div class="add-contact-section">
                    <h2>Add New Contact</h2>
                    <form id="addContactForm">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i></label>
                            <input type="text" id="name" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i></label>
                            <input type="tel" id="phone" placeholder="Phone Number" required pattern="[0-9]{10,15}" title="Enter a valid phone number (10-15 digits)">
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i></label>
                            <input type="email" id="email" placeholder="Email Address">
                        </div>
                        <div class="form-group">
                            <label for="group"><i class="fas fa-users"></i></label>
                            <input type="text" id="group" placeholder="Group Name" list="groupList">
                            <datalist id="groupList">
                                <!-- Will be populated with JS -->
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label for="notes"><i class="fas fa-sticky-note"></i></label>
                            <textarea id="notes" placeholder="Notes"></textarea>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus"></i> Add Contact
                        </button>
                    </form>
                </div>
                
                <div class="groups-section">
                    <h2>Groups</h2>
                    <ul id="groupFilterList">
                        <li class="active" data-group="all">All Contacts</li>
                        <!-- Will be populated with JS -->
                    </ul>
                </div>
            </div>

            <div class="main-content">
                <div class="contacts-header">
                    <h2>Your Contacts <span id="contactCount">(0)</span></h2>
                    <div class="contact-actions">
                        <button id="exportBtn" class="btn-secondary" title="Export Contacts">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                        <button id="importBtn" class="btn-secondary" title="Import Contacts">
                            <i class="fas fa-file-import"></i> Import
                        </button>
                    </div>
                </div>

                <div id="contactsContainer" class="grid-view">
                    <!-- Contacts will be populated here with JS -->
                    <div class="empty-state">
                        <i class="fas fa-user-plus"></i>
                        <p>No contacts found. Add your first contact!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Details Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="contact-details">
                <div class="contact-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2 id="modalName"></h2>
                <div id="modalDetails"></div>
                <div class="modal-actions">
                    <button id="editContactBtn" class="btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button id="deleteContactBtn" class="btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Contact</h2>
            <form id="editContactForm">
                <input type="hidden" id="editId">
                <div class="form-group">
                    <label for="editName"><i class="fas fa-user"></i> Name</label>
                    <input type="text" id="editName" required>
                </div>
                <div class="form-group">
                    <label for="editPhone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="tel" id="editPhone" required pattern="[0-9]{10,15}">
                </div>
                <div class="form-group">
                    <label for="editEmail"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="editEmail">
                </div>
                <div class="form-group">
                    <label for="editGroup"><i class="fas fa-users"></i> Group</label>
                    <input type="text" id="editGroup" list="editGroupList">
                    <datalist id="editGroupList">
                        <!-- Will be populated with JS -->
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="editNotes"><i class="fas fa-sticky-note"></i> Notes</label>
                    <textarea id="editNotes"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" id="cancelEdit" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Import Contacts</h2>
            <p>Upload a CSV file with your contacts data.</p>
            <form id="importForm">
                <div class="form-group">
                    <label for="importFile"><i class="fas fa-file-csv"></i> Select CSV File</label>
                    <input type="file" id="importFile" accept=".csv">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
            <div class="import-template">
                <p>Download template: <a href="#" id="downloadTemplate">contacts_template.csv</a></p>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="toast-content">
            <i class="toast-icon"></i>
            <div class="toast-message"></div>
        </div>
    </div>

  <script>
    // contacts.js

document.addEventListener("DOMContentLoaded", function() {
    // DOM Elements
    const contactsContainer = document.getElementById('contactsContainer');
    const addContactForm = document.getElementById('addContactForm');
    const searchInput = document.getElementById('search');
    const searchFilter = document.getElementById('searchFilter');
    const gridViewBtn = document.getElementById('gridView');
    const listViewBtn = document.getElementById('listView');
    const groupFilterList = document.getElementById('groupFilterList');
    const contactModal = document.getElementById('contactModal');
    const editModal = document.getElementById('editModal');
    const importModal = document.getElementById('importModal');
    const contactCount = document.getElementById('contactCount');
    const groupList = document.getElementById('groupList');
    const editGroupList = document.getElementById('editGroupList');
    const exportBtn = document.getElementById('exportBtn');
    const importBtn = document.getElementById('importBtn');
    const toast = document.getElementById('toast');
    
    // State
    let contacts = [];
    let groups = [];
    let currentGroup = 'all';
    let viewMode = 'grid';
    
    // Initialize
    loadContacts();
    initEventListeners();
    
    /**
     * Initialize all event listeners
     */
    function initEventListeners() {
        // Form submissions
        addContactForm.addEventListener('submit', handleAddContact);
        document.getElementById('editContactForm').addEventListener('submit', handleEditContact);
        document.getElementById('importForm').addEventListener('submit', handleImportContacts);
        
        // View toggles
        gridViewBtn.addEventListener('click', () => toggleView('grid'));
        listViewBtn.addEventListener('click', () => toggleView('list'));
        
        // Search
        searchInput.addEventListener('input', filterContacts);
        searchFilter.addEventListener('change', filterContacts);
        
        // Export/Import
        exportBtn.addEventListener('click', handleExportContacts);
        importBtn.addEventListener('click', () => openModal(importModal));
        document.getElementById('downloadTemplate').addEventListener('click', downloadTemplate);
        
        // Modal close buttons
        document.querySelectorAll('.close, #cancelEdit').forEach(el => {
            el.addEventListener('click', () => {
                closeAllModals();
            });
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === contactModal || e.target === editModal || e.target === importModal) {
                closeAllModals();
            }
        });
        
        // Edit and delete contact from modal
        document.getElementById('editContactBtn').addEventListener('click', () => {
            const contactId = contactModal.getAttribute('data-id');
            prepareEditForm(contactId);
            closeModal(contactModal);
            openModal(editModal);
        });
        
        document.getElementById('deleteContactBtn').addEventListener('click', () => {
            const contactId = contactModal.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this contact?')) {
                deleteContact(contactId);
                closeModal(contactModal);
            }
        });
    }
    
    /**
     * Load contacts from API
     */
    function loadContacts() {
        fetch("contacts_api.php", {
            headers: { "X-Requested-With": "XMLHttpRequest" },
            method: "GET",
            credentials: "same-origin" // Ensure cookies are sent with the request
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid data format received from server');
            }
            contacts = Array.isArray(data) ? data : [];
            updateContactCount();
            extractGroups();
            renderGroups();
            renderContacts();
        })
        .catch(error => {
            console.error("Error loading contacts:", error);
            showToast("Failed to load contacts. Please check your connection and try again.", "error");
            // Display a more user-friendly empty state
            contactsContainer.innerHTML = `
                <div class="empty-state error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Could not load your contacts. Please refresh the page to try again.</p>
                    <button onclick="location.reload()" class="btn-primary">Refresh</button>
                </div>
            `;
        });
    }
    
    /**
     * Extract unique groups from contacts
     */
    function extractGroups() {
        // Get unique groups
        const uniqueGroups = new Set();
        contacts.forEach(contact => {
            if (contact.group_name && contact.group_name !== 'N/A') {
                uniqueGroups.add(contact.group_name);
            }
        });
        
        groups = Array.from(uniqueGroups);
        
        // Update datalists
        updateGroupDatalist(groupList);
        updateGroupDatalist(editGroupList);
    }
    
    /**
     * Update group datalist with current groups
     */
    function updateGroupDatalist(datalistElement) {
        datalistElement.innerHTML = '';
        groups.forEach(group => {
            const option = document.createElement('option');
            option.value = group;
            datalistElement.appendChild(option);
        });
    }
    
    /**
     * Render group filters in sidebar
     */
    function renderGroups() {
        // Clear existing groups except "All Contacts"
        const allContactsItem = groupFilterList.querySelector('[data-group="all"]');
        groupFilterList.innerHTML = '';
        groupFilterList.appendChild(allContactsItem);
        
        // Add groups to filter list
        groups.forEach(group => {
            const li = document.createElement('li');
            li.textContent = group;
            li.setAttribute('data-group', group);
            li.addEventListener('click', () => {
                filterByGroup(group);
            });
            groupFilterList.appendChild(li);
        });
        
        // Highlight current group
        highlightCurrentGroup();
    }
    
    /**
     * Highlight the current group in the filter list
     */
    function highlightCurrentGroup() {
        document.querySelectorAll('#groupFilterList li').forEach(li => {
            li.classList.remove('active');
            if (li.getAttribute('data-group') === currentGroup) {
                li.classList.add('active');
            }
        });
    }
    
    /**
     * Filter contacts by group
     */
    function filterByGroup(group) {
        currentGroup = group;
        highlightCurrentGroup();
        renderContacts();
    }
    
    /**
     * Toggle between grid and list view
     */
    function toggleView(mode) {
        viewMode = mode;
        contactsContainer.className = mode === 'grid' ? 'grid-view' : 'list-view';
        
        // Update button states
        gridViewBtn.classList.toggle('active', mode === 'grid');
        listViewBtn.classList.toggle('active', mode === 'list');
        
        // Re-render contacts with new view mode
        renderContacts();
    }
    
    /**
     * Update contact count display
     */
    function updateContactCount() {
        const filteredContacts = filterContactsByGroup(contacts, currentGroup);
        contactCount.textContent = `(${filteredContacts.length})`;
    }
    
    /**
     * Filter contacts by current search and group
     */
    function filterContacts() {
        renderContacts();
    }
    
    /**
     * Filter contacts based on search input and current group
     */
    function getFilteredContacts() {
        let filtered = filterContactsByGroup(contacts, currentGroup);
        
        const searchTerm = searchInput.value.toLowerCase();
        const filter = searchFilter.value;
        
        if (searchTerm) {
            filtered = filtered.filter(contact => {
                if (filter === 'all') {
                    return (
                        contact.name.toLowerCase().includes(searchTerm) || 
                        contact.phone.includes(searchTerm) || 
                        (contact.email && contact.email.toLowerCase().includes(searchTerm)) ||
                        (contact.group_name && contact.group_name.toLowerCase().includes(searchTerm))
                    );
                } else if (filter === 'name') {
                    return contact.name.toLowerCase().includes(searchTerm);
                } else if (filter === 'phone') {
                    return contact.phone.includes(searchTerm);
                } else if (filter === 'group') {
                    return contact.group_name && contact.group_name.toLowerCase().includes(searchTerm);
                }
                return false;
            });
        }
        
        return filtered;
    }
    
    /**
     * Filter contacts by group
     */
    function filterContactsByGroup(contactsArray, group) {
        if (group === 'all') {
            return contactsArray;
        }
        return contactsArray.filter(contact => contact.group_name === group);
    }
    
    /**
     * Render contacts based on current filters and view mode
     */
    function renderContacts() {
        const filteredContacts = getFilteredContacts();
        updateContactCount();
        
        contactsContainer.innerHTML = '';
        
        if (filteredContacts.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <i class="fas fa-user-plus"></i>
                <p>No contacts found. Add your first contact!</p>
            `;
            contactsContainer.appendChild(emptyState);
            return;
        }
        
        filteredContacts.forEach(contact => {
            const contactElement = document.createElement('div');
            contactElement.className = viewMode === 'grid' ? 'contact-card' : 'contact-row';
            contactElement.setAttribute('data-id', contact.id);
            
            if (viewMode === 'grid') {
                contactElement.innerHTML = `
                    <div class="contact-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="contact-info">
                        <h3>${contact.name}</h3>
                        <p><i class="fas fa-phone"></i> ${contact.phone}</p>
                        ${contact.email ? `<p><i class="fas fa-envelope"></i> ${contact.email}</p>` : ''}
                        ${contact.group_name && contact.group_name !== 'N/A' ? 
                            `<p class="contact-group"><i class="fas fa-users"></i> ${contact.group_name}</p>` : ''}
                    </div>
                `;
            } else {
                contactElement.innerHTML = `
                    <div class="contact-info">
                        <h3>${contact.name}</h3>
                        <div class="contact-details">
                            <p><i class="fas fa-phone"></i> ${contact.phone}</p>
                            ${contact.email ? `<p><i class="fas fa-envelope"></i> ${contact.email}</p>` : ''}
                            ${contact.group_name && contact.group_name !== 'N/A' ? 
                                `<p><i class="fas fa-users"></i> ${contact.group_name}</p>` : ''}
                        </div>
                    </div>
                    <div class="contact-actions">
                        <button class="btn-icon view-contact" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon edit-contact" title="Edit Contact">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete-contact" title="Delete Contact">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                
                // Add event listeners for list view action buttons
                contactElement.querySelector('.view-contact').addEventListener('click', (e) => {
                    e.stopPropagation();
                    showContactDetails(contact.id);
                });
                
                contactElement.querySelector('.edit-contact').addEventListener('click', (e) => {
                    e.stopPropagation();
                    prepareEditForm(contact.id);
                    openModal(editModal);
                });
                
                contactElement.querySelector('.delete-contact').addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (confirm('Are you sure you want to delete this contact?')) {
                        deleteContact(contact.id);
                    }
                });
            }
            
            // Add click event to show contact details (for grid view)
            contactElement.addEventListener('click', () => {
                showContactDetails(contact.id);
            });
            
            contactsContainer.appendChild(contactElement);
        });
    }
    
    /**
     * Show contact details in modal
     */
    function showContactDetails(contactId) {
        const contact = contacts.find(c => c.id == contactId);
        if (!contact) return;
        
        const modalName = document.getElementById('modalName');
        const modalDetails = document.getElementById('modalDetails');
        
        modalName.textContent = contact.name;
        
        modalDetails.innerHTML = `
            <p><i class="fas fa-phone"></i> <span>${contact.phone}</span></p>
            ${contact.email ? `<p><i class="fas fa-envelope"></i> <span>${contact.email}</span></p>` : ''}
            ${contact.group_name && contact.group_name !== 'N/A' ? 
                `<p><i class="fas fa-users"></i> <span>${contact.group_name}</span></p>` : ''}
            ${contact.notes ? `<p><i class="fas fa-sticky-note"></i> <span>${contact.notes}</span></p>` : ''}
        `;
        
        contactModal.setAttribute('data-id', contactId);
        openModal(contactModal);
    }
    
    /**
     * Prepare edit form with contact data
     */
    function prepareEditForm(contactId) {
        const contact = contacts.find(c => c.id == contactId);
        if (!contact) return;
        
        document.getElementById('editId').value = contact.id;
        document.getElementById('editName').value = contact.name;
        document.getElementById('editPhone').value = contact.phone;
        document.getElementById('editEmail').value = contact.email || '';
        document.getElementById('editGroup').value = contact.group_name || '';
        document.getElementById('editNotes').value = contact.notes || '';
    }
    
    /**
     * Handle add contact form submission
     */
    function handleAddContact(e) {
        e.preventDefault();
        
        const name = document.getElementById('name').value;
        const phone = document.getElementById('phone').value;
        const email = document.getElementById('email').value;
        const group = document.getElementById('group').value;
        const notes = document.getElementById('notes').value;
        
        const newContact = {
            name,
            phone,
            email,
            group_name: group,
            notes
        };
        
        // Show loading state
        const submitBtn = addContactForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        submitBtn.disabled = true;
        
        // Send to API
        fetch('contacts_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'add', contact: newContact })
        })
        .then(response => {
            // Check for non-JSON responses
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Received non-JSON response: ' + text.substring(0, 100) + '...');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Reset form
                addContactForm.reset();
                
                // Add the new contact to the contacts array
                contacts.push(data.contact);
                
                // Update UI
                extractGroups();
                renderGroups();
                renderContacts();
                
                showToast('Contact added successfully', 'success');
            } else {
                showToast('Failed to add contact: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error adding contact:', error);
            showToast('Failed to add contact. Please try again.', 'error');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });
    }
    
    /**
     * Load contacts from API
     */
    function loadContacts() {
        fetch("contacts_api.php", {
            headers: { 
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            method: "GET",
            credentials: "same-origin"
        })
        .then(response => {
            // Check for non-JSON responses
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Received non-JSON response: ' + text.substring(0, 100) + '...');
                });
            }
            return response.json();
        })
        .then(data => {
            contacts = Array.isArray(data) ? data : [];
            updateContactCount();
            extractGroups();
            renderGroups();
            renderContacts();
        })
        .catch(error => {
            console.error("Error loading contacts:", error);
            showToast("Failed to load contacts. Please check your connection and try again.", "error");
            contactsContainer.innerHTML = `
                <div class="empty-state error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Could not load your contacts. Please refresh the page to try again.</p>
                    <button onclick="location.reload()" class="btn-primary">Refresh</button>
                </div>
            `;
        });
    }
    
    /**
     * Handle edit contact form submission
     */
    function handleEditContact(e) {
        e.preventDefault();
        
        const id = document.getElementById('editId').value;
        const name = document.getElementById('editName').value;
        const phone = document.getElementById('editPhone').value;
        const email = document.getElementById('editEmail').value;
        const group = document.getElementById('editGroup').value;
        const notes = document.getElementById('editNotes').value;
        
        const updatedContact = {
            id,
            name,
            phone,
            email,
            group_name: group,
            notes
        };
        
        // Send to API
        fetch('contacts_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'update', contact: updatedContact })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the contact in the contacts array
                const index = contacts.findIndex(c => c.id == id);
                if (index !== -1) {
                    contacts[index] = updatedContact;
                }
                
                // Update UI
                extractGroups();
                renderGroups();
                renderContacts();
                
                closeModal(editModal);
                showToast('Contact updated successfully', 'success');
            } else {
                showToast('Failed to update contact: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error updating contact:', error);
            showToast('Failed to update contact. Please try again.', 'error');
        });
    }
    
    /**
     * Delete a contact
     */
    function deleteContact(contactId) {
        // Send to API
        fetch('contacts_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'delete', id: contactId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the contact from the contacts array
                contacts = contacts.filter(c => c.id != contactId);
                
                // Update UI
                extractGroups();
                renderGroups();
                renderContacts();
                
                showToast('Contact deleted successfully', 'success');
            } else {
                showToast('Failed to delete contact: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting contact:', error);
            showToast('Failed to delete contact. Please try again.', 'error');
        });
    }
    
    /**
     * Handle export contacts
     */
    function handleExportContacts() {
        // Get current filtered contacts
        const filteredContacts = getFilteredContacts();
        
        if (filteredContacts.length === 0) {
            showToast('No contacts to export', 'warning');
            return;
        }
        
        // Convert to CSV
        const headers = ['Name', 'Phone', 'Email', 'Group', 'Notes'];
        const csvContent = [
            headers.join(','),
            ...filteredContacts.map(contact => [
                `"${contact.name.replace(/"/g, '""')}"`,
                `"${contact.phone.replace(/"/g, '""')}"`,
                `"${(contact.email || '').replace(/"/g, '""')}"`,
                `"${(contact.group_name || '').replace(/"/g, '""')}"`,
                `"${(contact.notes || '').replace(/"/g, '""')}"`
            ].join(','))
        ].join('\n');
        
        // Create and trigger download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'contacts.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('Contacts exported successfully', 'success');
    }
    
    /**
     * Handle import contacts
     */
    function handleImportContacts(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('importFile');
        const file = fileInput.files[0];
        
        if (!file) {
            showToast('Please select a file to import', 'warning');
            return;
        }
        
        const reader = new FileReader();
        reader.readAsText(file);
        reader.onload = function(event) {
            const csv = event.target.result;
            const lines = csv.split('\n');
            
            // Skip header row
            const headers = lines[0].split(',');
            const nameIdx = headers.indexOf('Name');
            const phoneIdx = headers.indexOf('Phone');
            const emailIdx = headers.indexOf('Email');
            const groupIdx = headers.indexOf('Group');
            const notesIdx = headers.indexOf('Notes');
            
            if (nameIdx === -1 || phoneIdx === -1) {
                showToast('Invalid CSV format. Name and Phone are required.', 'error');
                return;
            }
            
            const importedContacts = [];
            
            for (let i = 1; i < lines.length; i++) {
                if (!lines[i].trim()) continue;
                
                const values = parseCSVLine(lines[i]);
                
                const contact = {
                    name: values[nameIdx]?.replace(/^"|"$/g, '') || '',
                    phone: values[phoneIdx]?.replace(/^"|"$/g, '') || '',
                    email: emailIdx > -1 ? values[emailIdx]?.replace(/^"|"$/g, '') || '' : '',
                    group_name: groupIdx > -1 ? values[groupIdx]?.replace(/^"|"$/g, '') || '' : '',
                    notes: notesIdx > -1 ? values[notesIdx]?.replace(/^"|"$/g, '') || '' : ''
                };
                
                if (contact.name && contact.phone) {
                    importedContacts.push(contact);
                }
            }
            
            if (importedContacts.length === 0) {
                showToast('No valid contacts found in CSV', 'warning');
                return;
            }
            
            // Send to API
            fetch('contacts_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action: 'import', contacts: importedContacts })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload contacts
                    loadContacts();
                    
                    closeModal(importModal);
                    fileInput.value = '';
                    showToast(`${data.imported} contacts imported successfully`, 'success');
                } else {
                    showToast('Failed to import contacts: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error importing contacts:', error);
                showToast('Failed to import contacts. Please try again.', 'error');
            });
        };
        
        reader.onerror = function() {
            showToast('Error reading file', 'error');
        };
    }
    
    /**
     * Parse CSV line accounting for quoted values with commas
     */
    function parseCSVLine(line) {
        const result = [];
        let startPos = 0;
        let inQuotes = false;
        
        for (let i = 0; i < line.length; i++) {
            if (line[i] === '"') {
                inQuotes = !inQuotes;
            } else if (line[i] === ',' && !inQuotes) {
                result.push(line.substring(startPos, i));
                startPos = i + 1;
            }
        }
        
        result.push(line.substring(startPos));
        return result;
    }
    
    /**
     * Download template CSV
     */
    function downloadTemplate(e) {
        e.preventDefault();
        
        const template = 'Name,Phone,Email,Group,Notes\n"John Doe","1234567890","john@example.com","Friends","Sample note"';
        
        const blob = new Blob([template], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'contacts_template.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    /**
     * Open a modal
     */
    function openModal(modal) {
        modal.style.display = 'block';
    }
    
    /**
     * Close a modal
     */
    function closeModal(modal) {
        modal.style.display = 'none';
    }
    
    /**
     * Close all modals
     */
    function closeAllModals() {
        contactModal.style.display = 'none';
        editModal.style.display = 'none';
        importModal.style.display = 'none';
    }
    
    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        const toastContent = toast.querySelector('.toast-content');
        const toastMessage = toast.querySelector('.toast-message');
        const toastIcon = toast.querySelector('.toast-icon');
        
        toastMessage.textContent = message;
        
        // Set icon based on type
        toastIcon.className = 'toast-icon';
        if (type === 'success') {
            toastIcon.classList.add('fas', 'fa-check-circle');
            toastContent.style.backgroundColor = '#4CAF50';
        } else if (type === 'error') {
            toastIcon.classList.add('fas', 'fa-exclamation-circle');
            toastContent.style.backgroundColor = '#F44336';
        } else if (type === 'warning') {
            toastIcon.classList.add('fas', 'fa-exclamation-triangle');
            toastContent.style.backgroundColor = '#FF9800';
        } else if (type === 'info') {
            toastIcon.classList.add('fas', 'fa-info-circle');
            toastContent.style.backgroundColor = '#2196F3';
        }
        
        // Show toast
        toast.classList.add('show');
        
        // Hide after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
});
  </script>
</body>
</html>