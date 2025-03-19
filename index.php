<?php
// Include the data file
require_once('data.php');

// Function to format match date
function formatMatchDate($date_string)
{
    $date = new DateTime($date_string);
    return $date->format('M j, Y - g:i A');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveRW - Live Match Streaming</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <!-- Logo -->
        <div class="logo">
            <img src="./assets/logo_without_bg.png" alt="Live.rw logo" height="40px" width="auto">
        </div>

        <!-- Navigation items -->
        <div class="nav-items">
            <a href="#" class="active">Home</a>
            <a href="#">Live</a>
            <a href="#">Sports</a>
            <a href="#">Schedule</a>
            <a href="#">Teams</a>
            <a href="#">Highlights</a>
            <a href="#">Premium</a>
        </div>

        <!-- Right section - search and auth -->
        <div class="nav-right">
            <!-- Search bar -->
            <div class="search-bar">
                <span class="search-icon">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" placeholder="Search...">
            </div>

            <!-- Auth buttons -->
            <div class="auth-buttons">
                <a href="#" class="sign-in">Sign in</a>
                <a href="#" class="sign-up">Sign up</a>
            </div>

            <!-- Mobile menu toggle -->
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Main content will go here -->
    <div class="main-content">
        <!-- HERO SECTION -->
        <section class="hero-section">
            <div class="slideshow-container">
                <?php foreach ($featured_matches as $index => $match): ?>
                    <!-- Slide <?php echo $index + 1; ?> -->
                    <div class="slide fade">
                        <div class="slide-image" style="background-image: url('<?php echo $match['image']; ?>');">
                            <div class="slide-overlay">
                                <div class="slide-content">
                                    <h1 class="slide-title"><?php echo $match['title']; ?></h1>
                                    <p class="slide-description"><?php echo $match['description']; ?></p>
                                    <div class="slide-buttons">
                                        <a href="watch.php?id=<?php echo $match['id']; ?>"
                                            class="btn btn-primary watch-now">
                                            <i class="fas fa-play"></i> Watch Now
                                        </a>
                                        <a href="#" class="btn btn-secondary subscribe"
                                            data-match-id="<?php echo $match['id']; ?>">
                                            <i class="fas fa-bell"></i> Subscribe
                                        </a>
                                    </div>
                                    <div class="slide-info">
                                        <span class="quality"><?php echo $match['quality']; ?></span>
                                        <span class="category"><?php echo $match['category']; ?></span>
                                        <span class="date"><?php echo formatMatchDate($match['match_date']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Navigation arrows -->
                <a class="prev" onclick="changeSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a class="next" onclick="changeSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </a>

                <!-- Dots/circles -->
                <div class="dots-container">
                    <?php for ($i = 0; $i < count($featured_matches); $i++): ?>
                        <span class="dot" onclick="currentSlide(<?php echo $i + 1; ?>)"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </section>

        <!-- Highlights Section -->
        <section class="highlights-section">
            <div class="section-header">
                <h2>Highlights</h2>
                <a href="highlights.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="highlights-grid">
                <?php
                // Limit to 4 highlights for the homepage
                $homepage_highlights = array_slice($highlights, 0, 4);
                foreach ($homepage_highlights as $highlight):
                    ?>
                    <div class="highlight-card">
                        <div class="highlight-thumbnail"
                            style="background-image: url('<?php echo $highlight['image']; ?>');">
                            <div class="highlight-overlay">
                                <!-- <span class="highlight-duration"><?php echo $highlight['duration']; ?></span> -->
                                <!-- <span class="quality-badge"><?php echo $highlight['quality']; ?></span> -->
                                <a href="watch.php?highlight=<?php echo $highlight['id']; ?>" class="play-button">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                        </div>
                        <div class="highlight-details">
                            <h3 class="highlight-title"><?php echo $highlight['title']; ?></h3>
                            <div class="highlight-info">
                                <span class="league"><?php echo $highlight['league']; ?></span>
                                <span class="date"><?php echo $highlight['date']; ?></span>
                            </div>
                            <!-- <div class="highlight-stats">
                                <div class="views">
                                    <i class="fas fa-eye"></i> <?php echo $highlight['views']; ?>
                                </div>
                                <div class="likes">
                                    <i class="fas fa-heart"></i> <?php echo $highlight['likes']; ?>
                                </div>
                            </div> -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Upcoming Events Section -->
        <section class="upcoming-events-section">
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <a href="schedule.php" class="view-all">Full Schedule <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="events-container">
                <?php
                // Assuming $upcoming_matches is defined in data.php
                // Sort matches by date (closest first)
                usort($upcoming_matches, function ($a, $b) {
                    return strtotime($a['match_date']) - strtotime($b['match_date']);
                });

                // Get the first 4 upcoming matches
                $display_matches = array_slice($upcoming_matches, 0, 4);

                foreach ($display_matches as $match):
                    // Parse the date
                    $match_timestamp = strtotime($match['match_date']);
                    $match_day = date('D', $match_timestamp);
                    $match_date = date('M j', $match_timestamp);
                    $match_time = date('g:i A', $match_timestamp);

                    // Calculate if the match is within the next 24 hours
                    $is_soon = ($match_timestamp - time() < 86400); // 86400 seconds = 24 hours
                
                    // Get price information (with fallback)
                    $price = isset($match['price']) ? $match['price'] : '9.99';
                    ?>
                    <div class="event-card">
                        <div class="event-time">
                            <div class="day"><?php echo $match_day; ?></div>
                            <div class="date"><?php echo $match_date; ?></div>
                            <div class="time"><?php echo $match_time; ?></div>
                            <?php if ($is_soon): ?>
                                <div class="soon-badge">Soon</div>
                            <?php endif; ?>
                        </div>

                        <div class="event-details">
                            <div class="league-name"><?php echo $match['title']; ?></div>
                            <div class="match-teams">
                                <div class="team team-home">
                                    <span class="team-name"><?php echo $match['teams']['home']; ?></span>
                                </div>
                                <div class="vs">VS</div>
                                <div class="team team-away">
                                    <span class="team-name"><?php echo $match['teams']['away']; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="event-actions">
                            <a href="purchase.php?id=<?php echo $match['id']; ?>" class="btn-buy">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Buy Now $<?php echo $price; ?></span>
                            </a>
                            <a href="event.php?id=<?php echo $match['id']; ?>" class="btn-details">
                                Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <div class="logo">
                    <img src="assets/logo_without_bg.png" alt="LiveRW Logo" class="logo-image">
                </div>
                <p class="footer-tagline">Watch sports matches and highlights online in HD quality</p>
            </div>

            <div class="footer-nav">
                <nav class="footer-links">
                    <a href="browse.php">About Us</a>
                    <a href="trending.php">Terms Of Use</a>
                    <a href="top.php">Privacy Policy</a>
                    <a href="matches.php">FAQ</a>
                    <a href="tv-shows.php">Contact Us</a>
                    <a href="https://x.com/ireberolive">X (Twitter)</a>
                    <a href="https://www.instagram.com/ireberolive">Instagram</a>
                </nav>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> LiveRW. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>