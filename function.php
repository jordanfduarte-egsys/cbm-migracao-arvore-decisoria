<?php

function echoAlternativas($alternativas, $row, $lastInsert, $a, &$sqlAlternativas, &$sqlAgenciaEnvolvida, &$sqlUpdateOrientacaoNatureza, $natureza, &$sqlProximoPassoOutraNatureza, &$sqlAgenciaEnvolvidaCadastroGuanicoes, $formato) {
    if ($lastInsert) {
        if (!function_exists("numerico")) {
            function numerico($valor) {
                $v = trim($valor);
                return ctype_alnum($v) && $v > -1;
            }
        }
        $pergunta_principal = "";
        foreach ($alternativas as $alt) {
            if (empty(array_filter($alt))) break;
            if (strtoupper($alt['alernativa']) == 'ALTERNATIVA') continue;
            if ($alt['pergunta_principal']) {
                $pergunta_principal = str_replace("'", "´", $alt['pergunta_principal']);
                break;
            }
        }

        foreach ($alternativas as $alt) {
            if (empty(array_filter($alt))) break;
            if (strtoupper($alt['alernativa']) == 'ALTERNATIVA') continue;

            $idPrioridade = 'NULL';
            $alt['prioridade'] = trim($alt['prioridade']);
            if (strtolower($alt['prioridade']) == 'alta') {
                $idPrioridade = 3;
            }
            if (strtolower($alt['prioridade']) == 'média') {
                $idPrioridade = 2;
            }
            if (strtolower($alt['prioridade']) == 'baixa') {
                $idPrioridade = 1;
            }
            if (preg_match("/baixa/", strtolower($alt['prioridade']))) {
                $idPrioridade = 1;
            }

            $stringPriximoPasso = "";
            if (is_numeric($alt['proximo_passo'])) {
                foreach ($a as $iii => $row2) {
                    if ($iii == 0) continue;
                    if (preg_match("/ORIENTAÇÃO/", $row2[1])) break;

                    if ($alt['proximo_passo'] == $row2[0]) {
                        $stringPriximoPasso = $row2[1];
                        break;
                    }
                }
            } elseif (false && $alt['proximo_passo'] && trim(strtoupper($alt['proximo_passo'])) != "FIM" && !preg_match("/FIM/", trim(strtoupper($alt['proximo_passo'])))) {
                $numeroAlt = explode("-", $alt['proximo_passo']);
                $offset = 0;
                $fraseArvore = $alt['proximo_passo'];
                if (count($numeroAlt) == 2 && intval($numeroAlt[1]) > 0) {
                    $fraseArvore = $numeroAlt[0];
                    $offset = intval($numeroAlt[1]) - 1;
                }
                
                // quando o proximo passo é um outro card
                $sqlProximoPassoOutraNatureza[] = "UPDATE arv_perg_alt SET id_prox_arv_perg = (
                    select arv_perg1.id from arv_perg arv_perg1
                    inner join classificacao_atendimento classificacao_atendimento1 on
                        classificacao_atendimento1.id = arv_perg1.id_classificacao_atendimento
                    where classificacao_atendimento1.descricao like '{$fraseArvore}%' and arv_perg1.excluido  = 0 
                        limit 1 offset {$offset}
                ) WHERE id_arv_perg = (
                    SELECT arv_perg.id FROM arv_perg 
                    INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                    WHERE arv_perg.descricao = '{$pergunta_principal}' 
                    AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                ) AND arv_perg_alt.descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0";
            }
            
            $is_enc_atendimento = ($alt['enc_despacho'] == "S" || $alt['enc_despacho'] == "s" || trim(strtoupper($alt['enc_despacho'])) == "SIM") ? 1 : 0;
            $stringPriximoPasso = str_replace("'", "´", $stringPriximoPasso);
            $is_gerar_ocorrencia = $alt['classificacao'] == 'Ocorrência' ? 1 : 0;
            $mascara = 'NULL';
            if (strtoupper($alt['alernativa']) == 'DESCREVER' || preg_match("/DESCREVER/", strtoupper($alt['alernativa']))  || preg_match("/DESCREVA/", strtoupper($alt['alernativa']))) {
                $mascara = "(SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'ALPHA' AND arv_mascara.excluido = 0 LIMIT 1)";
            }

            if (strtoupper($alt['alernativa']) == 'NUMÉRICO') {
                $mascara = "(SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'INT' AND arv_mascara.excluido = 0 LIMIT 1)";
            }

            if (strtoupper($alt['alernativa']) == 'NÚMERO' || strtoupper($alt['alernativa']) == 'NúMERO') {
                $mascara = "(SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'INT' AND arv_mascara.excluido = 0 LIMIT 1)";
            }
            $alt['tipo_mascara'] = trim($alt['tipo_mascara']);
            // @todo colocar aqui se tiver outras mascaras
            if (strtoupper($alt['tipo_mascara']) == 'ALFANUMÉRICO' || strtoupper($alt['tipo_mascara']) == 'ALFANUMéRICO' || preg_match("/ALFA/", strtoupper($alt['tipo_mascara']))) {
                $mascara = "(SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'ALPHA' AND arv_mascara.excluido = 0 LIMIT 1)";
            }
            if (strtoupper($alt['tipo_mascara']) == 'MULTIPLO' || strtoupper($alt['tipo_mascara']) == 'MULTIPLO' || strtoupper($alt['tipo_mascara']) == 'MULTIPLA') {
                $mascara = "(SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'MULTIPLE' AND arv_mascara.excluido = 0 LIMIT 1)";
            }
            if (strtoupper($alt['tipo_mascara']) == 'INT') {
                $mascara = "(SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'INT' AND arv_mascara.excluido = 0 LIMIT 1)";
            }


            $stringPriximoPasso1 = 'NULL';
            if ($stringPriximoPasso) {
                $stringPriximoPasso1 = "(SELECT arv_perg.id FROM arv_perg 
                    INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id
                    WHERE arv_perg.descricao = '{$stringPriximoPasso}' AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                )";
            }
            $sqlAlternativas[] = "
                INSERT INTO arv_perg_alt (
                    id_arv_perg,
                    id_prox_arv_perg,
                    id_arv_card_pergunta,
                    id_arv_card,
                    id_nivel_atendimento,
                    descricao,
                    is_gerar_ocorrencia,
                    is_enc_atendimento,
                    excluido,
                    id_arv_mascara
                ) VALUES (
                    (SELECT arv_perg.id FROM arv_perg INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id WHERE arv_perg.descricao = '{$pergunta_principal}' AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1),
                    {$stringPriximoPasso1},
                    NULL, NULL, {$idPrioridade}, '{$alt['alernativa']}', {$is_gerar_ocorrencia}, {$is_enc_atendimento}, 0, {$mascara});
            ";
            
            $alt['orientacao_pre_socorro'] = str_replace("_", "", $alt['orientacao_pre_socorro']);
            $alt['orientacao_pre_socorro'] = trim($alt['orientacao_pre_socorro']);
            $orientacaoNome = $alt['orientacao_pre_socorro'];
            // if (preg_match("/HEMORRAGIA/", $alt['orientacao_pre_socorro'])) {
            //     $orientacaoNome = "HEMORRAGIA E CHOQUE";
            // }
            // if (preg_match("/PARTO/", $alt['orientacao_pre_socorro'])) {
            //     $orientacaoNome = "AUXÍLIO NO PARTO / NASCIMENTO";
            // }
            
            $orientacaoPreSocorroString = $alt['orientacao_pre_socorro'];
            $orientacaoPreSocorroString = str_replace([";"], ",", $orientacaoPreSocorroString);
            $aSplitOrientacoes = explode(",", $orientacaoPreSocorroString);

            $ordem = 0;
            foreach ($aSplitOrientacoes as $aSplitOrientacao) {
                $aSplitOrientacao = trim($aSplitOrientacao);
                if (is_numeric($aSplitOrientacao) || preg_match("/OPS/", $aSplitOrientacao)) {
                    $iOrientacao = preg_replace("/[^0-9]/", "", $aSplitOrientacao);
                    $isOrientacaoValidacao = false;
                    $orientacaoValidada = "";
                    $iOrientacao = trim($iOrientacao);
                    foreach ($a as $o) {
                        if (preg_match("/ORIENTAÇÃO/", $o[1])) {
                            $isOrientacaoValidacao = true;
                            continue;
                        }
                        if ($isOrientacaoValidacao && $iOrientacao == preg_replace("/[^0-9]/", "", $o[0])) {
                            $orientacaoValidada = $o[1];
                            break;
                        }
                    }

                    if ($orientacaoValidada) {
                        $sqlUpdateOrientacaoNatureza[] = "INSERT INTO arv_perg_alt_arv_card_perg (id_arv_perg_alt, id_arv_card_perg, ordem) VALUES (
                            (
                                SELECT arv_perg_alt.id FROM arv_perg_alt WHERE id_arv_perg = (
                                    SELECT arv_perg.id FROM arv_perg 
                                    INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                                    WHERE arv_perg.descricao = '{$pergunta_principal}' 
                                    AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                                ) AND descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0
                            
                            ),
                            (
                                SELECT arv_card_perg.id FROM arv_card_perg 
                                INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                                WHERE arv_card_perg.descricao = '{$orientacaoValidada}' AND arv_card.descricao = '{$natureza}' AND arv_card_perg.excluido = 0 LIMIT 1
                            ),
                            $ordem
                        );";
                        $ordem = $ordem + 1;
                    }
                } else if (
                    preg_match("/OVACE/", $aSplitOrientacao) ||
                    preg_match("/RCP/", $aSplitOrientacao) ||
                    preg_match("/DEA/", $aSplitOrientacao) ||
                    preg_match("/HEMORRAGIA E CHOQUE/", $aSplitOrientacao) ||
                    preg_match("/HEMORRAGIA CHOQUE/", $aSplitOrientacao) ||
                    preg_match("/AUXÍLIO NO PARTO \/ NASCIMENTO/", $aSplitOrientacao) ||
                    preg_match("/AUXILIO NO PARTO \/ NASCIMENTO/", $aSplitOrientacao) 
                    // in_array($aSplitOrientacao, [
                    //     'OVACE',
                    //     'RCP',
                    //     'DEA',
                    //     'HEMORRAGIA E CHOQUE',
                    //     'HEMORRAGIA CHOQUE',
                    //     'AUXÍLIO NO PARTO / NASCIMENTO'
                    // ])
                ) {
                    $numeroAlt = explode("-", $aSplitOrientacao);
                    $offset = 0;
                    $fraseArvore = $aSplitOrientacao;

                    $subqueryFiltroArvCardPerg = "SELECT arv_card_perg.id FROM arv_card_perg 
                        INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                        WHERE TRIM(UPPER(func_remove_acentos(arv_card.descricao))) = UPPER(func_remove_acentos('{$aSplitOrientacao}')) and arv_card_perg.is_pergunta_raiz = 1 and arv_card_perg.excluido = 0 limit 1";

                    if (count($numeroAlt) == 2 && intval($numeroAlt[1]) > 0) {
                        $fraseArvore = trim($numeroAlt[0]);
                        $offset = intval($numeroAlt[1]) - 1;

                        $subqueryFiltroArvCardPerg = "SELECT arv_card_perg.id FROM arv_card_perg 
                            INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                            WHERE TRIM(UPPER(func_remove_acentos(arv_card.descricao))) = UPPER(func_remove_acentos('{$fraseArvore}')) and arv_card_perg.excluido = 0 limit 1 offset {$offset}";
                    }

                    $aSplitOrientacao = $fraseArvore;

                    if (preg_match("/HEMORRAGIA/", $aSplitOrientacao)) {
                        $aSplitOrientacao = "HEMORRAGIA E CHOQUE";
                    }
                    if (preg_match("/PARTO/", $aSplitOrientacao)) {
                        $aSplitOrientacao = "AUXÍLIO NO PARTO / NASCIMENTO";
                    }
                    $orientacaoValidada = "";
                    $sqlUpdateOrientacaoNatureza[] = "INSERT INTO arv_perg_alt_arv_card_perg (id_arv_perg_alt, id_arv_card_perg, ordem) VALUES (
                        (
                            SELECT arv_perg_alt.id FROM arv_perg_alt WHERE id_arv_perg = (
                                SELECT arv_perg.id FROM arv_perg 
                                INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                                WHERE arv_perg.descricao = '{$pergunta_principal}' 
                                AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                            ) AND descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0
                        
                        ),
                        (
                            $subqueryFiltroArvCardPerg
                        ),
                        {$ordem}
                    );";
                    $ordem = $ordem + 1;
                } else if (strlen($aSplitOrientacao) > 0) {
                    // @todo está escrito algo que nao esta preparado
                    // echo "<b style='color:red;'>WARNING:</b> esta escrito alguma coisa errada na ORIENTAÇÕES PRÉ-SOCORRO da natureza <b>" . $natureza . "</b><br><div style='font-size: 9px;'>&nbsp;&nbsp;&nbsp;&nbsp;-> {$alt['orientacao_pre_socorro']}</div><br><br>";
                    // Vai para outra árvore, podendo ser EX: B6-2 ou B6

                    $numeroAlt = explode("-", $aSplitOrientacao);
                    $offset = 0;
                    $fraseArvore = $aSplitOrientacao;
                    if (count($numeroAlt) == 2 && intval($numeroAlt[1]) > 0) {
                        $fraseArvore = trim($numeroAlt[0]);
                        $offset = intval($numeroAlt[1]) - 1;
                    }

                    $sqlProximoPassoOutraNatureza[] = $sqlOutro = "INSERT INTO arv_perg_alt_arv_card_perg (id_arv_perg_alt, id_arv_card_perg, id_outra_arv_perg, ordem) VALUES (
                        (
                            SELECT arv_perg_alt.id FROM arv_perg_alt WHERE id_arv_perg = (
                                SELECT arv_perg.id FROM arv_perg 
                                INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                                WHERE arv_perg.descricao = '{$pergunta_principal}' 
                                AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                            ) AND descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0
                        
                        ),
                        415, -- padrão
                        (
                            select arv_perg1.id from arv_perg arv_perg1
                            inner join classificacao_atendimento classificacao_atendimento1 on
                                classificacao_atendimento1.id = arv_perg1.id_classificacao_atendimento
                            where classificacao_atendimento1.descricao like '{$fraseArvore}%' and arv_perg1.excluido  = 0 
                            limit 1 offset {$offset}
                        ),
                        {$ordem}
                    );";
                    $ordem = $ordem + 1;

                    $arvores = [
                        "T3",
                        "T7",
                        "B10",
                        "B17",
                        "T2",
                        "B6",
                        "R3",
                        "B2",
                        "B4",
                        "R2",
                        "B3",
                        "B5",
                        "B6",
                        "B7",
                        "B9",
                        "B11",
                        "B12",
                        "B13",
                        "C11",
                        "C12",
                        "C13",
                        "C14",
                        "R4",
                        "R5",
                        "C1",
                        "C2",
                        "C3",
                        "C4",
                        "C5",
                        "C6",
                        "C7",
                        "C8",
                        "C9",
                        "C10",
                        "R1",
                        "B1",
                        "B8",
                    ];
                    if (isset($_GET['OUTRO'])) {
                        $isOutro = false;
                        foreach ($arvores as $ar) {
                            if (preg_match("/" . $ar . "/", $fraseArvore)) {
                                $isOutro = true;
                            }
                        }
                      
                        if ($isOutro) {
                            echo $sqlOutro . "<br>";
                        }
                    }
                }
            }

            // if (preg_match("/T2/", $natureza) && preg_match("/4/", $alt['orientacao_pre_socorro'])) {
            //     var_dump($aSplitOrientacoes); exit;
            // }

            // Modo antigo
            if (false) {
                if (count(array_filter($aSplitOrientacoes, "numerico")) == count($aSplitOrientacoes) && !in_array($orientacaoNome, [
                    'OVACE',
                    'RCP',
                    'DEA',
                    'HEMORRAGIA E CHOQUE',
                    'HEMORRAGIA CHOQUE',
                    'AUXÍLIO NO PARTO / NASCIMENTO'
                ])) {
                    $ordem = 0;
                    foreach ($aSplitOrientacoes as $iOrientacao) {
                        $isOrientacaoValidacao = false;
                        $orientacaoValidada = "";
                        $iOrientacao = trim($iOrientacao);
                        foreach ($a as $o) {
                            if (preg_match("/ORIENTAÇÃO/", $o[1])) {
                                $isOrientacaoValidacao = true;
                                continue;
                            }
                            if ($isOrientacaoValidacao && $iOrientacao == $o[0]) {
                                $orientacaoValidada = $o[1];
                                break;
                            }
                        }

                        if ($orientacaoValidada) {
                            $sqlUpdateOrientacaoNatureza[] = "INSERT INTO arv_perg_alt_arv_card_perg (id_arv_perg_alt, id_arv_card_perg, ordem) VALUES (
                                (
                                    SELECT arv_perg_alt.id FROM arv_perg_alt WHERE id_arv_perg = (
                                        SELECT arv_perg.id FROM arv_perg 
                                        INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                                        WHERE arv_perg.descricao = '{$pergunta_principal}' 
                                        AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                                    ) AND descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0
                                
                                ),
                                (
                                    SELECT arv_card_perg.id FROM arv_card_perg 
                                    INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                                    WHERE arv_card_perg.descricao = '{$orientacaoValidada}' AND arv_card.descricao = '{$natureza}' AND arv_card_perg.excluido = 0 LIMIT 1
                                ),
                                $ordem
                            );";
                            $ordem = $ordem + 1;
                            //-----------------------------------------------------------
                            // $sqlUpdateOrientacaoNatureza[] = "
                            // UPDATE arv_perg_alt SET id_arv_card_pergunta = (
                            //     SELECT arv_card_perg.id FROM arv_card_perg 
                            //     INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                            //     WHERE arv_card_perg.descricao = '{$orientacaoValidada}' AND arv_card.descricao = '{$natureza}' AND arv_card_perg.excluido = 0 LIMIT 1
                            // ) WHERE id_arv_perg = (
                            //     SELECT arv_perg.id FROM arv_perg 
                            //     INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                            //     WHERE arv_perg.descricao = '{$pergunta_principal}' 
                            //     AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                            // ) AND descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0;
                            // ";
                        }
                    }
                } elseif ($orientacaoNome && in_array($orientacaoNome, [
                    'OVACE',
                    'RCP',
                    'DEA',
                    'HEMORRAGIA E CHOQUE',
                    'HEMORRAGIA CHOQUE',
                    'AUXÍLIO NO PARTO / NASCIMENTO'
                ])) {
                    // $orientacaoValidada = "";
                    // $sqlUpdateOrientacaoNatureza[] = "
                    // UPDATE arv_perg_alt SET id_arv_card_pergunta = (
                    //     SELECT arv_card_perg.id FROM arv_card_perg 
                    //     INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                    //     WHERE  
                    //     arv_card.descricao = '{$orientacaoNome}' and arv_card_perg.is_pergunta_raiz = 1 and arv_card_perg.excluido = 0 limit 1
                    // ) WHERE id_arv_perg = (
                    //     SELECT arv_perg.id FROM arv_perg 
                    //     INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                    //     WHERE arv_perg.descricao = '{$pergunta_principal}' 
                    //     AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') and arv_perg.excluido = 0 LIMIT 1
                    // ) AND descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0;
                    // ";
                    $orientacaoValidada = "";
                    $sqlUpdateOrientacaoNatureza[] = "INSERT INTO arv_perg_alt_arv_card_perg (id_arv_perg_alt, id_arv_card_perg, ordem) VALUES (
                        (
                            SELECT arv_perg_alt.id FROM arv_perg_alt WHERE id_arv_perg = (
                                SELECT arv_perg.id FROM arv_perg 
                                INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                                WHERE arv_perg.descricao = '{$pergunta_principal}' 
                                AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                            ) AND descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0
                        
                        ),
                        (
                            SELECT arv_card_perg.id FROM arv_card_perg 
                            INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                            WHERE UPPER(func_remove_acentos(arv_card.descricao)) = UPPER(func_remove_acentos('{$orientacaoNome}')) and arv_card_perg.is_pergunta_raiz = 1 and arv_card_perg.excluido = 0 limit 1
                        ),
                        1
                    );";
                } elseif (strlen($alt['orientacao_pre_socorro']) > 0) {
                    // @todo está escrito algo que nao esta preparado
                    if (!isset($_GET['OUTRO'])) {
                        echo "<b style='color:red;'>WARNING:</b> esta escrito alguma coisa errada na ORIENTAÇÕES PRÉ-SOCORRO da natureza <b>" . $natureza . "</b><br><div style='font-size: 9px;'>&nbsp;&nbsp;&nbsp;&nbsp;-> {$alt['orientacao_pre_socorro']}</div><br><br>";
                    }
                }
            }

            if ($alt['guarnicao']) {
                $alt['guarnicao'] = str_replace(",", ";", $alt['guarnicao']);
                $alt['guarnicao'] = str_replace("/", ";", $alt['guarnicao']);
                $alt['guarnicao'] = str_replace("-", ";", $alt['guarnicao']);
                $alt['guarnicao'] = str_replace(":", ";", $alt['guarnicao']);
                $guarnicaos = explode(";", $alt['guarnicao']);

                foreach ($guarnicaos as $f) {
                    $f = trim(str_replace(["'", 'é', 'É'], ["´", 'e', 'E'], $f));
                    $f = trim($f);
                    $alt['alernativa'] = str_replace("'", "´", $alt['alernativa']);
                    if (empty($f)) continue;
                    if ($formato == 'string') {
                        $sqlAgenciaEnvolvidaCadastroGuanicoes[$f] = "SELECT tipo_guarnicao.id FROM tipo_guarnicao WHERE UPPER(func_remove_acentos(TRIM(tipo_guarnicao.descricao))) = UPPER(func_remove_acentos(TRIM('{$f}'))) AND (tipo_guarnicao.excluido = 0) LIMIT 1\n\nINSERT INTO tipo_guarnicao (descricao, id_agencia) VALUES(TRIM('{$f}'), 3);";
                    } else {
                        $sqlAgenciaEnvolvidaCadastroGuanicoes[$f] = "SELECT tipo_guarnicao.id FROM tipo_guarnicao WHERE UPPER(func_remove_acentos(TRIM(tipo_guarnicao.descricao))) = UPPER(func_remove_acentos(TRIM('{$f}'))) AND (tipo_guarnicao.excluido = 0) LIMIT 1|INSERT INTO tipo_guarnicao (descricao, id_agencia) VALUES(TRIM('{$f}'), 3)";
                    }
                    
                    $sqlAgenciaEnvolvida[] = "
                    INSERT INTO arv_perg_alt_ag_envol (
                        id_tipo_guarnicao,
                        id_perg_alt,
                        excluido
                        ) VALUES (
                            (SELECT tipo_guarnicao.id FROM tipo_guarnicao WHERE UPPER(func_remove_acentos(TRIM(tipo_guarnicao.descricao))) = UPPER(func_remove_acentos(TRIM('{$f}'))) LIMIT 1),
                            (SELECT arv_perg_alt.id FROM arv_perg_alt WHERE arv_perg_alt.descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0 AND arv_perg_alt.id_arv_perg = (
                                SELECT arv_perg.id FROM arv_perg 
                                INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                                WHERE arv_perg.descricao = '{$pergunta_principal}' 
                                AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                            ) LIMIT 1),
                            0
                        );";
                }
            }

            if ($alt['guarnicao_externa']) {
                $alt['guarnicao_externa'] = str_replace(",", ";", $alt['guarnicao_externa']);
                $alt['guarnicao_externa'] = str_replace("/", ";", $alt['guarnicao_externa']);
                $alt['guarnicao_externa'] = str_replace("-", ";", $alt['guarnicao_externa']);
                $alt['guarnicao_externa'] = str_replace(":", ";", $alt['guarnicao_externa']);
                $guarnicaos = explode(";", $alt['guarnicao_externa']);

                foreach ($guarnicaos as $f) {
                    $f = trim(str_replace(["'", 'é', 'É'], ["´", 'e', 'E'], $f));
                    $f = trim($f);
                    $alt['alernativa'] = str_replace("'", "´", $alt['alernativa']);
                    if (empty($f)) continue;
                    if ($formato == 'string') {
                        $sqlAgenciaEnvolvidaCadastroGuanicoes[$f] = "SELECT cbm_tipo_apoio_externo.id FROM cbm_tipo_apoio_externo WHERE UPPER(func_remove_acentos(TRIM(cbm_tipo_apoio_externo.descricao))) = UPPER(func_remove_acentos(TRIM('{$f}'))) AND cbm_tipo_apoio_externo.excluido = 0 LIMIT 1\n\nINSERT INTO cbm_tipo_apoio_externo (descricao, envia_para_mobile) VALUES(TRIM('{$f})', 1);";
                    } else {
                        $sqlAgenciaEnvolvidaCadastroGuanicoes[$f] = "SELECT cbm_tipo_apoio_externo.id FROM cbm_tipo_apoio_externo WHERE UPPER(func_remove_acentos(TRIM(cbm_tipo_apoio_externo.descricao))) = UPPER(func_remove_acentos(TRIM('{$f}'))) AND cbm_tipo_apoio_externo.excluido = 0 LIMIT 1|INSERT INTO cbm_tipo_apoio_externo (descricao, envia_para_mobile) VALUES(TRIM('{$f}'), 1)";
                    }
                    
                    $sqlAgenciaEnvolvida[] = "
                    INSERT INTO arv_perg_alt_ag_ext_envol (
                        id_tipo_apoio,
                        id_perg_alt,
                        excluido
                        ) VALUES (
                            (SELECT cbm_tipo_apoio_externo.id FROM cbm_tipo_apoio_externo WHERE UPPER(func_remove_acentos(TRIM(cbm_tipo_apoio_externo.descricao))) = UPPER(func_remove_acentos(TRIM('{$f}'))) LIMIT 1),
                            (SELECT arv_perg_alt.id FROM arv_perg_alt WHERE arv_perg_alt.descricao = '{$alt['alernativa']}' AND arv_perg_alt.excluido = 0 AND arv_perg_alt.id_arv_perg = (
                                SELECT arv_perg.id FROM arv_perg 
                                INNER JOIN classificacao_atendimento ON arv_perg.id_classificacao_atendimento = classificacao_atendimento.id 
                                WHERE arv_perg.descricao = '{$pergunta_principal}' 
                                AND lower(classificacao_atendimento.descricao) = lower('{$natureza}') AND arv_perg.excluido = 0 LIMIT 1
                            ) LIMIT 1),
                            0
                        );";
                }
            }
        }
    }
}

function echoCardNatureza($orientacoes, $row, $lastInsertCard, $a, &$sqlsOrientacoesNaturezaAlternativa, &$sqlUpdateAlternativaCardNatureza, $natureza) {    
    if ($lastInsertCard) { 
        $pergunta_principal = "";
        
        foreach ($orientacoes as $alt) {
            if (empty(array_filter($alt))) continue;
            if (strtoupper($alt['alernativa']) == 'ALTERNATIVA') continue;
            if ($alt['pergunta_principal']) {
                $pergunta_principal = str_replace("'", "´", $alt['pergunta_principal']);
                break;
            }
        }
        
        foreach ($orientacoes as $alt) {
            if (empty(array_filter($alt))) continue;
            if (strtoupper($alt['alernativa']) == 'ALTERNATIVA') continue;
            $alernativa = str_replace("'", "´", $alt['alernativa']);
            if (!$alernativa) {
                $alernativa = "SEM ALT. VERIFIQUE";
                continue;
            }
            
            $proximoPassoOrientacaoNaturezaCard = 'NULL';
            $orientacaoNome = trim($alt['proximo_passo']);
            $orientacaoNome = str_replace("_", "", $orientacaoNome);
            if (preg_match("/HEMORRAGIA/", $alt['proximo_passo'])) {
                $orientacaoNome = "HEMORRAGIA E CHOQUE";
            }
            if (preg_match("/PARTO/", $alt['proximo_passo'])) {
                $orientacaoNome = "AUXÍLIO NO PARTO / NASCIMENTO";
            }
            if ($orientacaoNome && in_array($orientacaoNome, [
                'OVACE',
                'RCP',
                'DEA',
                'HEMORRAGIA E CHOQUE',
                'HEMORRAGIA CHOQUE',
                'AUXÍLIO NO PARTO / NASCIMENTO'
            ])) {
                $proximoPassoOrientacaoNaturezaCard = "(SELECT arv_card_perg.id 
                    FROM arv_card_perg 
                    INNER JOIN arv_card ON arv_card_perg.id_arv_card = arv_card.id
                    WHERE UPPER(func_remove_acentos(arv_card.descricao)) = UPPER(func_remove_acentos('{$orientacaoNome}')) 
                    and arv_card_perg.is_pergunta_raiz = 1 
                    AND arv_card_perg.excluido = 0
                    limit 1)";
            }
            $alt['proximo_passo'] = preg_replace("/[^0-9]/", "", $alt['proximo_passo']);
            if ($alt['proximo_passo'] && is_numeric($alt['proximo_passo'])) {
                $isOri = false;
                foreach ($a as $row3) {
                    if (preg_match("/ORIENTAÇÃO/", $row3[1]))  {
                        $isOri = true;
                        continue;
                    }
                    if ($isOri) {
                        if ($row3[0] == $alt['proximo_passo']) {
                            $row[1] = str_replace("'", "´", $row[1]);
                            $natureza = str_replace("'", "´", $natureza);
                            $sqlUpdateAlternativaCardNatureza[] = "UPDATE arv_card_perg_alt SET id_prox_arv_card_perg = 
                                (
                                    SELECT arv_card_perg2.id FROM arv_card_perg arv_card_perg2
                                    WHERE arv_card_perg2.descricao = '{$row[1]}' AND arv_card_perg2.id_arv_card = (
                                        SELECT arv_card.id FROM arv_card WHERE arv_card.descricao = '{$natureza}'
                                        AND arv_card.excluido = 0
                                    ) AND arv_card_perg2.excluido = 0
                                ) 
                                WHERE id_arv_card_perg = (SELECT arv_card_perg.id FROM arv_card_perg WHERE descricao = '{$pergunta_principal}' AND arv_card_perg.excluido = 0 AND arv_card_perg.id_arv_card = (
                                        SELECT arv_card.id FROM arv_card WHERE arv_card.descricao = '{$natureza}' AND arv_card.excluido = 0
                                ) LIMIT 1) AND descricao = '{$alernativa}' AND arv_card_perg_alt.excluido = 0 AND id_arv_mascara = (SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'CHECKED' AND arv_mascara.excluido = 0 LIMIT 1);
                                ";
                            break;
                        }
                    }
                }
            }
            $sqlsOrientacoesNaturezaAlternativa[] = "INSERT INTO arv_card_perg_alt (
                id_arv_card_perg,
                id_prox_arv_card_perg,
                descricao,
                id_arv_mascara)
                VALUES(
                    (
                        SELECT arv_card_perg.id FROM arv_card_perg                     
                        WHERE descricao = '{$pergunta_principal}' 
                        AND arv_card_perg.id_arv_card = (
                            SELECT arv_card.id FROM arv_card WHERE arv_card.descricao = '{$natureza}' AND arv_card.excluido = 0 
                        )
                        AND arv_card_perg.excluido = 0 
                        LIMIT 1
                    ),
                    $proximoPassoOrientacaoNaturezaCard,
                    '{$alernativa}',
                    (SELECT arv_mascara.id FROM arv_mascara WHERE chave = 'CHECKED' AND arv_mascara.excluido = 0 LIMIT 1)
                );";
        }
    }
}

function getSql($a, $natureza, $formato) {
    $alternativas = [];
    $orientacoes = [];
    $lastInsert = "";
    $lastInsertCard = "";
    $sqlAlternativas = [];
    $sqlAgenciaEnvolvida = [];
    $sqlsPerguntas = [];
    $sqlsOrientacoesNatureza = [];
    $sqlsOrientacoesNaturezaAlternativa = [];
    $sqlUpdateOrientacaoNatureza = [];
    $sqlProximoPassoOutraNatureza = [];
    $sqlAgenciaEnvolvidaCadastroGuanicoes = [];
    $sqlUpdateAlternativaCardNatureza = [];
    $sqlCard = "";
    $sqlNatureza = "";
    $isOrientacoes = false;
    $naturezaNome = "";
    $perguntaRaiz = 1;
    foreach ($a as $iii => $row) {
        if ($iii == 0) {
            $naturezaNome = $natureza;
            $naturezaNome = str_replace("'", "´", $naturezaNome);
            $naturezaNome = str_replace("🔼", "", $naturezaNome);

            $idTipoAtendimento = 351;
            if (preg_match("/R\d /", $natureza) || preg_match("/R\d\d /", $natureza)) {
                $idTipoAtendimento = 351;
            }
            if (preg_match("/B\d /", $natureza) || preg_match("/B\d\d /", $natureza)) {
                $idTipoAtendimento = 354;
            }
            if (preg_match("/T\d /", $natureza) || preg_match("/T\d\d /", $natureza)) {
                $idTipoAtendimento = 353;
            }
            if (preg_match("/C\d /", $natureza) || preg_match("/C\d\d /", $natureza)) {
                $idTipoAtendimento = 352;
            }
            
            if ($formato == 'string') {
                $sqlNatureza = "SELECT * FROM classificacao_atendimento WHERE descricao = '{$naturezaNome}' AND classificacao_atendimento.excluido = 0 ;\nINSERT INTO public.classificacao_atendimento (descricao, id_tipo_atendimento, id_nivel_atendimento, id_categoria) VALUES('{$naturezaNome}', {$idTipoAtendimento}, 3, 24);\n
                    INSERT INTO public.classificacao_atend_agencia (id_classificacao_atendimento, id_agencia) VALUES ((SELECT classificacao_atendimento1.id FROM classificacao_atendimento classificacao_atendimento1 WHERE classificacao_atendimento1.descricao = '{$naturezaNome}' AND classificacao_atendimento1.id_nivel_atendimento = 3  AND classificacao_atendimento1.excluido = 0), 3);
                ";

                $sqlCard = "INSERT INTO arv_card (id_classificacao_atendimento, descricao) VALUES (
                    (SELECT classificacao_atendimento.id FROM classificacao_atendimento WHERE descricao = '{$naturezaNome}'  AND classificacao_atendimento.excluido = 0 LIMIT 1),
                    '{$naturezaNome}'
                );";
            } else {
                $sqlNatureza = "SELECT * FROM classificacao_atendimento WHERE descricao = '{$naturezaNome}' AND classificacao_atendimento.excluido = 0|INSERT INTO public.classificacao_atendimento (descricao, id_tipo_atendimento, id_nivel_atendimento, id_categoria) VALUES('{$naturezaNome}', {$idTipoAtendimento}, 3, 24)|INSERT INTO public.classificacao_atend_agencia (id_classificacao_atendimento, id_agencia) VALUES ((SELECT classificacao_atendimento1.id FROM classificacao_atendimento classificacao_atendimento1 WHERE classificacao_atendimento1.descricao = '{$naturezaNome}' AND classificacao_atendimento1.id_nivel_atendimento = 3  AND classificacao_atendimento1.excluido = 0), 3)";

                $sqlCard = "SELECT * FROM arv_card WHERE descricao = '{$naturezaNome}' AND excluido = 0|INSERT INTO arv_card (id_classificacao_atendimento, descricao) VALUES ((SELECT classificacao_atendimento.id FROM classificacao_atendimento WHERE descricao = '{$naturezaNome}'  AND classificacao_atendimento.excluido = 0 LIMIT 1), '{$naturezaNome}')|";
            }
            continue;
        }

        if (preg_match("/ORIENTAÇÃO/", @$row[1]) || $isOrientacoes) {
            if (preg_match("/ORIENTAÇÃO/", $row[1])) {
                echoAlternativas($alternativas, $row, $lastInsert, $a, $sqlAlternativas, $sqlAgenciaEnvolvida, $sqlUpdateOrientacaoNatureza, $natureza, $sqlProximoPassoOutraNatureza, $sqlAgenciaEnvolvidaCadastroGuanicoes, $formato);
            }
            // Orientações aqui
            if ($isOrientacoes) {
                if (empty(array_filter($row))) {
                    echoCardNatureza($orientacoes, $row, $lastInsertCard, $a, $sqlsOrientacoesNaturezaAlternativa, $sqlUpdateAlternativaCardNatureza, $natureza);
                    break;
                }

                if (is_numeric($row[0]) || preg_match("/OPS/", strtoupper($row[0]))) {
                    echoCardNatureza($orientacoes, $row, $lastInsertCard, $a, $sqlsOrientacoesNaturezaAlternativa, $sqlUpdateAlternativaCardNatureza, $natureza);
                    $orientacoes = [];

                    $row[1] = str_replace("'", "´", $row[1]);
                    $sqlsOrientacoesNatureza[] = "INSERT INTO arv_card_perg (id_arv_card, descricao) VALUES (
                        (SELECT arv_card.id FROM arv_card WHERE arv_card.descricao = '{$naturezaNome}' AND arv_card.excluido = 0 LIMIT 1),
                        '{$row[1]}'
                    );";
                    $lastInsertCard = $row[1];
                    $orientacoes[] = [
                        'pergunta_principal'        => $row[1],
                        'alernativa'                => 'Orientado',
                        'prioridade'                => $row[6],
                        'enc_despacho'              => $row[5],
                        'guarnicao'                 => $row[7],
                        'guarnicao_externa'         => !empty($row[12]) ? $row[12] : "",//coluna M
                        'classificacao'             => $row[4],
                        'proximo_passo'             => null // $row[3]
                    ];
                    if (!isset($a[$iii + 1])) {
                        echoCardNatureza($orientacoes, $row, $lastInsertCard, $a, $sqlsOrientacoesNaturezaAlternativa, $sqlUpdateAlternativaCardNatureza, $natureza);
                    }
                } else {
                    $orientacoes[] = [
                        'pergunta_principal'        => $row[1],
                        'alernativa'                => 'Orientado',
                        'prioridade'                => $row[6],
                        'enc_despacho'              => $row[5],
                        'guarnicao'                 => $row[7],
                        'guarnicao_externa'         => !empty($row[12]) ? $row[12] : "",//coluna M
                        'classificacao'             => $row[4],
                        'proximo_passo'             => null // $row[3]
                    ];

                    if (!isset($a[$iii + 1])) {
                        echoCardNatureza($orientacoes, $row, $lastInsertCard, $a, $sqlsOrientacoesNaturezaAlternativa, $sqlUpdateAlternativaCardNatureza, $natureza);
                    }
                }
            }
            

            $isOrientacoes = true;
            continue;
        }

       
        if (is_numeric($row[0])) {
            echoAlternativas($alternativas, $row, $lastInsert, $a, $sqlAlternativas, $sqlAgenciaEnvolvida, $sqlUpdateOrientacaoNatureza, $natureza, $sqlProximoPassoOutraNatureza, $sqlAgenciaEnvolvidaCadastroGuanicoes, $formato);
            $alternativas = [];
            $row[1] = str_replace("'", "´", $row[1]);
            $sqlsPerguntas[] = "INSERT INTO arv_perg (id_classificacao_atendimento, descricao, excluido, is_pergunta_raiz) VALUES ((SELECT classificacao_atendimento.id FROM classificacao_atendimento WHERE lower(classificacao_atendimento.descricao) = lower('{$natureza}')  AND classificacao_atendimento.excluido = 0 LIMIT 1), '$row[1]', 0, {$perguntaRaiz});";
            $perguntaRaiz = 0;
            $lastInsert = $row[1];
            $row[2] = str_replace("'", "´", $row[2]);

            $alternativas[] = [
                'pergunta_principal'        => $row[1],
                'alernativa'                => $row[2],
                'prioridade'                => $row[6],
                'enc_despacho'              => $row[5],
                'guarnicao'                 => $row[7],
                'guarnicao_externa'         => !empty($row[12]) ? $row[12] : "",//coluna M
                'classificacao'             => $row[4],
                'proximo_passo'             => $row[3],
                'orientacao_pre_socorro'    => $row[10],
                'tipo_mascara'              => isset($row[11]) ? $row[11] : ""
            ];
        } else {
            $row[2] = str_replace("'", "´", @$row[2]);
            
            foreach ($alternativas as $alternativa) {
                if (trim($alternativa['alernativa']) == trim($row[2])) {
                    die("\nEXISTE ALGUMA PERGUNTA COM O MESMO NOME, VERIFICAR N = {$natureza}\n");
                }
            }

            $alternativas[] = [
                'pergunta_principal'        => "",
                'alernativa'                => $row[2],
                'prioridade'                => $row[6],
                'enc_despacho'              => $row[5],
                'guarnicao'                 => $row[7],
                'guarnicao_externa'         => !empty($row[12]) ? $row[12] : "",//coluna M
                'classificacao'             => $row[4],
                'proximo_passo'             => $row[3],
                'orientacao_pre_socorro'    => $row[10],
                'tipo_mascara'              => isset($row[11]) ? $row[11] : ""
            ];

            

            if (!isset($a[$iii + 1])) {
                echoAlternativas($alternativas, $row, $lastInsert, $a, $sqlAlternativas, $sqlAgenciaEnvolvida, $sqlUpdateOrientacaoNatureza, $natureza, $sqlProximoPassoOutraNatureza, $sqlAgenciaEnvolvidaCadastroGuanicoes, $formato);
            }
        }
    }
   
    return [
        'sqlsPerguntas'                     => $sqlsPerguntas,
        'sqlAlternativas'                   => $sqlAlternativas,    
        'sqlAgenciaEnvolvida'               => $sqlAgenciaEnvolvida,    
        'sqlCard'                           => [$sqlCard],
        'sqlNatureza'                       => [$sqlNatureza],
        'sqlProximoPassoOutraNatureza'      => $sqlProximoPassoOutraNatureza,
        'sqlsOrientacoesNatureza'           => $sqlsOrientacoesNatureza,    
        'sqlsOrientacoesNaturezaAlternativa'=> $sqlsOrientacoesNaturezaAlternativa,    
        'sqlUpdateOrientacaoNatureza'       => $sqlUpdateOrientacaoNatureza,
        'sqlAgenciaEnvolvidaCadastroGuanicoes'=> $sqlAgenciaEnvolvidaCadastroGuanicoes,
        'sqlUpdateAlternativaCardNatureza' => $sqlUpdateAlternativaCardNatureza
    ];
}
