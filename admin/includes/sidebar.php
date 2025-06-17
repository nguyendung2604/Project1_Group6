<div class="col-md-3 col-lg-2 px-0">
    <div class="sidebar">
        <nav class="nav flex-column">
            <a href="?tab=dashboard" class="nav-link <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
              <i class="bi bi-speedometer"></i>Dashboard
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-person-fill"></i>My Profile
            </a>
            <a href="?tab=users" class="nav-link <?php echo $active_tab === 'users' ? 'active' : ''; ?>">
                 <i class="bi bi-people-fill"></i>User Management
            </a>
            <a href="?tab=orders" class="nav-link <?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
                 <i class="bi bi-bag-fill"></i>Orders Management
            </a>
            <a href="?tab=products" class="nav-link <?php echo $active_tab === 'products' ? 'active' : ''; ?>">
               <i class="bi bi-box2-fill"></i>Products
            </a>
            <a href="?tab=insert_products" class="nav-link <?php echo $active_tab === 'insert_products' ? 'active' : ''; ?>">
              <i class="bi bi-plus-lg"></i>Insert Products
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-palette-fill"></i>Insert Image & Color
            </a>
            <a href="?tab=categories" class="nav-link <?php echo $active_tab === 'categories' ? 'active' : ''; ?>">
                 <i class="bi bi-bookmark-fill"></i>Category Management
            </a>
            <a href="?tab=brands" class="nav-link <?php echo $active_tab === 'brands' ? 'active' : ''; ?>">
               <i class="bi bi-credit-card-2-back-fill"></i>Brand Management
            </a>
            <a href="../admin/logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i>Logout
            </a>
        </nav>
    </div>
</div>