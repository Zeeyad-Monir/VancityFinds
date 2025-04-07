<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login / Sign Up</title>
  <!-- Example Google Fonts (optional) -->
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans&display=swap"
    rel="stylesheet"
  />
  <style>
    /* Basic Reset */
    * {
      margin: 0; 
      padding: 0; 
      box-sizing: border-box;
    }
    body {
      font-family: 'Open Sans', sans-serif;
      background: #f8ede9; /* Light pastel background to match the skeleton illustration vibe */
      display: flex;
      min-height: 100vh;
      align-items: center;
      justify-content: center;
    }
    .auth-container {
      display: flex;
      width: 80%;
      max-width: 1000px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
    }
    /* Left Side: Illustration */
.auth-illustration {
  flex: 1;
  background: url('photos/vancouverReal.png') no-repeat center center;
  background-size: cover;
  display: none; 
}

    /* Right Side: Form container */
    .auth-forms {
      flex: 1;
      padding: 2rem;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .auth-forms h2 {
      font-family: 'Montserrat', sans-serif;
      font-size: 1.8rem;
      margin-bottom: 1rem;
      text-align: center;
    }
    .auth-forms p.subtitle {
      font-size: 0.95rem;
      color: #666;
      text-align: center;
      margin-bottom: 2rem;
    }
    /* Google Button */
    .google-btn {
      display: none; /* Hide Google button as we're removing Firebase */
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.3rem;
    }
    .form-group input {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 0.95rem;
    }
    .form-link {
      display: inline-block;
      margin-top: 0.5rem;
      font-size: 0.85rem;
      text-decoration: none;
      color: #a35b5b;
    }
    .form-link:hover {
      text-decoration: underline;
    }
    .submit-btn {
      width: 100%;
      background: #7a3e3e; /* Deep maroon-like color for the button */
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 0.8rem;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 1rem;
      transition: background 0.3s;
    }
    .submit-btn:hover {
      background: #5f2f2f;
    }
    .toggle-section {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
    .toggle-section a {
      color: #7a3e3e;
      font-weight: 600;
      margin-left: 0.2rem;
    }
    .toggle-section a:hover {
      text-decoration: underline;
    }
    /* Show/hide forms */
    .form-hidden {
      display: none;
    }
    
    /* Visitor Button Styles */
    .visitor-btn {
      display: block;
      width: 100%;
      background-color: transparent;
      color: #666;
      border: 1px dashed #ccc;
      border-radius: 4px;
      padding: 0.6rem;
      font-size: 0.9rem;
      text-align: center;
      margin-top: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .visitor-btn:hover {
      background-color: #f8f8f8;
      color: #333;
      border-color: #999;
    }
    
    .or-divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 1rem 0;
      color: #999;
      font-size: 0.85rem;
    }
    
    .or-divider::before,
    .or-divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #eee;
    }
    
    .or-divider::before {
      margin-right: 0.5rem;
    }
    
    .or-divider::after {
      margin-left: 0.5rem;
    }
    
    /* Media queries */
    @media (min-width: 900px) {
      .auth-illustration {
        display: block; /* Show illustration on larger screens */
      }
    }
    
    /* Toast Notification Styles */
    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      max-width: 350px;
      width: 100%;
      pointer-events: none;
    }

    .toast {
      display: flex;
      align-items: center;
      background-color: white;
      border-left: 4px solid #38a169;
      border-radius: 6px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
      margin-bottom: 16px;
      padding: 16px;
      transform: translateX(120%);
      transition: transform 0.3s ease-in-out;
      pointer-events: auto;
      opacity: 0;
    }

    .toast.show {
      transform: translateX(0);
      opacity: 1;
    }

    .toast-success {
      border-left-color: #38a169;
    }

    .toast-error {
      border-left-color: #e53e3e;
    }

    .toast-icon {
      color: #38a169;
      flex-shrink: 0;
      margin-right: 12px;
    }

    .toast-error .toast-icon {
      color: #e53e3e;
    }

    .toast-content {
      flex: 1;
    }

    .toast-title {
      font-weight: 700;
      font-size: 0.95rem;
      margin-bottom: 4px;
      color: #1a202c;
    }

    .toast-message {
      font-size: 0.85rem;
      color: #4a5568;
    }

    .toast-close {
      background: transparent;
      border: none;
      color: #a0aec0;
      cursor: pointer;
      padding: 4px;
      margin-left: 8px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background-color 0.2s, color 0.2s;
    }

    .toast-close:hover {
      background-color: #f7fafc;
      color: #718096;
    }

    /* Progress Bar for Auto-dismiss */
    .toast-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 3px;
      background-color: rgba(66, 153, 225, 0.5);
      width: 100%;
      border-radius: 0 0 6px 6px;
      transform-origin: left;
    }

    /* Toast animation */
    @keyframes progress {
      from { transform: scaleX(1); }
      to { transform: scaleX(0); }
    }

    /* Mobile responsiveness */
    @media screen and (max-width: 576px) {
      .toast-container {
        left: 20px;
        right: 20px;
        max-width: none;
      }
    }
  </style>
</head>
<body>
  <div class="auth-container">
    <!-- Left Side: Illustration -->
    <div class="auth-illustration"></div>
    
    <!-- Right Side: Forms -->
    <div class="auth-forms">
      <!-- LOGIN FORM -->
      <div id="login-form">
        <h2>Login to Your Account</h2>
        <p class="subtitle">See what is going on in Vancouver</p>
        
        <div class="form-group">
          <label for="login-email">Email</label>
          <input type="email" id="login-email" placeholder="Enter your email" />
        </div>
        <div class="form-group">
          <label for="login-password">Password</label>
          <input type="password" id="login-password" placeholder="Enter your password" />
        </div>
        <a href="#" id="forgot-password-link" class="form-link">Forgot Password?</a>
        
        <button class="submit-btn" id="login-submit-btn">Login</button>
        
        <div class="or-divider">or</div>
        
        <button id="guest-login-btn" class="visitor-btn">View as Visitor</button>
        
        <div class="toggle-section">
          Not Registered Yet?
          <a href="#" id="show-signup">Create an account</a>
        </div>
      </div>
      
      <!-- SIGNUP FORM (hidden by default) -->
      <div id="signup-form" class="form-hidden">
        <h2>Create an Account</h2>
        <p class="subtitle">Start for free and enjoy the community</p>
        
        <div class="form-group">
          <label for="signup-email">Email</label>
          <input type="email" id="signup-email" placeholder="Enter your email" />
        </div>
        <div class="form-group">
          <label for="signup-password">Password</label>
          <input type="password" id="signup-password" placeholder="Create a password" />
        </div>
        <div class="form-group">
          <label for="signup-password2">Confirm Password</label>
          <input type="password" id="signup-password2" placeholder="Confirm your password" />
        </div>
        
        <button class="submit-btn" id="signup-submit-btn">Sign Up</button>
        
        <div class="or-divider">or</div>
        
        <button id="guest-signup-btn" class="visitor-btn">View as Visitor</button>
        
        <div class="toggle-section">
          Already have an account?
          <a href="#" id="show-login">Log In</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toast-container" class="toast-container"></div>

  <!-- Auth Logic -->
  <script>
    /***** DOM Elements *****/
    const loginForm = document.getElementById("login-form");
    const signupForm = document.getElementById("signup-form");
    
    const showSignupLink = document.getElementById("show-signup");
    const showLoginLink = document.getElementById("show-login");
    
    const loginSubmitBtn = document.getElementById("login-submit-btn");
    const signupSubmitBtn = document.getElementById("signup-submit-btn");
    
    const loginEmail = document.getElementById("login-email");
    const loginPassword = document.getElementById("login-password");
    
    const signupEmail = document.getElementById("signup-email");
    const signupPassword = document.getElementById("signup-password");
    const signupPassword2 = document.getElementById("signup-password2");
    
    const guestLoginBtn = document.getElementById("guest-login-btn");
    const guestSignupBtn = document.getElementById("guest-signup-btn");
    
    const forgotPasswordLink = document.getElementById("forgot-password-link");

    /***** Toggle between Login and Signup forms *****/
    showSignupLink.addEventListener("click", (e) => {
      e.preventDefault();
      loginForm.classList.add("form-hidden");
      signupForm.classList.remove("form-hidden");
    });
    showLoginLink.addEventListener("click", (e) => {
      e.preventDefault();
      signupForm.classList.add("form-hidden");
      loginForm.classList.remove("form-hidden");
    });

    /***** Toast Notification System *****/
    class ToastNotification {
      constructor() {
        this.init();
      }

      init() {
        this.container = document.getElementById('toast-container');
      }

      show({ title = 'Success!', message = '', type = 'success', duration = 5000 }) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Create progress bar for auto-dismiss
        const progressBar = document.createElement('div');
        progressBar.className = 'toast-progress';
        
        // Add content to toast
        toast.innerHTML = `
          <div class="toast-icon">
            ${type === 'success' ? 
              '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : 
              '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'
            }
          </div>
          <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
          </div>
          <button class="toast-close" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        `;
        
        // Add progress bar for auto-dismiss
        toast.appendChild(progressBar);
        
        // Add to container
        this.container.appendChild(toast);
        
        // Close button functionality
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
          this.dismiss(toast);
        });
        
        // Animation for progress bar
        progressBar.style.animation = `progress ${duration}ms linear forwards`;
        
        // Show toast with animation
        setTimeout(() => {
          toast.classList.add('show');
        }, 10);
        
        // Auto dismiss
        this.autoClose = setTimeout(() => {
          this.dismiss(toast);
        }, duration);
      }
      
      dismiss(toast) {
        // Remove show class to trigger hide animation
        toast.classList.remove('show');
        
        // Remove from DOM after animation completes
        setTimeout(() => {
          if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
          }
        }, 300);
      }
      
      success(message, title = 'Success!') {
        this.show({ title, message, type: 'success' });
      }
      
      error(message, title = 'Error') {
        this.show({ title, message, type: 'error' });
      }
    }

    // Initialize toast notification system
    const toast = new ToastNotification();

    // Replace alert with toast
    window.showToast = (message, type = 'success', title) => {
      if (type === 'success') {
        toast.success(message, title);
      } else if (type === 'error') {
        toast.error(message, title);
      }
    };

    /***** Email/Password Login *****/
    loginSubmitBtn.addEventListener("click", async () => {
      const email = loginEmail.value.trim();
      const password = loginPassword.value.trim();

      if (!email || !password) {
        showToast("Please fill in all login fields.", "error");
        return;
      }
      
      try {
        // Create form data for submission
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
        
        // Send login request to server
        const response = await fetch('login.php', {
          method: 'POST',
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          showToast("Successfully logged in!", "success", "Welcome Back!");
          // Redirect to main page after a short delay for the toast to be visible
          setTimeout(() => {
            window.location.href = "index.php?auth=success";
          }, 1500);
        } else {
          showToast(data.message, "error", "Login Error");
        }
      } catch (err) {
        showToast("An error occurred during login. Please try again.", "error", "Login Error");
        console.error(err);
      }
    });

    /***** Email/Password Sign Up *****/
    signupSubmitBtn.addEventListener("click", async () => {
      const email = signupEmail.value.trim();
      const pass1 = signupPassword.value.trim();
      const pass2 = signupPassword2.value.trim();

      if (!email || !pass1 || !pass2) {
        showToast("Please fill in all signup fields.", "error");
        return;
      }
      if (pass1 !== pass2) {
        showToast("Passwords do not match.", "error");
        return;
      }
      
      try {
        // Create form data for submission
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', pass1);
        formData.append('password2', pass2);
        
        // Send registration request to server
        const response = await fetch('register.php', {
          method: 'POST',
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          showToast("Account created successfully!", "success", "Welcome to Vancity Finds!");
          // Redirect to main page after a short delay for the toast to be visible
          setTimeout(() => {
            window.location.href = "index.php?auth=success";
          }, 1500);
        } else {
          showToast(data.message, "error", "Signup Error");
        }
      } catch (err) {
        showToast("An error occurred during registration. Please try again.", "error", "Signup Error");
        console.error(err);
      }
    });

    /***** Guest Login *****/
    const handleGuestLogin = async () => {
      try {
        // Send guest login request to server
        const response = await fetch('guest_login.php');
        const data = await response.json();
        
        if (data.success) {
          showToast("Accessing as guest!", "success", "Welcome!");
          // Redirect to main page after a short delay for the toast to be visible
          setTimeout(() => {
            window.location.href = "index.php?auth=guest";
          }, 1500);
        } else {
          showToast("Failed to access as guest.", "error", "Guest Access Error");
        }
      } catch (err) {
        showToast("An error occurred. Please try again.", "error", "Guest Access Error");
        console.error(err);
      }
    };
    
    guestLoginBtn.addEventListener("click", handleGuestLogin);
    guestSignupBtn.addEventListener("click", handleGuestLogin);

    /***** Forgot Password *****/
    forgotPasswordLink.addEventListener("click", async (e) => {
      e.preventDefault();
      const email = prompt("Enter your email to reset password:");
      if (!email) return;
      
      showToast("This feature is not yet implemented in the MySQL version.", "error", "Not Implemented");
      // In a real implementation, you would send a request to a password reset endpoint
    });
    
    // Check for mode parameter in URL
    document.addEventListener('DOMContentLoaded', () => {
      // Check for mode parameter in URL
      const urlParams = new URLSearchParams(window.location.search);
      const authMode = urlParams.get('mode');
      
      // If mode is 'login', show login form
      if (authMode === 'login') {
        document.getElementById('login-form').classList.remove('form-hidden');
        document.getElementById('signup-form').classList.add('form-hidden');
      }
      
      // If mode is 'signup', show signup form
      if (authMode === 'signup') {
        document.getElementById('signup-form').classList.remove('form-hidden');
        document.getElementById('login-form').classList.add('form-hidden');
      }
    });
  </script>
</body>
</html>
