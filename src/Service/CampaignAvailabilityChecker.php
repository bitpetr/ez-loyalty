<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Campaign;

class CampaignAvailabilityChecker
{
    public function isCampaignAvailable(Campaign $campaign, Account $account = null): bool
    {
        //Check if the campaign is within the set dates
        if(!$this->isWithinDates($campaign)) {
            return false;
        }

        //Check if the campaign is available for new accounts only and an account exists
        if($account && $campaign->getOnlyNew()) {
            return false;
        }

        //Check if the campaign is available only once and was already used by this account
        if($account && $campaign->getSingleUse() && $account->getCampaigns()->contains($campaign)) {
            return false;
        }

        return true;
    }

    protected function isWithinDates(Campaign $campaign): bool
    {
        $now = new \DateTime();
        $validUntil = $campaign->getValidUntil();
        $validSince = $campaign->getValidSince();

        return $validSince < $now && (!$validUntil || $now < $validUntil);
    }
}