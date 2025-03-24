<?php
$ads = [
    [
        'image' => 'assets/vv_prime_ad.png',
        'url' => 'https://www.vubavuba.rw/prime',
        'alt' => 'Vuba vuba prime'
    ],
    [
        'image' => 'assets/kpay_ad.png',
        'url' => 'https://kpay.africa/',
        'alt' => 'Kpay'
    ],
    [
        'image' => 'assets/vuba_prime_ad.png',
        'url' => 'https://www.vubavuba.rw/prime',
        'alt' => 'Vuba prime'
    ]
];

// Generate a unique ID for this ad container
$adContainerId = 'ad-container-' . rand(1000, 9999);

// Set a default position for the ad
$position = isset($ad_position) ? $ad_position : 'sidebar';
?>

<div id="<?php echo $adContainerId; ?>" class="ad-container <?php echo $position; ?>-ad">
    <div class="ad-slideshow">
        <?php foreach ($ads as $index => $ad): ?>
            <div class="ad-slide" style="display: <?php echo ($index === 0) ? 'block' : 'none'; ?>">
                <a href="<?php echo $ad['url']; ?>" target="_blank" rel="noopener">
                    <img src="<?php echo $ad['image']; ?>" alt="<?php echo $ad['alt']; ?>" class="ad-image">
                </a>
            </div>
        <?php endforeach; ?>

        <!-- Dots/circles for the slideshow -->
        <div class="ad-dots">
            <?php foreach ($ads as $index => $ad): ?>
                <span class="ad-dot <?php echo ($index === 0) ? 'active' : ''; ?>"
                    data-index="<?php echo $index; ?>"></span>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="ad-label">Advertisement</div>
</div>

<style>
    .ad-container {
        margin: 20px 0;
        position: relative;
        background-color: #1a1a1a;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .sidebar-ad {
        width: 100%;
        /* For sidebar position */
    }

    .banner-ad {
        width: 100%;
        /* For horizontal banner position */
        max-width: 728px;
        margin: 20px auto;
    }

    .footer-ad {
        width: 100%;
        max-width: 970px;
        margin: 20px auto;
    }

    .ad-slideshow {
        position: relative;
    }

    .ad-slide {
        width: 100%;
        transition: opacity 0.5s ease;
    }

    .ad-image {
        width: 100%;
        display: block;
        height: auto;
    }

    .ad-label {
        position: absolute;
        top: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.5);
        color: #a0a0a0;
        font-size: 10px;
        padding: 2px 5px;
        border-bottom-left-radius: 5px;
    }

    .ad-dots {
        position: absolute;
        bottom: 8px;
        width: 100%;
        text-align: center;
        z-index: 5;
    }

    .ad-dot {
        height: 6px;
        width: 6px;
        margin: 0 4px;
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .ad-dot.active,
    .ad-dot:hover {
        background-color: #FFC700;
    }
</style>

<script>
    (function () {
        function initAdSlideshow() {
            const adContainer = document.getElementById('<?php echo $adContainerId; ?>');
            if (!adContainer) return;

            const adSlides = adContainer.querySelectorAll('.ad-slide');
            const adDots = adContainer.querySelectorAll('.ad-dot');
            let currentAdIndex = 0;
            let slideshowInterval = null;

            if (adSlides.length === 0) return;

            // Set first slide and dot as active initially (redundant but for safety)
            adSlides[0].style.display = 'block';
            if (adDots.length > 0) {
                adDots[0].classList.add('active');
            }

            // Add click event to dots
            adDots.forEach(dot => {
                dot.addEventListener('click', function (e) {
                    e.preventDefault();
                    const index = parseInt(this.getAttribute('data-index'));
                    if (!isNaN(index)) {
                        showAd(index);

                        // Reset the interval when manually changing
                        if (slideshowInterval) {
                            clearInterval(slideshowInterval);
                            startAutoRotation();
                        }
                    }
                });
            });

            // Start auto-rotation
            if (adSlides.length > 1) {
                startAutoRotation();

                // Add pause on hover
                adContainer.addEventListener('mouseenter', function () {
                    clearInterval(slideshowInterval);
                    slideshowInterval = null;
                });

                adContainer.addEventListener('mouseleave', function () {
                    if (!slideshowInterval) {
                        startAutoRotation();
                    }
                });
            }

            function startAutoRotation() {
                slideshowInterval = setInterval(function () {
                    currentAdIndex++;
                    if (currentAdIndex >= adSlides.length) {
                        currentAdIndex = 0;
                    }
                    showAd(currentAdIndex);
                }, 5000);
            }

            function showAd(index) {
                // Validate index
                if (index < 0 || index >= adSlides.length) {
                    return;
                }

                // Hide all slides
                for (let i = 0; i < adSlides.length; i++) {
                    adSlides[i].style.display = 'none';
                }

                // Remove active class from all dots
                for (let i = 0; i < adDots.length; i++) {
                    adDots[i].classList.remove('active');
                }

                // Show the current slide
                adSlides[index].style.display = 'block';

                // Add active class to current dot
                if (adDots[index]) {
                    adDots[index].classList.add('active');
                }

                currentAdIndex = index;
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initAdSlideshow);
        } else {
            // DOM is already ready
            initAdSlideshow();
        }
    })();
</script>