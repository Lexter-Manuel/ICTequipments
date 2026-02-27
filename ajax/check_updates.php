<?php
/**
 * Smart Real-time: Change-check endpoint
 * 
 * Ultra-lightweight endpoint that returns last-modified timestamps
 * per data category. The client polls this every 5-10 seconds.
 * Only when timestamps differ from the client's cache will it
 * trigger a full data refresh — saving ~95% of server load vs
 * naive 1-second polling.
 * 
 * Response format:
 * {
 *   "timestamps": {
 *     "equipment":    "2026-02-27 10:30:45.123",
 *     "employees":    "2026-02-27 10:28:12.456",
 *     "maintenance":  "2026-02-27 10:25:00.000",
 *     ...
 *   },
 *   "server_time": "2026-02-27 10:31:00.000"
 * }
 */

require_once '../config/database.php';
require_once '../config/session-check.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    $db = Database::getInstance()->getConnection();

    // Single lightweight query — the table has at most ~7 rows
    $rows = $db->query("SELECT category, updated_at FROM data_change_tracker")->fetchAll();

    $timestamps = [];
    foreach ($rows as $row) {
        $timestamps[$row['category']] = $row['updated_at'];
    }

    echo json_encode([
        'success'     => true,
        'timestamps'  => $timestamps,
        'server_time' => (new DateTime())->format('Y-m-d H:i:s.v'),
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Change check failed',
    ]);
}
