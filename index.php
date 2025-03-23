<?php

session_start();

// Include the data file
require_once('functions.php');
require_once('config/db.php');
function formatMatchDate($date_string)
{
    $date = new DateTime($date_string);
    return $date->format('M j, Y - g:i A');
}

$featured_matches = getFeaturedMatches($db_mysql);
$highlights = getHighlights($db_mysql);
$upcoming_matches = getUpcomingMatches($db_mysql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveRW - Live Match Streaming</title>
    <link rel="icon" type="image/png" href="assets/favicon_2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

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
                                        <a href="player.php?id=<?php echo $match['id']; ?>"
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
                <?php foreach ($highlights as $highlight): ?>
                    <div class="highlight-card">
                        <div class="highlight-thumbnail"
                            style="background-image: url('<?php echo $highlight['image']; ?>');">
                            <div class="highlight-overlay">
                                <span class="highlight-duration"><?php echo $highlight['duration']; ?></span>
                                <a href="<?php echo $highlight['video_url']; ?>" target="_blank" class="play-button">
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
                    $price = isset($match['price']) ? $match['price'] : '1000';
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
                                <span>Buy Now <?php echo $price; ?> Frw</span>
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

    <!-- Banner ads before the footer -->

    <?php
    $ad_position = 'banner';
    include('./ads.php');
    ?>

    <!-- Footer -->
    <?php require_once 'views/footer.php'; ?>

    <script src="script.js"></script>
</body>

</html>