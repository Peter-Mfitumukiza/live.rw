<?php
/**
 * LiveRW - Match Data
 * This file contains arrays of match data for different sections of the website
 */

// Featured matches for the hero slideshow
$featured_matches = [
    [
        'id' => 1,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/disp/7077e43007319.560193cfa5ead.jpg',
        'rating' => '4.8',
        'year' => '2025',
        'title' => 'UEFA Champions League',
        'description' => 'Real Madrid vs Manchester City - Quarter Finals. The defending champions face off against the English giants in what promises to be a thrilling encounter at Santiago Bernabéu Stadium.',
        'quality' => 'HD',
        'category' => 'Live',
        'match_date' => '2025-04-15 20:45:00',
        'teams' => [
            'home' => 'Real Madrid',
            'away' => 'Manchester City'
        ],
        'league' => 'UEFA Champions League',
        'stage' => 'Quarter Finals'
    ],
    // [
    //     'id' => 2,
    //     'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/06c72b73932341.5c1aee5608337.jpg',
    //     'rating' => '4.7',
    //     'year' => '2025',
    //     'title' => 'NBA Playoffs',
    //     'description' => 'Los Angeles Lakers vs Boston Celtics - Conference Finals Game 5. The historic rivalry continues as both teams battle for a place in the NBA Finals in this must-win game.',
    //     'quality' => 'HD',
    //     'category' => 'Live',
    //     'match_date' => '2025-05-22 19:30:00',
    //     'teams' => [
    //         'home' => 'Los Angeles Lakers',
    //         'away' => 'Boston Celtics'
    //     ],
    //     'league' => 'NBA',
    //     'stage' => 'Conference Finals - Game 5'
    // ],
    // [
    //     'id' => 3,
    //     'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/e76c3276329315.5c6755c8c5347.jpg',
    //     'rating' => '4.6',
    //     'year' => '2025',
    //     'title' => 'Formula 1 Grand Prix',
    //     'description' => 'Monaco Grand Prix - Round 6. Experience the glitz and glamour of F1\'s most prestigious race as drivers navigate the challenging streets of Monte Carlo in pursuit of glory.',
    //     'quality' => 'HD',
    //     'category' => 'Live',
    //     'match_date' => '2025-05-25 14:00:00',
    //     'teams' => [
    //         'home' => 'Circuit de Monaco',
    //         'away' => ''
    //     ],
    //     'league' => 'Formula 1',
    //     'stage' => 'Round 6'
    // ],
    [
        'id' => 4,
        'image' => 'https://ichef.bbci.co.uk/images/ic/640x360/p0jbbdhc.jpg',
        'rating' => '4.5',
        'year' => '2025',
        'title' => 'Wimbledon Finals',
        'description' => 'Men\'s Singles Final. The two best players in the tournament face off on Centre Court for the prestigious Wimbledon title in this grand slam showdown.',
        'quality' => 'HD',
        'category' => 'Live',
        'match_date' => '2025-07-13 14:00:00',
        'teams' => [
            'home' => 'Finalist 1',
            'away' => 'Finalist 2'
        ],
        'league' => 'Tennis Grand Slam',
        'stage' => 'Finals'
    ],
    [
        'id' => 5,
        'image' => 'https://images.indianexpress.com/2019/07/brazil-vs-argentina.jpg',
        'rating' => '4.9',
        'year' => '2025',
        'title' => 'FIFA World Cup Qualifier',
        'description' => 'Brazil vs Argentina - CONMEBOL Qualifiers. The South American giants clash in a crucial World Cup qualifier match that could determine their path to the tournament.',
        'quality' => 'HD',
        'category' => 'Upcoming',
        'match_date' => '2025-06-05 19:00:00',
        'teams' => [
            'home' => 'Brazil',
            'away' => 'Argentina'
        ],
        'league' => 'FIFA World Cup',
        'stage' => 'Qualifiers'
    ]
];

// Live matches currently streaming
$live_matches = [
    [
        'id' => 101,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/57ea5f104454755.5f64738485d29.jpg',
        'title' => 'Premier League',
        'teams' => [
            'home' => 'Liverpool',
            'away' => 'Arsenal'
        ],
        'score' => [2, 1],
        'time' => '65\'',
        'quality' => 'HD'
    ],
    [
        'id' => 102,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/1f337f93991679.5e74e4ea44fec.jpg',
        'title' => 'La Liga',
        'teams' => [
            'home' => 'Barcelona',
            'away' => 'Atletico Madrid'
        ],
        'score' => [0, 0],
        'time' => '23\'',
        'quality' => 'HD'
    ],
    [
        'id' => 103,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/1acef054979277.5972d2a49c8b0.jpg',
        'title' => 'Cricket World Cup',
        'teams' => [
            'home' => 'India',
            'away' => 'England'
        ],
        'score' => ['156/3', ''],
        'time' => '25 overs',
        'quality' => 'HD'
    ],
    [
        'id' => 104,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/df015793991679.5e74e4ea43f20.jpg',
        'title' => 'NBA Regular Season',
        'teams' => [
            'home' => 'Brooklyn Nets',
            'away' => 'Miami Heat'
        ],
        'score' => [78, 82],
        'time' => 'Q3 5:45',
        'quality' => 'HD'
    ]
];

// Upcoming matches scheduled for broadcast
$upcoming_matches = [
    [
        'id' => 201,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/6c92a593991679.5e74e4ea45c4c.jpg',
        'title' => 'Serie A',
        'teams' => [
            'home' => 'Juventus',
            'away' => 'AC Milan'
        ],
        'match_date' => '2025-03-21 18:00:00',
        'quality' => 'HD',
        'notify' => true
    ],
    [
        'id' => 202,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/02367c104454755.5f64738484fcf.jpg',
        'title' => 'UFC 300',
        'teams' => [
            'home' => 'Jon Jones',
            'away' => 'Israel Adesanya'
        ],
        'match_date' => '2025-03-22 22:00:00',
        'quality' => 'HD',
        'notify' => true
    ],
    [
        'id' => 203,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/bfff3e93991679.5e74e4ea4379e.jpg',
        'title' => 'Bundesliga',
        'teams' => [
            'home' => 'Bayern Munich',
            'away' => 'Borussia Dortmund'
        ],
        'match_date' => '2025-03-23 15:30:00',
        'quality' => 'HD',
        'notify' => false
    ],
    [
        'id' => 204,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/0d6c5c54979277.5972d2a49cdbf.jpg',
        'title' => 'Tennis Masters 1000',
        'teams' => [
            'home' => 'Rafael Nadal',
            'away' => 'Novak Djokovic'
        ],
        'match_date' => '2025-03-24 16:00:00',
        'quality' => 'HD',
        'notify' => true
    ]
];

// Popular leagues for browsing
$popular_leagues = [
    [
        'id' => 301,
        'name' => 'Premier League',
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/4e495e93991679.5e74e4ea44a8e.jpg',
        'matches_count' => 24
    ],
    [
        'id' => 302,
        'name' => 'NBA',
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/06c72b73932341.5c1aee5608337.jpg',
        'matches_count' => 18
    ],
    [
        'id' => 303,
        'name' => 'UEFA Champions League',
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/disp/7077e43007319.560193cfa5ead.jpg',
        'matches_count' => 16
    ],
    [
        'id' => 304,
        'name' => 'Formula 1',
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/e76c3276329315.5c6755c8c5347.jpg',
        'matches_count' => 12
    ],
    [
        'id' => 305,
        'name' => 'UFC',
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/02367c104454755.5f64738484fcf.jpg',
        'matches_count' => 8
    ],
    [
        'id' => 306,
        'name' => 'Tennis Grand Slams',
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/e661b154979277.59724da41dfa1.jpg',
        'matches_count' => 10
    ]
];

$highlights = [
    [
        'id' => 101,
        'image' => 'https://i.ytimg.com/vi/ItHrwGSHFiY/maxresdefault.jpg',
        'title' => 'Liverpool vs Arsenal - Goals & Highlights',
        'league' => 'Premier League',
        'date' => 'Mar 15, 2025',
        'duration' => '05:26',
        'quality' => 'HD',
        'views' => '24.5K',
        'likes' => '1.2K'
    ],
    [
        'id' => 102,
        'image' => 'https://i.ytimg.com/vi/Z0vsD8J-0_4/hq720.jpg?sqp=-oaymwEhCK4FEIIDSFryq4qpAxMIARUAAAAAGAElAADIQj0AgKJD&rs=AOn4CLCDurr_sY3SkVaE4Y6D9VA1UphomA',
        'title' => 'Barcelona vs Atletico Madrid - Extended Highlights',
        'league' => 'La Liga',
        'date' => 'Mar 14, 2025',
        'duration' => '03:47',
        'quality' => 'HD',
        'views' => '18.9K',
        'likes' => '956'
    ],
    [
        'id' => 103,
        'image' => 'https://resize.indiatvnews.com/en/resize/newbucket/1200_-/2022/11/eng-vs-ind-highlights-1668078206.jpg',
        'title' => 'India vs England - Match Highlights',
        'league' => 'Cricket World Cup',
        'date' => 'Mar 12, 2025',
        'duration' => '08:12',
        'quality' => 'HD',
        'views' => '32.1K',
        'likes' => '2.8K'
    ],
    [
        'id' => 104,
        'image' => 'https://i.ytimg.com/vi/CSAI75xA_Q4/maxresdefault.jpg',
        'title' => 'Brooklyn Nets vs Miami Heat - Top Plays',
        'league' => 'NBA Regular Season',
        'date' => 'Mar 10, 2025',
        'duration' => '04:35',
        'quality' => 'HD',
        'views' => '15.3K',
        'likes' => '743'
    ],
    [
        'id' => 105,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/e76c3276329315.5c6755c8c5347.jpg',
        'title' => 'Monaco Grand Prix - Race Highlights',
        'league' => 'Formula 1',
        'date' => 'Mar 8, 2025',
        'duration' => '10:18',
        'quality' => 'HD',
        'views' => '28.7K',
        'likes' => '1.5K'
    ],
    [
        'id' => 106,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/e661b154979277.59724da41dfa1.jpg',
        'title' => 'Wimbledon Finals Highlights - Best Moments',
        'league' => 'Tennis Grand Slam',
        'date' => 'Mar 5, 2025',
        'duration' => '07:32',
        'quality' => 'HD',
        'views' => '12.3K',
        'likes' => '876'
    ]
];

$upcoming_matches = [
    [
        'id' => 201,
        'title' => 'Serie A',
        'teams' => [
            'home' => 'Juventus',
            'away' => 'AC Milan'
        ],
        'match_date' => '2025-03-21 18:00:00', // Format: YYYY-MM-DD HH:MM:SS
        'quality' => 'HD',
        'notify' => true,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/6c92a593991679.5e74e4ea45c4c.jpg',
        'price' => '9.99' // Added price information
    ],
    [
        'id' => 202,
        'title' => 'UFC 300',
        'teams' => [
            'home' => 'Jon Jones',
            'away' => 'Israel Adesanya'
        ],
        'match_date' => '2025-03-22 22:00:00',
        'quality' => 'HD',
        'notify' => false,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/02367c104454755.5f64738484fcf.jpg',
        'price' => '29.99' // UFC events typically cost more
    ],
    [
        'id' => 203,
        'title' => 'Bundesliga',
        'teams' => [
            'home' => 'Bayern Munich',
            'away' => 'Borussia Dortmund'
        ],
        'match_date' => date('Y-m-d H:i:s', strtotime('+3 hours')), // Today, 3 hours from now
        'quality' => 'HD',
        'notify' => true,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/bfff3e93991679.5e74e4ea4379e.jpg',
        'price' => '12.99'
    ],
    [
        'id' => 204,
        'title' => 'Tennis Masters 1000',
        'teams' => [
            'home' => 'Rafael Nadal',
            'away' => 'Novak Djokovic'
        ],
        'match_date' => date('Y-m-d H:i:s', strtotime('+20 hours')), // Tomorrow
        'quality' => 'HD',
        'notify' => false,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/0d6c5c54979277.5972d2a49cdbf.jpg',
        'price' => '14.99'
    ],
    [
        'id' => 205,
        'title' => 'Premier League',
        'teams' => [
            'home' => 'Manchester United',
            'away' => 'Chelsea'
        ],
        'match_date' => date('Y-m-d H:i:s', strtotime('+2 days')), // In 2 days
        'quality' => 'HD',
        'notify' => true,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/57ea5f104454755.5f64738485d29.jpg',
        'price' => '9.99'
    ],
    [
        'id' => 206,
        'title' => 'La Liga',
        'teams' => [
            'home' => 'Real Madrid',
            'away' => 'FC Barcelona'
        ],
        'match_date' => date('Y-m-d H:i:s', strtotime('+5 days')), // In 5 days
        'quality' => '4K',
        'notify' => false,
        'image' => 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/1f337f93991679.5e74e4ea44fec.jpg',
        'price' => '14.99' // Premium price for 4K stream
    ]
];
