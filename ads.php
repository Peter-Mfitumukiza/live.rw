<?php
$ads = [
    [
        'image' => 'assets/vv_prime_ad.png',
        'url' => 'vv.rw/page/prime',
        'alt' => 'Vuba vuba prime'
    ],
    [
        'image' => 'assets/kpay_ad.png',
        'url' => 'kpay.africa',
        'alt' => 'Kpay'
    ],
    [
        'image' => 'assets/vuba_prime_ad.png',
        'url' => 'https://www.vubavuba.rw/prime',
        'alt' => 'Vuba prime'
    ]
];

// Set a default position for the ad
$position = isset($ad_position) ? $ad_position : 'sidebar';
?>

<div class="ad-container <?php echo $position; ?>-ad">
    <div class="ad-slideshow">
        <?php foreach ($ads as $index => $ad): ?>
            <div class="ad-slide fade" style="display: <?php echo ($index === 0) ? 'block' : 'none'; ?>">
                <a href="<?php echo $ad['url']; ?>" target="_blank" rel="noopener">
                    <img src="<?php echo $ad['image']; ?>" alt="<?php echo $ad['alt']; ?>" class="ad-image">
                </a>
            </div>
        <?php endforeach; ?>
        
        <!-- Dots/circles for the slideshow -->
        <div class="ad-dots">
            <?php foreach ($ads as $index => $ad): ?>
                <span class="ad-dot" data-index="<?php echo $index; ?>"></span>
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
    width: 100%; /* For sidebar position */
}

.banner-ad {
    width: 100%; /* For horizontal banner position */
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

.ad-dot.active, .ad-dot:hover {
    background-color: #FFC700;
}

.fade {
    animation: fadeAnimation 1s ease;
}

@keyframes fadeAnimation {
    from {opacity: 0.4}
    to {opacity: 1}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ad slideshow
    const adSlides = document.querySelectorAll('.ad-slide');
    const adDots = document.querySelectorAll('.ad-dot');
    let currentAdIndex = 0;

    // Set first slide and dot as active
    if (adSlides.length > 0) {
        adSlides[0].style.display = 'block';
    }
    if (adDots.length > 0) {
        adDots[0].classList.add('active');
    }

    // Add click event to dots
    adDots.forEach(dot => {
        dot.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            showAd(index);
        });
    });
    
    // Auto-rotate ads
    if (adSlides.length > 1) {
        setInterval(() => {
            currentAdIndex++;
            if (currentAdIndex >= adSlides.length) {
                currentAdIndex = 0;
            }
            showAd(currentAdIndex);
        }, 5000); // Change ad every 5 seconds
    }
    
    function showAd(index) {
        // Hide all slides
        adSlides.forEach(slide => {
            slide.style.display = 'none';
        });
        
        // Remove active class from all dots
        adDots.forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Show the current slide and activate dot
        adSlides[index].style.display = 'block';
        adDots[index].classList.add('active');
        currentAdIndex = index;
    }
});
</script>