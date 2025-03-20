<?php
// Include the data file
require_once('data.php');

// Get the video ID from URL parameter
$videoId = isset($_GET['id']) ? intval($_GET['id']) : null;
$isHighlight = isset($_GET['highlight']) ? true : false;

// Find the requested video
$videoData = null;

if ($isHighlight && $videoId) {
    // Find in highlights
    foreach ($highlights as $highlight) {
        if ($highlight['id'] == $videoId) {
            $videoData = $highlight;
            break;
        }
    }
} elseif ($videoId) {
    // Find in featured matches
    foreach ($featured_matches as $match) {
        if ($match['id'] == $videoId) {
            $videoData = $match;
            break;
        }
    }

    // If not found in featured, check live matches
    if (!$videoData) {
        foreach ($live_matches as $match) {
            if ($match['id'] == $videoId) {
                $videoData = $match;
                break;
            }
        }
    }
}

// If video not found, redirect to homepage
if (!$videoData) {
    header('Location: index.php');
    exit;
}

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
    <title><?php echo $videoData['title']; ?> - LiveRW</title>
    <link rel="icon" type="image/png" href="assets/favicon_2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="player.css">
</head>

<body class="player-page">
    <!-- Navbar -->
    <nav class="navbar">
        <!-- Logo -->
        <div class="logo">
            <a href="index.php">
                <img src="./assets/logo_without_bg.png" alt="Live.rw logo" height="40px" width="auto">
            </a>
        </div>

        <!-- Navigation items -->
        <div class="nav-items">
            <a href="index.php">Home</a>
            <a href="#" class="active">Live</a>
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

    <!-- Player content -->
    <div class="player-container">
        <div class="video-section">
            <!-- Video title and back button -->
            <div class="video-header">
                <a href="index.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h1 class="video-title"><?php echo $videoData['title']; ?></h1>
                <?php if (isset($videoData['quality'])): ?>
                    <span class="quality-tag"><?php echo $videoData['quality']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Video player -->
            <div class="video-wrapper">
                <video id="videoPlayer" poster="<?php echo $videoData['image']; ?>" controls>
                    <source src="assets/video.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>

                <!-- Custom video controls will be positioned over the video -->
                <div class="custom-controls">
                    <div class="control-bar">
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                                <div class="progress-marker"></div>
                            </div>
                            <div class="time-display">
                                <span class="current-time">00:00</span>
                                <span class="duration">00:00</span>
                            </div>
                        </div>

                        <div class="control-buttons">
                            <button class="play-btn" aria-label="Play">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="pause-btn" aria-label="Pause" style="display: none;">
                                <i class="fas fa-pause"></i>
                            </button>
                            <button class="volume-btn" aria-label="Volume">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <div class="volume-slider">
                                <input type="range" min="0" max="1" step="0.05" value="1">
                            </div>

                            <div class="spacer"></div>

                            <button class="quality-btn" aria-label="Quality">
                                <i class="fas fa-cog"></i>
                                <span>HD</span>
                            </button>
                            <div class="quality-menu">
                                <div class="quality-option selected" data-quality="hd">HD</div>
                                <div class="quality-option" data-quality="sd">SD</div>
                                <div class="quality-option" data-quality="auto">Auto</div>
                            </div>
                            <button class="fullscreen-btn" aria-label="Fullscreen">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Big play button in center of video -->
                    <div class="big-play-btn">
                        <i class="fas fa-play"></i>
                    </div>
                </div>

                <?php if (isset($videoData['category']) && $videoData['category'] === 'Live'): ?>
                    <div class="live-badge">
                        <span class="pulse-dot"></span> LIVE
                    </div>
                <?php endif; ?>
            </div>

            <!-- Video details -->
            <div class="video-details">
                <div class="match-info">
                    <?php if (isset($videoData['teams'])): ?>
                        <div class="teams-container">
                            <div class="team home-team">
                                <span class="team-name"><?php echo $videoData['teams']['home']; ?></span>
                            </div>
                            <div class="versus">VS</div>
                            <div class="team away-team">
                                <span class="team-name"><?php echo $videoData['teams']['away']; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($videoData['match_date'])): ?>
                        <div class="match-date">
                            <i class="far fa-calendar-alt"></i>
                            <?php echo formatMatchDate($videoData['match_date']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($videoData['league']) && isset($videoData['stage'])): ?>
                        <div class="match-stage">
                            <span class="league"><?php echo $videoData['league']; ?></span>
                            <span class="separator">•</span>
                            <span class="stage"><?php echo $videoData['stage']; ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="video-actions">
                    <button class="action-btn share-btn">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <button class="action-btn favorite-btn">
                        <i class="far fa-heart"></i> Favorite
                    </button>
                    <button class="action-btn report-btn">
                        <i class="fas fa-flag"></i> Report
                    </button>
                </div>
            </div>

            <?php if (isset($videoData['description'])): ?>
                <div class="video-description">
                    <h3>Description</h3>
                    <p><?php echo $videoData['description']; ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Related videos sidebar -->
        <div class="related-videos">
            <h3>You might also like</h3>
            <div class="related-list">
                <?php
                // Get 5 random videos from highlights for related content
                $related = array_slice($highlights, 0, 5);
                foreach ($related as $video):
                    ?>
                    <a href="player.php?highlight=<?php echo $video['id']; ?>" class="related-item">
                        <div class="related-thumbnail" style="background-image: url('<?php echo $video['image']; ?>');">
                            <span class="duration"><?php echo $video['duration']; ?></span>
                        </div>
                        <div class="related-info">
                            <h4><?php echo $video['title']; ?></h4>
                            <div class="related-meta">
                                <span class="related-league"><?php echo $video['league']; ?></span>
                                <span class="related-date"><?php echo $video['date']; ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
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
                    <a href="https://x.com/livedotrw">X (Twitter)</a>
                    <a href="https://www.instagram.com/livedotrw">Instagram</a>
                </nav>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> LiveRW. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script src="player.js"></script>
</body>

</html>