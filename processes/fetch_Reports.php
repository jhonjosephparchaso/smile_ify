<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'owner' && $_SESSION['role'] !== 'admin')) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

try {
    $branch_id = $_GET['branch_id'] ?? null;
    $mode = $_GET['mode'] ?? 'daily';

    if ($mode === 'daily') {
        $startDate = $endDate = date('Y-m-d');
    } elseif ($mode === 'weekly') {
        $startDate = date('Y-m-d', strtotime('monday this week'));
        $endDate   = date('Y-m-d', strtotime('sunday this week'));
    } elseif ($mode === 'monthly') {
        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-t');
    } else {
        $startDate = $endDate = date('Y-m-d');
    }

    $totalServices = 0;
    $totalIncome = 0;
    $baseServiceIncome = 0;
    $extraCharges = 0;
    $topService = "-";
    $newPatients = 0;

    if ($branch_id) {
        
        $sql = "SELECT COUNT(*) AS cnt
                FROM appointment_transaction
                WHERE branch_id = ? AND appointment_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $branch_id, $startDate, $endDate);
        $stmt->execute();
        $totalServices = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        $stmt->close();
        
        $sql = "SELECT 
                    SUM(
                        dt.total 
                        + IFNULL(dt.additional_payment, 0) 
                        + IFNULL(dt.medcert_request_payment, 0)
                    ) AS total_income,

                    SUM(s.price) AS base_service_income,

                    SUM(
                        (dt.total 
                        + IFNULL(dt.additional_payment, 0) 
                        + IFNULL(dt.medcert_request_payment, 0)
                        ) - s.price
                    ) AS extra_charges

                FROM dental_transaction AS dt
                JOIN appointment_transaction AS at 
                    ON dt.appointment_transaction_id = at.appointment_transaction_id
                JOIN appointment_services AS aps
                    ON aps.appointment_transaction_id = at.appointment_transaction_id
                JOIN service AS s 
                    ON aps.service_id = s.service_id
                WHERE at.branch_id = ?
                AND DATE(dt.date_created) BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $branch_id, $startDate, $endDate);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $totalIncome = $res['total_income'] ?? 0;
        $baseServiceIncome = $res['base_service_income'] ?? 0;
        $extraCharges = $res['extra_charges'] ?? 0;

        $sql = "SELECT s.name AS service_name, COUNT(*) AS cnt
                FROM appointment_services AS aps
                JOIN service AS s ON aps.service_id = s.service_id
                JOIN appointment_transaction AS at ON aps.appointment_transaction_id = at.appointment_transaction_id
                WHERE at.branch_id = ? 
                AND at.appointment_date BETWEEN ? AND ?
                GROUP BY s.name
                ORDER BY cnt DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $branch_id, $startDate, $endDate);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $topService = $res['service_name'];
        }
        $stmt->close();
        
        $sql = "SELECT COUNT(*) AS cnt 
                FROM users 
                WHERE branch_id = ? AND DATE(date_created) BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $branch_id, $startDate, $endDate);
        $stmt->execute();
        $newPatients = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        $stmt->close();
    }

    $appointments = ["booked" => 0, "completed" => 0, "cancelled" => 0];
    $sql = "SELECT status, COUNT(*) as cnt 
            FROM appointment_transaction
            WHERE branch_id = ? AND appointment_date BETWEEN ? AND ?
            GROUP BY status";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $branch_id, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[strtolower($row['status'])] = (int)$row['cnt'];
    }
    $stmt->close();

    $servicesTrend = [];
    $incomeTrend = [];
    $labels = [];

    $servicesBreakdownPerDate = [];

    if ($mode === 'daily') {
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $sql = "SELECT s.name AS service_name, SUM(dts.quantity) AS cnt
                    FROM dental_transaction_services dts
                    JOIN dental_transaction dt ON dts.dental_transaction_id = dt.dental_transaction_id
                    JOIN appointment_transaction at ON dt.appointment_transaction_id = at.appointment_transaction_id
                    JOIN service s ON dts.service_id = s.service_id
                    WHERE at.branch_id = ?
                    AND DATE(at.appointment_date) = ?
                    GROUP BY s.service_id
                    ORDER BY cnt DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $branch_id, $day);
            $stmt->execute();
            $result = $stmt->get_result();
            $dayServices = [];
            while ($row = $result->fetch_assoc()) {
                $dayServices[] = $row['service_name'] . " (" . $row['cnt'] . ")";
            }
            $servicesBreakdownPerDate[] = $dayServices;
            $stmt->close();
        }
    } elseif ($mode === 'weekly') {
        $days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
        foreach ($days as $d) {
            $sql = "SELECT s.name AS service_name, SUM(dts.quantity) AS cnt
                    FROM dental_transaction_services dts
                    JOIN dental_transaction dt ON dts.dental_transaction_id = dt.dental_transaction_id
                    JOIN appointment_transaction at ON dt.appointment_transaction_id = at.appointment_transaction_id
                    JOIN service s ON dts.service_id = s.service_id
                    WHERE at.branch_id = ?
                    AND YEARWEEK(at.appointment_date,1) = YEARWEEK(CURDATE(),1)
                    AND DAYNAME(at.appointment_date) = ?
                    GROUP BY s.service_id
                    ORDER BY cnt DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $branch_id, $d);
            $stmt->execute();
            $result = $stmt->get_result();
            $dayServices = [];
            while ($row = $result->fetch_assoc()) {
                $dayServices[] = $row['service_name'] . " (" . $row['cnt'] . ")";
            }
            $servicesBreakdownPerDate[] = $dayServices;
            $stmt->close();
        }
    } elseif ($mode === 'monthly') {
        $daysInMonth = date('t');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sql = "SELECT s.name AS service_name, SUM(dts.quantity) AS cnt
                    FROM dental_transaction_services dts
                    JOIN dental_transaction dt ON dts.dental_transaction_id = dt.dental_transaction_id
                    JOIN appointment_transaction at ON dt.appointment_transaction_id = at.appointment_transaction_id
                    JOIN service s ON dts.service_id = s.service_id
                    WHERE at.branch_id = ?
                    AND YEAR(at.appointment_date) = YEAR(CURDATE())
                    AND MONTH(at.appointment_date) = MONTH(CURDATE())
                    AND DAY(at.appointment_date) = ?
                    GROUP BY s.service_id
                    ORDER BY cnt DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $branch_id, $d);
            $stmt->execute();
            $result = $stmt->get_result();
            $dayServices = [];
            while ($row = $result->fetch_assoc()) {
                $dayServices[] = $row['service_name'] . " (" . $row['cnt'] . ")";
            }
            $servicesBreakdownPerDate[] = $dayServices;
            $stmt->close();
        }
    }

    if ($mode === 'daily') {
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $labels[] = $day;

            $sql = "SELECT COALESCE(
                SUM(
                    dt.total 
                    + IFNULL(dt.additional_payment, 0) 
                    + IFNULL(dt.medcert_request_payment, 0)
                ), 0) AS total
            FROM dental_transaction dt
            JOIN appointment_transaction at
                ON dt.appointment_transaction_id = at.appointment_transaction_id
            WHERE at.branch_id = ?
            AND DATE(at.appointment_date) = ?
            AND at.status = 'Completed'";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $branch_id, $day);
            $stmt->execute();
            $servicesTrend[] = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
            $stmt->close();

            $sql = "SELECT 
                        COALESCE(
                            SUM(
                                dt.total 
                                + IFNULL(dt.additional_payment, 0) 
                                + IFNULL(dt.medcert_request_payment, 0)
                            ), 
                        0) AS total
                    FROM dental_transaction dt
                    JOIN appointment_transaction at 
                        ON dt.appointment_transaction_id = at.appointment_transaction_id
                    WHERE at.branch_id = ? 
                    AND DATE(at.appointment_date) = ?
                    AND at.status = 'Completed'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $branch_id, $day);
            $stmt->execute();
            $incomeTrend[] = (float)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
            $stmt->close();
        }
    } elseif ($mode === 'weekly') {
        $days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
        foreach ($days as $d) {
            $labels[] = $d;

            $sql = "SELECT COALESCE(SUM(dts.quantity),0) AS cnt
                    FROM dental_transaction_services dts
                    JOIN dental_transaction dt ON dts.dental_transaction_id = dt.dental_transaction_id
                    JOIN appointment_transaction at ON dt.appointment_transaction_id = at.appointment_transaction_id
                    WHERE at.branch_id = ? 
                    AND YEARWEEK(at.appointment_date,1) = YEARWEEK(CURDATE(),1)
                    AND DAYNAME(at.appointment_date) = ?
                    AND at.status = 'Completed'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $branch_id, $d);
            $stmt->execute();
            $servicesTrend[] = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
            $stmt->close();

            $sql = "SELECT 
                        COALESCE(
                            SUM(
                                dt.total 
                                + IFNULL(dt.additional_payment, 0) 
                                + IFNULL(dt.medcert_request_payment, 0)
                            ), 
                        0) AS total
                    FROM dental_transaction dt
                    JOIN appointment_transaction at 
                        ON dt.appointment_transaction_id = at.appointment_transaction_id
                    WHERE at.branch_id = ? 
                    AND YEARWEEK(at.appointment_date, 1) = YEARWEEK(CURDATE(), 1)
                    AND DAYNAME(at.appointment_date) = ?
                    AND at.status = 'Completed'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $branch_id, $d);
            $stmt->execute();
            $incomeTrend[] = (float)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
            $stmt->close();
        }
    } elseif ($mode === 'monthly') {
        $daysInMonth = date('t');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[] = $d;

            $sql = "SELECT COALESCE(SUM(dts.quantity),0) AS cnt
                    FROM dental_transaction_services dts
                    JOIN dental_transaction dt ON dts.dental_transaction_id = dt.dental_transaction_id
                    JOIN appointment_transaction at ON dt.appointment_transaction_id = at.appointment_transaction_id
                    WHERE at.branch_id = ? 
                    AND YEAR(at.appointment_date) = YEAR(CURDATE()) 
                    AND MONTH(at.appointment_date) = MONTH(CURDATE()) 
                    AND DAY(at.appointment_date) = ?
                    AND at.status = 'Completed'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $branch_id, $d);
            $stmt->execute();
            $servicesTrend[] = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
            $stmt->close();

            $sql = "SELECT 
                        COALESCE(
                            SUM(
                                dt.total 
                                + IFNULL(dt.additional_payment, 0) 
                                + IFNULL(dt.medcert_request_payment, 0)
                            ), 
                        0) AS total
                    FROM dental_transaction dt
                    JOIN appointment_transaction at 
                        ON dt.appointment_transaction_id = at.appointment_transaction_id
                    WHERE at.branch_id = ? 
                    AND YEAR(at.appointment_date) = YEAR(CURDATE()) 
                    AND MONTH(at.appointment_date) = MONTH(CURDATE()) 
                    AND DAY(at.appointment_date) = ?
                    AND at.status = 'Completed'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $branch_id, $d);
            $stmt->execute();
            $incomeTrend[] = (float)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
            $stmt->close();
        }
    }

    $branchComparison = [];
    $sql = "SELECT 
                b.name, 
                SUM(
                    dt.total 
                    + IFNULL(dt.additional_payment, 0) 
                    + IFNULL(dt.medcert_request_payment, 0)
                ) AS total_income
            FROM dental_transaction AS dt
            JOIN appointment_transaction AS at 
                ON dt.appointment_transaction_id = at.appointment_transaction_id
            JOIN branch AS b 
                ON at.branch_id = b.branch_id
            WHERE DATE(dt.date_created) BETWEEN ? AND ?
            GROUP BY b.branch_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $branchComparison[$row['name']] = (float)$row['total_income'];
    }
    $stmt->close();

    $servicePrices = [];
    $sql = "
        SELECT 
            s.name AS service,
            s.price AS price
        FROM branch_service AS bs
        INNER JOIN service AS s ON bs.service_id = s.service_id
        WHERE bs.branch_id = ?
        AND bs.status = 'active'
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $servicePrices[] = [
            'service' => $row['service'],
            'price'   => number_format($row['price'], 2)
        ];
    }
    $stmt->close();
    
    $growthTrend = [
        'current' => [],
        'previous' => []
    ];

    if ($_SESSION['role'] === 'owner') {
        if ($mode === 'daily') {
            $currStart = $currEnd = date('Y-m-d');
            $prevStart = $prevEnd = date('Y-m-d', strtotime('-1 day'));
        } elseif ($mode === 'weekly') {
            $currStart = date('Y-m-d', strtotime('monday this week'));
            $currEnd   = date('Y-m-d', strtotime('sunday this week'));
            $prevStart = date('Y-m-d', strtotime('monday last week'));
            $prevEnd   = date('Y-m-d', strtotime('sunday last week'));
        } else {
            $currStart = date('Y-m-01');
            $currEnd   = date('Y-m-t');
            $prevStart = date('Y-m-01', strtotime('-1 month'));
            $prevEnd   = date('Y-m-t', strtotime('-1 month'));
        }

        $periodSql = "
            SELECT
                DATE(at.appointment_date) AS period,
                SUM(
                    dt.total
                    + IFNULL(dt.additional_payment, 0)
                    + IFNULL(dt.medcert_request_payment, 0)
                ) AS revenue
            FROM appointment_transaction AS at
            JOIN dental_transaction AS dt
                ON at.appointment_transaction_id = dt.appointment_transaction_id
            WHERE at.branch_id = ?
            AND DATE(at.appointment_date) BETWEEN ? AND ?
            AND at.status = 'Completed'
            GROUP BY period
            ORDER BY period ASC
        ";

        $stmt = $conn->prepare($periodSql);
        $stmt->bind_param("iss", $branch_id, $currStart, $currEnd);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $growthTrend['current'][$r['period']] = (float)$r['revenue'];
        }
        $stmt->close();

        $stmt = $conn->prepare($periodSql);
        $stmt->bind_param("iss", $branch_id, $prevStart, $prevEnd);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            if ($mode === 'weekly') {
                $offsetDate = date('Y-m-d', strtotime($r['period'] . ' +7 days'));
                $growthTrend['previous'][$offsetDate] = (float)$r['revenue'];
            } elseif ($mode === 'monthly') {
                $prevDate = strtotime($r['period']);
                $prevDay = date('d', $prevDate);
                $currDate = strtotime(date('Y-m-01'));
                $daysInCurrMonth = date('t', $currDate);
                
                if ($prevDay <= $daysInCurrMonth) {
                    $offsetDate = date('Y-m-' . str_pad($prevDay, 2, '0', STR_PAD_LEFT), $currDate);
                    $growthTrend['previous'][$offsetDate] = (float)$r['revenue'];
                }
            } else {
                $offsetDate = date('Y-m-d', strtotime($r['period'] . ' +1 day'));
                $growthTrend['previous'][$offsetDate] = (float)$r['revenue'];
            }
        }
        $stmt->close();

        $allPeriods = array_keys($growthTrend['current']);
        foreach ($allPeriods as $period) {
            if (!isset($growthTrend['previous'][$period])) {
                $growthTrend['previous'][$period] = 0;
            }
        }
        $ordered = [];
        foreach ($allPeriods as $period) {
            $ordered[$period] = $growthTrend['previous'][$period];
        }
        $growthTrend['previous'] = $ordered;
    }

    $avgRevPerAppt    = 0;
    $avgRevPerPatient = 0;
    $apptCount        = 0;
    $patientCount     = 0;
    $totalRevenue     = 0;

    if ($_SESSION['role'] === 'owner') {
        $sql = "
            SELECT
                COUNT(*) AS appt_count,
                COUNT(DISTINCT at.user_id) AS patient_count,
                SUM(dt.total + IFNULL(dt.additional_payment,0) + IFNULL(dt.medcert_request_payment,0)) AS total_revenue,
                ROUND(SUM(dt.total + IFNULL(dt.additional_payment,0) + IFNULL(dt.medcert_request_payment,0)) / COUNT(*), 2) AS avg_revenue_per_appointment,
                ROUND(SUM(dt.total + IFNULL(dt.additional_payment,0) + IFNULL(dt.medcert_request_payment,0)) / COUNT(DISTINCT at.user_id), 2) AS avg_revenue_per_patient
            FROM appointment_transaction AS at
            JOIN dental_transaction AS dt
                ON at.appointment_transaction_id = dt.appointment_transaction_id
            WHERE DATE(at.appointment_date) BETWEEN ? AND ?
            AND at.branch_id = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $currStart, $currEnd, $branch_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $apptCount        = (int) $row['appt_count'];
        $patientCount     = (int) $row['patient_count'];
        $totalRevenue     = (float) $row['total_revenue'];
        $avgRevPerAppt    = (float) $row['avg_revenue_per_appointment'];
        $avgRevPerPatient = (float) $row['avg_revenue_per_patient'];
    }

    $branchGrowthChartData = [];
    if ($_SESSION['role'] === 'owner' && $branch_id === 'all') {
        
        $branchesQuery = "SELECT branch_id, name FROM branch WHERE status IN ('active', 'inactive') ORDER BY branch_id";
        $branchesResult = $conn->query($branchesQuery);
        $allBranches = [];
        while ($b = $branchesResult->fetch_assoc()) {
            $allBranches[] = $b;
        }
        
        if ($mode === 'daily') {
            $labels = [date('Y-m-d')];
            $datasets = [];
            
            foreach ($allBranches as $branch) {
                $sql = "
                    SELECT COALESCE(SUM(dt.total + IFNULL(dt.additional_payment,0) + IFNULL(dt.medcert_request_payment,0)), 0) AS revenue
                    FROM appointment_transaction AS at
                    LEFT JOIN dental_transaction AS dt 
                        ON at.appointment_transaction_id = dt.appointment_transaction_id
                    WHERE at.branch_id = ? 
                    AND DATE(at.appointment_date) = ?
                    AND at.status = 'Completed'
                ";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $branch['branch_id'], $startDate);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $revenue = (float)$row['revenue'];
                $stmt->close();
                
                $datasets[] = [
                    'label' => $branch['name'],
                    'data' => [$revenue],
                    'branch_id' => $branch['branch_id']
                ];
            }
            
            $branchGrowthChartData = [
                'labels' => $labels,
                'datasets' => $datasets
            ];
            
        } elseif ($mode === 'weekly') {
            $labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $datasets = [];
            
            foreach ($allBranches as $branch) {
                $branchData = [];
                
                for ($i = 0; $i < 7; $i++) {
                    $date = date('Y-m-d', strtotime($weekStart . " +$i days"));
                    
                    $sql = "
                        SELECT COALESCE(SUM(
                            dt.total + 
                            IFNULL(dt.additional_payment, 0) + 
                            IFNULL(dt.medcert_request_payment, 0)
                        ), 0) AS revenue
                        FROM appointment_transaction AS at
                        LEFT JOIN dental_transaction AS dt 
                            ON at.appointment_transaction_id = dt.appointment_transaction_id
                        WHERE at.branch_id = ? 
                        AND DATE(at.appointment_date) = ?
                        AND at.status = 'Completed'
                    ";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $branch['branch_id'], $date);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $revenue = (float)$row['revenue'];
                    $stmt->close();
                    
                    $branchData[] = $revenue;
                }
                
                $datasets[] = [
                    'label' => $branch['name'],
                    'data' => $branchData,
                    'branch_id' => $branch['branch_id']
                ];
            }
            
            $branchGrowthChartData = [
                'labels' => $labels,
                'datasets' => $datasets
            ];
            
        } elseif ($mode === 'monthly') {
            $currentYear = date('Y');
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $datasets = [];
            
            foreach ($allBranches as $branch) {
                $branchData = [];
                
                for ($month = 1; $month <= 12; $month++) {
                    $monthStart = date("$currentYear-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01");
                    $monthEnd = date("Y-m-t", strtotime($monthStart));
                    
                    $sql = "
                        SELECT COALESCE(SUM(
                            dt.total + 
                            IFNULL(dt.additional_payment, 0) + 
                            IFNULL(dt.medcert_request_payment, 0)
                        ), 0) AS revenue
                        FROM appointment_transaction AS at
                        LEFT JOIN dental_transaction AS dt 
                            ON at.appointment_transaction_id = dt.appointment_transaction_id
                        WHERE at.branch_id = ? 
                        AND DATE(at.appointment_date) BETWEEN ? AND ?
                        AND at.status = 'Completed'
                    ";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $branch['branch_id'], $monthStart, $monthEnd);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $revenue = (float)$row['revenue'];
                    $stmt->close();
                    
                    $branchData[] = $revenue;
                }
                
                $datasets[] = [
                    'label' => $branch['name'],
                    'data' => $branchData,
                    'branch_id' => $branch['branch_id']
                ];
            }
            
            $branchGrowthChartData = [
                'labels' => $labels,
                'datasets' => $datasets
            ];
        }
    }

    $branchGrowthData = [];
    $totalRevenue = 0;

    foreach ($branchGrowthChartData['datasets'] as $ds) {
        $branchRevenue = array_sum($ds['data']);
        $totalRevenue += $branchRevenue;
    }

    foreach ($branchGrowthChartData['datasets'] as $ds) {
        $branchRevenue = array_sum($ds['data']);
        $percentage = $totalRevenue > 0 ? round(($branchRevenue / $totalRevenue) * 100, 2) : 0;

        $branchGrowthData[] = [
            'branch_name' => $ds['label'],
            'revenue' => $branchRevenue,
            'percentage' => $percentage
        ];
    }

    $declineData = [];
    if ($_SESSION['role'] === 'owner') {
        if ($mode === 'daily') {
            $currStart = $currEnd = date('Y-m-d');
            $prevStart = $prevEnd = date('Y-m-d', strtotime('-1 day'));
        } elseif ($mode === 'weekly') {
            $currStart = date('Y-m-d', strtotime('monday this week'));
            $currEnd   = date('Y-m-d', strtotime('sunday this week'));
            $prevStart = date('Y-m-d', strtotime('monday last week'));
            $prevEnd   = date('Y-m-d', strtotime('sunday last week'));
        } else {
            $currStart = date('Y-m-01');
            $currEnd   = date('Y-m-t');
            $prevStart = date('Y-m-01', strtotime('-1 month'));
            $prevEnd   = date('Y-m-t', strtotime('-1 month'));
        }

        $sql = "
            SELECT
                b.branch_id,
                b.name AS branch_name,
                COALESCE(prev_counts.count, 0) AS previous_count,
                COALESCE(curr_counts.count, 0) AS current_count,
                GREATEST(0, COALESCE(prev_counts.count, 0) - COALESCE(curr_counts.count, 0)) AS decline,
                CASE 
                    WHEN COALESCE(prev_counts.count, 0) > 0 THEN 
                        ROUND(((COALESCE(prev_counts.count, 0) - COALESCE(curr_counts.count, 0)) / COALESCE(prev_counts.count, 0) * 100), 2)
                    ELSE 0
                END AS percentage_decline
            FROM branch AS b
            LEFT JOIN (
                SELECT
                    branch_id,
                    COUNT(*) AS count
                FROM appointment_transaction AS at
                WHERE DATE(at.appointment_date) BETWEEN ? AND ?
                AND at.status = 'Completed'
                GROUP BY branch_id
            ) AS curr_counts ON curr_counts.branch_id = b.branch_id
            LEFT JOIN (
                SELECT
                    branch_id,
                    COUNT(*) AS count
                FROM appointment_transaction AS at
                WHERE DATE(at.appointment_date) BETWEEN ? AND ?
                AND at.status = 'Completed'
                GROUP BY branch_id
            ) AS prev_counts ON prev_counts.branch_id = b.branch_id
            WHERE b.status = 'active'
            ORDER BY decline DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $currStart, $currEnd, $prevStart, $prevEnd);
        $stmt->execute();
        $result = $stmt->get_result();

        $totalDecline = 0;
        $rows = [];
        while ($r = $result->fetch_assoc()) {
            $rows[] = $r;
            $totalDecline += $r['decline'];
        }
        
        foreach ($rows as $r) {
            $pct = $totalDecline > 0 
                ? round($r['decline'] / $totalDecline * 100, 2) 
                : 0;
            $declineData[] = [
                'branch_id'      => (int)$r['branch_id'],
                'branch_name'    => $r['branch_name'],
                'previous_count' => (int)$r['previous_count'],
                'current_count'  => (int)$r['current_count'],
                'decline'        => (int)$r['decline'],
                'percentage'     => $pct
            ];
        }
        $stmt->close();
    }

    $staffPerformance = [];
    $sql = "
        SELECT
            CONCAT(d.first_name, ' ', d.last_name) AS dentist_name,
            b.name AS branch_name,
            COUNT(dt.dental_transaction_id) AS services_rendered,
            SUM(
                dt.total + 
                IFNULL(dt.additional_payment, 0) + 
                IFNULL(dt.medcert_request_payment, 0)
            ) AS total_income
        FROM dental_transaction AS dt
        JOIN appointment_transaction AS at 
            ON dt.appointment_transaction_id = at.appointment_transaction_id
        JOIN dentist AS d 
            ON dt.dentist_id = d.dentist_id
        JOIN branch AS b 
            ON at.branch_id = b.branch_id
        WHERE at.branch_id = ? 
        AND DATE(dt.date_created) BETWEEN ? AND ?
        AND at.status = 'Completed'
        GROUP BY d.dentist_id, b.branch_id
        ORDER BY total_income DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $branch_id, $startDate, $endDate); 
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $staffPerformance[] = $row;
    }
    $stmt->close();

    $sql = "
        SELECT 
            s.name AS service,
            SUM(dts.quantity) AS service_count,
            ROUND(
                (SUM(dts.quantity) / NULLIF((
                    SELECT SUM(dts2.quantity)
                    FROM dental_transaction_services AS dts2
                    INNER JOIN dental_transaction AS dt2 ON dts2.dental_transaction_id = dt2.dental_transaction_id
                    INNER JOIN appointment_transaction AS at2 ON dt2.appointment_transaction_id = at2.appointment_transaction_id
                    WHERE at2.branch_id = ?
                    AND DATE(dt2.date_created) BETWEEN ? AND ?
                ), 0)) * 100, 2
            ) AS percent_total
        FROM dental_transaction_services AS dts
        INNER JOIN dental_transaction AS dt ON dts.dental_transaction_id = dt.dental_transaction_id
        INNER JOIN appointment_transaction AS at ON dt.appointment_transaction_id = at.appointment_transaction_id
        INNER JOIN service AS s ON dts.service_id = s.service_id
        WHERE at.branch_id = ?
        AND DATE(dt.date_created) BETWEEN ? AND ?
        GROUP BY s.service_id
        ORDER BY service_count DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ississ",
        $branch_id, 
        $startDate,
        $endDate,
        $branch_id,
        $startDate,
        $endDate
    );
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $servicesBreakdown[] = [
            'service' => $row['service'],
            'service_count' => (int)$row['service_count'],
            'percent_total' => (float)$row['percent_total']
        ];
    }
    $stmt->close();

    $promosAvailed = [];
    $sql = "SELECT 
        p.name AS promo_name,
        COUNT(dt.dental_transaction_id) AS promo_count,
        ROUND(
            (COUNT(dt.dental_transaction_id) / NULLIF((
                SELECT COUNT(dt2.dental_transaction_id)
                FROM dental_transaction AS dt2
                INNER JOIN appointment_transaction AS at2 ON dt2.appointment_transaction_id = at2.appointment_transaction_id
                WHERE at2.branch_id = ?
                AND DATE(dt2.date_created) BETWEEN ? AND ?
                AND dt2.promo_id IS NOT NULL
            ), 0)) * 100, 2
        ) AS percent_total
    FROM dental_transaction AS dt
    INNER JOIN appointment_transaction AS at ON dt.appointment_transaction_id = at.appointment_transaction_id
    INNER JOIN promo AS p ON dt.promo_id = p.promo_id
    WHERE at.branch_id = ? 
    AND DATE(dt.date_created) BETWEEN ? AND ?
    AND dt.promo_id IS NOT NULL
    GROUP BY p.promo_id
    ORDER BY promo_count DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ississ", $branch_id, $startDate, $endDate, $branch_id, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $promosAvailed[] = [
            'promo_name' => $row['promo_name'],
            'promo_count' => (int)$row['promo_count'],
            'percent_total' => (float)$row['percent_total']
        ];
    }
    $stmt->close();

    $patientMix = ["new" => 0, "returning" => 0];
    $sql = "SELECT COUNT(*) AS new_patients 
            FROM users 
            WHERE branch_id = ? 
            AND DATE(date_created) BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $branch_id, $startDate, $endDate);
    $stmt->execute();
    $patientMix['new'] = (int)$stmt->get_result()->fetch_assoc()['new_patients'];
    $stmt->close();

    $sql = "SELECT COUNT(DISTINCT a.user_id) AS returning_patients
            FROM appointment_transaction AS a
            JOIN users AS u ON a.user_id = u.user_id
            WHERE a.branch_id = ?
            AND a.appointment_date BETWEEN ? AND ?
            AND DATE(u.date_created) < ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $branch_id, $startDate, $endDate, $startDate);
    $stmt->execute();
    $patientMix['returning'] = (int)$stmt->get_result()->fetch_assoc()['returning_patients'];
    $stmt->close();

    $peakHours = [];
    $sql = "
        SELECT
            CONCAT(LPAD(HOUR(at.appointment_time), 2, '0'), ':00') AS hourslot,
            COUNT(*) AS totalappointments
        FROM appointment_transaction AS at
        WHERE at.branch_id = ?
        AND DATE(at.appointment_date) BETWEEN ? AND ?
        GROUP BY HOUR(at.appointment_time)
        ORDER BY hourslot ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $branch_id, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $peakHours[] = [
            'hour'  => $row['hourslot'],
            'count' => (int)$row['totalappointments']
        ];
    }
    $stmt->close();
    
    $avgServices = 0;
    $dateCondition = "";
    if ($mode === 'daily') {
        $dateCondition = "DATE(at.appointment_date) = CURDATE()";
    } elseif ($mode === 'weekly') {
        $dateCondition = "YEAR(at.appointment_date) = YEAR(CURDATE()) AND WEEK(at.appointment_date, 1) = WEEK(CURDATE(), 1)";
    } elseif ($mode === 'monthly') {
        $dateCondition = "YEAR(at.appointment_date) = YEAR(CURDATE()) AND MONTH(at.appointment_date) = MONTH(CURDATE())";
    }

    $avgQuery = $conn->prepare("
        SELECT AVG(service_count) AS avgServices
        FROM (
            SELECT at.appointment_transaction_id, COUNT(dts.service_id) AS service_count
            FROM appointment_transaction at
            JOIN dental_transaction dt ON dt.appointment_transaction_id = at.appointment_transaction_id
            JOIN dental_transaction_services dts ON dts.dental_transaction_id = dt.dental_transaction_id
            WHERE at.branch_id = ? AND at.status = 'completed' AND $dateCondition
            GROUP BY at.appointment_transaction_id
        ) AS sub
    ");
    $avgQuery->bind_param("i", $branch_id);
    $avgQuery->execute();
    $avgResult = $avgQuery->get_result()->fetch_assoc();
    if ($avgResult && $avgResult['avgServices'] !== null) {
        $avgServices = round((float)$avgResult['avgServices'], 2);
    }
    $kpi['avgServices'] = $avgServices;
    $conn->close();

    if ($_POST['action'] === 'fetchServicesBreakdown') {
    $branchID = $_POST['branchID'];
    $mode = $_POST['mode'];

    $dateFilter = "";
    if ($mode === 'daily') {
    $dateFilter = "DATE(at.appointment_date) = CURDATE()";
    } elseif ($mode === 'weekly') {
    $dateFilter = "YEARWEEK(at.appointment_date, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($mode === 'monthly') {
    $dateFilter = "MONTH(at.appointment_date) = MONTH(CURDATE()) AND YEAR(at.appointment_date) = YEAR(CURDATE())";
    }
    $query = "
        SELECT 
            s.name AS serviceName,
            COUNT(dts.id) AS serviceCount
        FROM dental_transaction_services dts
        JOIN dental_transaction dt ON dts.dental_transaction_id = dt.dental_transaction_id
        JOIN appointment_transaction at ON dt.appointment_transaction_id = at.appointment_transaction_id
        JOIN service s ON dts.service_id = s.service_id
        WHERE at.branch_id = ? AND $dateFilter
        GROUP BY s.service_id
        ORDER BY serviceCount DESC
        ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $branchID);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    $totalServices = 0;
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
        $totalServices += $row['serviceCount'];
    }

    foreach ($data as &$row) {
        $row['percentage'] = $totalServices > 0 ? round(($row['serviceCount'] / $totalServices) * 100, 2) : 0;
    }

    echo json_encode(['data' => $data]);
        exit;
    }

    echo json_encode([
        "kpi" => [
            "totalServices" => $totalServices,
            "totalIncome" => $totalIncome,
            "baseServiceIncome" => $baseServiceIncome,
            "extraCharges" => $extraCharges,
            "topService" => $topService,
            "newPatients" => $newPatients,
            "avgServices" => $avgServices
        ],
        "appointments" => $appointments,
        "trend" => [
            "labels" => $labels,
            "services" => $servicesTrend,
            "income" => $incomeTrend,
            "servicesBreakdown" => $servicesBreakdownPerDate
        ],
        "branchComparison" => $branchComparison,
        'servicePrices'     => $servicePrices,
        "staffPerformance" => $staffPerformance,
        "servicesBreakdown" => $servicesBreakdown, 
        'growthTrend'          => $growthTrend,
        'apptCount'            => $apptCount,
        'patientCount'         => $patientCount,
        'totalRevenue'         => $totalRevenue,
        'avgRevPerAppt'    => $avgRevPerAppt,
        'avgRevPerPatient'=> $avgRevPerPatient,
        'promosAvailed' => $promosAvailed,
        "patientMix" => $patientMix,
        "branchGrowthData" => $branchGrowthData,
        "branchGrowthChartData" => $branchGrowthChartData,
        "declineData" => $declineData,
        "peakHours" => $peakHours
    ]);

} catch (Throwable $e) {
    echo json_encode([
        "error" => "Server error",
        "details" => $e->getMessage()
    ]);
    exit;
}
?>