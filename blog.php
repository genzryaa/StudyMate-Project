<?php
require_once 'includes/load.php';
require_user();

// --- HELPER: CHECK IF ADMIN ---
$is_admin = false;
if(isset($_SESSION['author_id'])) {
    $user_id = intval($_SESSION['author_id']);
}

// --- FETCH POSTS ---
$sql = "SELECT * FROM blog_posts ORDER BY created_at DESC";

$posts = [];
$result = $con->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/blog.css">

<div class="main-page-content">
    
    <div class="blog-header">
    <div class="header-text">
        <h1>Blogs</h1>
        <p>Latest updates and study tips.</p>
    </div>
</div>

    <div class="blog-grid">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <span class="material-icons-outlined">article</span>
                <h3>No posts yet</h3>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="blog-card" onclick="location.href='read_post.php?id=<?php echo $post['id']; ?>'">
                    
                    <?php if (!empty($post['image'])): ?>
                        <div class="card-image">
                            <img src="admin/uploads/<?php echo $post['image']; ?>" alt="Blog Image">
                        </div>
                    <?php endif; ?>

                    <div class="card-content">
                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p class="excerpt">
                            <?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?>
                        </p>
                        
                        

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>


<?php include_once('layouts/footer.php'); ?>