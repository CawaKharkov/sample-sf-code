<?php

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $users = $manager->getRepository(User::class)->findAll();

        $types = [
            Transaction::TYPE_DEPOSIT,
            Transaction::TYPE_WITHDRAW,
        ];

        /*foreach ($users as $user) {
            foreach ($user->getUserAccounts() as $account) {
                for ($i=0; $i<10; $i++) {
                    $transaction = (new Transaction())
                        ->setAmount(rand(10, 1000))
                        ->setUserAccount($account)
                        ->setType($types[array_rand($types)])
                        ->setDescription('Test transaction')
                    ;

                    $manager->persist($transaction);
                }
            }

        }*/

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
