<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/processes/load_notifications.php';
require_once BASE_PATH . '/Owner/processes/load_employees_status.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/Owner/css/style.css?v=<?= time(); ?>" />
<nav>
    <div class="nav-container">
        <button class="menu-toggle">&#9776;</button>
        <ul class="nav-menu">
            <li>
                <a href="<?= BASE_URL ?>/Owner/index.php" class="<?= ($currentPage == 'index') ? 'active' : '' ?>">
                    <span class="link-text">Home</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Owner/pages/reports.php" class="<?= ($currentPage == 'reports') ? 'active' : '' ?>">
                    <span class="link-text">Report</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Owner/pages/calendar.php" class="<?= ($currentPage == 'calendar') ? 'active' : '' ?>">
                    <span class="link-text">Calendar</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Owner/pages/employees.php" class="<?= ($currentPage == 'employees') ? 'active' : '' ?>">
                    <span class="link-text">Employees</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Owner/pages/services.php" class="<?= ($currentPage == 'services') ? 'active' : '' ?>">
                    <span class="link-text">Services</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Owner/pages/promos.php" class="<?= ($currentPage == 'promos') ? 'active' : '' ?>">
                    <span class="link-text">Promos</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Owner/pages/profile.php" class="<?= ($currentPage == 'profile') ? 'active' : '' ?>">
                    <span class="link-text">Branches</span>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link" id="notifDropdownToggle">
                    <span class="link-text">Notifications</span>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notif-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <div class="notif-dropdown" id="notifDropdown">
                    <h4>Notifications</h4>
                    <ul>
                        <?php if (count($notifications) === 0): ?>
                            <li class="notif-item">No notifications</li>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>" data-id="<?= $n['notification_id'] ?>" >
                                    <span class="notif-message"><?= htmlspecialchars($n['message']) ?></span>
                                    <span class="notif-date"><?= date('M d, Y H:i', strtotime($n['date_created'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <a href="#" id="markAllRead">Mark all as read</a>
                </div>
            </li>
            <li>
                <a href="#" id="logoutLink">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="link-text">Logout</span>
                </a>

                <div id="logoutModal" class="logout-modal">
                    <div class="logout-modal-content">
                        <p>Are you sure you want to log out?</p>
                        <div class="modal-buttons">
                            <button id="confirmLogout">Yes, log out</button>
                            <button id="cancelLogout">Cancel</button>
                        </div>
                    </div>
                </div>
            </li>
            <li class="logged-user">
                Logged in as <span class="colon">:</span> 
                <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
                <span class="dash">-</span> 
                <span class="user-role">
                    <?= htmlspecialchars(ucfirst($_SESSION['role'] ?? '')) ?>
                </span>
            </li>
        </ul>
    </div>
</nav>
