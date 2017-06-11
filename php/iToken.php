<?php

/**
 * User: Aleksandar Zivanovic <coapsyfactor@gmail.com>
 */
class iToken
{
    /** Token lifetime in seconds */
    const DEFAULT_TOKEN_LIFETIME = 60;

    /** @var PDO */
    private static $_connection;

    /**
     * @param string $token
     * @param string|int|null $entity
     * @return bool
     */
    public function isValid($token, $entity = null)
    {
        if (false === self::$_connection instanceof PDO) {
            throw new RuntimeException('Missing database connection.');
        }

        /** @var string $query Insert query depending on input data */
        $query = is_null($entity) ? $this->buildTokenFetchQuery() : $this->buildEntityTokenFetchQuery();

        $statement = self::$_connection->prepare($query);

        if (false === $statement) {
            throw new RuntimeException('Error fetching token, error code ' . self::$_connection->errorCode());
        }

        if (false === $statement->execute(is_null($entity) ? [$token] : [$token, $entity])) {
            return false;
        }

        return $this->validateToken($statement);
    }

    /**
     *
     * Removes certain token
     *
     * @param string $token Token that needs to be removed
     * @return bool
     */
    public function destroyToken($token)
    {
        if (false === self::$_connection instanceof PDO) {
            throw new RuntimeException('Missing database connection.');
        }

        $query = 'DELETE FROM `itokens` WHERE `itokens`.`token` = ?;';

        $statement = self::$_connection->prepare($query);

        if (false === $statement) {
            throw new RuntimeException('Error deleting token, error code ' . self::$_connection->errorCode());
        }

        return $statement->execute([$token]);
    }

    /**
     *
     * Removes all tokens that are owned by certain entity
     *
     * @param string|int $entity Entity identifier
     * @return bool
     */
    public function destroyEntityTokens($entity)
    {
        if (false === self::$_connection instanceof PDO) {
            throw new RuntimeException('Missing database connection.');
        }

        $query = 'DELETE FROM `itokens` WHERE `itokens`.`entity` = ?;';

        $statement = self::$_connection->prepare($query);

        if (false === $statement) {
            throw new RuntimeException('Error deleting token, error code ' . self::$_connection->errorCode());
        }

        return $statement->execute([$entity]);
    }

    /**
     * @param string|int|null $entity Identifier of entity that owns this token
     * @param int $lifeTime Token lifetime in seconds
     * @return string|null Generated token or NULL if token not created/saved
     */
    public function generate($entity = null, $lifeTime = iToken::DEFAULT_TOKEN_LIFETIME)
    {
        if (false === self::$_connection instanceof PDO) {
            throw new RuntimeException('Missing database connection.');
        }

        /** @var string $token unique hash of token */
        $token = hash('sha256', uniqid(uniqid('', true), true));

        /** @var string $query Query for saving token in database */
        $query = 'INSERT INTO `itokens` (`token`, `entity`, `life_time`, `last_used`) VALUES (?, ?, ?, ?);';

        /** @var PDOStatement $statement Prepared insert query */
        $statement = self::$_connection->prepare($query);

        if (false === $statement) {
            throw new RuntimeException('Error creating token, error code ' . self::$_connection->errorCode());
        }

        /** Execute query */
        if (false === $statement->execute([$token, $entity, $lifeTime, time()])) {
            return null;
        }

        /** If there is insert id/affected rows return token, otherwise return NULL */
        return $statement->rowCount() ?  $token : null;
    }

    /**
     *
     * Refresh last_used time for certain token
     *
     * @param string $token Token that should be refreshed
     * @return bool
     */
    public function refresh($token)
    {
        if (false === self::$_connection instanceof PDO) {
            throw new RuntimeException('Missing database connection.');
        }

        /** @var string $query Query that will update token's last used time */
        $query = 'UPDATE `itokens` SET `itokens`.`last_used` = ? WHERE `itokens`.`token` = ?;';

        $statement = self::$_connection->prepare($query);

        if (false === $statement) {
            throw new RuntimeException('Error updating token, error code ' . self::$_connection->errorCode());
        }

        if (false === $statement->execute([time(), $token])) {
            return false;
        }

        return (bool) $statement->rowCount();
    }

    /**
     *
     * Sets active database connection
     *
     * @param PDO $connection
     */
    public static function setDatabaseConnection(PDO $connection)
    {
        self::$_connection = $connection;
    }

    /**
     * @param string $dbHost Host url
     * @param string $dbUser User used to connect
     * @param string $dbPass User's password
     * @param string $dbName Name of database to connect to
     * @param int $dbPort Database port number
     */
    public static function establishDatabaseConnection($dbHost, $dbUser, $dbPass, $dbName, $dbPort = 3306)
    {
        $dsn = "mysql:host={$dbHost}:{$dbPort};dbname={$dbName};";

        self::$_connection = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * Create tokens table
     */
    public static function executeInitialSQL()
    {
        if (false === self::$_connection instanceof PDO) {
            throw new RuntimeException('Missing database connection.');
        }

        $createTableQuery = "
          CREATE TABLE IF NOT EXISTS `itokens` (
            `token` VARCHAR(64) NOT NULL,
            `entity` VARCHAR(255) NULL,
            `last_used` INT NULL,
            `life_time` INT NULL DEFAULT 60,
          PRIMARY KEY (`token`),
          UNIQUE INDEX `entity_token` (`token` ASC, `entity` ASC));
        ";

        $statement = self::$_connection->prepare($createTableQuery);

        if (false === $statement) {
            throw new RuntimeException('Error while creating table, error code ' . self::$_connection->errorCode());
        }

        $statement->execute();
    }

    /**
     *
     * Checks token existence and is it expired
     *
     * @param PDOStatement $statement Executed statement
     * @return bool
     */
    private function validateToken(PDOStatement $statement)
    {
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($data)) {
            return false;
        }

        return ($data['last_used'] + $data['life_time']) >= time();
    }

    /**
     *
     * Creates select query for token only
     *
     * @return string
     */
    private function buildTokenFetchQuery()
    {
        return 'SELECT * FROM `itokens` WHERE `itokens`.`token` = ?;';
    }


    /**
     *
     * Creates select query for token and entity
     *
     * @return string
     */
    private function buildEntityTokenFetchQuery()
    {
        return 'SELECT * FROM `itokens` WHERE `itokens`.`token` = ? AND `itokens`.`entity` = ?;';
    }
}