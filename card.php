<?php 
// https://t.yctin.com/en/excel/to-php-array/
$a = array(
	0 => array('CARTÃO DE PRÉ-SOCORRO - AUXÍLIO NO PARTO / NASCIMENTO', '', '', '', '', '', '', ''),
	1 => array('Nº', 'PERGUNTA', 'ALTERNATIVA', 'PROX. PERGUNTA', 'CARD_GENERICO', 'ORIENTAÇÕES PRÉ-SOCORRO', 'ORDEM', 'OBSERVAÇÕES / REGRAS DE NEGÓCIO'),
	2 => array('1', 'A paciente já teve um bebê ANTES?', 'Sim', '2', '', '', '', ''),
	3 => array('', '', 'Não', '4', '', '', '', ''),
	4 => array('2', 'O intervalo entre as CONTRAÇÕES é maior ou menor que 5 minutos ?', 'Maior', '-', '', 'OUÇA com atenção, eu vou lhe dizer o que fazer.', '1', ''),
	5 => array('', '', '', '', '', 'Coloque-a DEITADA do LADO ESQUERDO em uma posição confortável.', '2', ''),
	6 => array('', '', '', '', '', 'Faça-a RESPIRAR profundo.', '3', '"- APÓS O ULTIMO CHECKED, APRESENTARÁ A PRÓXIMA PERGUNTA CONFIGURADA'),
	7 => array('- Caso não exista próxima pergunta, finalizará"'),
	8 => array('', '', 'Menor', '3', '', '', '', ''),
	9 => array('3', 'Ela tem vontade de "EMPURRAR"?', 'Sim', '5', '', 'Peça para ela RESISTIR o desejo de EXPULSAR.', '1', ''),
	10 => array('', '', '', '', '', 'Peça para que ela DEITE com a barriga para cima e RELAXE, respire PROFUNDO pela BOCA.', '2', ''),
	11 => array('', '', '', '', '', 'Peça para ela retire sua ROUPA ÍNTIMA.', '3', ''),
	12 => array('', '', '', '', '', 'Coloque toalhas limpas sob suas NÁDEGAS.', '4', ''),
	13 => array('', '', '', '', '', 'Tenha toalhas EXTRAS por perto.', '5', '"- APÓS O ULTIMO CHECKED, APRESENTARÁ A PRÓXIMA PERGUNTA CONFIGURADA'),
	14 => array('- Caso não exista próxima pergunta, finalizará"'),
	15 => array('', '', 'Não', '-', '', 'MONITORE a paciente.', '1', ''),
	16 => array('', '', '', '', '', 'Desejo de EMPURRAR é sinal de parto iminente.', '2', '"- APÓS O ULTIMO CHECKED, APRESENTARÁ A PRÓXIMA PERGUNTA CONFIGURADA'),
	17 => array('- Caso não exista próxima pergunta, finalizará"'),
	18 => array('4', 'O intervalo entre as CONTRAÇÕES é maior ou menor que 2 minutos ?', 'Maior', '-', '', 'OUÇA com atenção, eu vou lhe dizer o que fazer.', '1', ''),
	19 => array('', '', '', '', '', 'Coloque-a DEITADA do LADO ESQUERDO em uma posição confortável.', '2', ''),
	20 => array('', '', '', '', '', 'Faça-a RESPIRAR profundo.', '3', '"- APÓS O ULTIMO CHECKED, APRESENTARÁ A PRÓXIMA PERGUNTA CONFIGURADA'),
	21 => array('- Caso não exista próxima pergunta, finalizará"'),
	22 => array('', '', 'Menor', '3', '', '', '', ''),
	23 => array('5', 'Considera um Parto Iminente ( Bolsa estourou, presença de sangue ou coroamento) ?', 'Sim', '-', '-', 'O BEBÊ sairá normalmente. NÃO PUXE ou EMPURRE.', '1', ''),
	24 => array('', '', '', '', '', 'Haverá água e sangue. Isto é NORMAL.', '2', '"- APÓS O ULTIMO CHECKED, APRESENTARÁ A PRÓXIMA PERGUNTA CONFIGURADA'),
	25 => array('- Caso não exista próxima pergunta, finalizará"'),
	26 => array('', '', 'Não', '6', '', '', '', ''),
	27 => array('6', 'A criança nasceu ?', 'Sim', '7', '', 'LIMPE sua BOCA e NARIZ com um pano limpo.', '', '"- APÓS O ULTIMO CHECKED, APRESENTARÁ A PRÓXIMA PERGUNTA CONFIGURADA'),
	28 => array('- Caso não exista próxima pergunta, finalizará"'),
	29 => array('', '', 'Não', '-', '-', 'Tranquilize a mãe.', '1', ''),
	30 => array('', '', '', '', '', 'Peça para ficar deitada com o joelhos dobrados.', '2', ''),
	31 => array('', '', '', '', '', 'Diga para NÃO empurrar.', '3', '"- QUANDO NÃO POSSUIR PRÓXIMA PERGUNTA OU NÃO CHAMAR OUTRO CARD, FINALIZARÁ'),
	32 => array('- Caso exista Próxima Pergunta e Card, irá para o Card e depois retorna pra próxima pergunta"'),
	33 => array('7', 'A criança respira ?', 'Sim', '-', '-', 'ENROLE o bebê em uma toalha limpa.', '1', ''),
	34 => array('', '', '', '', '', 'NÃO CORTE o cordão umbilical.', '2', ''),
	35 => array('', '', '', '', '', 'Coloque a criança nas pernas da mãe.', '3', '"- QUANDO NÃO POSSUIR PRÓXIMA PERGUNTA OU NÃO CHAMAR OUTRO CARD, FINALIZARÁ'),
	36 => array('- Caso exista Próxima Pergunta e Card, irá para o Card e depois retorna pra próxima pergunta"'),
	37 => array('', '', 'Não', '-', 'CARD_RCP', 'MASSAGEIE as costas e o peito do bebê.', '1', ''),
	38 => array('', '', '', '', '', 'Dê pequenos TAPAS na sola do pé.', '2', '"- QUANDO NÃO POSSUIR PRÓXIMA PERGUNTA OU NÃO CHAMAR OUTRO CARD, FINALIZARÁ'),
	39 => array('- Caso exista Próxima Pergunta e Card, irá para o Card e depois retorna pra próxima pergunta"'),
);

echo "<pre>";
// print_r($a);exit;

$sqlPerguntaCard = [];
$sqlAlternativaCard = [];
$sqlUpdateProximoCardOutroCard = [];
$sqlSubOrientacoesAlternativaCard = [];

$perguntas = [];
function echoAlternativa($perguntas, $lastInsertCard, &$sqlAlternativaCard, $a, &$sqlSubOrientacoesAlternativaCard, &$sqlUpdateProximoCardOutroCard, $nomeCard) {
    if ($lastInsertCard) {
        $alternativaStr = "";
        if ($lastInsertCard == "O bebê está CONSCIENTE?") {
           // var_dump($perguntas); exit;
        }
        // foreach($a as $alt) {
        //     $alt['alternativa'] = str_replace("'", "´", $alt['alternativa']);
        //     if (is_numeric($alt['proximo_passo'])) {
        //     }
        // }


        /**
         * 'pergunta'         => $row[1],
         * 'alternativa'    => $row[2],
         * 'proximo_passo' => $row[3],
         * 'card_generico' => $row[4],
         * 'ordem'           => $row[6],
         * 'sub_orientacao' => $row[5]
         */
        foreach ($perguntas as $alt) {
            $alt['alternativa'] = str_replace("'", "´", $alt['alternativa']);
            $stringProximoPasso = "";
            if (!empty($alt['alternativa']) && $alt['alternativa'] != 'ALTERNATIVA') {
                $alternativaStr = $alt['alternativa'];
            }
            $alternativaStrFinal = !empty($alt['alternativa']) ? $alt['alternativa'] : $alternativaStr;
            if (is_numeric($alt['proximo_passo'])) {
                foreach ($a as $row) {
                    if ($row[0] == $alt['proximo_passo']) {
                        $stringProximoPasso = $row[1];
                        break;
                    }
                }
            } 
            if ($alt['sub_orientacao']) {
               $alt['ordem'] = empty($alt['ordem']) ? 1 : $alt['ordem'];
                $sqlSubOrientacoesAlternativaCard[] = "INSERT INTO arv_card_sub_orien (id_card_perg_alt,
                    descricao,
                    ordem) VALUES (                        
                        (
                            SELECT arv_card_perg_alt.id FROM arv_card_perg_alt
                            WHERE arv_card_perg_alt.id_arv_card_perg = (
                                SELECT acp.id FROM arv_card_perg acp 
                                INNER JOIN arv_card ac ON ac.id = acp.id_arv_card 
                                WHERE acp.descricao = '{$lastInsertCard}' AND ac.descricao = '{$nomeCard}' LIMIT 1
                            ) AND arv_card_perg_alt.descricao = '{$alternativaStrFinal}' LIMIT 1                            
                        ),
                        '{$alt['sub_orientacao']}',
                        {$alt['ordem']}                        
                    );";
                
            }

            if ($alt['alternativa']) {
                $cardProx = "(SELECT arv_card_perg.id FROM arv_card_perg WHERE arv_card_perg.descricao = '{$stringProximoPasso}' LIMIT 1)";
                if (preg_match("/CARD_/", $alt['card_generico'])) {
                    $cardProx = 'NULL';
                    $nomeCardAux = str_replace("CARD_", "", $alt['card_generico']);
                    $nomeCardAux = str_replace("_", " ", $nomeCardAux);
                    $sqlUpdateProximoCardOutroCard[] = "UPDATE arv_card_perg_alt SET id_prox_arv_card_perg = (
                        SELECT arv_card_perg.id FROM arv_card_perg 
                        INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                        WHERE arv_card.descricao = '{$nomeCardAux}' ORDER BY arv_card_perg.id ASC LIMIT 1
                    ) WHERE id_arv_card_perg = (SELECT acp.id FROM arv_card_perg acp INNER JOIN arv_card ac ON ac.id = acp.id_arv_card WHERE acp.descricao = '{$lastInsertCard}' AND ac.descricao = '{$nomeCard}' LIMIT 1) AND arv_card_perg_alt.descricao = '{$alternativaStrFinal}';";
                }

                $sqlAlternativaCard[] = "INSERT INTO arv_card_perg_alt (
                    id_arv_card_perg,
                    id_prox_arv_card_perg,
                    descricao) VALUES (
                        (SELECT acp.id FROM arv_card_perg acp 
                        INNER JOIN arv_card ac ON ac.id = acp.id_arv_card
                        WHERE acp.descricao = '{$lastInsertCard}' AND ac.descricao = '{$nomeCard}'
                        LIMIT 1),
                        {$cardProx},
                        '{$alternativaStrFinal}'
                    );";
            }
        }
    }
}

$sqlCard = "";
$nomeCard = "";
$lastInsertCard = "";
foreach ($a as $iii => $row) {
    if ($iii == 0) {
        $nomeCard = trim(end(explode("-", $row[0])));
        $sqlCard = "INSERT INTO arv_card (id_classificacao_atendimento, descricao) VALUES (
            NULL,
            '{$nomeCard}'
        );";
        continue;
    }

    if (is_numeric($row[0])) {
        echoAlternativa($perguntas, $lastInsertCard, $sqlAlternativaCard, $a, $sqlSubOrientacoesAlternativaCard, $sqlUpdateProximoCardOutroCard, $nomeCard);
        if ($row[1] == 'O bebê está CONSCIENTE?') {
             // var_dump($row); 
             // echo "<--->";
        }
        $perguntas = [];
        $row[1] = str_replace("'", "´", $row[1]);
        $sqlPerguntaCard[] = "INSERT INTO arv_card_perg (
            id_arv_card,
            descricao) VALUES (
                (SELECT arv_card.id FROM arv_card WHERE arv_card.descricao = '{$nomeCard}'),
                '{$row[1]}'
            );";

        $lastInsertCard = $row[1];
        $perguntas[] = [
            'pergunta' => $row[1],
            'alternativa' => $row[2],
            'proximo_passo' => $row[3],
            'card_generico' => $row[4],
            'ordem' => $row[6],
            'sub_orientacao' => $row[5]
        ];

        if (!isset($a[$iii + 1])) {
            echoAlternativa($perguntas, $lastInsertCard, $sqlAlternativaCard, $a, $sqlSubOrientacoesAlternativaCard, $sqlUpdateProximoCardOutroCard, $nomeCard);
        }
    } elseif (!is_numeric($row[0]) && (!empty($row[2]) || empty($row[2]) && !empty($row[5])) && $row[2] != "ALTERNATIVA") {
        $perguntas[] = [
            'pergunta' => $row[1],
            'alternativa' => $row[2],
            'proximo_passo' => $row[3],
            'card_generico' => $row[4],
            'ordem' => $row[6],
            'sub_orientacao' => $row[5]
        ];        
    }

    if (!isset($a[$iii + 1])) {
        echoAlternativa($perguntas, $lastInsertCard, $sqlAlternativaCard, $a, $sqlSubOrientacoesAlternativaCard, $sqlUpdateProximoCardOutroCard, $nomeCard);
    }
}

function printAll($all) {
    foreach ($all as $row) {
        echo trim($row) . "<br>";
    }
}
echo "-- SQL do CARD<br><br>";
printAll([$sqlCard]);

echo "<br><br>-- SQL de PERGUNTAS CARD<br><br>";
printAll($sqlPerguntaCard);

echo "<br><br>-- SQL de ALTERNATIVAS CARD<br><br>";
printAll($sqlAlternativaCard);

echo "<br><br>-- SQL de ORIENTACOES CARD ALTERNATIVA<br><br>";
printAll($sqlSubOrientacoesAlternativaCard);

echo "<br><br>-- SQL de UPDATE DE TROCA DE CARD<br><br>";
printAll($sqlUpdateProximoCardOutroCard);