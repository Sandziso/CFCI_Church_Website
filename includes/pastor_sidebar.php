<?php
// This file contains the sidebar HTML and JavaScript for the Pastor Dashboard.
// It is designed to be included in other PHP files.
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="church-logo">
            <i class="fas fa-church"></i>
            <div class="church-logo-text">CF<span>CI</span></div>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="menu-item active" data-target="dashboard">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </div>
        <div class="menu-item" data-target="members">
            <i class="fas fa-users"></i>
            <span>Members</span>
        </div>
        <div class="menu-item" data-target="events">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </div>
        <div class="menu-item" data-target="sermons">
            <i class="fas fa-file-alt"></i>
            <span>Sermons</span>
        </div>
        <div class="menu-item" data-target="donations">
            <i class="fas fa-donate"></i>
            <span>Donations</span>
        </div>
        <div class="menu-item" data-target="prayer-requests">
            <i class="fas fa-pray"></i>
            <span>Prayer Requests</span>
        </div>
        <div class="menu-item" data-target="reports">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </div>
        <div class="menu-item" data-target="settings">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </div>
        <div class="menu-item" data-target="logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('.sidebar .menu-item');

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove 'active' class from all menu items
                menuItems.forEach(i => i.classList.remove('active'));
                // Add 'active' class to the clicked item
                this.classList.add('active');

                // In a real application, you would load content dynamically here
                // based on the data-target attribute, e.g.:
                const targetPage = this.getAttribute('data-target');
                console.log(`Navigating to: ${targetPage}`);
                // Example: window.location.href = `pastor/${targetPage}.php`;
                // Or use AJAX to load content into the main-content area
            });
        });

        // Set initial active menu item based on current URL or a default
        // This is a basic example; for more complex routing, you'd use a router.
        const path = window.location.pathname;
        if (path.includes('pastor/dashboard.php')) {
            document.querySelector('.menu-item[data-target="dashboard"]').classList.add('active');
        }
        // Add more conditions for other pages as they are created
    });
</script>
