<header class="client-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="client_dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <!-- Rest of your header content -->
        </div>
    </nav>
</header>

<style>
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.1);
    transition: var(--transition);
}

.back-link:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(-5px);
    color: var(--text-light);
}

.back-link i {
    font-size: 1.1rem;
}
</style>