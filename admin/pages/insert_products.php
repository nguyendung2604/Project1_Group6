<h2 class="mb-4">Insert Products</h2>

<div class="form-container">
    <h5 class="mb-3">Add new product</h5>
    <form method="POST" class="row g-3">

        <div class="col-md-6">
            <label class="form-label">ProductName</label>
            <input type="text" name="product_name" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Price</label>
            <input type="number" name="price" class="form-control" step="0.01" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Image_url</label>
            <input type="text" name="image_url" class="form-control">
        </div>

        <!-- lấy trong categories -->
        <div class="col-md-6">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select category</option>
                <?php
                $sql = "SELECT * FROM categories ORDER BY name";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($categories as $cat):
                ?>
                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
       <!-- lấy trong brands -->
        <div class="col-md-6">
            <label class="form-label">Brand</label>
            <select name="brand_id" class="form-select" required>
                <option value="">Select brand</option>
                <?php
                $sql = "SELECT * FROM brands ORDER BY name";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($brands as $brand):
                ?>
                <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Enter product description..."></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Screen Size</label>
            <input type="text" name="screen_size" class="form-control" placeholder="e.g. 6.5 inch">
        </div>
        <div class="col-md-6">
            <label class="form-label">RAM</label>
            <input type="text" name="ram" class="form-control" placeholder="e.g. 8GB">
        </div>
        <div class="col-md-6">
            <label class="form-label">Storage</label>
            <input type="text" name="storage" class="form-control" placeholder="e.g. 128GB">
        </div>
        <div class="col-md-6">
            <label class="form-label">Camera</label>
            <input type="text" name="camera" class="form-control" placeholder="e.g. 48MP + 12MP">
        </div>
        <div class="col-md-6">
            <label class="form-label">Battery</label>
            <input type="text" name="battery" class="form-control" placeholder="e.g. 5000 mAh">
        </div>
        <div class="col-md-6">
            <label class="form-label">OS</label>
            <input type="text" name="os" class="form-control" placeholder="e.g. Android 14">
        </div>
        <div class="col-md-6">
            <label class="form-label">CPU</label>
            <input type="text" name="cpu" class="form-control" placeholder="e.g. Snapdragon 8 Gen 3">
        </div>

        <div class="col-12">
            <button type="submit" name="add_product" class="btn btn-success-custom btn-custom">
                <i class="fas fa-plus me-2"></i>Add product
            </button>
        </div>
    </form>
</div>