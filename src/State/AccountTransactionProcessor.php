<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Account;
use App\Entity\AccountTransaction;
use App\Entity\ApiClient;
use Symfony\Component\Security\Core\Security;

class AccountTransactionProcessor implements ProcessorInterface
{
    private ProcessorInterface $decorated;
    private Security $security;

    public function __construct(ProcessorInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        //Set the transaction source
        if ($data instanceof AccountTransaction && $operation instanceof Post) {
            $client = $this->security->getUser();
            if($client instanceof ApiClient) {
                $data->setSource($client); //set current api client as transaction source
                $date = new \DateTime($_ENV['APP_ACCOUNT_LIFETIME'] ?? Account::LIFETIME);
                if(($account = $data->getAccount()) && $account->getExpiryDate() < $date) {
                    $account->setExpiryDate($date); //update account expiry date
                }
            }
        }

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
