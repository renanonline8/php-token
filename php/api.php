<?php
header("Access-Control-Allow-Origin: *");
include_once('db.php');
include_once('token.php');

$jsonResultado = array();
//Toda url deve ter parametro função
$funcao = '';
if (array_key_exists('funcao', $_GET)) {
	$funcao = $_GET['funcao'];
}
$jsonResultado['funcao'] = $funcao;
switch($funcao){
    case "criarToken" && "validarToken" && "destroiTodosTokenUsuario" :
        $jsonResultado['erro'] = '0';
        if (array_key_exists('usuario', $_GET)) {
            $usuario = $_GET['usuario'];
            $token = '';
            switch ($funcao) {
                case 'criarToken' :
                    $jsonResultado = token($usuario, $conn, $jsonResultado, $funcao, $token);
                    break;
                case 'validarToken' :
                    if (array_key_exists('token',$_GET)){
                        $token = $_GET['token'];
                        $jsonResultado = token($usuario, $conn, $jsonResultado, $funcao, $token);
                        break;
                    } else {
                        $jsonResultado['erro'] = '1';
                        $jsonResultado['msgErro'] = "Parametro token ausente";
                    }
                case 'destroiTodosTokenUsuario' : 
                    destroiTodosTokenUsuario($usuario);
                    $jsonResultado['reposta'] = array('usuario'=>$usuario);
                    break;
                default :
                    $jsonResultado['erro'] = '1';
                    $jsonResultado['msgErro'] = "Função $funcao sem acao definida";
                    break;
            }
        } else {
            $jsonResultado['erro'] = '1';
            $jsonResultado['msgErro'] = 'Parametro usuario não atribuido';
        }
        break;
    /*case "validarToken":
        $jsonResultado['erro'] = '0';
        break;*/
    default :
        $jsonResultado['erro'] = '1';
        $jsonResultado['msgErro'] = 'Parametro nao atribuido a uma funcao ou nulo';
        break;
}
echo json_encode($jsonResultado);

function token($usuario, $conn, $jsonResultado, $acao, $tokenValidar){
    if (!empty($usuario)) {
        $query = "select * from usuarios where usuario = '$usuario'";
        if($data = mysqli_query($conn, $query)){
            $linha = mysqli_fetch_assoc($data);
            if(count($linha) > 0) {
                switch ($acao) {
                    case 'criarToken' :
                        $token = criarToken($usuario);
                        $resultado = array('token'=>$token,'usuario'=>$usuario);
                        $jsonResultado['resposta'] = $resultado;
                        break;
                    case 'validarToken' :
                        $tokenResultadoValidacao = validaToken($tokenValidar, $usuario);
                        $resultado = array();
                        $resultado['token'] = $tokenValidar;
                        $resultado['usuario'] = $usuario;
                        $resultado['statusValidacao'] = $tokenResultadoValidacao;
                        $jsonResultado['resposta'] = $resultado;
                        break;
                    default :
                        $jsonResultado['erro'] = '1';
                        $jsonResultado['msgErro'] = "Parametro $acao não esperado para a função token";
                        break;
                }
               
            } else {
                $jsonResultado['erro'] = '1';
                $jsonResultado['msgErro'] = 'Usuario nao encontrado';
            }
        } else {
            $jsonResultado['erro'] = '2';
            $jsonResultado['msgErro'] = 'Erro no SQL';
            $jsonResultado['sqlErro'] = $query;
            $jsonResultado['sqlErroMsg'] = mysqli_error($conn);
        }
        mysqli_close($conn);
    } else {
        $jsonResultado['erro'] = '1';
        $jsonResultado['msgErro'] = 'Parametro usuário em branco';
    }

    return $jsonResultado;
}
?>