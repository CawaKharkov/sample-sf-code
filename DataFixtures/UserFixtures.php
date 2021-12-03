<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
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
        $firstnames = ['Arnold', 'Brad', 'Angelina', 'Jean-Claude', 'Conor'];
        $lastnames = ['Schwarznegger', 'Pitt', 'Joley', 'Van Damm', 'McGregor'];

        for ($i=1; $i<=5; $i++) {
            $firstname = $firstnames[rand(0, count($firstnames)-1)];
            $lastname = $lastnames[rand(0, count($lastnames)-1)];

            $user = new User();
            $user->setEmail("user{$i}@test.com");
            $user->setFirstName($firstname);
            $user->setLastName($lastname);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'password'
            ));

            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CurrencyFixtures::class,
            DirectionFixtures::class,
        ];
    }
}
