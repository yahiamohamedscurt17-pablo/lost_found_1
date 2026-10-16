document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert').forEach((el) => {
    if (el.classList.contains('success')) {
      window.setTimeout(() => {
        el.style.transition = 'opacity .4s ease';
        el.style.opacity = '0';
        window.setTimeout(() => el.remove(), 450);
      }, 5000);
    }
  });
});


// إظهار الحقول المناسبة لنوع الحساب في صفحة التسجيل.
document.addEventListener('DOMContentLoaded', () => {
  const role = document.querySelector('#account-role');
  const classField = document.querySelector('#student-class-field');
  const classSelect = document.querySelector('#class-code');
  const adminField = document.querySelector('#admin-code-field');
  const adminCode = document.querySelector('#admin-code');
  if (!role || !classField || !classSelect || !adminField || !adminCode) return;
  const updateRoleFields = () => {
    const isStudent = role.value === 'student';
    classField.hidden = !isStudent;
    classSelect.required = isStudent;
    adminField.hidden = isStudent;
    adminCode.required = !isStudent;
  };
  role.addEventListener('change', updateRoleFields);
  updateRoleFields();
});
