<?php
require_once 'config.php';

echo "<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
.success { color: #22c55e; }
.error { color: #ef4444; }
.warning { color: #f59e0b; }
.test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h1 { color: #1f2937; }
h2 { color: #374151; margin-top: 30px; }
ul { background: #f8fafc; padding: 15px; border-radius: 5px; }
li { margin: 5px 0; }
a { color: #3b82f6; text-decoration: none; padding: 10px 15px; background: #eff6ff; border-radius: 5px; display: inline-block; margin: 5px; }
a:hover { background: #dbeafe; }
</style>";

try {
    $pdo = getDBConnection();
    echo "<div class='test-section'>";
    echo "<h1 class='success'>✅ Database Connection Successful!</h1>";
    echo "<p><strong>Connected to:</strong> " . DB_HOST . " / " . DB_NAME . "</p>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>PDO MySQL Available:</strong> " . (extension_loaded('pdo_mysql') ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
    echo "</div>";

    // Test if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<div class='test-section'>";
    echo "<h2>📋 Database Tables (" . count($tables) . " found):</h2>";
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠️ No tables found. Make sure you imported database_schema.sql</p>";
    }
    echo "</div>";

    // Test basic query
    echo "<div class='test-section'>";
    echo "<h2>🔍 Database Query Test:</h2>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
        $result = $stmt->fetch();
        echo "<p class='success'>✅ Users table query successful! Current users: " . $result['user_count'] . "</p>";
    } catch (Exception $e) {
        echo "<p class='warning'>⚠️ Users table not found or query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";

    echo "<div class='test-section'>";
    echo "<h2>🚀 Next Steps:</h2>";
    echo "<p>Database connection is working! Now test the authentication:</p>";
    echo "<a href='signup.php'>📝 Test User Registration</a>";
    echo "<a href='login.php'>🔐 Test User Login</a>";
    echo "<a href='index.php'>🏠 Back to Homepage</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='test-section'>";
    echo "<h1 class='error'>❌ Database Connection Failed!</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";

    echo "<h2>🔧 Troubleshooting Steps:</h2>";
    echo "<ol>";
    echo "<li>Make sure XAMPP Apache and MySQL are running</li>";
    echo "<li>Check that database '" . DB_NAME . "' exists in phpMyAdmin</li>";
    echo "<li>Verify database credentials in config.php</li>";
    echo "<li>Import database_schema.sql into your database</li>";
    echo "</ol>";

    echo "<p><strong>Current config:</strong></p>";
    echo "<ul>";
    echo "<li>Host: " . DB_HOST . "</li>";
    echo "<li>Database: " . DB_NAME . "</li>";
    echo "<li>Username: " . DB_USER . "</li>";
    echo "<li>Password: " . (DB_PASS ? 'Set' : 'Empty (XAMPP default)') . "</li>";
    echo "</ul>";
    echo "</div>";
}
?>
