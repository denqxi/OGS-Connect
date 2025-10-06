<!-- Account Management Content -->
<div class="space-y-8">

    <!-- Section 1: Security / Account Details -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Security / Account Details</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <!-- System ID -->
        <div>
            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">System ID</label>
            <input type="text" value="{{ $tutor->tutorID ?? 'N/A' }}" readonly
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 cursor-not-allowed">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">System ID is automatically assigned and cannot be changed.</p>
        </div>

        <!-- Username -->
        <div>
            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Username</label>
            <input type="text" value="{{ $tutor->tusername ?? 'N/A' }}" readonly
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 cursor-not-allowed">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Username is automatically assigned and cannot be changed.</p>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Email Address</label>
            <input type="email" value="{{ $tutor->email ?? 'N/A' }}" readonly
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 cursor-not-allowed">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Email address is managed by the system administrator.</p>
        </div>
    </div>

    <hr class="border-gray-200 dark:border-gray-700">

    <!-- Section 2: Change Password (Optional) -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Change Password</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Current Password</label>
                <input type="password" placeholder="Enter your current password"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">New Password</label>
                <input type="password" placeholder="Enter your new password"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Confirm New Password</label>
                <input type="password" placeholder="Confirm your new password"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
            </div>
        </div>

        <!-- Update Password Button -->
        <div class="flex justify-center md:justify-end">
            <button
                class="bg-gray-700 dark:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors duration-200">
                Update Password
            </button>
        </div>
    </div>

    <hr class="border-gray-200 dark:border-gray-700">

    <!-- Section 3: Authentication Questions -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Authentication Questions</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <p class="text-sm text-gray-500 dark:text-gray-400">These questions are used to verify your identity when you need to recover your account or reset your password. Please choose questions and answers that only you would know.</p>

        <div id="securityQuestionsContainer">
            <!-- Security questions will be dynamically loaded here -->
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600 mx-auto"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Loading security questions...</p>
            </div>
        </div>

        <!-- Update Security Questions Button -->
        <div class="flex justify-center md:justify-end">
            <button id="updateSecurityQuestionsBtn"
                class="bg-gray-700 dark:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                Update Security Questions
            </button>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadSecurityQuestions();
    setupEventListeners();
});

function setupEventListeners() {
    // Password change form
    const updatePasswordBtn = document.querySelector('button[class*="bg-gray-700"]');
    if (updatePasswordBtn && updatePasswordBtn.textContent.includes('Update Password')) {
        updatePasswordBtn.addEventListener('click', handlePasswordChange);
    }

    // Security questions update
    const updateSecurityBtn = document.getElementById('updateSecurityQuestionsBtn');
    if (updateSecurityBtn) {
        updateSecurityBtn.addEventListener('click', handleSecurityQuestionsUpdate);
    }
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
    const currentPassword = document.querySelector('input[type="password"][placeholder*="current"]').value;
    const newPassword = document.querySelector('input[type="password"][placeholder*="new password"]').value;
    const confirmPassword = document.querySelector('input[type="password"][placeholder*="Confirm"]').value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        alert('Please fill in all password fields.');
        return;
    }

    if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match.');
        return;
    }

    if (newPassword.length < 6) {
        alert('New password must be at least 6 characters long.');
        return;
    }

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
            alert('Password updated successfully!');
            // Clear form
            document.querySelectorAll('input[type="password"]').forEach(input => input.value = '');
        } else {
            alert(result.message || 'Failed to update password. Please try again.');
        }
    } catch (error) {
        console.error('Error updating password:', error);
        alert('An error occurred while updating your password. Please try again.');
    }
}

async function handleSecurityQuestionsUpdate() {
    const questions = [];
    const answers = [];

    // Collect security questions and answers
    for (let i = 1; i <= 2; i++) {
        const questionSelect = document.querySelector(`select[name="security_question_${i}"]`);
        const answerInput = document.querySelector(`input[name="security_answer_${i}"]`);

        if (questionSelect && answerInput) {
            const question = questionSelect.value;
            const answer = answerInput.value.trim();

            if (!question || !answer) {
                alert(`Please fill in both question and answer for Security Question ${i}.`);
                return;
            }

            questions.push(question);
            answers.push(answer);
        }
    }

    if (questions.length === 0) {
        alert('Please set up at least one security question.');
        return;
    }

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
            alert('Security questions updated successfully!');
            // Reload security questions
            loadSecurityQuestions();
        } else {
            alert(result.message || 'Failed to update security questions. Please try again.');
        }
    } catch (error) {
        console.error('Error updating security questions:', error);
        alert('An error occurred while updating your security questions. Please try again.');
    }
}
</script>
