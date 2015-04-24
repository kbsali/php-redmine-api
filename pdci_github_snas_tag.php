<?php

/**
 * @file
 * This file holds example commands for reading, creating, updating and deleting redmine components.
 */
// As this is only an example file, we make sure, this is not accidently executed and may destroy real
// life content.
//return;

require_once 'vendor/autoload.php';

$sComandoSvnRevisaoTags = "svn info  https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags";

$return = exec($sComandoSvnRevisaoTags, $output);

echo "decobrindo a ultima revisão de tag do svn do " . $return . " \n\n ";

$nRevision = trim(eregi_replace('Revision:', '', $output[5]));

$nLastChangedRev = trim(eregi_replace('Last Changed Rev:', '', $output[8]));
//$nLastChangedRev = 1488;


echo "nRevision = " . $nRevision . " nLastChangedRev = " . $nLastChangedRev . " \n\n ";

$dDataAtual = new DateTime(date('Y-m-d'));

$dDataSeginte = clone $dDataAtual;
$dDataSeginte->add(new DateInterval('P01D'));

$sVersaoExtensaoSnas = '1.6';
$sVersaoSgdoc = '4.3.83';

$sCaminhoDestinoDiretorio = 'out_svn_snas/';
$sNomeDiretorioOutGitHub = 'github_sgdoc-codigo-master/';

$sNomeTagDestino = $dDataAtual->format('Ymd') . "_sgdoc" . $nLastChangedRev . "-vs." . $sVersaoSgdoc;

$sDiretorioExtensao = "pr_snas/" . $sVersaoExtensaoSnas;
$sComandoSvnCopiarTagBase = "svn mkdir -m 'criando tag " . $sNomeTagDestino . "' https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino . "/ ";

$sCaminhoUrlGitHubMaster = "https://github.com/sgdoc/sgdoc-codigo.git";
$sComandoGitHubClone = "git clone " . $sCaminhoUrlGitHubMaster . " " . $sCaminhoDestinoDiretorio . $sNomeDiretorioOutGitHub;
$sComandoDeletarDiretorio = 'rm -rf ' . $sCaminhoDestinoDiretorio;

$sComandoGitHubDownloadZip = "wget https://github.com/sgdoc/sgdoc-codigo/archive/master.zip ";

$sCaminhoSvnOut = $sCaminhoDestinoDiretorio . $sNomeTagDestino . "/";

$sComandoSvnCheckout = "svn co https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino . " " . $sCaminhoSvnOut;

$sComandoSvnCheckoutConf = "svn co https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino . " " . $sCaminhoSvnOut;

$sComandoSvnListarTags = "svn ls https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/";



$sProject_id = "pdci-sgdoc-snas";
$sNomeRedmineVersao = 'extensaoSnas-' . $sVersaoExtensaoSnas;

$client = new Redmine\Client('https://projeto.sgdoc.icmbio.gov.br', '69974349168', '123456');

$aUserLogado = $client->api('user')->getCurrentUser();
$aVersions = $client->api('version')->getIdByName($sProject_id, $sNomeRedmineVersao);



$return = \exec($sComandoDeletarDiretorio, $output);

if ($return === false) {
    die("ERRO comando :: '" . $sComandoDeletarDiretorio . "' " . $return . " \n " . var_dump($output) . " \n ");
} else {
    echo "Comando de deletando diretorio executado =>  " . $sComandoDeletarDiretorio . " => MSG :: " . $return . " \n " . var_dump($output) . " \n ";
}

if (!file_exists($sCaminhoDestinoDiretorio)) {
    mkdir($sCaminhoDestinoDiretorio);
}


//var_dump($argc);
//echo "arec => " . $argc . " \n";
if ($argc > 1) {
    echo " \n Valor passados para o scrpt \n\n ";
    var_dump($argv);
    eval('$' . $argv[1] . ';');
    echo "\n \n id = " . $id;
}

echo "Sctript para criar a tag no nosso svn vindo do github. \n\n";

echo "criando tag nova para o servidor do icmbio. \n\n ";

echo "verificando se a tag ja foi gerada: \n";


echo "Comando " . $sComandoSvnListarTags . "\n sendo executado: \n";

$return = exec($sComandoSvnListarTags, $output);


if (is_numeric(array_search($sNomeTagDestino . '/', $output))) {
    echo "Diretorio da TAG svn ja foi criando \n\n\n\n " . $return;
} else {
    echo "Diretorio da TAG de  destino nao existe. Diretorio da tag Criado \n\n";
    echo "Comando " . $sComandoSvnCopiarTagBase . "\n sendo executado: \n";
    $return = exec($sComandoSvnCopiarTagBase, $output);
    echo "\n\n return copiar => " . $return . " \n\n ";
}
/*
 * fazer clone do github-master-codigosgdoc
 */

echo "\n\n\n ********************************* \n\n\n";

try {
    echo "Clonando Repositório do gitHub\n\n";
    echo "Comando " . $sComandoGitHubClone . "\n sendo executado: \n";
    $return = exec($sComandoGitHubClone, $output);

    echo "CLONE GIT HUB executado com sucesso \n\n ";
} catch (Exception $exc) {
    echo "ERRO AO CLONAR O GIT";
    echo $exc->getTraceAsString();
    exit();
}

$sComandoParaEscreverArquivoComRetornoGitShow = "#!/bin/bash
cd " . $sCaminhoDestinoDiretorio . $sNomeDiretorioOutGitHub . "
pwd
git show > ../tmp_git_show_master
";

/*
 * Verifica se a versão da extensao informada existe no github 
 * 
 */



if (!file_exists($sCaminhoDestinoDiretorio . $sNomeDiretorioOutGitHub . 'extensoes/' . $sDiretorioExtensao) ){
   die('ERRO :: Diretório da versão '.$sVersaoExtensaoSnas.' não existe em '. $sCaminhoDestinoDiretorio . $sNomeDiretorioOutGitHub . 'extensoes/' . $sDiretorioExtensao .' 
    

');    
}

try {
    $return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "tmp_scrit_get_git_show", $sComandoParaEscreverArquivoComRetornoGitShow);
    chmod($sCaminhoDestinoDiretorio . 'tmp_scrit_get_git_show', 0770);
    echo " \n \n executando tmp_scrit_get_git_show = " . $return . " \n\n " . var_dump($output) . "\n\n";
    $return = exec($sCaminhoDestinoDiretorio . './tmp_scrit_get_git_show', $output);
} catch (Exception $exc) {
    echo "ERRO ";
    echo $exc->getTraceAsString();
    die('ERRO ao executar tmp_scrit_get_git_show');
}

echo "\n\n\n ********************************* \n\n\n";

echo "Fazendo checkout da tag do SVN - ICMBIO \n\n";
echo "Comando " . $sComandoSvnCheckout . "\n sendo executado: \n";
$return = exec($sComandoSvnCheckout, $output);

echo "\n\n svn => " . $return . " \n\n ";

echo "\n\n\n ********************************* \n\n\n";

echo "Download zipado da versão master do github  \n\n";
echo "Comando " . $sComandoGitHubDownloadZip . "\n sendo executado: \n";

$return = exec($sComandoGitHubDownloadZip, $output);

echo "\n\n\n ********************************* \n\n\n";

$sComandoUnzipDownloadZip = "unzip -n master.zip  -x sgdoc-codigo-master/cfg/configuration.ini";

echo "UNZip da versão master do github  sem o arquivo de configuração\n\n";
echo "Comando " . $sComandoUnzipDownloadZip . "\n sendo executado: \n";
$return = exec($sComandoUnzipDownloadZip, $output);


$sComandoMovendoArquivosDaDescompactaParaTag = "mv -f sgdoc-codigo-master/* " . $sCaminhoSvnOut . " | mv -f sgdoc-codigo-master/.* " . $sCaminhoSvnOut . "  ";
echo "Movendo tag git descompactada para tag do svn " . $sComandoMovendoArquivosDaDescompactaParaTag . "  \n";
$return = exec($sComandoMovendoArquivosDaDescompactaParaTag, $output);

/*
 * apagando o arquivo master.zip e o diretorio extraindo que foi descompactado e copiado para out_svn_snas
 */

$return = exec('rm -rf master.zip', $output);

$return = exec('rm -rf sgdoc-codigo-master', $output);

/*
 * Pegando o arquivo padrao de configuração do ambiente do icmbio
 * 
 * 
 */

/* @var $return type */

$return = \exec("svn co https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/_base_tag_configure_ambientes/cfg/ " . $sCaminhoDestinoDiretorio . "cfg/", $output);

/* @var $sConteudoDoArquivoConfiguração type */

$sConteudoDoArquivoConfiguração = \preg_replace("'X.Y.Z'", $sNomeTagDestino . " $sDiretorioExtensao ", \file_get_contents($sCaminhoDestinoDiretorio . "cfg/configuration.ini"));

$sConteudoDoArquivoConfiguração = \preg_replace("'extensao.versao'", $sDiretorioExtensao, $sConteudoDoArquivoConfiguração);

echo "\n Alterado os parametros de rervisao e extensao \n\n ";

//echo $sConteudoDoArquivoConfiguração." \n\n\n ";

$return = \file_put_contents($sCaminhoDestinoDiretorio . $sNomeTagDestino . "/cfg/configuration.ini", $sConteudoDoArquivoConfiguração);

/*
 * 
 * Pegando as informacoes do github para serem comitados
 * nao implementado
 */


$sTextoAberturaChamado = "
cotec-suporte = cotec.suporte@icmbio.gov.br
c/c = %s


Abertura de chamado para o terceiro nível.

Ambiente a ser atualizado: %s
Número da Revisão do documento Plano de Implantação: 1362
Caminho de acesso ao Plano de Implantação no SVN: https://svn.icmbio.gov.br/svn/sgdoc/documentacao/plano-de-implantacao-sgdocf3-pr.snas_extensoes.odt

Caminho da tag svn: %s

Realizar a seguinte rotina:

%s
";

/*
 * abiente de treinamento
 */
$sTextoAberturaChamadoRotinaBancodeTreinamento = "
    1) Subir o ultimo dump de produção da aplicação pr.sgdoc para o ambiente de treinamento.

    2) Rodar o script de atualização de banco: https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino . "/extensoes/" . $sDiretorioExtensao . "/script.sql
        
    3) Realizar o upload do arquivo de dump da produção do pr.sgdoc no ticket do https://projeto.sgdoc.icmbio.gov.br/issues/%s/edit
    ";

$sTextoAberturaChamadoRotinaAplicacaoTreinamento = "
    1) Atualizar a aplicação conforme tag https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino . " .

    2) Garantir que os diretórios: documento_virtual e cache possua permissão de escrita pelo apache.
    
    3) Realizar o upload de arquivos de logs da aplicação assim como alterar a situação do ticket como executada. Segue link do ticket no redmine ticket do https://projeto.sgdoc.icmbio.gov.br/issues/%s/edit
";

/*
 * Ambiente de produção
 */

$sConteudoDoTicketBancoTRN = sprintf($sTextoAberturaChamado, 'dauro.sobrinho.terceirizado@icmbio.gov.br', 'TRN::SGDOC [BANCO DE DADOS] (SNAS) ', "https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino, $sTextoAberturaChamadoRotinaBancodeTreinamento);

$sConteudoDoTicketAplicacaoTRN = sprintf($sTextoAberturaChamado, 'fabio.silva-lima.terceirizado@icmbio.gov.br', 'TRN::SGDOC [Atualizar aplicação] (SNAS)', "https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino, $sTextoAberturaChamadoRotinaAplicacaoTreinamento);

$sConteudoDoTicketBancoPRODUCAO = sprintf($sTextoAberturaChamado, 'dauro.sobrinho.terceirizado@icmbio.gov.br', 'PRD::PR.SGDOC [BANCO DE DADOS] (SNAS) ', "https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino, "");

$sConteudoDoTicketAplicacaoPRODUCAO = sprintf($sTextoAberturaChamado, 'fabio.silva-lima.terceirizado@icmbio.gov.br', 'PRD::PR.SGDOC [Atualizar aplicação] (SNAS)', "https://svn.icmbio.gov.br/svn/sgdoc/implementacao/tags/" . $sNomeTagDestino, "");

$return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "email-ticket-banco-TRN.txt", $sConteudoDoTicketBancoTRN);

$return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "email-ticket-aplicacao-TRN.txt", $sConteudoDoTicketAplicacaoTRN);

$return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "email-ticket-banco-PR.SGDOC_PRODUCAO.txt", $sConteudoDoTicketBancoPRODUCAO);

$return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "email-ticket-aplicacao-PR.SGDOC_PRODUCAO.txt", $sConteudoDoTicketAplicacaoPRODUCAO);

$sTextoDoArquivoDeComitarNoSVN = "#!/bin/bash
cd " . $sCaminhoSvnOut . "
pwd
svn add .gitignore
svn add .htaccess
svn add *
svn  ci -m '".  file_get_contents($sCaminhoDestinoDiretorio.'tmp_git_show_master')."' > ../tmp_log_scrit_commit_tag_gerada 
echo $!
";

$return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "tmp_scrit_commit_tag_gerada", $sTextoDoArquivoDeComitarNoSVN);
chmod($sCaminhoDestinoDiretorio . 'tmp_scrit_commit_tag_gerada', 0770);


/*
 * Registro no redmine
 */

if ($client->api('version')->getIdByName($sProject_id, $sNomeRedmineVersao) === false) {
    echo "\n Versão nao criada no redmine, Registrando versão " . $sNomeRedmineVersao . " no projeto " . $sProject_id . " \n\n";

    $aVersions = $client->api('version')->create($sProject_id, array(
        'name' => $sNomeRedmineVersao,
        'description' => null,
        'status' => null,
        'sharing' => null,
        'due_date' => null,
    ));
} else {
    echo "\n versão " . $sNomeRedmineVersao . " ja criada no redmine no projeto " . $sProject_id . " . \n\n ";
}

$id_versao = $client->api('version')->getIdByName($sProject_id, $sNomeRedmineVersao);

$aMemberships = $client->api('membership')->all($sProject_id);

foreach ($aMemberships['memberships'] as $key => $vMembros) {
    echo " \n " . $vMembros['user']['name'];
}

$sRedmineSubject = "Publicação da versão " . $sNomeRedmineVersao . " para homologação";

$aIssues = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineSubject));

if ($aIssues['total_count'] == 0) {
    echo " \n\n\n Criando tarefa de publicação no redmine";

    $aIssues = $client->api('issue')->create(
            array(
                'subject' => $sRedmineSubject,
                'description' => \file_get_contents($sCaminhoDestinoDiretorio . "tmp_git_show_master"),
                'project_id' => $sProject_id,
                'category_id' => 58, //PDCI-Demanda
                'priority_id' => null,
                'fixed_version_id' => $id_versao,
                'status_id' => null,
                'tracker_id' => null,
                'assigned_to_id' => $aUserLogado['user']['id'],
                'author_id' => $aUserLogado['user']['id'],
                'due_date' => date('Y-m-d'),
                'start_date' => date('Y-m-d'),
                'watcher_user_ids' => array(304, 305, 287)
            )
    );
} else {
    echo " \n\n\n A tarefa " . $sRedmineSubject . " já esta cadastrada";
}

$aIssues = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineSubject));


$id_IssuesPai = $aIssues['issues'][0]['id'];

echo "\n\n Demanda registrada com o id " . $aIssues['issues'][0]['id'] . " titulo: " . $aIssues['issues'][0]['subject'] . " \n\n";


try {

    $sRedmineTarefaAberturaChamado = "Abertura de chamado para o terceiro nível para publicação da aplicação " . $sNomeRedmineVersao . " no ambiente de treinamento.";

//     sai 20:43
    $aIssuesTarefaAberturaChamado = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineTarefaAberturaChamado));

    if ($aIssuesTarefaAberturaChamado['total_count'] == 0) {
        echo " \n\n\n Criando tarefa filha de abertura de chamado para o terceiro nivel. \n\n ";

        $aParametros = array(
            'subject' => $sRedmineTarefaAberturaChamado,
            'description' => \file_get_contents($sCaminhoDestinoDiretorio . "email-ticket-aplicacao-TRN.txt"),
            'project_id' => $sProject_id,
            'category_id' => 53, //PDCI-Tarefa
            'priority_id' => null,
            'fixed_version_id' => $id_versao,
            'status_id' => null,
            'parent_issue_id' => $id_IssuesPai,
            'assigned_to_id' => 306, //fABIO
            'author_id' => $aUserLogado['user']['id'],
            'due_date' => date('Y-m-d'),
            'start_date' => date('Y-m-d'),
            'watcher_user_ids' => array(304, 305, 287)
        );

        echo " \n\n\n ";
        // print_r($aParametros);
        echo " \n\n\n ";

        $aIssuesTarefaAberturaChamado = $client->api('issue')->create($aParametros);

        if (is_object($aIssuesTarefaAberturaChamado)) {
            echo " \n\n\n\ e um objeto id " . $aIssuesTarefaAberturaChamado->id;
        } else {
            die('ERRO :: ao criar tarefa filha de abertura de chamado para o terceiro nivel. ' . print_r($aIssuesTarefaAberturaChamado));
        }
        /*
         * 
         */
    } else {
        echo " \n\n\n A tarefa  " . $sRedmineTarefaAberturaChamado . " já esta cadastrada";
    }

    $aIssuesTarefaAberturaChamado = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineTarefaAberturaChamado));


    echo "\n\n Tarefa para o terceiro nivel aplicação id = " . $aIssuesTarefaAberturaChamado['issues'][0]['id'] . " \n\n";

    /*
     * Atualização do link de redmine para 
     * 
     * 
     */

    if ($aIssuesTarefaAberturaChamado['issues'][0]['id'] > 0) {
        echo " \n\n\n Update a tarefa filha de abertura de chamado para o terceiro para APLICAÇÃO \n\n";

        $return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "email-ticket-aplicacao-TRN.txt", sprintf($sConteudoDoTicketAplicacaoTRN, $aIssuesTarefaAberturaChamado['issues'][0]['id']));

        $aIssuesTarefaAberturaChamado = $client->api('issue')->update($aIssuesTarefaAberturaChamado['issues'][0]['id'], array(
            'description' => \file_get_contents($sCaminhoDestinoDiretorio . "email-ticket-aplicacao-TRN.txt"),
            'assigned_to_id' => null, //Fabio lima silva fabio.silva-lima.terceirizado@icmbio.gov.br 306
                )
        );
        //$aIssuesTarefaAberturaChamado = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineTarefaAberturaChamadoBanco));
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
    die("ERRO no :: " . $sRedmineTarefaAberturaChamado);
}

/*
 * Abertura de ticket para banco de dados
 */
//ECHO " \n\n \n ".$sCaminhoDestinoDiretorio . $sNomeTagDestino . '/extensoes/' . $sDiretorioExtensao . '/script.sql'. " \n\n\n".        
  //      file_exists($sCaminhoDestinoDiretorio . $sNomeTagDestino . '/extensoes/' . $sDiretorioExtensao . '/script.sql');

if (file_exists($sCaminhoDestinoDiretorio . $sNomeTagDestino . '/extensoes/' . $sDiretorioExtensao . '/script.sql')) {
    $sRedmineTarefaAberturaChamadoBanco = "Abertura de chamado para o terceiro nível para publicar o script de banco de dados da " . $sNomeRedmineVersao . " no ambiente de treinamento.";

//     sai 20:43
    $aIssuesTarefaAberturaChamadoBanco = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineTarefaAberturaChamadoBanco));

    if ($aIssuesTarefaAberturaChamadoBanco['total_count'] == 0) {
        echo " \n\n\n Criando tarefa filha de abertura de chamado para o terceiro nivel ";

        $aIssuesTarefaAberturaChamadoBanco = $client->api('issue')->create(
                array(
                    'subject' => $sRedmineTarefaAberturaChamadoBanco,
                    'description' => \file_get_contents($sCaminhoDestinoDiretorio . "email-ticket-banco-TRN.txt"),
                    'project_id' => $sProject_id,
                    'category_id' => 53, //PDCI-Tarefa
                    'priority_id' => null,
                    'fixed_version_id' => $id_versao,
                    'status_id' => null,
                    'parent_issue_id' => $id_IssuesPai,
                    'assigned_to_id' => $aUserLogado['user']['id'],
                    'author_id' => $aUserLogado['user']['id'],
                    'due_date' => date('Y-m-d'),
                    'start_date' => date('Y-m-d'),
                    'watcher_user_ids' => array(304, 305, 287)
                )
        );

        if (is_object($aIssuesTarefaAberturaChamadoBanco)) {

            echo " \n\n\n\ e um objeto id " . $aIssuesTarefaAberturaChamadoBanco->id;
            echo " \n\n Update a tarefa filha de abertura de chamado para o terceiro para BANCO DE DADOS " . $aIssuesTarefaAberturaChamadoBanco->id . "REALIZADA COM SUCESSO \n ";
        } else {
            die('ERRO :: ao criar tarefa filha de abertura de chamado para o terceiro nivel de BANCO DE DADOS. ' . print_r($aIssuesTarefaAberturaChamadoBanco));
        }

        
    } else {
        echo " \n\n\n A tarefa  " . $sRedmineTarefaAberturaChamadoBanco . " já esta cadastrada";
    }

    $aIssuesTarefaAberturaChamadoBanco = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineTarefaAberturaChamadoBanco));
    
    echo "\n\n Tarefa 3 nivel banco id " . $aIssuesTarefaAberturaChamadoBanco['issues'][0]['id'] . " titulo: " . $aIssuesTarefaAberturaChamadoBanco['issues'][0]['subject'] . " \n\n";

    /*
     * Atualizando ticket de banco para o 
     */
    if ($aIssuesTarefaAberturaChamadoBanco['issues'][0]['id'] > 0) {

        $return2[] = \file_put_contents($sCaminhoDestinoDiretorio . "email-ticket-banco-TRN.txt", sprintf(file_get_contents($sCaminhoDestinoDiretorio . "email-ticket-banco-TRN.txt"), $aIssuesTarefaAberturaChamadoBanco['issues'][0]['id']));

        echo " \n\n\n Update a tarefa filha de abertura de chamado para o terceiro nivel para banco ";

        $aIssuesTarefaAberturaChamadoBanco = $client->api('issue')->update($aIssuesTarefaAberturaChamadoBanco['issues'][0]['id'], array(
            'description' => \file_get_contents($sCaminhoDestinoDiretorio . "email-ticket-banco-TRN.txt"),
            'category_id' => 53, //PDCI-Tarefa
            'assigned_to_id' => 24, //Dauro
                )
        );

        $aIssuesTarefaAberturaChamadoBanco = $client->api('issue')->all(array('project_id' => $sProject_id, 'subject' => $sRedmineTarefaAberturaChamadoBanco));
    }
}


echo "\n\n\n\ executando o arquivo tmp_scrit_commit_tag_gerada para comitar a tag";
$output = '';

try{
 //   mail('rafael.mello.terceirizado@icmbio.gov.br' , 'teste' , file_get_contents($sCaminhoDestinoDiretorio . "email-ticket-banco-TRN.txt") );
} catch (Exception $ex) {
   echo "\n\n\n ". $ex->getMessage(). " \n\n\n\ ";
}

        
//$return = exec($sCaminhoDestinoDiretorio . './tmp_scrit_commit_tag_gerada', $output);

 //   echo "FINALIZADO PID DO PROCESSO BACK = " . $return . " \n\n " . var_dump($output);

    
