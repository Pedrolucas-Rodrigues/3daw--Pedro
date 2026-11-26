document.getElementById('formDisciplina').addEventListener('submit', async function(event) {
    event.preventDefault(); 
    const btnSalvar = document.getElementById('btnSalvar');
    const divMensagem = document.getElementById('mensagem');

    // Captura os valores dos inputs
    const dados = {
        nome: document.getElementById('nome').value,
        codigo: document.getElementById('codigo').value,
        carga_horaria: document.getElementById('carga_horaria').value
    };

    // Desabilita o botão durante a requisição para evitar cliques duplos
    btnSalvar.disabled = true;
    btnSalvar.textContent = 'Salvando...';
    divMensagem.style.display = 'none';

    try {
        const resposta = await fetch('cadastrar_disciplina.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });

        const resultado = await resposta.json();

        // Exibe o feedback visual
        divMensagem.textContent = resultado.mensagem;
        divMensagem.className = resultado.status === 'sucesso' ? 'sucesso' : 'erro';
        divMensagem.style.display = 'block';

        // Limpa o formulário se deu certo
        if (resultado.status === 'sucesso') {
            document.getElementById('formDisciplina').reset();
        }

    } catch (erro) {
        divMensagem.textContent = 'Erro de comunicação com o servidor.';
        divMensagem.className = 'erro';
        divMensagem.style.display = 'block';
    } finally {
        btnSalvar.disabled = false;
        btnSalvar.textContent = 'Cadastrar Disciplina';
    }
});