<!-- Only include this file after header.php and navbar.php -->

<aside class="side-nav-container">
    <ul class="navbar-nav side-nav">
        <li class="nav-item">
            <a class="nav-link" id="home-link" href="<?php echo ADMINAPPURL; ?>">
                <i class="fas fa-home me-2"></i>Home
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="admin-link" href="<?php echo ADMINAPPURL; ?>/admins/admins.php">
                <i class="fas fa-user-shield me-2"></i>Admins
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pos-link" href="<?php echo ADMINAPPURL; ?>/pos-admins/index.php">
                <i class="fas fa-cash-register me-2"></i>POS Terminal
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="orders-link" href="<?php echo ADMINAPPURL; ?>/orders-admins/show-orders.php">
                <i class="fas fa-shopping-bag me-2"></i>Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="products-link" href="<?php echo ADMINAPPURL; ?>/products-admins/show-products.php">
                <i class="fas fa-coffee me-2"></i>Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="bookings-link" href="<?php echo ADMINAPPURL; ?>/bookings-admins/show-bookings.php">
                <i class="fas fa-calendar-check me-2"></i>Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="reviews-link" href="<?php echo ADMINAPPURL; ?>/reviews-admins/show-reviews.php">
                <i class="fas fa-star me-2"></i>Reviews
            </a>
        </li>
        <li class="nav-item"><a class="nav-link" id="reviews-link" href="<?php echo ADMINAPPURL; ?>/reviews-admins/show-reviews.php"><a class="nav-link" id="reviews-link" href="<?php echo ADMINAPPURL; ?>/reviews-admins/show-reviews.php"></li>
        <a class="nav-link" id="about-link" href="<?php echo ADMINAPPURL; ?>/admins/about.php">
            <i class="fas fa-info-circle me-2"></i>About
        </a>
        </li>
    </ul>
</aside>

<style>
    /* Enhanced sidebar styling for Bootstrap 5 */
    .side-nav-container {
        background: #2D1E18;
        height: 100%;
        position: fixed;
        top: 56px;
        left: 0;
        width: 250px;
        z-index: 1000;
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    .side-nav {
        padding: 1rem 0;
    }

    .side-nav .nav-item {
        margin: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }

    .side-nav .nav-item:hover {
        background-color: rgba(177, 131, 80, 0.1);
    }

    .side-nav .nav-item .nav-link {
        color: #E6D8CC;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }

    .side-nav .nav-item .nav-link:hover,
    .side-nav .nav-item .nav-link.active {
        color: #C9A66B;
        background-color: rgba(177, 131, 80, 0.15);
    }

    .side-nav .nav-item .nav-link i {
        color: #B18350;
        width: 20px;
        text-align: center;
        transition: all 0.2s;
    }

    .side-nav .nav-item .nav-link:hover i,
    .side-nav .nav-item .nav-link.active i {
        color: #C9A66B;
    }

    /* Responsive sidebar */
    @media (max-width: 991.98px) {
        .side-nav-container {
            width: 60px;
            overflow-x: hidden;
        }

        .side-nav .nav-item .nav-link span {
            display: none;
        }

        .side-nav .nav-item {
            margin: 0.25rem 0.5rem;
        }

        .side-nav .nav-item .nav-link {
            padding: 0.75rem;
            justify-content: center;
        }

        .side-nav .nav-item .nav-link i {
            margin-right: 0 !important;
            font-size: 1.1rem;
        }
    }
</style>

<script>
    // Set active menu item based on current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.side-nav .nav-link');

        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (currentPath.includes(href) ||
                (currentPath.endsWith('/admin-panel/') && href === '<?php echo ADMINAPPURL; ?>')) {
                item.classList.add('active');
            }
        });
    });
</script>