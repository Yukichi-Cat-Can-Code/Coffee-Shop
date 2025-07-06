<!-- Navbar -->
<nav class="navbar header-top fixed-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="<?php echo ADMINAPPURL; ?>">
            <div class="premium-logo">
                <div class="logo-circle">
                    <div class="bean-shape">
                        <div class="bean-highlight"></div>
                    </div>
                </div>
            </div>
            <div class="brand-titles">
                <h1 class="brand-name">Artisan Coffee</h1>
                <div class="brand-tagline">Premium Management</div>
            </div>
        </a>

        <!-- Toggler for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
            aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav ms-auto d-flex right-nav-items">
                <?php if (isset($_SESSION['admin_name'])): ?>
                    <li class="nav-item" id="store-link">
                        <a target="_blank" class="nav-link" href="<?php echo "http://localhost/coffee-Shop" ?>">
                            <i class="fas fa-store me-1"></i>Store
                        </a>
                    </li>

                    <li class="nav-item dropdown user-dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end premium-dropdown" aria-labelledby="userDropdown">
                            <li>
                                <div class="dropdown-header">
                                    <div class="user-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                                        <div class="user-role">Administrator</div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo ADMINAPPURL ?>/admins/profile.php">
                                    <i class="fas fa-id-card me-2"></i>Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo ADMINAPPURL ?>/admins/settings.php">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item logout-item" href="<?php echo ADMINAPPURL ?>/admins/logout.php">
                                    <i class="fas fa-sign-out-alt me-2" id="logout-icon"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMINAPPURL; ?>/admins/login-admins.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    .navbar-brand {
        padding: 0;
        height: 75px;
        display: flex;
        align-items: center;
        text-decoration: none;
        margin-left: 0;
        z-index: 10;
    }

    .navbar-collapse {
        margin-left: 0;
    }

    /* Enhanced dropdown toggle styling */
    .user-dropdown .dropdown-toggle {
        padding: 8px 15px;
        border-radius: 4px;
        background: linear-gradient(135deg, var(--mocha), var(--dark-roast));
        transition: all 0.3s ease;
    }

    .user-dropdown .dropdown-toggle:hover,
    .user-dropdown.show .dropdown-toggle {
        background: linear-gradient(135deg, var(--dark-roast), var(--mocha));
        color: var(--gold-accent);
    }

    /* Better dropdown hover behavior */
    @media (min-width: 992px) {

        /* Create a hover area container */
        .user-dropdown {
            position: relative;
        }

        /* Fix the gap between toggle and menu */
        .dropdown-menu {
            margin-top: 0 !important;
            padding-top: 5px;
        }

        /* Add padding to create an invisible hover bridge */
        .user-dropdown::after {
            content: '';
            position: absolute;
            height: 15px;
            bottom: -15px;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        /* Show menu on hover with slight delay for better UX */
        .user-dropdown:hover .dropdown-menu {
            display: block;
            opacity: 0;
            animation: fadeIn 0.2s forwards;
            animation-delay: 0.1s;
        }

        /* Keep menu visible when hovering the menu itself */
        .dropdown-menu:hover {
            display: block;
            opacity: 1;
        }
    }

    /* Animation for smooth appearance */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu-end {
        right: 0 !important;
        left: auto !important;
    }

    @media (max-width: 991.98px) {
        .navbar-collapse {
            margin-top: 0;
            padding-top: 15px;
        }

        .user-dropdown .dropdown-toggle {
            justify-content: center;
            width: 100%;
            text-align: center;
        }
    }

    #wrapper {
        margin-left: 230px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced dropdown behavior
        const userDropdowns = document.querySelectorAll('.user-dropdown');

        userDropdowns.forEach(function(dropdown) {
            const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
            let closeTimeout;

            // Desktop hover behavior
            if (window.innerWidth >= 992) {
                // Show dropdown on hover with slight delay
                dropdown.addEventListener('mouseenter', function() {
                    clearTimeout(closeTimeout);
                    dropdownMenu.classList.add('show');
                });

                // Hide dropdown when mouse leaves with delay
                dropdown.addEventListener('mouseleave', function() {
                    closeTimeout = setTimeout(function() {
                        dropdownMenu.classList.remove('show');
                    }, 200); // Small delay to allow movement to menu
                });

                // Clicking toggle still works
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdownMenu.classList.toggle('show');
                });
            } else {
                // Mobile behavior - use click only
                dropdownToggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                    } else {
                        // Close other open dropdowns
                        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                            menu.classList.remove('show');
                        });
                        dropdownMenu.classList.add('show');
                    }
                });
            }
        });

        // Close dropdowns when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 992 && !event.target.closest('.user-dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                    menu.classList.remove('show');
                });
            }
        });

        // Make dropdown items clickable
        document.querySelectorAll('.dropdown-item').forEach(function(item) {
            item.addEventListener('click', function(event) {
                if (this.getAttribute('href')) {
                    window.location.href = this.getAttribute('href');
                }
            });
        });
    });
</script>