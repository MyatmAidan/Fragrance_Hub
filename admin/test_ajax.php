<?php
// Simple test to verify AJAX functionality
header('Content-Type: text/html; charset=utf-8');

$test_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

echo '<div class="alert alert-success">';
echo '<h6>✅ AJAX Test Successful</h6>';
echo '<p>Receipt ID: <strong>' . $test_id . '</strong></p>';
echo '<p>Server time: ' . date('Y-m-d H:i:s') . '</p>';
echo '<p>This confirms that AJAX calls are working properly.</p>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-body">';
echo '<h6 class="text-primary">Test Results</h6>';
echo '<ul>';
echo '<li>✅ AJAX request received</li>';
echo '<li>✅ PHP processing successful</li>';
echo '<li>✅ Response generated</li>';
echo '<li>✅ Data returned: ID = ' . $test_id . '</li>';
echo '</ul>';
echo '</div>';
echo '</div>';
