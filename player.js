// player.js - Video player functionality
document.addEventListener('DOMContentLoaded', function () {
    const videoPlayer = document.getElementById('videoPlayer');
    const videoWrapper = document.querySelector('.video-wrapper');
    const customControls = document.querySelector('.custom-controls');
    const progressBar = document.querySelector('.progress-bar');
    const progressFill = document.querySelector('.progress-fill');
    const progressMarker = document.querySelector('.progress-marker');
    const currentTimeEl = document.querySelector('.current-time');
    const durationEl = document.querySelector('.duration');
    const playBtn = document.querySelector('.play-btn');
    const pauseBtn = document.querySelector('.pause-btn');
    const bigPlayBtn = document.querySelector('.big-play-btn');
    const volumeBtn = document.querySelector('.volume-btn');
    const volumeSlider = document.querySelector('.volume-slider input');
    const fullscreenBtn = document.querySelector('.fullscreen-btn');
    const qualityOptions = document.querySelectorAll('.quality-option');
    const favoriteBtn = document.querySelector('.favorite-btn');
    const shareBtn = document.querySelector('.share-btn');

    // Hide native video controls
    videoPlayer.controls = false;

    // Initialize player state
    let playerState = {
        playing: false,
        volume: 1,
        muted: false,
        progress: 0,
        duration: 0,
        fullscreen: false,
        controlsVisible: false,
        controlsTimeout: null,
        quality: 'hd'
    };

    // Format time in seconds to MM:SS format
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    // Update progress bar
    function updateProgress() {
        if (!videoPlayer.duration) return;

        const percentage = (videoPlayer.currentTime / videoPlayer.duration) * 100;
        progressFill.style.width = `${percentage}%`;
        currentTimeEl.textContent = formatTime(videoPlayer.currentTime);

        // Update progress marker position
        progressMarker.style.left = `${percentage}%`;
    }

    // Set video time based on progress bar click
    function setProgress(e) {
        const progressBarRect = progressBar.getBoundingClientRect();
        const clickPosition = e.clientX - progressBarRect.left;
        const percentage = clickPosition / progressBarRect.width;

        videoPlayer.currentTime = percentage * videoPlayer.duration;
    }

    // Toggle play/pause
    function togglePlay() {
        if (videoPlayer.paused) {
            videoPlayer.play();
            playerState.playing = true;
            videoWrapper.classList.add('playing');
            playBtn.style.display = 'none';
            pauseBtn.style.display = 'block';
        } else {
            videoPlayer.pause();
            playerState.playing = false;
            videoWrapper.classList.remove('playing');
            playBtn.style.display = 'block';
            pauseBtn.style.display = 'none';
        }
    }

    // Toggle volume mute
    function toggleMute() {
        if (videoPlayer.muted) {
            videoPlayer.muted = false;
            playerState.muted = false;
            volumeBtn.innerHTML = playerState.volume > 0.5 ?
                '<i class="fas fa-volume-up"></i>' :
                '<i class="fas fa-volume-down"></i>';
            volumeSlider.value = playerState.volume;
        } else {
            videoPlayer.muted = true;
            playerState.muted = true;
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            volumeSlider.value = 0;
        }
    }

    // Set volume based on slider
    function setVolume() {
        playerState.volume = volumeSlider.value;
        videoPlayer.volume = volumeSlider.value;
        videoPlayer.muted = (volumeSlider.value === 0);
        playerState.muted = (volumeSlider.value === 0);

        // Update volume icon
        if (volumeSlider.value === 0) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
        } else if (volumeSlider.value < 0.5) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-down"></i>';
        } else {
            volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
        }
    }

    // Toggle fullscreen
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            if (videoWrapper.requestFullscreen) {
                videoWrapper.requestFullscreen();
            } else if (videoWrapper.webkitRequestFullscreen) {
                videoWrapper.webkitRequestFullscreen();
            } else if (videoWrapper.msRequestFullscreen) {
                videoWrapper.msRequestFullscreen();
            }
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
            playerState.fullscreen = true;
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
            playerState.fullscreen = false;
        }
    }

    // Set video quality
    function setQuality(quality) {
        // In a real implementation, this would switch video sources
        // For this demo, we'll just update the UI
        playerState.quality = quality;

        // Update quality button text
        document.querySelector('.quality-btn span').textContent = quality.toUpperCase();

        // Update selected quality option
        qualityOptions.forEach(option => {
            if (option.dataset.quality === quality) {
                option.classList.add('selected');
            } else {
                option.classList.remove('selected');
            }
        });

        // Show notification
        showNotification(`Quality changed to ${quality.toUpperCase()}`);
    }

    // Show a notification on the video
    function showNotification(message) {
        // Create notification element if it doesn't exist
        let notification = document.querySelector('.video-notification');

        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'video-notification';
            videoWrapper.appendChild(notification);

            // Add styles for notification
            const style = document.createElement('style');
            style.textContent = `
                .video-notification {
                    position: absolute;
                    top: 60px;
                    right: 20px;
                    background-color: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 8px 15px;
                    border-radius: 5px;
                    font-size: 14px;
                    z-index: 20;
                    opacity: 0;
                    transform: translateY(-10px);
                    transition: opacity 0.3s, transform 0.3s;
                }
                .video-notification.show {
                    opacity: 1;
                    transform: translateY(0);
                }
            `;
            document.head.appendChild(style);
        }

        // Set message and show notification
        notification.textContent = message;
        notification.classList.add('show');

        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    // Toggle favorite
    function toggleFavorite() {
        const icon = favoriteBtn.querySelector('i');

        if (icon.classList.contains('far')) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            favoriteBtn.classList.add('active');
            showNotification('Added to favorites');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            favoriteBtn.classList.remove('active');
            showNotification('Removed from favorites');
        }
    }

    // Share functionality
    function shareVideo() {
        // In a real implementation, this would open a share dialog
        // For this demo, we'll just show a notification
        navigator.clipboard.writeText(window.location.href)
            .then(() => {
                showNotification('Link copied to clipboard');
            })
            .catch(err => {
                showNotification('Failed to copy link');
                console.error('Could not copy text: ', err);
            });
    }

    // Show/hide controls based on mouse movement
    function handleControlsVisibility() {
        customControls.classList.add('active');

        // Clear existing timeout
        clearTimeout(playerState.controlsTimeout);

        // Set new timeout to hide controls
        if (playerState.playing) {
            playerState.controlsTimeout = setTimeout(() => {
                customControls.classList.remove('active');
            }, 3000);
        }
    }

    // Event Listeners

    // Video metadata loaded
    videoPlayer.addEventListener('loadedmetadata', function () {
        playerState.duration = videoPlayer.duration;
        durationEl.textContent = formatTime(videoPlayer.duration);
    });

    // Time update
    videoPlayer.addEventListener('timeupdate', updateProgress);

    // Video ended
    videoPlayer.addEventListener('ended', function () {
        playerState.playing = false;
        videoWrapper.classList.remove('playing');
        playBtn.style.display = 'block';
        pauseBtn.style.display = 'none';
    });

    // Progress bar click
    progressBar.addEventListener('click', setProgress);

    // Progress bar hover
    progressBar.addEventListener('mousemove', function (e) {
        if (videoPlayer.duration) {
            const progressBarRect = progressBar.getBoundingClientRect();
            const hoverPosition = e.clientX - progressBarRect.left;
            const percentage = hoverPosition / progressBarRect.width;

            // Show time at hover position
            const hoverTime = percentage * videoPlayer.duration;
            // If we had a tooltip, we would update it here
        }
    });

    // Play/pause buttons
    playBtn.addEventListener('click', togglePlay);
    pauseBtn.addEventListener('click', togglePlay);
    bigPlayBtn.addEventListener('click', togglePlay);
    videoPlayer.addEventListener('click', function (e) {
        // Prevent click when clicking on controls
        if (e.target === videoPlayer) {
            togglePlay();
        }
    });

    // Volume controls
    volumeBtn.addEventListener('click', toggleMute);
    volumeSlider.addEventListener('input', setVolume);

    // Fullscreen button
    fullscreenBtn.addEventListener('click', toggleFullscreen);

    // Quality selection
    qualityOptions.forEach(option => {
        option.addEventListener('click', function () {
            setQuality(this.dataset.quality);
        });
    });

    // Favorite button
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', toggleFavorite);
    }

    // Share button
    if (shareBtn) {
        shareBtn.addEventListener('click', shareVideo);
    }

    // Mouse move on video wrapper
    videoWrapper.addEventListener('mousemove', handleControlsVisibility);

    // Mouse leave video wrapper
    videoWrapper.addEventListener('mouseleave', function () {
        if (playerState.playing) {
            clearTimeout(playerState.controlsTimeout);
            playerState.controlsTimeout = setTimeout(() => {
                customControls.classList.remove('active');
            }, 1000);
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Only process when focused on player
        if (document.activeElement.tagName === 'INPUT') return;

        switch (e.key.toLowerCase()) {
            case ' ':
            case 'k':
                togglePlay();
                e.preventDefault();
                break;
            case 'f':
                toggleFullscreen();
                e.preventDefault();
                break;
            case 'm':
                toggleMute();
                e.preventDefault();
                break;
            case 'arrowleft':
                videoPlayer.currentTime = Math.max(0, videoPlayer.currentTime - 5);
                e.preventDefault();
                break;
            case 'arrowright':
                videoPlayer.currentTime = Math.min(videoPlayer.duration, videoPlayer.currentTime + 5);
                e.preventDefault();
                break;
            case 'arrowup':
                volumeSlider.value = Math.min(1, parseFloat(volumeSlider.value) + 0.1);
                setVolume();
                e.preventDefault();
                break;
            case 'arrowdown':
                volumeSlider.value = Math.max(0, parseFloat(volumeSlider.value) - 0.1);
                setVolume();
                e.preventDefault();
                break;
        }
    });

    // Mobile touch events
    let touchStartX = 0;
    let touchStartY = 0;
    let touchStartTime = 0;

    videoWrapper.addEventListener('touchstart', function (e) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        touchStartTime = Date.now();
    }, { passive: true });

    videoWrapper.addEventListener('touchend', function (e) {
        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        const touchEndTime = Date.now();

        const touchDuration = touchEndTime - touchStartTime;
        const touchDistanceX = Math.abs(touchEndX - touchStartX);
        const touchDistanceY = Math.abs(touchEndY - touchStartY);

        // Check if it's a tap (short touch without much movement)
        if (touchDuration < 300 && touchDistanceX < 20 && touchDistanceY < 20) {
            // Toggle play/pause on tap
            togglePlay();
        }

        // Check for horizontal swipe (seek)
        if (touchDistanceX > 50 && touchDistanceX > touchDistanceY) {
            if (touchEndX < touchStartX) {
                // Swipe left (rewind)
                videoPlayer.currentTime = Math.max(0, videoPlayer.currentTime - 10);
                showNotification('Rewinding 10s');
            } else {
                // Swipe right (forward)
                videoPlayer.currentTime = Math.min(videoPlayer.duration, videoPlayer.currentTime + 10);
                showNotification('Forwarding 10s');
            }
        }

        // Check for vertical swipe on right side (volume)
        if (touchDistanceY > 50 && touchDistanceY > touchDistanceX && touchStartX > window.innerWidth / 2) {
            if (touchEndY < touchStartY) {
                // Swipe up (volume up)
                volumeSlider.value = Math.min(1, parseFloat(volumeSlider.value) + 0.1);
                setVolume();
            } else {
                // Swipe down (volume down)
                volumeSlider.value = Math.max(0, parseFloat(volumeSlider.value) - 0.1);
                setVolume();
            }
        }
    }, { passive: true });

    // Initialize
    updateProgress();
    setVolume();

    // Auto-play with muted audio (to comply with browser autoplay policies)
    videoPlayer.muted = true;
    playerState.muted = true;
    volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
    volumeSlider.value = 0;

    // Show initial notification
    setTimeout(() => {
        showNotification('Video is muted. Click volume icon to unmute.');
    }, 1000);
});