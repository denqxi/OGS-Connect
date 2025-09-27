/**
 * Excel Upload Functionality
 * Handles file upload with progress tracking and error handling
 */

function uploadExcelFile() {
    const fileInput = document.getElementById('excelFileInput');
    const file = fileInput.files[0];
    
    if (!file) {
        return;
    }

    // Validate file type
    if (!validateFileType(file)) {
        fileInput.value = '';
        return;
    }

    // Check CSRF token
    const csrfToken = getCSRFToken();
    if (!csrfToken) {
        alert('Security token not found. Please refresh the page and try again.');
        return;
    }

    // Initialize upload UI
    const uploadElements = initializeUploadUI();
    if (!uploadElements) return;

    // Create and send request
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', csrfToken);

    const xhr = createUploadRequest(uploadElements);
    xhr.open('POST', window.uploadRoute || '/import/upload', true);
    
    // Set headers
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.send(formData);

    // Reset file input
    fileInput.value = '';
}

function validateFileType(file) {
    const validTypes = ['xlsx', 'xls', 'csv'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!validTypes.includes(fileExtension)) {
        alert('Please select a valid Excel file (.xlsx, .xls, or .csv)');
        return false;
    }
    return true;
}

function getCSRFToken() {
    // Try to get CSRF token from multiple sources
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        return metaToken.getAttribute('content');
    }
    
    const inputToken = document.querySelector('input[name="_token"]');
    if (inputToken) {
        return inputToken.value;
    }
    
    // Try to get from window object (if set elsewhere)
    if (window.csrfToken) {
        return window.csrfToken;
    }
    
    console.error('CSRF token not found');
    return null;
}

function initializeUploadUI() {
    const modal = document.getElementById('uploadModal');
    const progress = document.getElementById('uploadProgress');
    const status = document.getElementById('uploadStatus');
    const icon = document.getElementById('uploadIcon');
    
    if (!modal || !progress || !status || !icon) {
        console.error('Upload UI elements not found');
        return null;
    }

    // Show modal and reset UI
    modal.classList.remove('hidden');
    progress.style.width = '0%';
    status.textContent = 'Uploading file...';
    icon.className = 'fas fa-file-upload text-4xl text-blue-600';

    return { modal, progress, status, icon };
}

function createUploadRequest(elements) {
    const { modal, progress, status, icon } = elements;
    const xhr = new XMLHttpRequest();
    
    // Upload progress
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progress.style.width = percentComplete + '%';
            status.textContent = `Uploading... ${Math.round(percentComplete)}%`;
        }
    });

    // Handle response
    xhr.onload = function() {
        handleUploadResponse(xhr, elements);
    };

    // Handle errors
    xhr.onerror = function() {
        showUploadError(elements, 'Network error - please check your connection');
    };

    xhr.ontimeout = function() {
        showUploadError(elements, 'Upload timeout - please try again');
    };

    // Set timeout (60 seconds)
    xhr.timeout = 60000;

    return xhr;
}

function handleUploadResponse(xhr, elements) {
    const { modal, progress, status, icon } = elements;
    
    console.log('Upload Response Status:', xhr.status);
    console.log('Upload Response Text:', xhr.responseText);

    switch (xhr.status) {
        case 200:
            handleSuccessResponse(xhr.responseText, elements);
            break;
        case 419:
            showUploadError(elements, 'Security token expired. Please refresh the page and try again.');
            break;
        case 422:
            handleDuplicateResponse(xhr.responseText, elements);
            break;
        case 400:
            handleBadRequestResponse(xhr.responseText, elements);
            break;
        default:
            showUploadError(elements, `Upload failed (Error ${xhr.status})`);
            break;
    }
}

function handleSuccessResponse(responseText, elements) {
    const { modal, progress, status, icon } = elements;
    
    try {
        const response = JSON.parse(responseText);
        console.log('Parsed Success Response:', response);
        
        if (response.success === true) {
            showUploadSuccess(elements, response.message || 'Upload completed successfully!');
            setTimeout(() => {
                modal.classList.add('hidden');
                location.reload();
            }, 2000);
        } else {
            showUploadWarning(elements, response.message || 'Upload failed');
        }
    } catch (e) {
        // Handle non-JSON success responses
        const responseText_lower = responseText.toLowerCase();
        if (responseText_lower.includes('success') || responseText_lower.includes('imported')) {
            showUploadSuccess(elements, 'Upload completed successfully!');
            setTimeout(() => {
                modal.classList.add('hidden');
                location.reload();
            }, 2000);
        } else {
            showUploadError(elements, 'Server error - please try again');
        }
    }
}

function handleDuplicateResponse(responseText, elements) {
    try {
        const response = JSON.parse(responseText);
        showUploadWarning(elements, response.message || 'File contains duplicate data');
    } catch (e) {
        showUploadWarning(elements, 'This file has already been uploaded');
    }
}

function handleBadRequestResponse(responseText, elements) {
    try {
        const response = JSON.parse(responseText);
        showUploadError(elements, response.message || 'Invalid file format');
    } catch (e) {
        showUploadError(elements, 'Invalid file or format error');
    }
}

function showUploadSuccess(elements, message) {
    const { progress, status, icon } = elements;
    progress.style.width = '100%';
    icon.className = 'fas fa-check-circle text-4xl text-green-600';
    status.textContent = message;
}

function showUploadWarning(elements, message) {
    const { modal, status, icon } = elements;
    icon.className = 'fas fa-exclamation-circle text-4xl text-orange-600';
    status.textContent = message;
    setTimeout(() => modal.classList.add('hidden'), 4000);
}

function showUploadError(elements, message) {
    const { modal, status, icon } = elements;
    icon.className = 'fas fa-exclamation-triangle text-4xl text-red-600';
    status.textContent = message;
    setTimeout(() => modal.classList.add('hidden'), 3000);
}