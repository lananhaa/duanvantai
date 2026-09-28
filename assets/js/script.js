document.addEventListener('DOMContentLoaded', function() {
    // Toggle Sidebar
    const toggleSidebarBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    if (toggleSidebarBtn && sidebar) {
        toggleSidebarBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }

    const navLinks = document.querySelectorAll('.nav-link[href="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            document.querySelectorAll('.nav-link').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye / eye slash icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    const customerModal = document.getElementById('customerModal');
    const customerForm = document.getElementById('customerForm');
    if (customerModal && customerForm) {
        const title = document.getElementById('customerModalTitle');
        const id = document.getElementById('customerId');
        const name = document.getElementById('customerName');
        const username = document.getElementById('customerUsername');
        const password = document.getElementById('customerPassword');
        const passwordRequired = document.getElementById('passwordRequired');
        const passwordHint = document.getElementById('passwordHint');

        const closeModal = () => {
            customerModal.classList.remove('is-open');
            customerModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        };
        document.querySelectorAll('[data-customer-modal]').forEach(button => {
            button.addEventListener('click', () => {
                const isEdit = button.dataset.customerModal === 'edit';
                customerForm.reset();
                id.value = isEdit ? button.dataset.id : '';
                name.value = isEdit ? button.dataset.name : '';
                username.value = isEdit ? button.dataset.username : '';
                password.required = !isEdit;
                passwordRequired.textContent = isEdit ? '' : '*';
                passwordHint.textContent = isEdit ? 'Để trống nếu không muốn đổi mật khẩu' : 'Mật khẩu đăng nhập của khách hàng';
                title.textContent = isEdit ? 'Sửa khách hàng' : 'Thêm khách hàng';
                document.getElementById('customerPhone').value = isEdit ? button.dataset.phone : '';
                document.getElementById('customerEmail').value = isEdit ? button.dataset.email : '';
                document.getElementById('customerAddress').value = isEdit ? button.dataset.address : '';
                customerModal.classList.add('is-open');
                customerModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                name.focus();
            });
        });
        document.querySelectorAll('[data-close-customer-modal]').forEach(button => button.addEventListener('click', closeModal));
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && customerModal.classList.contains('is-open')) closeModal(); });
    }

    const orderModal = document.getElementById('orderModal');
    if (orderModal) {
        document.querySelectorAll('[data-order-modal="create"]').forEach(button => button.addEventListener('click', () => {
            orderModal.classList.add('is-open');
            orderModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        }));
        document.querySelectorAll('[data-close-order-modal]').forEach(button => button.addEventListener('click', () => {
            window.location.href = 'index.php?page=orders';
        }));
    }
});
