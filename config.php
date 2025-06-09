<?php
// Carrega variáveis de ambiente a partir de um arquivo .env se existir
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = array_map('trim', explode('=', $line, 2));
        putenv(sprintf('%s=%s', $key, $value));
        $_ENV[$key] = $value;
    }
}

class MySQLiteResult {
    public $num_rows = 0;
    private $rows;
    private $index = 0;

    public function __construct(array $rows) {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }

    public function fetch_assoc() {
        if ($this->index < $this->num_rows) {
            return $this->rows[$this->index++];
        }
        return null;
    }

    public function fetch_all($mode = null) {
        return $this->rows;
    }
}

class MySQLiteStatement {
    private $stmt;
    private $params = [];
    public $error = '';

    public function __construct(PDOStatement $stmt) {
        $this->stmt = $stmt;
    }

    public function bind_param($types, &...$vars) {
        $this->params = &$vars;
    }

    public function execute() {
        try {
            return $this->stmt->execute($this->params);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function get_result() {
        $rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        return new MySQLiteResult($rows);
    }

    public function close() {
        $this->stmt = null;
    }
}

class MySQLiteConnection {
    private $pdo;

    public function __construct($path) {
        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function prepare($query) {
        $stmt = $this->pdo->prepare($query);
        return new MySQLiteStatement($stmt);
    }

    public function query($query) {
        $res = $this->pdo->query($query);
        $rows = $res ? $res->fetchAll(PDO::FETCH_ASSOC) : [];
        return new MySQLiteResult($rows);
    }

    public function close() {
        $this->pdo = null;
    }

    public function set_charset($c) {
        // não aplicável ao SQLite
    }

    public function __get($name) {
        if ($name === 'insert_id') {
            return $this->pdo->lastInsertId();
        }
        return null;
    }
}

$dbPath = getenv('DB_PATH') ?: __DIR__ . '/fazendinha.db';

$conn = new MySQLiteConnection($dbPath);

// Define constante usada por código legado
if (!defined('MYSQLI_ASSOC')) {
    define('MYSQLI_ASSOC', 1);
}
?>
