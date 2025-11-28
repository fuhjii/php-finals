<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="dashboard.php">Rental Property Management</a>
        </div>
        <nav class="main-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="tenants.php">Tenants</a>
            <a href="properties.php">Properties</a>
            <a href="payments.php">Payments</a>
            <a href="contact_directory.php">Contacts</a>
        </nav>
        <div class="user-menu">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="btn btn-small">Logout</a>
        </div>
    </div>
</header>
