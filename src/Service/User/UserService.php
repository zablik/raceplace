<?php

namespace App\Service\User;

use App\Entity\Profile;
use App\Entity\User;
use App\Service\User\Exception\UserException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $em;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
    }

    public static function getRoles(): array
    {
        return [
            self::ROLE_USER,
            self::ROLE_ADMIN,
        ];
    }

    public function createUser(string $email, string $password, array $roles)
    {
        if (count($roles) !== count(array_intersect($roles, self::getRoles()))) {
            throw new UserException('Unexpected roles provided');
        }

        $user = (new User())
            ->setEmail($email)
            ->setRoles($roles)
        ;

        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function attachProfile(User $user, string $profileName)
    {
        /** @var Profile $profile */
        $profile = $this->em->getRepository(Profile::class)->findOneBy(['name' => $profileName]);

        if ($profile) {
            $user->setProfile($profile);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $profile;
    }
}
