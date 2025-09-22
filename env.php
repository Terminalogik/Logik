<?php
// app/env.php (adjust paths/user/pass/db)
return [
    'DB_DSN'        => 'mysql:host=localhost;dbname=logik;charset=utf8mb4',
    'DB_USER'       => 'logik',
    'DB_PASS'       => 'l0gik',
    'DB_PERSISTENT' => false,     // set true if you want persistent connections
    'DB_OPTIONS'    => [],        // extra PDO options if needed
];
