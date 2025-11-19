<?php
/**
 * Script to fix all PHP files to ensure buttons and forms work
 * This will update all pages to load scripts correctly
 */

$files = [
    'student_login.php',
    'student_signup.php',
    'add_classes.php',
    'add_students.php',
    'add_results.php',
    'manage_results.php',
    'manage_classes.php',
    'manage_students.php',
    'dashboard.php',
    'student_dashboard.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Remove toast.js from head if present
        $content = preg_replace('/<script[^>]*src=["\']\.\/js\/toast\.js["\'][^>]*><\/script>\s*/i', '', $content);
        
        // Ensure toast.js is loaded before closing body tag
        if (strpos($content, '</body>') !== false && strpos($content, 'js/toast.js') === false) {
            $content = str_replace('</body>', '    <script src="./js/toast.js"></script>' . "\n</body>", $content);
        }
        
        // Wrap inline scripts in DOMContentLoaded
        $pattern = '/<script>(.*?)<\/script>/s';
        $content = preg_replace_callback($pattern, function($matches) {
            $scriptContent = $matches[1];
            // Skip if already wrapped or is external script
            if (strpos($scriptContent, 'DOMContentLoaded') !== false || 
                strpos($scriptContent, 'src=') !== false) {
                return $matches[0];
            }
            // Wrap in DOMContentLoaded
            return '<script>
        document.addEventListener("DOMContentLoaded", function() {
            ' . $scriptContent . '
        });
    </script>';
        }, $content);
        
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
    }
}

echo "All files fixed!\n";
?>

