<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Function to get dashboard statistics
function getDashboardStats($db)
{
    $stats = [];

    // Get total users
    $userQuery = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($db, $userQuery);
    $stats['total_users'] = mysqli_fetch_assoc($result)['total'];

    // Get total events
    $eventQuery = "SELECT COUNT(*) as total FROM events";
    $result = mysqli_query($db, $eventQuery);
    $stats['total_events'] = mysqli_fetch_assoc($result)['total'];

    // Get total highlights
    $highlightQuery = "SELECT COUNT(*) as total FROM highlights";
    $result = mysqli_query($db, $highlightQuery);
    $stats['total_highlights'] = mysqli_fetch_assoc($result)['total'];

    // Get total revenue
    $revenueQuery = "SELECT SUM(amount_paid) as total FROM transactions WHERE payment_status = 'completed'";
    $result = mysqli_query($db, $revenueQuery);
    $stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;

    // Get recent transactions
    $transactionQuery = "
        SELECT t.id, t.amount_paid, t.payment_status, t.created_at, 
               u.name as username, e.title as event_title
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN events e ON t.event_id = e.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ";
    $result = mysqli_query($db, $transactionQuery);
    $stats['recent_transactions'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['recent_transactions'][] = $row;
    }

    // Get upcoming events
    $upcomingQuery = "
        SELECT e.id, e.title, e.image, e.match_date, e.price,
               home.name as home_team, away.name as away_team
        FROM events e
        LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
        LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
        LEFT JOIN teams home ON et_home.team_id = home.id
        LEFT JOIN teams away ON et_away.team_id = away.id
        WHERE e.match_date > NOW()
        ORDER BY e.match_date ASC
        LIMIT 4
    ";
    $result = mysqli_query($db, $upcomingQuery);
    $stats['upcoming_events'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['teams'] = [
            'home' => $row['home_team'],
            'away' => $row['away_team']
        ];
        unset($row['home_team'], $row['away_team']);
        $stats['upcoming_events'][] = $row;
    }

    return $stats;
}

// Function to get all events with pagination
function getAllEvents($db, $page = 1, $limit = 10, $search = '')
{
    $offset = ($page - 1) * $limit;

    $searchCondition = '';
    if (!empty($search)) {
        $search = mysqli_real_escape_string($db, $search);
        $searchCondition = "WHERE e.title LIKE '%$search%' OR home.name LIKE '%$search%' OR away.name LIKE '%$search%' OR l.name LIKE '%$search%'";
    }

    // Get total events count
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM events e
        LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
        LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
        LEFT JOIN teams home ON et_home.team_id = home.id
        LEFT JOIN teams away ON et_away.team_id = away.id
        LEFT JOIN leagues l ON e.league_id = l.id
        $searchCondition
    ";
    $result = mysqli_query($db, $countQuery);
    $totalEvents = mysqli_fetch_assoc($result)['total'];
    $totalPages = ceil($totalEvents / $limit);

    // Get events for the current page
    $query = "
        SELECT e.id, e.title, e.image, e.match_date, e.status, e.is_paid, e.price,
               home.name as home_team, away.name as away_team, l.name as league_name
        FROM events e
        LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
        LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
        LEFT JOIN teams home ON et_home.team_id = home.id
        LEFT JOIN teams away ON et_away.team_id = away.id
        LEFT JOIN leagues l ON e.league_id = l.id
        $searchCondition
        ORDER BY e.match_date DESC
        LIMIT $offset, $limit
    ";

    $result = mysqli_query($db, $query);
    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }

    return [
        'events' => $events,
        'total_events' => $totalEvents,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

// Function to get all highlights with pagination
function getAllHighlights($db, $page = 1, $limit = 10, $search = '')
{
    $offset = ($page - 1) * $limit;

    $searchCondition = '';
    if (!empty($search)) {
        $search = mysqli_real_escape_string($db, $search);
        $searchCondition = "WHERE h.title LIKE '%$search%' OR l.name LIKE '%$search%'";
    }

    // Get total highlights count
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM highlights h
        LEFT JOIN leagues l ON h.league_id = l.id
        $searchCondition
    ";
    $result = mysqli_query($db, $countQuery);
    $totalHighlights = mysqli_fetch_assoc($result)['total'];
    $totalPages = ceil($totalHighlights / $limit);

    // Get highlights for the current page
    $query = "
        SELECT h.id, h.title, h.image, h.duration, h.date, h.views_count, h.likes_count,
               l.name as league_name
        FROM highlights h
        LEFT JOIN leagues l ON h.league_id = l.id
        $searchCondition
        ORDER BY h.date DESC
        LIMIT $offset, $limit
    ";

    $result = mysqli_query($db, $query);
    $highlights = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $highlights[] = $row;
    }

    return [
        'highlights' => $highlights,
        'total_highlights' => $totalHighlights,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

// Function to get all leagues
function getAllLeagues($db)
{
    $query = "SELECT id, name FROM leagues ORDER BY name ASC";
    $result = mysqli_query($db, $query);
    $leagues = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $leagues[] = $row;
    }
    return $leagues;
}

// Function to get all teams
function getAllTeams($db)
{
    $query = "SELECT id, name FROM teams ORDER BY name ASC";
    $result = mysqli_query($db, $query);
    $teams = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $teams[] = $row;
    }
    return $teams;
}

// Function to get a single event by ID
function getEventById($db, $id)
{
    $query = "
        SELECT e.*, 
               l.name as league_name,
               home.name as home_team, home.id as home_team_id,
               away.name as away_team, away.id as away_team_id
        FROM events e
        LEFT JOIN leagues l ON e.league_id = l.id
        LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
        LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
        LEFT JOIN teams home ON et_home.team_id = home.id
        LEFT JOIN teams away ON et_away.team_id = away.id
        WHERE e.id = ?
    ";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($event = mysqli_fetch_assoc($result)) {
        return $event;
    }

    return null;
}

// Function to get a single highlight by ID
function getHighlightById($db, $id)
{
    $query = "
        SELECT h.*, l.name as league_name
        FROM highlights h
        LEFT JOIN leagues l ON h.league_id = l.id
        WHERE h.id = ?
    ";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($highlight = mysqli_fetch_assoc($result)) {
        return $highlight;
    }

    return null;
}

// Function to create a new event
function createEvent($db, $data)
{
    try {
        // Start transaction
        mysqli_begin_transaction($db);

        // Insert into events table
        $query = "
            INSERT INTO events (title, description, image, category, quality, match_date, stage, 
                               status, is_paid, price, league_id, stream_url, featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($db, $query);

        $featured = isset($data['featured']) ? 1 : 0;
        $isPaid = isset($data['is_paid']) ? 1 : 0;

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssiidsi",
            $data['title'],
            $data['description'],
            $data['image'],
            $data['category'],
            $data['quality'],
            $data['match_date'],
            $data['stage'],
            $data['status'],
            $isPaid,
            $data['price'],
            $data['league_id'],
            $data['stream_url'],
            $featured
        );

        mysqli_stmt_execute($stmt);
        $eventId = mysqli_insert_id($db);

        // Insert home team
        if (!empty($data['home_team_id'])) {
            $teamQuery = "INSERT INTO event_teams (event_id, team_id, is_home) VALUES (?, ?, 1)";
            $teamStmt = mysqli_prepare($db, $teamQuery);
            mysqli_stmt_bind_param($teamStmt, "ii", $eventId, $data['home_team_id']);
            mysqli_stmt_execute($teamStmt);
        }

        // Insert away team
        if (!empty($data['away_team_id'])) {
            $teamQuery = "INSERT INTO event_teams (event_id, team_id, is_home) VALUES (?, ?, 0)";
            $teamStmt = mysqli_prepare($db, $teamQuery);
            mysqli_stmt_bind_param($teamStmt, "ii", $eventId, $data['away_team_id']);
            mysqli_stmt_execute($teamStmt);
        }

        // Commit transaction
        mysqli_commit($db);

        return ['success' => true, 'event_id' => $eventId];
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($db);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to update an existing event
function updateEvent($db, $id, $data)
{
    try {
        // Start transaction
        mysqli_begin_transaction($db);

        // Update events table
        $query = "
            UPDATE events SET
                title = ?,
                description = ?,
                image = ?,
                category = ?,
                quality = ?,
                match_date = ?,
                stage = ?,
                status = ?,
                is_paid = ?,
                price = ?,
                league_id = ?,
                stream_url = ?,
                featured = ?
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($db, $query);

        $featured = isset($data['featured']) ? 1 : 0;
        $isPaid = isset($data['is_paid']) ? 1 : 0;

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssiidsii",
            $data['title'],
            $data['description'],
            $data['image'],
            $data['category'],
            $data['quality'],
            $data['match_date'],
            $data['stage'],
            $data['status'],
            $isPaid,
            $data['price'],
            $data['league_id'],
            $data['stream_url'],
            $featured,
            $id
        );

        mysqli_stmt_execute($stmt);

        // Delete existing teams
        $deleteTeamsQuery = "DELETE FROM event_teams WHERE event_id = ?";
        $deleteStmt = mysqli_prepare($db, $deleteTeamsQuery);
        mysqli_stmt_bind_param($deleteStmt, "i", $id);
        mysqli_stmt_execute($deleteStmt);

        // Insert home team
        if (!empty($data['home_team_id'])) {
            $teamQuery = "INSERT INTO event_teams (event_id, team_id, is_home) VALUES (?, ?, 1)";
            $teamStmt = mysqli_prepare($db, $teamQuery);
            mysqli_stmt_bind_param($teamStmt, "ii", $id, $data['home_team_id']);
            mysqli_stmt_execute($teamStmt);
        }

        // Insert away team
        if (!empty($data['away_team_id'])) {
            $teamQuery = "INSERT INTO event_teams (event_id, team_id, is_home) VALUES (?, ?, 0)";
            $teamStmt = mysqli_prepare($db, $teamQuery);
            mysqli_stmt_bind_param($teamStmt, "ii", $id, $data['away_team_id']);
            mysqli_stmt_execute($teamStmt);
        }

        // Commit transaction
        mysqli_commit($db);

        return ['success' => true];
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($db);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to delete an event
function deleteEvent($db, $id)
{
    try {
        // Start transaction
        mysqli_begin_transaction($db);

        // Delete from event_teams
        $query = "DELETE FROM event_teams WHERE event_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        // Delete from events
        $query = "DELETE FROM events WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        // Commit transaction
        mysqli_commit($db);

        return ['success' => true];
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($db);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to create a new highlight
function createHighlight($db, $data)
{
    $query = "
        INSERT INTO highlights (title, description, image, duration, date, 
                              video_url, league_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($db, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssi",
        $data['title'],
        $data['description'],
        $data['image'],
        $data['duration'],
        $data['date'],
        $data['video_url'],
        $data['league_id']
    );

    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'highlight_id' => mysqli_insert_id($db)];
    } else {
        return ['success' => false, 'message' => mysqli_error($db)];
    }
}

// Function to update an existing highlight
function updateHighlight($db, $id, $data)
{
    $query = "
        UPDATE highlights SET
            title = ?,
            description = ?,
            image = ?,
            duration = ?,
            date = ?,
            video_url = ?,
            league_id = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($db, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssii",
        $data['title'],
        $data['description'],
        $data['image'],
        $data['duration'],
        $data['date'],
        $data['video_url'],
        $data['league_id'],
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => mysqli_error($db)];
    }
}

// Function to delete a highlight
function deleteHighlight($db, $id)
{
    $query = "DELETE FROM highlights WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => mysqli_error($db)];
    }
}

// Function to upload an image
function uploadImage($file, $directory = '../uploads/')
{
    try {
        // Check if directory exists, if not create it
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Get file info
        $fileName = basename($file['name']);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Generate unique file name
        $uniqueName = uniqid() . '.' . $fileType;
        $targetPath = $directory . $uniqueName;

        // Check if the file is an image
        $validTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileType, $validTypes)) {
            return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.'];
        }

        // Check file size (5MB max)
        if ($file['size'] > 5000000) {
            return ['success' => false, 'message' => 'File is too large. Maximum size is 5MB.'];
        }

        // Upload the file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'file_path' => $targetPath];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to get transactions with pagination
function getTransactions($db, $page = 1, $limit = 10, $search = '')
{
    $offset = ($page - 1) * $limit;

    $searchCondition = '';
    if (!empty($search)) {
        $search = mysqli_real_escape_string($db, $search);
        $searchCondition = "WHERE 
            t.transaction_ref LIKE '%$search%' OR 
            u.name LIKE '%$search%' OR 
            u.email LIKE '%$search%' OR 
            e.title LIKE '%$search%'";
    }

    // Get total transactions count
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN events e ON t.event_id = e.id
        $searchCondition
    ";
    $result = mysqli_query($db, $countQuery);
    $totalTransactions = mysqli_fetch_assoc($result)['total'];
    $totalPages = ceil($totalTransactions / $limit);

    // Get transactions for the current page
    $query = "
        SELECT t.*, u.name as username, u.email as user_email, e.title as event_title
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN events e ON t.event_id = e.id
        $searchCondition
        ORDER BY t.created_at DESC
        LIMIT $offset, $limit
    ";

    $result = mysqli_query($db, $query);
    $transactions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }

    return [
        'transactions' => $transactions,
        'total_transactions' => $totalTransactions,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

// Function to get users with pagination
function getUsers($db, $page = 1, $limit = 10, $search = '')
{
    $offset = ($page - 1) * $limit;

    $searchCondition = '';
    if (!empty($search)) {
        $search = mysqli_real_escape_string($db, $search);
        $searchCondition = "WHERE name LIKE '%$search%' OR email LIKE '%$search%'";
    }

    // Get total users count
    $countQuery = "SELECT COUNT(*) as total FROM users $searchCondition";
    $result = mysqli_query($db, $countQuery);
    $totalUsers = mysqli_fetch_assoc($result)['total'];
    $totalPages = ceil($totalUsers / $limit);

    // Get users for the current page
    $query = "
        SELECT id, name, email, status, created_at, last_login
        FROM users
        $searchCondition
        ORDER BY created_at DESC
        LIMIT $offset, $limit
    ";

    $result = mysqli_query($db, $query);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    return [
        'users' => $users,
        'total_users' => $totalUsers,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

// Function to get user details including their payment history
function getUserDetails($db, $userId)
{
    // Get user info
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        return null;
    }

    // Get user transactions
    $query = "
        SELECT t.*, e.title as event_title
        FROM transactions t
        LEFT JOIN events e ON t.event_id = e.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
    ";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $transactions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }

    // Calculate total spent
    $query = "
        SELECT SUM(amount_paid) as total_spent
        FROM transactions
        WHERE user_id = ? AND payment_status = 'completed'
    ";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $totalSpent = mysqli_fetch_assoc($result)['total_spent'] ?? 0;

    return [
        'user' => $user,
        'transactions' => $transactions,
        'total_spent' => $totalSpent
    ];
}