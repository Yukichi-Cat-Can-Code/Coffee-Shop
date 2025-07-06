<?php

require_once __DIR__ . "/../../config/config.php";

define("ADMINPATH", dirname(__DIR__));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Premium Coffee Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Your CSS links remain the same -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?php echo ADMINAPPURL ?>/styles/style.css" rel="stylesheet">
  <style>
    :root {
      --espresso: #25120F;
      --dark-roast: #3A2113;
      --mocha: #583E25;
      --caramel: #B18350;
      --gold-accent: #C9A66B;
      --cream: #F3EEE5;
      --milk: #FCFAF7;
      --deep-gold: #9D7553;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html,
    body {
      height: 100%;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background-color: var(--milk);
      color: var(--espresso);
      padding-top: 75px;
      overflow-x: hidden;
      display: flex;
      flex-direction: column;
    }

    /* Elegant woodgrain texture for background */
    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url('https://images.unsplash.com/photo-1585314062340-f1a5a7c9328d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwxfDB8MXxyYW5kb218MHx8d29vZHx8fHx8fDE2MjMzNjg4MjA&ixlib=rb-1.2.1&q=80&utm_campaign=api-credit&utm_medium=referral&utm_source=unsplash_source&w=1920');
      background-size: cover;
      opacity: 0.03;
      z-index: -1;
      pointer-events: none;
    }

    /* Premium Header Styles */
    .navbar.header-top {
      background: var(--espresso) !important;
      padding: 0;
      height: 75px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      width: 100%;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1030;
    }

    /* Gold accent bar */
    .navbar.header-top::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--deep-gold), var(--gold-accent), var(--caramel), var(--gold-accent), var(--deep-gold));
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Container sizing */
    .navbar .container {
      height: 100%;
      display: flex;
      align-items: center;
      position: relative;
    }

    .navbar-brand {
      padding: 0;
      height: 75px;
      display: flex;
      align-items: center;
      text-decoration: none;
      position: relative;
      left: -65pt;
      margin-right: 0;
      z-index: 10;
    }

    /* Ensure enough space for the logo */
    .navbar-collapse {
      margin-left: 110pt;
    }

    /* Logo styling */
    .premium-logo {
      position: relative;
      margin-right: 15px;
    }

    .logo-circle {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--gold-accent) 0%, var(--caramel) 100%);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15), inset 0 2px 4px rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      transition: all 0.3s ease;
    }

    .navbar-brand:hover .logo-circle {
      transform: scale(1.05);
      box-shadow: 0 5px 12px rgba(0, 0, 0, 0.2), inset 0 2px 4px rgba(255, 255, 255, 0.2);
    }

    /* Coffee Bean Logo Design */
    .bean-shape {
      position: absolute;
      background: var(--espresso);
      border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
      width: 22px;
      height: 28px;
      transform: rotate(45deg);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .bean-shape::after {
      content: '';
      position: absolute;
      width: 2px;
      height: 18px;
      background: var(--caramel);
      opacity: 0.5;
      border-radius: 1px;
    }

    .bean-highlight {
      position: absolute;
      width: 8px;
      height: 6px;
      top: 6px;
      left: 7px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 50%;
      transform: rotate(-45deg);
    }

    .brand-titles {
      display: flex;
      flex-direction: column;
    }

    .brand-name {
      font-family: 'Playfair Display', serif;
      color: var(--gold-accent);
      font-size: 1.4rem;
      line-height: 1;
      font-weight: 600;
      letter-spacing: 0.5px;
      margin: 0;
    }

    .brand-tagline {
      font-size: 0.7rem;
      font-weight: 400;
      color: var(--caramel);
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-top: 2px;
    }

    /* Navigation styling */
    .navbar-toggler {
      border-color: rgba(201, 166, 107, 0.5);
      background-color: rgba(201, 166, 107, 0.1);
      margin-right: 0;
      z-index: 1020;
    }

    .navbar-toggler:focus,
    .navbar-toggler:hover {
      outline: none;
      box-shadow: none;
    }

    /* Content layout */
    .page-container {
      display: flex;
      flex: 1;
    }

    /* Side navigation styling - FIXED */
    .side-nav {
      padding-top: 10px;
      background: linear-gradient(to bottom, var(--dark-roast), var(--espresso));
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      width: 230px;
      transition: all 0.3s ease;
    }

    .side-nav .nav-item {
      margin: 2px 0;
      position: relative;
      width: 100%;
    }

    .side-nav .nav-link {
      color: var(--cream);
      padding: 12px 20px;
      margin-left: 0;
      font-size: 0.95rem;
      font-weight: 500;
      letter-spacing: 0.5px;
      transition: all 0.3s;
      position: relative;
      opacity: 0.8;
      display: flex;
      align-items: center;
    }

    .side-nav .nav-link i {
      margin-right: 12px;
      width: 18px;
      text-align: center;
      font-size: 1.1rem;
    }

    .side-nav .nav-link:hover {
      color: var(--gold-accent);
      opacity: 1;
      background-color: rgba(0, 0, 0, 0.1);
    }

    /* Underline effect for nav links */
    .side-nav .nav-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 10px;
      left: 20px;
      background: var(--gold-accent);
      transition: width 0.3s;
    }

    .side-nav .nav-link:hover::after {
      width: 30px;
    }

    /* Active link styling */
    .side-nav .nav-link.active {
      color: var(--gold-accent);
      opacity: 1;
      background-color: rgba(0, 0, 0, 0.15);
      border-left: 3px solid var(--gold-accent);
      padding-left: 17px;
    }

    .side-nav .nav-link.active::after {
      width: 30px;
    }

    /* Right side menu styling */
    .navbar-nav.ms-md-auto {
      margin-left: auto !important;
      z-index: 1100;
      display: flex;
      flex-direction: row;
    }

    .ms-md-auto .nav-item {
      margin: 0 3px;
      position: relative;
    }

    .ms-md-auto .nav-link {
      color: var(--caramel);
      transition: all 0.3s;
      font-weight: 500;
      padding: 8px 15px;
    }

    .ms-md-auto .nav-link:hover {
      color: var(--gold-accent);
    }

    /* Enhanced Dropdown Menu Styles */
    .premium-dropdown {
      overflow: hidden;
      background: linear-gradient(to bottom, var(--milk) 0%, var(--cream) 100%);
      border-radius: 8px;
      border: 1px solid rgba(201, 166, 107, 0.2);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2), 0 0 1px rgba(0, 0, 0, 0.15);
      padding: 0;
    }

    .dropdown-header {
      display: flex;
      align-items: center;
      padding: 15px;
      background: linear-gradient(135deg, var(--caramel), var(--gold-accent));
      color: var(--dark-roast);
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .user-avatar i {
      font-size: 1.8rem;
      color: var(--milk);
    }

    .user-info {
      flex: 1;
    }

    .user-name {
      font-weight: 600;
      font-size: 0.9rem;
      color: var(--espresso);
    }

    .user-role {
      font-size: 0.75rem;
      color: rgba(58, 33, 19, 0.8);
    }

    .dropdown-divider {
      margin: 0;
      border-top: 1px solid rgba(201, 166, 107, 0.2);
    }

    /* Store link styling */
    .ms-md-auto .nav-item:first-child .nav-link {
      border: 1px solid var(--caramel);
      border-radius: 4px;
      padding: 7px 15px;
    }

    .ms-md-auto .nav-item:first-child .nav-link:hover {
      background: rgba(201, 166, 107, 0.1);
      color: var(--gold-accent);
      border-color: var(--gold-accent);
    }

    /* Fixed dropdown styles */
    .dropdown-toggle {
      background: linear-gradient(135deg, var(--mocha), var(--dark-roast));
      border-radius: 4px;
      cursor: pointer;
      position: relative;
    }

    .dropdown-toggle::after {
      vertical-align: middle;
      margin-left: 5px;
    }

    .dropdown-menu {
      display: none;
      position: absolute;
      right: 0;
      left: auto;
      top: 100%;
      min-width: 200px;
      padding: 10px 0;
      margin-top: 10px;
      border: none;
      border-radius: 4px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
      background: var(--milk);
    }

    .dropdown-menu.show {
      display: block;
      z-index: 1050;
      animation: fadeDown 0.3s ease forwards;
    }

    @keyframes fadeDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .dropdown-item {
      padding: 12px 20px;
      display: flex;
      align-items: center;
      color: var(--mocha);
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }

    .dropdown-item i {
      width: 20px;
      text-align: center;
      font-size: 1rem;
      color: var(--caramel);
      transition: all 0.2s ease;
    }

    .dropdown-item:hover {
      background-color: var(--cream);
      color: var(--espresso);
    }

    .dropdown-item:hover i {
      transform: translateX(3px);
      color: var(--gold-accent);
    }

    /* Logout item special styling */
    .logout-item {
      border-radius: 0 0 8px 8px;
      color: var(--espresso);
      font-weight: 500;
    }

    .logout-item i {
      color: #c75c5c;
    }

    .logout-item:hover {
      background: linear-gradient(to right, rgba(199, 92, 92, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
    }

    .logout-item:hover i {
      color: #c75c5c;
    }

    /* Right-aligned nav items */
    .ms-md-auto {
      justify-content: flex-end;
      margin-left: auto !important;
    }

    #store-link {
      margin-right: 10px;
    }

    /* Make dropdown position more precise */
    .dropdown-menu-end {
      right: 0;
      left: auto;
    }

    @media (max-width: 991px) {
      .ms-md-auto {
        align-items: center;
      }

      #store-link {
        margin-right: 0;
        width: 100%;
        text-align: center;
      }

      .dropdown-menu-end {
        position: static;
        width: 100%;
      }

      .premium-dropdown {
        border-radius: 4px;
      }

      .logout-item {
        border-radius: 0 0 4px 4px;
      }
    }

    /* Login button styling */
    .ms-md-auto .nav-item:last-child .nav-link {
      background: linear-gradient(135deg, var(--gold-accent), var(--caramel));
      color: var(--espresso);
      border-radius: 4px;
      padding: 7px 15px;
      font-weight: 600;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .ms-md-auto .nav-item:last-child .nav-link:hover {
      background: linear-gradient(135deg, var(--caramel), var(--gold-accent));
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Wrapper and main content area */
    #wrapper {
      flex: 1;
      padding: 20px 25px;
      transition: all 0.3s ease;
      min-height: calc(100vh - 135px);
      /* Account for header and footer */
    }

    /* Footer Styles */
    .admin-footer {
      background: var(--dark-roast);
      color: var(--cream);
      padding: 15px 0;
      box-shadow: 0 -3px 10px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .admin-footer::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, var(--deep-gold), var(--gold-accent), var(--caramel), var(--gold-accent), var(--deep-gold));
      opacity: 0.7;
    }

    .footer-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }

    .footer-logo {
      display: flex;
      align-items: center;
    }

    .footer-logo .mini-logo {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--gold-accent) 0%, var(--caramel) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
    }

    .footer-logo .mini-bean {
      position: relative;
      background: var(--espresso);
      border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
      width: 15px;
      height: 20px;
      transform: rotate(45deg);
    }

    .footer-text {
      font-size: 0.8rem;
      opacity: 0.8;
    }

    .footer-nav {
      display: flex;
      align-items: center;
    }

    .footer-nav a {
      color: var(--gold-accent);
      margin-left: 15px;
      font-size: 0.85rem;
      transition: all 0.3s;
      text-decoration: none;
    }

    .footer-nav a:hover {
      color: var(--cream);
    }

    .back-to-top {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 35px;
      height: 35px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      color: var(--gold-accent);
      transition: all 0.3s;
      cursor: pointer;
    }

    .back-to-top:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-3px);
    }

    /* Responsive styles - FIXED SIDEBAR */
    @media (min-width: 992px) {
      .page-container {
        padding-top: 75px;
        min-height: 100vh;
      }

      .side-nav-container {
        position: fixed;
        top: 75px;
        left: 0;
        width: 230px;
        height: calc(100% - 75px);
        z-index: 1000;
        overflow-y: auto;
      }

      #wrapper {
        margin-left: 230px;
        width: calc(100% - 230px);
      }

      .admin-footer {
        margin-left: 230px;
        width: calc(100% - 230px);
      }

      /* Keep the navbar links visible in desktop mode */
      .navbar-collapse.collapse:not(.show) {
        display: flex !important;
      }
    }

    @media (max-width: 991px) {
      .page-container {
        flex-direction: column;
      }

      .side-nav-container {
        display: none;
        width: 100%;
      }

      .side-nav-container.show {
        display: block;
      }

      /* Reset logo position on mobile */
      .navbar-brand {
        left: 0;
        margin-right: 0;
      }

      .navbar-collapse {
        margin-left: 0;
        background-color: var(--espresso);
        padding: 15px;
        margin-top: 15px;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 1200;
        position: absolute;
        top: 75px;
        left: 0;
        right: 0;
      }

      #wrapper {
        margin-left: 0;
        width: 100%;
        padding-top: 30px;
      }

      .admin-footer {
        margin-left: 0;
        width: 100%;
      }

      .side-nav {
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
        width: 100%;
        height: auto;
      }

      .side-nav .nav-item {
        width: 100%;
      }

      .side-nav .nav-link {
        margin-left: 0;
        padding: 10px;
      }

      .ms-md-auto {
        flex-direction: column;
        width: 100%;
      }

      .ms-md-auto .nav-item {
        width: 100%;
        margin: 5px 0;
      }

      .ms-md-auto .nav-item .nav-link {
        width: 100%;
        text-align: center;
      }

      .dropdown-menu {
        position: static;
        width: 100%;
        margin-top: 5px;
        box-shadow: none;
        border: 1px solid rgba(201, 166, 107, 0.2);
      }

      .footer-content {
        flex-direction: column;
        text-align: center;
      }

      .footer-logo {
        margin-bottom: 10px;
      }

      .footer-nav {
        margin-top: 10px;
        justify-content: center;
      }
    }

    @media (max-width: 575.98px) {
      .navbar.header-top {
        padding: 0;
        height: auto;
        min-height: 60px;
      }

      .navbar-brand {
        height: 60px;
        padding: 5px 0;
      }

      .brand-name {
        font-size: 1.2rem;
      }

      .brand-tagline {
        font-size: 0.6rem;
        letter-spacing: 1px;
      }

      .logo-circle {
        width: 36px;
        height: 36px;
      }

      .bean-shape {
        width: 20px;
        height: 24px;
      }

      .side-nav .nav-link {
        padding: 8px 15px;
        margin-left: 0;
      }

      .footer-nav a {
        margin: 0 8px;
        font-size: 0.75rem;
      }
    }
  </style>
</head>

<body>
  <?php require ADMINPATH . "/layouts/navbar.php"; ?>

  <!-- Page Container with Sidebar and Content -->
  <div class="page-container">
    <?php require ADMINPATH . "/layouts/sidebar.php"; ?>

    <!-- Main content wrapper -->
    <div id="wrapper">
      <!-- Page content will be included here -->