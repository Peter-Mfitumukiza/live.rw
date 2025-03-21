<?php

if (!isset($db_mysql)) {
    require_once('config/db.php');
}

function getFeaturedMatches($db)
{
    $matches = [];

    $query = "
        SELECT 
            e.id, e.title, e.description, e.image, e.category, 
            e.quality, e.match_date, e.stage, l.name AS league, 
            home.name AS home_team, away.name AS away_team
        FROM events e
        INNER JOIN leagues l ON e.league_id = l.id
        LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
        LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
        LEFT JOIN teams home ON et_home.team_id = home.id
        LEFT JOIN teams away ON et_away.team_id = away.id
        WHERE e.featured = 1
        ORDER BY e.match_date ASC
        LIMIT 5
    ";

    $result = mysqli_query($db, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $matches[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'image' => $row['image'],
                'quality' => $row['quality'],
                'category' => $row['category'],
                'match_date' => $row['match_date'],
                'teams' => [
                    'home' => $row['home_team'],
                    'away' => $row['away_team']
                ],
                'league' => $row['league'],
                'stage' => $row['stage']
            ];
        }
    }

    return $matches;
}

// Fetch highlights from database
function getHighlights($db, $limit = 4)
{
    $highlights = [];

    $query = "
        SELECT 
            h.id, h.title, h.image, h.duration, h.date, 
            h.views_count, h.video_url, h.likes_count, h.video_url, l.name AS league
        FROM highlights h
        LEFT JOIN leagues l ON h.league_id = l.id
        ORDER BY h.date DESC
        LIMIT {$limit}
    ";

    $result = mysqli_query($db, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $highlights[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'image' => $row['image'],
                'duration' => $row['duration'],
                'league' => $row['league'],
                'video_url' => $row['video_url'],
                'date' => date('M j, Y', strtotime($row['date'])),
                'views' => number_format($row['views_count']),
                'likes' => number_format($row['likes_count'])
            ];
        }
    }

    return $highlights;
}
function getUpcomingMatches($db, $limit = 4)
{
    $matches = [];

    $query = "
        SELECT 
            e.id, e.title, e.image, e.match_date, e.price,
            home.name AS home_team, away.name AS away_team
        FROM events e
        LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
        LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
        LEFT JOIN teams home ON et_home.team_id = home.id
        LEFT JOIN teams away ON et_away.team_id = away.id
        WHERE e.match_date > NOW() AND e.status = 'scheduled'
        ORDER BY e.match_date ASC
        LIMIT {$limit}
    ";

    $result = mysqli_query($db, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $matches[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'teams' => [
                    'home' => $row['home_team'],
                    'away' => $row['away_team']
                ],
                'match_date' => $row['match_date'],
                'image' => $row['image'],
                'price' => $row['price'] ?? '9.99'
            ];
        }
    }

    return $matches;
}

function getEventById($db, $id)
{
    $query = "
        SELECT 
            e.id, e.title, e.description, e.image, e.category, 
            e.quality, e.match_date, e.stage, e.stream_url, e.status, e.is_paid,
            l.name AS league, 
            home_team.name AS home_team, 
            away_team.name AS away_team,
            et_home.score AS home_score,
            et_away.score AS away_score
        FROM events e
        LEFT JOIN leagues l ON e.league_id = l.id
        LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
        LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
        LEFT JOIN teams home_team ON et_home.team_id = home_team.id
        LEFT JOIN teams away_team ON et_away.team_id = away_team.id
        WHERE e.id = ?
    ";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'image' => $row['image'],
            'quality' => $row['quality'],
            'category' => $row['category'],
            'match_date' => $row['match_date'],
            'stream_url' => $row['stream_url'],
            'is_paid' => $row['is_paid'],
            'status' => $row['status'],
            'teams' => [
                'home' => $row['home_team'],
                'away' => $row['away_team'],
                'home_score' => $row['home_score'],
                'away_score' => $row['away_score']
            ],
            'league' => $row['league'],
            'stage' => $row['stage']
        ];
    }

    return null;
}

function getHighlightById($db, $id)
{
    $query = "
        SELECT 
            h.id, h.title, h.image, h.duration, h.date, h.video_url,
            h.views_count, h.likes_count, h.description,
            l.name AS league
        FROM highlights h
        LEFT JOIN leagues l ON h.league_id = l.id
        WHERE h.id = ?
    ";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'image' => $row['image'],
            'duration' => $row['duration'],
            'video_url' => $row['video_url'],
            'description' => $row['description'],
            'league' => $row['league'],
            'date' => date('M j, Y', strtotime($row['date'])),
            'views' => number_format($row['views_count']),
            'likes' => number_format($row['likes_count'])
        ];
    }

    return null;
}

function getRelatedContent($db, $eventId = null, $sportId = null, $limit = 5)
{
    $relatedContent = [];

    // Logic to find related content based on event ID or sport ID
    if ($eventId) {
        // First try to get related by same sport
        $query = "
            SELECT 
                e.id, e.title, e.image, e.match_date, 
                'event' AS content_type,
                l.name AS league
            FROM events e
            LEFT JOIN leagues l ON e.league_id = l.id
            WHERE e.sport_id = (SELECT sport_id FROM events WHERE id = ?)
            AND e.id != ?
            ORDER BY 
                CASE 
                    WHEN e.status = 'live' THEN 1 
                    WHEN e.status = 'scheduled' THEN 2 
                    ELSE 3 
                END, 
                e.match_date DESC
            LIMIT {$limit}
        ";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ii", $eventId, $eventId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $relatedContent[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'image' => $row['image'],
                'date' => date('M j, Y', strtotime($row['match_date'])),
                'content_type' => 'event',
                'league' => $row['league']
            ];
        }
    }

    // If we still need more, add highlights
    if (count($relatedContent) < $limit) {
        $neededHighlights = $limit - count($relatedContent);

        $query = "
            SELECT 
                h.id, h.title, h.image, h.duration, h.date,
                'highlight' AS content_type,
                l.name AS league
            FROM highlights h
            LEFT JOIN leagues l ON h.league_id = l.id
            ORDER BY h.date DESC
            LIMIT {$neededHighlights}
        ";

        $result = mysqli_query($db, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $relatedContent[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'image' => $row['image'],
                'duration' => $row['duration'],
                'date' => date('M j, Y', strtotime($row['date'])),
                'content_type' => 'highlight',
                'league' => $row['league']
            ];
        }
    }

    return $relatedContent;
}