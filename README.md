# cbm-migracao-arvore-decisoria

# Requisitos
- PHP >= 7.4
- xlsx2csv

# Instalação

Instalar o xlsx2csv no servidor onde será rodado, através do comando 
`sudo apt install xlsx2csv`

# Modo de uso
Acessar o terminal, entrar na pasta do repositória, e rodar o comando `php index.php`. Deve ser considerando que o arquivo `arvore.xlsx` esteja na pasta e configurado no mesmo padrão em comparação a essa do repositória git.

Após esse comando é realizado a criação das querys dentro da pasta querys.

Para rodar todos os comandos e subir para o servidor automaticamente, executar `php migrar-banco.php`.

# Configuração
- No arquivo planilhas-validas.php, deve ser listado apenas títulos de planilhas que vão ser migrados para o banco
- No arquivo index.php, a variável $formato, aceita como parametro o valor 'string' que tornará o export no formato texto de query. Já se parametrizado o formato `json`, faz com que o export seja em arquivos json tornando a migração automática possível, através do comando `php migrar-banco.php`.

