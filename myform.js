$(document).ready(function() {
    $('#toggleLoginRegister').click(function() {
      $('#loginForm').toggleClass('d-none');
      $('#registerForm').toggleClass('d-none');
      $('#formTitle').text($('#loginForm').hasClass('d-none') ? 'Register' : 'Login');
      $('#toggleLoginRegister').text($('#loginForm').hasClass('d-none') ? 'Switch to Login' : 'Switch to Register');
    });
  
    let loginAttempts = 0;
  
    $('#loginForm').on('submit', function(event) {
      event.preventDefault();
      loginAttempts++;
      if (loginAttempts >= 3) {
        alert('Too many failed attempts. Please try again in 5 seconds.');
        setTimeout(() => {
          loginAttempts = 0;
        }, 5000);
      } else {
        // Proceed with login
        this.submit();
      }
    });
  });
  document.getElementById("forgotPassword").addEventListener("click", function(event) {
    event.preventDefault();
    // Add forgot password logic here
    alert("Forgot Password functionality not implemented yet.");
  });
  
  document.getElementById("forgotEmail").addEventListener("click", function(event) {
    event.preventDefault();
    // Add forgot email logic here
    alert("Forgot Email functionality not implemented yet.");
  });  