<?php

namespace App\DataFixtures;

use App\Entity\Direction;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\UserAccount;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BotFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $directions = $manager->getRepository(Direction::class)->findAll();

        $accountsMap = [];
        foreach ($directions as $direction) {
            foreach (['ask', 'bid'] as $side) {
                $firstname = 'Bot';
                $lastname = $direction->getCode();

                $bot = new User();
                $bot->setIsBot(true);
                $bot->setEmail(strtolower("bot{$direction->getCode()}{$side}@test.com"));
                $bot->setFirstName($firstname);
                $bot->setLastName($lastname);
                $bot->setPassword($this->passwordEncoder->encodePassword(
                    $bot,
                    'password'
                ));

                $manager->persist($bot);

                $accountsMap[$bot->getUsername()] = $direction;
            }

        }
        $manager->flush();

        $money = [
            'BTC' => 1000,
            'USD' => 1000000,
            'EUR' => 1000000,
            'USDT' => 1000000,
            'Dash' => 100000,
            'BCH' => 100000,
            'LTC' => 100000,
            'ETH' => 100000,
        ];

        /**
         * @var string $botUsername
         * @var Direction $direction
         */
        foreach ($accountsMap as $botUsername => $direction) {
            /** @var User $bot */
            $bot = $manager->getRepository(User::class)->findOneByEmail($botUsername);

            $accountBase = $bot->getPrimaryAccountByCurrency($direction->getCurrencyBase());
            $accountQuote = $bot->getPrimaryAccountByCurrency($direction->getCurrencyQuote());

            $transactionBase = (new Transaction())
                ->setAmount($money[$direction->getCurrencyBase()->getCode()])
                ->setUserAccount($accountBase)
                ->setType(Transaction::TYPE_DEPOSIT)
                ->setDescription('Bot account deposit (base)');

            $manager->persist($transactionBase);

            $transactionQuote = (new Transaction())
                ->setAmount($money[$direction->getCurrencyQuote()->getCode()])
                ->setUserAccount($accountQuote)
                ->setType(Transaction::TYPE_DEPOSIT)
                ->setDescription('Bot account deposit (quote)');

            $manager->persist($transactionQuote);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CurrencyFixtures::class,
            DirectionFixtures::class,
            TransactionFixtures::class,
        ];
    }
}
