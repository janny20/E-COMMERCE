0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/about.css">

<main class="about-page">
    <div class="about-header">
        <div class="container">
            <h1>About UniMall</h1>
            <p>Connecting passionate sellers with discerning buyers, all in one vibrant marketplace.</p>
        </div>
    </div>

    <div class="container page-section">
        <div class="about-content">
            <div class="about-text">
                <h2>Our Mission</h2>
                <p>Our mission is to empower small businesses and independent vendors by providing a platform that is simple, powerful, and fair. We believe in creating a diverse marketplace where creativity thrives and customers can discover unique products they love.</p>
                
                <h2>Our Story</h2>
                <p>Founded in 2023, UniMall started with a simple idea: to make online selling accessible to everyone. We saw talented creators and entrepreneurs struggling with the complexities of building their own e-commerce presence. We wanted to build a community where they could focus on what they do best—creating great products—while we handle the technology.</p>
                <p>Today, we are proud to host thousands of vendors from around the world, offering a wide array of products to customers who value quality and originality. Our journey is just beginning, and we are excited to continue growing our community of sellers and shoppers.</p>
            </div>
            <div class="about-image">
                <img src="<?php echo BASE_URL; ?>assets/images/about-us-image.jpg" alt="A vibrant marketplace scene">
            </div>
        </div>
    </div>

    <div class="team-section page-section">
        <div class="container">
            <h2 class="section-title">Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="<?php echo BASE_URL; ?>assets/images/team-member-1.jpg" alt="Team Member Jane Doe">
                    <h3>Jane Doe</h3>
                    <p>Founder & CEO</p>
                </div>
                <div class="team-member">
                    <img src="<?php echo BASE_URL; ?>assets/images/team-member-2.jpg" alt="Team Member John Smith">
                    <h3>John Smith</h3>
                    <p>Head of Technology</p>
                </div>
                <div class="team-member">
                    <img src="<?php echo BASE_URL; ?>assets/images/team-member-3.jpg" alt="Team Member Emily White">
                    <h3>Emily White</h3>
                    <p>Vendor Relations</p>
                </div>
                <div class="team-member">
                    <img src="<?php echo BASE_URL; ?>assets/images/team-member-4.jpg" alt="Team Member Michael Brown">
                    <h3>Michael Brown</h3>
                    <p>Marketing Director</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once '../includes/footer.php';
?>