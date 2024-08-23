<?php
// Redis connection
$redis = new Redis();
$redis->connect('redis');

// Function to get categories and subcategories recursively
function getCategories($redis, $parentCategory = '') {
    $categories = $redis->zRange("sub_categories:$parentCategory", 0, -1);
    $result = [];
    foreach ($categories as $category) {
        $result[$category] = getCategories($redis, $category); // Recursive call for subcategories
    }
    return $result;
}

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $parentCategory = $_POST['parent_category'] ?? '';
        $newCategory = $_POST['new_category'] ?? '';
        if ($newCategory !== '') {
            $redis->zAdd("sub_categories:$parentCategory", time(), $newCategory);
            // Removed the echo line that outputs "Category added!"
        }
    } 
}

// Retrieve all categories and subcategories
$categories = getCategories($redis);

// Function to render breadcrumbs
function renderBreadcrumbs($path) {
    $breadcrumbs = ['<a href="index.php">LUNA STORE</a>']; // Start with LUNA STORE
    $crumbs = explode('/', $path);
    $url = '';
    foreach ($crumbs as $index => $crumb) {
        if ($crumb !== '') {
            $url .= $crumb . '/';
            $breadcrumbs[] = '<a href="?category=' . urlencode($url) . '">' . htmlspecialchars($crumb) . '</a>';
        }
    }
    echo implode(' &gt; ', $breadcrumbs);
}

$currentCategoryPath = $_GET['category'] ?? '';
$currentCategoryPath = rtrim($currentCategoryPath, '/');
$currentCategories = explode('/', $currentCategoryPath);

// Get subcategories of the current category
$currentSubCategories = getCategories($redis, end($currentCategories));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Category</title>
    <style>
    body {
        background-image: url('images/moon.jpg'); /* Arka plan resmi yolu */
        color: #D3D3D3; /* Açık gri yazı rengi */
        font-family: 'Times New Roman', Times, serif;
        display: flex;
        margin: 0;
    }
    .sidebar {
        width: 200px;
        background-color: #2F4F4F; /* Koyu gri arka plan rengi */
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }
    .sidebar a {
        color: #FFFFFF; /* Kategoriler için beyaz yazı rengi */
        text-decoration: none;
    }
    .sidebar a:hover {
        text-decoration: underline;
    }
    .content {
        flex: 1;
        padding: 20px;
    }
    .store-button {
        margin-bottom: 20px;
    }
    
    h1, h2 {
        color: #D3D3D3; /* Açık gri başlık rengi */
    }
    select, input[type="text"], input[type="submit"] {
        margin-top: 10px;
    }
    nav p a {
        color: #D3D3D3; /* Açık gri link rengi */
        text-decoration: none;
    }
    nav p a:hover {
        text-decoration: underline;
    }
</style>


</head>
<body>

    <div class="sidebar">
        <h2>Categories</h2>
        <ul>
            <?php function renderCategorySidebar($categories, $parentPath = '') { ?>
                <?php foreach ($categories as $category => $subCategories): ?>
                    <li>
                        <a href="?category=<?php echo urlencode($parentPath . $category . '/'); ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </a>
                        <?php if (!empty($subCategories)): ?>
                            <ul>
                                <?php renderCategorySidebar($subCategories, $parentPath . $category . '/'); ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php } ?>
            <?php renderCategorySidebar($categories); ?>
        </ul>
    </div>

    <div class="content">
        <h1>Manage Category</h1>

        <!-- Breadcrumb -->
        <nav>
            <p>
            <?php 
                // Render breadcrumbs with "LUNA STORE" as the first item
                renderBreadcrumbs($currentCategoryPath);
            ?>
            </p>
        </nav>
        
        <h2>Add Category</h2>
        <form method="post">
            <select name="parent_category">
                <option value="">-- Select Parent Category --</option>
                <?php function renderCategoryOptions($categories, $prefix = '') { ?>
                    <?php foreach ($categories as $category => $subCategories): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>">
                            <?php echo $prefix . htmlspecialchars($category); ?>
                        </option>
                        <?php renderCategoryOptions($subCategories, $prefix . '- '); ?>
                    <?php endforeach; ?>
                <?php } ?>
                <?php renderCategoryOptions($categories); ?>
            </select>
            <input type="text" name="new_category" placeholder="New Category" required>
            <input type="submit" name="add_category" value="Add Category">
        </form>

        <ul>
            <?php function renderCategoryList($categories, $parentPath = '') { ?>
                <?php foreach ($categories as $category => $subCategories): ?>
                    <li>
                        <a href="?category=<?php echo urlencode($parentPath . $category . '/'); ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </a>
                        <?php if (!empty($subCategories)): ?>
                            <ul>
                                <?php renderCategoryList($subCategories, $parentPath . $category . '/'); ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php } ?>
            <?php renderCategoryList($currentSubCategories, $currentCategoryPath . '/'); ?>
        </ul>
    </div>

</body>
</html>
