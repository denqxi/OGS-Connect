document.addEventListener('DOMContentLoaded', function() {
    loadSecurityQuestions();
    setupEventListeners();
});

function setupEventListeners() {
    // Password change form
    const updatePasswordBtn = document.getElementById('updatePasswordBtn');
    if (updatePasswordBtn) {
        updatePasswordBtn.addEventListener('click', handlePasswordChange);
    }

    // Password match indicator
    const newPasswordField = document.getElementById('newPassword');
    const confirmPasswordField = document.getElementById('confirmPassword');
    
    if (newPasswordField && confirmPasswordField) {
        newPasswordField.addEventListener('input', checkPasswordMatch);
        confirmPasswordField.addEventListener('input', checkPasswordMatch);
        
        // Clear indicator when fields are cleared
        newPasswordField.addEventListener('input', function() {
            if (this.value === '') {
                clearPasswordMatchIndicator();
            }
        });
        confirmPasswordField.addEventListener('input', function() {
            if (this.value === '') {
                clearPasswordMatchIndicator();
            }
        });
    }

    // Security questions update
    const updateSecurityBtn = document.getElementById('updateSecurityQuestionsBtn');
    if (updateSecurityBtn) {
        updateSecurityBtn.addEventListener('click', handleSecurityQuestionsUpdate);
    }

    // Password toggle functionality
    setupPasswordToggles();
}

async function loadSecurityQuestions() {
    try {
        const response = await fetch('/tutor/availability/', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();
        
        if (result.success && result.tutor_info) {
            displaySecurityQuestions(result.tutor_info);
        } else {
            showNoSecurityQuestions();
        }
    } catch (error) {
        console.error('Error loading security questions:', error);
        showSecurityQuestionsError();
    }
}

function displaySecurityQuestions(tutorInfo) {
    const container = document.getElementById('securityQuestionsContainer');
    if (!container) return;

    // Default security questions
    const securityQuestions = [
        'What is your favorite color?',
        'What was the name of your first pet?',
        'What is your mother\'s maiden name?',
        'What was your first car?',
        'What city were you born in?',
        'What is the name of your favorite teacher?',
        'What was your childhood nickname?',
        'What is your favorite food?',
        'What was the name of your elementary school?',
        'What is your favorite movie?'
    ];

    // Check if tutor has security questions
    const hasSecurityQuestions = tutorInfo.security_questions && tutorInfo.security_questions.length > 0;

    if (hasSecurityQuestions) {
        // Display existing security questions
        let questionsHTML = '';
        tutorInfo.security_questions.forEach((question, index) => {
            questionsHTML += `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Security Question ${index + 1}</label>
                        <select name="security_question_${index + 1}" 
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                            ${securityQuestions.map(q => 
                                `<option value="${q}" ${q === question.question ? 'selected' : ''}>${q}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Answer ${index + 1}</label>
                        <input type="text" name="security_answer_${index + 1}" value="${question.answer || ''}" placeholder="Enter your answer"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                    </div>
                </div>
            `;
        });
        container.innerHTML = questionsHTML;
    } else {
        // Display default security questions form
        container.innerHTML = `
            <div class="space-y-4">
                <!-- Security Question 1 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Security Question 1</label>
                        <select name="security_question_1" 
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                            ${securityQuestions.map(q => `<option value="${q}">${q}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Answer 1</label>
                        <input type="text" name="security_answer_1" placeholder="Enter your answer"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                    </div>
                </div>

                <!-- Security Question 2 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Security Question 2</label>
                        <select name="security_question_2" 
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                            ${securityQuestions.map(q => `<option value="${q}">${q}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Answer 2</label>
                        <input type="text" name="security_answer_2" placeholder="Enter your answer"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                    </div>
                </div>
            </div>
        `;
    }
}

function showNoSecurityQuestions() {
    const container = document.getElementById('securityQuestionsContainer');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Security Questions</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Security questions have not been set up yet.</p>
        </div>
    `;
}

function showSecurityQuestionsError() {
    const container = document.getElementById('securityQuestionsContainer');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Error Loading Security Questions</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There was an error loading your security questions. Please try again.</p>
        </div>
    `;
}

async function handlePasswordChange() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const messageDiv = document.getElementById('passwordMessage');
    const updateBtn = document.getElementById('updatePasswordBtn');

    if (!currentPassword || !newPassword || !confirmPassword) {
        showPasswordMessage('Please fill in all password fields.', 'error');
        showNotification('Please fill in all password fields.', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showPasswordMessage('New password and confirm password do not match.', 'error');
        showNotification('New password and confirm password do not match.', 'error');
        return;
    }

    if (newPassword.length < 6) {
        showPasswordMessage('New password must be at least 6 characters long.', 'error');
        showNotification('New password must be at least 6 characters long.', 'error');
        return;
    }

    // Show confirmation modal
    showConfirmationModal(
        'Change Password',
        'Are you sure you want to change your password? This action cannot be undone.',
        'Change Password',
        'Cancel',
        proceedWithPasswordChange
    );
}

async function proceedWithPasswordChange() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const messageDiv = document.getElementById('passwordMessage');
    const updateBtn = document.getElementById('updatePasswordBtn');

    // Disable button and show loading
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';

    try {
        const response = await fetch('/tutor/change-password', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            })
        });

        const result = await response.json();

        if (result.success) {
            showPasswordMessage('Password updated successfully!', 'success');
            showNotification('Password updated successfully!', 'success');
            // Clear form
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            // Clear password match indicator
            clearPasswordMatchIndicator();
            // Clear success message after 3 seconds
            setTimeout(() => {
                const messageDiv = document.getElementById('passwordMessage');
                if (messageDiv) {
                    messageDiv.classList.add('hidden');
                }
            }, 3000);
        } else {
            showPasswordMessage(result.message || 'Failed to update password. Please try again.', 'error');
            showNotification(result.message || 'Failed to update password. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error updating password:', error);
        showPasswordMessage('An error occurred while updating your password. Please try again.', 'error');
        showNotification('An error occurred while updating your password. Please try again.', 'error');
    } finally {
        // Re-enable button
        updateBtn.disabled = false;
        updateBtn.innerHTML = 'Update Password';
    }
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const indicator = document.getElementById('passwordMatchIndicator');
    const icon = document.getElementById('passwordMatchIcon');
    const text = document.getElementById('passwordMatchText');
    
    // Only show indicator if both fields have content
    if (newPassword.length > 0 && confirmPassword.length > 0) {
        indicator.classList.remove('hidden');
        
        if (newPassword === confirmPassword) {
            // Passwords match
            icon.className = 'fas fa-check-circle text-green-500 mr-2';
            text.textContent = 'Passwords match';
            text.className = 'text-green-600 dark:text-green-400';
        } else {
            // Passwords don't match
            icon.className = 'fas fa-times-circle text-red-500 mr-2';
            text.textContent = 'Passwords do not match';
            text.className = 'text-red-600 dark:text-red-400';
        }
    } else {
        // Hide indicator if either field is empty
        indicator.classList.add('hidden');
    }
}

function clearPasswordMatchIndicator() {
    const indicator = document.getElementById('passwordMatchIndicator');
    if (indicator) {
        indicator.classList.add('hidden');
    }
}

function setupPasswordToggles() {
    // Current Password Toggle
    const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
    const currentPasswordInput = document.getElementById('currentPassword');
    const currentPasswordIcon = document.getElementById('currentPasswordIcon');
    
    if (toggleCurrentPassword && currentPasswordInput && currentPasswordIcon) {
        toggleCurrentPassword.addEventListener('click', function() {
            if (currentPasswordInput.type === 'password') {
                currentPasswordInput.type = 'text';
                currentPasswordIcon.className = 'fas fa-eye-slash';
            } else {
                currentPasswordInput.type = 'password';
                currentPasswordIcon.className = 'fas fa-eye';
            }
        });
    }

    // New Password Toggle
    const toggleNewPassword = document.getElementById('toggleNewPassword');
    const newPasswordInput = document.getElementById('newPassword');
    const newPasswordIcon = document.getElementById('newPasswordIcon');
    
    if (toggleNewPassword && newPasswordInput && newPasswordIcon) {
        toggleNewPassword.addEventListener('click', function() {
            if (newPasswordInput.type === 'password') {
                newPasswordInput.type = 'text';
                newPasswordIcon.className = 'fas fa-eye-slash';
            } else {
                newPasswordInput.type = 'password';
                newPasswordIcon.className = 'fas fa-eye';
            }
        });
    }

    // Confirm Password Toggle
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');
    
    if (toggleConfirmPassword && confirmPasswordInput && confirmPasswordIcon) {
        toggleConfirmPassword.addEventListener('click', function() {
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                confirmPasswordIcon.className = 'fas fa-eye-slash';
            } else {
                confirmPasswordInput.type = 'password';
                confirmPasswordIcon.className = 'fas fa-eye';
            }
        });
    }
}


function showPasswordMessage(message, type) {
    const messageDiv = document.getElementById('passwordMessage');
    messageDiv.classList.remove('hidden');
    
    if (type === 'success') {
        messageDiv.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
        messageDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + message;
    } else {
        messageDiv.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + message;
    }
}

async function handleSecurityQuestionsUpdate() {
    const questions = [];
    const answers = [];
    const updateBtn = document.getElementById('updateSecurityQuestionsBtn');

    // Collect security questions and answers
    for (let i = 1; i <= 2; i++) {
        const questionSelect = document.querySelector(`select[name="security_question_${i}"]`);
        const answerInput = document.querySelector(`input[name="security_answer_${i}"]`);

        if (questionSelect && answerInput) {
            const question = questionSelect.value;
            const answer = answerInput.value.trim();

            if (!question || !answer) {
                showSecurityMessage(`Please fill in both question and answer for Security Question ${i}.`, 'error');
                showNotification(`Please fill in both question and answer for Security Question ${i}.`, 'error');
                return;
            }

            questions.push(question);
            answers.push(answer);
        }
    }

    if (questions.length === 0) {
        showSecurityMessage('Please set up at least one security question.', 'error');
        showNotification('Please set up at least one security question.', 'error');
        return;
    }

    // Show confirmation modal
    showConfirmationModal(
        'Update Security Questions',
        'Are you sure you want to update your security questions? This will replace your existing questions.',
        'Update Questions',
        'Cancel',
        () => proceedWithSecurityQuestionsUpdate(questions, answers)
    );
}

async function proceedWithSecurityQuestionsUpdate(questions, answers) {
    const updateBtn = document.getElementById('updateSecurityQuestionsBtn');

    // Disable button and show loading
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';

    try {
        const response = await fetch('/tutor/update-security-questions', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                questions: questions,
                answers: answers
            })
        });

        const result = await response.json();

        if (result.success) {
            showSecurityMessage('Security questions updated successfully!', 'success');
            showNotification('Security questions updated successfully!', 'success');
            // Reload security questions
            loadSecurityQuestions();
        } else {
            showSecurityMessage(result.message || 'Failed to update security questions. Please try again.', 'error');
            showNotification(result.message || 'Failed to update security questions. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error updating security questions:', error);
        showSecurityMessage('An error occurred while updating your security questions. Please try again.', 'error');
        showNotification('An error occurred while updating your security questions. Please try again.', 'error');
    } finally {
        // Re-enable button
        updateBtn.disabled = false;
        updateBtn.innerHTML = 'Update Security Questions';
    }
}

function showSecurityMessage(message, type) {
    // Create or find message div
    let messageDiv = document.getElementById('securityMessage');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'securityMessage';
        messageDiv.className = 'hidden mb-4 p-3 rounded-lg text-sm';
        document.getElementById('securityQuestionsContainer').parentNode.insertBefore(messageDiv, document.getElementById('securityQuestionsContainer').nextSibling);
    }
    
    messageDiv.classList.remove('hidden');
    
    if (type === 'success') {
        messageDiv.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
        messageDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + message;
    } else {
        messageDiv.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + message;
    }
}

// Toast notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.toast-notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'toast-notification fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transform translate-x-full transition-transform duration-300 ease-in-out';
    
    // Set icon and colors based on type
    let icon, bgColor, textColor, borderColor;
    switch (type) {
        case 'success':
            icon = 'fas fa-check-circle';
            bgColor = 'bg-green-50 dark:bg-green-900';
            textColor = 'text-green-800 dark:text-green-200';
            borderColor = 'border-green-200 dark:border-green-700';
            break;
        case 'error':
            icon = 'fas fa-exclamation-circle';
            bgColor = 'bg-red-50 dark:bg-red-900';
            textColor = 'text-red-800 dark:text-red-200';
            borderColor = 'border-red-200 dark:border-red-700';
            break;
        case 'warning':
            icon = 'fas fa-exclamation-triangle';
            bgColor = 'bg-yellow-50 dark:bg-yellow-900';
            textColor = 'text-yellow-800 dark:text-yellow-200';
            borderColor = 'border-yellow-200 dark:border-yellow-700';
            break;
        default:
            icon = 'fas fa-info-circle';
            bgColor = 'bg-blue-50 dark:bg-blue-900';
            textColor = 'text-blue-800 dark:text-blue-200';
            borderColor = 'border-blue-200 dark:border-blue-700';
    }

    notification.innerHTML = `
        <div class="p-4 ${bgColor} ${textColor} ${borderColor} border-l-4 rounded-lg">
            <div class="flex items-center">
                <i class="${icon} mr-3 text-lg"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="ml-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Modal confirmation function
function showConfirmationModal(title, message, confirmText, cancelText, onConfirm) {
    // Remove existing modal if any
    const existingModal = document.getElementById('confirmationModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal
    const modal = document.createElement('div');
    modal.id = 'confirmationModal';
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeConfirmationModal()"></div>
            <div class="relative w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">${title}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">${message}</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="closeConfirmationModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors">
                            ${cancelText}
                        </button>
                        <button id="confirmModalBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add to page
    document.body.appendChild(modal);

    // Add event listener to confirm button
    document.getElementById('confirmModalBtn').addEventListener('click', () => {
        closeConfirmationModal();
        if (onConfirm) {
            onConfirm();
        }
    });
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.remove();
    }
}