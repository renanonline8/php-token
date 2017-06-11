var token = {
    urlAPI : 'http://localhost:3310/php-token/php/api.php?',
    inicializar: function() {
        $(document).ready(this.pronto);
    },
    pronto: function(){
        $(document).submit(function(e){
            //Evitar Submit
            e.preventDefault();
            
            // Obter valores dos inputs
            var formValores = $(e.target).serialize();
            console.log(formValores);
            
            $.getJSON(token.urlAPI + formValores, function(resposta){
                console.log(resposta);
                var erro = resposta.erro;
                if (erro == '0') {
                    var respostaAPI = resposta.resposta;
                    var msgRespostaAPI = null;
                    switch (resposta.funcao){
                        case 'criarToken' :
                            msgRespostaAPI = 'Token Gerado: ' + respostaAPI.token;
                            break;
                        case 'validarToken' :
                            var statusToken = (respostaAPI.statusValidacao) ? 'Válido' : 'Inválido';
                            msgRespostaAPI = 'Status do Token: ' + statusToken;
                            break;
                        case 'destroiTodosTokenUsuario' :
                            msgRespostaAPI = 'Destruido os tokens';
                            break;
                    }
                    $(e.target).find('.msg_resposta').text(msgRespostaAPI);
                } else {
                    $(e.target).find('.msg_resposta').text('Erro: ' + resposta.msgErro);
                }
            })
                .fail(function(resposta){
                    console.log('Falha' + resposta.status);
                })
                .always(function() {
                    console.log( "complete" );
                });
        });
    }
}