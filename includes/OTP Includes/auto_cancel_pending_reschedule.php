<?php
if (!isset($conn)) return;

$conn->query("
    UPDATE appointment_transaction
    SET status = 'Cancelled',
        date_updated = NOW()
    WHERE status = 'Pending Reschedule'
        AND date_updated <= NOW() - INTERVAL 24 HOUR
");
