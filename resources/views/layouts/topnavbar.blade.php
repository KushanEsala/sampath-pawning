<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div class="shadow-lg p-3 mb-5 bg-body-tertiary rounded">
    <div class="header header-one"
        style="background-image: linear-gradient(to top, #f3e7e9 0%, #e3eeff 99%, #e3eeff 100%); display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">

        <div class="header-left header-left-one">
            <a href="index.html" class="logo mt-2">
                <img src="https://i.ibb.co/F4VxjFy/Untitled.jpg" alt="Logo" style="height: 40px;">
            </a>
        </div>

        <a href="javascript:void(0);" id="toggle_btn" class="text-dark me-3" aria-label="Expand or minimize sidebar">
            <i class="fas fa-bars"></i>
        </a>

        <ul class="nav user-menu">
            <li class="nav-item">
                <a href="{{ route('journalEntry') }}" class="branch-pill">
                    <i class="fas fa-code-branch me-2"></i>
                    <span>Branch: <strong>{{ Auth::user()->Branch }}</strong></span>
                </a>
            </li>

            <li class="nav-item dropdown has-arrow main-drop">
                <a href="#" class="dropdown-toggle nav-link user-link" data-bs-toggle="dropdown">
                    <span class="user-img-wrapper">
                        <i class="fas fa-user-circle"></i>
                    </span>
                    <span class="username-text ms-2">{{ Auth::user()->username }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-end animated-dropdown">
                    <a class="dropdown-item" href="{{ route('profile') }}">
                        <i data-feather="user" class="me-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item logout-link" href="#"
                        onclick="event.preventDefault(); logoutAnimation();">
                        <i data-feather="log-out" class="me-2"></i> Logout
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>

<div id="logoutOverlay" class="logout-overlay d-none">
    <div class="logout-box">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <p class="mb-0 fw-semibold">Securing your session...</p>
    </div>
</div>

<style>
/* Header & Menu Reset */
.user-menu {
    display: flex;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
}

/* Branch Pill Style */
.branch-pill {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(5px);
    padding: 8px 15px;
    border-radius: 50px;
    color: #444 !important;
    text-decoration: none;
    font-size: 13px;
    margin-right: 15px;
    border: 1px solid rgba(0,0,0,0.05);
    transition: 0.3s;
}

.branch-pill:hover {
    background: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.branch-pill i {
    color: #4a90e2;
}

/* User Dropdown Style */
.user-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    padding: 5px 10px !important;
}

.user-img-wrapper i {
    font-size: 28px;
    color: #6c757d;
}

.username-text {
    font-weight: 600;
    color: #333;
}

/* Dropdown Menu Customization */
.animated-dropdown {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    margin-top: 10px !important;
    padding: 10px;
}

.dropdown-item {
    border-radius: 8px;
    padding: 10px 15px;
    display: flex;
    align-items: center;
    color: #555;
    transition: 0.2s;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #000;
}

.logout-link {
    color: #dc3545 !important;
}

.logout-link:hover {
    background-color: #fff5f5;
}

/* Logout Overlay Animations */
.logout-overlay {
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.95);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logout-box {
    background: #fff;
    padding: 30px 50px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    text-align: center;
    animation: bounceIn 0.5s ease;
}

@keyframes bounceIn {
    0% { transform: scale(0.8); opacity: 0; }
    70% { transform: scale(1.05); }
    100% { transform: scale(1); opacity: 1; }
}

.d-none { display: none !important; }
</style>

<script>
// Initialize Feather Icons
document.addEventListener("DOMContentLoaded", function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

function logoutAnimation() {
    const overlay = document.getElementById('logoutOverlay');
    overlay.classList.remove('d-none');

    setTimeout(() => {
        document.getElementById('logout-form').submit();
    }, 1000);
}
</script>
