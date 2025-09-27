<link rel="stylesheet" href="../assets/css/pages/admin-users.css">
<?php
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Set page title
$page_title = "Categories Management";

// Handle form submission for adding/editing categories
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : NULL;
    
    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        // Check if editing or adding
        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            // Update existing category
            $category_id = $_POST['category_id'];
            $query = "UPDATE categories SET name = :name, slug = :slug, description = :description, parent_id = :parent_id WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->bindParam(':id', $category_id);
            
            if ($stmt->execute()) {
                $success = "Category updated successfully.";
            } else {
                $error = "Error updating category.";
            }
        } else {
            // Add new category
            $query = "INSERT INTO categories (name, slug, description, parent_id) VALUES (:name, :slug, :description, :parent_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':parent_id', $parent_id);
            
            if ($stmt->execute()) {
                $success = "Category added successfully.";
            } else {
                $error = "Error adding category.";
            }
        }
    }
}

// Handle category deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $category_id = $_GET['id'];
    
    // Check if category has products
    $query = "SELECT COUNT(*) as product_count FROM products WHERE category_id = :category_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['product_count'] > 0) {
        $error = "Cannot delete category with products. Please reassign products first.";
    } else {
        // Check if category has subcategories
        $query = "SELECT COUNT(*) as subcategory_count FROM categories WHERE parent_id = :category_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['subcategory_count'] > 0) {
            $error = "Cannot delete category with subcategories. Please delete or reassign subcategories first.";
        } else {
            // Delete category
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $category_id);
            
            if ($stmt->execute()) {
                $success = "Category deleted successfully.";
            } else {
                $error = "Error deleting category.";
            }
        }
    }
}

// Get all categories
$query = "SELECT c1.*, c2.name as parent_name 
          FROM categories c1 
          LEFT JOIN categories c2 ON c1.parent_id = c2.id 
          ORDER BY c1.parent_id, c1.name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get parent categories for dropdown
$query = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$parent_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if editing a category
$editing_category = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $category_id = $_GET['id'];
    $query = "SELECT * FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $category_id);
    $stmt->execute();
    $editing_category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Include admin header
include_once '../includes/admin-header.php';
?>

<div class="admin-users-container">
    <div class="admin-users-header">
        <h1>Categories Management</h1>
        <p>Manage product categories and subcategories</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="dashboard-content">
        <div class="dashboard-column">
            <div class="card">
                <div class="card-header">
                    <h2><?php echo $editing_category ? 'Edit Category' : 'Add New Category'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($editing_category): ?>
                            <input type="hidden" name="category_id" value="<?php echo $editing_category['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name">Category Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo $editing_category ? htmlspecialchars($editing_category['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="parent_id">Parent Category</label>
                            <select id="parent_id" name="parent_id">
                                <option value="">-- No Parent (Main Category) --</option>
                                <?php foreach ($parent_categories as $parent): 
                                    $selected = ($editing_category && $editing_category['parent_id'] == $parent['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $parent['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($parent['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"><?php echo $editing_category ? htmlspecialchars($editing_category['description']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editing_category ? 'Update Category' : 'Add Category'; ?>
                        </button>
                        
                        <?php if ($editing_category): ?>
                            <a href="admin-categories.php" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="dashboard-column">
            <div class="card">
                <div class="card-header">
                    <h2>All Categories</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($categories)): ?>
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Parent Category</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): 
                                    // Count products in this category
                                    $query = "SELECT COUNT(*) as product_count FROM products WHERE category_id = :category_id";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(':category_id', $category['id']);
                                    $stmt->execute();
                                    $product_count = $stmt->fetch(PDO::FETCH_ASSOC)['product_count'];
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                            <?php if ($category['parent_id']): ?>
                                                <div class="text-muted">Subcategory</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '—'; ?></td>
                                        <td><?php echo $product_count; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="admin-categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-edit">Edit</a>
                                                <a href="admin-categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center">No categories found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include_once '../includes/admin-footer.php';
?>