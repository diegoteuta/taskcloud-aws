<?php
/**
 * db.php — Conexión a la base de datos.
 *
 * Las credenciales se leen de /etc/db-config.ini, archivo poblado por el
 * script user-data al arranque de la EC2 a partir de AWS Secrets Manager.
 * De esta forma no quedan credenciales hardcoded en el código fuente.
 *
 * Formato esperado de /etc/db-config.ini:
 *   host = "instancia.xxxxxxxx.us-east-1.rds.amazonaws.com"
 *   port = "3306"
 *   dbname = "tasksdb"
 *   username = "admin"
 *   password = "************"
 */

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $configFile = __DIR__ . '/db-config.ini';
    if (!file_exists($configFile) || !is_readable($configFile)) {
        http_response_code(500);
        die("Error: archivo de configuración de base de datos no encontrado o sin permisos de lectura ({$configFile}).");
    }

    $config = parse_ini_file($configFile);
    if ($config === false) {
        http_response_code(500);
        die("Error: no se pudo parsear el archivo de configuración.");
    }

    $host    = $config['host']     ?? 'localhost';
    $port    = $config['port']     ?? '3306';
    $dbname  = $config['dbname']   ?? 'tasksdb';
    $user    = $config['username'] ?? 'root';
    $pass    = $config['password'] ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_TIMEOUT            => 5,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        die("Error de conexión a la base de datos: " . htmlspecialchars($e->getMessage()));
    }

    return $pdo;
}

/**
 * Devuelve el hostname de la instancia EC2 que está sirviendo la petición.
 * Útil para demostrar visualmente que el balanceador está distribuyendo
 * tráfico entre múltiples instancias.
 */
function getServerHostname(): string {
    $hostname = gethostname();
    return $hostname !== false ? $hostname : 'unknown';
}

/**
 * Devuelve la AZ donde corre esta instancia, consultando el IMDS de EC2.
 * Cachea el resultado para no llamar al metadata en cada request.
 */
function getAvailabilityZone(): string {
    static $az = null;
    if ($az !== null) {
        return $az;
    }
    $cacheFile = '/tmp/ec2-az.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        $az = trim(file_get_contents($cacheFile));
        return $az;
    }
    // IMDSv2: primero pedimos un token
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'PUT',
            'header'  => "X-aws-ec2-metadata-token-ttl-seconds: 60\r\n",
            'timeout' => 1,
        ],
    ]);
    $token = @file_get_contents('http://169.254.169.254/latest/api/token', false, $ctx);
    if ($token) {
        $ctx2 = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "X-aws-ec2-metadata-token: {$token}\r\n",
                'timeout' => 1,
            ],
        ]);
        $az = @file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone', false, $ctx2);
    }
    if (!$az) {
        $az = 'n/a';
    }
    @file_put_contents($cacheFile, $az);
    return $az;
}
