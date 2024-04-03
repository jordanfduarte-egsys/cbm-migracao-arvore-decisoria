<?php
require "./vendor/autoload.php";
require "./function.php";
set_time_limit(-1);

echo "<pre>";

$nomeArquivo = "arvoreFINAL.csv";
$file = "./{$nomeArquivo}";

echo "Iniciando processo<br><br>\n";

echo "Removendo arquivo: {$nomeArquivo}<br><br>\n";
unlink("{$nomeArquivo}");

echo "Criando novo arquivo: {$nomeArquivo}<br><br>\n";
exec("xlsx2csv arvore.xlsx --all > {$nomeArquivo}", $a, $b);


$dir = "./querys";
$di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);

foreach ( $ri as $file ) {
    $file->isDir() ?  rmdir($file) : unlink($file);
}
// https://www.geeksforgeeks.org/methods-to-convert-xlsx-format-files-to-csv-on-linux-cli/#:~:text=Gnumeric%20Spreadsheet%20Program&text=To%20install%20Gnumeric%20in%20Linux,Gnumeric%20repository%20via%20Linux%20terminal.&text=Now%20to%20convert%20xlsx%20format,Gnumeric%20to%20convert%20the%20file.&text=To%20view%20the%20contents%20of,to%20check%20the%20csv%20file.
// $ xlsx2csv SampleData.xlsx --all > Output.csv

function printAll($all, $formato) {
    $sql = [];
    if ($formato == 'string') {
        foreach ($all as $row) {
            $sql[] = trim($row) . "\n";
        }
        return implode("\n", $sql);
    }

    foreach ($all as &$row) {
        $row = str_replace('"', '', $row);
    }

    return json_encode($all);
}

$sqlAgenciaEnvolvidaCadastroGuanicoes = [];
$sqlProximoPassoOutraNatureza = [];
try {
    $reader = \Ark4ne\XlReader\Factory::createReader($file);    
    $reader->load();
        
    $formato = "json";
    $aParams = [];
    $natureza = "";
    $naturezaOld = "";
    $initParam = false;
    echo "Iniciando processo dos scripts<br><br>\r";
    $processados = 0;
    $countUpdatesNaturezas = 0;

    // @TODO Colocar aqui planilhas que estao tmb com erro na lista ou remover do arquivo xls
    //include("./planilhas-validas.php");
    $listArquivos = [
        'R1 - INCIDENTE EM MEIO LÍQUID',
        'R1 - INCIDENTE EM MEIO LÍQUIDO-V2',
        'R2 - CHOQUE ELÉTRICO-RAIO',
        'R3 - INCONSCIENCIA-DESMAIO',
        'R4 - OVACE BEBE+ADULTO',
        'R5 - PCR',
        'C1 - AVC-DERRAME',
        'C2 - ALERGIA-REAÇÃO MEDICAMENT',
        'C3 - CONVULSÕES – EPILEPSIA',
        'C4 - DIABETES',
        'C5 - DOR ABDOMINAL',
        'C6 - DOR DE CABEÇA',
        'C7 - DOR NAS COSTAS',
        'C8 - DOR NO PEITO - PROBLEMAS C',
        'C9 - ENVENENAMENTO - OVERDOSE',
        'C10 - GRAVIDEZ - PARTO - ABORTO',
        'C11 - PROBLEMA DESC - HOMEM CAÍ',
        'C12 - PROBLEMAS RESPIRATÓRIOS',
        'C13 - PSIQUIÁTRICO',
        'C14 - SUICÍDIO - TENTATIVA DE S',
        'T1 - ACIDENTE C MAQUINA-INDUST',
        'T2 NOVO - ACIDENTE DE TRANSIT',
        'T3 - AGRESSÃO - VIOL. SEXUAL',
        'T4 - FAB E FAF',
        'T5 - MORDIDA DE ANIMAL',
        'T6 - ANIMAIS PEÇONHENTOS',
        'T7 - QUEDAS-TRAUMA DE COLUNA',
        'T8 - QUEIMADURAS',
        'T9 - TRAUMAS ESPECÍFICOS',
        'B1 - ANIMAL - SALVAM. - CAPTU',
        'B2 - ARVORE - QUEDA DE - CORT',
        'B3 - DESLIZAMENTO - SOTERRAMENT',
        'B4 - ELETRICO - RISCO',
        'B5 - EXPLOSÃO',
        'B6 - FUMAÇA EXTERIOR - VERIFI',
        'B7 - INCÊNDIO - ALARME',
        'B8 - INCÊNDIO EM EDIFICAÇÃO',
        'B9 - INCÊNDIO EXTERIOR',
        'B10 - INCÊNDIO FLORESTAL',
        'B11 - INCÊNDIO VEICULAR',
        'B12 - INCIDENTE COM AERONAVE',
        'B13 - INCIDENTE COM EMBARCAÇÃO',
        'B14 - INCIDENTE COM TREM - METR',
        'B15 - INSETOS - REMOÇÃO – EXTER',
        'Nova B16 - PESSOA PERDIDA',
        'B17 - PRODUTOS PERIGOSOS',
        'B18 - SALV ALTURA',
        'B19 - SALV AQUATICO - ENCHENTE',
        'B20 - SALV CONFINADO - COLAPSAD',
        'B21 - SALV ELEVADOR',
        'B22 - VAZAMENTO GAS - GLP-GN',
        'B23 - SERVIÇO APOIO ORGAO',
        'B24 - SERVIÇO ASSIST CIDADAO',
        'B25 - VENDAVAL GRANIZO',
        'COMPLEMENTAR - VEÍCULO'
    ];
    foreach ($reader->read() as $row) {
        if (preg_match("/------/", $row['A'])) {
            $natureza = preg_replace("/-------- \d -/", "", $row['A']);
            $natureza = preg_replace("/-------- \d\d -/", "", $natureza);
            //$natureza = str_replace("COMPL", "COMPLEMENTAR", $natureza);
            $natureza = trim($natureza);
            $natureza = str_replace("'", "´", $natureza);
            $natureza = str_replace("🔼", "", $natureza);

            //"./planilhas-validas.php"
            // echo $natureza . " ANTES \n";
            if (!in_array($natureza, $listArquivos)) {
                $aParams = [];
                continue;
            }
        //     if ($natureza == 'B8 - INCÊNDIO EM EDIFICAÇÃO') {
        //       //  die("KDKOS");
        //     }
            
            $initParam = true;
        }

        if (preg_match("/(------).*/", $row['A']) && !empty($aParams)) {
            // echo $naturezaOld . " COMPARE \n";
            // var_dump($aParams); exit;
            // if ($naturezaOld == 'B25 - VENDAVAL GRANIZO') {
            //     var_dump($processados);
            //     die("KDKOS");
            // }
            if (!in_array($naturezaOld, $listArquivos)) {
                $aParams = [];
                continue;
            }

            $result = getSql($aParams, $naturezaOld, $formato);
           
            $sqlsPerguntas = $result['sqlsPerguntas'];
        
            $sqlAlternativas = $result['sqlAlternativas'];
            $sqlNatureza = $result['sqlNatureza'];
            $sqlAgenciaEnvolvida = $result['sqlAgenciaEnvolvida'];
            $sqlCard = $result['sqlCard'];
            $sqlProximoPassoOutraNatureza2 = $result['sqlProximoPassoOutraNatureza'];
            $sqlProximoPassoOutraNatureza = array_merge($sqlProximoPassoOutraNatureza, $sqlProximoPassoOutraNatureza2);
            $sqlsOrientacoesNatureza = $result['sqlsOrientacoesNatureza'];
            $sqlsOrientacoesNaturezaAlternativa = $result['sqlsOrientacoesNaturezaAlternativa'];
            $sqlUpdateOrientacaoNatureza = $result['sqlUpdateOrientacaoNatureza'];
            $sqlAgenciaEnvolvidaCadastroGuanicoes2 = $result['sqlAgenciaEnvolvidaCadastroGuanicoes'];
            $sqlUpdateAlternativaCardNatureza = $result['sqlUpdateAlternativaCardNatureza'];
            $sqlAgenciaEnvolvidaCadastroGuanicoes = array_merge($sqlAgenciaEnvolvidaCadastroGuanicoes, $sqlAgenciaEnvolvidaCadastroGuanicoes2);

            if ($formato == "string") {
                $sqlFinal = "";
                $sqlFinal .= "--SQL CLASSIFICACAO -- ATENÇÃO VER SE JA NAO EXISTE (EXECUTAR ANTES DE TD DESSA NATUREZA)<--------------\n\n";            
                $sqlFinal .=  printAll($sqlNatureza, $formato);

                $sqlFinal .=  "\n\n-- SQL CARD\n\n";
                $sqlFinal .=  printAll($sqlCard, $formato);

                $sqlFinal .=  "\n\n-- SQL de PERGUNTAS\n\n";
                $sqlFinal .=  printAll($sqlsPerguntas, $formato);
                
                $sqlFinal .=  "\n\n-- SQL de ALTERNATIVAS\n\n";
                $sqlFinal .=  printAll($sqlAlternativas, $formato);
                
                $sqlFinal .=  "\n\n-- SQL de AGE. ENVOLVIDO\n\n";
                $sqlFinal .=  printAll($sqlAgenciaEnvolvida, $formato);
                
                $sqlFinal .=  "\n\n-- SQL ORIENTACOES NATUREZA\n\n";
                $sqlFinal .=  printAll($sqlsOrientacoesNatureza, $formato);
                
                $sqlFinal .=  "\n\n-- SQL ALTERNATIVA ORIENTACAO\n\n";
                $sqlFinal .=  printAll($sqlsOrientacoesNaturezaAlternativa, $formato);
                
                $sqlFinal .=  "\n\n-- SQL INSERT ORIENTACOES DA NATUREZA NOVA TABELA <--- (EXECUTAR DEPOIS DE TODAS AS QUERYS DESSA NATUREZA)\n\n";
                $sqlFinal .=  printAll($sqlUpdateOrientacaoNatureza, $formato);

                $sqlFinal .= "\n\n-- SQL UPDATE ALTERNATIVAS DAS ORIENTAÇÔES DESSA NATUREZA QUE CHAMA OUTRA ORIENTAÇÃO DESSA NATUREZA TMB\n\n";
                $sqlFinal .=  printAll($sqlUpdateAlternativaCardNatureza, $formato);

                // $sqlFinal .=  "\n\n-- SQL UPDATE PROXIMO PASSO OUTRA NATUREZA DIFERENTE <--- (EXECUTAR DEPOIS DE INSERIR TODAS AS NATUREZAS)\n\n";
                // $sqlFinal .=  printAll($sqlProximoPassoOutraNatureza);          , $formato  
                if ($sqlProximoPassoOutraNatureza) {
                    $countUpdatesNaturezas += count($sqlProximoPassoOutraNatureza);
                    $sqlFinal1 = "\n\n-- " . $naturezaOld . "\n\n";
                    $sqlFinal1 .=  "\n\n-- SQL UPDATE PROXIMO PASSO OUTRA NATUREZA DIFERENTE: {$naturezaOld} <--- (EXECUTAR DEPOIS DE INSERIR TODAS AS NATUREZAS)\n\n";
                    $sqlFinal1 .=  printAll($sqlProximoPassoOutraNatureza, $formato);
                    error_log($sqlFinal1, 3, "./querys/10000 - query - EXECUTAR_DEPOIS-TODAS-NATUREZAS.sql");
                }

                $processadosItem = $processados + 1;
                error_log($sqlFinal, 3, "./querys/{$processadosItem} - query - {$naturezaOld}.sql");
                if (!isset($_GET['OUTRO'])) {
                    echo "<div style='color:green;'>Finalizado processo na natureza: <b>" . $naturezaOld . "</b></div> <br><br>\n";
                }
            } else {
                $sqlFinal = [];
                $sqlFinal[] = $sqlNatureza;
                $sqlFinal[] = $sqlCard;
                $sqlFinal[] = $sqlsPerguntas;
                $sqlFinal[] = $sqlAlternativas;
                $sqlFinal[] = $sqlAgenciaEnvolvida;
                $sqlFinal[] = $sqlsOrientacoesNatureza;
                $sqlFinal[] = $sqlsOrientacoesNaturezaAlternativa;
                $sqlFinal[] = $sqlUpdateOrientacaoNatureza;
                $sqlFinal[] = $sqlUpdateAlternativaCardNatureza;

                $processadosItem = $processados + 1;
                error_log(printAll($sqlFinal, $formato), 3, "./querys/{$processadosItem} - query - {$naturezaOld}.sql");
            }

            $aParams = [];
            $processados++;
        } elseif (!preg_match("/(------).*/", $row['A'])) {
            $naturezaOld = $natureza;
            if ($initParam) {
                $aParam = [];
                foreach ($row as $column) {
                    $aParam[] = empty($column) && $column != 0 ? "" : $column;
                }
                $aParams[] = $aParam;
            }

            //  if ($naturezaOld == "B8 - INCÊNDIO EM EDIFICAÇÃO") {
            //      var_dump($aParams);exit;
            //  }
        }
    }

    if ($sqlAgenciaEnvolvidaCadastroGuanicoes) {
        if ($formato == "string") {
            $sqlFinal2 = "\n\n-- " . $naturezaOld . "\n\n";
            $sqlFinal2 .=  "\n\n-- SQL INSERT AGENCIA ENVOLVIDA: {$naturezaOld} <--- (EXECUTAR ANTES DE TUDO)\n\n";
            $sqlFinal2 .=  printAll($sqlAgenciaEnvolvidaCadastroGuanicoes, $formato);
            error_log($sqlFinal2, 3, "./querys/00 - query - EXECUTAR_ANTES.sql");
        } else {
            error_log(printAll($sqlAgenciaEnvolvidaCadastroGuanicoes, $formato), 3, "./querys/00 - query - EXECUTAR_ANTES.sql");
        }
    }

    if ($sqlProximoPassoOutraNatureza) {
        error_log(printAll($sqlProximoPassoOutraNatureza, $formato), 3, "./querys/10000 - query - EXECUTAR_DEPOIS-TODAS-NATUREZAS.sql");
    }
    
    echo "Processados: " . $processados . "<br><br>\n";
    echo "Processados naturezas updates: " . $countUpdatesNaturezas . "<br><br>\n";
    echo "FIM<br><br>\n";
} catch (Exception $e) {
    var_dump($e);
}