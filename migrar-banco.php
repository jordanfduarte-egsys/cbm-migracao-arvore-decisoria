<?php
echo "\n\n";
include "./planilhas-validas.php";
set_time_limit(0);
$host = 'servidor_banco';
$dbname = 'harpya';
$user = 'harpya_app';
$pass = 'Sbpbn938N9kCNFDU4FskAWMP';

$conn = pg_connect("host=$host port=5432 dbname=$dbname user=$user password=$pass");

echo "\n\rSera processado os itens: ";
foreach ($listArquivos as $arquivo) {
    echo " - " . $arquivo . "\n\r";
}
echo "\n\n\r";

function marcaComoExcluidoBanco($arquivo, $conn)
{
    $d = trim(explode("-", $arquivo['nome_ajustado'])[0]);
    $q1 = "SELECT * FROM classificacao_atendimento WHERE descricao LIKE '{$d} %' ORDER BY ID DESC LIMIT 1";
    $qr = pg_query($conn, $q1);
    $au = pg_fetch_array($qr, null, PGSQL_ASSOC);
    if (!empty($au)) {
        $idClassificacaoAtendimento = $au['id'];
        //pg_query($conn, $inseri);

        $q2 = "select * from arv_card ac where ac.descricao like '{$d} %'";
        $qr = pg_query($conn, $q2);
        $au = pg_fetch_array($qr, null, PGSQL_ASSOC);
        $idCard = null;
        if (!empty($au)) {
            $idCard = $au['id'];
        }

        pg_query($conn, "update arv_perg set excluido = 1 where id in (
            select id from arv_perg ap where id_classificacao_atendimento = {$idClassificacaoAtendimento} and excluido = 0
        )");

        pg_query($conn, "update arv_perg_alt set excluido = 1 where id in (
            select id from arv_perg_alt apa where apa.id_arv_perg in (
                select id from arv_perg ap where id_classificacao_atendimento = {$idClassificacaoAtendimento} 
            )
        )");

        pg_query($conn, "update arv_perg_alt_ag_envol set excluido = 1 where id in (
            select id from arv_perg_alt_ag_envol apaae where apaae .id_perg_alt in (
                select apa.id from arv_perg_alt apa where apa.id_arv_perg in (
                    select id from arv_perg ap where id_classificacao_atendimento = {$idClassificacaoAtendimento} 
                )
            )
        )");

        pg_query($conn, "update arv_perg_alt_arv_card_perg set excluido = 1 where id_arv_perg_alt in (
            select apa.id from arv_perg_alt apa where apa.id_arv_perg in (
                select id from arv_perg ap where id_classificacao_atendimento = ${idClassificacaoAtendimento} 
            )
        )");

        if ($idCard) {
            pg_query($conn, "update arv_card_perg set excluido = 1 where id in (
                select id from arv_card_perg where arv_card_perg.id_arv_card  = {$idCard}
            )");

            pg_query($conn, "update arv_card_perg_alt set excluido = 1 where id in (
                select id from arv_card_perg_alt acpa where  acpa.id_arv_card_perg in (
                    select id from arv_card_perg where arv_card_perg.id_arv_card  = {$idCard}  
                )
            )");
        }
    }
    
    


    // update arv_perg set excluido = 1 where id in (
    //     select id from arv_perg ap where id_classificacao_atendimento = 8077 and excluido = 0
    // );

    // update arv_perg_alt set excluido = 1 where id in (
    //     select id from arv_perg_alt apa where apa.id_arv_perg in (
    //         select id from arv_perg ap where id_classificacao_atendimento = 8077 
    //     )
    // );

    // update arv_perg_alt_ag_envol set excluido = 1 where id in (
    //     select id from arv_perg_alt_ag_envol apaae where apaae .id_perg_alt in (
    //         select apa.id from arv_perg_alt apa where apa.id_arv_perg in (
    //             select id from arv_perg ap where id_classificacao_atendimento = 8077 
    //         )
    //     )
    // );


    // update arv_perg_alt_arv_card_perg set excluido = 1 where id_arv_perg_alt in (
    //     select apa.id from arv_perg_alt apa where apa.id_arv_perg in (
    //         select id from arv_perg ap where id_classificacao_atendimento = 8077 
    //     )
    // );


    // update arv_card_perg set excluido = 1 where id in (
    //     select id from arv_card_perg where arv_card_perg.id_arv_card  = 30
    // );

    // update arv_card_perg_alt set excluido = 1 where id in (
    //     select id from arv_card_perg_alt acpa where  acpa.id_arv_card_perg in (
    //         select id from arv_card_perg where arv_card_perg.id_arv_card  = 30  
    //     )
    // );
}

// Valida as GUS
$json = json_decode(file_get_contents("./querys/00 - query - EXECUTAR_ANTES.sql"));
if (true) {
    foreach ($json as $line) {
        $lines = explode("|", $line);
        $verifica = $lines[0];
        $inseri = $lines[1];
        
        $qr = pg_query($conn, str_replace(";", "", $verifica));
        $au = pg_fetch_array($qr, null, PGSQL_ASSOC);

        if (empty($au['id'])) {
            pg_query($conn, $inseri);
        }
    }
}

// Update de dados do banco
$path = "./querys";
$diretorio = dir($path);
$arquivosLeitura = [];
$planilhas = 0;
while ($arquivoCheck = $diretorio->read()){
    $arquivoCheckAux = $arquivoCheck;
    $arquivoCheck = trim($arquivoCheck);
    $arquivoCheck = str_replace("'", "´", $arquivoCheck);
    $arquivoCheck = str_replace("🔼", "", $arquivoCheck);
    foreach ($listArquivos as $arquivoValido) {
        $arquivoValidoAux = $arquivoValido;
        $arquivoValido = str_replace(["+", "Ç", "Ã", "Õ", "-", "Ê"], "", $arquivoValido);
        $arquivoCheck = str_replace(["+", "Ç", "Ã", "Õ", "-", "Ê"], "", $arquivoCheck);
        if (preg_match("/" . $arquivoValido ."/", $arquivoCheck)) {
            $arquivosLeitura[] = [
                'arquivo' => $arquivoCheckAux,
                'nome_ajustado' => $arquivoValidoAux
            ];
            break;
        }
    }
    
}
$diretorio->close();

 // var_dump($arquivosLeitura); exit;
foreach ($arquivosLeitura as $arquivo) {
    $json = json_decode(file_get_contents("./querys/" . $arquivo['arquivo']));
    marcaComoExcluidoBanco($arquivo, $conn);
    $linhasBanco = 0;
    foreach ($json as $line22) {
        foreach ($line22 as $i => $line) {
            if (empty($line)) continue;

            $line = str_replace(["\n", ";"], "", trim($line));

            // Linha da classificacao atendimento
            if (preg_match("/INSERT INTO public.classificacao_atendimento /", $line)) {
                $lines = explode("|", $line);
                $verifica = $lines[0];
                $inseri = $lines[1];
                $inseri2 = $lines[2];

                $qr = pg_query($conn, str_replace(";", "", $verifica));
                $au = pg_fetch_array($qr, null, PGSQL_ASSOC);
                $linhasBanco++;
                if (empty($au)) {
                    pg_query($conn, $inseri);
                    $linhasBanco++;

                    pg_query($conn, $inseri2);
                    $linhasBanco++;
                }
            } else if (preg_match("/INSERT INTO arv_card /", $line)) {
                // Linha da arv_card
                $lines = explode("|", $line);
                $verifica = $lines[0];
                $inseri = $lines[1];

                $qr = pg_query($conn, str_replace(";", "", $verifica));
                $au = pg_fetch_array($qr, null, PGSQL_ASSOC);
                $linhasBanco++;
                if (empty($au)) {
                    pg_query($conn, $inseri);
                    $linhasBanco++;
                }
            } else {
                // Inseri as coisas aqui
                pg_query($conn, trim(str_replace(";", "", $line)));
                $linhasBanco++;

                if (pg_last_error($conn)) {
                    echo $line;
                    die("ERRO");
                }
            }
        }
    }

    echo " - INSERIDO o " . $arquivo['nome_ajustado'] . " (" . $linhasBanco . ")\n";
    $planilhas++;
}

// Pos inserções
$total = 0;
if (file_exists("./querys/10000 - query - EXECUTAR_DEPOIS-TODAS-NATUREZAS.sql")) {
    $json = json_decode(file_get_contents("./querys/10000 - query - EXECUTAR_DEPOIS-TODAS-NATUREZAS.sql"));

    foreach ($json as $line) {
        $line = trim(str_replace([";", "-- padrão"], "", $line));
        
        if (!pg_query($conn, $line)) {
            echo $line; exit;
        }
    // echo $line; exit;
    $total++;
    }
}

// Corrige a string do ARV_PERG e troca de "25#PERGUNTA" para "PERGUNTA"
foreach ($arquivosLeitura as $arquivo) {
    $json = json_decode(file_get_contents("./querys/" . $arquivo['arquivo']));
    $linhasBanco = 0;

    $d = trim(explode("-", $arquivo['nome_ajustado'])[0]);
    $q1 = "SELECT * FROM classificacao_atendimento WHERE descricao LIKE '{$d} %' ORDER BY ID DESC LIMIT 1";
    $qr = pg_query($conn, $q1);
    $au = pg_fetch_array($qr, null, PGSQL_ASSOC);
    if (!empty($au)) {
        $idClassificacaoAtendimento = $au['id'];
        $q2 = "UPDATE FROM arv_perg SET descricao = select select REGEXP_REPLACE(REGEXP_REPLACE(descricao, '(\d\d)#', '', 'g'), '(\d)#', '', 'g') WHERE ID_CLASSIFICACAO_ATENDIMENTO = " . $idClassificacaoAtendimento;
        pg_query($conn, $q2);
    }
}

echo "TOTAL Planilhas: " . $planilhas . " Total de redirecionamentos/chamdas extras: " . $total . "\n";

// Parte dos inserts finais
echo "FIM";