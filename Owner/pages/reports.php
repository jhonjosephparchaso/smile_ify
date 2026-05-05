<?php
session_start();

$currentPage = 'reports';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Owner/includes/navbar.php';
require_once BASE_PATH . '/includes/db.php';

$sql = "SELECT branch_id, nickname, address, phone_number, status FROM branch";
$result = $conn->query($sql);

$branches = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
}
$conn->close();
?>

<title>Reports</title>

<div class="tabs-container">
    <div class="tabs-nomargin">
        <div 
            class="tab active" 
            data-branch="branchall" 
            onclick="switchTab('branchall')">
            All Branches
        </div>

        <?php foreach ($branches as $branch): ?>
            <div 
                class="tab" 
                data-branch="branch<?= $branch['branch_id'] ?>"
                onclick="switchTab('branch<?= $branch['branch_id'] ?>')">
                <?= htmlspecialchars($branch['nickname']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php 
    $modes = ['daily', 'weekly', 'monthly']; 
    ?>

    <div class="tab-content active" id="branchall">
        <div class="sub-tabs">
            <?php foreach ($modes as $j => $mode): ?>
                <div 
                    class="sub-tab <?= $j === 0 ? 'active' : '' ?>" 
                    data-mode="<?= $mode ?>"
                    onclick="switchSubTab('all', '<?= $mode ?>')">
                    <?= strtoupper($mode) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($modes as $j => $mode): ?>
            <div 
                class="sub-tab-content <?= $j === 0 ? 'active' : '' ?>" 
                id="branchall-<?= $mode ?>">
                <?php 
                    $currentMode = $mode;
                    $branchId = 'all';
                    $role = $_SESSION['role'] ?? '';

                    if ($role === 'owner') {
                        ?>
                        <h2>Branch Growth Tracker</h2>
                        <div class="staff-performance">
                            <div class="branch-growth-grid">
                                <div class="branch-growth-list">
                                    <h4 class="branch-growth-tracker-text">Branch Revenue Comparison</h4>
                                    <table class="branch-growth-table">
                                        <thead>
                                            <tr><th>Branch</th><th style="text-align: right;">Revenue (₱)</th><th style="text-align: right;">Contribution (%)</th></tr>
                                        </thead>
                                        <tbody id="branchGrowthTableBody<?= $branchId ?>-<?= $mode ?>"></tbody>
                                    </table>
                                    <div class="branch-growth-chart">
                                        <h4>Revenue by Period</h4>
                                        <canvas id="branchGrowthChartall-<?= $mode ?>"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php foreach ($branches as $branch): ?>
        <div 
            class="tab-content" 
            id="branch<?= $branch['branch_id'] ?>">

            <div class="sub-tabs">
                <?php foreach ($modes as $j => $mode): ?>
                    <div 
                        class="sub-tab <?= $j === 0 ? 'active' : '' ?>" 
                        data-mode="<?= $mode ?>"
                        onclick="switchSubTab(<?= $branch['branch_id'] ?>, '<?= $mode ?>')">
                        <?= strtoupper($mode) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php foreach ($modes as $j => $mode): ?>
                <div 
                    class="sub-tab-content <?= $j === 0 ? 'active' : '' ?>" 
                    id="branch<?= $branch['branch_id'] ?>-<?= $mode ?>">
                    <?php 
                        $currentMode = $mode;
                        $branchId = $branch['branch_id'];
                        include BASE_PATH . "/includes/reportSection.php"; 
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
