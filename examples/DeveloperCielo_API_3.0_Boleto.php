<?php

//

ini_set('display_errors', 1);

//

$composer_autoload = dirname(dirname(__FILE__)) . "/vendor/autoload.php";

require $composer_autoload;

//

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;

use Cielo\API30\Ecommerce\Request\CieloRequestException;
// ...
// Configure o ambiente
//$environment = Environment::sandbox();
$environment = Environment::production();

// Configure seu merchant
//$merchant = new Merchant('MERCHANT ID', 'MERCHANT KEY');
$merchant = new Merchant('a2133427-a0f8-4fe8-b605-6469161e7711', 'XUMUBMGQBPNUAYIESMSHTCNLVTNEXIDPHXQRZYOC');

// Crie uma instância de Sale informando o ID do pagamento
$sale = new Sale('123');

// Crie uma instância de Customer informando o nome do cliente
$customer = $sale->customer('Fulano de Tal');

// Crie uma instância de Payment informando o valor do pagamento
$payment = $sale->payment(15700);

// Crie uma instância de Credit Card utilizando os dados de teste
// esses dados estão disponíveis no manual de integração
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", "Visa")
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal");

// Crie o pagamento na Cielo
try {
    // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
    $sale = (new CieloEcommerce($merchant, $environment))->createSale($sale);

    print_r('1');
    print_r($sale);

    // Com a venda criada na Cielo, já temos o ID do pagamento, TID e demais
    // dados retornados pela Cielo
    $paymentId = $sale->getPayment()->getPaymentId();

    print_r($paymentId);

    // Com o ID do pagamento, podemos fazer sua captura, se ela não tiver sido capturada ainda
    //$sale = (new CieloEcommerce($merchant, $environment))->captureSale($paymentId, 15700, 0);

    // E também podemos fazer seu cancelamento, se for o caso
    //$sale = (new CieloEcommerce($merchant, $environment))->cancelSale($paymentId, 15700);
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();

    print_r('2');
    print_r($error);
}