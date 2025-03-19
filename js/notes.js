/**
 * Notes Application JavaScript
 * Handles all client-side functionality for the notes web app
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the rich text editor
    initializeEditor();
    
    // Load notes from the server
    loadNotes();
    
    // Load folders and tags
    loadFolders();
    loadTags();
    
    // Set up event listeners
    setupEventListeners();
});

// Global variables
let currentNote = null;
let currentFolder = null;
let editor = null;

/**
 * Initialize the rich text editor using Quill
 */
function initializeEditor() {
    // Check if the editor container exists
    const editorContainer = document.getElementById('editor-container');
    if (!editorContainer) return;
    
    // Initialize Quill with toolbar options
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'script': 'sub' }, { 'script': 'super' }],
        [{ 'indent': '-1' }, { 'indent': '+1' }],
        [{ 'direction': 'rtl' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'font': [] }],
        [{ 'align': [] }],
        ['clean'],
        ['link', 'image', 'video']
    ];
    
    editor = new Quill('#editor-container', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Write something amazing...',
        theme: 'snow'
    });
    
    // Add custom handlers for images and attachments
    const toolbar = editor.getModule('toolbar');
    toolbar.addHandler('image', showImageUploadDialog);
}

/**
 * Load notes from the server
 * @param {number} folderId - Optional folder ID to filter notes
 * @param {boolean} showArchived - Whether to show archived notes
 */
function loadNotes(folderId = null, showArchived = false) {
    const notesContainer = document.getElementById('notes-list');
    if (!notesContainer) return;
    
    // Clear the notes container
    notesContainer.innerHTML = '<div class="loading-spinner"></div>';
    
    // Prepare the request data
    const requestData = new FormData();
    requestData.append('action', 'get_notes');
    if (folderId) requestData.append('folder_id', folderId);
    requestData.append('show_archived', showArchived ? '1' : '0');
    
    // Fetch notes from the server
    fetch('notes.php', {
        method: 'POST',
        body: requestData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            renderNotes(data.notes, notesContainer);
        } else {
            notesContainer.innerHTML = `<div class="error-message">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error loading notes:', error);
        notesContainer.innerHTML = '<div class="error-message">Failed to load notes. Please try again.</div>';
    });
}

/**
 * Render notes in the container
 * @param {Array} notes - Array of note objects
 * @param {HTMLElement} container - Container element to render notes in
 */
function renderNotes(notes, container) {
    // Clear the container
    container.innerHTML = '';
    
    // Check if there are any notes
    if (notes.length === 0) {
        container.innerHTML = '<div class="empty-state">No notes found. Create a new note to get started!</div>';
        return;
    }
    
    // Sort notes: pinned first, then by updated_at date
    notes.sort((a, b) => {
        if (a.is_pinned !== b.is_pinned) return b.is_pinned - a.is_pinned;
        return new Date(b.updated_at) - new Date(a.updated_at);
    });
    
    // Create a document fragment for better performance
    const fragment = document.createDocumentFragment();
    
    // Render each note
    notes.forEach(note => {
        const noteElement = createNoteElement(note);
        fragment.appendChild(noteElement);
    });
    
    // Append all notes to the container
    container.appendChild(fragment);
}

/**
 * Create a note element
 * @param {Object} note - Note object
 * @returns {HTMLElement} - Note element
 */
function createNoteElement(note) {
    const noteElement = document.createElement('div');
    noteElement.className = `note-card ${note.is_pinned ? 'pinned' : ''} ${note.is_archived ? 'archived' : ''}`;
    noteElement.dataset.noteId = note.id;
    
    // Format the date
    const updatedDate = new Date(note.updated_at);
    const formattedDate = updatedDate.toLocaleDateString() + ' ' + updatedDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    // Create the note content
    noteElement.innerHTML = `
        <div class="note-header">
            <h3 class="note-title">${escapeHtml(note.title)}</h3>
            <div class="note-actions">
                ${note.is_pinned ? '<button class="pin-button pinned" title="Unpin"><i class="fas fa-thumbtack"></i></button>' : 
                                  '<button class="pin-button" title="Pin"><i class="fas fa-thumbtack"></i></button>'}
                <button class="more-button" title="More options"><i class="fas fa-ellipsis-v"></i></button>
            </div>
        </div>
        <div class="note-preview">${getPreviewText(note.content)}</div>
        <div class="note-footer">
            <span class="note-date">${formattedDate}</span>
            ${renderNoteTags(note.tags)}
            ${note.is_encrypted ? '<span class="encrypted-badge"><i class="fas fa-lock"></i></span>' : ''}
        </div>
        <div class="note-dropdown">
            <button class="dropdown-item edit-note"><i class="fas fa-edit"></i> Edit</button>
            ${note.is_archived ? 
              '<button class="dropdown-item unarchive-note"><i class="fas fa-box-open"></i> Unarchive</button>' : 
              '<button class="dropdown-item archive-note"><i class="fas fa-archive"></i> Archive</button>'}
            <button class="dropdown-item share-note"><i class="fas fa-share-alt"></i> Share</button>
            <button class="dropdown-item add-reminder"><i class="fas fa-bell"></i> Reminder</button>
            ${note.is_encrypted ? 
              '<button class="dropdown-item decrypt-note"><i class="fas fa-unlock"></i> Decrypt</button>' : 
              '<button class="dropdown-item encrypt-note"><i class="fas fa-lock"></i> Encrypt</button>'}
            <button class="dropdown-item delete-note"><i class="fas fa-trash"></i> Delete</button>
        </div>
    `;
    
    // Add event listeners
    noteElement.querySelector('.more-button').addEventListener('click', function(e) {
        e.stopPropagation();
        toggleNoteDropdown(noteElement);
    });
    
    noteElement.querySelector('.pin-button').addEventListener('click', function(e) {
        e.stopPropagation();
        togglePinNote(note.id);
    });
    
    noteElement.querySelector('.edit-note').addEventListener('click', function(e) {
        e.stopPropagation();
        openNoteEditor(note.id);
    });
    
    noteElement.querySelector('.archive-note, .unarchive-note').addEventListener('click', function(e) {
        e.stopPropagation();
        toggleArchiveNote(note.id);
    });
    
    noteElement.querySelector('.share-note').addEventListener('click', function(e) {
        e.stopPropagation();
        showShareDialog(note.id);
    });
    
    noteElement.querySelector('.add-reminder').addEventListener('click', function(e) {
        e.stopPropagation();
        showReminderDialog(note.id);
    });
    
    noteElement.querySelector('.encrypt-note, .decrypt-note').addEventListener('click', function(e) {
        e.stopPropagation();
        toggleEncryptNote(note.id);
    });
    
    noteElement.querySelector('.delete-note').addEventListener('click', function(e) {
        e.stopPropagation();
        confirmDeleteNote(note.id);
    });
    
    // Open note when clicking on the card
    noteElement.addEventListener('click', function() {
        openNoteEditor(note.id);
    });
    
    return noteElement;
}

/**
 * Get a preview text from the note content
 * @param {string} content - Note content (HTML)
 * @returns {string} - Preview text
 */
function getPreviewText(content) {
    // Create a temporary element to parse HTML
    const temp = document.createElement('div');
    temp.innerHTML = content;
    
    // Get the text content
    let text = temp.textContent || temp.innerText || '';
    
    // Limit to 100 characters
    if (text.length > 100) {
        text = text.substring(0, 100) + '...';
    }
    
    return escapeHtml(text);
}

/**
 * Render tags for a note
 * @param {Array} tags - Array of tag objects
 * @returns {string} - HTML for tags
 */
function renderNoteTags(tags) {
    if (!tags || tags.length === 0) return '';
    
    let tagsHtml = '<div class="note-tags">';
    
    tags.forEach(tag => {
        tagsHtml += `<span class="tag" style="background-color: ${tag.color}">${escapeHtml(tag.name)}</span>`;
    });
    
    tagsHtml += '</div>';
    return tagsHtml;
}

/**
 * Toggle the dropdown menu for a note
 * @param {HTMLElement} noteElement - Note element
 */
function toggleNoteDropdown(noteElement) {
    // Close all other dropdowns
    document.querySelectorAll('.note-dropdown.active').forEach(dropdown => {
        if (dropdown !== noteElement.querySelector('.note-dropdown')) {
            dropdown.classList.remove('active');
        }
    });
    
    // Toggle this dropdown
    noteElement.querySelector('.note-dropdown').classList.toggle('active');
}

/**
 * Toggle pin status for a note
 * @param {number} noteId - Note ID
 */
function togglePinNote(noteId) {
    const requestData = new FormData();
    requestData.append('action', 'pin');
    requestData.append('note_id', noteId);
    
    fetch('notes.php', {
        method: 'POST',
        body: requestData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Reload notes to reflect the change
            loadNotes(currentFolder);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling pin status:', error);
        showNotification('Failed to update pin status. Please try again.', 'error');
    });
}

/**
 * Toggle archive status for a note
 * @param {number} noteId - Note ID
 */
function toggleArchiveNote(noteId) {
    const requestData = new FormData();
    requestData.append('action', 'archive');
    requestData.append('note_id', noteId);
    
    fetch('notes.php', {
        method: 'POST',
        body: requestData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Reload notes to reflect the change
            loadNotes(currentFolder);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling archive status:', error);
        showNotification('Failed to update archive status. Please try again.', 'error');
    });
}

/**
 * Open the note editor for a specific note
 * @param {number} noteId - Note ID
 */
function openNoteEditor(noteId) {
    // Show the editor panel
    document.getElementById('editor-panel').classList.add('active');
    
    // Show loading state
    document.getElementById('editor-container').innerHTML = '<div class="loading-spinner"></div>';
    
    // Fetch the note data
    const requestData = new FormData();
    requestData.append('action', 'get_note');
    requestData.append('note_id', noteId);
    
    fetch('notes.php', {
        method: 'POST',
        body: requestData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Set the current note
            currentNote = data.note;
            
            // Set the title
            document.getElementById('note-title').value = currentNote.title;
            
            // Set the content in the editor
            editor.root.innerHTML = currentNote.content;
            
            // Update the folder dropdown
            if (currentNote.folder_id) {
                document.getElementById('note-folder').value = currentNote.folder_id;
            } else {
                document.getElementById('note-folder