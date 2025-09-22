<?php
declare(strict_types=1);

namespace Logik\Data;

use PDO;
use PDOException;

final class DB
{
    private static ?PDO $pdo = null;
    private static array $config;

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {}

    //Set if this is development environment
    public static $isDev = true;


    /** Get shared PDO (lazy) */
    private static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = self::config();
        $options = $cfg['DB_OPTIONS'] ?? [];
        $options[PDO::ATTR_ERRMODE]            = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
        $options[PDO::ATTR_EMULATE_PREPARES]   = false;
        if (!empty($cfg['DB_PERSISTENT'])) {
            $options[PDO::ATTR_PERSISTENT] = true;
        }

        self::$pdo = new PDO($cfg['DB_DSN'], $cfg['DB_USER'], $cfg['DB_PASS'], $options);
        return self::$pdo;
    }

    /** Your existing config fetcher (assumed to exist elsewhere) */
    private static function config(): array
    {

        if (self::$isDev === false) {
            // Load from env.php variables
            self::$config = [
                'DB_DSN'        => 'mysql:host=localhost;dbname=terminalogic_pdztoolkit;charset=utf8mb4',
                'DB_USER'       => 'root',
                'DB_PASS'       => '',
                'DB_PERSISTENT' => false,     // set true if you want persistent connections
                'DB_OPTIONS'    => [],        // extra PDO options if needed
            ];
            return self::$config;
        } else {
            // Load from env.php variables
            self::$config = [
                'DB_DSN'        => 'mysql:host=mysql.us.cloudlogin.co;dbname=terminalogic_pdztoolkit;charset=utf8mb4',
                'DB_USER'       => 'terminalogic_pdztoolkit',
                'DB_PASS'       => 'Do0msday$$',
                'DB_PERSISTENT' => false,     // set true if you want persistent connections
                'DB_OPTIONS'    => [],        // extra PDO options if needed
            ];
            return self::$config;
        }
    }

    /** Close connection (let PDO object be GC’d) */
    public static function close(): void
    {
        self::$pdo = null;
    }

    /** Low-level runner (kept for compatibility) */
    private static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // =========================
    // New simplified API
    // =========================

    public const READ_ALL   = 'all';
    public const READ_ONE   = 'one';
    public const READ_VALUE = 'value';

    /**
     * READ query helper.
     * $mode:
     *   - self::READ_ALL   → array<array<string,mixed>>
     *   - self::READ_ONE   → ?array<string,mixed>
     *   - self::READ_VALUE → mixed|null
     */
    public static function read(string $sql, array $params = [], string $mode = self::READ_ALL): mixed
    {
        $stmt = self::run($sql, $params);

        switch ($mode) {
            case self::READ_ONE:
                $row = $stmt->fetch();
                return $row === false ? null : $row;

            case self::READ_VALUE:
                $val = $stmt->fetchColumn();
                return $val === false ? null : $val;

            case self::READ_ALL:
            default:
                return $stmt->fetchAll();
        }
    }

    /**
     * WRITE query helper (INSERT/UPDATE/DELETE, DDL).
     * @return array{rowCount:int, lastInsertId:?string}
     */
    public static function write(string $sql, array $params = []): array
    {
        $stmt = self::run($sql, $params);
        $rowCount = $stmt->rowCount();

        // lastInsertId is meaningful for INSERT with auto-increment
        $id = null;
        try {
            $last = self::pdo()->lastInsertId();
            if ($last !== '0' && $last !== '') {
                $id = $last;
            }
        } catch (\Throwable $e) {
            // some drivers may throw; ignore and keep null
        }

        return ['rowCount' => $rowCount, 'lastInsertId' => $id];
    }   

    /** Simple health/info string */
    public static function serverInfo(): string
    {
        $pdo = self::pdo();
        return 'MySQL Server ' .
            $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) .
            ' on ' .
            $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }
}




//USAGE EXAMPLES:

// use Logik\Data\DB;

// // READ (all rows)
// $rows = DB::read('SELECT * FROM events WHERE owner_id = :uid', ['uid' => $userId]);
// // READ (one row)
// $event = DB::read('SELECT * FROM events WHERE id = :id', ['id' => $id], DB::READ_ONE);
// // READ (single value)
// $total = DB::read('SELECT COUNT(*) FROM events', [], DB::READ_VALUE);

// // WRITE (insert)
// $res = DB::write(
//   'INSERT INTO events (name, starts_at) VALUES (:n, :s)',
//   ['n' => $name, 's' => $startsAt]
// );
// // $res['rowCount'], $res['lastInsertId']

// // WRITE (update)
// $res = DB::write(
//   'UPDATE events SET name = :n WHERE id = :id',
//   ['n' => $name, 'id' => $id]
// );
// // $res['rowCount']
