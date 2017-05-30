<?php

//

ini_set('display_errors', 1);

//

$composer_autoload = dirname(dirname(__FILE__)) . "/vendor/autoload.php";

require $composer_autoload;

//

?>

<?php

$remetenteNome='xGoogle+S%C3%A3o+Paulo';
$remetenteLogradouro='Av.+Brigadeiro+Faria+Lima';
$remetenteNumero='3900';
$remetenteComplemento='5%C2%BA+andar';
$remetenteBairro='Itaim';
$remetenteCep='04538-132';
$remetenteEstado='101';
$remetenteCidade='S%C3%A3o+Paulo';
$destinatarioNome='Google+Belo+Horizonte';
$destinatarioLogradouro='Av.+Bias +Fortes';
$destinatarioNumero='382';
$destinatarioComplemento='6%C2%BA+andar';
$destinatarioBairro='Lourdes';
$destinatarioCep='30170-010';
$destinatarioEstado='77';
$destinatarioCidade='Belo+Horizonte';
$servicoDePostagem='81019';
$servicosAdicionaisSelecionados[]='mp';
$peso='0.500';
$valorDeclarado='75.90';

$relatorio='etiquetas';

$is_sandbox = true;
$enviroment = ($is_sandbox) ? \PhpSigep\Config::ENV_DEVELOPMENT : \PhpSigep\Config::ENV_PRODUCTION;


$accessDataParaAmbienteDeHomologacao = new \PhpSigep\Model\AccessDataHomologacao();
$config = new \PhpSigep\Config();
$config->setAccessData($accessDataParaAmbienteDeHomologacao);
$config->setEnv($enviroment);
$config->setCacheOptions(
    array(
        'storageOptions' => array(
            // Qualquer valor setado neste atributo será mesclado ao atributos das classes 
            // "\PhpSigep\Cache\Storage\Adapter\AdapterOptions" e "\PhpSigep\Cache\Storage\Adapter\FileSystemOptions".
            // Por tanto as chaves devem ser o nome de um dos atributos dessas classes.
            'enabled' => false,
            'ttl' => 10,// "time to live" de 10 segundos
            'cacheDir' => sys_get_temp_dir(), // Opcional. Quando não inforado é usado o valor retornado de "sys_get_temp_dir()"
        ),
    )
);
\PhpSigep\Bootstrap::start($config);

//dump($config);exit;

$servicoDePostagem = new \PhpSigep\Model\ServicoDePostagem($servicoDePostagem);
$servicosAdicionais = array();
foreach ($servicosAdicionaisSelecionados as $servicoAdicional) {
    $valorDeclarado = null;
    if ($servicoAdicional == 'mp') {
        $codServicosAdicional = \PhpSigep\Model\ServicoAdicional::SERVICE_MAO_PROPRIA;
    } else if ($servicoAdicional == 'vd') {
        $codServicosAdicional = \PhpSigep\Model\ServicoAdicional::SERVICE_VALOR_DECLARADO;
        $valorDeclarado       = (float)$_POST['valorDeclarado'];
    } else if ($servicoAdicional == 'ar') {
        $codServicosAdicional = \PhpSigep\Model\ServicoAdicional::SERVICE_AVISO_DE_RECEBIMENTO;
    } else {
        continue;
    }
    $servicosAdicionais[] = new \PhpSigep\Model\ServicoAdicional(array(
        'codigoServicoAdicional' => $codServicosAdicional,
        'valorDeclarado'         => $valorDeclarado,
    ));
}
$dimensao = new \PhpSigep\Model\Dimensao();
$dimensao->setAltura(20);
$dimensao->setLargura(20);
$dimensao->setComprimento(20);
$dimensao->setTipo(\PhpSigep\Model\Dimensao::TIPO_PACOTE_CAIXA);

//dump($dimensao);exit;

$accessData = \PhpSigep\Bootstrap::getConfig()->getAccessData();
$destinatario = new \PhpSigep\Model\Destinatario();
$destinatario->setNome($destinatarioNome);
$destinatario->setLogradouro($destinatarioLogradouro);
$destinatario->setNumero($destinatarioNumero);
$destinatario->setComplemento($destinatarioComplemento);

//dump($destinatario);exit;

$destino = new \PhpSigep\Model\DestinoNacional();
$destino->setBairro($destinatarioBairro);
$destino->setCep($destinatarioCep);
$destino->setCidade($destinatarioCidade);
$destino->setUf($destinatarioEstado);

//dump($destino);exit;

$etiqueta = new \PhpSigep\Model\Etiqueta();
$etiqueta->setEtiquetaSemDv('SI' . mt_rand(10000000, 99999999) . 'BR');

//dump($etiqueta);exit;

$encomenda = new \PhpSigep\Model\ObjetoPostal();
$encomenda->setServicosAdicionais($servicosAdicionais);
$encomenda->setDestinatario($destinatario);
$encomenda->setDestino($destino);
$encomenda->setDimensao($dimensao);
$encomenda->setEtiqueta($etiqueta);
$encomenda->setPeso($peso);
$encomenda->setServicoDePostagem($servicoDePostagem);

//dump($encomenda);exit;

$remetente = new \PhpSigep\Model\Remetente();
$remetente->setNome($remetenteNome);
$remetente->setNumero($remetenteNumero);
$remetente->setUf($remetenteEstado);
$remetente->setCidade($remetenteCidade);
$remetente->setBairro($remetenteBairro);
$remetente->setCep($remetenteCep);
//$remetente->setCodigoAdministrativo($accessData->getCodAdministrativo());
$remetente->setComplemento($remetenteComplemento);
//$remetente->setDiretoria($accessData->getDiretoria());
$remetente->setLogradouro($remetenteLogradouro);

//dump($remetente);exit;

$plp = new \PhpSigep\Model\PreListaDePostagem();
$plp->setAccessData($accessData);
$plp->setEncomendas(array($encomenda));
$plp->setRemetente($remetente);

//dump($plp);exit;

$logoFile = null;
$layoutChancela = array();

$pdf = new \PhpSigep\Pdf\CartaoDePostagem2016($plp, time(), $logoFile, $layoutChancela);
$_file_etiqueta = 'etiquetas.pdf';
$pdf->render('F', $_file_etiqueta);

//

$pdf  = new \PhpSigep\Pdf\ListaDePostagem($plp, time());
$_file_plp = 'plp.pdf';
$pdf->render('F', $_file_plp);

//


//dump($servico);exit;

// return

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename='.basename($_file_etiqueta));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($_file_etiqueta);

unlink($_file_etiqueta);

exit;