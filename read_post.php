<?php
require_once 'includes/load.php';
require_user();

if (!isset($_GET['id'])) { redirect('blog.php'); }

$post_id = (int)$_GET['id'];

// SQL: Fetches post details and joins with users table for author info
$sql = "SELECT p.*, u.username, u.profile_image FROM blog_posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.id = '$post_id'";
$result = $con->query($sql);
$post = $result->fetch_assoc();

if (!$post) { redirect('blog.php'); }

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/blog.css">

<style>
    /* Ensure the whole container uses Poppins */
    .article-container {
        font-family: 'Poppins', sans-serif; /* Added Poppins here */
        max-width: 850px;
        margin: 20px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }

    .back-link-top { display: inline-block; margin-bottom: 20px; color: #6c63ff; text-decoration: none; font-weight: 600; }
    .article-header h1 { font-size: 2.2rem; /* Reduced from 2.8rem */ color: #2d3436; margin: 10px 0; }
    .article-date { color: #636e72; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; }
    .article-meta { display: flex; align-items: center; gap: 15px; margin: 20px 0; padding-bottom: 20px; border-bottom: 1px solid #eee; }
    .article-meta img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #6c63ff; }
    .article-hero-image img { width: 100%; max-height: 450px; object-fit: cover; border-radius: 12px; margin-bottom: 30px; }
    
    /* --- UPDATED BODY TEXT STYLE --- */
    .article-body { 
        font-size: 1rem;       /* Changed from 1.15rem to 1rem (Standard size) */
        line-height: 1;      /* Adjusted for better readability */
        color: #4a4a4a;        /* Slightly softer black for easier reading */
        white-space: pre-wrap; 
        font-weight: 400;      /* Normal weight */
    }
</style>

<div class="main-page-content">
    <div class="article-container">
        <a href="blog.php" class="back-link-top">
            <span class="material-icons-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 5px;">arrow_back</span> 
            Back to Blogs
        </a>
        <div class="article-header">
            <span class="article-date"><?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="article-meta">
                <img src="<?php echo URL_ROOT; ?>/uploads/users/<?php echo !empty($post['profile_image']) ? $post['profile_image'] : 'default.png'; ?>" alt="Author">
                <span>By <strong><?php echo htmlspecialchars($post['username'] ?? 'Admin'); ?></strong></span>
            </div>
        </div>

        <?php if (!empty($post['image'])): ?>
            <div class="article-hero-image">
                <img src="admin/uploads/<?php echo $post['image']; ?>" alt="Cover Image">
            </div>
        <?php endif; ?>

        <div class="article-body">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>