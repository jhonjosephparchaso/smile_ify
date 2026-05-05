<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No supply ID provided"]);
    exit();
}

$branch_id = $_SESSION['branch_id'] ?? null;
$supplyId = intval($_GET['id']);

if (!$branch_id) {
    echo json_encode(["error" => "Branch not set"]);
    exit();
}

try {
    $sql = "SELECT 
                s.supply_id,
                s.name,
                s.description,
                s.category,
                s.unit,
                bs.quantity,
                bs.reorder_level,
                bs.expiration_date,
                bs.status,
                bs.date_created,
                bs.date_updated
            FROM supply s
            INNER JOIN branch_supply bs ON s.supply_id = bs.supply_id
            WHERE s.supply_id = ? AND bs.branch_id = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $supplyId, $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $serviceSql = "SELECT 
                            ss.service_id,
                            sv.name AS service_name,
                            ss.quantity_used,
                            ss.date_created,
                            ss.date_updated
                        FROM service_supplies ss
                        INNER JOIN service sv ON ss.service_id = sv.service_id
                        WHERE ss.supply_id = ? AND ss.branch_id = ?";

        $stmtServices = $conn->prepare($serviceSql);
        $stmtServices->bind_param("ii", $supplyId, $branch_id);
        $stmtServices->execute();
        $servicesResult = $stmtServices->get_result();

        $services = [];
        $latestServiceUpdate = $row['date_updated'];

        while ($srv = $servicesResult->fetch_assoc()) {
            $services[] = [
                "service_id"     => (int)$srv["service_id"],
                "service_name"   => $srv["service_name"],
                "quantity_used"  => (int)$srv["quantity_used"],
                "date_created"   => $srv["date_created"],
                "date_updated"   => $srv["date_updated"]
            ];

            if (!empty($srv["date_updated"]) && $srv["date_updated"] > $latestServiceUpdate) {
                $latestServiceUpdate = $srv["date_updated"];
            }
        }

        $stmtServices->close();

        $row["service"] = $services;
        $row["latest_update"] = $latestServiceUpdate;

        echo json_encode($row, JSON_UNESCAPED_UNICODE);

    } else {
        echo json_encode(["error" => "Supply not found"]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
