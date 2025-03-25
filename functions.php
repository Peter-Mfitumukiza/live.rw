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

/**
 * Device Limit Implementation
 * 
 */

/**
 * Generate a unique device identifier based on user agent, IP, and other factors
 * Note: For improved security in production, consider using a more robust fingerprinting library
 * 
 * @return string Unique device identifier
 */
function generateDeviceIdentifier()
{
    // Create a hash from user agent, IP, and an additional salt
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $salt = 'LiveRW_device_identifier_salt'; // Change this in production

    return hash('sha256', $userAgent . $ip . $salt);
}

/**
 * Get device name from user agent
 * 
 * @param string $userAgent The user agent string
 * @return string Formatted device name
 */
function getDeviceName($userAgent)
{
    $deviceName = 'Unknown Device';

    // Simple detection of common devices and browsers
    if (strpos($userAgent, 'iPhone') !== false) {
        $deviceName = 'iPhone';
    } elseif (strpos($userAgent, 'iPad') !== false) {
        $deviceName = 'iPad';
    } elseif (strpos($userAgent, 'Android') !== false) {
        if (strpos($userAgent, 'Mobile') !== false) {
            $deviceName = 'Android Phone';
        } else {
            $deviceName = 'Android Tablet';
        }
    } elseif (strpos($userAgent, 'Windows') !== false) {
        $deviceName = 'Windows PC';
    } elseif (strpos($userAgent, 'Macintosh') !== false) {
        $deviceName = 'Mac';
    } elseif (strpos($userAgent, 'Linux') !== false) {
        $deviceName = 'Linux PC';
    }

    // Add browser info
    if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
        $deviceName .= ' - Chrome';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $deviceName .= ' - Firefox';
    } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
        $deviceName .= ' - Safari';
    } elseif (strpos($userAgent, 'Edg') !== false) {
        $deviceName .= ' - Edge';
    }

    return $deviceName;
}

/**
 * Check if user has reached the device limit
 * 
 * @param mysqli $db Database connection
 * @param int $userId User ID
 * @param int $maxDevices Maximum number of devices allowed (default: 3)
 * @return bool True if limit reached, false otherwise
 */
function hasReachedDeviceLimit($db, $userId, $maxDevices = 3)
{
    $query = "SELECT COUNT(*) as device_count FROM user_devices 
              WHERE user_id = ? AND is_active = 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row['device_count'] >= $maxDevices;
}

/**
 * Get user's active devices
 * 
 * @param mysqli $db Database connection
 * @param int $userId User ID
 * @return array Array of active devices
 */
function getUserActiveDevices($db, $userId)
{
    $query = "SELECT id, device_name, last_login, last_active, ip_address 
              FROM user_devices 
              WHERE user_id = ? AND is_active = 1 
              ORDER BY last_active DESC";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $devices = [];
    while ($device = mysqli_fetch_assoc($result)) {
        $devices[] = $device;
    }

    return $devices;
}

/**
 * Register a device login
 * 
 * @param mysqli $db Database connection
 * @param int $userId User ID
 * @return array Result with success status and message
 */
function registerDeviceLogin($db, $userId)
{
    $deviceIdentifier = generateDeviceIdentifier();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $deviceName = getDeviceName($userAgent);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $currentTime = date('Y-m-d H:i:s');

    // Check if this device is already registered for this user
    $query = "SELECT id FROM user_devices 
              WHERE user_id = ? AND device_identifier = ?";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "is", $userId, $deviceIdentifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Device exists, update its last_login and last_active time
        $deviceRow = mysqli_fetch_assoc($result);
        $deviceId = $deviceRow['id'];

        $updateQuery = "UPDATE user_devices 
                        SET last_login = ?, last_active = ?, is_active = 1, ip_address = ? 
                        WHERE id = ?";

        $updateStmt = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "sssi", $currentTime, $currentTime, $ipAddress, $deviceId);

        if (mysqli_stmt_execute($updateStmt)) {
            return ['success' => true, 'message' => 'Device login updated', 'device_id' => $deviceId];
        } else {
            return ['success' => false, 'message' => 'Failed to update device login'];
        }
    } else {
        // Check if user has reached device limit
        if (hasReachedDeviceLimit($db, $userId)) {
            return ['success' => false, 'message' => 'Maximum device limit reached', 'limit_reached' => true];
        }

        // New device, insert it
        $insertQuery = "INSERT INTO user_devices 
                        (user_id, device_identifier, device_name, last_login, last_active, ip_address, user_agent) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $insertStmt = mysqli_prepare($db, $insertQuery);
        mysqli_stmt_bind_param(
            $insertStmt,
            "issssss",
            $userId,
            $deviceIdentifier,
            $deviceName,
            $currentTime,
            $currentTime,
            $ipAddress,
            $userAgent
        );

        if (mysqli_stmt_execute($insertStmt)) {
            $deviceId = mysqli_insert_id($db);
            return ['success' => true, 'message' => 'New device registered', 'device_id' => $deviceId];
        } else {
            return ['success' => false, 'message' => 'Failed to register new device'];
        }
    }
}

/**
 * Deactivate a device
 * 
 * @param mysqli $db Database connection
 * @param int $userId User ID
 * @param int $deviceId Device ID to deactivate
 * @return array Result with success status and message
 */
function deactivateDevice($db, $userId, $deviceId)
{
    // Ensure the user owns this device
    $query = "UPDATE user_devices 
              SET is_active = 0 
              WHERE id = ? AND user_id = ?";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ii", $deviceId, $userId);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        return ['success' => true, 'message' => 'Device deactivated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to deactivate device or device not found'];
    }
}

/**
 * Update device activity timestamp
 * Should be called periodically while the user is active
 * 
 * @param mysqli $db Database connection
 * @param int $userId User ID
 * @return bool Success status
 */
function updateDeviceActivity($db, $userId)
{
    $deviceIdentifier = generateDeviceIdentifier();
    $currentTime = date('Y-m-d H:i:s');

    $query = "UPDATE user_devices 
              SET last_active = ? 
              WHERE user_id = ? AND device_identifier = ?";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sis", $currentTime, $userId, $deviceIdentifier);

    return mysqli_stmt_execute($stmt);
}

/**
 * Deactivate current device on logout
 * 
 * @param mysqli $db Database connection
 * @param int $userId User ID
 * @return bool Success status
 */
function logoutCurrentDevice($db, $userId)
{
    $deviceIdentifier = generateDeviceIdentifier();

    $query = "UPDATE user_devices 
              SET is_active = 0 
              WHERE user_id = ? AND device_identifier = ?";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "is", $userId, $deviceIdentifier);

    return mysqli_stmt_execute($stmt);
}

/**
 * Clean up inactive devices (can be run as a cron job)
 * This will deactivate devices that haven't been active for a specified period
 * 
 * @param mysqli $db Database connection
 * @param int $inactiveDays Number of days of inactivity before deactivation
 * @return int Number of deactivated devices
 */
function cleanupInactiveDevices($db, $inactiveDays = 30)
{
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-$inactiveDays days"));

    $query = "UPDATE user_devices 
              SET is_active = 0 
              WHERE last_active < ? AND is_active = 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $cutoffDate);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_affected_rows($stmt);
}