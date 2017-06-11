<?php
/**
 * User: Aleksandar Zivanovic <coapsyfactor@gmail.com>
 */

/** Load required class */
require_once __DIR__ . '/iToken.php';

$dbHost = 'localhost';

$dbUser = 'root';

$dbPass = '';

$dbName = 'php_token';

$dbPort = 3306;

// Connect to database ($dbPort is not required, if not set default value is used, default value of port is 3306)
iToken::establishDatabaseConnection($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

// Create itokens table if it doesn't exists
iToken::executeInitialSQL();

// Get iToken instance
$iToken = new iToken();

// Generate normal token
$token = $iToken->generate();

echo "Token created: {$token}\n";

echo "Token {$token} is valid {$iToken->isValid($token)}\n";

// Check is token is valid
if ($iToken->isValid($token)) {

    // Remove token
    //$iToken->destroyToken($token);

    //echo "Token {$token} removed.\n";

}

// Generate entity token
$entityToken = $iToken->generate('oi');

echo "Entity Token created: {$entityToken}\n";

echo "Entity Token {$entityToken} is valid {$iToken->isValid($entityToken, 'EntityID')}\n";

// Check is entity token valid
if ($iToken->isValid($entityToken, 'EntityID')) {

    // Remove all tokens for given entity
    //$iToken->destroyEntityTokens('EntityID');

    //echo "Entity Tokens removed.\n";

}
?>