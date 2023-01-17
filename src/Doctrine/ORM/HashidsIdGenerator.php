<?php


namespace App\Doctrine\ORM;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Hashids\Hashids;

class HashidsIdGenerator extends AbstractIdGenerator
{
    private Hashids $hashids;

    public function generate(EntityManager $em, $entity): string
    {
        $i = 0;
        do {
            $id = substr($this->hashids->encode(random_int(1000000, 9999999)), 0, 6);
            $item = $em->find($entity::class, $id);
            if (!$item) {
                return $id;
            }
        } while(++$i < 100);
        throw new \Exception('HashidsIdGenerator could not generate a unique ID');
    }

    /**
     * @required
     */
    public function setHashids(Hashids $hashids): void
    {
        $this->hashids = $hashids;
    }
}