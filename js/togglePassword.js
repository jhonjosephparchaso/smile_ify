function togglePassword(inputId) {
  const passwordInput = document.getElementById(inputId);
  passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
}
