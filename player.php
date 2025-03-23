<?php
// Include the data file
session_start();
require_once('config/db.php');
require_once('functions.php');

// Get the video ID from URL parameter
$videoId = $eventId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Find the requested video
$videoData = null;

if ($videoId) {
    $videoData = getEventById($db_mysql, $videoId);
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

// Get related content
$relatedContent = getRelatedContent($db_mysql, $videoId);

// Check if user is allowed to watch this event
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;
$isPaidEvent = $videoData['is_paid'];

if ($isPaidEvent == true) {
    if (!$isLoggedIn) {
        header("Location: login.php");
        exit;
    }

    // Check if the user has paid for the event
    $paymentCheckQuery = $db_mysql->prepare("SELECT * FROM user_events WHERE user_id = ? AND event_id = ? AND payment_status = 'completed'");
    $paymentCheckQuery->bind_param("ii", $userId, $eventId);
    $paymentCheckQuery->execute();
    $paymentResult = $paymentCheckQuery->get_result();

    if ($paymentResult->num_rows === 0) {
        header("Location: payment.php?event_id=$eventId"); // Redirect to payment
        exit;
    }
}

// Get stream URL from database or use a default if not available
$streamUrl = !empty($videoData['stream_url']) ? $videoData['stream_url'] : '';
// Encrypt the stream URL for FWDEVPlayer if needed
// $encryptedStreamUrl = !empty($streamUrl) ? $streamUrl : 'https://customer-yhqshugcyepebnmz.cloudflarestream.com/a194a815c958da8e1d6fa113be689fc1/manifest/video.m3u8';

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
    <style>
        .loading-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.2);
            border-top: 5px solid #1C6EA4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .video-wrapper {
            position: relative;
            aspect-ratio: 16/9;
            background-color: #000;
            overflow: hidden;
        }

        .live-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #e53170;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            z-index: 10;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #fff;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 1.5s infinite ease-in-out;
        }

        @keyframes pulse {
            0% {
                opacity: 0.4;
                transform: scale(0.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }

            100% {
                opacity: 0.4;
                transform: scale(0.8);
            }
        }
    </style>
</head>

<body class="player-page">
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

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
                <!-- Loading indicator -->
                <div class="loading-indicator"></div>

                <!-- <?php if (isset($videoData['category']) && $videoData['category'] === 'Live'): ?>
                    <div class="live-badge">
                        <span class="pulse-dot"></span> LIVE
                    </div>
                <?php endif; ?> -->

                <!-- FWDEVPlayer -->
                <script type="text/javascript" src="https://live.rw/tt/site_assets/player/java/FWDEVPlayer.js"></script>
                <div id="viavi_player" style="margin:auto;"></div>

                <!-- Setup EVP -->
                <script type="text/javascript">
                    FWDEVPUtils.onReady(function () {
                        FWDEVPlayer.videoStartBehaviour = "pause";

                        new FWDEVPlayer({
                            //main settings
                            instanceName: "player1",
                            parentId: "viavi_player",
                            mainFolderPath: "https://live.rw/tt/site_assets/player/content",
                            initializeOnlyWhenVisible: "no",
                            skinPath: "modern_skin_dark",
                            displayType: "responsive",
                            autoScale: "yes",
                            fillEntireVideoScreen: "no",
                            playsinline: "yes",
                            useWithoutVideoScreen: "no",
                            openDownloadLinkOnMobile: "no",
                            googleAnalyticsMeasurementId: "",
                            useVectorIcons: "no",
                            useResumeOnPlay: "yes",
                            goFullScreenOnButtonPlay: "no",
                            useHEXColorsForSkin: "no",
                            normalHEXButtonsColor: "#FF0000",
                            privateVideoPassword: "428c841430ea18a70f7b06525d4b748a",
                            startAtVideoSource: 0,
                            startAtTime: "",
                            stopAtTime: "",
                            videoSource: [
                                { source: "<?php echo $streamUrl; ?>", label: "" }
                            ],
                            posterPath: "<?php echo $videoData['image']; ?>",
                            showErrorInfo: "yes",
                            fillEntireScreenWithPoster: "no",
                            disableDoubleClickFullscreen: "no",
                            useChromeless: "no",
                            showPreloader: "yes",
                            preloaderColors: ["#999999", "#FFFFFF"],
                            addKeyboardSupport: "yes",
                            autoPlay: "yes",
                            autoPlayText: "Click to Unmute",
                            loop: "no",
                            scrubAtTimeAtFirstPlay: "00:00:00",
                            maxWidth: 1325,
                            maxHeight: 535,
                            volume: .8,
                            greenScreenTolerance: 200,
                            backgroundColor: "#000000",
                            posterBackgroundColor: "#000000",
                            //lightbox settings
                            closeLightBoxWhenPlayComplete: "no",
                            lightBoxBackgroundOpacity: .6,
                            lightBoxBackgroundColor: "#000000",
                            //logo settings
                            logoSource: "../assets/logo_without_bg.png",
                            showLogo: "yes",
                            hideLogoWithController: "yes",
                            logoPosition: "topRight",
                            logoLink: "#",
                            logoMargins: 5,
                            //controller settings
                            showController: "yes",
                            showDefaultControllerForVimeo: "yes",
                            showScrubberWhenControllerIsHidden: "yes",
                            showControllerWhenVideoIsStopped: "yes",
                            showVolumeScrubber: "yes",
                            showVolumeButton: "yes",
                            showTime: "yes",
                            showAudioTracksButton: "yes",
                            showRewindButton: "yes",
                            showQualityButton: "yes",
                            showShareButton: "no",
                            showEmbedButton: "no",
                            showDownloadButton: "no",
                            showMainScrubberToolTipLabel: "yes",
                            showChromecastButton: "yes",
                            how360DegreeVideoVrButton: "no",
                            showFullScreenButton: "yes",
                            repeatBackground: "no",
                            controllerHeight: 43,
                            controllerHideDelay: 3,
                            startSpaceBetweenButtons: 11,
                            spaceBetweenButtons: 11,
                            mainScrubberOffestTop: 15,
                            scrubbersOffsetWidth: 2,
                            timeOffsetLeftWidth: 1,
                            timeOffsetRightWidth: 2,
                            volumeScrubberWidth: 80,
                            volumeScrubberOffsetRightWidth: 0,
                            timeColor: "#bdbdbd",
                            showYoutubeRelAndInfo: "no",
                            youtubeQualityButtonNormalColor: "#888888",
                            youtubeQualityButtonSelectedColor: "#FFFFFF",
                            scrubbersToolTipLabelBackgroundColor: "#FFFFFF",
                            scrubbersToolTipLabelFontColor: "#5a5a5a",
                            //redirect at video end
                            redirectURL: "",
                            redirectTarget: "_blank",
                            //cuepoints
                            executeCuepointsOnlyOnce: "no",
                            cuepoints: [],
                            //annotations
                            annotiationsListId: "none",
                            showAnnotationsPositionTool: "no",
                            //subtitles
                            showSubtitleButton: "yes",
                            subtitlesOffLabel: "Subtitle off",
                            startAtSubtitle: 1,
                            subtitlesSource: [],
                            //audio visualizer
                            audioVisualizerLinesColor: "#0099FF",
                            audioVisualizerCircleColor: "#FFFFFF",
                            //advertisement on pause window
                            aopwTitle: "Advertisement",
                            aopwSource: "",
                            aopwWidth: 400,
                            aopwHeight: 240,
                            aopwBorderSize: 6,
                            aopwTitleColor: "#FFFFFF",
                            //playback rate / speed
                            showPlaybackRateButton: "yes",
                            defaultPlaybackRate: "1", //0.25, 0.5, 1, 1.25, 1.5, 2
                            //sticky on scroll
                            stickyOnScroll: "yes",
                            stickyOnScrollShowOpener: "yes",
                            stickyOnScrollWidth: "700",
                            stickyOnScrollHeight: "394",
                            //sticky display settings
                            showOpener: "yes",
                            showOpenerPlayPauseButton: "yes",
                            verticalPosition: "bottom",
                            horizontalPosition: "center",
                            showPlayerByDefault: "yes",
                            animatePlayer: "yes",
                            openerAlignment: "right",
                            mainBackgroundImagePath: "https://live.rw/site_assets/player/content/minimal_skin_dark/main-background.png",
                            openerEqulizerOffsetTop: -1,
                            openerEqulizerOffsetLeft: 3,
                            offsetX: 0,
                            offsetY: 0,
                            //embed window
                            embedWindowCloseButtonMargins: 15,
                            borderColor: "#333333",
                            mainLabelsColor: "#FFFFFF",
                            secondaryLabelsColor: "#a1a1a1",
                            shareAndEmbedTextColor: "#5a5a5a",
                            inputBackgroundColor: "#000000",
                            inputColor: "#FFFFFF",
                            //a to b loop
                            useAToB: "no",
                            atbTimeBackgroundColor: "transparent",
                            atbTimeTextColorNormal: "#FFFFFF",
                            atbTimeTextColorSelected: "#FF0000",
                            atbButtonTextNormalColor: "#888888",
                            atbButtonTextSelectedColor: "#FFFFFF",
                            atbButtonBackgroundNormalColor: "#FFFFFF",
                            atbButtonBackgroundSelectedColor: "#000000",
                            //thumbnails preview
                            thumbnailsPreview: "auto",
                            thumbnailsPreviewWidth: 196,
                            thumbnailsPreviewHeight: 110,
                            thumbnailsPreviewBackgroundColor: "#000000",
                            thumbnailsPreviewBorderColor: "#666",
                            thumbnailsPreviewLabelBackgroundColor: "#666",
                            thumbnailsPreviewLabelFontColor: "#FFF",
                            // context menu
                            contextMenuType: 'default',
                            showScriptDeveloper: "no",
                            contextMenuBackgroundColor: "#1b1b1b",
                            contextMenuBorderColor: "#1b1b1b",
                            contextMenuSpacerColor: "#333",
                            contextMenuItemNormalColor: "#bdbdbd",
                            contextMenuItemSelectedColor: "#FFFFFF",
                            contextMenuItemDisabledColor: "#333",
                            useYoutube: "yes",
                            useVimeo: "yes"
                        });

                        // Hide loading indicator when player is ready
                        document.querySelector('.loading-indicator').style.display = 'none';
                    });
                </script>
            </div>

            <!-- Video details -->
            <div class="video-details">
                <div class="match-info">
                    <?php if (isset($videoData['teams'])): ?>
                        <div class="teams-container">
                            <div class="team home-team">
                                <span class="team-name"><?php echo $videoData['teams']['home']; ?></span>
                                <?php if (isset($videoData['teams']['home_score'])): ?>
                                    <span class="team-score"><?php echo $videoData['teams']['home_score']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="versus">VS</div>
                            <div class="team away-team">
                                <span class="team-name"><?php echo $videoData['teams']['away']; ?></span>
                                <?php if (isset($videoData['teams']['away_score'])): ?>
                                    <span class="team-score"><?php echo $videoData['teams']['away_score']; ?></span>
                                <?php endif; ?>
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
                    <button class="action-btn share-btn" id="shareBtn">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <button class="action-btn favorite-btn" id="favoriteBtn" data-id="<?php echo $videoData['id']; ?>"
                        data-type="event">
                        <i class="far fa-heart"></i> Favorite
                    </button>
                    <button class="action-btn report-btn" id="reportBtn">
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
                <?php foreach ($relatedContent as $content): ?>
                    <a href="player.php?<?php echo $content['content_type'] === 'highlight' ? 'highlight=' : 'id='; ?><?php echo $content['id']; ?>"
                        class="related-item">
                        <div class="related-thumbnail" style="background-image: url('<?php echo $content['image']; ?>');">
                            <?php if (isset($content['duration'])): ?>
                                <span class="duration"><?php echo $content['duration']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="related-info">
                            <h4><?php echo $content['title']; ?></h4>
                            <div class="related-meta">
                                <span class="related-league"><?php echo $content['league'] ?? ''; ?></span>
                                <span class="related-date"><?php echo $content['date']; ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <!-- Adds -->
            <?php
            $ad_position = 'sidebar';
            include('./ads.php');
            ?>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'views/footer.php'; ?>

    <script src="script.js"></script>
    <script>
        // Simple sharing functionality
        document.getElementById('shareBtn').addEventListener('click', function () {
            navigator.clipboard.writeText(window.location.href)
                .then(() => {
                    alert('Link copied to clipboard');
                })
                .catch(() => {
                    alert('Failed to copy link');
                });
        });

        // Simple favorite functionality
        document.getElementById('favoriteBtn').addEventListener('click', function () {
            const icon = this.querySelector('i');

            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                this.classList.add('active');
                alert('Added to favorites');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                this.classList.remove('active');
                alert('Removed from favorites');
            }
        });

        // Simple report functionality
        document.getElementById('reportBtn').addEventListener('click', function () {
            alert('Thank you for reporting this content. Our team will review it.');
        });
    </script>
</body>

</html>