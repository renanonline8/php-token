<?php
function iniciarToken(){
    //Incluir script no código
    require_once __DIR__ . '/iToken.php';
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'php_token';
    $dbPort = 3306;

    //Conectar com a base de dados
    iToken::establishDatabaseConnection($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    //Cria a tabela itoken, se não existir
    iToken::executeInitialSQL();
    //Cria instancia Itoken
    $iToken = new iToken();
    return $iToken;
}

function criarToken($usuario) {
    $iToken = iniciarToken();
    $lifetime = 360;
    $token = $iToken->generate($usuario, $lifetime);
    return $token;
}

function validaToken($token, $usuario) {
    $iToken = iniciarToken();
    $tokenChecado = $iToken->isValid($token, $usuario);
    return $tokenChecado;
}

function destroiTodosTokenUsuario($usuario){
     $iToken = iniciarToken();
     $iToken->destroyEntityTokens($usuario);
     return;
}
?>